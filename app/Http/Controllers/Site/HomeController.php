<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\Education;
use App\Models\Experience;
use App\Models\Profile;
use App\Models\Project;
use App\Models\Skill;
use App\Models\SocialLink;
use App\Models\Training;
use App\Models\WebsiteSetting;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __invoke(): Response
    {
        $settings = WebsiteSetting::pluck('value', 'key')->all();
        $profile = Profile::first();
        $projects = $this->selectedProjects();
        $socialLinks = SocialLink::where('is_active', true)->orderBy('sort_order')->get();

        return Inertia::render('public/Home', [
            'profile' => $profile ? [
                ...$profile->toArray(),
                'profile_image_url' => $this->fileUrl($profile->profile_image),
                'hero_image_url' => $this->fileUrl($profile->hero_image),
                'cv_url' => $this->fileUrl($profile->cv_file),
            ] : null,
            'personalDetails' => $this->personalDetails($profile),
            'projects' => $projects->map(fn (Project $project) => $this->projectData($project)),
            'skills' => Skill::active()->orderBy('sort_order')->orderBy('name')->get(),
            'experiences' => Experience::active()->orderBy('sort_order')->orderByDesc('start_date')->get(),
            'educations' => Education::where('is_active', true)->orderBy('sort_order')->orderByDesc('start_year')->get(),
            'trainings' => Training::active()->orderBy('sort_order')->orderByDesc('start_date')->get(),
            'certificates' => Certificate::where('is_active', true)
                ->orderBy('sort_order')->orderByDesc('issue_date')->limit(4)->get()
                ->map(fn (Certificate $certificate) => [
                    ...$certificate->toArray(),
                    'image_url' => $this->fileUrl($certificate->image),
                ]),
            'socialLinks' => $socialLinks,
            'sidebarSocialLinks' => $this->sidebarSocialLinks($socialLinks),
            'settings' => [
                ...$settings,
                'logo_url' => $this->fileUrl($settings['logo'] ?? null),
                'favicon_url' => $this->fileUrl($settings['favicon'] ?? null),
            ],
            'seo' => [
                'title' => $settings['seo_title'] ?? $settings['website_title'] ?? $profile?->full_name ?? config('app.name'),
                'description' => $settings['seo_description'] ?? $settings['website_description'] ?? $profile?->short_bio,
                'canonical' => route('home'),
                'image' => $this->fileUrl($settings['logo'] ?? $profile?->profile_image),
            ],
        ]);
    }

    private function selectedProjects()
    {
        return Project::published()
            ->with('technologies:id,name,slug,icon')
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->orderByDesc('project_date')
            ->limit(6)
            ->get();
    }

    private function projectData(Project $project): array
    {
        return [
            ...$project->toArray(),
            'thumbnail_url' => $this->fileUrl($project->thumbnail),
            'url' => route('projects.show', $project),
        ];
    }

    private function personalDetails(?Profile $profile): array
    {
        if (! $profile) {
            return [];
        }

        $born = collect([
            $profile->birth_place,
            $profile->birth_date?->format('d M Y'),
        ])->filter()->implode(' · ');

        return collect([
            ['label' => 'Name', 'value' => $profile->full_name],
            ['label' => 'Professional role', 'value' => $profile->professional_title],
            ['label' => 'Born', 'value' => $born],
            ['label' => 'Age', 'value' => $profile->birth_date?->age !== null ? (string) $profile->birth_date->age : null],
            ['label' => 'Based in', 'value' => $profile->location],
            ['label' => 'Availability', 'value' => $profile->availability_status],
            ['label' => 'Status', 'value' => $profile->marital_status],
            ['label' => 'Email', 'value' => $profile->email, 'href' => $profile->email ? 'mailto:'.$profile->email : null],
        ])->filter(fn (array $detail) => filled($detail['value']))->values()->all();
    }

    private function fileUrl(?string $path): ?string
    {
        return $path ? Storage::disk('public')->url($path) : null;
    }

    private function sidebarSocialLinks($links)
    {
        $aliases = ['github' => 'github', 'instagram' => 'instagram', 'tiktok' => 'tiktok', 'whatsapp' => 'whatsapp', 'facebook' => 'facebook', 'fb' => 'facebook'];
        $order = array_flip(['github', 'instagram', 'tiktok', 'whatsapp', 'facebook']);

        return $links->filter(function (SocialLink $link) use ($aliases) {
            $platform = strtolower((string) preg_replace('/[\s_-]+/', '', $link->platform));

            return filled($link->url) && isset($aliases[$platform]);
        })->sortBy(function (SocialLink $link) use ($aliases, $order) {
            $platform = strtolower((string) preg_replace('/[\s_-]+/', '', $link->platform));

            return $order[$aliases[$platform]];
        })->values();
    }
}
