<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = session('locale', config('app.locale'));

        if (array_key_exists($locale, config('app.supported_locales', []))) {
            app()->setLocale($locale);
        }

        return $next($request);
    }
}
