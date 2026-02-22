<?php

namespace Tests\Performance;

use App\Models\Glyph;
use App\Models\Tablet;
use App\Models\TabletRendering;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Database performance benchmarks.
 *
 * Run with: php artisan test --testsuite=Performance
 *
 * These tests measure query execution time and assert it stays within
 * acceptable thresholds. Thresholds are intentionally generous to account
 * for CI/test environment overhead.
 */
class DatabasePerformanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedTestData();
    }

    /** Fetch all glyphs with images — should be fast with eager loading */
    public function test_fetch_all_glyphs_with_images(): void
    {
        $start = hrtime(true);

        $glyphs = Glyph::with('images')->orderBy('barthel_code')->get();

        $ms = (hrtime(true) - $start) / 1_000_000;

        $this->assertGreaterThan(0, $glyphs->count(), 'Should return glyphs');
        $this->assertLessThan(500, $ms, "Fetching all glyphs with images took {$ms}ms (limit: 500ms)");

        $this->addToAssertionCount(1);
        fwrite(STDERR, "\n  [DB] fetch all glyphs with images: {$ms}ms\n");
    }

    /** Count tablet renderings — simple aggregate */
    public function test_count_tablet_renderings(): void
    {
        $start = hrtime(true);

        $count = TabletRendering::count();

        $ms = (hrtime(true) - $start) / 1_000_000;

        $this->assertIsInt($count);
        $this->assertLessThan(100, $ms, "COUNT query took {$ms}ms (limit: 100ms)");

        fwrite(STDERR, "\n  [DB] TabletRendering::count(): {$ms}ms (result: {$count})\n");
    }

    /** Complex JOIN — occurrence counts from alphabet() controller */
    public function test_occurrence_counts_join(): void
    {
        $start = hrtime(true);

        $result = DB::table('tablet_renderings')
            ->join('renderings', 'renderings.id', '=', 'tablet_renderings.rendering_id')
            ->selectRaw('renderings.glyph_id, count(*) as cnt')
            ->groupBy('renderings.glyph_id')
            ->pluck('cnt', 'glyph_id');

        $ms = (hrtime(true) - $start) / 1_000_000;

        $this->assertLessThan(300, $ms, "Occurrence JOIN took {$ms}ms (limit: 300ms)");

        fwrite(STDERR, "\n  [DB] occurrence counts JOIN: {$ms}ms\n");
    }

    /** Tablet with deeply nested relations — mirrors tablet() controller */
    public function test_tablet_with_deep_relations(): void
    {
        $tablet = Tablet::first();
        if (!$tablet) {
            $this->markTestSkipped('No tablets in test DB');
        }

        $start = hrtime(true);

        $loaded = Tablet::with([
            'lines' => fn ($q) => $q->orderBy('side')->orderBy('line'),
            'lines.tabletRenderings' => fn ($q) => $q->orderBy('position'),
            'lines.tabletRenderings.rendering.glyph.images',
            'lines.tabletRenderings.compoundGlyph.parts' => fn ($q) => $q->orderBy('order'),
            'lines.tabletRenderings.compoundGlyph.parts.glyph.images',
        ])->where('code', $tablet->code)->firstOrFail();

        $ms = (hrtime(true) - $start) / 1_000_000;

        $this->assertNotNull($loaded);
        $this->assertLessThan(2000, $ms, "Deep tablet load took {$ms}ms (limit: 2000ms)");

        fwrite(STDERR, "\n  [DB] tablet deep relations load: {$ms}ms\n");
    }

    /** Verify no N+1 on glyph listing */
    public function test_no_n_plus_one_on_glyph_listing(): void
    {
        $queryCount = 0;
        DB::listen(function () use (&$queryCount) {
            $queryCount++;
        });

        Glyph::with('images')->get();

        // Should be exactly 2 queries: one for glyphs, one for images (polymorphic)
        $this->assertLessThanOrEqual(3, $queryCount, "Expected ≤3 queries for Glyph::with('images'), got {$queryCount}");

        fwrite(STDERR, "\n  [DB] Glyph::with('images') query count: {$queryCount}\n");
    }

    private function seedTestData(): void
    {
        // Create minimal test data for performance measurements
        $tablet = Tablet::create(['code' => 'A', 'name' => 'Tablet A']);
        $line = $tablet->lines()->create(['side' => 0, 'line' => 1, 'direction' => 'ltr']);

        for ($i = 1; $i <= 50; $i++) {
            $code = str_pad($i, 3, '0', STR_PAD_LEFT);
            $glyph = Glyph::create(['barthel_code' => $code]);
            $glyph->images()->create(['path' => "glyphs/{$code}.GIF", 'type' => 'glyph', 'sort_order' => 0]);

            $rendering = $glyph->renderings()->create(['code' => $code]);
            $line->tabletRenderings()->create([
                'rendering_id' => $rendering->id,
                'position' => $i,
            ]);
        }
    }
}
