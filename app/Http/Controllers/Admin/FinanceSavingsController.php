<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FinanceAccount;
use App\Models\FinanceSavingsEntry;
use App\Models\FinanceSavingsGoal;
use App\Models\FinanceSavingsMonthlyTarget;
use App\Models\FinanceTransaction;
use App\Services\FinanceBalanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class FinanceSavingsController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin/finance/savings/Index', ['goals' => FinanceSavingsGoal::latest()->get()->map(fn ($g) => $this->data($g))]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/finance/savings/Form');
    }

    public function store(Request $r): RedirectResponse
    {
        $g = FinanceSavingsGoal::create($this->validated($r));

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Savings goal added successfully.']);

        return to_route('admin.finance.savings.show', $g);
    }

    public function show(FinanceSavingsGoal $saving): Response
    {
        $saving->load(['monthlyTargets' => fn ($q) => $q->orderByDesc('year')->orderByDesc('month'), 'entries' => fn ($q) => $q->with('account:id,name')->latest('entry_date')]);

        return Inertia::render('admin/finance/savings/Show', ['goal' => $this->data($saving), 'accounts' => FinanceAccount::where('is_active', true)->orderBy('name')->get(['id', 'name'])]);
    }

    public function edit(FinanceSavingsGoal $saving): Response
    {
        return Inertia::render('admin/finance/savings/Form', ['goal' => $saving]);
    }

    public function update(Request $r, FinanceSavingsGoal $saving): RedirectResponse
    {
        $saving->update($this->validated($r));

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Savings goal updated successfully.']);

        return to_route('admin.finance.savings.show', $saving);
    }

    public function destroy(FinanceSavingsGoal $saving): RedirectResponse
    {
        DB::transaction(function () use ($saving) {
            $transactionIds = $saving->entries()->pluck('finance_transaction_id');

            $saving->entries()->delete();
            FinanceTransaction::whereIn('id', $transactionIds)->delete();
            $saving->delete();
        });

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Savings goal deleted successfully.',
        ]);

        return to_route('admin.finance.savings.index');
    }

    public function target(Request $r, FinanceSavingsGoal $saving): RedirectResponse
    {
        $v = $r->validate(['year' => ['required', 'integer', 'between:2000,2100'], 'month' => ['required', 'integer', 'between:1,12'], 'target_amount' => ['required', 'decimal:0,2', 'gt:0']]);
        FinanceSavingsMonthlyTarget::updateOrCreate(['savings_goal_id' => $saving->id, 'year' => $v['year'], 'month' => $v['month']], ['target_amount' => $v['target_amount']]);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Monthly target saved successfully.']);

        return back();
    }

    public function entry(Request $r, FinanceSavingsGoal $saving, FinanceBalanceService $money): RedirectResponse
    {
        $v = $r->validate(['type' => ['required', Rule::in(['contribution', 'withdrawal'])], 'amount' => ['required', 'decimal:0,2', 'gt:0'], 'entry_date' => ['required', 'date'], 'account_id' => ['required', Rule::exists('finance_accounts', 'id')->where('is_active', true)], 'notes' => ['nullable', 'string']]);
        if ($v['type'] === 'withdrawal' && $money->toCents($v['amount']) > $money->toCents($saving->savedAmount())) {
            return back()->withErrors(['amount' => 'Withdrawal cannot exceed the saved balance.']);
        }DB::transaction(function () use ($v, $saving) {
            $tx = FinanceTransaction::create(['type' => 'adjustment', 'transaction_date' => $v['entry_date'], 'amount' => $v['amount'], 'account_id' => $v['account_id'], 'adjustment_direction' => $v['type'] === 'contribution' ? 'decrease' : 'increase', 'description' => ($v['notes'] ?? null) ?: ucfirst($v['type']).': '.$saving->name, 'reference_type' => 'finance_savings', 'reference_id' => $saving->id]);
            FinanceSavingsEntry::create(['savings_goal_id' => $saving->id, 'type' => $v['type'], 'amount' => $v['amount'], 'entry_date' => $v['entry_date'], 'account_id' => $v['account_id'], 'finance_transaction_id' => $tx->id, 'notes' => $v['notes'] ?? null]);
            $saving->refresh();
            if ($saving->progress() >= 100) {
                $saving->update(['status' => 'completed']);
            }
        });

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => $v['type'] === 'contribution' ? 'Savings contribution added successfully.' : 'Savings withdrawal recorded successfully.',
        ]);

        return back();
    }

    private function validated(Request $r): array
    {
        return $r->validate(['name' => ['required', 'string', 'max:255'], 'target_amount' => ['required', 'decimal:0,2', 'gt:0'], 'start_date' => ['nullable', 'date'], 'target_date' => ['nullable', 'date', 'after_or_equal:start_date'], 'status' => ['required', Rule::in(['active', 'completed', 'paused', 'cancelled'])], 'notes' => ['nullable', 'string']]);
    }

    private function data(FinanceSavingsGoal $g): array
    {
        $month = $g->monthSummary(now()->year, now()->month);

        return [...$g->toArray(), 'saved_amount' => $g->savedAmount(), 'remaining_amount' => app(FinanceBalanceService::class)->fromCents(max(0, app(FinanceBalanceService::class)->toCents((string) $g->target_amount) - app(FinanceBalanceService::class)->toCents($g->savedAmount()))), 'progress' => $g->progress(), 'current_month' => $month, 'previous_month' => $g->previousMonthShortfall()];
    }
}
