{!! '<' . '?xml version="1.0" encoding="UTF-8"?' . '>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    {{-- Alphabet pages --}}
    @for($p = 1; $p <= $alphabetPages; $p++)
    <url>
        <loc>{{ $p === 1 ? route('alphabet') : route('alphabet', ['page' => $p]) }}</loc>
        <priority>0.8</priority>
    </url>
    @endfor

    {{-- Glyph detail pages --}}
    @foreach($glyphs as $glyph)
    <url>
        <loc>{{ route('glyph', $glyph->barthel_code) }}</loc>
        <priority>0.7</priority>
    </url>
    @endforeach

    {{-- Rendering pages --}}
    @foreach($renderingGroups as $i => $base)
    <url>
        <loc>{{ $i === 0 ? route('renderings') : route('renderings', ['page' => $i + 1]) }}</loc>
        <priority>0.6</priority>
    </url>
    @endforeach

    {{-- Rendering detail pages --}}
    @foreach($glyphs->filter(fn($g) => $g->renderings_count ?? true) as $glyph)
    <url>
        <loc>{{ route('rendering', $glyph->barthel_code) }}</loc>
        <priority>0.5</priority>
    </url>
    @endforeach

    {{-- Ligature pages --}}
    @for($p = 1; $p <= $ligaturePages; $p++)
    <url>
        <loc>{{ $p === 1 ? route('ligatures') : route('ligatures', ['page' => $p]) }}</loc>
        <priority>0.6</priority>
    </url>
    @endfor

    {{-- Tablets --}}
    <url>
        <loc>{{ route('tablets') }}</loc>
        <priority>0.8</priority>
    </url>
    @foreach($tablets as $tablet)
    <url>
        <loc>{{ route('tablet', $tablet->code) }}</loc>
        <priority>0.7</priority>
    </url>
    @endforeach

    {{-- Lines --}}
    @foreach($tablets as $tablet)
    <url>
        <loc>{{ route('lines', ['tablet' => $tablet->code]) }}</loc>
        <priority>0.6</priority>
    </url>
    @endforeach

    {{-- Line detail pages --}}
    @foreach($lines as $line)
    <url>
        <loc>{{ route('line', [$line->tablet->code, chr(ord('a') + $line->side), $line->line]) }}</loc>
        <priority>0.5</priority>
    </url>
    @endforeach

    {{-- About --}}
    <url>
        <loc>{{ route('about') }}</loc>
        <priority>0.4</priority>
    </url>
</urlset>
