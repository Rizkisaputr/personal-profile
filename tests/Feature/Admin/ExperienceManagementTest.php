<?php

namespace Tests\Feature\Admin;

use App\Models\Experience;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ExperienceManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['is_admin' => true]);
    }

    public function test_admin_can_list_create_update_and_delete_experience(): void
    {
        $this->actingAs($this->admin)->get('/admin/experiences')->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('admin/experiences/Index'));

        $this->actingAs($this->admin)->post('/admin/experiences', $this->data())->assertRedirect('/admin/experiences');
        $experience = Experience::firstOrFail();

        $this->actingAs($this->admin)->put("/admin/experiences/{$experience->id}", $this->data(['position' => 'Senior Developer']))
            ->assertRedirect('/admin/experiences');
        $this->assertDatabaseHas('experiences', ['id' => $experience->id, 'position' => 'Senior Developer']);

        $this->actingAs($this->admin)->delete("/admin/experiences/{$experience->id}")->assertRedirect('/admin/experiences');
        $this->assertDatabaseMissing('experiences', ['id' => $experience->id]);
    }

    public function test_current_experience_can_be_created_without_end_date(): void
    {
        $this->actingAs($this->admin)->post('/admin/experiences', $this->data(['is_current' => true, 'end_date' => null]))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('experiences', ['is_current' => true, 'end_date' => null]);
    }

    private function data(array $overrides = []): array
    {
        return array_merge([
            'company_name' => 'Example Company',
            'position' => 'Developer',
            'employment_type' => 'Full-time',
            'location' => 'Indonesia',
            'start_date' => '2022-01-01',
            'end_date' => '2023-01-01',
            'is_current' => false,
            'description' => 'Built business applications.',
            'sort_order' => 0,
            'is_active' => true,
        ], $overrides);
    }
}
