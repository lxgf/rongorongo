<?php

use App\Http\Controllers\Api\CorpusApiController;
use Illuminate\Support\Facades\Route;

// Public read-only JSON API (stateless, CORS open, rate limited)
Route::prefix('v1')->middleware('throttle:api')->name('api.')->group(function () {
    Route::get('/', [CorpusApiController::class, 'index'])->name('index');
    Route::get('/corpus', [CorpusApiController::class, 'corpus'])->name('corpus');

    Route::get('/tablets', [CorpusApiController::class, 'tablets'])->name('tablets');
    Route::get('/tablets/{code}', [CorpusApiController::class, 'tablet'])->name('tablet');
    Route::get('/tablets/{code}/lines/{side}/{line}', [CorpusApiController::class, 'line'])
        ->where(['side' => '[a-hrv]|[0-7]', 'line' => '\d+'])
        ->name('line');

    Route::get('/glyphs', [CorpusApiController::class, 'glyphs'])->name('glyphs');
    Route::get('/glyphs/{code}', [CorpusApiController::class, 'glyph'])->name('glyph');

    Route::get('/renderings', [CorpusApiController::class, 'renderings'])->name('renderings');
    Route::get('/renderings/{code}', [CorpusApiController::class, 'rendering'])->name('rendering');

    Route::get('/ligatures', [CorpusApiController::class, 'ligatures'])->name('ligatures');
    Route::get('/ligatures/{code}', [CorpusApiController::class, 'ligature'])->name('ligature');

    Route::get('/occurrences', [CorpusApiController::class, 'occurrences'])->name('occurrences');
});
