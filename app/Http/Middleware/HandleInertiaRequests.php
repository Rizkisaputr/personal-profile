<?php

namespace App\Http\Middleware;

use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $profile = Profile::query()->select([
            'full_name',
            'professional_title',
            'tagline',
            'availability_status',
            'profile_image',
            'hero_image',
            'login_image',
        ])->first();

        $loginImage = $profile?->login_image ?: ($profile?->hero_image ?: $profile?->profile_image);

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $request->user(),
            ],
            'profileIdentity' => $profile ? [
                'name' => $profile->full_name,
                'professional_title' => $profile->professional_title,
                'tagline' => $profile->tagline,
                'availability_status' => $profile->availability_status,
                'image_url' => $profile->profile_image
                    ? Storage::disk('public')->url($profile->profile_image)
                    : null,
                'login_image_url' => $loginImage
                    ? Storage::disk('public')->url($loginImage)
                    : null,
            ] : null,
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'locale' => $request->is('admin/*') || $request->is('admin') ? app()->getLocale() : null,
            'availableLocales' => $request->is('admin/*') || $request->is('admin') ? [
                ['code' => 'en', 'name' => 'English'],
                ['code' => 'id', 'name' => 'Bahasa Indonesia'],
            ] : [],
        ];
    }
}
