<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreEducationRequest;
use App\Http\Requests\Admin\UpdateEducationRequest;
use App\Models\Education;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class EducationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        return Inertia::render('admin/educations/Index', [
            'educations' => Education::orderBy('sort_order')->orderByDesc('start_year')->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('admin/educations/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreEducationRequest $request): RedirectResponse
    {
        Education::create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Education created successfully.']);

        return to_route('admin.educations.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Education $education)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Education $education): Response
    {
        return Inertia::render('admin/educations/Edit', ['education' => $education]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEducationRequest $request, Education $education): RedirectResponse
    {
        $education->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Education updated successfully.']);

        return to_route('admin.educations.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Education $education): RedirectResponse
    {
        $education->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Education deleted.']);

        return to_route('admin.educations.index');
    }
}
