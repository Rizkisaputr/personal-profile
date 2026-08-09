<?php

namespace Tests\Feature\Admin;

use App\Models\Project;
use App\Models\Technology;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TechnologyManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_crud_technology(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin)->get('/admin/technologies')->assertOk();
        $this->actingAs($admin)->post('/admin/technologies', ['name' => 'Vue.js', 'slug' => '', 'icon' => 'vue'])
            ->assertRedirect('/admin/technologies');

        $technology = Technology::firstOrFail();
        $this->assertSame('vuejs', $technology->slug);
        $this->actingAs($admin)->put("/admin/technologies/{$technology->id}", ['name' => 'Vue', 'slug' => $technology->slug, 'icon' => 'vue'])
            ->assertRedirect('/admin/technologies');
        $this->assertDatabaseHas('technologies', ['id' => $technology->id, 'name' => 'Vue']);
        $this->actingAs($admin)->delete("/admin/technologies/{$technology->id}")->assertRedirect('/admin/technologies');
        $this->assertDatabaseMissing('technologies', ['id' => $technology->id]);
    }

    public function test_technology_slug_must_be_unique(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        Technology::create(['name' => 'Laravel', 'slug' => 'laravel']);
        $this->actingAs($admin)->post('/admin/technologies', ['name' => 'Laravel', 'slug' => ''])
            ->assertSessionHasErrors('slug');
    }

    public function test_technology_has_project_relationship(): void
    {
        $technology = Technology::create(['name' => 'Laravel', 'slug' => 'laravel']);
        $project = Project::create(['title' => 'Project', 'slug' => 'project']);
        $technology->projects()->attach($project);
        $this->assertTrue($technology->projects->contains($project));
    }
}
