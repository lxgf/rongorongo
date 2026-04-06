<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();

        // ru.rongorongo.top → Russian
        if (str_starts_with($host, 'ru.')) {
            app()->setLocale('ru');
        } else {
            // Fallback to session for local dev, default to EN
            $locale = session('locale', config('app.locale'));
            if (array_key_exists($locale, config('app.supported_locales', []))) {
                app()->setLocale($locale);
            }
        }

        return $next($request);
    }
}
