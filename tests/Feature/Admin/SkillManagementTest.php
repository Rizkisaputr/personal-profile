<?php

namespace Tests\Feature\Admin;

use App\Models\Skill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class SkillManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['is_admin' => true]);
    }

    public function test_admin_can_list_skills(): void
    {
        Skill::create(['name' => 'Laravel', 'slug' => 'laravel']);

        $this->actingAs($this->admin)->get('/admin/skills')->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('admin/skills/Index')->has('skills', 1));
    }

    public function test_admin_can_create_skill_with_generated_slug(): void
    {
        $this->actingAs($this->admin)->post('/admin/skills', $this->data(['name' => 'Vue.js']))
            ->assertRedirect('/admin/skills');

        $this->assertDatabaseHas('skills', ['name' => 'Vue.js', 'slug' => 'vuejs', 'is_active' => true]);
    }

    public function test_admin_can_update_skill(): void
    {
        $skill = Skill::create(['name' => 'Vue', 'slug' => 'vue']);

        $this->actingAs($this->admin)->put("/admin/skills/{$skill->id}", $this->data(['name' => 'Vue.js', 'proficiency' => 90]))
            ->assertRedirect('/admin/skills');

        $this->assertDatabaseHas('skills', ['id' => $skill->id, 'slug' => 'vuejs', 'proficiency' => 90]);
    }

    public function test_admin_can_delete_skill(): void
    {
        $skill = Skill::create(['name' => 'Git', 'slug' => 'git']);
        $this->actingAs($this->admin)->delete("/admin/skills/{$skill->id}")->assertRedirect('/admin/skills');
        $this->assertDatabaseMissing('skills', ['id' => $skill->id]);
    }

    public function test_skill_validation_runs(): void
    {
        $this->actingAs($this->admin)->post('/admin/skills', $this->data(['name' => '', 'proficiency' => 101]))
            ->assertSessionHasErrors(['name', 'proficiency']);
    }

    private function data(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Laravel',
            'category' => 'Engineering',
            'icon' => null,
            'proficiency' => 80,
            'description' => null,
            'sort_order' => 0,
            'is_active' => true,
        ], $overrides);
    }
}
