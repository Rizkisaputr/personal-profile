<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FinanceAccount;
use App\Models\FinanceDebt;
use App\Models\FinanceSavingsGoal;
use App\Models\FinanceTransaction;
use App\Services\FinanceBalanceService;
use Inertia\Inertia;
use Inertia\Response;

class FinanceOverviewController extends Controller
{
    public function __invoke(FinanceBalanceService $balances): Response
    {
        $accounts = FinanceAccount::where('is_active', true)->get();
        $monthTransactions = FinanceTransaction::whereBetween('transaction_date', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()])->get();
        $income = $balances->add(...$monthTransactions->where('type', 'income')->pluck('amount')->map(fn ($amount) => (string) $amount)->all());
        $expense = $balances->add(...$monthTransactions->where('type', 'expense')->pluck('amount')->map(fn ($amount) => (string) $amount)->all());

        return Inertia::render('admin/finance/Overview', [
            'summary' => ['total_balance' => $balances->add(...$accounts->map(fn ($account) => $balances->balance($account))->all()), 'income' => $income, 'expense' => $expense, 'net_cash_flow' => $balances->subtract($income, $expense),
                'remaining_debt' => $balances->add(...FinanceDebt::where('type', 'debt')->where('status', 'active')->get()->map(fn ($debt) => $debt->remainingAmount())->all()),
                'receivables' => $balances->add(...FinanceDebt::where('type', 'receivable')->where('status', 'active')->get()->map(fn ($debt) => $debt->remainingAmount())->all()),
                'total_savings' => $balances->add(...FinanceSavingsGoal::whereIn('status', ['active', 'completed', 'paused'])->get()->map(fn ($goal) => $goal->savedAmount())->all()),
            ],
            'recentTransactions' => FinanceTransaction::with(['category:id,name', 'account:id,name', 'fromAccount:id,name', 'toAccount:id,name'])->orderByDesc('transaction_date')->orderByDesc('id')->limit(8)->get(),
        ]);
    }
}
