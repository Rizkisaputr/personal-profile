<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FinanceAccount;
use App\Models\FinanceDebt;
use App\Models\FinanceSavingsGoal;
use App\Models\FinanceTransaction;
use App\Models\Project;
use App\Services\FinanceBalanceService;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(FinanceBalanceService $money): Response
    {
        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();
        $activeAccounts = FinanceAccount::query()->where('is_active', true)->get();
        $monthTotals = FinanceTransaction::query()
            ->whereBetween('transaction_date', [$monthStart, $monthEnd])
            ->whereIn('type', ['income', 'expense'])
            ->selectRaw('type, SUM(amount) as total')
            ->groupBy('type')
            ->pluck('total', 'type');

        $income = (string) ($monthTotals['income'] ?? '0.00');
        $expense = (string) ($monthTotals['expense'] ?? '0.00');
        $activeSavings = FinanceSavingsGoal::query()
            ->where('status', 'active')
            ->with(['entries:id,savings_goal_id,type,amount,entry_date', 'monthlyTargets:id,savings_goal_id,year,month,target_amount'])
            ->orderBy('target_date')
            ->get();
        $activeDebts = FinanceDebt::query()
            ->where('type', 'debt')
            ->where('status', 'active')
            ->with('payments:id,debt_id,amount')
            ->get();

        $savingsGoals = $activeSavings->map(fn (FinanceSavingsGoal $goal) => $this->savingsData($goal, $money))->values();
        $debts = $activeDebts->map(fn (FinanceDebt $debt) => $this->debtData($debt, $money))->values();
        $savingsTotal = $money->add(...$savingsGoals->pluck('saved')->all());
        $remainingDebt = $money->add(...$debts->pluck('remaining')->all());

        return Inertia::render('admin/Index', [
            'financialSummary' => [
                'total_balance' => $money->totalBalance($activeAccounts),
                'income' => $income,
                'expense' => $expense,
                'net_cash_flow' => $money->subtract($income, $expense),
                'remaining_debt' => $remainingDebt,
                'savings' => $savingsTotal,
            ],
            'financeTrend' => $this->financeTrend(),
            'expenseByCategory' => $this->expenseByCategory($monthStart, $monthEnd, $money),
            'savingsGoals' => $savingsGoals->take(4),
            'debtSummary' => ['total' => $remainingDebt, 'items' => $debts->take(4)],
            'projectSummary' => Project::workStatusCounts(),
            'recentProjects' => Project::query()->with('technologies:id,name')->latest('updated_at')->latest('id')->limit(5)
                ->get(['id', 'title', 'slug', 'work_status', 'updated_at'])
                ->map(fn (Project $project) => [
                    'id' => $project->id,
                    'title' => $project->title,
                    'status' => $project->work_status,
                    'technologies' => $project->technologies->pluck('name')->take(3)->values(),
                    'updated_at' => $project->updated_at?->toIso8601String(),
                ]),
            'recentTransactions' => FinanceTransaction::query()->with(['category:id,name', 'account:id,name', 'fromAccount:id,name', 'toAccount:id,name'])
                ->latest('transaction_date')->latest('id')->limit(6)
                ->get(['id', 'type', 'transaction_date', 'description', 'amount', 'category_id', 'account_id', 'from_account_id', 'to_account_id']),
        ]);
    }

    private function financeTrend(): array
    {
        $start = now()->subMonthsNoOverflow(5)->startOfMonth();
        $dateExpression = FinanceTransaction::query()->getConnection()->getDriverName() === 'sqlite'
            ? "strftime('%Y-%m', transaction_date)"
            : "DATE_FORMAT(transaction_date, '%Y-%m')";
        $totals = FinanceTransaction::query()
            ->whereDate('transaction_date', '>=', $start)
            ->whereIn('type', ['income', 'expense'])
            ->selectRaw("{$dateExpression} as period, type, SUM(amount) as total")
            ->groupBy('period', 'type')
            ->get()
            ->keyBy(fn ($row) => "{$row->period}:{$row->type}");

        return collect(range(5, 0))->map(function (int $monthsAgo) use ($totals) {
            $month = now()->subMonthsNoOverflow($monthsAgo);
            $period = $month->format('Y-m');

            return [
                'period' => $period,
                'label' => $month->translatedFormat('M'),
                'income' => (string) ($totals->get("{$period}:income")?->total ?? '0.00'),
                'expense' => (string) ($totals->get("{$period}:expense")?->total ?? '0.00'),
            ];
        })->all();
    }

    private function expenseByCategory($start, $end, FinanceBalanceService $money): array
    {
        $rows = FinanceTransaction::query()
            ->leftJoin('finance_categories', 'finance_categories.id', '=', 'finance_transactions.category_id')
            ->where('finance_transactions.type', 'expense')
            ->whereBetween('finance_transactions.transaction_date', [$start, $end])
            ->selectRaw("COALESCE(finance_categories.name, 'Uncategorized') as category, SUM(finance_transactions.amount) as total")
            ->groupBy('finance_categories.id', 'finance_categories.name')
            ->orderByDesc('total')
            ->get();

        if ($rows->count() <= 6) {
            return $rows->map(fn ($row) => ['category' => $row->category, 'amount' => (string) $row->total])->all();
        }

        $top = $rows->take(5)->map(fn ($row) => ['category' => $row->category, 'amount' => (string) $row->total]);
        $other = $money->add(...$rows->skip(5)->pluck('total')->map(fn ($value) => (string) $value)->all());

        return $top->push(['category' => 'Other', 'amount' => $other])->all();
    }

    private function savingsData(FinanceSavingsGoal $goal, FinanceBalanceService $money): array
    {
        $savedCents = $goal->entries->sum(fn ($entry) => ($entry->type === 'contribution' ? 1 : -1) * $money->toCents((string) $entry->amount));
        $targetCents = $money->toCents((string) $goal->target_amount);

        return [
            'id' => $goal->id,
            'name' => $goal->name,
            'saved' => $money->fromCents($savedCents),
            'target' => (string) $goal->target_amount,
            'progress' => $targetCents > 0 ? max(0, min(100, (int) intdiv($savedCents * 100, $targetCents))) : 0,
        ];
    }

    private function debtData(FinanceDebt $debt, FinanceBalanceService $money): array
    {
        $paidCents = $debt->payments->sum(fn ($payment) => $money->toCents((string) $payment->amount));
        $originalCents = $money->toCents((string) $debt->original_amount);

        return [
            'id' => $debt->id,
            'name' => $debt->name,
            'remaining' => $money->fromCents(max(0, $originalCents - $paidCents)),
            'progress' => $originalCents > 0 ? min(100, (int) intdiv($paidCents * 100, $originalCents)) : 0,
        ];
    }
}
