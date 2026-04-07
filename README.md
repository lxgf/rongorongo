# Rongorongo

Open-source research platform for the undeciphered writing system of Rapa Nui (Easter Island).

**[rongorongo.top](https://rongorongo.top)** | **[ru.rongorongo.top](https://ru.rongorongo.top)**

---

## What is this?

Rongorongo is a system of glyphs discovered in the 19th century on Easter Island. Fewer than two dozen inscribed wooden artifacts survive, scattered across museums worldwide. The script remains undeciphered.

This platform structures the corpus of ~11,000 glyph occurrences across 24 tablets, with individual SVG renderings for each occurrence, parsed from [kohaumotu.org](http://kohaumotu.org).

## Features

- **632 glyphs** with Barthel catalog codes (001–799)
- **822 rendering variants** — graphic forms of each glyph
- **2,428 ligatures** — compound signs fused from multiple glyphs
- **11,003 occurrences** mapped to exact positions on tablet lines
- **~15,600 SVG files** — one per glyph occurrence, with computed viewBox
- **Specimen sheets** — visual grid of all occurrences per glyph, grouped by tablet
- **Tablet photographs** from Wikimedia Commons
- **Glyph meanings** — confirmed (lunar calendar, turtle) and proposed (bird-man, deity, fish)
- **Bilingual** — English and Russian via subdomain (`ru.rongorongo.top`)
- **SEO optimized** — meta tags, Open Graph, sitemap, hreflang

## Stack

Laravel 12 · PHP 8.4 · Filament v5 · Tailwind CSS v4 · PostgreSQL · Redis · Varnish · OpenResty · Docker

## Quick start

```bash
git clone https://github.com/lxgf/rongorongo.git
cd rongorongo
chmod +x prod.sh
./prod.sh
```

The deploy script creates `.env.docker`, builds Docker containers, runs migrations, restores the corpus dump, seeds data, and runs health checks.

## Development

```bash
# Docker dev environment (with bind mounts for live reload)
docker compose -f docker-compose.yml -f docker-compose.dev.yml up -d

# Import corpus from kohaumotu.org (scrapes SVG glyphs)
docker compose exec app php artisan rongorongo:import-corpus

# Download tablet photos from Wikimedia Commons
docker compose exec app php artisan rongorongo:download-photos

# Rebuild frontend
npx vite build
```

See [AGENTS.md](AGENTS.md) for full documentation.

## Data sources

- SVG glyph tracings from [kohaumotu.org](http://kohaumotu.org) by P. Spaelti
- Glyph catalog: Barthel, Thomas (1958). *Grundlagen zur Entzifferung der Osterinselschrift*
- Extended catalog: Fischer, Steven Roger (1997). *Rongorongo: The Easter Island Script*
- Tablet photographs from Wikimedia Commons (CC BY-SA 3.0)
- Corpus structure informed by [rongopy](https://github.com/jgregoriods/rongopy)

Materials used under fair use for non-commercial academic research.

## License

MIT
