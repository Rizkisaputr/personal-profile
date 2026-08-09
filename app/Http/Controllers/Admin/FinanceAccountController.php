<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdjustFinanceBalanceRequest;
use App\Http\Requests\Admin\StoreFinanceAccountRequest;
use App\Http\Requests\Admin\UpdateFinanceAccountRequest;
use App\Models\FinanceAccount;
use App\Models\FinanceTransaction;
use App\Services\FinanceBalanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class FinanceAccountController extends Controller
{
    public function index(FinanceBalanceService $balances): Response
    {
        return Inertia::render('admin/finance/accounts/Index', ['accounts' => FinanceAccount::orderByDesc('is_active')->orderBy('name')->get()->map(fn ($account) => [...$account->toArray(), 'current_balance' => $balances->balance($account), 'has_transactions' => $account->hasTransactions()])]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/finance/accounts/Create');
    }

    public function store(StoreFinanceAccountRequest $request): RedirectResponse
    {
        FinanceAccount::create($request->validated());
        Inertia::flash('toast', ['type' => 'success', 'message' => 'Finance account created.']);

        return to_route('admin.finance.accounts.index');
    }

    public function edit(FinanceAccount $account): Response
    {
        return Inertia::render('admin/finance/accounts/Edit', ['account' => $account]);
    }

    public function update(UpdateFinanceAccountRequest $request, FinanceAccount $account): RedirectResponse
    {
        $account->update($request->validated());
        Inertia::flash('toast', ['type' => 'success', 'message' => 'Finance account updated.']);

        return to_route('admin.finance.accounts.index');
    }

    public function destroy(FinanceAccount $account): RedirectResponse
    {
        if ($account->hasTransactions()) {
            $account->update(['is_active' => false]);
            Inertia::flash('toast', ['type' => 'success', 'message' => 'Account deactivated because it has transaction history.']);
        } else {
            $account->delete();
            Inertia::flash('toast', ['type' => 'success', 'message' => 'Finance account deleted.']);
        }

        return to_route('admin.finance.accounts.index');
    }

    public function adjust(FinanceAccount $account, FinanceBalanceService $balances): Response
    {
        return Inertia::render('admin/finance/accounts/Adjust', ['account' => [...$account->toArray(), 'current_balance' => $balances->balance($account)]]);
    }

    public function storeAdjustment(AdjustFinanceBalanceRequest $request, FinanceAccount $account, FinanceBalanceService $balances): RedirectResponse
    {
        $difference = $balances->subtract($request->validated('actual_balance'), $balances->balance($account));
        if ($balances->toCents($difference) === 0) {
            return back()->withErrors(['actual_balance' => 'The actual balance already matches the system balance.']);
        }

        DB::transaction(fn () => FinanceTransaction::create([
            'type' => 'adjustment', 'transaction_date' => $request->validated('transaction_date'),
            'amount' => ltrim($difference, '-'), 'account_id' => $account->id,
            'adjustment_direction' => str_starts_with($difference, '-') ? 'decrease' : 'increase',
            'description' => $request->validated('description'),
        ]));
        Inertia::flash('toast', ['type' => 'success', 'message' => 'Account balance adjusted.']);

        return to_route('admin.finance.accounts.index');
    }
}
