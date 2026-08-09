<?php

namespace Tests\Feature\Admin;

use App\Models\FinanceAccount;
use App\Models\FinanceBillPayment;
use App\Models\FinanceDebt;
use App\Models\FinanceDebtSchedule;
use App\Models\FinanceRecurringBill;
use App\Models\FinanceSavingsEntry;
use App\Models\FinanceSavingsGoal;
use App\Models\FinanceSavingsMonthlyTarget;
use App\Models\FinanceTransaction;
use App\Models\User;
use App\Services\FinanceBalanceService;
use Database\Seeders\FinanceCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class FinancePersonalModulesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(FinanceCategorySeeder::class);
    }

    public function test_personal_finance_modules_are_private_to_admins(): void
    {
        foreach (['debts', 'savings', 'bills'] as $module) {
            $this->actingAs(User::factory()->create(['is_admin' => false]))
                ->get("/admin/finance/{$module}")->assertForbidden();
        }

        auth()->logout();
        $this->get('/admin/finance/debts')->assertRedirect('/login');
    }

    public function test_admin_can_create_and_update_debt_and_receivable(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->post('/admin/finance/debts', $this->debtPayload('debt'))
            ->assertRedirect();
        $debt = FinanceDebt::firstOrFail();
        $this->actingAs($admin)->put("/admin/finance/debts/{$debt->id}", [
            ...$this->debtPayload('receivable'), 'name' => 'Client invoice',
        ])->assertRedirect();

        $this->assertDatabaseHas('finance_debts', [
            'id' => $debt->id, 'name' => 'Client invoice', 'type' => 'receivable',
        ]);
    }

    public function test_debt_payment_uses_expense_ledger_and_history_derived_progress(): void
    {
        $account = $this->account('1000000.00');
        $debt = FinanceDebt::create($this->debtPayload('debt', '500000.00'));
        $schedule = $debt->schedules()->create([
            'due_date' => today(), 'amount' => '250000.00', 'status' => 'upcoming',
        ]);

        $this->actingAs($this->admin())->post("/admin/finance/debts/{$debt->id}/payments", [
            'amount' => '250000.00', 'payment_date' => today()->toDateString(),
            'account_id' => $account->id, 'schedule_id' => $schedule->id,
        ])->assertRedirect();

        $this->assertDatabaseHas('finance_transactions', ['type' => 'expense', 'amount' => '250000.00']);
        $this->assertDatabaseHas('finance_debt_payments', ['debt_id' => $debt->id, 'schedule_id' => $schedule->id]);
        $this->assertSame('250000.00', $debt->remainingAmount());
        $this->assertSame(50, $debt->progress());
        $this->assertSame('paid', $schedule->refresh()->status);
        $this->assertSame('750000.00', app(FinanceBalanceService::class)->balance($account));

        $this->actingAs($this->admin())->post("/admin/finance/debts/{$debt->id}/payments", [
            'amount' => '250000.01', 'payment_date' => today()->toDateString(), 'account_id' => $account->id,
        ])->assertSessionHasErrors('amount');
    }

    public function test_partial_schedule_payment_does_not_mark_schedule_paid(): void
    {
        $debt = FinanceDebt::create($this->debtPayload('debt', '500000.00'));
        $schedule = $debt->schedules()->create([
            'due_date' => today(), 'amount' => '250000.00', 'status' => 'upcoming',
        ]);
        $this->actingAs($this->admin())->post("/admin/finance/debts/{$debt->id}/payments", [
            'amount' => '100000.00', 'payment_date' => today()->toDateString(),
            'account_id' => $this->account()->id, 'schedule_id' => $schedule->id,
        ])->assertRedirect();

        $this->assertSame('upcoming', $schedule->refresh()->status);
    }

    public function test_receivable_payment_creates_income_and_increases_destination_account(): void
    {
        $account = $this->account('100000.00');
        $receivable = FinanceDebt::create($this->debtPayload('receivable', '300000.00'));
        $this->actingAs($this->admin())->post("/admin/finance/debts/{$receivable->id}/payments", [
            'amount' => '300000.00', 'payment_date' => today()->toDateString(), 'account_id' => $account->id,
        ])->assertRedirect();

        $this->assertDatabaseHas('finance_transactions', ['type' => 'income', 'amount' => '300000.00']);
        $this->assertSame('400000.00', app(FinanceBalanceService::class)->balance($account));
        $this->assertSame('paid', $receivable->refresh()->status);
    }

    public function test_debt_schedule_overdue_and_due_within_helpers_work(): void
    {
        $debt = FinanceDebt::create($this->debtPayload('debt'));
        $overdue = $debt->schedules()->create(['due_date' => today()->subDay(), 'amount' => 1, 'status' => 'upcoming']);
        $dueSoon = $debt->schedules()->create(['due_date' => today()->addDays(5), 'amount' => 1, 'status' => 'upcoming']);

        $this->assertTrue($overdue->is_overdue);
        $this->assertTrue(FinanceDebtSchedule::overdue()->whereKey($overdue)->exists());
        $this->assertTrue(FinanceDebtSchedule::dueWithin(7)->whereKey($dueSoon)->exists());
    }

    public function test_savings_contribution_and_withdrawal_are_non_cashflow_adjustments(): void
    {
        $account = $this->account('1000000.00');
        $goal = FinanceSavingsGoal::create($this->goalPayload());
        $admin = $this->admin();
        $this->actingAs($admin)->post("/admin/finance/savings/{$goal->id}/entries", [
            'type' => 'contribution', 'amount' => '400000.00', 'entry_date' => today()->toDateString(), 'account_id' => $account->id,
        ])->assertRedirect();

        $this->assertSame('400000.00', $goal->savedAmount());
        $this->assertSame('600000.00', app(FinanceBalanceService::class)->balance($account));
        $this->assertSame(0, FinanceTransaction::whereIn('type', ['income', 'expense'])->count());
        $this->assertDatabaseHas('finance_transactions', ['type' => 'adjustment', 'adjustment_direction' => 'decrease']);

        $this->actingAs($admin)->post("/admin/finance/savings/{$goal->id}/entries", [
            'type' => 'withdrawal', 'amount' => '100000.00', 'entry_date' => today()->toDateString(), 'account_id' => $account->id,
        ])->assertRedirect();
        $this->assertSame('300000.00', $goal->savedAmount());
        $this->assertSame('700000.00', app(FinanceBalanceService::class)->balance($account));

        $this->actingAs($admin)->post("/admin/finance/savings/{$goal->id}/entries", [
            'type' => 'withdrawal', 'amount' => '300000.01', 'entry_date' => today()->toDateString(), 'account_id' => $account->id,
        ])->assertSessionHasErrors('amount');
    }

    public function test_admin_can_create_savings_goal_and_monthly_target(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->post('/admin/finance/savings', $this->goalPayload())
            ->assertRedirect();
        $goal = FinanceSavingsGoal::firstOrFail();

        $this->actingAs($admin)->post("/admin/finance/savings/{$goal->id}/targets", [
            'year' => 2026, 'month' => 8, 'target_amount' => '300000.00',
        ])->assertRedirect();

        $this->assertDatabaseHas('finance_savings_monthly_targets', [
            'savings_goal_id' => $goal->id, 'year' => 2026, 'month' => 8, 'target_amount' => '300000.00',
        ]);
    }

    public function test_savings_monthly_targets_are_independent_without_carry_over(): void
    {
        Carbon::setTestNow('2026-08-09');
        $goal = FinanceSavingsGoal::create($this->goalPayload('1000000.00'));
        FinanceSavingsMonthlyTarget::create(['savings_goal_id' => $goal->id, 'year' => 2026, 'month' => 7, 'target_amount' => '500000.00']);
        FinanceSavingsMonthlyTarget::create(['savings_goal_id' => $goal->id, 'year' => 2026, 'month' => 8, 'target_amount' => '300000.00']);
        FinanceSavingsEntry::create([
            'savings_goal_id' => $goal->id, 'type' => 'contribution', 'amount' => '400000.00',
            'entry_date' => '2026-07-10', 'account_id' => $this->account()->id,
            'finance_transaction_id' => $this->adjustment()->id,
        ]);

        $this->assertSame('100000.00', $goal->previousMonthShortfall()['shortfall']);
        $this->assertSame('300000.00', $goal->monthSummary(2026, 8)['target']);
        $this->assertSame(40, $goal->progress());
        Carbon::setTestNow();
    }

    public function test_admin_can_create_bill_and_mark_it_paid(): void
    {
        $admin = $this->admin();
        $account = $this->account('1000000.00');
        $this->actingAs($admin)->post('/admin/finance/bills', $this->billPayload($account->id, 'monthly'))
            ->assertRedirect('/admin/finance/bills');
        $bill = FinanceRecurringBill::firstOrFail();
        $this->actingAs($admin)->post("/admin/finance/bills/{$bill->id}/payments", [
            'amount' => '100000.00', 'paid_at' => '2026-08-09', 'account_id' => $account->id,
        ])->assertRedirect();

        $this->assertSame('2026-09-15', $bill->refresh()->next_due_date->toDateString());
        $this->assertSame(1, FinanceBillPayment::where('bill_id', $bill->id)->count());
        $this->assertDatabaseHas('finance_transactions', ['type' => 'expense', 'amount' => '100000.00']);
        $this->assertSame('900000.00', app(FinanceBalanceService::class)->balance($account));
    }

    public function test_bill_yearly_next_due_and_due_helpers_work(): void
    {
        Carbon::setTestNow('2026-08-09');
        $yearly = FinanceRecurringBill::create($this->billPayload(null, 'yearly'));
        $this->assertSame('2027-08-15', $yearly->nextDateAfterPayment()->toDateString());
        $soon = FinanceRecurringBill::create([...$this->billPayload(), 'name' => 'Soon', 'next_due_date' => today()->addDays(7)]);
        $overdue = FinanceRecurringBill::create([...$this->billPayload(), 'name' => 'Late', 'next_due_date' => today()->subDay()]);
        $inactive = FinanceRecurringBill::create([...$this->billPayload(), 'name' => 'Inactive', 'next_due_date' => today(), 'is_active' => false]);

        $this->assertTrue(FinanceRecurringBill::dueWithin(7)->whereKey($soon)->exists());
        $this->assertTrue(FinanceRecurringBill::overdue()->whereKey($overdue)->exists());
        $this->assertFalse(FinanceRecurringBill::dueWithin(7)->whereKey($inactive)->exists());
        $this->actingAs($this->admin())->post("/admin/finance/bills/{$inactive->id}/payments", [
            'amount' => '100.00', 'paid_at' => today()->toDateString(), 'account_id' => $this->account()->id,
        ])->assertUnprocessable();
        Carbon::setTestNow();
    }

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    private function account(string $opening = '0.00'): FinanceAccount
    {
        return FinanceAccount::create(['name' => fake()->unique()->word(), 'type' => 'bank', 'opening_balance' => $opening, 'currency' => 'IDR', 'is_active' => true]);
    }

    private function debtPayload(string $type, string $amount = '1000000.00'): array
    {
        return ['name' => 'Personal obligation', 'type' => $type, 'original_amount' => $amount, 'status' => 'active'];
    }

    private function goalPayload(string $target = '1000000.00'): array
    {
        return ['name' => 'Emergency fund', 'target_amount' => $target, 'status' => 'active'];
    }

    private function billPayload(?int $accountId = null, string $frequency = 'monthly'): array
    {
        return ['name' => 'Internet', 'amount' => '100000.00', 'frequency' => $frequency, 'due_day' => 15,
            'next_due_date' => '2026-08-15', 'default_account_id' => $accountId, 'is_active' => true];
    }

    private function adjustment(): FinanceTransaction
    {
        return FinanceTransaction::create(['type' => 'adjustment', 'transaction_date' => today(), 'amount' => '400000.00',
            'account_id' => FinanceAccount::latest('id')->firstOrFail()->id, 'adjustment_direction' => 'decrease']);
    }
}
