@extends('layouts.public')

@section('title', app()->getLocale() === 'ru'
    ? 'Лигатуры ронгоронго' . ($pagination['currentPage'] > 1 ? ' — Стр. ' . $pagination['currentPage'] : '') . ' — Составные глифы'
    : 'Rongorongo Ligatures' . ($pagination['currentPage'] > 1 ? ' — Page ' . $pagination['currentPage'] : '') . ' — Compound Glyphs')
@section('meta_description', app()->getLocale() === 'ru'
    ? 'Составные глифы (лигатуры) ронгоронго. ' . $pagination['total'] . ' комбинаций. Страница ' . $pagination['currentPage'] . ' из ' . $pagination['totalPages'] . '.'
    : 'Rongorongo compound glyphs (ligatures) from the Easter Island script. ' . $pagination['total'] . ' fused sign combinations. Page ' . $pagination['currentPage'] . ' of ' . $pagination['totalPages'] . '.')
@section('canonical', $pagination['currentPage'] === 1 ? route('ligatures') : route('ligatures', ['page' => $pagination['currentPage']]))

@section('content')
    {{-- Definition block --}}
    <div class="mb-10 border-t-[3px] border-soviet-red pt-4 pb-5 border-b border-rule">
        <div class="flex items-start gap-4 sm:gap-6">
            <span class="text-[40px] sm:text-[52px] font-bold leading-none text-soviet-red select-none tracking-tight" aria-hidden="true">{{ mb_substr(__('front.ligatures.title'), 0, 1) }}</span>
            <div class="pt-0.5">
                <h1 class="text-[11px] font-medium tracking-[0.2em] uppercase mb-2">{{ __('front.ligatures.title') }}</h1>
                <p class="text-sm leading-relaxed text-ink/80 max-w-2xl">{{ __('front.ligatures.definition') }}</p>
            </div>
        </div>
    </div>

    {{-- Notation block --}}
    <div class="mb-10">
        <div class="flex items-center gap-4 mb-4">
            <h2 class="text-[11px] font-medium tracking-[0.15em] uppercase whitespace-nowrap">
                {{ __('front.ligatures.notation_title') }}
            </h2>
            <div class="flex-1 border-t border-rule"></div>
        </div>
        <div class="flex items-start gap-4 max-w-xl">
            <span class="text-lg font-semibold leading-none text-soviet-red select-none tabular-nums mt-0.5">001.006</span>
            <div class="border-l border-rule pl-4">
                <p class="text-sm text-ink/70 leading-relaxed">{{ __('front.ligatures.notation_code') }}</p>
            </div>
        </div>
    </div>

    {{-- Pagination strip --}}
    @if($pagination['totalPages'] > 1)
        <div class="flex items-center gap-px mb-8 flex-wrap">
            @for($p = 1; $p <= $pagination['totalPages']; $p++)
                @php $isCurrent = $p === $pagination['currentPage']; @endphp
                <a href="{{ $p === 1 ? route('ligatures') : route('ligatures', ['page' => $p]) }}"
                   class="w-8 h-8 flex items-center justify-center text-[12px] tabular-nums border transition-colors
                          {{ $isCurrent
                              ? 'bg-soviet-red text-white border-soviet-red font-semibold'
                              : 'bg-white text-warm-gray border-rule hover:border-soviet-red hover:text-ink' }}">{{ $p }}</a>
            @endfor
        </div>
    @endif

    {{-- Search + count --}}
    <div class="flex items-center justify-between mb-8">
        <span class="text-[11px] font-medium tracking-[0.15em] uppercase text-warm-gray">
            {{ $pagination['total'] }} {{ __('front.ligatures.title') }}
            <span class="font-normal">&middot; {{ $pagination['currentPage'] }} / {{ $pagination['totalPages'] }}</span>
        </span>
        <div class="relative w-48 sm:w-64">
            <input type="text"
                   id="compound-search"
                   placeholder="{{ __('front.ligatures.search') }}"
                   class="w-full bg-transparent border-b border-ink pb-1 text-sm
                          placeholder:text-warm-gray focus:outline-none focus:border-soviet-red
                          transition-colors tabular-nums"
                   autocomplete="off">
        </div>
    </div>

    {{-- Compound glyphs list --}}
    <div class="border-t border-ink">
        @foreach($ligatures as $ligature)
            <div class="compound-item group py-4 border-b border-rule hover:bg-cream-dark transition-colors -mx-4 px-4"
                 data-code="{{ $ligature->code }}">
                <div class="flex items-center gap-6">
                    <span class="text-lg sm:text-xl font-semibold text-soviet-red tabular-nums min-w-[4rem] flex-shrink-0">
                        {{ $ligature->code }}
                    </span>

                    <div class="flex items-center gap-1 flex-shrink-0">
                        @foreach($ligature->parts as $part)
                            @php $imgPath = $part->glyph->preferredImagePath(); @endphp
                            <a href="{{ route('glyph', $part->glyph->barthel_code) }}"
                               class="size-8 border border-rule bg-white flex items-center justify-center overflow-hidden flex-shrink-0 hover:border-soviet-red transition-colors"
                               title="{{ $part->glyph->barthel_code }}">
                                @if($imgPath)
                                    <img src="{{ asset($imgPath) }}"
                                         class="max-w-full max-h-full object-contain p-0.5"
                                         alt="{{ $part->glyph->barthel_code }}"
                                         loading="lazy">
                                @else
                                    <span class="text-[9px] text-warm-gray font-medium tabular-nums">
                                        {{ $part->glyph->barthel_code }}
                                    </span>
                                @endif
                            </a>
                            @if(!$loop->last)
                                <span class="text-warm-gray text-xs">+</span>
                            @endif
                        @endforeach
                    </div>

                    <div class="flex-1 border-t border-dotted border-warm-gray self-center hidden sm:block"></div>

                    <span class="hidden sm:block text-sm text-warm-gray tabular-nums flex-shrink-0">
                        {{ $ligature->parts->pluck('glyph.barthel_code')->join(' + ') }}
                    </span>

                    <span class="text-sm text-warm-gray tabular-nums flex-shrink-0">
                        &times;{{ $ligature->tablet_renderings_count }}
                    </span>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Bottom pagination --}}
    @if($pagination['totalPages'] > 1)
        <div class="flex items-center justify-between mt-8">
            @if($pagination['currentPage'] > 1)
                @php $prev = $pagination['currentPage'] - 1; @endphp
                <a href="{{ $prev === 1 ? route('ligatures') : route('ligatures', ['page' => $prev]) }}"
                   class="text-sm text-warm-gray hover:text-ink transition-colors">&larr; {{ $prev }}</a>
            @else
                <span></span>
            @endif

            <span class="text-[11px] text-warm-gray tabular-nums">{{ $pagination['currentPage'] }} / {{ $pagination['totalPages'] }}</span>

            @if($pagination['currentPage'] < $pagination['totalPages'])
                @php $next = $pagination['currentPage'] + 1; @endphp
                <a href="{{ route('ligatures', ['page' => $next]) }}"
                   class="text-sm text-warm-gray hover:text-ink transition-colors">{{ $next }} &rarr;</a>
            @else
                <span></span>
            @endif
        </div>
    @endif

    <script>
        document.getElementById('compound-search').addEventListener('input', function () {
            const q = this.value.trim().toLowerCase();
            document.querySelectorAll('.compound-item').forEach(function (el) {
                el.style.display = !q || el.dataset.code.toLowerCase().includes(q) ? '' : 'none';
            });
        });
    </script>
@endsection

@section('footer')
    <p class="text-[11px] text-warm-gray tracking-wider tabular-nums">
        {{ $pagination['total'] }} {{ __('front.ligatures.title') }}
    </p>
@endsection
