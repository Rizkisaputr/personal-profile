<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectImage;
use App\Models\SocialLink;
use App\Models\WebsiteSetting;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    public function index(): Response
    {
        $settings = WebsiteSetting::pluck('value', 'key')->all();
        $publicSettings = $this->publicSettings($settings);
        $projects = Project::published()
            ->with('technologies:id,name,slug,icon')
            ->orderBy('sort_order')->orderByDesc('project_date')->get();

        return Inertia::render('public/projects/Index', [
            'projects' => $projects->map(fn (Project $project) => $this->projectData($project)),
            'categories' => $projects->pluck('category')->filter()->unique()->values(),
            'socialLinks' => SocialLink::where('is_active', true)->orderBy('sort_order')->get(),
            'settings' => $publicSettings,
            'seo' => [
                'title' => 'Projects · '.($settings['website_title'] ?? config('app.name')),
                'description' => $settings['website_description'] ?? 'Selected projects and professional case studies.',
                'canonical' => route('projects.index'),
            ],
        ]);
    }

    public function show(Project $project): Response
    {
        abort_unless($project->is_published, 404);

        $project->load([
            'technologies:id,name,slug,icon',
            'images' => fn ($query) => $query->orderBy('sort_order'),
        ]);
        $settings = WebsiteSetting::pluck('value', 'key')->all();
        $publicSettings = $this->publicSettings($settings);
        $nextProject = $this->nextProject($project);

        return Inertia::render('public/projects/Show', [
            'project' => [
                ...$this->projectData($project),
                'images' => $project->images->map(fn (ProjectImage $image) => [
                    ...$image->toArray(),
                    'url' => $this->fileUrl($image->image),
                ]),
            ],
            'nextProject' => $nextProject ? $this->projectData($nextProject) : null,
            'socialLinks' => SocialLink::where('is_active', true)->orderBy('sort_order')->get(),
            'settings' => $publicSettings,
            'seo' => [
                'title' => $project->seo_title ?: $project->title,
                'description' => $project->seo_description ?: $project->short_description,
                'canonical' => route('projects.show', $project),
                'image' => $this->fileUrl($project->thumbnail),
            ],
        ]);
    }

    private function nextProject(Project $project): ?Project
    {
        $projects = Project::published()
            ->with('technologies:id,name,slug,icon')
            ->orderBy('sort_order')->orderByDesc('project_date')->orderBy('id')->get();
        $index = $projects->search(fn (Project $candidate) => $candidate->is($project));

        if ($index === false || $index === $projects->keys()->last()) {
            return null;
        }

        return $projects->get($index + 1);
    }

    private function projectData(Project $project): array
    {
        return [
            ...$project->toArray(),
            'thumbnail_url' => $this->fileUrl($project->thumbnail),
            'url' => route('projects.show', $project),
        ];
    }

    private function fileUrl(?string $path): ?string
    {
        return $path ? Storage::disk('public')->url($path) : null;
    }

    private function publicSettings(array $settings): array
    {
        return [
            ...$settings,
            'logo_url' => $this->fileUrl($settings['logo'] ?? null),
            'favicon_url' => $this->fileUrl($settings['favicon'] ?? null),
        ];
    }
}
