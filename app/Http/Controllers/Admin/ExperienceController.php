<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreExperienceRequest;
use App\Http\Requests\Admin\UpdateExperienceRequest;
use App\Models\Experience;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ExperienceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        return Inertia::render('admin/experiences/Index', [
            'experiences' => Experience::orderBy('sort_order')->orderByDesc('start_date')->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('admin/experiences/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreExperienceRequest $request): RedirectResponse
    {
        Experience::create($this->validatedData($request));

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Experience created successfully.']);

        return to_route('admin.experiences.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Experience $experience)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Experience $experience): Response
    {
        return Inertia::render('admin/experiences/Edit', ['experience' => $experience]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateExperienceRequest $request, Experience $experience): RedirectResponse
    {
        $experience->update($this->validatedData($request));

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Experience updated successfully.']);

        return to_route('admin.experiences.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Experience $experience): RedirectResponse
    {
        $experience->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Experience deleted.']);

        return to_route('admin.experiences.index');
    }

    private function validatedData(StoreExperienceRequest|UpdateExperienceRequest $request): array
    {
        $data = $request->validated();

        if ($data['is_current']) {
            $data['end_date'] = null;
        }

        return $data;
    }
}
