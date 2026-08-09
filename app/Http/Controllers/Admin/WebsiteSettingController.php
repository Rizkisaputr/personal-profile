<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateWebsiteSettingRequest;
use App\Models\WebsiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class WebsiteSettingController extends Controller
{
    private const TEXT_KEYS = [
        'website_title', 'website_description', 'seo_title', 'seo_description',
        'footer_text', 'contact_email', 'contact_whatsapp',
    ];

    public function edit(): Response
    {
        $settings = WebsiteSetting::whereIn('key', [...self::TEXT_KEYS, 'logo', 'favicon'])
            ->pluck('value', 'key')
            ->all();

        return Inertia::render('admin/settings/Edit', [
            'settings' => [
                ...array_fill_keys(self::TEXT_KEYS, null),
                ...$settings,
                'logo_url' => isset($settings['logo']) ? Storage::disk('public')->url($settings['logo']) : null,
                'favicon_url' => isset($settings['favicon']) ? Storage::disk('public')->url($settings['favicon']) : null,
            ],
        ]);
    }

    public function update(UpdateWebsiteSettingRequest $request): RedirectResponse
    {
        foreach (Arr::only($request->validated(), self::TEXT_KEYS) as $key => $value) {
            WebsiteSetting::updateOrCreate(['key' => $key], ['value' => $value, 'type' => 'string']);
        }

        $this->replaceFile($request, 'logo');
        $this->replaceFile($request, 'favicon');

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Website settings updated.']);

        return to_route('admin.settings.edit');
    }

    private function replaceFile(UpdateWebsiteSettingRequest $request, string $key): void
    {
        if (! $request->hasFile($key)) {
            return;
        }

        $setting = WebsiteSetting::firstOrNew(['key' => $key]);
        if ($setting->value) {
            Storage::disk('public')->delete($setting->value);
        }
        $setting->fill([
            'value' => $request->file($key)->store('settings', 'public'),
            'type' => 'file',
        ])->save();
    }
}
