@extends('layouts.public')

@section('title', __('front.renderings.title') . ' ' . $glyph->barthel_code . ' — ' . __('front.site_title'))

@section('content')
    {{-- Back + Prev/Next --}}
    <div class="flex items-center justify-between mb-8">
        <a href="{{ route('renderings') }}"
           class="text-sm text-warm-gray hover:text-ink transition-colors">
            &larr; {{ __('front.renderings.back') }}
        </a>
        <div class="flex items-center gap-4">
            @if($prev)
                <a href="{{ route('rendering', $prev->barthel_code) }}"
                   class="text-sm text-warm-gray hover:text-ink transition-colors tabular-nums">
                    &larr; {{ $prev->barthel_code }}
                </a>
            @endif
            @if($next)
                <a href="{{ route('rendering', $next->barthel_code) }}"
                   class="text-sm text-warm-gray hover:text-ink transition-colors tabular-nums">
                    {{ $next->barthel_code }} &rarr;
                </a>
            @endif
        </div>
    </div>

    {{-- Glyph specimen --}}
    <div class="flex flex-col items-center mb-10">
        <div class="w-32 h-32 sm:w-40 sm:h-40 border border-ink flex items-center justify-center bg-white mb-4">
            @if($glyph->images->first())
                <img src="{{ asset($glyph->images->first()->path) }}"
                     alt="{{ $glyph->barthel_code }}"
                     class="max-w-full max-h-full object-contain p-4">
            @else
                <span class="text-3xl font-light text-warm-gray">{{ $glyph->barthel_code }}</span>
            @endif
        </div>
        <h1 class="text-2xl font-semibold tabular-nums">{{ $glyph->barthel_code }}</h1>
        @if($glyph->description)
            <p class="text-sm text-warm-gray mt-1 text-center max-w-md">{{ $glyph->description }}</p>
        @endif
    </div>

    {{-- Stats --}}
    <div class="flex justify-center gap-4 sm:gap-6 text-sm text-warm-gray mb-10 flex-wrap">
        <span class="tabular-nums">{{ $glyph->renderings->count() }} {{ __('front.glyph.count_renderings') }}</span>
        <span>&middot;</span>
        <span class="tabular-nums">{{ $totalOccurrences }} {{ __('front.glyph.count_occurrences') }}</span>
    </div>

    {{-- Renderings with occurrences --}}
    @foreach($glyph->renderings as $rendering)
        <section class="mb-8">
            {{-- Rendering header --}}
            <div class="flex items-center gap-4 mb-3">
                <div class="size-10 border border-ink bg-white flex items-center justify-center overflow-hidden flex-shrink-0">
                    @if($glyph->images->first())
                        <img src="{{ asset($glyph->images->first()->path) }}"
                             alt="{{ $rendering->code }}"
                             class="max-w-full max-h-full object-contain p-1"
                             loading="lazy">
                    @else
                        <span class="text-xs font-light text-warm-gray">{{ $glyph->barthel_code }}</span>
                    @endif
                </div>
                <h2 class="text-base font-semibold tabular-nums whitespace-nowrap">
                    {{ $rendering->code }}
                </h2>
                <div class="flex-1 border-t border-ink"></div>
                <span class="text-xs text-warm-gray tabular-nums">
                    {{ $rendering->tabletRenderings->count() }} {{ __('front.renderings.occurrences') }}
                </span>
            </div>

            {{-- Occurrences --}}
            @if($rendering->tabletRenderings->isNotEmpty())
                <div class="space-y-0">
                    @foreach($rendering->tabletRenderings as $occ)
                        @php
                            $modifierKeys = ['is_inverted', 'is_mirrored', 'is_small', 'is_enlarged', 'is_truncated', 'is_distorted', 'is_uncertain', 'is_nonstandard'];
                            $activeModifiers = collect($modifierKeys)->filter(fn($k) => $occ->$k)->map(fn($k) => __('front.renderings.modifiers.' . $k))->values();
                            $modifierText = $activeModifiers->isNotEmpty() ? $activeModifiers->join(', ') : null;
                            $source = __('front.renderings.from_tablet', [
                                'tablet' => $occ->tabletLine->tablet->name . ' (' . $occ->tabletLine->tablet->code . ')',
                                'line' => ($occ->tabletLine->side === 0 ? __('front.glyph.recto') : __('front.glyph.verso')) . ' ' . $occ->tabletLine->line,
                                'position' => $occ->position,
                            ]);
                        @endphp
                        <div class="py-1.5 border-b border-rule/50 flex flex-wrap items-baseline gap-x-2 text-sm hover:bg-cream-dark transition-colors -mx-4 px-4">
                            @if($modifierText)
                                <span class="font-medium text-soviet-red">{{ $modifierText }}</span>
                                <span class="text-warm-gray">&mdash;</span>
                            @endif
                            <a href="{{ route('tablet', $occ->tabletLine->tablet->code) }}"
                               class="text-ink/70 hover:text-soviet-red transition-colors">{{ $source }}</a>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>
    @endforeach
@endsection
