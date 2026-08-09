<?php

namespace Tests\Feature\Admin;

use App\Models\FinanceAccount;
use App\Models\FinanceCategory;
use App\Models\FinanceTransaction;
use App\Models\User;
use App\Services\FinanceBalanceService;
use Database\Seeders\FinanceCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class FinanceManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(FinanceCategorySeeder::class);
    }

    public function test_finance_pages_are_private_to_admins(): void
    {
        $this->get('/admin/finance')->assertRedirect('/login');
        $this->actingAs(User::factory()->create(['is_admin' => false]))->get('/admin/finance')->assertForbidden();
    }

    public function test_admin_can_create_update_and_delete_account_without_history(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin)->post('/admin/finance/accounts', [
            'name' => 'BCA', 'type' => 'bank', 'opening_balance' => '1000000.00', 'currency' => 'IDR', 'is_active' => true,
        ])->assertRedirect('/admin/finance/accounts');

        $account = FinanceAccount::firstOrFail();
        $this->assertSame('1000000.00', $account->opening_balance);
        $this->actingAs($admin)->put("/admin/finance/accounts/{$account->id}", [
            'name' => 'BCA Primary', 'type' => 'bank', 'opening_balance' => '1250000.00', 'currency' => 'IDR', 'is_active' => true,
        ])->assertRedirect('/admin/finance/accounts');
        $this->assertDatabaseHas('finance_accounts', ['id' => $account->id, 'name' => 'BCA Primary']);

        $this->actingAs($admin)->delete("/admin/finance/accounts/{$account->id}")->assertRedirect('/admin/finance/accounts');
        $this->assertDatabaseMissing('finance_accounts', ['id' => $account->id]);
    }

    public function test_income_and_expense_change_account_balance(): void
    {
        $account = $this->account('Cash', '100000.00');
        $income = FinanceCategory::where('type', 'income')->firstOrFail();
        $expense = FinanceCategory::where('type', 'expense')->firstOrFail();
        FinanceTransaction::create(['type' => 'income', 'transaction_date' => today(), 'amount' => '50000.00', 'account_id' => $account->id, 'category_id' => $income->id]);
        FinanceTransaction::create(['type' => 'expense', 'transaction_date' => today(), 'amount' => '20000.00', 'account_id' => $account->id, 'category_id' => $expense->id]);

        $this->assertSame('130000.00', app(FinanceBalanceService::class)->balance($account));
    }

    public function test_transfer_moves_balance_without_affecting_income_or_expense(): void
    {
        $source = $this->account('BCA', '1000000.00');
        $destination = $this->account('Cash', '100000.00');
        FinanceTransaction::create(['type' => 'transfer', 'transaction_date' => today(), 'amount' => '250000.00', 'from_account_id' => $source->id, 'to_account_id' => $destination->id]);

        $balances = app(FinanceBalanceService::class);
        $this->assertSame('750000.00', $balances->balance($source));
        $this->assertSame('350000.00', $balances->balance($destination));
        $this->assertSame(0, FinanceTransaction::whereIn('type', ['income', 'expense'])->count());
    }

    public function test_adjustments_increase_and_decrease_balance(): void
    {
        $account = $this->account('Cash', '100000.00');
        FinanceTransaction::create(['type' => 'adjustment', 'transaction_date' => today(), 'amount' => '25000.00', 'account_id' => $account->id, 'adjustment_direction' => 'increase']);
        FinanceTransaction::create(['type' => 'adjustment', 'transaction_date' => today(), 'amount' => '10000.00', 'account_id' => $account->id, 'adjustment_direction' => 'decrease']);
        $this->assertSame('115000.00', app(FinanceBalanceService::class)->balance($account));
    }

    public function test_adjust_balance_action_records_the_difference(): void
    {
        $account = $this->account('BCA', '100000.00');
        $this->actingAs(User::factory()->create(['is_admin' => true]))->post("/admin/finance/accounts/{$account->id}/adjust", [
            'actual_balance' => '95000.00', 'transaction_date' => today()->toDateString(), 'description' => 'Bank reconciliation',
        ])->assertRedirect('/admin/finance/accounts');

        $this->assertDatabaseHas('finance_transactions', ['type' => 'adjustment', 'amount' => '5000.00', 'adjustment_direction' => 'decrease']);
        $this->assertSame('95000.00', app(FinanceBalanceService::class)->balance($account));
    }

    public function test_overview_calculates_current_month_without_transfer_or_adjustment_as_cash_flow(): void
    {
        $account = $this->account('BCA', '100000.00');
        $other = $this->account('Cash', '50000.00');
        $income = FinanceCategory::where('type', 'income')->firstOrFail();
        $expense = FinanceCategory::where('type', 'expense')->firstOrFail();
        FinanceTransaction::create(['type' => 'income', 'transaction_date' => today(), 'amount' => '30000.00', 'account_id' => $account->id, 'category_id' => $income->id]);
        FinanceTransaction::create(['type' => 'expense', 'transaction_date' => today(), 'amount' => '12000.00', 'account_id' => $account->id, 'category_id' => $expense->id]);
        FinanceTransaction::create(['type' => 'transfer', 'transaction_date' => today(), 'amount' => '10000.00', 'from_account_id' => $account->id, 'to_account_id' => $other->id]);
        FinanceTransaction::create(['type' => 'adjustment', 'transaction_date' => today(), 'amount' => '2000.00', 'account_id' => $other->id, 'adjustment_direction' => 'increase']);

        $this->actingAs(User::factory()->create(['is_admin' => true]))->get('/admin/finance')->assertInertia(fn (Assert $page) => $page
            ->where('summary.total_balance', '170000.00')->where('summary.income', '30000.00')->where('summary.expense', '12000.00')->where('summary.net_cash_flow', '18000.00'));
    }

    public function test_account_with_transactions_is_deactivated_instead_of_deleted(): void
    {
        $account = $this->account('Cash', '0.00');
        $category = FinanceCategory::where('type', 'income')->firstOrFail();
        FinanceTransaction::create(['type' => 'income', 'transaction_date' => today(), 'amount' => '1000.00', 'account_id' => $account->id, 'category_id' => $category->id]);

        $this->actingAs(User::factory()->create(['is_admin' => true]))->delete("/admin/finance/accounts/{$account->id}")->assertRedirect('/admin/finance/accounts');
        $this->assertDatabaseHas('finance_accounts', ['id' => $account->id, 'is_active' => false]);
    }

    private function account(string $name, string $openingBalance): FinanceAccount
    {
        return FinanceAccount::create(['name' => $name, 'type' => 'bank', 'opening_balance' => $openingBalance, 'currency' => 'IDR', 'is_active' => true]);
    }
}
