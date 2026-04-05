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
    public function alphabet(?int $page = null)
    {
        // All groups (for navigation)
        $allGroups = Glyph::selectRaw("(CAST(barthel_code AS INTEGER) / 100) * 100 as base, count(*) as cnt")
            ->groupByRaw("(CAST(barthel_code AS INTEGER) / 100) * 100")
            ->orderBy('base')
            ->pluck('cnt', 'base');

        // Current page: 1-based index into groups
        $groupKeys = $allGroups->keys()->values();
        $currentIndex = $page ? $page - 1 : 0;

        if ($currentIndex < 0 || $currentIndex >= $groupKeys->count()) {
            abort(404);
        }

        $currentBase = $groupKeys[$currentIndex];

        // Fetch glyphs for current group
        $glyphs = Glyph::with('images')
            ->whereRaw("(CAST(barthel_code AS INTEGER) / 100) * 100 = ?", [$currentBase])
            ->orderBy('barthel_code')
            ->get();

        $glyphIds = $glyphs->pluck('id');

        $occurrenceCounts = DB::table('tablet_renderings')
            ->join('renderings', 'renderings.id', '=', 'tablet_renderings.rendering_id')
            ->whereIn('renderings.glyph_id', $glyphIds)
            ->selectRaw('renderings.glyph_id, count(*) as cnt')
            ->groupBy('renderings.glyph_id')
            ->pluck('cnt', 'glyph_id');

        $stats = [
            'glyphs' => Glyph::count(),
            'tablets' => Tablet::count(),
            'occurrences' => TabletRendering::count(),
        ];

        $rangeStart = str_pad($currentBase, 3, '0', STR_PAD_LEFT);
        $rangeEnd = str_pad($currentBase + 99, 3, '0', STR_PAD_LEFT);

        $pagination = [
            'groups' => $allGroups,
            'groupKeys' => $groupKeys,
            'currentIndex' => $currentIndex,
            'currentBase' => $currentBase,
            'totalPages' => $groupKeys->count(),
            'rangeStart' => $rangeStart,
            'rangeEnd' => $rangeEnd,
        ];

        return view('alphabet', compact('glyphs', 'occurrenceCounts', 'stats', 'pagination'));
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

    public function renderings(?int $page = null)
    {
        // All groups for navigation
        $allGroups = Glyph::has('renderings')
            ->selectRaw("(CAST(barthel_code AS INTEGER) / 100) * 100 as base, count(*) as cnt")
            ->groupByRaw("(CAST(barthel_code AS INTEGER) / 100) * 100")
            ->orderBy('base')
            ->pluck('cnt', 'base');

        $groupKeys = $allGroups->keys()->values();
        $currentIndex = $page ? $page - 1 : 0;

        if ($currentIndex < 0 || $currentIndex >= $groupKeys->count()) {
            abort(404);
        }

        $currentBase = $groupKeys[$currentIndex];

        $glyphs = Glyph::with([
            'images',
            'renderings' => fn ($q) => $q->withCount('tabletRenderings')->orderBy('code'),
        ])
            ->has('renderings')
            ->whereRaw("(CAST(barthel_code AS INTEGER) / 100) * 100 = ?", [$currentBase])
            ->orderBy('barthel_code')
            ->get();

        $totalRenderings = \App\Models\Rendering::count();

        $rangeStart = str_pad($currentBase, 3, '0', STR_PAD_LEFT);
        $rangeEnd = str_pad($currentBase + 99, 3, '0', STR_PAD_LEFT);

        $pagination = [
            'groups' => $allGroups,
            'groupKeys' => $groupKeys,
            'currentIndex' => $currentIndex,
            'rangeStart' => $rangeStart,
            'rangeEnd' => $rangeEnd,
            'totalPages' => $groupKeys->count(),
        ];

        return view('renderings', compact('glyphs', 'totalRenderings', 'pagination'));
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

    private const LIGATURES_PER_PAGE = 25;

    public function ligatures(?int $page = null)
    {
        $perPage = self::LIGATURES_PER_PAGE;
        $total = CompoundGlyph::count();
        $totalPages = (int) ceil($total / $perPage);
        $currentPage = $page ?? 1;

        if ($currentPage < 1 || $currentPage > $totalPages) {
            abort(404);
        }

        $ligatures = CompoundGlyph::with([
            'parts' => fn ($q) => $q->orderBy('order'),
            'parts.glyph.images',
            'images',
        ])
            ->withCount('tabletRenderings')
            ->orderBy('code')
            ->skip(($currentPage - 1) * $perPage)
            ->take($perPage)
            ->get();

        $pagination = [
            'currentPage' => $currentPage,
            'totalPages' => $totalPages,
            'total' => $total,
        ];

        return view('ligatures', compact('ligatures', 'pagination'));
    }

    public function lines(?string $tabletCode = null)
    {
        $tablets = Tablet::has('lines')
            ->withCount('lines')
            ->orderBy('code')
            ->get();

        $selectedTablet = null;
        $sides = collect();

        if ($tabletCode) {
            $selectedTablet = Tablet::where('code', $tabletCode)->firstOrFail();

            $sides = TabletLine::where('tablet_id', $selectedTablet->id)
                ->withCount('tabletRenderings')
                ->orderBy('side')
                ->orderBy('line')
                ->get()
                ->groupBy('side');
        }

        return view('lines', compact('tablets', 'selectedTablet', 'sides'));
    }

    public function line(string $tabletCode, string $side, int $lineNumber)
    {
        $sideMap = ['r' => 0, 'a' => 0, 'v' => 1, 'b' => 1, 'c' => 2, 'd' => 3, 'e' => 4, 'f' => 5, 'g' => 6, 'h' => 7];
        $sideValue = $sideMap[$side] ?? (ord($side) - ord('a'));

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
