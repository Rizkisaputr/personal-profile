<?php

namespace Tests\Feature\Admin;

use App\Models\Document;
use App\Models\FinanceAccount;
use App\Models\FinanceCategory;
use App\Models\FinanceDebt;
use App\Models\FinanceRecurringBill;
use App\Models\FinanceSavingsEntry;
use App\Models\FinanceSavingsGoal;
use App\Models\FinanceSavingsMonthlyTarget;
use App\Models\FinanceTransaction;
use App\Models\Project;
use App\Models\Subscription;
use App\Models\User;
use Database\Seeders\FinanceCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PersonalDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-08-09 10:00:00');
        $this->seed(FinanceCategorySeeder::class);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_dashboard_finance_summary_and_cash_flow_exclude_transfers(): void
    {
        $main = $this->account('1000000.00');
        $cash = $this->account('100000.00');
        $incomeCategory = FinanceCategory::where('type', 'income')->firstOrFail();
        $expenseCategory = FinanceCategory::where('type', 'expense')->firstOrFail();
        $this->transaction('income', '300000.00', $main, $incomeCategory);
        $this->transaction('expense', '125000.00', $main, $expenseCategory);
        FinanceTransaction::create(['type' => 'transfer', 'transaction_date' => today(), 'amount' => '50000.00', 'from_account_id' => $main->id, 'to_account_id' => $cash->id]);

        $this->actingAs($this->admin())->get('/admin')->assertInertia(fn (Assert $page) => $page
            ->where('financeSummary.total_balance', '1275000.00')
            ->where('financeSummary.income', '300000.00')
            ->where('financeSummary.expense', '125000.00')
            ->where('cashFlow.5.income', '300000.00')
            ->where('cashFlow.5.expense', '125000.00'));
    }

    public function test_dashboard_exposes_savings_progress_and_previous_shortfall_without_carry_over(): void
    {
        $account = $this->account();
        $goal = FinanceSavingsGoal::create(['name' => 'Dana Darurat', 'target_amount' => '1000000.00', 'status' => 'active']);
        FinanceSavingsMonthlyTarget::create(['savings_goal_id' => $goal->id, 'year' => 2026, 'month' => 7, 'target_amount' => '500000.00']);
        FinanceSavingsMonthlyTarget::create(['savings_goal_id' => $goal->id, 'year' => 2026, 'month' => 8, 'target_amount' => '300000.00']);
        $this->savingEntry($goal, $account, '400000.00', '2026-07-10');
        $this->savingEntry($goal, $account, '150000.00', '2026-08-05');

        $this->actingAs($this->admin())->get('/admin')->assertInertia(fn (Assert $page) => $page
            ->where('financeSummary.savings', '550000.00')
            ->where('savingsGoals.0.progress', 55)
            ->where('savingsGoals.0.month.target', '300000.00')
            ->where('savingsGoals.0.month.actual', '150000.00')
            ->where('savingsGoals.0.previous_month.shortfall', '100000.00'));
    }

    public function test_dashboard_builds_dynamic_due_and_expiry_alerts(): void
    {
        $debt = FinanceDebt::create(['name' => 'Shopee PayLater', 'type' => 'debt', 'original_amount' => '750000.00', 'status' => 'active']);
        $debt->schedules()->create(['due_date' => today()->addDays(7), 'amount' => '750000.00', 'status' => 'upcoming']);
        $overdue = FinanceDebt::create(['name' => 'Overdue Loan', 'type' => 'debt', 'original_amount' => '100000.00', 'status' => 'active']);
        $overdue->schedules()->create(['due_date' => today()->subDays(2), 'amount' => '100000.00', 'status' => 'upcoming']);
        $paid = FinanceDebt::create(['name' => 'Paid Loan', 'type' => 'debt', 'original_amount' => '100000.00', 'status' => 'paid']);
        $paid->schedules()->create(['due_date' => today(), 'amount' => '100000.00', 'status' => 'upcoming']);
        FinanceRecurringBill::create(['name' => 'Internet', 'amount' => '300000.00', 'frequency' => 'monthly', 'next_due_date' => today()->addDays(12), 'is_active' => true]);
        Subscription::create(['service_name' => 'AWS', 'cost' => '320000.00', 'billing_cycle' => 'monthly', 'next_renewal_date' => today()->addDays(3), 'is_active' => true]);
        Document::create(['name' => 'SIM A', 'category' => 'Identity', 'expiry_date' => today()->addDays(24), 'is_active' => true]);

        $this->actingAs($this->admin())->get('/admin')->assertInertia(fn (Assert $page) => $page
            ->has('alerts', 5)
            ->where('alerts.0.title', 'Overdue Loan')->where('alerts.0.severity', 'urgent')
            ->where('alerts.1.title', 'AWS')->where('alerts.1.severity', 'warning')
            ->where('alerts.2.title', 'Shopee PayLater')->where('alerts.2.severity', 'warning')
            ->where('alerts.3.title', 'Internet')->where('alerts.3.severity', 'info')
            ->where('alerts.4.title', 'SIM A')->where('alerts.4.severity', 'info'));
    }

    public function test_dashboard_project_counts_use_work_status(): void
    {
        foreach (['waiting', 'running', 'on_hold', 'done', 'done'] as $index => $status) {
            Project::create(['title' => "Project {$index}", 'slug' => "project-{$index}", 'work_status' => $status, 'is_published' => false, 'is_featured' => false, 'sort_order' => $index]);
        }

        $this->actingAs($this->admin())->get('/admin')->assertInertia(fn (Assert $page) => $page
            ->where('portfolioSummary', ['total' => 5, 'waiting' => 1, 'running' => 1, 'on_hold' => 1, 'done' => 2]));
    }

    public function test_non_admin_cannot_access_personal_control_center(): void
    {
        $this->get('/admin')->assertRedirect('/login');
        $this->actingAs(User::factory()->create(['is_admin' => false]))->get('/admin')->assertForbidden();
    }

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    private function account(string $opening = '0.00'): FinanceAccount
    {
        return FinanceAccount::create(['name' => fake()->unique()->word(), 'type' => 'bank', 'opening_balance' => $opening, 'currency' => 'IDR', 'is_active' => true]);
    }

    private function transaction(string $type, string $amount, FinanceAccount $account, FinanceCategory $category): FinanceTransaction
    {
        return FinanceTransaction::create(['type' => $type, 'transaction_date' => today(), 'amount' => $amount, 'account_id' => $account->id, 'category_id' => $category->id]);
    }

    private function savingEntry(FinanceSavingsGoal $goal, FinanceAccount $account, string $amount, string $date): void
    {
        $transaction = FinanceTransaction::create(['type' => 'adjustment', 'transaction_date' => $date, 'amount' => $amount, 'account_id' => $account->id, 'adjustment_direction' => 'decrease']);
        FinanceSavingsEntry::create(['savings_goal_id' => $goal->id, 'type' => 'contribution', 'amount' => $amount, 'entry_date' => $date, 'account_id' => $account->id, 'finance_transaction_id' => $transaction->id]);
    }
}
