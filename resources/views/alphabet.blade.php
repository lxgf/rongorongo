@extends('layouts.public')

@section('title', __('front.alphabet.title') . ' — ' . __('front.site_title'))

@section('content')
    {{-- Definition block — Soviet primer rule style --}}
    <div class="mb-10 border-t-[3px] border-soviet-red pt-4 pb-5 border-b border-rule">
        <div class="flex items-start gap-4 sm:gap-6">
            <span class="text-[40px] sm:text-[52px] font-bold leading-none text-soviet-red select-none tracking-tight" aria-hidden="true">{{ mb_substr(__('front.alphabet.title'), 0, 1) }}</span>
            <div class="pt-0.5">
                <h1 class="text-[11px] font-medium tracking-[0.2em] uppercase mb-2">{{ __('front.alphabet.title') }}</h1>
                <p class="text-sm leading-relaxed text-ink/80 max-w-2xl">{{ __('front.alphabet.definition') }}</p>
            </div>
        </div>
    </div>

    {{-- Search --}}
    <div class="flex justify-end mb-8">
        <div class="relative w-full sm:w-64">
            <input type="text"
                   id="glyph-search"
                   placeholder="{{ __('front.alphabet.search') }}"
                   class="w-full bg-transparent border-b border-ink pb-1 text-sm
                          placeholder:text-warm-gray focus:outline-none focus:border-soviet-red
                          transition-colors tabular-nums"
                   autocomplete="off">
        </div>
    </div>

    {{-- Glyph groups --}}
    @foreach($groups as $base => $groupGlyphs)
        @php
            $start = $base === 0 ? '001' : str_pad($base, 3, '0', STR_PAD_LEFT);
            $end = str_pad($base + 99, 3, '0', STR_PAD_LEFT);
        @endphp
        <section class="mb-10 glyph-group">
            <div class="flex items-center gap-4 mb-4">
                <h2 class="text-[11px] font-medium tracking-[0.15em] uppercase text-warm-gray whitespace-nowrap">
                    {{ $start }}&mdash;{{ $end }}
                </h2>
                <div class="flex-1 border-t border-rule"></div>
            </div>
            <div class="grid grid-cols-4 sm:grid-cols-6 md:grid-cols-8 lg:grid-cols-10 gap-2">
                @foreach($groupGlyphs as $glyph)
                    @php $imgPath = $glyph->preferredImagePath(); @endphp
                    <a href="{{ route('glyph', $glyph->barthel_code) }}"
                       class="glyph-card group block border border-rule hover:border-soviet-red transition-colors"
                       data-code="{{ $glyph->barthel_code }}">
                        <div class="aspect-square bg-white flex items-center justify-center p-2 overflow-hidden">
                            @if($imgPath)
                                <img src="{{ asset($imgPath) }}"
                                     alt="{{ $glyph->barthel_code }}"
                                     class="max-w-full max-h-full object-contain group-hover:scale-110 transition-transform duration-200"
                                     loading="lazy">
                            @else
                                <span class="text-lg font-light text-warm-gray">{{ $glyph->barthel_code }}</span>
                            @endif
                        </div>
                        <div class="border-t border-rule px-1.5 py-1 flex items-baseline justify-between bg-cream">
                            <span class="text-[11px] font-semibold tabular-nums">{{ $glyph->barthel_code }}</span>
                            @if(($occurrenceCounts[$glyph->id] ?? 0) > 0)
                                <span class="text-[9px] text-warm-gray tabular-nums">&times;{{ $occurrenceCounts[$glyph->id] }}</span>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @endforeach

    <script>
        document.getElementById('glyph-search').addEventListener('input', function () {
            const q = this.value.trim().toLowerCase();
            document.querySelectorAll('.glyph-card').forEach(function (el) {
                el.style.display = !q || el.dataset.code.includes(q) ? '' : 'none';
            });
            document.querySelectorAll('.glyph-group').forEach(function (sec) {
                const visible = sec.querySelectorAll('.glyph-card[style=""], .glyph-card:not([style])');
                sec.style.display = !q || visible.length > 0 ? '' : 'none';
            });
        });
    </script>
@endsection

@section('footer')
    <p class="text-[11px] text-warm-gray tracking-wider tabular-nums">
        {{ $stats['glyphs'] }} {{ __('front.stats.glyphs') }} &middot;
        {{ $stats['tablets'] }} {{ __('front.stats.tablets') }} &middot;
        {{ $stats['occurrences'] }} {{ __('front.stats.occurrences') }}
    </p>
@endsection
