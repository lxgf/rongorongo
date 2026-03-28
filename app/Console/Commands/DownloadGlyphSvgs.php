<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

class DownloadGlyphSvgs extends Command
{
    protected $signature = 'rongorongo:download-svgs {--force : Overwrite existing files}';
    protected $description = 'Download Barthel SVG glyphs from kohaumotu.org into public/glyphs/';

    private const TABLETS = ['A', 'B', 'C', 'D', 'E', 'G', 'H', 'K', 'N', 'P', 'Q', 'R', 'S'];

    private const BASE_URL = 'https://kohaumotu.org/Rongorongo_new/views/glyph_list.php';

    public function handle(): int
    {
        $force = $this->option('force');
        $dir = public_path('glyphs');
        File::ensureDirectoryExists($dir);

        $saved = 0;
        $skipped = 0;
        $seen = [];

        foreach (self::TABLETS as $tablet) {
            $this->info("Tablet {$tablet}...");

            $html = $this->fetchPage($tablet);
            if (! $html) {
                $this->error("  Failed to fetch page for tablet {$tablet}");
                continue;
            }

            $glyphs = $this->extractSvgGlyphs($html);
            $this->line("  Found " . count($glyphs) . " SVG glyphs");

            foreach ($glyphs as $code => $svgContent) {
                if (isset($seen[$code])) {
                    continue; // already saved from an earlier tablet
                }
                $seen[$code] = true;

                $filename = "{$code}.svg";
                $filepath = "{$dir}/{$filename}";

                if (file_exists($filepath) && ! $force) {
                    $skipped++;
                    continue;
                }

                file_put_contents($filepath, $svgContent);
                $saved++;
            }

            // polite delay
            usleep(300_000);
        }

        $this->newLine();
        $this->info("=== Done ===");
        $this->info("Unique Barthel codes found: " . count($seen));
        $this->info("Saved:   {$saved}");
        $this->info("Skipped: {$skipped}");

        return self::SUCCESS;
    }

    private function fetchPage(string $tablet): ?string
    {
        try {
            $response = Http::withoutVerifying()
                ->withHeaders(['User-Agent' => 'RongorongoProject/1.0 (d.shaludnyov@gmail.com)'])
                ->timeout(30)
                ->get(self::BASE_URL, [
                    'item' => $tablet,
                    'type' => 'b',
                    'show' => 'id',
                ]);

            return $response->successful() ? $response->body() : null;
        } catch (\Exception $e) {
            $this->error("  HTTP error: {$e->getMessage()}");
            return null;
        }
    }

    /**
     * Extract inline SVG glyphs mapped by Barthel code.
     * Structure: <h4><a ...>CODE [N]</a></h4> followed by <p><svg>...</svg>...
     * Take the first SVG after each h4 as the representative glyph.
     *
     * @return array<string, string> barthel_code => standalone SVG content
     */
    private function extractSvgGlyphs(string $html): array
    {
        $glyphs = [];

        // Match: <h4><a ...>CODE [N]</a></h4> ... <svg ...>...</svg>
        // The h4 contains the Barthel code, the first SVG after it is the glyph
        if (! preg_match_all(
            '/<h4><a[^>]*>(\d{3})\s*\[\d+\]<\/a><\/h4>\s*<p>\s*(<svg\s[^>]*>.*?<\/svg>)/s',
            $html,
            $matches,
            PREG_SET_ORDER
        )) {
            return $glyphs;
        }

        foreach ($matches as $match) {
            $barthelCode = $match[1];
            $svgBlock = $match[2];

            // Keep first occurrence only
            if (isset($glyphs[$barthelCode])) {
                continue;
            }

            $glyphs[$barthelCode] = $this->cleanSvg($svgBlock);
        }

        return $glyphs;
    }

    /**
     * Make the inline SVG a proper standalone file.
     * - Add xmlns attribute
     * - Add XML declaration
     * - Remove the id from the path (it references a specific tablet position)
     */
    private function cleanSvg(string $svg): string
    {
        // Add xmlns if missing
        if (! str_contains($svg, 'xmlns')) {
            $svg = preg_replace('/<svg\s/', '<svg xmlns="http://www.w3.org/2000/svg" ', $svg);
        }

        // Remove position-specific id from path
        $svg = preg_replace('/\s*id="glyph[^"]*"/', '', $svg);

        return '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . $svg . "\n";
    }
}
