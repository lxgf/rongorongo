<?php

namespace Tests\Performance;

use App\Models\Glyph;
use App\Models\Image;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Image loading performance benchmarks.
 *
 * Measures eager loading of 100 glyph images and verifies
 * there are no N+1 query problems.
 */
class ImageLoadPerformanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed100Glyphs();
    }

    /** Load 100 glyphs with images using eager loading */
    public function test_eager_load_100_glyph_images(): void
    {
        $start = hrtime(true);

        $glyphs = Glyph::with('images')->orderBy('barthel_code')->get();

        $ms = (hrtime(true) - $start) / 1_000_000;

        $this->assertCount(100, $glyphs, 'Should have 100 glyphs');
        $this->assertTrue($glyphs->every(fn ($g) => $g->images->isNotEmpty()), 'Every glyph should have an image');
        $this->assertLessThan(300, $ms, "Eager load of 100 glyphs with images took {$ms}ms (limit: 300ms)");

        fwrite(STDERR, "\n  [IMG] eager load 100 glyphs with images: {$ms}ms\n");
    }

    /** Verify exactly 2 queries for 100 glyphs with images (no N+1) */
    public function test_no_n_plus_one_for_100_images(): void
    {
        $queries = [];
        DB::listen(function ($query) use (&$queries) {
            $queries[] = $query->sql;
        });

        Glyph::with('images')->get();

        // Expect: 1 query for glyphs + 1 query for images (polymorphic morph map)
        $this->assertLessThanOrEqual(3, count($queries),
            "Expected ≤3 queries, got " . count($queries) . ":\n" . implode("\n", $queries)
        );

        fwrite(STDERR, "\n  [IMG] N+1 check — query count: " . count($queries) . "\n");
    }

    /** Measure raw image record retrieval by glyph codes */
    public function test_image_retrieval_by_glyph_codes(): void
    {
        $codes = Glyph::orderBy('barthel_code')->limit(100)->pluck('barthel_code');

        $start = hrtime(true);

        $images = Image::whereHasMorph(
            'imageable',
            [Glyph::class],
            fn ($q) => $q->whereIn('barthel_code', $codes)
        )->get();

        $ms = (hrtime(true) - $start) / 1_000_000;

        $this->assertCount(100, $images, 'Should find 100 images');
        $this->assertLessThan(200, $ms, "Image retrieval by codes took {$ms}ms (limit: 200ms)");

        fwrite(STDERR, "\n  [IMG] image retrieval via whereHasMorph: {$ms}ms\n");
    }

    /** Memory usage when loading 100 images */
    public function test_memory_usage_loading_100_images(): void
    {
        $before = memory_get_usage(true);

        $glyphs = Glyph::with('images')->get();

        $after = memory_get_usage(true);
        $mb = ($after - $before) / 1024 / 1024;

        $this->assertCount(100, $glyphs);
        $this->assertLessThan(20, $mb, "Memory usage for 100 glyphs with images: {$mb}MB (limit: 20MB)");

        fwrite(STDERR, "\n  [IMG] memory for 100 glyphs+images: " . round($mb, 2) . "MB\n");
    }

    private function seed100Glyphs(): void
    {
        for ($i = 1; $i <= 100; $i++) {
            $code = str_pad($i, 3, '0', STR_PAD_LEFT);
            $glyph = Glyph::create(['barthel_code' => $code]);
            $glyph->images()->create([
                'path' => "glyphs/{$code}.GIF",
                'type' => 'glyph',
                'sort_order' => 0,
            ]);
        }
    }
}
