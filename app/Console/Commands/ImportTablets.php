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

class ImportTablets extends Command
{
    protected $signature = 'rongorongo:import {file? : Path to tablets.json}';
    protected $description = 'Import rongorongo corpus from tablets.json';

    // Rendering variant letters (go into renderings.code)
    private const RENDERING_VARIANTS = ['a', 'c', 'd', 'j', 'h', 'e', 'k', 'i', 'o', 'g'];

    // Geometric modifier letters (go into tablet_renderings.is_* flags)
    private const MODIFIER_MAP = [
        'f' => 'is_inverted',
        'b' => 'is_mirrored',
        's' => 'is_small',
        'V' => 'is_enlarged',
        't' => 'is_truncated',
        'y' => 'is_distorted',
    ];

    // Traditional tablet names
    private const TABLET_NAMES = [
        'A' => 'Tahua',
        'B' => 'Aruku Kurenga',
        'C' => 'Mamari',
        'D' => 'Échancrée',
        'E' => 'Keiti',
        'G' => 'Small Santiago',
        'H' => "Jaussen's String",
        'K' => 'Réunion',
        'N' => 'Small Vienna',
        'P' => 'Apai',
        'Q' => 'Unknown',
        'R' => 'Small Washington',
        'S' => 'Large Washington',
    ];

    // Cache for created records
    private array $glyphCache = [];
    private array $renderingCache = [];
    private array $compoundGlyphCache = [];

    // Available GIF files
    private array $availableGifs = [];

    public function handle(): int
    {
        $file = $this->argument('file') ?? storage_path('app/tablets.json');

        if (!file_exists($file)) {
            $this->error("File not found: {$file}");
            return self::FAILURE;
        }

        $data = json_decode(file_get_contents($file), true);
        if (!$data) {
            $this->error('Failed to parse JSON');
            return self::FAILURE;
        }

        // Scan available GIF files
        foreach (glob(public_path('glyphs/*.GIF')) as $gif) {
            $this->availableGifs[pathinfo($gif, PATHINFO_FILENAME)] = true;
        }

        $this->info("Found " . count($this->availableGifs) . " GIF files");
        $this->info("Importing " . count($data) . " tablets...");

        DB::transaction(function () use ($data) {
            $this->clearExistingData();

            $totalPositions = 0;

            foreach ($data as $tabletCode => $lines) {
                $tablet = Tablet::create([
                    'code' => $tabletCode,
                    'name' => self::TABLET_NAMES[$tabletCode] ?? $tabletCode,
                ]);

                $this->info("  Tablet {$tabletCode}: " . count($lines) . " lines");

                foreach ($lines as $lineKey => $text) {
                    $parsed = $this->parseLineKey($lineKey, $tabletCode);
                    if (!$parsed) {
                        $this->warn("    Skipping unparseable line key: {$lineKey}");
                        continue;
                    }

                    $tabletLine = TabletLine::create([
                        'tablet_id' => $tablet->id,
                        'side' => $parsed['side'],
                        'line' => $parsed['line'],
                        'direction' => 'ltr',
                    ]);

                    $positions = $this->parseLine($text, $tabletLine->id);
                    $totalPositions += $positions;
                }
            }

            $this->newLine();
            $this->info("=== Import complete ===");
            $this->info("Tablets:          " . Tablet::count());
            $this->info("Tablet lines:     " . TabletLine::count());
            $this->info("Glyphs:           " . Glyph::count());
            $this->info("Renderings:       " . Rendering::count());
            $this->info("Compound glyphs:  " . CompoundGlyph::count());
            $this->info("Compound parts:   " . CompoundGlyphPart::count());
            $this->info("Tablet renderings: " . TabletRendering::count());
            $this->info("Images:           " . Image::count());
            $this->info("Total positions:  {$totalPositions}");
        });

        return self::SUCCESS;
    }

    private function clearExistingData(): void
    {
        $this->info("Clearing existing data...");
        TabletRendering::query()->delete();
        CompoundGlyphPart::query()->delete();
        CompoundGlyph::query()->delete();
        Rendering::query()->delete();
        Image::query()->delete();
        Glyph::query()->delete();
        TabletLine::query()->delete();
        Tablet::query()->delete();
    }

    /**
     * Parse line key like "Aa1", "Ab3", "Br2", "Bv5"
     * Returns ['side' => 0|1, 'line' => int]
     */
    private function parseLineKey(string $key, string $tabletCode): ?array
    {
        // Remove tablet code prefix (can be multi-char like for some tablets)
        $rest = substr($key, strlen($tabletCode));

        if (!preg_match('/^([abrv])(\d+)$/i', $rest, $m)) {
            return null;
        }

        $sideChar = strtolower($m[1]);
        $side = in_array($sideChar, ['a', 'r']) ? 0 : 1;
        $line = (int) $m[2];

        return ['side' => $side, 'line' => $line];
    }

    /**
     * Parse a line of tokens and create TabletRendering records.
     * Returns number of positions created.
     */
    private function parseLine(string $text, int $tabletLineId): int
    {
        $tokens = explode('-', $text);
        $position = 0;

        foreach ($tokens as $token) {
            $token = trim($token);
            if ($token === '' || $token === '*') {
                continue;
            }

            $position++;
            $this->parseToken($token, $tabletLineId, $position);
        }

        return $position;
    }

    /**
     * Parse a single token and create the necessary records.
     *
     * Token examples:
     *   022bf        → glyph 022, modifiers b+f
     *   008a         → glyph 008, rendering variant a
     *   008a.451V    → ligature: 008a + 451V
     *   005:008a     → stack (treat as ligature): 005 + 008a
     *   430!         → glyph 430, nonstandard
     *   195a?        → glyph 195, rendering a, uncertain
     */
    private function parseToken(string $token, int $tabletLineId, int $position): void
    {
        // Split by ligature separators: dot, colon, apostrophe
        $parts = preg_split("/[.:\']/", $token);

        if (count($parts) === 1) {
            // Simple glyph (possibly with rendering variant and modifiers)
            $parsed = $this->parseGlyphCode($parts[0]);
            if (!$parsed) {
                return;
            }

            $renderingId = null;
            if ($parsed['variant']) {
                $renderingId = $this->getOrCreateRendering(
                    $parsed['code'],
                    $parsed['variant']
                );
            } else {
                // Create a "base" rendering for the glyph
                $renderingId = $this->getOrCreateRendering($parsed['code'], null);
            }

            TabletRendering::create([
                'tablet_line_id' => $tabletLineId,
                'rendering_id' => $renderingId,
                'compound_glyph_id' => null,
                'position' => $position,
                'is_inverted' => $parsed['modifiers']['is_inverted'],
                'is_mirrored' => $parsed['modifiers']['is_mirrored'],
                'is_small' => $parsed['modifiers']['is_small'],
                'is_enlarged' => $parsed['modifiers']['is_enlarged'],
                'is_truncated' => $parsed['modifiers']['is_truncated'],
                'is_distorted' => $parsed['modifiers']['is_distorted'],
                'is_uncertain' => $parsed['modifiers']['is_uncertain'],
                'is_nonstandard' => $parsed['modifiers']['is_nonstandard'],
            ]);
        } else {
            // Compound glyph (ligature)
            $parsedParts = [];
            $compoundCodeParts = [];
            $globalModifiers = $this->defaultModifiers();

            foreach ($parts as $part) {
                $parsed = $this->parseGlyphCode($part);
                if (!$parsed) {
                    continue;
                }
                $parsedParts[] = $parsed;

                // Build compound code from full rendering codes
                $code = $parsed['code'];
                if ($parsed['variant']) {
                    $code .= $parsed['variant'];
                }
                $compoundCodeParts[] = $code;

                // Merge modifiers (any part's modifiers apply to the whole position)
                foreach ($parsed['modifiers'] as $key => $val) {
                    if ($val) {
                        $globalModifiers[$key] = true;
                    }
                }
            }

            if (empty($parsedParts)) {
                return;
            }

            $compoundCode = implode('.', $compoundCodeParts);
            $compoundGlyphId = $this->getOrCreateCompoundGlyph($compoundCode, $parsedParts);

            TabletRendering::create([
                'tablet_line_id' => $tabletLineId,
                'rendering_id' => null,
                'compound_glyph_id' => $compoundGlyphId,
                'position' => $position,
                'is_inverted' => $globalModifiers['is_inverted'],
                'is_mirrored' => $globalModifiers['is_mirrored'],
                'is_small' => $globalModifiers['is_small'],
                'is_enlarged' => $globalModifiers['is_enlarged'],
                'is_truncated' => $globalModifiers['is_truncated'],
                'is_distorted' => $globalModifiers['is_distorted'],
                'is_uncertain' => $globalModifiers['is_uncertain'],
                'is_nonstandard' => $globalModifiers['is_nonstandard'],
            ]);
        }
    }

    /**
     * Parse a single glyph code like "022bf", "008a", "430!", "195a?"
     * Returns: ['code' => '022', 'variant' => 'a'|null, 'modifiers' => [...]]
     */
    private function parseGlyphCode(string $raw): ?array
    {
        $raw = trim($raw);
        if ($raw === '' || $raw === '*') {
            return null;
        }

        // Extract the 3-digit numeric code
        if (!preg_match('/^(\d{3})(.*)$/', $raw, $m)) {
            return null;
        }

        $code = $m[1];
        $suffix = $m[2];

        $variant = null;
        $modifiers = $this->defaultModifiers();

        // Parse suffix character by character
        for ($i = 0; $i < strlen($suffix); $i++) {
            $ch = $suffix[$i];

            if ($ch === '!') {
                $modifiers['is_nonstandard'] = true;
            } elseif ($ch === '?') {
                $modifiers['is_uncertain'] = true;
            } elseif ($ch === 'x') {
                $modifiers['is_nonstandard'] = true;
            } elseif (isset(self::MODIFIER_MAP[$ch])) {
                $modifiers[self::MODIFIER_MAP[$ch]] = true;
            } elseif (in_array($ch, self::RENDERING_VARIANTS)) {
                $variant = $ch;
            }
            // Ignore any other characters
        }

        // Ensure glyph exists
        $this->getOrCreateGlyph($code);

        return [
            'code' => $code,
            'variant' => $variant,
            'modifiers' => $modifiers,
        ];
    }

    private function defaultModifiers(): array
    {
        return [
            'is_inverted' => false,
            'is_mirrored' => false,
            'is_small' => false,
            'is_enlarged' => false,
            'is_truncated' => false,
            'is_distorted' => false,
            'is_uncertain' => false,
            'is_nonstandard' => false,
        ];
    }

    private function getOrCreateGlyph(string $code): int
    {
        if (isset($this->glyphCache[$code])) {
            return $this->glyphCache[$code];
        }

        $glyph = Glyph::firstOrCreate(['barthel_code' => $code]);

        // Attach GIF image via polymorphic images table
        if (isset($this->availableGifs[$code])) {
            $glyph->images()->firstOrCreate(
                ['path' => "glyphs/{$code}.GIF"],
                ['type' => 'glyph', 'sort_order' => 0]
            );
        }

        $this->glyphCache[$code] = $glyph->id;
        return $glyph->id;
    }

    private function getOrCreateRendering(string $glyphCode, ?string $variant): int
    {
        $renderingCode = $variant ? $glyphCode . $variant : $glyphCode;

        if (isset($this->renderingCache[$renderingCode])) {
            return $this->renderingCache[$renderingCode];
        }

        $glyphId = $this->getOrCreateGlyph($glyphCode);

        $rendering = Rendering::firstOrCreate(
            ['code' => $renderingCode],
            ['glyph_id' => $glyphId]
        );

        $this->renderingCache[$renderingCode] = $rendering->id;
        return $rendering->id;
    }

    private function getOrCreateCompoundGlyph(string $compoundCode, array $parsedParts): int
    {
        if (isset($this->compoundGlyphCache[$compoundCode])) {
            return $this->compoundGlyphCache[$compoundCode];
        }

        $compound = CompoundGlyph::firstOrCreate(['code' => $compoundCode]);

        // Create parts if this is a new compound glyph
        if ($compound->wasRecentlyCreated) {
            foreach ($parsedParts as $order => $parsed) {
                $glyphId = $this->getOrCreateGlyph($parsed['code']);
                CompoundGlyphPart::create([
                    'compound_glyph_id' => $compound->id,
                    'glyph_id' => $glyphId,
                    'order' => $order + 1,
                ]);
            }
        }

        $this->compoundGlyphCache[$compoundCode] = $compound->id;
        return $compound->id;
    }
}
