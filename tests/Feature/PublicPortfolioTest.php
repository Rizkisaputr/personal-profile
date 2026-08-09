<?php

namespace Tests\Feature;

use App\Models\Profile;
use App\Models\Project;
use App\Models\SocialLink;
use App\Models\Technology;
use App\Models\Training;
use App\Models\WebsiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PublicPortfolioTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_is_accessible_to_guests_with_empty_optional_content(): void
    {
        $this->get('/')->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('public/Home')
                ->where('profile', null)
                ->has('projects', 0)
                ->has('skills', 0));
    }

    public function test_featured_project_appears_and_unpublished_project_does_not_appear_on_home(): void
    {
        Project::create($this->projectData(['title' => 'Featured Work', 'slug' => 'featured', 'is_featured' => true]));
        Project::create($this->projectData(['title' => 'Private Work', 'slug' => 'private', 'is_featured' => true, 'is_published' => false]));

        $this->get('/')->assertInertia(fn (Assert $page) => $page
            ->has('projects', 1)
            ->where('projects.0.title', 'Featured Work'));
    }

    public function test_home_falls_back_to_published_projects_when_no_featured_project_exists(): void
    {
        Project::create($this->projectData(['title' => 'Published Work', 'slug' => 'published']));

        $this->get('/')->assertInertia(fn (Assert $page) => $page
            ->has('projects', 1)
            ->where('projects.0.title', 'Published Work'));
    }

    public function test_projects_page_only_contains_published_projects(): void
    {
        Project::create($this->projectData(['title' => 'Public Project', 'slug' => 'public-project']));
        Project::create($this->projectData(['title' => 'Draft Project', 'slug' => 'draft-project', 'is_published' => false]));

        $this->get('/projects')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('public/projects/Index')
            ->has('projects', 1)
            ->where('projects.0.title', 'Public Project'));
    }

    public function test_published_project_detail_is_accessible_and_loads_technologies(): void
    {
        $project = Project::create($this->projectData());
        $technology = Technology::create(['name' => 'Laravel', 'slug' => 'laravel']);
        $project->technologies()->attach($technology);

        $this->get("/projects/{$project->slug}")->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('public/projects/Show')
            ->where('project.title', $project->title)
            ->has('project.technologies', 1)
            ->where('project.technologies.0.name', 'Laravel'));
    }

    public function test_unpublished_project_detail_returns_not_found(): void
    {
        $project = Project::create($this->projectData(['is_published' => false]));
        $this->get("/projects/{$project->slug}")->assertNotFound();
    }

    public function test_website_settings_are_used_for_home_seo(): void
    {
        WebsiteSetting::create(['key' => 'seo_title', 'value' => 'Custom Portfolio Title']);
        WebsiteSetting::create(['key' => 'seo_description', 'value' => 'Custom portfolio description.']);

        $this->get('/')->assertInertia(fn (Assert $page) => $page
            ->where('seo.title', 'Custom Portfolio Title')
            ->where('seo.description', 'Custom portfolio description.'));
    }

    public function test_birth_date_produces_the_current_age(): void
    {
        Carbon::setTestNow('2026-08-08');
        Profile::create([
            'full_name' => 'Raka Pratama',
            'birth_place' => 'Jakarta',
            'birth_date' => '1995-08-17',
        ]);

        $this->get('/')->assertInertia(fn (Assert $page) => $page
            ->where('personalDetails', fn ($details) => collect($details)->contains(
                fn ($detail) => $detail['label'] === 'Age' && $detail['value'] === '30',
            ) && collect($details)->contains(
                fn ($detail) => $detail['label'] === 'Born' && $detail['value'] === 'Jakarta · 17 Aug 1995',
            )));

        Carbon::setTestNow();
    }

    public function test_age_is_omitted_when_birth_date_is_empty(): void
    {
        Profile::create(['full_name' => 'Raka Pratama']);

        $this->get('/')->assertInertia(fn (Assert $page) => $page
            ->where('personalDetails', fn ($details) => ! collect($details)->contains('label', 'Age')));
    }

    public function test_personal_details_only_contain_available_values(): void
    {
        Profile::create([
            'full_name' => 'Raka Pratama',
            'email' => 'raka@example.com',
        ]);

        $this->get('/')->assertInertia(fn (Assert $page) => $page
            ->has('personalDetails', 2)
            ->where('personalDetails.0.label', 'Name')
            ->where('personalDetails.1.label', 'Email'));
    }

    public function test_home_only_includes_active_training(): void
    {
        Training::create($this->trainingData(['program_name' => 'Visible Training']));
        Training::create($this->trainingData(['program_name' => 'Hidden Training', 'is_active' => false]));

        $this->get('/')->assertInertia(fn (Assert $page) => $page
            ->has('trainings', 1)
            ->where('trainings.0.program_name', 'Visible Training'));
    }

    public function test_home_exposes_independent_sidebar_and_hero_image_urls(): void
    {
        Storage::fake('public');
        Profile::create([
            'full_name' => 'Rizki Saputra',
            'profile_image' => 'profile/sidebar/rizki.jpg',
            'hero_image' => 'profile/hero/rizki.png',
        ]);

        $this->get('/')->assertInertia(fn (Assert $page) => $page
            ->where('profile.profile_image_url', Storage::disk('public')->url('profile/sidebar/rizki.jpg'))
            ->where('profile.hero_image_url', Storage::disk('public')->url('profile/hero/rizki.png')));
    }

    public function test_home_allows_hero_to_fallback_when_hero_image_is_empty(): void
    {
        Profile::create(['full_name' => 'Rizki Saputra', 'profile_image' => 'profile/sidebar/rizki.jpg']);

        $this->get('/')->assertInertia(fn (Assert $page) => $page
            ->where('profile.profile_image_url', Storage::disk('public')->url('profile/sidebar/rizki.jpg'))
            ->where('profile.hero_image_url', null));
    }

    public function test_sidebar_social_links_are_active_whitelisted_and_ordered(): void
    {
        SocialLink::create(['platform' => 'Instagram', 'url' => 'https://example.com/instagram', 'is_active' => true, 'sort_order' => 1]);
        SocialLink::create(['platform' => 'GITHUB', 'url' => 'https://example.com/github', 'is_active' => true, 'sort_order' => 9]);
        SocialLink::create(['platform' => 'Tik Tok', 'url' => 'https://example.com/tiktok', 'is_active' => false, 'sort_order' => 2]);
        SocialLink::create(['platform' => 'LinkedIn', 'url' => 'https://example.com/linkedin', 'is_active' => true, 'sort_order' => 0]);

        $this->get('/')->assertInertia(fn (Assert $page) => $page
            ->has('sidebarSocialLinks', 2)
            ->where('sidebarSocialLinks.0.platform', 'GITHUB')
            ->where('sidebarSocialLinks.1.platform', 'Instagram'));
    }

    public function test_home_has_no_training_entries_when_none_are_active(): void
    {
        Training::create($this->trainingData(['is_active' => false]));

        $this->get('/')->assertInertia(fn (Assert $page) => $page->has('trainings', 0));
    }

    private function trainingData(array $overrides = []): array
    {
        return array_merge([
            'institution' => 'Training Institution',
            'program_name' => 'Professional Course',
            'sort_order' => 0,
            'is_active' => true,
        ], $overrides);
    }

    private function projectData(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Example Project',
            'slug' => 'example-project',
            'short_description' => 'A practical case study.',
            'is_featured' => false,
            'is_published' => true,
            'sort_order' => 0,
            'project_date' => '2025-01-01',
        ], $overrides);
    }
}
