<?php

namespace App\Console\Commands;

use App\Models\CompoundGlyph;
use App\Models\CompoundGlyphPart;
use App\Models\Glyph;
use App\Models\Image;
use App\Models\Rendering;
use App\Models\Tablet;
use App\Models\TabletLine;
use App\Models\TabletRendering;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

class ImportCorpus extends Command
{
    protected $signature = 'rongorongo:import-corpus {--force : Re-download existing SVG files}';
    protected $description = 'Scrape the Rongorongo corpus from kohaumotu.org and populate the database';

    private const BASE_URL = 'http://kohaumotu.org/Rongorongo_new/views/lines.php';
    private const TABLET_CODES = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X'];
    private const USER_AGENT = 'RongorongoProject/1.0 (d.shaludnyov@gmail.com)';

    private const RENDERING_VARIANTS = ['a', 'c', 'd', 'j', 'h', 'e', 'k', 'i', 'o', 'g'];

    private const MODIFIER_MAP = [
        'f' => 'is_inverted',
        'b' => 'is_mirrored',
        's' => 'is_small',
        'V' => 'is_enlarged',
        't' => 'is_truncated',
        'y' => 'is_distorted',
    ];

    private array $glyphCache = [];
    private array $renderingCache = [];
    private array $compoundCache = [];
    private array $glyphSvgSaved = []; // track which glyphs already have a representative SVG

    private int $svgCount = 0;

    public function handle(): int
    {
        $force = $this->option('force');

        $this->info('Phase 1: Fetching tablet pages from kohaumotu.org...');
        $tabletPages = $this->fetchAllTablets();

        if (empty($tabletPages)) {
            $this->error('No tablets fetched. Aborting.');
            return self::FAILURE;
        }

        $this->info("Fetched " . count($tabletPages) . " tablets.");
        $this->newLine();

        $this->info('Phase 2: Parsing HTML and importing into database...');

        DB::transaction(function () use ($tabletPages, $force) {
            $this->clearCorpusData();

            foreach ($tabletPages as $code => $html) {
                $this->processTablet($code, $html, $force);
            }
        });

        $this->newLine();
        $this->info('=== Import Complete ===');
        $this->info('Tablets processed: ' . count($tabletPages));
        $this->info('Lines:            ' . TabletLine::count());
        $this->info('Glyphs:           ' . Glyph::count());
        $this->info('Renderings:       ' . Rendering::count());
        $this->info('Compound glyphs:  ' . CompoundGlyph::count());
        $this->info('Compound parts:   ' . CompoundGlyphPart::count());
        $this->info('Occurrences:      ' . TabletRendering::count());
        $this->info('SVG files:        ' . $this->svgCount);

        return self::SUCCESS;
    }

    private function fetchAllTablets(): array
    {
        $pages = [];

        foreach (self::TABLET_CODES as $code) {
            $this->line("  Fetching tablet {$code}...");

            try {
                $response = Http::withoutVerifying()
                    ->withHeaders(['User-Agent' => self::USER_AGENT])
                    ->timeout(120)
                    ->get(self::BASE_URL, ['item' => $code, 'type' => 'b']);

                if ($response->successful()) {
                    $pages[$code] = $response->body();
                    $this->line("    <info>OK</info> (" . round(strlen($response->body()) / 1024) . " KB)");
                } else {
                    $this->error("    HTTP {$response->status()}");
                }
            } catch (\Exception $e) {
                $this->error("    {$e->getMessage()}");
            }

            usleep(300_000);
        }

        return $pages;
    }

    private function clearCorpusData(): void
    {
        $this->info('Clearing existing corpus data...');

        // Delete images linked to tablet renderings
        Image::where('imageable_type', TabletRendering::class)->delete();

        TabletRendering::query()->delete();
        TabletLine::query()->delete();
        CompoundGlyphPart::query()->delete();
        CompoundGlyph::query()->delete();
        Rendering::query()->delete();

        // Delete glyph images (rendering SVGs) but keep tablet photos
        Image::where('imageable_type', Glyph::class)->delete();
        Glyph::query()->delete();
    }

    private function processTablet(string $code, string $html, bool $force): void
    {
        $tablet = Tablet::where('code', $code)->first();
        if (! $tablet) {
            $this->warn("Tablet {$code} not in database, skipping.");
            return;
        }

        $this->info("Tablet {$code} ({$tablet->name}):");

        // Parse with regex — DOMDocument struggles with inline SVG in this HTML
        $lines = $this->parseLines($html, $code);

        $this->line("  Found " . count($lines) . " lines");

        foreach ($lines as $lineData) {
            $tabletLine = TabletLine::create([
                'tablet_id' => $tablet->id,
                'side' => $lineData['side'],
                'line' => $lineData['line'],
                'direction' => 'ltr',
            ]);

            $glyphCount = $this->parseSvgGlyphs(
                $lineData['svg'],
                $tabletLine,
                $code,
                $force,
            );

            $sideLabel = chr(ord('a') + $lineData['side']);
            $this->line("  {$sideLabel}{$lineData['line']}: {$glyphCount} glyphs");
        }
    }

    /**
     * Parse lines from the HTML page.
     * Returns array of ['side' => int, 'line' => int, 'svg' => string]
     *
     * Side mapping from h3 id letter:
     *   r/a → 0, v/b → 1, c → 2, d → 3, e → 4, f → 5, g → 6, h → 7
     */
    private function parseLines(string $html, string $tabletCode): array
    {
        $lines = [];

        // Side letter to number mapping
        $sideMap = ['r' => 0, 'a' => 0, 'v' => 1, 'b' => 1, 'c' => 2, 'd' => 3, 'e' => 4, 'f' => 5, 'g' => 6, 'h' => 7];

        // Find all h3 line headers and their following SVGs
        $pattern = '/<h3\s+id="([^"]+)"[^>]*>.*?<\/h3>\s*<p>Glyphs:\s*\d+<\/p>\s*(<svg\s[^>]*>.*?<\/svg>)/s';
        if (! preg_match_all($pattern, $html, $matches, PREG_SET_ORDER)) {
            return $lines;
        }

        foreach ($matches as $match) {
            $h3Id = $match[1];
            $svgContent = $match[2];

            // Parse h3 id: {TabletCode}{side_letter}{line_number}
            // Examples: "Ar01", "Av03", "Db06", "Xg02", "Ia1"
            if (! preg_match('/^' . preg_quote($tabletCode, '/') . '([a-z])(\d+)$/i', $h3Id, $idParts)) {
                continue;
            }

            $sideLetter = strtolower($idParts[1]);
            $side = $sideMap[$sideLetter] ?? (ord($sideLetter) - ord('a'));
            $lineNum = (int) $idParts[2];

            $lines[] = [
                'side' => $side,
                'line' => $lineNum,
                'svg' => $svgContent,
            ];
        }

        return $lines;
    }

    /**
     * Parse SVG content and create glyph records.
     *
     * Grouping strategy: everything between two consecutive <text class="ggn">
     * markers belongs to one position. If a group contains >1 path, it's a compound glyph.
     *
     * Returns number of positions created.
     */
    private function parseSvgGlyphs(string $svgContent, TabletLine $tabletLine, string $tabletCode, bool $force): int
    {
        // Extract parent SVG dimensions for viewBox
        $parentWidth = 1000;
        $parentHeight = 172;
        if (preg_match('/height="(\d+)"/', $svgContent, $hm)) {
            $parentHeight = (int) $hm[1];
        }
        if (preg_match('/width="(\d+)"/', $svgContent, $wm)) {
            $parentWidth = (int) $wm[1];
        }

        // Extract all child elements in order: path, text, line
        // Use greedy [^>]* for attributes (safe because > doesn't appear in SVG attributes)
        $pattern = '/<(path|text|line)\s([^>]*)(?:\/>|>([^<]*)<\/\1>)/s';
        preg_match_all($pattern, $svgContent, $elMatches, PREG_SET_ORDER);

        // Group elements by ggn position markers.
        // Each ggn text ends a position group.
        $positionGroups = [];
        $currentGroup = [];

        foreach ($elMatches as $el) {
            $tag = $el[1];
            $attrs = $el[2];
            $content = trim($el[3] ?? '');
            $isGgn = $tag === 'text' && str_contains($attrs, 'class="ggn"');

            $currentGroup[] = [
                'tag' => $tag,
                'attrs' => $attrs,
                'content' => $content,
                'is_ggn' => $isGgn,
            ];

            if ($isGgn) {
                $positionGroups[] = $currentGroup;
                $currentGroup = [];
            }
        }

        // Process each position group
        // Use our own position counter instead of trusting ggn values
        // (some tablets have duplicate ggn numbers in the source HTML)
        $sideChar = chr(ord('a') + $tabletLine->side);
        $lineStr = str_pad($tabletLine->line, 2, '0', STR_PAD_LEFT);
        $positionCount = 0;

        foreach ($positionGroups as $group) {
            // Extract parts: each path + following text = one part
            $parts = [];
            $pendingPathData = null;

            foreach ($group as $el) {
                if ($el['tag'] === 'path') {
                    if (preg_match('/\bd="([^"]+)"/', $el['attrs'], $dm)) {
                        $pendingPathData = $dm[1];
                    }
                } elseif ($el['tag'] === 'text' && ! $el['is_ggn']) {
                    $parts[] = [
                        'label' => $el['content'],
                        'pathData' => $pendingPathData,
                    ];
                    $pendingPathData = null;
                }
            }

            if (empty($parts)) {
                continue;
            }

            // Filter out skip labels
            $validParts = array_filter($parts, fn ($p) => $p['label'] !== '_' && $p['label'] !== '');
            if (empty($validParts)) {
                continue;
            }

            $validParts = array_values($validParts);
            $positionCount++;

            if (count($validParts) === 1) {
                $this->createSimpleGlyph(
                    $validParts[0]['label'],
                    $validParts[0]['pathData'],
                    $tabletLine,
                    $positionCount,
                    $tabletCode,
                    $sideChar,
                    $lineStr,
                    $parentWidth,
                    $parentHeight,
                    $force,
                );
            } else {
                $this->createCompoundGlyph(
                    $validParts,
                    $tabletLine,
                    $positionCount,
                    $tabletCode,
                    $sideChar,
                    $lineStr,
                    $parentWidth,
                    $parentHeight,
                    $force,
                );
            }
        }

        return $positionCount;
    }

    private function createSimpleGlyph(
        string $label,
        ?string $pathData,
        TabletLine $tabletLine,
        int $position,
        string $tabletCode,
        string $sideChar,
        string $lineStr,
        int $parentWidth,
        int $parentHeight,
        bool $force,
    ): void {
        $parsed = $this->parseLabel($label);
        if (! $parsed) {
            return;
        }

        $glyph = $this->getOrCreateGlyph($parsed['barthel_code']);
        $rendering = $this->getOrCreateRendering($parsed['rendering_code'], $glyph);

        $tr = TabletRendering::create([
            'tablet_line_id' => $tabletLine->id,
            'rendering_id' => $rendering->id,
            'compound_glyph_id' => null,
            'position' => $position,
            ...$parsed['modifiers'],
        ]);

        if ($pathData) {
            $posStr = str_pad($position, 3, '0', STR_PAD_LEFT);
            $relativePath = $this->saveSvgFile(
                $pathData,
                "svg/{$tabletCode}/{$sideChar}{$lineStr}-{$posStr}.svg",
                $parentWidth,
                $parentHeight,
                $force,
            );
            if ($relativePath) {
                $tr->images()->create([
                    'path' => $relativePath,
                    'type' => 'rendering-svg',
                    'sort_order' => 0,
                ]);
            }

            // Save representative glyph SVG (first occurrence wins)
            $this->saveGlyphSvg($parsed['barthel_code'], $pathData, $parentWidth, $parentHeight, $force);
        }
    }

    private function createCompoundGlyph(
        array $parts,
        TabletLine $tabletLine,
        int $position,
        string $tabletCode,
        string $sideChar,
        string $lineStr,
        int $parentWidth,
        int $parentHeight,
        bool $force,
    ): void {
        $renderingCodes = [];
        $glyphs = [];
        $allModifiers = [
            'is_inverted' => false,
            'is_mirrored' => false,
            'is_small' => false,
            'is_enlarged' => false,
            'is_truncated' => false,
            'is_distorted' => false,
            'is_uncertain' => false,
            'is_nonstandard' => false,
        ];
        $svgPaths = [];

        foreach ($parts as $index => $part) {
            if ($part['label'] === '_' || $part['label'] === '') {
                continue;
            }

            $parsed = $this->parseLabel($part['label']);
            if (! $parsed) {
                continue;
            }

            $glyph = $this->getOrCreateGlyph($parsed['barthel_code']);
            $renderingCodes[] = $parsed['rendering_code'];
            $glyphs[] = $glyph;

            foreach ($parsed['modifiers'] as $key => $value) {
                if ($value) {
                    $allModifiers[$key] = true;
                }
            }

            if ($part['pathData']) {
                $posStr = str_pad($position, 3, '0', STR_PAD_LEFT);
                $partNum = $index + 1;
                $svgPaths[] = $this->saveSvgFile(
                    $part['pathData'],
                    "svg/{$tabletCode}/{$sideChar}{$lineStr}-{$posStr}-{$partNum}.svg",
                    $parentWidth,
                    $parentHeight,
                    $force,
                );

                // Save representative glyph SVG (first occurrence wins)
                $this->saveGlyphSvg($parsed['barthel_code'], $part['pathData'], $parentWidth, $parentHeight, $force);
            }
        }

        if (empty($renderingCodes)) {
            return;
        }

        $compoundCode = implode('.', $renderingCodes);
        $compound = $this->getOrCreateCompoundGlyph($compoundCode, $glyphs);

        $tr = TabletRendering::create([
            'tablet_line_id' => $tabletLine->id,
            'rendering_id' => null,
            'compound_glyph_id' => $compound->id,
            'position' => $position,
            ...$allModifiers,
        ]);

        foreach ($svgPaths as $sortOrder => $relativePath) {
            if ($relativePath) {
                $tr->images()->create([
                    'path' => $relativePath,
                    'type' => 'rendering-svg',
                    'sort_order' => $sortOrder,
                ]);
            }
        }
    }

    /**
     * Parse a label like "005jt", "002Va", "430!", "_"
     */
    private function parseLabel(string $label): ?array
    {
        $label = trim($label);
        if ($label === '' || $label === '_') {
            return null;
        }

        if (! preg_match('/^(\d{3})(.*)$/', $label, $m)) {
            return null;
        }

        $barthelCode = $m[1];
        $suffix = $m[2];

        $variant = null;
        $modifiers = [
            'is_inverted' => false,
            'is_mirrored' => false,
            'is_small' => false,
            'is_enlarged' => false,
            'is_truncated' => false,
            'is_distorted' => false,
            'is_uncertain' => false,
            'is_nonstandard' => false,
        ];

        for ($i = 0; $i < strlen($suffix); $i++) {
            $ch = $suffix[$i];

            if ($ch === '!' || $ch === 'x') {
                $modifiers['is_nonstandard'] = true;
            } elseif ($ch === '?') {
                $modifiers['is_uncertain'] = true;
            } elseif (isset(self::MODIFIER_MAP[$ch])) {
                $modifiers[self::MODIFIER_MAP[$ch]] = true;
            } elseif ($variant === null && in_array($ch, self::RENDERING_VARIANTS)) {
                $variant = $ch;
            }
        }

        $renderingCode = $variant ? $barthelCode . $variant : $barthelCode;

        return [
            'barthel_code' => $barthelCode,
            'variant' => $variant,
            'rendering_code' => $renderingCode,
            'modifiers' => $modifiers,
        ];
    }

    private function getOrCreateGlyph(string $barthelCode): Glyph
    {
        if (isset($this->glyphCache[$barthelCode])) {
            return $this->glyphCache[$barthelCode];
        }

        $glyph = Glyph::firstOrCreate(['barthel_code' => $barthelCode]);
        $this->glyphCache[$barthelCode] = $glyph;

        return $glyph;
    }

    private function getOrCreateRendering(string $renderingCode, Glyph $glyph): Rendering
    {
        if (isset($this->renderingCache[$renderingCode])) {
            return $this->renderingCache[$renderingCode];
        }

        $rendering = Rendering::firstOrCreate(
            ['code' => $renderingCode],
            ['glyph_id' => $glyph->id],
        );
        $this->renderingCache[$renderingCode] = $rendering;

        return $rendering;
    }

    private function getOrCreateCompoundGlyph(string $compoundCode, array $glyphs): CompoundGlyph
    {
        if (isset($this->compoundCache[$compoundCode])) {
            return $this->compoundCache[$compoundCode];
        }

        $compound = CompoundGlyph::firstOrCreate(['code' => $compoundCode]);

        if ($compound->wasRecentlyCreated) {
            foreach ($glyphs as $order => $glyph) {
                CompoundGlyphPart::create([
                    'compound_glyph_id' => $compound->id,
                    'glyph_id' => $glyph->id,
                    'order' => $order + 1,
                ]);
            }
        }

        $this->compoundCache[$compoundCode] = $compound;

        return $compound;
    }

    /**
     * Save a representative SVG for a glyph (first occurrence wins).
     */
    private function saveGlyphSvg(string $barthelCode, string $pathData, int $width, int $height, bool $force): void
    {
        if (isset($this->glyphSvgSaved[$barthelCode])) {
            return;
        }

        $this->saveSvgFile($pathData, "glyphs/{$barthelCode}.svg", $width, $height, $force);
        $this->glyphSvgSaved[$barthelCode] = true;
    }

    private function saveSvgFile(
        string $pathData,
        string $relativePath,
        int $width,
        int $height,
        bool $force,
    ): ?string {
        $fullPath = public_path($relativePath);

        if (file_exists($fullPath) && ! $force) {
            return $relativePath;
        }

        File::ensureDirectoryExists(dirname($fullPath));

        $viewBox = $this->computeViewBox($pathData, $height);

        $svg = '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
            . '<svg xmlns="http://www.w3.org/2000/svg" viewBox="' . $viewBox . '">' . "\n"
            . '  <path d="' . $pathData . '"/>' . "\n"
            . '</svg>' . "\n";

        file_put_contents($fullPath, $svg);
        $this->svgCount++;

        return $relativePath;
    }

    /**
     * Compute a tight viewBox from SVG path coordinates.
     * Extracts all numeric coordinate pairs and adds padding.
     */
    private function computeViewBox(string $pathData, int $parentHeight): string
    {
        // Extract all numbers from the path data
        preg_match_all('/-?\d+(?:\.\d+)?/', $pathData, $nums);
        $numbers = array_map('floatval', $nums[0]);

        if (count($numbers) < 2) {
            return '0 0 100 120';
        }

        // Numbers alternate x, y in most SVG path commands (M, L, C, etc.)
        // Simple approach: collect all numbers, find min/max
        // Since commands like C have 6 numbers (3 pairs), this still works
        $xs = [];
        $ys = [];
        for ($i = 0; $i < count($numbers) - 1; $i += 2) {
            $xs[] = $numbers[$i];
            $ys[] = $numbers[$i + 1];
        }

        if (empty($xs) || empty($ys)) {
            return '0 0 100 120';
        }

        $minX = min($xs);
        $maxX = max($xs);
        $minY = min($ys);
        $maxY = max($ys);

        // Add padding (5% of dimensions, minimum 2px)
        $w = $maxX - $minX;
        $h = $maxY - $minY;
        $padX = max(2, $w * 0.05);
        $padY = max(2, $h * 0.05);

        $vx = round($minX - $padX, 2);
        $vy = round($minY - $padY, 2);
        $vw = round($w + $padX * 2, 2);
        $vh = round($h + $padY * 2, 2);

        return "{$vx} {$vy} {$vw} {$vh}";
    }
}
