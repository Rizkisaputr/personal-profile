<?php

namespace Tests\Feature\Admin;

use App\Models\FinanceCategory;
use App\Models\FinanceTransaction;
use App\Models\User;
use Database\Seeders\FinanceCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class FinanceCategoryManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['is_admin' => true]);
    }

    public function test_admin_can_list_create_update_and_delete_an_unused_category(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.finance.categories.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/finance/categories/Index')
                ->has('categories.data', 0));

        $this->post(route('admin.finance.categories.store'), [
            'name' => 'Freelance',
            'type' => 'income',
            'is_active' => true,
        ])->assertRedirect(route('admin.finance.categories.index'));

        $category = FinanceCategory::firstOrFail();
        $this->assertSame(1, $category->sort_order);

        $this->put(route('admin.finance.categories.update', $category), [
            'name' => 'Freelance Project',
            'type' => 'income',
            'is_active' => false,
        ])->assertRedirect(route('admin.finance.categories.index'));
        $this->assertDatabaseHas('finance_categories', [
            'id' => $category->id,
            'name' => 'Freelance Project',
            'is_active' => false,
        ]);

        $this->delete(route('admin.finance.categories.destroy', $category))
            ->assertRedirect(route('admin.finance.categories.index'));
        $this->assertDatabaseMissing('finance_categories', ['id' => $category->id]);
    }

    public function test_category_name_is_unique_within_each_type(): void
    {
        FinanceCategory::create(['name' => 'Other', 'type' => 'income', 'is_active' => true]);

        $this->actingAs($this->admin)->post(route('admin.finance.categories.store'), [
            'name' => 'Other',
            'type' => 'income',
            'is_active' => true,
        ])->assertSessionHasErrors('name');

        $this->post(route('admin.finance.categories.store'), [
            'name' => 'Other',
            'type' => 'expense',
            'is_active' => true,
        ])->assertRedirect(route('admin.finance.categories.index'));
    }

    public function test_category_in_use_is_deactivated_instead_of_deleted(): void
    {
        $category = FinanceCategory::create(['name' => 'Salary', 'type' => 'income', 'is_active' => true]);
        FinanceTransaction::create([
            'type' => 'income',
            'transaction_date' => today(),
            'amount' => '1000.00',
            'category_id' => $category->id,
        ]);

        $this->actingAs($this->admin)
            ->delete(route('admin.finance.categories.destroy', $category))
            ->assertRedirect(route('admin.finance.categories.index'));

        $this->assertDatabaseHas('finance_categories', [
            'id' => $category->id,
            'is_active' => false,
        ]);
    }

    public function test_transaction_form_receives_only_active_categories_for_both_types(): void
    {
        FinanceCategory::create(['name' => 'Salary', 'type' => 'income', 'is_active' => true]);
        FinanceCategory::create(['name' => 'Food', 'type' => 'expense', 'is_active' => true]);
        FinanceCategory::create(['name' => 'Archived', 'type' => 'expense', 'is_active' => false]);

        $this->actingAs($this->admin)
            ->get(route('admin.finance.transactions.create'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/finance/transactions/Create')
                ->has('categories', 2)
                ->where('categories.0.type', 'expense')
                ->where('categories.0.name', 'Food')
                ->where('categories.1.type', 'income')
                ->where('categories.1.name', 'Salary'));
    }

    public function test_default_category_seeder_is_idempotent(): void
    {
        $this->seed(FinanceCategorySeeder::class);
        $count = FinanceCategory::count();
        $this->seed(FinanceCategorySeeder::class);

        $this->assertSame($count, FinanceCategory::count());
        $this->assertDatabaseHas('finance_categories', ['name' => 'Investment', 'type' => 'income']);
        $this->assertDatabaseHas('finance_categories', ['name' => 'Entertainment', 'type' => 'expense']);
    }
}
