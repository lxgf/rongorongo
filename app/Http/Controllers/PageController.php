<?php

namespace App\Http\Controllers;

use App\Models\CompoundGlyph;
use App\Models\Glyph;
use App\Models\Tablet;
use App\Models\TabletLine;
use App\Models\TabletRendering;
use Illuminate\Support\Facades\DB;

class PageController extends Controller
{
    public function alphabet()
    {
        $glyphs = Glyph::with('images')
            ->orderBy('barthel_code')
            ->get();

        $occurrenceCounts = DB::table('tablet_renderings')
            ->join('renderings', 'renderings.id', '=', 'tablet_renderings.rendering_id')
            ->selectRaw('renderings.glyph_id, count(*) as cnt')
            ->groupBy('renderings.glyph_id')
            ->pluck('cnt', 'glyph_id');

        $groups = $glyphs->groupBy(fn ($g) => intdiv(intval($g->barthel_code), 100) * 100);

        $stats = [
            'glyphs' => $glyphs->count(),
            'tablets' => Tablet::count(),
            'occurrences' => TabletRendering::count(),
        ];

        return view('alphabet', compact('groups', 'occurrenceCounts', 'stats'));
    }

    public function glyph(string $code)
    {
        $glyph = Glyph::with([
            'images',
            'renderings' => fn ($q) => $q->withCount('tabletRenderings')->orderBy('code'),
        ])
            ->where('barthel_code', $code)
            ->firstOrFail();

        $occurrences = TabletRendering::whereIn('rendering_id', $glyph->renderings->pluck('id'))
            ->with(['tabletLine.tablet', 'rendering', 'images'])
            ->orderBy('tablet_line_id')
            ->orderBy('position')
            ->get();

        $ligatures = CompoundGlyph::whereHas('parts', fn ($q) => $q->where('glyph_id', $glyph->id))
            ->with(['parts' => fn ($q) => $q->orderBy('order'), 'parts.glyph.images'])
            ->orderBy('code')
            ->get();

        $prev = Glyph::where('barthel_code', '<', $code)->orderByDesc('barthel_code')->first();
        $next = Glyph::where('barthel_code', '>', $code)->orderBy('barthel_code')->first();

        return view('glyph', compact('glyph', 'occurrences', 'ligatures', 'prev', 'next'));
    }

    public function tablets()
    {
        $tablets = Tablet::withCount('lines')
            ->orderBy('code')
            ->get();

        $renderingCounts = DB::table('tablet_renderings')
            ->join('tablet_lines', 'tablet_lines.id', '=', 'tablet_renderings.tablet_line_id')
            ->selectRaw('tablet_lines.tablet_id, count(*) as cnt')
            ->groupBy('tablet_lines.tablet_id')
            ->pluck('cnt', 'tablet_id');

        return view('tablets', compact('tablets', 'renderingCounts'));
    }

    public function tablet(string $code)
    {
        $tablet = Tablet::with([
            'images' => fn ($q) => $q->where('type', 'photo')->orderBy('sort_order'),
            'lines' => fn ($q) => $q->orderBy('side')->orderBy('line'),
            'lines.tabletRenderings' => fn ($q) => $q->orderBy('position'),
            'lines.tabletRenderings.images',
            'lines.tabletRenderings.rendering.glyph.images',
            'lines.tabletRenderings.compoundGlyph.parts' => fn ($q) => $q->orderBy('order'),
            'lines.tabletRenderings.compoundGlyph.parts.glyph.images',
        ])->where('code', $code)->firstOrFail();

        return view('tablet', compact('tablet'));
    }

    public function renderings()
    {
        $glyphs = Glyph::with([
            'images',
            'renderings' => fn ($q) => $q->withCount('tabletRenderings')->orderBy('code'),
        ])
            ->has('renderings')
            ->orderBy('barthel_code')
            ->get();

        $groups = $glyphs->groupBy(fn ($g) => intdiv(intval($g->barthel_code), 100) * 100);

        $totalRenderings = $glyphs->sum(fn ($g) => $g->renderings->count());

        return view('renderings', compact('groups', 'totalRenderings'));
    }

    public function rendering(string $code)
    {
        $glyph = Glyph::with([
            'images',
            'renderings' => fn ($q) => $q->orderBy('code'),
            'renderings.tabletRenderings' => fn ($q) => $q->orderBy('tablet_line_id')->orderBy('position'),
            'renderings.tabletRenderings.images',
            'renderings.tabletRenderings.tabletLine.tablet',
        ])
            ->where('barthel_code', $code)
            ->firstOrFail();

        $totalOccurrences = $glyph->renderings->sum(fn ($r) => $r->tabletRenderings->count());

        $prev = Glyph::where('barthel_code', '<', $code)->has('renderings')->orderByDesc('barthel_code')->first();
        $next = Glyph::where('barthel_code', '>', $code)->has('renderings')->orderBy('barthel_code')->first();

        return view('rendering', compact('glyph', 'totalOccurrences', 'prev', 'next'));
    }

    public function about()
    {
        $stats = [
            'tablets' => Tablet::count(),
            'lines' => \App\Models\TabletLine::count(),
            'glyphs' => Glyph::count(),
            'renderings' => \App\Models\Rendering::count(),
            'ligatures' => CompoundGlyph::count(),
            'occurrences' => TabletRendering::count(),
        ];

        return view('about', compact('stats'));
    }

    public function ligatures()
    {
        $ligatures = CompoundGlyph::with([
            'parts' => fn ($q) => $q->orderBy('order'),
            'parts.glyph.images',
            'images',
        ])
            ->withCount('tabletRenderings')
            ->orderBy('code')
            ->get();

        return view('ligatures', compact('ligatures'));
    }

    public function lines()
    {
        $tablets = Tablet::orderBy('code')->get();

        $query = TabletLine::with('tablet')
            ->withCount('tabletRenderings')
            ->orderBy('tablet_id')
            ->orderBy('side')
            ->orderBy('line');

        if (request('tablet')) {
            $query->whereHas('tablet', fn ($q) => $q->where('code', request('tablet')));
        }

        $lines = $query->get()->groupBy(fn ($l) => $l->tablet->code);

        return view('lines', compact('lines', 'tablets'));
    }

    public function line(string $tabletCode, string $side, int $lineNumber)
    {
        $sideValue = in_array($side, ['b', 'v']) ? 1 : 0;

        $tablet = Tablet::where('code', $tabletCode)->firstOrFail();

        $line = TabletLine::with([
            'tabletRenderings' => fn ($q) => $q->orderBy('position'),
            'tabletRenderings.images',
            'tabletRenderings.rendering.glyph.images',
            'tabletRenderings.compoundGlyph.parts' => fn ($q) => $q->orderBy('order'),
            'tabletRenderings.compoundGlyph.parts.glyph.images',
        ])
            ->where('tablet_id', $tablet->id)
            ->where('side', $sideValue)
            ->where('line', $lineNumber)
            ->firstOrFail();

        $prev = TabletLine::where('tablet_id', $tablet->id)
            ->where(fn ($q) => $q
                ->where('side', '<', $sideValue)
                ->orWhere(fn ($q2) => $q2->where('side', $sideValue)->where('line', '<', $lineNumber))
            )
            ->orderByDesc('side')
            ->orderByDesc('line')
            ->first();

        $next = TabletLine::where('tablet_id', $tablet->id)
            ->where(fn ($q) => $q
                ->where('side', '>', $sideValue)
                ->orWhere(fn ($q2) => $q2->where('side', $sideValue)->where('line', '>', $lineNumber))
            )
            ->orderBy('side')
            ->orderBy('line')
            ->first();

        return view('line', compact('tablet', 'line', 'prev', 'next'));
    }
}
