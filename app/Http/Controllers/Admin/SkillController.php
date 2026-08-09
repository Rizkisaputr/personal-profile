<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSkillRequest;
use App\Http\Requests\Admin\UpdateSkillRequest;
use App\Models\Skill;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class SkillController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        return Inertia::render('admin/skills/Index', [
            'skills' => Skill::orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('admin/skills/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSkillRequest $request): RedirectResponse
    {
        Skill::create([...$request->validated(), 'slug' => Str::slug((string) $request->string('name'))]);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Skill created successfully.']);

        return to_route('admin.skills.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Skill $skill): Response
    {
        return Inertia::render('admin/skills/Edit', ['skill' => $skill]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSkillRequest $request, Skill $skill): RedirectResponse
    {
        $skill->update([...$request->validated(), 'slug' => Str::slug((string) $request->string('name'))]);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Skill updated successfully.']);

        return to_route('admin.skills.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Skill $skill): RedirectResponse
    {
        $skill->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Skill deleted.']);

        return to_route('admin.skills.index');
    }
}
