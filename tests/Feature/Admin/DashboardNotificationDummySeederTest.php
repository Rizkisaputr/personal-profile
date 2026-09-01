<?php

namespace Tests\Feature\Admin;

use App\Models\FinanceDebt;
use App\Models\FinanceRecurringBill;
use App\Models\User;
use Database\Seeders\DashboardNotificationDummySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DashboardNotificationDummySeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_is_idempotent_and_dashboard_omits_due_bill_alerts(): void
    {
        $this->seed(DashboardNotificationDummySeeder::class);
        $this->seed(DashboardNotificationDummySeeder::class);

        $this->assertSame(1, FinanceDebt::where('name', 'Shopee PayLater')->count());
        $this->assertSame(1, FinanceDebt::where('name', 'Shopee PayLater')->firstOrFail()->schedules()->count());
        $this->assertSame(1, FinanceRecurringBill::where('name', 'Internet Rumah')->count());

        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->get('/admin')->assertInertia(fn (Assert $page) => $page
            ->where('debtSummary.items.0.name', 'Shopee PayLater')
            ->missing('alerts')
            ->missing('upcomingPayments'));
    }
}
