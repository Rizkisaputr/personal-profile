<?php

namespace Tests\Feature\Admin;

use App\Models\Project;
use App\Models\Technology;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ProjectManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->admin = User::factory()->create(['is_admin' => true]);
    }

    public function test_admin_can_list_projects(): void
    {
        Project::create($this->projectAttributes());

        $this->actingAs($this->admin)->get('/admin/projects')->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('admin/projects/Index')->has('projects', 1));
    }

    public function test_admin_can_create_project_with_thumbnail_gallery_and_technologies(): void
    {
        $technology = Technology::create(['name' => 'Laravel', 'slug' => 'laravel']);
        $payload = $this->requestData([
            'thumbnail' => UploadedFile::fake()->image('thumbnail.jpg'),
            'gallery_images' => [UploadedFile::fake()->image('one.jpg'), UploadedFile::fake()->image('two.jpg')],
            'gallery_captions' => ['Overview', 'Dashboard'],
            'gallery_sort_orders' => [1, 2],
            'technology_ids' => [$technology->id],
        ]);

        $this->actingAs($this->admin)->post('/admin/projects', $payload)->assertRedirect('/admin/projects');

        $project = Project::with(['images', 'technologies'])->firstOrFail();
        $this->assertSame('example-project', $project->slug);
        $this->assertSame('running', $project->work_status);
        $this->assertCount(2, $project->images);
        $this->assertTrue($project->technologies->contains($technology));
        Storage::disk('public')->assertExists($project->thumbnail);
        $project->images->each(fn ($image) => Storage::disk('public')->assertExists($image->image));
    }

    public function test_admin_can_update_project_replace_thumbnail_and_sync_technologies(): void
    {
        $old = Technology::create(['name' => 'PHP', 'slug' => 'php']);
        $new = Technology::create(['name' => 'Vue', 'slug' => 'vue']);
        $project = Project::create([...$this->projectAttributes(), 'thumbnail' => 'projects/thumbnails/old.jpg']);
        $project->technologies()->attach($old);
        Storage::disk('public')->put($project->thumbnail, 'old');

        $this->actingAs($this->admin)->put("/admin/projects/{$project->id}", $this->requestData([
            'title' => 'Updated Project',
            'slug' => $project->slug,
            'thumbnail' => UploadedFile::fake()->image('new.jpg'),
            'technology_ids' => [$new->id],
        ]))->assertRedirect("/admin/projects/{$project->id}/edit");

        $project->refresh()->load('technologies');
        $this->assertSame('Updated Project', $project->title);
        $this->assertSame('example-project', $project->slug);
        $this->assertTrue($project->technologies->contains($new));
        $this->assertFalse($project->technologies->contains($old));
        Storage::disk('public')->assertMissing('projects/thumbnails/old.jpg');
        Storage::disk('public')->assertExists($project->thumbnail);
    }

    public function test_deleting_project_cleans_thumbnail_and_gallery_files(): void
    {
        $project = Project::create([...$this->projectAttributes(), 'thumbnail' => 'projects/thumbnails/project.jpg']);
        $image = $project->images()->create(['image' => 'projects/gallery/image.jpg']);
        Storage::disk('public')->put($project->thumbnail, 'thumbnail');
        Storage::disk('public')->put($image->image, 'gallery');

        $this->actingAs($this->admin)->delete("/admin/projects/{$project->id}")->assertRedirect('/admin/projects');

        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
        $this->assertDatabaseMissing('project_images', ['id' => $image->id]);
        Storage::disk('public')->assertMissing($project->thumbnail);
        Storage::disk('public')->assertMissing($image->image);
    }

    public function test_project_slug_must_be_unique(): void
    {
        Project::create($this->projectAttributes());

        $this->actingAs($this->admin)->post('/admin/projects', $this->requestData(['slug' => '']))
            ->assertSessionHasErrors('slug');
    }

    public function test_non_admin_cannot_access_project_admin(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => false]))->get('/admin/projects')->assertForbidden();
    }

    public function test_published_and_featured_values_are_cast_to_boolean(): void
    {
        $project = Project::create([...$this->projectAttributes(), 'is_published' => 1, 'is_featured' => 1]);
        $this->assertTrue($project->is_published);
        $this->assertTrue($project->is_featured);
    }

    public function test_work_status_is_validated_and_counts_are_available(): void
    {
        Project::create([...$this->projectAttributes(), 'slug' => 'waiting', 'work_status' => 'waiting']);
        Project::create([...$this->projectAttributes(), 'slug' => 'running', 'work_status' => 'running']);
        Project::create([...$this->projectAttributes(), 'slug' => 'done', 'work_status' => 'done']);

        $this->assertSame([
            'total' => 3, 'waiting' => 1, 'running' => 1, 'on_hold' => 0, 'done' => 1,
        ], Project::workStatusCounts());
        $this->assertSame(1, Project::withWorkStatus('running')->count());

        $this->actingAs($this->admin)->post('/admin/projects', $this->requestData(['work_status' => 'invalid']))
            ->assertSessionHasErrors('work_status');
    }

    private function projectAttributes(): array
    {
        return [
            'title' => 'Example Project', 'slug' => 'example-project', 'sort_order' => 0,
            'is_published' => true, 'is_featured' => false, 'work_status' => 'done',
        ];
    }

    private function requestData(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Example Project', 'slug' => '', 'short_description' => 'A practical project.',
            'description' => 'Project Overview', 'thumbnail' => null, 'category' => 'Web Application',
            'project_date' => '2025-01-01', 'github_url' => 'https://github.com/example/project',
            'demo_url' => 'https://example.com', 'client_name' => 'Example Client',
            'is_featured' => false, 'is_published' => true, 'work_status' => 'running', 'sort_order' => 0,
            'seo_title' => null, 'seo_description' => null, 'technology_ids' => [],
            'gallery_images' => [], 'gallery_captions' => [], 'gallery_sort_orders' => [],
        ], $overrides);
    }
}
