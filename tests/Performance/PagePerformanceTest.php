<?php

namespace Tests\Performance;

use App\Models\CompoundGlyph;
use App\Models\CompoundGlyphPart;
use App\Models\Glyph;
use App\Models\Rendering;
use App\Models\Tablet;
use App\Models\TabletLine;
use App\Models\TabletRendering;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Page response time benchmarks.
 *
 * Measures time-to-first-byte for each frontend route.
 * These are application-level measurements (no network/Varnish overhead).
 */
class PagePerformanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedTestData();
    }

    /** Home page — alphabet listing */
    public function test_alphabet_page_response_time(): void
    {
        $start = hrtime(true);
        $response = $this->get('/');
        $ms = (hrtime(true) - $start) / 1_000_000;

        $response->assertStatus(200);
        $this->assertLessThan(1000, $ms, "/ took {$ms}ms (limit: 1000ms)");

        fwrite(STDERR, "\n  [PAGE] GET /: {$ms}ms\n");
    }

    /** Tablets list */
    public function test_tablets_page_response_time(): void
    {
        $start = hrtime(true);
        $response = $this->get('/tablets');
        $ms = (hrtime(true) - $start) / 1_000_000;

        $response->assertStatus(200);
        $this->assertLessThan(1000, $ms, "/tablets took {$ms}ms (limit: 1000ms)");

        fwrite(STDERR, "\n  [PAGE] GET /tablets: {$ms}ms\n");
    }

    /** Single tablet — heaviest page (deep nested relations) */
    public function test_single_tablet_page_response_time(): void
    {
        $start = hrtime(true);
        $response = $this->get('/tablet/A');
        $ms = (hrtime(true) - $start) / 1_000_000;

        $response->assertStatus(200);
        $this->assertLessThan(2000, $ms, "/tablet/A took {$ms}ms (limit: 2000ms)");

        fwrite(STDERR, "\n  [PAGE] GET /tablet/A: {$ms}ms\n");
    }

    /** Single glyph page */
    public function test_glyph_page_response_time(): void
    {
        $start = hrtime(true);
        $response = $this->get('/glyph/001');
        $ms = (hrtime(true) - $start) / 1_000_000;

        $response->assertStatus(200);
        $this->assertLessThan(1000, $ms, "/glyph/001 took {$ms}ms (limit: 1000ms)");

        fwrite(STDERR, "\n  [PAGE] GET /glyph/001: {$ms}ms\n");
    }

    /** Ligatures page */
    public function test_ligatures_page_response_time(): void
    {
        $start = hrtime(true);
        $response = $this->get('/ligatures');
        $ms = (hrtime(true) - $start) / 1_000_000;

        $response->assertStatus(200);
        $this->assertLessThan(1000, $ms, "/ligatures took {$ms}ms (limit: 1000ms)");

        fwrite(STDERR, "\n  [PAGE] GET /ligatures: {$ms}ms\n");
    }

    /** Lines page */
    public function test_lines_page_response_time(): void
    {
        $start = hrtime(true);
        $response = $this->get('/lines');
        $ms = (hrtime(true) - $start) / 1_000_000;

        $response->assertStatus(200);
        $this->assertLessThan(2000, $ms, "/lines took {$ms}ms (limit: 2000ms)");

        fwrite(STDERR, "\n  [PAGE] GET /lines: {$ms}ms\n");
    }

    private function seedTestData(): void
    {
        $tablet = Tablet::create(['code' => 'A', 'name' => 'Tablet A']);

        $glyphs = [];
        $renderings = [];

        for ($i = 1; $i <= 30; $i++) {
            $code = str_pad($i, 3, '0', STR_PAD_LEFT);
            $glyph = Glyph::create(['barthel_code' => $code]);
            $glyph->images()->create(['path' => "glyphs/{$code}.GIF", 'type' => 'glyph', 'sort_order' => 0]);
            $rendering = $glyph->renderings()->create(['code' => $code]);
            $glyphs[$code] = $glyph;
            $renderings[$code] = $rendering;
        }

        // Create a compound glyph
        $compound = CompoundGlyph::create(['code' => '001.002']);
        CompoundGlyphPart::create(['compound_glyph_id' => $compound->id, 'glyph_id' => $glyphs['001']->id, 'order' => 1]);
        CompoundGlyphPart::create(['compound_glyph_id' => $compound->id, 'glyph_id' => $glyphs['002']->id, 'order' => 2]);

        // Two lines with renderings
        foreach ([0, 1] as $side) {
            $line = TabletLine::create([
                'tablet_id' => $tablet->id,
                'side' => $side,
                'line' => 1,
                'direction' => 'ltr',
            ]);

            foreach (range(1, 15) as $pos) {
                $code = str_pad($pos, 3, '0', STR_PAD_LEFT);
                TabletRendering::create([
                    'tablet_line_id' => $line->id,
                    'rendering_id' => $renderings[$code]->id,
                    'position' => $pos,
                ]);
            }

            // One compound position
            TabletRendering::create([
                'tablet_line_id' => $line->id,
                'compound_glyph_id' => $compound->id,
                'position' => 16,
            ]);
        }
    }
}
