<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\FinanceAccount;
use App\Models\FinanceDebt;
use App\Models\FinanceDebtSchedule;
use App\Models\FinanceRecurringBill;
use App\Models\FinanceSavingsGoal;
use App\Models\FinanceTransaction;
use App\Models\Project;
use App\Models\Subscription;
use App\Services\FinanceBalanceService;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(FinanceBalanceService $money): Response
    {
        $monthTransactions = FinanceTransaction::query()
            ->whereBetween('transaction_date', [now()->startOfMonth(), now()->endOfMonth()])
            ->get();
        $income = $this->sumTransactions($monthTransactions->where('type', 'income'), $money);
        $expense = $this->sumTransactions($monthTransactions->where('type', 'expense'), $money);
        $activeSavings = FinanceSavingsGoal::where('status', 'active')->get();
        $activeDebts = FinanceDebt::where('type', 'debt')->where('status', 'active')->get();

        return Inertia::render('admin/Index', [
            'financeSummary' => [
                'total_balance' => $money->add(...FinanceAccount::where('is_active', true)->get()->map(fn (FinanceAccount $account) => $money->balance($account))->all()),
                'income' => $income,
                'expense' => $expense,
                'savings' => $money->add(...$activeSavings->map(fn (FinanceSavingsGoal $goal) => $goal->savedAmount())->all()),
                'remaining_debt' => $money->add(...$activeDebts->map(fn (FinanceDebt $debt) => $debt->remainingAmount())->all()),
            ],
            'cashFlow' => $this->cashFlow($money),
            'expenseBreakdown' => $this->expenseBreakdown($monthTransactions, $money),
            'savingsGoals' => $activeSavings->map(fn (FinanceSavingsGoal $goal) => $this->savingsData($goal, $money))->values(),
            'alerts' => $this->alerts($money),
            'portfolioSummary' => Project::workStatusCounts(),
            'recentTransactions' => FinanceTransaction::with(['account:id,name', 'fromAccount:id,name', 'toAccount:id,name'])
                ->latest('transaction_date')->latest('id')->limit(8)->get(),
            'upcomingPayments' => $this->upcomingPayments(),
        ]);
    }

    private function cashFlow(FinanceBalanceService $money): array
    {
        return collect(range(5, 0))->map(function (int $monthsAgo) use ($money) {
            $month = now()->subMonthsNoOverflow($monthsAgo);
            $transactions = FinanceTransaction::whereBetween('transaction_date', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])->get();

            return [
                'label' => $month->format('M'),
                'income' => $this->sumTransactions($transactions->where('type', 'income'), $money),
                'expense' => $this->sumTransactions($transactions->where('type', 'expense'), $money),
            ];
        })->all();
    }

    private function expenseBreakdown(Collection $transactions, FinanceBalanceService $money): array
    {
        return $transactions->where('type', 'expense')->groupBy(fn (FinanceTransaction $transaction) => $transaction->category?->name ?? 'Uncategorized')
            ->map(fn (Collection $items, string $category) => ['category' => $category, 'amount' => $this->sumTransactions($items, $money)])
            ->sortByDesc(fn (array $item) => $money->toCents($item['amount']))->values()->all();
    }

    private function savingsData(FinanceSavingsGoal $goal, FinanceBalanceService $money): array
    {
        $current = $goal->monthSummary(now()->year, now()->month);

        return [
            'id' => $goal->id, 'name' => $goal->name, 'saved' => $goal->savedAmount(), 'target' => (string) $goal->target_amount,
            'progress' => $goal->progress(), 'month' => $current, 'previous_month' => $goal->previousMonthShortfall(),
            'remaining' => $money->fromCents(max(0, $money->toCents((string) $goal->target_amount) - $money->toCents($goal->savedAmount()))),
        ];
    }

    private function alerts(FinanceBalanceService $money): array
    {
        $alerts = collect();
        FinanceDebtSchedule::with('debt')->whereHas('debt', fn ($query) => $query->where('status', 'active'))->where('status', 'upcoming')->whereDate('due_date', '<=', today()->addDays(30))->get()
            ->each(function (FinanceDebtSchedule $schedule) use ($alerts, $money) {
                $paid = $schedule->payments()->sum('amount');
                $amount = $money->fromCents(max(0, $money->toCents((string) $schedule->amount) - $money->toCents((string) $paid)));
                $alerts->push($this->alert('debt', $schedule->debt->name, $amount, $schedule->due_date, route('admin.finance.debts.show', $schedule->debt)));
            });
        FinanceRecurringBill::where('is_active', true)->whereDate('next_due_date', '<=', today()->addDays(30))->get()
            ->each(fn (FinanceRecurringBill $bill) => $alerts->push($this->alert('bill', $bill->name, (string) $bill->amount, $bill->next_due_date, route('admin.finance.bills.show', $bill))));
        Subscription::where('is_active', true)->whereDate('next_renewal_date', '<=', today()->addDays(30))->get()
            ->each(fn (Subscription $subscription) => $alerts->push($this->alert('subscription', $subscription->service_name, (string) $subscription->cost, $subscription->next_renewal_date, route('admin.subscriptions.show', $subscription))));
        Document::where('is_active', true)->whereNotNull('expiry_date')->whereDate('expiry_date', '<=', today()->addDays(30))->get()
            ->each(fn (Document $document) => $alerts->push($this->alert('document', $document->name, null, $document->expiry_date, route('admin.documents.edit', $document))));

        return $alerts->sortBy(fn (array $item) => (['urgent' => 0, 'warning' => 1, 'info' => 2][$item['severity']] * 100000) + $item['days'])->values()->all();
    }

    private function alert(string $type, string $title, ?string $amount, CarbonInterface $dueDate, string $href): array
    {
        $days = today()->diffInDays($dueDate, false);

        return ['type' => $type, 'title' => $title, 'amount' => $amount, 'due_date' => $dueDate->toDateString(), 'days' => $days,
            'severity' => $days <= 0 ? 'urgent' : ($days <= 7 ? 'warning' : 'info'), 'href' => $href];
    }

    private function upcomingPayments(): array
    {
        return collect()
            ->merge(FinanceDebtSchedule::with('debt')->whereHas('debt', fn ($query) => $query->where('status', 'active'))->where('status', 'upcoming')->get()->map(fn ($item) => ['id' => "debt-{$item->id}", 'type' => 'Debt', 'name' => $item->debt->name, 'amount' => (string) $item->amount, 'due_date' => $item->due_date->toDateString(), 'href' => route('admin.finance.debts.show', $item->debt)]))
            ->merge(FinanceRecurringBill::where('is_active', true)->get()->map(fn ($item) => ['id' => "bill-{$item->id}", 'type' => 'Bill', 'name' => $item->name, 'amount' => (string) $item->amount, 'due_date' => $item->next_due_date->toDateString(), 'href' => route('admin.finance.bills.show', $item)]))
            ->merge(Subscription::where('is_active', true)->get()->map(fn ($item) => ['id' => "subscription-{$item->id}", 'type' => 'Subscription', 'name' => $item->service_name, 'amount' => (string) $item->cost, 'due_date' => $item->next_renewal_date->toDateString(), 'href' => route('admin.subscriptions.show', $item)]))
            ->sortBy('due_date')->take(10)->values()->all();
    }

    private function sumTransactions(Collection $transactions, FinanceBalanceService $money): string
    {
        return $money->add(...$transactions->pluck('amount')->map(fn ($amount) => (string) $amount)->all());
    }
}
