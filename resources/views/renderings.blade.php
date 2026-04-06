@extends('layouts.public')

@section('title', 'Rongorongo Renderings ' . $pagination['rangeStart'] . '–' . $pagination['rangeEnd'] . ' — Glyph Variants')
@section('meta_description', 'Graphic variants of Rongorongo glyphs ' . $pagination['rangeStart'] . '–' . $pagination['rangeEnd'] . '. ' . $glyphs->count() . ' signs from the Easter Island script with ' . $totalRenderings . ' rendering variants cataloged by Barthel.')
@section('canonical', $pagination['currentIndex'] === 0 ? route('renderings') : route('renderings', ['page' => $pagination['currentIndex'] + 1]))

@section('content')
    {{-- Definition block --}}
    <div class="mb-10 border-t-[3px] border-soviet-red pt-4 pb-5 border-b border-rule">
        <div class="flex items-start gap-4 sm:gap-6">
            <span class="text-[40px] sm:text-[52px] font-bold leading-none text-soviet-red select-none tracking-tight" aria-hidden="true">{{ mb_substr(__('front.renderings.title'), 0, 1) }}</span>
            <div class="pt-0.5">
                <h1 class="text-[11px] font-medium tracking-[0.2em] uppercase mb-2">{{ __('front.renderings.title') }}</h1>
                <p class="text-sm leading-relaxed text-ink/80 max-w-2xl">{{ __('front.renderings.definition') }}</p>
            </div>
        </div>
    </div>

    {{-- Notation block --}}
    <div class="mb-10">
        <div class="flex items-center gap-4 mb-4">
            <h2 class="text-[11px] font-medium tracking-[0.15em] uppercase whitespace-nowrap">
                {{ __('front.renderings.notation_title') }}
            </h2>
            <div class="flex-1 border-t border-rule"></div>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 sm:gap-8 mb-6">
            <div class="flex items-start gap-4">
                <span class="text-lg font-semibold leading-none text-soviet-red select-none tabular-nums mt-0.5">002a</span>
                <div class="border-l border-rule pl-4">
                    <p class="text-[10px] font-medium tracking-[0.15em] uppercase mb-1">{{ __('front.renderings.notation_variants_label') }}</p>
                    <p class="text-sm text-ink/70 leading-relaxed">{{ __('front.renderings.notation_variants') }}</p>
                </div>
            </div>
            <div class="flex items-start gap-4">
                <span class="text-lg font-semibold leading-none text-soviet-red select-none mt-0.5">f b s</span>
                <div class="border-l border-rule pl-4">
                    <p class="text-[10px] font-medium tracking-[0.15em] uppercase mb-1">{{ __('front.renderings.notation_modifiers_label') }}</p>
                    <p class="text-sm text-ink/70 leading-relaxed">{{ __('front.renderings.notation_modifiers') }}</p>
                </div>
            </div>
        </div>

        {{-- Modifiers table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <tbody>
                    @foreach(__('front.renderings.modifiers') as $key => $label)
                        <tr class="border-b border-rule">
                            <td class="py-1.5 pr-4 w-10 text-center text-soviet-red font-semibold tabular-nums">
                                {{ __('front.renderings.modifier_symbols.' . $key) }}
                            </td>
                            <td class="py-1.5 pr-4 text-warm-gray text-xs font-mono">
                                {{ $key }}
                            </td>
                            <td class="py-1.5">
                                {{ $label }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
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
            <a href="{{ $pageNum === 1 ? route('renderings') : route('renderings', ['page' => $pageNum]) }}"
               class="flex-1 py-2 text-center border transition-colors
                      {{ $isCurrent
                          ? 'bg-soviet-red text-white border-soviet-red font-semibold'
                          : 'bg-white text-ink border-rule hover:border-soviet-red hover:text-soviet-red' }}">
                <span class="block text-[13px] tabular-nums leading-none">{{ $start }}</span>
                <span class="block text-[8px] tabular-nums leading-none mt-1 {{ $isCurrent ? 'text-white/70' : 'text-warm-gray' }}">{{ $count }}</span>
            </a>
        @endforeach
    </div>

    {{-- Search + range --}}
    <div class="flex items-center justify-between mb-6">
        <span class="text-[11px] font-medium tracking-[0.15em] uppercase text-warm-gray">
            {{ $pagination['rangeStart'] }}&mdash;{{ $pagination['rangeEnd'] }}
            <span class="font-normal">&middot; {{ $glyphs->count() }}</span>
        </span>
        <div class="relative w-48 sm:w-64">
            <input type="text"
                   id="rendering-search"
                   placeholder="{{ __('front.renderings.search') }}"
                   class="w-full bg-transparent border-b border-ink pb-1 text-sm
                          placeholder:text-warm-gray focus:outline-none focus:border-soviet-red
                          transition-colors tabular-nums"
                   autocomplete="off">
        </div>
    </div>

    {{-- Glyph list --}}
    <div class="border-t border-ink">
        @foreach($glyphs as $glyph)
            @php $imgPath = $glyph->preferredImagePath(); @endphp
            <a href="{{ route('rendering', $glyph->barthel_code) }}"
               class="glyph-rendering-row group block py-3 border-b border-rule hover:bg-cream-dark transition-colors -mx-4 px-4"
               data-code="{{ $glyph->barthel_code }}"
               data-renderings="{{ $glyph->renderings->pluck('code')->join(' ') }}">
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-3 flex-shrink-0">
                        <div class="size-10 border border-rule group-hover:border-soviet-red bg-white flex items-center justify-center overflow-hidden transition-colors flex-shrink-0">
                            @if($imgPath)
                                <img src="{{ asset($imgPath) }}"
                                     alt="{{ $glyph->barthel_code }}"
                                     class="w-full h-full object-contain group-hover:scale-110 transition-transform duration-200"
                                     loading="lazy">
                            @else
                                <span class="text-xs font-light text-warm-gray">{{ $glyph->barthel_code }}</span>
                            @endif
                        </div>
                        <span class="text-base font-semibold tabular-nums group-hover:text-soviet-red transition-colors">{{ $glyph->barthel_code }}</span>
                    </div>

                    <div class="flex flex-wrap gap-1.5 flex-1 min-w-0">
                        @foreach($glyph->renderings as $rendering)
                            <span class="inline-flex items-baseline gap-1 px-2 py-0.5 border border-rule text-[11px] tabular-nums font-medium bg-white">
                                {{ $rendering->code }}
                                @if($rendering->tablet_renderings_count > 0)
                                    <span class="text-[9px] text-warm-gray">&times;{{ $rendering->tablet_renderings_count }}</span>
                                @endif
                            </span>
                        @endforeach
                    </div>

                    <span class="text-xs text-warm-gray tabular-nums flex-shrink-0 hidden sm:block">
                        {{ $glyph->renderings->count() }} {{ __('front.glyph.count_renderings') }}
                    </span>
                </div>
            </a>
        @endforeach
    </div>

    {{-- Prev / Next --}}
    <div class="flex items-center justify-between mt-8">
        @if($pagination['currentIndex'] > 0)
            @php $prevPage = $pagination['currentIndex']; @endphp
            <a href="{{ $prevPage === 1 ? route('renderings') : route('renderings', ['page' => $prevPage]) }}"
               class="text-sm text-warm-gray hover:text-ink transition-colors tabular-nums">
                &larr; {{ str_pad($pagination['groupKeys'][$pagination['currentIndex'] - 1], 3, '0', STR_PAD_LEFT) }}
            </a>
        @else
            <span></span>
        @endif

        @if($pagination['currentIndex'] < $pagination['totalPages'] - 1)
            @php $nextPage = $pagination['currentIndex'] + 2; @endphp
            <a href="{{ route('renderings', ['page' => $nextPage]) }}"
               class="text-sm text-warm-gray hover:text-ink transition-colors tabular-nums">
                {{ str_pad($pagination['groupKeys'][$pagination['currentIndex'] + 1], 3, '0', STR_PAD_LEFT) }} &rarr;
            </a>
        @else
            <span></span>
        @endif
    </div>

    <script>
        document.getElementById('rendering-search').addEventListener('input', function () {
            const q = this.value.trim().toLowerCase();
            document.querySelectorAll('.glyph-rendering-row').forEach(function (el) {
                if (!q) { el.style.display = ''; return; }
                var match = el.dataset.code.includes(q) || el.dataset.renderings.toLowerCase().includes(q);
                el.style.display = match ? '' : 'none';
            });
        });
    </script>
@endsection

@section('footer')
    <p class="text-[11px] text-warm-gray tracking-wider tabular-nums">
        {{ $totalRenderings }} {{ __('front.renderings.title') }}
    </p>
@endsection
