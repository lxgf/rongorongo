<?php

use App\Http\Controllers\PageController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

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

Route::get('/locale/{locale}', function (string $locale) {
    if (array_key_exists($locale, config('app.supported_locales', []))) {
        session()->put('locale', $locale);
    }

    return redirect()->back();
})->name('locale.switch');
