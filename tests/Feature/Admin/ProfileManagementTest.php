<?php

namespace Tests\Feature\Admin;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ProfileManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_profile_page(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]))
            ->get('/admin/profile')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('admin/profile/Edit'));
    }

    public function test_admin_can_update_profile_and_upload_files(): void
    {
        Storage::fake('public');
        $profile = Profile::create([
            'full_name' => 'Old Name',
            'profile_image' => 'profile/old.jpg',
            'hero_image' => 'profile/hero/old.jpg',
            'login_image' => 'profile/login/old.jpg',
            'cv_file' => 'cv/old.pdf',
        ]);
        Storage::disk('public')->put('profile/old.jpg', 'old');
        Storage::disk('public')->put('profile/hero/old.jpg', 'old hero');
        Storage::disk('public')->put('profile/login/old.jpg', 'old login');
        Storage::disk('public')->put('cv/old.pdf', 'old');

        $response = $this->actingAs(User::factory()->create(['is_admin' => true]))
            ->patch('/admin/profile', [
                'full_name' => 'Updated Name',
                'professional_title' => 'Software Developer',
                'email' => 'hello@example.com',
                'birth_place' => 'Jakarta',
                'birth_date' => '1995-08-17',
                'marital_status' => 'Married',
                'location' => 'Jakarta, Indonesia',
                'availability_status' => 'Available for selected projects',
                'profile_image' => UploadedFile::fake()->image('profile.jpg'),
                'hero_image' => UploadedFile::fake()->image('hero.webp'),
                'login_image' => UploadedFile::fake()->image('login.png'),
                'cv_file' => UploadedFile::fake()->create('resume.pdf', 100, 'application/pdf'),
            ]);

        $response->assertRedirect('/admin/profile');
        $profile->refresh();
        $this->assertSame('Updated Name', $profile->full_name);
        $this->assertSame('Software Developer', $profile->professional_title);
        $this->assertSame('Jakarta', $profile->birth_place);
        $this->assertSame('1995-08-17', $profile->birth_date->format('Y-m-d'));
        $this->assertSame('Married', $profile->marital_status);
        $this->assertSame('Jakarta, Indonesia', $profile->location);
        $this->assertSame('Available for selected projects', $profile->availability_status);
        Storage::disk('public')->assertMissing('profile/old.jpg');
        Storage::disk('public')->assertMissing('profile/hero/old.jpg');
        Storage::disk('public')->assertMissing('profile/login/old.jpg');
        Storage::disk('public')->assertMissing('cv/old.pdf');
        Storage::disk('public')->assertExists($profile->profile_image);
        Storage::disk('public')->assertExists($profile->hero_image);
        Storage::disk('public')->assertExists($profile->login_image);
        $this->assertStringStartsWith('profile/sidebar/', $profile->profile_image);
        $this->assertStringStartsWith('profile/hero/', $profile->hero_image);
        $this->assertStringStartsWith('profile/login/', $profile->login_image);
        Storage::disk('public')->assertExists($profile->cv_file);
    }

    public function test_sidebar_and_hero_images_can_be_replaced_independently(): void
    {
        Storage::fake('public');
        $profile = Profile::create([
            'full_name' => 'Rizki Saputra',
            'profile_image' => 'profile/sidebar/current.jpg',
            'hero_image' => 'profile/hero/current.jpg',
        ]);
        Storage::disk('public')->put($profile->profile_image, 'sidebar');
        Storage::disk('public')->put($profile->hero_image, 'hero');

        $this->actingAs(User::factory()->create(['is_admin' => true]))
            ->patch('/admin/profile', [
                'full_name' => $profile->full_name,
                'hero_image' => UploadedFile::fake()->image('replacement.png'),
            ])->assertRedirect('/admin/profile');

        $profile->refresh();
        $this->assertSame('profile/sidebar/current.jpg', $profile->profile_image);
        Storage::disk('public')->assertExists('profile/sidebar/current.jpg');
        Storage::disk('public')->assertMissing('profile/hero/current.jpg');
        Storage::disk('public')->assertExists($profile->hero_image);
    }

    public function test_login_image_can_be_replaced_without_changing_other_images(): void
    {
        Storage::fake('public');
        $profile = Profile::create([
            'full_name' => 'Rizki Saputra',
            'profile_image' => 'profile/sidebar/avatar.jpg',
            'hero_image' => 'profile/hero/hero.png',
            'login_image' => 'profile/login/old.webp',
        ]);
        Storage::disk('public')->put($profile->profile_image, 'avatar');
        Storage::disk('public')->put($profile->hero_image, 'hero');
        Storage::disk('public')->put($profile->login_image, 'login');

        $this->actingAs(User::factory()->create(['is_admin' => true]))
            ->patch('/admin/profile', [
                'full_name' => $profile->full_name,
                'login_image' => UploadedFile::fake()->image('replacement.webp'),
            ])->assertRedirect('/admin/profile');

        $profile->refresh();
        $this->assertSame('profile/sidebar/avatar.jpg', $profile->profile_image);
        $this->assertSame('profile/hero/hero.png', $profile->hero_image);
        Storage::disk('public')->assertExists($profile->profile_image);
        Storage::disk('public')->assertExists($profile->hero_image);
        Storage::disk('public')->assertMissing('profile/login/old.webp');
        Storage::disk('public')->assertExists($profile->login_image);
        $this->assertStringStartsWith('profile/login/', $profile->login_image);
    }

    public function test_non_admin_cannot_access_profile_management(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => false]))
            ->get('/admin/profile')
            ->assertForbidden();
    }
}
