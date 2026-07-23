<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $supportedLocales = array_keys(LaravelLocalization::getSupportedLocales());
        $defaultLocale = config('app.locale', 'en');
        $locale = (string) $request->session()->get('app_locale', $defaultLocale);

        if (! in_array($locale, $supportedLocales, true)) {
            $locale = $defaultLocale;
        }

        app()->setLocale($locale);
        LaravelLocalization::setLocale($locale);

        return $next($request);
    }
}
