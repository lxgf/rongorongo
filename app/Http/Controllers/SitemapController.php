<?php

namespace App\Http\Controllers;

use App\Models\CompoundGlyph;
use App\Models\Glyph;
use App\Models\Tablet;
use App\Models\TabletLine;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $glyphs = Glyph::orderBy('barthel_code')->get();
        $tablets = Tablet::has('lines')->orderBy('code')->get();
        $lines = TabletLine::with('tablet')->orderBy('tablet_id')->orderBy('side')->orderBy('line')->get();

        // Alphabet pages
        $glyphGroups = $glyphs->groupBy(fn ($g) => intdiv(intval($g->barthel_code), 100) * 100);
        $alphabetPages = $glyphGroups->keys()->values()->count();

        // Rendering pages
        $renderingGroups = Glyph::has('renderings')
            ->selectRaw("(CAST(barthel_code AS INTEGER) / 100) * 100 as base")
            ->groupByRaw("(CAST(barthel_code AS INTEGER) / 100) * 100")
            ->orderBy('base')
            ->pluck('base');

        // Ligature pages
        $ligatureTotal = CompoundGlyph::count();
        $ligaturePages = (int) ceil($ligatureTotal / 25);

        $content = view('sitemap', compact(
            'glyphs',
            'tablets',
            'lines',
            'alphabetPages',
            'renderingGroups',
            'ligaturePages',
        ))->render();

        return response($content, 200)->header('Content-Type', 'text/xml');
    }
}
