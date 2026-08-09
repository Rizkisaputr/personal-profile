<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTechnologyRequest;
use App\Http\Requests\Admin\UpdateTechnologyRequest;
use App\Models\Technology;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class TechnologyController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin/technologies/Index', [
            'technologies' => Technology::withCount('projects')->orderBy('name')->get(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/technologies/Create');
    }

    public function store(StoreTechnologyRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['slug'] = Str::slug((string) ($request->filled('slug') ? $request->string('slug') : $request->string('name')));
        Technology::create($data);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Technology created successfully.']);

        return to_route('admin.technologies.index');
    }

    public function edit(Technology $technology): Response
    {
        return Inertia::render('admin/technologies/Edit', ['technology' => $technology]);
    }

    public function update(UpdateTechnologyRequest $request, Technology $technology): RedirectResponse
    {
        $data = $request->validated();
        $data['slug'] = $request->filled('slug') ? Str::slug((string) $request->string('slug')) : $technology->slug;
        $technology->update($data);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Technology updated successfully.']);

        return to_route('admin.technologies.index');
    }

    public function destroy(Technology $technology): RedirectResponse
    {
        $technology->delete();
        Inertia::flash('toast', ['type' => 'success', 'message' => 'Technology deleted.']);

        return to_route('admin.technologies.index');
    }
}
