<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Technology;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectRelationshipsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     */
    public function test_project_has_many_images(): void
    {
        $project = Project::create(['title' => 'Example Project', 'slug' => 'example-project']);
        $image = $project->images()->create(['image' => 'projects/example.jpg']);

        $this->assertTrue($project->images->contains($image));
        $this->assertTrue($image->project->is($project));
        $this->assertDatabaseHas('project_images', [
            'project_id' => $project->id,
            'image' => 'projects/example.jpg',
        ]);
    }

    public function test_projects_and_technologies_have_a_many_to_many_relationship(): void
    {
        $project = Project::create(['title' => 'Example Project', 'slug' => 'example-project']);
        $technology = Technology::create(['name' => 'Laravel', 'slug' => 'laravel']);

        $project->technologies()->attach($technology);

        $this->assertTrue($project->technologies->contains($technology));
        $this->assertTrue($technology->projects->contains($project));
        $this->assertDatabaseHas('project_technology', [
            'project_id' => $project->id,
            'technology_id' => $technology->id,
        ]);
    }
}
