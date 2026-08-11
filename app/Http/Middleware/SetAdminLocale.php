<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetAdminLocale
{
    /** @var list<string> */
    private const SUPPORTED_LOCALES = ['en', 'id'];

    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->is('admin') && ! $request->is('admin/*')) {
            return $next($request);
        }

        $locale = $request->session()->get(
            'admin_locale',
            $request->cookie('admin_locale', 'en'),
        );

        if (! in_array($locale, self::SUPPORTED_LOCALES, true)) {
            $locale = 'en';
        }

        app()->setLocale($locale);
        $request->session()->put('admin_locale', $locale);

        return $next($request);
    }
}
