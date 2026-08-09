<?php

namespace Tests\Feature\Admin;

use App\Models\SocialLink;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SocialLinkManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_crud_social_link(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $data = ['platform' => 'GitHub', 'label' => 'GitHub', 'url' => 'https://github.com/example', 'icon' => 'github', 'sort_order' => 0, 'is_active' => true];
        $this->actingAs($admin)->get('/admin/social-links')->assertOk();
        $this->actingAs($admin)->post('/admin/social-links', $data)->assertRedirect('/admin/social-links');
        $link = SocialLink::firstOrFail();
        $this->actingAs($admin)->put("/admin/social-links/{$link->id}", [...$data, 'label' => 'Source code'])
            ->assertRedirect('/admin/social-links');
        $this->assertDatabaseHas('social_links', ['id' => $link->id, 'label' => 'Source code']);
        $this->actingAs($admin)->delete("/admin/social-links/{$link->id}")->assertRedirect('/admin/social-links');
        $this->assertDatabaseMissing('social_links', ['id' => $link->id]);
    }

    public function test_social_link_requires_valid_url(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin)->post('/admin/social-links', [
            'platform' => 'Invalid', 'url' => 'not-a-url', 'sort_order' => 0, 'is_active' => true,
        ])->assertSessionHasErrors('url');
    }
}
