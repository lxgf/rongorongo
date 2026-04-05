<?php

use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PageController::class, 'alphabet'])->name('alphabet');
Route::get('/glyph/{code}', [PageController::class, 'glyph'])->name('glyph');
Route::get('/tablets', [PageController::class, 'tablets'])->name('tablets');
Route::get('/tablet/{code}', [PageController::class, 'tablet'])->name('tablet');

Route::get('/ligatures', [PageController::class, 'ligatures'])->name('ligatures');
Route::get('/renderings', [PageController::class, 'renderings'])->name('renderings');
Route::get('/renderings/{code}', [PageController::class, 'rendering'])->name('rendering');
Route::get('/lines', [PageController::class, 'lines'])->name('lines');
Route::get('/line/{tablet}/{side}/{line}', [PageController::class, 'line'])->name('line');

Route::get('/about', [PageController::class, 'about'])->name('about');

Route::get('/locale/{locale}', function (string $locale) {
    if (array_key_exists($locale, config('app.supported_locales', []))) {
        session()->put('locale', $locale);
    }

    return redirect()->back();
})->name('locale.switch');
