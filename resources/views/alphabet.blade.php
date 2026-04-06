@extends('layouts.public')

@section('title', 'Rongorongo Glyphs ' . $pagination['rangeStart'] . '–' . $pagination['rangeEnd'] . ' — Easter Island Script Catalog')
@section('meta_description', 'Rongorongo script glyphs ' . $pagination['rangeStart'] . '–' . $pagination['rangeEnd'] . '. ' . $glyphs->count() . ' signs from the undeciphered Easter Island writing system with SVG renderings. Barthel catalog.')
@section('canonical', $pagination['currentIndex'] === 0 ? route('alphabet') : route('alphabet', ['page' => $pagination['currentIndex'] + 1]))

@section('content')
    {{-- Definition block --}}
    <div class="mb-10 border-t-[3px] border-soviet-red pt-4 pb-5 border-b border-rule">
        <div class="flex items-start gap-4 sm:gap-6">
            <span class="text-[40px] sm:text-[52px] font-bold leading-none text-soviet-red select-none tracking-tight" aria-hidden="true">{{ mb_substr(__('front.alphabet.title'), 0, 1) }}</span>
            <div class="pt-0.5">
                <h1 class="text-[11px] font-medium tracking-[0.2em] uppercase mb-2">{{ __('front.alphabet.title') }}</h1>
                <p class="text-sm leading-relaxed text-ink/80 max-w-2xl">{{ __('front.alphabet.definition') }}</p>
            </div>
        </div>
    </div>

    {{-- Pagination strip --}}
    <div class="flex items-center gap-px mb-8">
        @foreach($pagination['groupKeys'] as $i => $base)
            @php
                $isCurrent = $i === $pagination['currentIndex'];
                $pageNum = $i + 1;
                $start = str_pad($base, 3, '0', STR_PAD_LEFT);
                $count = $pagination['groups'][$base];
            @endphp
            <a href="{{ $pageNum === 1 ? route('alphabet') : route('alphabet', ['page' => $pageNum]) }}"
               class="flex-1 py-2 text-center border transition-colors
                      {{ $isCurrent
                          ? 'bg-soviet-red text-white border-soviet-red font-semibold'
                          : 'bg-white text-ink border-rule hover:border-soviet-red hover:text-soviet-red' }}">
                <span class="block text-[13px] tabular-nums leading-none">{{ $start }}</span>
                <span class="block text-[8px] tabular-nums leading-none mt-1 {{ $isCurrent ? 'text-white/70' : 'text-warm-gray' }}">{{ $count }}</span>
            </a>
        @endforeach
    </div>

    {{-- Search --}}
    <div class="flex items-center justify-between mb-6">
        <span class="text-[11px] font-medium tracking-[0.15em] uppercase text-warm-gray">
            {{ $pagination['rangeStart'] }}&mdash;{{ $pagination['rangeEnd'] }}
            <span class="font-normal">&middot; {{ $glyphs->count() }}</span>
        </span>
        <div class="relative w-48 sm:w-64">
            <input type="text"
                   id="glyph-search"
                   placeholder="{{ __('front.alphabet.search') }}"
                   class="w-full bg-transparent border-b border-ink pb-1 text-sm
                          placeholder:text-warm-gray focus:outline-none focus:border-soviet-red
                          transition-colors tabular-nums"
                   autocomplete="off">
        </div>
    </div>

    {{-- Glyph grid --}}
    <div class="grid grid-cols-4 sm:grid-cols-6 md:grid-cols-8 lg:grid-cols-10 gap-2 mb-8">
        @foreach($glyphs as $glyph)
            @php
                $imgPath = $glyph->preferredImagePath();
                $borderClass = match($glyph->meaning_status) {
                    'confirmed' => 'border-2 border-green-600',
                    'proposed' => 'border-2 border-amber-500',
                    default => 'border border-rule',
                };
            @endphp
            <a href="{{ route('glyph', $glyph->barthel_code) }}"
               class="glyph-card group block {{ $borderClass }} hover:border-soviet-red transition-colors"
               data-code="{{ $glyph->barthel_code }}"
               @if($glyph->meaning) title="{{ $glyph->meaning }}" @endif>
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

    {{-- Prev / Next --}}
    <div class="flex items-center justify-between">
        @if($pagination['currentIndex'] > 0)
            @php $prevPage = $pagination['currentIndex']; @endphp
            <a href="{{ $prevPage === 1 ? route('alphabet') : route('alphabet', ['page' => $prevPage]) }}"
               class="text-sm text-warm-gray hover:text-ink transition-colors tabular-nums">
                &larr; {{ str_pad($pagination['groupKeys'][$pagination['currentIndex'] - 1], 3, '0', STR_PAD_LEFT) }}
            </a>
        @else
            <span></span>
        @endif

        @if($pagination['currentIndex'] < $pagination['totalPages'] - 1)
            @php $nextPage = $pagination['currentIndex'] + 2; @endphp
            <a href="{{ route('alphabet', ['page' => $nextPage]) }}"
               class="text-sm text-warm-gray hover:text-ink transition-colors tabular-nums">
                {{ str_pad($pagination['groupKeys'][$pagination['currentIndex'] + 1], 3, '0', STR_PAD_LEFT) }} &rarr;
            </a>
        @else
            <span></span>
        @endif
    </div>

    <script>
        document.getElementById('glyph-search').addEventListener('input', function () {
            const q = this.value.trim().toLowerCase();
            document.querySelectorAll('.glyph-card').forEach(function (el) {
                el.style.display = !q || el.dataset.code.includes(q) ? '' : 'none';
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
