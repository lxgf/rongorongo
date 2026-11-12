<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\GlyphResource;
use App\Http\Resources\Api\LigatureResource;
use App\Http\Resources\Api\LineResource;
use App\Http\Resources\Api\OccurrenceResource;
use App\Http\Resources\Api\RenderingResource;
use App\Http\Resources\Api\TabletResource;
use App\Models\CompoundGlyph;
use App\Models\Glyph;
use App\Models\Rendering;
use App\Models\Tablet;
use App\Models\TabletLine;
use App\Models\TabletRendering;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

/**
 * Public read-only JSON API for every level of the corpus:
 * tablets → lines → occurrences, plus glyphs, renderings and ligatures.
 */
class CorpusApiController extends Controller
{
    private const JSON_FLAGS = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;

    private const CORPUS_CACHE_TTL = 600;

    private const PER_PAGE_DEFAULT = 100;

    private const PER_PAGE_MAX = 1000;

    public function index(): JsonResponse
    {
        return $this->json([
            'name' => 'Rongorongo corpus API',
            'version' => 'v1',
            'description' => 'Free read-only JSON API for the rongorongo corpus: tablets, lines, glyph occurrences, glyphs, renderings and ligatures.',
            'attribution' => __('front.colophon.attribution', [], 'en'),
            'rate_limit' => '120 requests per minute per IP',
            'stats' => Tablet::corpusStats(),
            'endpoints' => [
                'corpus' => route('api.corpus'),
                'tablets' => route('api.tablets'),
                'tablet' => route('api.tablet', 'A'),
                'line' => route('api.line', ['A', 'a', 1]),
                'glyphs' => route('api.glyphs'),
                'glyph' => route('api.glyph', '001'),
                'renderings' => route('api.renderings'),
                'rendering' => route('api.rendering', '001a'),
                'ligatures' => route('api.ligatures'),
                'ligature' => route('api.ligature', CompoundGlyph::orderBy('code')->value('code') ?? '000.000'),
                'occurrences' => route('api.occurrences').'?tablet=A&glyph=001&page=1&per_page=100',
            ],
        ]);
    }

    /**
     * Whole corpus in one document (all levels).
     * Cached as an encoded JSON string: a serialized PHP array of the corpus does not fit in memory.
     */
    public function corpus(Request $request): Response
    {
        $key = 'api:v1:corpus:'.$request->getSchemeAndHttpHost();

        $json = Cache::remember($key, self::CORPUS_CACHE_TTL, function () use ($request) {
            $tablets = Tablet::with([
                'images' => fn ($q) => $q->where('type', 'photo')->orderBy('sort_order'),
                'lines' => fn ($q) => $q->orderBy('side')->orderBy('line'),
                ...$this->occurrenceRelations('lines.tabletRenderings'),
            ])->orderBy('code')->get();

            $tablets->each(fn ($tablet) => $tablet->lines->each->setRelation('tablet', $tablet));

            return json_encode([
                'generated_at' => now()->toIso8601String(),
                'stats' => Tablet::corpusStats(),
                'tablets' => TabletResource::collection($tablets)->resolve($request),
                'glyphs' => GlyphResource::collection($this->glyphList())->resolve($request),
                'renderings' => RenderingResource::collection($this->renderingList())->resolve($request),
                'ligatures' => LigatureResource::collection($this->ligatureList())->resolve($request),
            ], self::JSON_FLAGS | JSON_THROW_ON_ERROR);
        });

        return response($json, 200, ['Content-Type' => 'application/json']);
    }

    public function tablets(Request $request): JsonResponse
    {
        $tablets = Tablet::withCount(['lines', 'occurrences'])->orderBy('code')->get();

        return $this->json(['data' => TabletResource::collection($tablets)->resolve($request)]);
    }

    public function tablet(Request $request, string $code): JsonResponse
    {
        $tablet = Tablet::withCount(['lines', 'occurrences'])
            ->with([
                'images' => fn ($q) => $q->where('type', 'photo')->orderBy('sort_order'),
                'lines' => fn ($q) => $q->withCount('tabletRenderings')->orderBy('side')->orderBy('line'),
            ])
            ->where('code', $code)
            ->firstOrFail();

        $tablet->lines->each->setRelation('tablet', $tablet);

        return $this->json(['data' => (new TabletResource($tablet))->resolve($request)]);
    }

    public function line(Request $request, string $code, string $side, int $line): JsonResponse
    {
        $tablet = Tablet::where('code', $code)->firstOrFail();

        $tabletLine = TabletLine::with($this->occurrenceRelations('tabletRenderings'))
            ->withCount('tabletRenderings')
            ->where('tablet_id', $tablet->id)
            ->where('side', TabletLine::sideFromParam($side))
            ->where('line', $line)
            ->firstOrFail();

        $tabletLine->setRelation('tablet', $tablet);

        return $this->json(['data' => (new LineResource($tabletLine))->resolve($request)]);
    }

    public function glyphs(Request $request): JsonResponse
    {
        return $this->json(['data' => GlyphResource::collection($this->glyphList())->resolve($request)]);
    }

    public function glyph(Request $request, string $code): JsonResponse
    {
        $glyph = Glyph::withCount(['renderings', 'occurrences'])
            ->with([
                'images',
                'renderings' => fn ($q) => $q->with('glyph')->withCount('tabletRenderings')->orderBy('code'),
                ...$this->occurrenceRelations('occurrences', withLocation: true),
            ])
            ->where('barthel_code', $code)
            ->firstOrFail();

        $glyph->setRelation('ligatures', CompoundGlyph::whereHas('parts', fn ($q) => $q->where('glyph_id', $glyph->id))
            ->with('parts.glyph')
            ->withCount('tabletRenderings')
            ->orderBy('code')
            ->get());

        return $this->json(['data' => (new GlyphResource($glyph))->resolve($request)]);
    }

    public function renderings(Request $request): JsonResponse
    {
        return $this->json(['data' => RenderingResource::collection($this->renderingList())->resolve($request)]);
    }

    public function rendering(Request $request, string $code): JsonResponse
    {
        $rendering = Rendering::with(['glyph', ...$this->occurrenceRelations('tabletRenderings', withLocation: true)])
            ->withCount('tabletRenderings')
            ->where('code', $code)
            ->firstOrFail();

        return $this->json(['data' => (new RenderingResource($rendering))->resolve($request)]);
    }

    public function ligatures(Request $request): JsonResponse
    {
        return $this->json(['data' => LigatureResource::collection($this->ligatureList())->resolve($request)]);
    }

    public function ligature(Request $request, string $code): JsonResponse
    {
        $ligature = CompoundGlyph::with(['parts.glyph', ...$this->occurrenceRelations('tabletRenderings', withLocation: true)])
            ->withCount('tabletRenderings')
            ->where('code', $code)
            ->firstOrFail();

        return $this->json(['data' => (new LigatureResource($ligature))->resolve($request)]);
    }

    /** All occurrences, paginated, filterable by tablet, glyph, rendering and ligature. */
    public function occurrences(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'tablet' => ['nullable', 'string', 'max:5'],
            'glyph' => ['nullable', 'string', 'max:10'],
            'rendering' => ['nullable', 'string', 'max:10'],
            'ligature' => ['nullable', 'string', 'max:30'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:'.self::PER_PAGE_MAX],
        ]);

        $occurrences = TabletRendering::query()
            ->select('tablet_renderings.*')
            ->join('tablet_lines', 'tablet_lines.id', '=', 'tablet_renderings.tablet_line_id')
            ->join('tablets', 'tablets.id', '=', 'tablet_lines.tablet_id')
            ->when($filters['tablet'] ?? null, fn ($q, $v) => $q->where('tablets.code', $v))
            ->when($filters['glyph'] ?? null, fn ($q, $v) => $q->whereHas('rendering.glyph', fn ($g) => $g->where('barthel_code', $v)))
            ->when($filters['rendering'] ?? null, fn ($q, $v) => $q->whereHas('rendering', fn ($r) => $r->where('code', $v)))
            ->when($filters['ligature'] ?? null, fn ($q, $v) => $q->whereHas('compoundGlyph', fn ($c) => $c->where('code', $v)))
            ->with($this->occurrenceRelations(null, withLocation: true))
            ->orderBy('tablets.code')
            ->orderBy('tablet_lines.side')
            ->orderBy('tablet_lines.line')
            ->orderBy('tablet_renderings.position')
            ->paginate($filters['per_page'] ?? self::PER_PAGE_DEFAULT)
            ->withQueryString();

        return $this->json([
            'data' => OccurrenceResource::collection($occurrences->getCollection())->resolve($request),
            'meta' => [
                'current_page' => $occurrences->currentPage(),
                'last_page' => $occurrences->lastPage(),
                'per_page' => $occurrences->perPage(),
                'total' => $occurrences->total(),
            ],
            'links' => [
                'first' => $occurrences->url(1),
                'last' => $occurrences->url($occurrences->lastPage()),
                'prev' => $occurrences->previousPageUrl(),
                'next' => $occurrences->nextPageUrl(),
            ],
        ]);
    }

    /**
     * Eager-load map for occurrences under the given relation path (null = the occurrence itself).
     *
     * @return array<string|int, mixed>
     */
    private function occurrenceRelations(?string $path, bool $withLocation = false): array
    {
        $prefix = $path ? "{$path}." : '';
        $relations = [
            "{$prefix}images" => fn ($q) => $q->orderBy('sort_order')->orderBy('path'),
            "{$prefix}rendering.glyph",
            "{$prefix}compoundGlyph.parts.glyph",
        ];

        if ($path) {
            $relations[$path] = fn ($q) => $q->orderBy('tablet_line_id')->orderBy('position');
        }

        if ($withLocation) {
            $relations[] = "{$prefix}tabletLine.tablet";
        }

        return $relations;
    }

    private function glyphList(): Collection
    {
        return Glyph::with('images')->withCount(['renderings', 'occurrences'])->orderBy('barthel_code')->get();
    }

    private function renderingList(): Collection
    {
        return Rendering::with('glyph')->withCount('tabletRenderings')->orderBy('code')->get();
    }

    private function ligatureList(): Collection
    {
        return CompoundGlyph::with('parts.glyph')->withCount('tabletRenderings')->orderBy('code')->get();
    }



    private function json(array $data): JsonResponse
    {
        return response()->json($data, 200, [], self::JSON_FLAGS);
    }
}
