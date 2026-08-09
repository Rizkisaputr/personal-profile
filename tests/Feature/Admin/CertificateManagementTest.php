<?php

namespace Tests\Feature\Admin;

use App\Models\Certificate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CertificateManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_crud_certificate_with_image(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin)->get('/admin/certificates')->assertOk();
        $this->actingAs($admin)->post('/admin/certificates', $this->data(['image' => UploadedFile::fake()->image('certificate.jpg')]))
            ->assertRedirect('/admin/certificates');

        $certificate = Certificate::firstOrFail();
        Storage::disk('public')->assertExists($certificate->image);
        $oldImage = $certificate->image;

        $this->actingAs($admin)->put("/admin/certificates/{$certificate->id}", $this->data([
            'title' => 'Updated Certificate', 'image' => UploadedFile::fake()->image('new.jpg'),
        ]))->assertRedirect('/admin/certificates');
        $certificate->refresh();
        Storage::disk('public')->assertMissing($oldImage);
        Storage::disk('public')->assertExists($certificate->image);

        $image = $certificate->image;
        $this->actingAs($admin)->delete("/admin/certificates/{$certificate->id}")->assertRedirect('/admin/certificates');
        Storage::disk('public')->assertMissing($image);
    }

    private function data(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Laravel Certificate', 'issuer' => 'Example', 'issue_date' => '2025-01-01',
            'credential_id' => 'ABC123', 'credential_url' => 'https://example.com/credential',
            'image' => null, 'description' => null, 'sort_order' => 0, 'is_active' => true,
        ], $overrides);
    }
}
