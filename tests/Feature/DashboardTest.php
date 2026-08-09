<?php

namespace Tests\Feature;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page()
    {
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_admins_are_redirected_to_the_admin_dashboard()
    {
        $user = User::factory()->create(['is_admin' => true]);
        $this->actingAs($user);

        $response = $this->get(route('dashboard'));
        $response->assertRedirect('/admin');
    }

    public function test_admin_dashboard_receives_profile_identity(): void
    {
        Profile::create([
            'full_name' => 'Rizki Saputra',
            'professional_title' => 'Full Stack Web Developer',
            'availability_status' => 'Open to selected projects and collaborations.',
        ]);
        $user = User::factory()->create(['is_admin' => true]);

        $this->actingAs($user)->get('/admin')->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/Index')
                ->where('profileIdentity.name', 'Rizki Saputra')
                ->where('profileIdentity.availability_status', 'Open to selected projects and collaborations.'));
    }
}
