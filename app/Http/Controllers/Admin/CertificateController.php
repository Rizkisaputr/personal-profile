<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCertificateRequest;
use App\Http\Requests\Admin\UpdateCertificateRequest;
use App\Models\Certificate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class CertificateController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin/certificates/Index', [
            'certificates' => Certificate::orderBy('sort_order')->orderByDesc('issue_date')->get(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/certificates/Create');
    }

    public function store(StoreCertificateRequest $request): RedirectResponse
    {
        $data = Arr::except($request->validated(), ['image']);
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('certificates', 'public');
        }
        Certificate::create($data);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Certificate created successfully.']);

        return to_route('admin.certificates.index');
    }

    public function edit(Certificate $certificate): Response
    {
        return Inertia::render('admin/certificates/Edit', [
            'certificate' => [
                ...$certificate->toArray(),
                'image_url' => $certificate->image ? Storage::disk('public')->url($certificate->image) : null,
            ],
        ]);
    }

    public function update(UpdateCertificateRequest $request, Certificate $certificate): RedirectResponse
    {
        $data = Arr::except($request->validated(), ['image']);
        if ($request->hasFile('image')) {
            if ($certificate->image) {
                Storage::disk('public')->delete($certificate->image);
            }
            $data['image'] = $request->file('image')->store('certificates', 'public');
        }
        $certificate->update($data);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Certificate updated successfully.']);

        return to_route('admin.certificates.index');
    }

    public function destroy(Certificate $certificate): RedirectResponse
    {
        if ($certificate->image) {
            Storage::disk('public')->delete($certificate->image);
        }
        $certificate->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Certificate deleted.']);

        return to_route('admin.certificates.index');
    }
}
