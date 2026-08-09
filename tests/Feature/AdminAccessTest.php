<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     */
    public function test_guest_cannot_access_admin_area(): void
    {
        $this->get('/admin')->assertRedirect('/login');
    }

    public function test_non_admin_cannot_access_admin_area(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)->get('/admin')->assertForbidden();
    }

    public function test_admin_can_access_admin_area(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->get('/admin')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('admin/Index'));
    }

    public function test_public_registration_is_not_available(): void
    {
        $this->get('/register')->assertNotFound();

        $this->post('/register')->assertNotFound();
    }
}
