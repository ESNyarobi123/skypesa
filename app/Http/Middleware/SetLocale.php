<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Available languages
     */
    protected $languages = ['en', 'sw'];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->determineLocale($request);
        
        App::setLocale($locale);
        
        return $next($request);
    }

    /**
     * Determine the locale to use
     *
     * @param Request $request
     * @return string
     */
    protected function determineLocale(Request $request): string
    {
        // 1. Check if user is authenticated and has a saved preference
        if (auth()->check() && auth()->user()->locale) {
            $locale = auth()->user()->locale;
            if (in_array($locale, $this->languages)) {
                Session::put('locale', $locale);
                return $locale;
            }
        }

        // 2. Check session
        if (Session::has('locale')) {
            $locale = Session::get('locale');
            if (in_array($locale, $this->languages)) {
                return $locale;
            }
        }

        // 3. Check browser preference
        $browserLocale = $request->getPreferredLanguage($this->languages);
        if ($browserLocale && in_array($browserLocale, $this->languages)) {
            Session::put('locale', $browserLocale);
            return $browserLocale;
        }

        // 4. Default to English
        return config('app.locale', 'en');
    }
}
