<?php

namespace App\Console\Commands;

use App\Models\Image;
use App\Models\Tablet;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

class DownloadTabletPhotos extends Command
{
    protected $signature = 'rongorongo:download-photos {--force : Redownload existing files}';
    protected $description = 'Download tablet photographs from Wikimedia Commons';

    /**
     * Mapping: tablet code => array of Wikimedia Commons filenames.
     * Only actual photographs, no Barthel drawings or wiki icons.
     */
    private const TABLET_PHOTOS = [
        'A' => [
            'Rongorongo A-a Tahua left.jpg',
            'Rongorongo A-a Tahua center.jpg',
            'Rongorongo A-a Tahua right.jpg',
            'Rongorongo A-b Tahua left.jpg',
            'Rongorongo A-b Tahua center.jpg',
            'Rongorongo A-b Tahua right.jpg',
        ],
        'B' => [
            'Rongorongo B-r Aruku-Kurenga.jpg',
            'Rongorongo B-v Aruku-Kurenga.jpg',
            'Rongorongo B-v Aruku-Kurenga (color).jpg',
        ],
        'C' => [
            'Rongorongo C-a Mamari.jpg',
            'Rongorongo C-b Mamari.jpg',
            'Rongorongo C-b Mamari smooth.jpg',
        ],
        'D' => [
            'Rongorongo D-a Échancrée.jpg',
            'Rongorongo D-a Échancrée (natural).jpg',
            'Rongorongo D-b Échancrée.jpg',
            'Rongorongo D-b Échancrée (natural).jpg',
        ],
        'E' => [
            'Rongorongo E-r Keiti.jpg',
            'Rongorongo E-v Keiti.jpg',
            'Rongorongo E-v Keiti raw.jpg',
        ],
        'G' => [
            'Rongorongo G-r Small Santiago.jpg',
            'Rongorongo G-r Small Santiago (color).jpg',
            'Rongorongo G-v Small Santiago.jpg',
        ],
        'H' => [
            'Rongorongo H-r Great Santiago.jpg',
            'Rongorongo H-v Great Santiago.jpg',
        ],
        'N' => [
            'Rongorongo N-a Small Vienna.png',
            'Rongorongo N-b Small Vienna.jpg',
        ],
        'P' => [
            'Rongorongo P-r Great St Petersburg.jpg',
            'Rongorongo P-v Great St Petersburg.jpg',
        ],
        'Q' => [
            'Rongorongo Q-v Small St Petersburg.jpg',
        ],
        'R' => [
            'Rongorongo R-a Atua-Mata-Riri.jpg',
            'Rongorongo R-b Atua-Mata-Riri.jpg',
        ],
        'S' => [
            'Rongorongo S-a Great Washington.jpg',
            'Rongorongo S-b Great Washington.jpg',
        ],
    ];

    public function handle(): int
    {
        $force = $this->option('force');
        $totalDownloaded = 0;
        $totalSkipped = 0;
        $totalFailed = 0;

        foreach (self::TABLET_PHOTOS as $code => $files) {
            $tablet = Tablet::where('code', $code)->first();
            if (! $tablet) {
                $this->warn("Tablet {$code} not found in database, skipping.");
                continue;
            }

            $this->info("Tablet {$code} ({$tablet->name}): " . count($files) . ' photos');

            $dir = public_path("tablet-photos/{$code}");
            File::ensureDirectoryExists($dir);

            // Batch-query Wikimedia for download URLs
            $urls = $this->resolveDownloadUrls($files);

            foreach ($files as $sortOrder => $wikiFilename) {
                $localFilename = $this->sanitizeFilename($wikiFilename);
                $localPath = "{$dir}/{$localFilename}";
                $relativePath = "tablet-photos/{$code}/{$localFilename}";

                if (file_exists($localPath) && ! $force) {
                    // Ensure DB record exists even if file was already downloaded
                    $tablet->images()->firstOrCreate(
                        ['path' => $relativePath],
                        ['type' => 'photo', 'sort_order' => $sortOrder]
                    );
                    $this->line("  <comment>skip</comment> {$localFilename}");
                    $totalSkipped++;
                    continue;
                }

                $url = $urls[$wikiFilename] ?? null;
                if (! $url) {
                    $this->error("  <error>fail</error> Could not resolve URL for: {$wikiFilename}");
                    $totalFailed++;
                    continue;
                }

                try {
                    $response = Http::withoutVerifying()
                        ->withHeaders(['User-Agent' => 'RongorongoProject/1.0 (d.shaludnyov@gmail.com)'])
                        ->timeout(60)
                        ->get($url);
                    if ($response->successful()) {
                        file_put_contents($localPath, $response->body());

                        $tablet->images()->firstOrCreate(
                            ['path' => $relativePath],
                            ['type' => 'photo', 'sort_order' => $sortOrder]
                        );

                        $size = round(filesize($localPath) / 1024);
                        $this->info("  <info>done</info> {$localFilename} ({$size} KB)");
                        $totalDownloaded++;
                    } else {
                        $this->error("  <error>fail</error> HTTP {$response->status()} for {$wikiFilename}");
                        $totalFailed++;
                    }
                } catch (\Exception $e) {
                    $this->error("  <error>fail</error> {$e->getMessage()}");
                    $totalFailed++;
                }

                // Small delay to be polite to Wikimedia
                usleep(300_000);
            }
        }

        $this->newLine();
        $this->info("=== Done ===");
        $this->info("Downloaded: {$totalDownloaded}");
        $this->info("Skipped:    {$totalSkipped}");
        $this->info("Failed:     {$totalFailed}");
        $this->info("Total images in DB: " . Image::where('type', 'photo')->count());

        return self::SUCCESS;
    }

    /**
     * Query Wikimedia Commons API to resolve actual download URLs for file names.
     * Batches up to 50 titles per request.
     */
    private function resolveDownloadUrls(array $filenames): array
    {
        $urls = [];
        $chunks = array_chunk($filenames, 50);

        foreach ($chunks as $chunk) {
            $titles = implode('|', array_map(fn ($f) => "File:{$f}", $chunk));

            try {
                $response = Http::withoutVerifying()
                    ->withHeaders(['User-Agent' => 'RongorongoProject/1.0 (d.shaludnyov@gmail.com)'])
                    ->timeout(30)
                    ->get('https://commons.wikimedia.org/w/api.php', [
                    'action' => 'query',
                    'titles' => $titles,
                    'prop' => 'imageinfo',
                    'iiprop' => 'url',
                    'format' => 'json',
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    $pages = $data['query']['pages'] ?? [];

                    // Build a normalized title → original filename map
                    $normalizedMap = [];
                    foreach ($data['query']['normalized'] ?? [] as $norm) {
                        $normalizedMap[$norm['to']] = $norm['from'];
                    }

                    foreach ($pages as $page) {
                        $pageTitle = $page['title'] ?? '';
                        $imageInfo = $page['imageinfo'][0] ?? null;

                        if ($imageInfo && isset($imageInfo['url'])) {
                            // Match back to original filename
                            $originalTitle = $normalizedMap[$pageTitle] ?? $pageTitle;
                            // Remove "File:" prefix
                            $filename = preg_replace('/^File:/', '', $originalTitle);
                            $urls[$filename] = $imageInfo['url'];
                        }
                    }
                }
            } catch (\Exception $e) {
                $this->error("API error: {$e->getMessage()}");
            }
        }

        return $urls;
    }

    /**
     * Convert a Wikimedia filename to a clean local filename.
     * "Rongorongo A-a Tahua center.jpg" → "a-a-tahua-center.jpg"
     */
    private function sanitizeFilename(string $filename): string
    {
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $name = pathinfo($filename, PATHINFO_FILENAME);

        // Remove "Rongorongo" prefix
        $name = preg_replace('/^-?\s*Rongorongo\s+/i', '', $name);

        // Remove parentheses but keep content
        $name = str_replace(['(', ')'], '', $name);

        // Replace commas, spaces, underscores with hyphens
        $name = preg_replace('/[,\s_]+/', '-', $name);

        // Lowercase and clean up
        $name = strtolower($name);
        $name = preg_replace('/-+/', '-', $name);
        $name = trim($name, '-');

        return "{$name}.{$ext}";
    }
}
