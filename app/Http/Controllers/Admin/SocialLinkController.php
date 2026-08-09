<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSocialLinkRequest;
use App\Http\Requests\Admin\UpdateSocialLinkRequest;
use App\Models\SocialLink;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class SocialLinkController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin/social-links/Index', [
            'socialLinks' => SocialLink::orderBy('sort_order')->orderBy('platform')->get(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/social-links/Create');
    }

    public function store(StoreSocialLinkRequest $request): RedirectResponse
    {
        SocialLink::create($request->validated());
        Inertia::flash('toast', ['type' => 'success', 'message' => 'Social link created successfully.']);

        return to_route('admin.social-links.index');
    }

    public function edit(SocialLink $socialLink): Response
    {
        return Inertia::render('admin/social-links/Edit', ['socialLink' => $socialLink]);
    }

    public function update(UpdateSocialLinkRequest $request, SocialLink $socialLink): RedirectResponse
    {
        $socialLink->update($request->validated());
        Inertia::flash('toast', ['type' => 'success', 'message' => 'Social link updated successfully.']);

        return to_route('admin.social-links.index');
    }

    public function destroy(SocialLink $socialLink): RedirectResponse
    {
        $socialLink->delete();
        Inertia::flash('toast', ['type' => 'success', 'message' => 'Social link deleted.']);

        return to_route('admin.social-links.index');
    }
}
