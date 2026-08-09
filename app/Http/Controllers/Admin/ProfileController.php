<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateProfileRequest;
use App\Models\Profile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function edit(): Response
    {
        $profile = Profile::firstOrCreate(['id' => 1], ['full_name' => 'Your Name']);

        return Inertia::render('admin/profile/Edit', [
            'profile' => [
                ...$profile->toArray(),
                'profile_image_url' => $profile->profile_image
                    ? Storage::disk('public')->url($profile->profile_image)
                    : null,
                'hero_image_url' => $profile->hero_image
                    ? Storage::disk('public')->url($profile->hero_image)
                    : null,
                'login_image_url' => $profile->login_image
                    ? Storage::disk('public')->url($profile->login_image)
                    : null,
                'cv_url' => $profile->cv_file
                    ? Storage::disk('public')->url($profile->cv_file)
                    : null,
                'cv_name' => $profile->cv_file ? basename($profile->cv_file) : null,
            ],
        ]);
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $profile = Profile::firstOrCreate(['id' => 1], ['full_name' => 'Your Name']);
        $data = $request->safe()->except(['profile_image', 'hero_image', 'login_image', 'cv_file']);

        if ($request->hasFile('profile_image')) {
            if ($profile->profile_image) {
                Storage::disk('public')->delete($profile->profile_image);
            }

            $data['profile_image'] = $request->file('profile_image')->store('profile/sidebar', 'public');
        }

        if ($request->hasFile('hero_image')) {
            if ($profile->hero_image) {
                Storage::disk('public')->delete($profile->hero_image);
            }

            $data['hero_image'] = $request->file('hero_image')->store('profile/hero', 'public');
        }

        if ($request->hasFile('login_image')) {
            if ($profile->login_image) {
                Storage::disk('public')->delete($profile->login_image);
            }

            $data['login_image'] = $request->file('login_image')->store('profile/login', 'public');
        }

        if ($request->hasFile('cv_file')) {
            if ($profile->cv_file) {
                Storage::disk('public')->delete($profile->cv_file);
            }

            $data['cv_file'] = $request->file('cv_file')->store('cv', 'public');
        }

        $profile->update($data);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Profile updated successfully.']);

        return to_route('admin.profile.edit');
    }
}
