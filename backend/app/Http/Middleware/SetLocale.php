<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class SetLocale
{
    private const SUPPORTED = ['lv', 'en', 'ru', 'cs', 'de'];

    public function handle(Request $request, Closure $next)
    {
        $locale = $request->header('X-Locale')
            ?? $request->query('locale')
            ?? ($request->user()?->locale)
            ?? $request->getPreferredLanguage(self::SUPPORTED)
            ?? config('app.fallback_locale');

        if (in_array($locale, self::SUPPORTED, true)) {
            App::setLocale($locale);
        }

        return $next($request);
    }
}
