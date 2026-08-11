<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FinanceAccount;
use App\Models\FinanceCategory;
use App\Models\FinanceDebt;
use App\Models\FinanceDebtPayment;
use App\Models\FinanceDebtSchedule;
use App\Models\FinanceTransaction;
use App\Services\FinanceBalanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class FinanceDebtController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin/finance/debts/Index', ['debts' => FinanceDebt::with(['payments', 'schedules' => fn ($q) => $q->where('status', 'upcoming')->orderBy('due_date')])->latest()->get()->map(fn ($d) => $this->data($d))]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/finance/debts/Form');
    }

    public function store(Request $r): RedirectResponse
    {
        $d = FinanceDebt::create($this->validated($r));

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Debt or receivable added successfully.']);

        return to_route('admin.finance.debts.show', $d);
    }

    public function show(FinanceDebt $debt): Response
    {
        $debt->load(['schedules' => fn ($q) => $q->orderBy('due_date'), 'payments' => fn ($q) => $q->with('schedule')->latest('payment_date')]);

        return Inertia::render('admin/finance/debts/Show', ['debt' => $this->data($debt), 'accounts' => FinanceAccount::where('is_active', true)->orderBy('name')->get(['id', 'name'])]);
    }

    public function edit(FinanceDebt $debt): Response
    {
        return Inertia::render('admin/finance/debts/Form', ['debt' => $debt]);
    }

    public function update(Request $r, FinanceDebt $debt): RedirectResponse
    {
        $debt->update($this->validated($r));

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Debt or receivable updated successfully.']);

        return to_route('admin.finance.debts.show', $debt);
    }

    public function destroy(FinanceDebt $debt): RedirectResponse
    {
        $cancelled = $debt->payments()->exists();
        $cancelled ? $debt->update(['status' => 'cancelled']) : $debt->delete();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => $cancelled ? 'Debt or receivable cancelled successfully.' : 'Debt or receivable deleted successfully.',
        ]);

        return to_route('admin.finance.debts.index');
    }

    public function schedule(Request $r, FinanceDebt $debt): RedirectResponse
    {
        $v = $r->validate(['due_date' => ['required', 'date'], 'amount' => ['required', 'decimal:0,2', 'gt:0'], 'notes' => ['nullable', 'string']]);
        $debt->schedules()->create($v);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Payment schedule added successfully.']);

        return back();
    }

    public function pay(Request $r, FinanceDebt $debt, FinanceBalanceService $money): RedirectResponse
    {
        $v = $r->validate(['amount' => ['required', 'decimal:0,2', 'gt:0'], 'payment_date' => ['required', 'date'], 'account_id' => ['required', Rule::exists('finance_accounts', 'id')->where('is_active', true)], 'schedule_id' => ['nullable', Rule::exists('finance_debt_schedules', 'id')->where('debt_id', $debt->id)], 'notes' => ['nullable', 'string']]);
        if ($money->toCents($v['amount']) > $money->toCents($debt->remainingAmount())) {
            return back()->withErrors(['amount' => 'Payment cannot exceed the remaining amount.']);
        }DB::transaction(function () use ($v, $debt, $money) {
            $category = FinanceCategory::firstOrCreate(['name' => $debt->type === 'debt' ? 'Debt Payment' : 'Other', 'type' => $debt->type === 'debt' ? 'expense' : 'income'], ['is_active' => true]);
            $tx = FinanceTransaction::create(['type' => $debt->type === 'debt' ? 'expense' : 'income', 'transaction_date' => $v['payment_date'], 'amount' => $v['amount'], 'category_id' => $category->id, 'account_id' => $v['account_id'], 'description' => ($v['notes'] ?? null) ?: ($debt->type === 'debt' ? 'Debt payment: ' : 'Receivable payment: ').$debt->name, 'reference_type' => 'finance_debt', 'reference_id' => $debt->id]);
            FinanceDebtPayment::create(['debt_id' => $debt->id, 'schedule_id' => $v['schedule_id'] ?? null, 'amount' => $v['amount'], 'payment_date' => $v['payment_date'], 'finance_transaction_id' => $tx->id, 'notes' => $v['notes'] ?? null]);
            if (! empty($v['schedule_id'])) {
                $schedule = FinanceDebtSchedule::findOrFail($v['schedule_id']);
                $paidForSchedule = $schedule->payments()->sum('amount');

                if ($money->toCents((string) $paidForSchedule) >= $money->toCents((string) $schedule->amount)) {
                    $schedule->update(['status' => 'paid']);
                }
            }$debt->refresh();
            if ($money->toCents($debt->remainingAmount()) === 0) {
                $debt->update(['status' => 'paid']);
            }
        });

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => $debt->type === 'debt' ? 'Debt payment recorded successfully.' : 'Receivable payment received successfully.',
        ]);

        return back();
    }

    private function validated(Request $r): array
    {
        return $r->validate(['name' => ['required', 'string', 'max:255'], 'type' => ['required', Rule::in(['debt', 'receivable'])], 'party_name' => ['nullable', 'string', 'max:255'], 'original_amount' => ['required', 'decimal:0,2', 'gt:0'], 'start_date' => ['nullable', 'date'], 'notes' => ['nullable', 'string'], 'status' => ['required', Rule::in(['active', 'paid', 'cancelled'])]]);
    }

    private function data(FinanceDebt $d): array
    {
        $next = $d->schedules->where('status', 'upcoming')->sortBy('due_date')->first();

        return [...$d->toArray(), 'paid_amount' => $d->paidAmount(), 'remaining_amount' => $d->remainingAmount(), 'progress' => $d->progress(), 'next_due' => $next?->toArray()];
    }
}
