<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class LanguageController extends Controller
{
    /**
     * Available languages
     */
    protected $languages = ['en', 'sw'];

    /**
     * Switch the application language
     *
     * @param Request $request
     * @param string $locale
     * @return \Illuminate\Http\RedirectResponse
     */
    public function switch(Request $request, string $locale)
    {
        // Validate the locale
        if (!in_array($locale, $this->languages)) {
            $locale = 'en'; // Default to English
        }

        // Store in session
        Session::put('locale', $locale);

        // If user is authenticated, save preference to database
        if (auth()->check()) {
            auth()->user()->update(['locale' => $locale]);
        }

        return redirect()->back()->with('success', $locale === 'sw' 
            ? 'Lugha imebadilishwa kuwa Kiswahili' 
            : 'Language changed to English');
    }

    /**
     * Get the current language
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function current()
    {
        return response()->json([
            'locale' => App::getLocale(),
            'available' => $this->languages
        ]);
    }
}
