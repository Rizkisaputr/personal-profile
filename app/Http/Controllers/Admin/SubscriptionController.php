<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FinanceAccount;
use App\Models\FinanceCategory;
use App\Models\FinanceTransaction;
use App\Models\Subscription;
use App\Models\SubscriptionPayment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class SubscriptionController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin/subscriptions/Index', ['subscriptions' => Subscription::with('paymentAccount:id,name')->orderBy('next_renewal_date')->get()]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/subscriptions/Form', $this->options());
    }

    public function edit(Subscription $subscription): Response
    {
        return Inertia::render('admin/subscriptions/Form', $this->options() + ['subscription' => $subscription]);
    }

    public function show(Subscription $subscription): Response
    {
        return Inertia::render('admin/subscriptions/Show', ['subscription' => $subscription->load('payments'), 'accounts' => $this->accounts()]);
    }

    public function store(Request $request): RedirectResponse
    {
        Subscription::create($this->validated($request));

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Subscription added successfully.']);

        return to_route('admin.subscriptions.index');
    }

    public function update(Request $request, Subscription $subscription): RedirectResponse
    {
        $subscription->update($this->validated($request));

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Subscription updated successfully.']);

        return to_route('admin.subscriptions.index');
    }

    public function destroy(Subscription $subscription): RedirectResponse
    {
        $deactivated = $subscription->payments()->exists();
        $deactivated ? $subscription->update(['is_active' => false]) : $subscription->delete();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => $deactivated ? 'Subscription deactivated successfully.' : 'Subscription deleted successfully.',
        ]);

        return back();
    }

    public function pay(Request $request, Subscription $subscription): RedirectResponse
    {
        abort_unless($subscription->is_active, 422);
        $data = $request->validate([
            'amount' => ['required', 'decimal:0,2', 'gt:0'], 'paid_at' => ['required', 'date'],
            'account_id' => ['required', Rule::exists('finance_accounts', 'id')->where('is_active', true)], 'notes' => ['nullable', 'string'],
        ]);
        DB::transaction(function () use ($data, $subscription) {
            $category = FinanceCategory::firstOrCreate(['name' => 'Subscription', 'type' => 'expense'], ['is_active' => true]);
            $transaction = FinanceTransaction::create([
                'type' => 'expense', 'transaction_date' => $data['paid_at'], 'amount' => $data['amount'],
                'category_id' => $category->id, 'account_id' => $data['account_id'],
                'description' => ($data['notes'] ?? null) ?: 'Subscription payment: '.$subscription->service_name,
                'reference_type' => 'subscription', 'reference_id' => $subscription->id,
            ]);
            SubscriptionPayment::create([
                'subscription_id' => $subscription->id, 'amount' => $data['amount'], 'paid_at' => $data['paid_at'],
                'finance_transaction_id' => $transaction->id, 'notes' => $data['notes'] ?? null,
            ]);
            $subscription->update(['next_renewal_date' => $subscription->nextDateAfterPayment()->toDateString()]);
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Subscription payment recorded successfully.']);

        return back();
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'service_name' => ['required', 'string', 'max:255'], 'cost' => ['required', 'decimal:0,2', 'gt:0'],
            'billing_cycle' => ['required', Rule::in(['monthly', 'yearly', 'custom'])],
            'custom_interval_days' => ['nullable', 'required_if:billing_cycle,custom', 'integer', 'min:1'],
            'start_date' => ['nullable', 'date'], 'next_renewal_date' => ['required', 'date'],
            'payment_account_id' => ['nullable', 'exists:finance_accounts,id'], 'is_active' => ['required', 'boolean'], 'notes' => ['nullable', 'string'],
        ]);
    }

    private function options(): array
    {
        return ['accounts' => $this->accounts()];
    }

    private function accounts()
    {
        return FinanceAccount::where('is_active', true)->orderBy('name')->get(['id', 'name']);
    }
}
