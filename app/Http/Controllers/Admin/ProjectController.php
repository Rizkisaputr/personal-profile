<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProjectRequest;
use App\Http\Requests\Admin\UpdateProjectRequest;
use App\Models\Project;
use App\Models\ProjectImage;
use App\Models\Technology;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    public function index(Request $request): Response
    {
        $projects = Project::query()
            ->with('technologies:id,name,slug')
            ->when($request->string('search')->toString(), function ($query, string $search) {
                $query->where(fn ($query) => $query
                    ->where('title', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%"));
            })
            ->when($request->string('published')->toString(), function ($query, string $published) {
                if (in_array($published, ['published', 'draft'], true)) {
                    $query->where('is_published', $published === 'published');
                }
            })
            ->when($request->string('featured')->toString() === 'featured', fn ($query) => $query->where('is_featured', true))
            ->orderBy('sort_order')
            ->orderByDesc('project_date')
            ->get()
            ->map(fn (Project $project) => [
                ...$project->toArray(),
                'thumbnail_url' => $project->thumbnail ? Storage::disk('public')->url($project->thumbnail) : null,
            ]);

        return Inertia::render('admin/projects/Index', [
            'projects' => $projects,
            'filters' => $request->only(['search', 'published', 'featured']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/projects/Create', [
            'technologies' => Technology::orderBy('name')->get(['id', 'name', 'slug', 'icon']),
        ]);
    }

    public function store(StoreProjectRequest $request): RedirectResponse
    {
        $data = Arr::except($request->validated(), [
            'thumbnail', 'technology_ids', 'gallery_images', 'gallery_captions', 'gallery_sort_orders',
        ]);
        $data['slug'] = Str::slug((string) ($request->filled('slug') ? $request->string('slug') : $request->string('title')));

        if ($request->hasFile('thumbnail')) {
            $data['thumbnail'] = $request->file('thumbnail')->store('projects/thumbnails', 'public');
        }

        $project = Project::create($data);
        $project->technologies()->sync($request->input('technology_ids', []));
        $this->storeGallery($project, $request);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Project created successfully.']);

        return to_route('admin.projects.index');
    }

    public function edit(Project $project): Response
    {
        $project->load(['technologies:id,name,slug,icon', 'images' => fn ($query) => $query->orderBy('sort_order')]);

        return Inertia::render('admin/projects/Edit', [
            'project' => [
                ...$project->toArray(),
                'thumbnail_url' => $project->thumbnail ? Storage::disk('public')->url($project->thumbnail) : null,
                'technology_ids' => $project->technologies->pluck('id'),
                'images' => $project->images->map(fn (ProjectImage $image) => [
                    ...$image->toArray(),
                    'url' => Storage::disk('public')->url($image->image),
                ]),
            ],
            'technologies' => Technology::orderBy('name')->get(['id', 'name', 'slug', 'icon']),
        ]);
    }

    public function update(UpdateProjectRequest $request, Project $project): RedirectResponse
    {
        $data = Arr::except($request->validated(), [
            'thumbnail', 'technology_ids', 'gallery_images', 'gallery_captions', 'gallery_sort_orders', 'existing_images',
        ]);
        $data['slug'] = $request->filled('slug') ? Str::slug((string) $request->string('slug')) : $project->slug;

        if ($request->hasFile('thumbnail')) {
            if ($project->thumbnail) {
                Storage::disk('public')->delete($project->thumbnail);
            }
            $data['thumbnail'] = $request->file('thumbnail')->store('projects/thumbnails', 'public');
        }

        $project->update($data);
        $project->technologies()->sync($request->input('technology_ids', []));
        $this->updateExistingImages($project, $request->input('existing_images', []));
        $this->storeGallery($project, $request);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Project updated successfully.']);

        return to_route('admin.projects.edit', $project);
    }

    public function destroy(Project $project): RedirectResponse
    {
        $project->load('images');
        $files = $project->images->pluck('image')->all();
        if ($project->thumbnail) {
            $files[] = $project->thumbnail;
        }
        Storage::disk('public')->delete($files);
        $project->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Project deleted.']);

        return to_route('admin.projects.index');
    }

    public function destroyImage(Project $project, ProjectImage $projectImage): RedirectResponse
    {
        abort_unless($projectImage->project_id === $project->id, 404);
        Storage::disk('public')->delete($projectImage->image);
        $projectImage->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Gallery image deleted.']);

        return to_route('admin.projects.edit', $project);
    }

    private function storeGallery(Project $project, StoreProjectRequest|UpdateProjectRequest $request): void
    {
        foreach ($request->file('gallery_images', []) as $index => $file) {
            $project->images()->create([
                'image' => $file->store('projects/gallery', 'public'),
                'caption' => $request->input("gallery_captions.{$index}"),
                'sort_order' => $request->integer("gallery_sort_orders.{$index}", $index),
            ]);
        }
    }

    private function updateExistingImages(Project $project, array $images): void
    {
        foreach ($images as $id => $attributes) {
            $project->images()->whereKey($id)->update([
                'caption' => $attributes['caption'] ?? null,
                'sort_order' => $attributes['sort_order'] ?? 0,
            ]);
        }
    }
}
