@extends('layouts.public')

@section('title', $glyph->barthel_code . ' — ' . __('front.site_title'))

@section('content')
    {{-- Prev / Next navigation --}}
    <div class="flex items-center justify-between mb-8">
        @if($prev)
            <a href="{{ route('glyph', $prev->barthel_code) }}"
               class="text-sm text-warm-gray hover:text-ink transition-colors tabular-nums">
                &larr; {{ $prev->barthel_code }}
            </a>
        @else
            <span></span>
        @endif
        @if($next)
            <a href="{{ route('glyph', $next->barthel_code) }}"
               class="text-sm text-warm-gray hover:text-ink transition-colors tabular-nums">
                {{ $next->barthel_code }} &rarr;
            </a>
        @else
            <span></span>
        @endif
    </div>

    {{-- Glyph specimen --}}
    <div class="flex flex-col items-center mb-10">
        <div class="w-40 h-40 sm:w-52 sm:h-52 border border-ink flex items-center justify-center bg-white mb-4">
            @if($glyph->images->first())
                <img src="{{ asset($glyph->images->first()->path) }}"
                     alt="{{ $glyph->barthel_code }}"
                     class="max-w-full max-h-full object-contain p-4">
            @else
                <span class="text-4xl font-light text-warm-gray">{{ $glyph->barthel_code }}</span>
            @endif
        </div>
        <h1 class="text-3xl font-semibold tabular-nums">{{ $glyph->barthel_code }}</h1>
        @if($glyph->description)
            <p class="text-sm text-warm-gray mt-1 text-center max-w-md">{{ $glyph->description }}</p>
        @endif
    </div>

    {{-- Quick stats --}}
    <div class="flex justify-center gap-4 sm:gap-6 text-sm text-warm-gray mb-10 flex-wrap">
        <span class="tabular-nums">{{ $glyph->renderings->count() }} {{ __('front.glyph.count_renderings') }}</span>
        <span class="hidden sm:inline">&middot;</span>
        <span class="tabular-nums">{{ $occurrences->count() }} {{ __('front.glyph.count_occurrences') }}</span>
        <span class="hidden sm:inline">&middot;</span>
        <span class="tabular-nums">{{ $ligatures->count() }} {{ __('front.glyph.count_ligatures') }}</span>
    </div>

    {{-- Renderings --}}
    @if($glyph->renderings->isNotEmpty())
        <section class="mb-10">
            <div class="flex items-center gap-4 mb-4">
                <h2 class="text-[11px] font-medium tracking-[0.15em] uppercase whitespace-nowrap">
                    {{ __('front.glyph.renderings') }}
                </h2>
                <div class="flex-1 border-t border-ink"></div>
                <a href="{{ route('rendering', $glyph->barthel_code) }}"
                   class="text-[11px] text-warm-gray hover:text-soviet-red transition-colors tracking-wider whitespace-nowrap">
                    {{ __('front.glyph.all_occurrences') }} &rarr;
                </a>
            </div>
            <div class="flex flex-wrap gap-2">
                @foreach($glyph->renderings as $rendering)
                    <a href="{{ route('rendering', $glyph->barthel_code) }}"
                       class="group inline-flex items-baseline gap-1.5 px-3 py-1 border border-rule hover:border-soviet-red text-sm tabular-nums font-medium transition-colors">
                        <span class="group-hover:text-soviet-red transition-colors">{{ $rendering->code }}</span>
                        @if($rendering->tablet_renderings_count > 0)
                            <span class="text-[9px] text-warm-gray">&times;{{ $rendering->tablet_renderings_count }}</span>
                        @endif
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Occurrences --}}
    @if($occurrences->isNotEmpty())
        <section class="mb-10">
            <div class="flex items-center gap-4 mb-4">
                <h2 class="text-[11px] font-medium tracking-[0.15em] uppercase whitespace-nowrap">
                    {{ __('front.glyph.occurrences') }}
                </h2>
                <div class="flex-1 border-t border-ink"></div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-ink text-left">
                            <th class="py-2 pr-4 font-medium text-[10px] tracking-[0.1em] uppercase">{{ __('front.glyph.tablet') }}</th>
                            <th class="py-2 pr-4 font-medium text-[10px] tracking-[0.1em] uppercase">{{ __('front.glyph.tablet_code') }}</th>
                            <th class="py-2 pr-4 font-medium text-[10px] tracking-[0.1em] uppercase">{{ __('front.glyph.side') }}</th>
                            <th class="py-2 pr-4 font-medium text-[10px] tracking-[0.1em] uppercase">{{ __('front.glyph.line') }}</th>
                            <th class="py-2 pr-4 font-medium text-[10px] tracking-[0.1em] uppercase">{{ __('front.glyph.position') }}</th>
                            <th class="py-2 pr-4 font-medium text-[10px] tracking-[0.1em] uppercase">{{ __('front.glyph.rendering') }}</th>
                            <th class="py-2 font-medium text-[10px] tracking-[0.1em] uppercase">{{ __('front.glyph.modifiers') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($occurrences as $occ)
                            @php
                                $modifierKeys = ['is_inverted', 'is_mirrored', 'is_small', 'is_enlarged', 'is_truncated', 'is_distorted', 'is_uncertain', 'is_nonstandard'];
                                $activeModifiers = collect($modifierKeys)->filter(fn($k) => $occ->$k)->map(fn($k) => __('front.renderings.modifiers.' . $k))->values();
                            @endphp
                            <tr class="border-b border-rule hover:bg-cream-dark transition-colors">
                                <td class="py-1.5 pr-4">
                                    <a href="{{ route('tablet', $occ->tabletLine->tablet->code) }}"
                                       class="hover:text-soviet-red transition-colors">
                                        {{ $occ->tabletLine->tablet->name }}
                                    </a>
                                </td>
                                <td class="py-1.5 pr-4 tabular-nums text-warm-gray">
                                    {{ $occ->tabletLine->tablet->code }}
                                </td>
                                <td class="py-1.5 pr-4 text-warm-gray">
                                    {{ $occ->tabletLine->side === 0 ? __('front.glyph.recto') : __('front.glyph.verso') }}
                                </td>
                                <td class="py-1.5 pr-4 tabular-nums">{{ $occ->tabletLine->line }}</td>
                                <td class="py-1.5 pr-4 tabular-nums">{{ $occ->position }}</td>
                                <td class="py-1.5 pr-4 tabular-nums">{{ $occ->rendering->code }}</td>
                                <td class="py-1.5 text-soviet-red text-xs font-medium">
                                    {{ $activeModifiers->join(', ') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    @endif

    {{-- Compound glyphs (ligatures) --}}
    @if($ligatures->isNotEmpty())
        <section class="mb-10">
            <div class="flex items-center gap-4 mb-4">
                <h2 class="text-[11px] font-medium tracking-[0.15em] uppercase whitespace-nowrap">
                    {{ __('front.glyph.ligatures') }}
                </h2>
                <div class="flex-1 border-t border-ink"></div>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3">
                @foreach($ligatures as $ligature)
                    <div class="border border-rule p-3 hover:border-soviet-red transition-colors">
                        <div class="flex items-center gap-1 mb-2 min-h-[2rem]">
                            @foreach($ligature->parts as $part)
                                @php $img = $part->glyph->images->first(); @endphp
                                <a href="{{ route('glyph', $part->glyph->barthel_code) }}"
                                   class="size-8 border border-rule bg-white flex items-center justify-center overflow-hidden flex-shrink-0 hover:border-soviet-red transition-colors"
                                   title="{{ $part->glyph->barthel_code }}">
                                    @if($img)
                                        <img src="{{ asset($img->path) }}"
                                             class="max-w-full max-h-full object-contain p-0.5"
                                             alt="{{ $part->glyph->barthel_code }}"
                                             loading="lazy">
                                    @else
                                        <span class="text-[9px] text-warm-gray">{{ $part->glyph->barthel_code }}</span>
                                    @endif
                                </a>
                                @if(!$loop->last)
                                    <span class="text-rule text-[10px]">&middot;</span>
                                @endif
                            @endforeach
                        </div>
                        <div class="text-[11px] tabular-nums text-warm-gray font-medium">{{ $ligature->code }}</div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif
@endsection
