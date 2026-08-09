<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\WebsiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class WebsiteSettingManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_and_update_website_settings_with_logo(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin)->get('/admin/settings')->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('admin/settings/Edit'));

        $this->actingAs($admin)->patch('/admin/settings', [
            'website_title' => 'Professional Profile',
            'website_description' => 'Professional portfolio website',
            'seo_title' => 'Professional Profile',
            'seo_description' => 'Selected work and professional experience.',
            'footer_text' => 'Professional Profile',
            'contact_email' => 'hello@example.com',
            'contact_whatsapp' => '+620000000',
            'logo' => UploadedFile::fake()->image('logo.png'),
        ])->assertRedirect('/admin/settings');

        $this->assertDatabaseHas('website_settings', ['key' => 'website_title', 'value' => 'Professional Profile']);
        $logo = WebsiteSetting::where('key', 'logo')->firstOrFail();
        Storage::disk('public')->assertExists($logo->value);
    }

    public function test_non_admin_cannot_access_website_settings(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => false]))->get('/admin/settings')->assertForbidden();
    }
}
