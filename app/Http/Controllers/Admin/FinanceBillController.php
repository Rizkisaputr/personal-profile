<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FinanceAccount;
use App\Models\FinanceBillPayment;
use App\Models\FinanceCategory;
use App\Models\FinanceRecurringBill;
use App\Models\FinanceTransaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class FinanceBillController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin/finance/bills/Index', ['bills' => FinanceRecurringBill::with(['category:id,name', 'defaultAccount:id,name'])->orderBy('next_due_date')->get()]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/finance/bills/Form', $this->options());
    }

    public function store(Request $r): RedirectResponse
    {
        FinanceRecurringBill::create($this->validated($r));

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Recurring bill added successfully.']);

        return to_route('admin.finance.bills.index');
    }

    public function edit(FinanceRecurringBill $bill): Response
    {
        return Inertia::render('admin/finance/bills/Form', $this->options() + ['bill' => $bill]);
    }

    public function update(Request $r, FinanceRecurringBill $bill): RedirectResponse
    {
        $bill->update($this->validated($r));

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Recurring bill updated successfully.']);

        return to_route('admin.finance.bills.index');
    }

    public function destroy(FinanceRecurringBill $bill): RedirectResponse
    {
        $deactivated = $bill->payments()->exists();
        $deactivated ? $bill->update(['is_active' => false]) : $bill->delete();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => $deactivated ? 'Recurring bill deactivated successfully.' : 'Recurring bill deleted successfully.',
        ]);

        return back();
    }

    public function show(FinanceRecurringBill $bill): Response
    {
        return Inertia::render('admin/finance/bills/Show', ['bill' => [...$bill->load('payments')->toArray()], 'accounts' => FinanceAccount::where('is_active', true)->orderBy('name')->get(['id', 'name'])]);
    }

    public function pay(Request $r, FinanceRecurringBill $bill): RedirectResponse
    {
        abort_unless($bill->is_active, 422);
        $v = $r->validate(['amount' => ['required', 'decimal:0,2', 'gt:0'], 'paid_at' => ['required', 'date'], 'account_id' => ['required', Rule::exists('finance_accounts', 'id')->where('is_active', true)], 'notes' => ['nullable', 'string']]);
        DB::transaction(function () use ($v, $bill) {
            $category = $bill->category ?: FinanceCategory::firstOrCreate(['name' => 'Bills', 'type' => 'expense'], ['is_active' => true]);
            $tx = FinanceTransaction::create(['type' => 'expense', 'transaction_date' => $v['paid_at'], 'amount' => $v['amount'], 'category_id' => $category->id, 'account_id' => $v['account_id'], 'description' => ($v['notes'] ?? null) ?: 'Bill payment: '.$bill->name, 'reference_type' => 'finance_bill', 'reference_id' => $bill->id]);
            FinanceBillPayment::create(['bill_id' => $bill->id, 'amount' => $v['amount'], 'paid_at' => $v['paid_at'], 'finance_transaction_id' => $tx->id, 'notes' => $v['notes'] ?? null]);
            $bill->update(['next_due_date' => $bill->nextDateAfterPayment()->toDateString()]);
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Bill marked as paid successfully.']);

        return back();
    }

    private function options(): array
    {
        return ['categories' => FinanceCategory::where('type', 'expense')->where('is_active', true)->orderBy('sort_order')->get(['id', 'name']), 'accounts' => FinanceAccount::where('is_active', true)->orderBy('name')->get(['id', 'name'])];
    }

    private function validated(Request $r): array
    {
        return $r->validate(['name' => ['required', 'string', 'max:255'], 'category_id' => ['nullable', 'exists:finance_categories,id'], 'amount' => ['required', 'decimal:0,2', 'gt:0'], 'frequency' => ['required', Rule::in(['monthly', 'yearly', 'custom'])], 'due_day' => ['nullable', 'integer', 'between:1,31'], 'custom_interval_days' => ['nullable', 'required_if:frequency,custom', 'integer', 'min:1'], 'next_due_date' => ['required', 'date'], 'default_account_id' => ['nullable', 'exists:finance_accounts,id'], 'is_active' => ['required', 'boolean'], 'notes' => ['nullable', 'string']]);
    }
}
