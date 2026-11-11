<?php

use App\Http\Controllers\PageController;
use App\Http\Controllers\SitemapController;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Route;
use Illuminate\View\Middleware\ShareErrorsFromSession;

// Public pages are stateless: no session or cookies, so Varnish can cache them.
Route::withoutMiddleware([
    EncryptCookies::class,
    AddQueuedCookiesToResponse::class,
    StartSession::class,
    ShareErrorsFromSession::class,
    ValidateCsrfToken::class,
])->group(function () {
    Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

    Route::get('/{page?}', [PageController::class, 'alphabet'])->name('alphabet')->where('page', '[1-9]');
    Route::get('/glyph/{code}', [PageController::class, 'glyph'])->name('glyph');
    Route::get('/tablets', [PageController::class, 'tablets'])->name('tablets');
    Route::get('/tablet/{code}', [PageController::class, 'tablet'])->name('tablet');

    Route::get('/ligatures/{page?}', [PageController::class, 'ligatures'])->name('ligatures')->where('page', '[0-9]+');
    Route::get('/renderings/{page?}', [PageController::class, 'renderings'])->name('renderings')->where('page', '[1-9]');
    Route::get('/renderings/{code}', [PageController::class, 'rendering'])->name('rendering')->where('code', '\d{3}');
    Route::get('/lines/{tablet?}', [PageController::class, 'lines'])->name('lines')->where('tablet', '[A-Z]');
    Route::get('/line/{tablet}/{side}/{line}', [PageController::class, 'line'])->name('line');

    Route::get('/about', [PageController::class, 'about'])->name('about');
});
