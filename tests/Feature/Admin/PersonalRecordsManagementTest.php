<?php

namespace Tests\Feature\Admin;

use App\Models\Asset;
use App\Models\Document;
use App\Models\FinanceAccount;
use App\Models\FinanceTransaction;
use App\Models\Subscription;
use App\Models\SubscriptionPayment;
use App\Models\User;
use App\Services\FinanceBalanceService;
use Database\Seeders\FinanceCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PersonalRecordsManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(FinanceCategorySeeder::class);
    }

    public function test_documents_subscriptions_and_assets_are_admin_only(): void
    {
        foreach (['documents', 'subscriptions', 'assets'] as $path) {
            $this->get("/admin/{$path}")->assertRedirect('/login');
        }

        $user = User::factory()->create(['is_admin' => false]);
        foreach (['documents', 'subscriptions', 'assets'] as $path) {
            $this->actingAs($user)->get("/admin/{$path}")->assertForbidden();
        }
    }

    public function test_admin_can_store_document_on_private_disk_and_update_metadata(): void
    {
        Storage::fake('local');
        $admin = $this->admin();
        $this->actingAs($admin)->post('/admin/documents', [
            'name' => 'Identity Card', 'category' => 'Identity', 'document_number' => '1234567890',
            'issue_date' => '2026-01-01', 'expiry_date' => '2027-01-01',
            'file' => UploadedFile::fake()->create('identity.pdf', 100, 'application/pdf'), 'is_active' => true,
        ])->assertRedirect('/admin/documents');

        $document = Document::firstOrFail();
        Storage::disk('local')->assertExists($document->file_path);
        Storage::disk('public')->assertMissing($document->file_path);
        $this->assertSame('••••••7890', $document->maskedNumber());

        $this->actingAs($admin)->put("/admin/documents/{$document->id}", [
            'name' => 'Updated Identity', 'category' => 'Identity', 'document_number' => '1234567890',
            'issue_date' => '2026-01-01', 'expiry_date' => '2027-01-01', 'is_active' => true,
        ])->assertRedirect('/admin/documents');
        $this->assertDatabaseHas('documents', ['id' => $document->id, 'name' => 'Updated Identity']);
    }

    public function test_document_file_cannot_be_viewed_or_downloaded_by_guest_or_non_admin(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('documents/private.pdf', 'classified');
        $document = Document::create([
            'name' => 'Private', 'category' => 'Finance', 'file_path' => 'documents/private.pdf',
            'original_filename' => 'private.pdf', 'mime_type' => 'application/pdf', 'is_active' => true,
        ]);

        foreach (["/admin/documents/{$document->id}/view", "/admin/documents/{$document->id}/download"] as $url) {
            $this->get($url)->assertRedirect('/login');
            $this->actingAs(User::factory()->create(['is_admin' => false]))->get($url)->assertForbidden();
            auth()->logout();
        }

        $this->get('/storage/documents/private.pdf')->assertForbidden();
    }

    public function test_admin_can_view_and_download_private_document(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('documents/report.pdf', 'private-content');
        $document = Document::create([
            'name' => 'Report', 'category' => 'Finance', 'file_path' => 'documents/report.pdf',
            'original_filename' => 'report.pdf', 'mime_type' => 'application/pdf', 'is_active' => true,
        ]);
        $admin = $this->admin();

        $this->actingAs($admin)->get("/admin/documents/{$document->id}/view")->assertOk();
        $this->actingAs($admin)->get("/admin/documents/{$document->id}/download")
            ->assertOk()->assertDownload('report.pdf');
    }

    public function test_document_expiry_helpers_are_accurate(): void
    {
        $expired = Document::create(['name' => 'Old', 'category' => 'Other', 'expiry_date' => today()->subDay(), 'is_active' => true]);
        $today = Document::create(['name' => 'Today', 'category' => 'Other', 'expiry_date' => today(), 'is_active' => true]);
        $soon = Document::create(['name' => 'Soon', 'category' => 'Other', 'expiry_date' => today()->addDays(7), 'is_active' => true]);

        $this->assertTrue(Document::expired()->whereKey($expired)->exists());
        $this->assertTrue(Document::expiresToday()->whereKey($today)->exists());
        $this->assertTrue(Document::expiresWithin(7)->whereKey($soon)->exists());
        $this->assertTrue(Document::expiresWithin(30)->whereKey($soon)->exists());
    }

    public function test_subscription_payment_creates_expense_history_and_updates_renewal(): void
    {
        $account = $this->account('500000.00');
        $subscription = Subscription::create([
            'service_name' => 'Hosting', 'cost' => '100000.00', 'billing_cycle' => 'monthly',
            'next_renewal_date' => '2026-08-15', 'payment_account_id' => $account->id, 'is_active' => true,
        ]);

        $this->actingAs($this->admin())->post("/admin/subscriptions/{$subscription->id}/payments", [
            'amount' => '100000.00', 'paid_at' => '2026-08-10', 'account_id' => $account->id,
        ])->assertRedirect();

        $this->assertDatabaseHas('finance_transactions', ['type' => 'expense', 'reference_type' => 'subscription']);
        $this->assertSame(1, SubscriptionPayment::where('subscription_id', $subscription->id)->count());
        $this->assertSame('2026-09-15', $subscription->refresh()->next_renewal_date->toDateString());
        $this->assertSame('400000.00', app(FinanceBalanceService::class)->balance($account));
    }

    public function test_subscription_custom_cycle_and_inactive_payment_safety(): void
    {
        $subscription = Subscription::create([
            'service_name' => 'Domain', 'cost' => '100.00', 'billing_cycle' => 'custom',
            'custom_interval_days' => 10, 'next_renewal_date' => '2026-08-15', 'is_active' => false,
        ]);
        $this->assertSame('2026-08-25', $subscription->nextDateAfterPayment()->toDateString());
        $this->actingAs($this->admin())->post("/admin/subscriptions/{$subscription->id}/payments", [
            'amount' => '100.00', 'paid_at' => '2026-08-15', 'account_id' => $this->account()->id,
        ])->assertUnprocessable();
        $this->assertSame(0, FinanceTransaction::count());
    }

    public function test_admin_can_crud_asset_without_value_accounting(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->post('/admin/assets', [
            'name' => 'Laptop', 'category' => 'Computer', 'purchase_price' => '15000000.00',
            'purchase_date' => '2026-01-01', 'warranty_expiry' => '2028-01-01', 'serial_number' => 'ABC-123', 'status' => 'active',
        ])->assertRedirect('/admin/assets');
        $asset = Asset::firstOrFail();
        $this->actingAs($admin)->put("/admin/assets/{$asset->id}", [
            'name' => 'Work Laptop', 'category' => 'Computer', 'purchase_price' => '15000000.00', 'status' => 'active',
        ])->assertRedirect('/admin/assets');
        $this->assertDatabaseHas('assets', ['id' => $asset->id, 'name' => 'Work Laptop']);
        $this->actingAs($admin)->delete("/admin/assets/{$asset->id}")->assertRedirect();
        $this->assertDatabaseMissing('assets', ['id' => $asset->id]);
    }

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    private function account(string $opening = '0.00'): FinanceAccount
    {
        return FinanceAccount::create(['name' => fake()->unique()->word(), 'type' => 'bank', 'opening_balance' => $opening, 'currency' => 'IDR', 'is_active' => true]);
    }
}
