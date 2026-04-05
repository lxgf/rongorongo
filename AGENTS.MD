# Rongorongo Research Platform

Research platform for the rongorongo script (Easter Island).
Structured glyph corpus with SVG renderings, relationship visualization, and analysis tools.

---

## Commits

Format: `type: short message in english`

| Prefix | Use |
|--------|-----|
| `feat` | New feature or functionality |
| `fix` | Bug fix |
| `refactor` | Code restructuring without behavior change |
| `delete` | Removing code, files, or features |
| `style` | CSS, formatting, whitespace |
| `docs` | Documentation changes |
| `chore` | Dependencies, config, tooling |

Examples:
```
feat: add about page with attribution and corpus stats
fix: resolve duplicate position in tablet D import
refactor: replace state machine with ggn-based grouping
delete: remove legacy ImportTablets command and tablets.json
style: compact specimen sheet on glyph page
docs: add AGENTS.md with project structure
chore: add public/svg bind mount to docker-compose
```

---

## Stack

| Layer | Technology |
|-------|-----------|
| Backend | Laravel 12, PHP 8.4 |
| Admin | Filament v5 |
| Frontend | Blade + Tailwind CSS v4 + Vite |
| Database | PostgreSQL 17 (Docker) / SQLite (local) |
| Cache | Varnish 7.6 (HTTP) + Redis 7 (session/cache/queue) |
| Web server | OpenResty (nginx) |
| Font | Jost |
| Localization | EN / RU |

---

## Running

### Docker (primary)

```bash
# Start all services
docker compose -f docker-compose.yml -f docker-compose.dev.yml up -d

# Ports:
#   2020 — Varnish (production-like cache)
#   2030 — OpenResty directly (no cache, for debugging)
```

### Artisan commands inside Docker

```bash
docker compose -f docker-compose.yml -f docker-compose.dev.yml exec app php artisan <command>
```

### Local (without Docker)

```bash
composer setup        # full setup: .env, key, migrate, npm, build
composer dev          # artisan serve + queue + pail + vite dev (concurrent)
```

---

## Frontend Build

Tailwind CSS v4 with JIT compilation via Vite. CSS is rebuilt on every build.

```bash
# Production build (required after adding new Tailwind classes in Blade templates)
npx vite build

# Dev server with HMR
npm run dev
```

**Important:** In Docker, CSS is built during image build. If you add new Tailwind classes in Blade — rebuild locally (`npx vite build`), the `public/build` bind mount delivers files to the container.

Theme defined in `resources/css/app.css`:
- `--color-cream` #FAFAF5 — background
- `--color-ink` #1A1A1A — text
- `--color-soviet-red` #CC2936 — accent
- `--color-deep-blue` #2B4570 — secondary
- `--color-warm-gray` #9E9689 — tertiary
- `--color-rule` #D4CFC3 — rules/borders

Aesthetic: Soviet constructivism / editorial. No border-radius (`border-radius: 0` globally).

---

## Database

### Schema

```
tablets                         # 26 tablets (A-Z), seeded
├── tablet_lines                # lines (side 0-7, line, direction ltr/rtl)
│   └── tablet_renderings       # glyph occurrences (position, 8 modifier flags)
│       ├── → rendering         # rendering variant (code, glyph_id)
│       │   └── → glyph         # abstract glyph (barthel_code)
│       └── → compound_glyph   # ligature (code: "005.009")
│           └── compound_glyph_parts  # parts (glyph_id, order)
│
images                          # polymorphic (imageable → any model)
    type: 'photo'               # tablet photographs (Wikimedia)
    type: 'rendering-svg'       # SVG occurrences (kohaumotu.org)
```

### Migrations

```bash
php artisan migrate             # inside Docker container
```

### Seeders

```bash
php artisan db:seed             # creates test user + 26 tablets (TabletSeeder)
```

`TabletSeeder` — all 26 tablets with code, name, name_en, name_ru, type, location.

### Docker vs Local DB

- `.env.docker` → PostgreSQL (db:5432, rongorongo/secret)
- `.env` → SQLite (database/database.sqlite)
- These are two **independent** databases, not synced
- Primary working DB is PostgreSQL in Docker

---

## Data Import

### `php artisan rongorongo:import-corpus [--force]`

Parses HTML/SVG from http://kohaumotu.org/Rongorongo_new/ and populates DB + saves SVG files.

**What it does:**
1. Fetches 24 tablet pages (A-X) from `lines.php?item={CODE}&type=b`
2. Parses h3 ids (`Ar01`, `Db06`, `Xg02`) → side + line
3. Parses SVG: groups elements by `<text class="ggn">` position markers
4. Creates: `TabletLine`, `Glyph`, `Rendering`, `CompoundGlyph`, `CompoundGlyphPart`, `TabletRendering`
5. Saves SVGs: `public/svg/{Tablet}/{side}{line}-{pos}.svg` and `public/glyphs/{code}.svg`
6. Creates `Image` records (type=`rendering-svg`) linked to `TabletRendering`

**Label parsing** (e.g. `005jt`, `002Va`, `430!`):
- 3 digits → barthel_code → `Glyph`
- Letter from [a,c,d,j,h,e,k,i,o,g] → rendering variant
- f=inverted, b=mirrored, s=small, V=enlarged, t=truncated, y=distorted
- `!`/`x` = nonstandard, `?` = uncertain

**Compound glyphs:** multiple paths sharing one position (no line-separator between them).

**Side mapping:** r/a→0, v/b→1, c→2, d→3, e→4, f→5, g→6, h→7 (tablet X has 8 locations).

**On re-run** — clears all corpus data (preserving tablets and their photos) and re-imports. `--force` overwrites SVG files.

### `php artisan rongorongo:download-photos [--force]`

Downloads tablet photographs from Wikimedia Commons.
- 13 tablets, ~33 photos
- Saves to `public/tablet-photos/{CODE}/`
- Creates `Image` records (type=`photo`) linked to `Tablet`

---

## File Structure

```
app/
├── Console/Commands/
│   ├── ImportCorpus.php          # corpus import from kohaumotu.org
│   └── DownloadTabletPhotos.php  # photos from Wikimedia
├── Http/Controllers/
│   └── PageController.php        # all public pages
├── Models/
│   ├── Tablet.php                # tablet artifact
│   ├── TabletLine.php            # line (side, line, direction)
│   ├── TabletRendering.php       # glyph occurrence (position, modifiers)
│   ├── Glyph.php                 # abstract glyph (barthel_code)
│   ├── Rendering.php             # rendering variant (code = barthel + variant)
│   ├── CompoundGlyph.php         # ligature (code = "005.009")
│   ├── CompoundGlyphPart.php     # ligature part (glyph_id, order)
│   └── Image.php                 # polymorphic image
├── Filament/Resources/           # 7 admin panel resources
└── Http/Middleware/SetLocale.php

resources/views/
├── layouts/public.blade.php      # layout (header, nav, footer)
├── alphabet.blade.php            # glyph catalog
├── glyph.blade.php               # glyph page + specimen sheet
├── tablet.blade.php              # tablet (photos + chess grid)
├── line.blade.php                # line (glyph grid)
├── ligatures.blade.php           # ligatures
├── rendering.blade.php           # glyph renderings + specimen sheet
├── renderings.blade.php          # all renderings
├── tablets.blade.php             # tablet listing
└── lines.blade.php               # all lines

public/
├── glyphs/                       # representative glyph SVGs (632 files)
├── svg/{A..X}/                   # per-occurrence SVGs (~15000 files)
├── tablet-photos/{A..S}/         # tablet photographs
└── build/                        # Vite output (CSS + JS)

docker/
├── php/Dockerfile                # PHP 8.4-FPM + Node 22
├── openresty/nginx.conf          # nginx config
└── varnish/default.vcl           # Varnish cache rules
```

---

## Docker Architecture

```
[Browser] → :2020 Varnish → :80 OpenResty → :9000 PHP-FPM (app)
                                    ↓                    ↓
                              static files         PostgreSQL :5432
                              (public/)             Redis :6379
```

**Dev bind mounts** (docker-compose.dev.yml):
- `app/`, `resources/`, `routes/`, `config/`, `database/`, `bootstrap/`, `tests/`, `lang/`, `artisan` — application code
- `public/build` — Vite output
- `public/tablet-photos`, `public/glyphs`, `public/svg` — static SVGs and photos

**Named volumes:** `vendor_data` (composer), `storage_data`, `app_public`, `pgdata`, `redis_data`

**Important:** After changing Blade templates, restart the app container to clear OPcache:
```bash
docker compose -f docker-compose.yml -f docker-compose.dev.yml restart app
```

To clear Varnish cache:
```bash
docker compose -f docker-compose.yml -f docker-compose.dev.yml restart varnish
```

---

## Routes

| URL | Name | Description |
|-----|------|------------|
| `/` | alphabet | Glyph catalog |
| `/glyph/{code}` | glyph | Glyph detail + specimen sheet |
| `/renderings` | renderings | All renderings |
| `/renderings/{code}` | rendering | Glyph renderings + specimen sheet |
| `/ligatures` | ligatures | Ligatures |
| `/tablets` | tablets | Tablet listing |
| `/tablet/{code}` | tablet | Tablet (photos + chess grid) |
| `/lines` | lines | All lines |
| `/line/{tablet}/{side}/{line}` | line | Line (glyph grid) |
| `/locale/{locale}` | locale.switch | Toggle EN/RU |
| `/admin` | — | Filament admin panel |

---

## Filament Admin

**URL:** `/admin`
**Login:** `admin@rongorongo.local` / `password`

7 resources in "Corpus" navigation group:
TabletResource, GlyphResource, RenderingResource, CompoundGlyphResource, TabletLineResource, TabletRenderingResource, CompoundGlyphPartResource

---

## Corpus Statistics

- 24 tablets (A-X)
- 338 lines
- 632 unique glyphs
- 822 renderings
- 2428 ligatures
- 11003 occurrences
- ~15600 SVG files
