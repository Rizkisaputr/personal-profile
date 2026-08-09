<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTrainingRequest;
use App\Http\Requests\Admin\UpdateTrainingRequest;
use App\Models\Training;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class TrainingController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin/trainings/Index', [
            'trainings' => Training::orderBy('sort_order')->orderByDesc('start_date')->get(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/trainings/Create');
    }

    public function store(StoreTrainingRequest $request): RedirectResponse
    {
        Training::create($request->validated());
        Inertia::flash('toast', ['type' => 'success', 'message' => 'Training created successfully.']);

        return to_route('admin.trainings.index');
    }

    public function edit(Training $training): Response
    {
        return Inertia::render('admin/trainings/Edit', ['training' => $training]);
    }

    public function update(UpdateTrainingRequest $request, Training $training): RedirectResponse
    {
        $training->update($request->validated());
        Inertia::flash('toast', ['type' => 'success', 'message' => 'Training updated successfully.']);

        return to_route('admin.trainings.index');
    }

    public function destroy(Training $training): RedirectResponse
    {
        $training->delete();
        Inertia::flash('toast', ['type' => 'success', 'message' => 'Training deleted.']);

        return to_route('admin.trainings.index');
    }
}
