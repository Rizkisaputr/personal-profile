<?php

namespace Tests\Feature\Admin;

use App\Models\Asset;
use App\Models\Document;
use App\Models\FinanceAccount;
use App\Models\FinanceDebt;
use App\Models\FinanceRecurringBill;
use App\Models\FinanceSavingsGoal;
use App\Models\FinanceTransaction;
use App\Models\Project;
use App\Models\Subscription;
use App\Models\User;
use Database\Seeders\DevelopmentDataSeeder;
use Database\Seeders\FinanceCategorySeeder;
use Database\Seeders\PersonalDevelopmentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PersonalDevelopmentSeederTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([DevelopmentDataSeeder::class, FinanceCategorySeeder::class, PersonalDevelopmentSeeder::class]);
    }

    public function test_development_seed_populates_complete_personal_control_center_data(): void
    {
        $this->assertSame(5, FinanceAccount::count());
        $this->assertGreaterThanOrEqual(50, FinanceTransaction::count());
        $this->assertSame(5, FinanceDebt::count());
        $this->assertSame(2, FinanceSavingsGoal::count());
        $this->assertSame(4, FinanceRecurringBill::count());
        $this->assertSame(4, Subscription::count());
        $this->assertSame(5, Document::count());
        $this->assertSame(4, Asset::count());
        $this->assertSame(0, Document::whereNotNull('file_path')->count());
    }

    public function test_development_seed_is_idempotent_for_personal_master_and_history_data(): void
    {
        $before = $this->counts();
        $this->seed(PersonalDevelopmentSeeder::class);

        $this->assertSame($before, $this->counts());
    }

    public function test_seeded_savings_progress_and_shortfall_match_scenario(): void
    {
        $emergency = FinanceSavingsGoal::where('name', 'Dana Darurat')->firstOrFail();

        $this->assertSame('18000000.00', $emergency->savedAmount());
        $this->assertSame(60, $emergency->progress());
        $this->assertSame('3250000.00', $emergency->monthSummary(now()->year, now()->month)['actual']);
        $this->assertSame(65, $emergency->monthSummary(now()->year, now()->month)['progress']);
        $this->assertSame('1000000.00', $emergency->previousMonthShortfall()['shortfall']);
    }

    public function test_seeded_dashboard_has_chart_alert_and_activity_data(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->get('/admin')->assertInertia(fn (Assert $page) => $page
            ->component('admin/Index')
            ->has('cashFlow', 6)
            ->where('cashFlow.5.income', fn ($value) => (float) $value > 0)
            ->where('cashFlow.5.expense', fn ($value) => (float) $value > 0)
            ->has('expenseBreakdown')
            ->has('alerts', fn (Assert $alerts) => $alerts
                ->where('0.severity', 'urgent')->etc())
            ->has('recentTransactions', 8)
            ->has('upcomingPayments'));
    }

    public function test_seed_distributes_project_work_status_without_changing_visibility(): void
    {
        $visibility = Project::orderBy('id')->pluck('is_published', 'id')->all();

        $this->assertSame(['total' => 5, 'waiting' => 1, 'running' => 2, 'on_hold' => 1, 'done' => 1], Project::workStatusCounts());
        $this->seed(PersonalDevelopmentSeeder::class);
        $this->assertSame($visibility, Project::orderBy('id')->pluck('is_published', 'id')->all());
    }

    public function test_seeded_private_modules_remain_admin_only(): void
    {
        foreach (['/admin/finance', '/admin/documents', '/admin/subscriptions', '/admin/assets'] as $url) {
            $this->get($url)->assertRedirect('/login');
        }

        $user = User::factory()->create(['is_admin' => false]);
        foreach (['/admin/finance', '/admin/documents', '/admin/subscriptions', '/admin/assets'] as $url) {
            $this->actingAs($user)->get($url)->assertForbidden();
        }
    }

    private function counts(): array
    {
        return [
            FinanceAccount::count(), FinanceTransaction::count(), FinanceDebt::count(), FinanceRecurringBill::count(),
            FinanceSavingsGoal::count(), Subscription::count(), Document::count(), Asset::count(),
        ];
    }
}
