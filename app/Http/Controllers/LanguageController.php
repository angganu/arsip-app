<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

class LanguageController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $supportedLocales = array_keys(LaravelLocalization::getSupportedLocales());

        $validated = $request->validate([
            'locale' => ['required', 'string', 'in:' . implode(',', $supportedLocales)],
        ]);

        $request->session()->put('app_locale', $validated['locale']);

        return back()->with('status', __('texts.language_updated'));
    }
}
