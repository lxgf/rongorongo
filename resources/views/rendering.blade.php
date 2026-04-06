@extends('layouts.public')

@section('title', 'Rongorongo Glyph ' . $glyph->barthel_code . ' Renderings — Script Variants')
@section('meta_description', 'All graphic renderings of Rongorongo glyph ' . $glyph->barthel_code . '. ' . $glyph->renderings->count() . ' variants with ' . $totalOccurrences . ' occurrences across Easter Island tablets. SVG specimen sheet.')
@section('canonical', route('rendering', $glyph->barthel_code))
@section('og_type', 'article')
@section('og_image', $glyph->preferredImagePath() ? asset($glyph->preferredImagePath()) : '')

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
            @if($imgPath = $glyph->preferredImagePath())
                <img src="{{ asset($imgPath) }}"
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
                    @if($imgPath = $glyph->preferredImagePath())
                        <img src="{{ asset($imgPath) }}"
                             alt="{{ $rendering->code }}"
                             class="w-full h-full object-contain"
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

            {{-- Occurrences — visual grid by tablet --}}
            @if($rendering->tabletRenderings->isNotEmpty())
                <div class="space-y-2">
                    @foreach($rendering->tabletRenderings->groupBy(fn($o) => $o->tabletLine->tablet->code) as $tabletCode => $tabletOccs)
                        <div class="flex items-start gap-3">
                            <div class="w-8 shrink-0 pt-1.5 text-right">
                                <span class="text-[11px] font-semibold text-soviet-red tabular-nums leading-none">{{ $tabletCode }}</span>
                                <span class="block text-[8px] text-warm-gray tabular-nums leading-none mt-0.5">{{ $tabletOccs->count() }}</span>
                            </div>
                            <div class="flex flex-wrap gap-px flex-1 min-w-0">
                                @foreach($tabletOccs as $occ)
                                    @php
                                        $imgPath = $occ->preferredImagePath();
                                        $sideChar = chr(ord('a') + $occ->tabletLine->side);
                                        $loc = $sideChar . $occ->tabletLine->line . ':' . $occ->position;
                                        $modSymbols = collect([
                                            'is_inverted' => 'f', 'is_mirrored' => 'b', 'is_small' => 's',
                                            'is_enlarged' => 'V', 'is_truncated' => 't', 'is_distorted' => 'y',
                                            'is_uncertain' => '?', 'is_nonstandard' => 'x',
                                        ])->filter(fn($sym, $field) => $occ->$field)->values()->join('');
                                    @endphp
                                    <a href="{{ route('line', [$tabletCode, $sideChar, $occ->tabletLine->line]) }}"
                                       class="group relative size-10 bg-white hover:bg-cream-dark flex items-center justify-center transition-colors
                                              {{ $modSymbols ? 'ring-1 ring-inset ring-soviet-red/20' : '' }}"
                                       title="{{ $rendering->code }}{{ $modSymbols ? ' ['.$modSymbols.']' : '' }} — {{ $tabletCode }}{{ $loc }}">
                                        @if($imgPath)
                                            <img src="{{ asset($imgPath) }}"
                                                 class="size-8 object-contain group-hover:scale-110 transition-transform"
                                                 alt="{{ $loc }}"
                                                 loading="lazy">
                                        @else
                                            <span class="text-[8px] text-warm-gray tabular-nums">{{ $rendering->code }}</span>
                                        @endif
                                        <span class="absolute inset-x-0 -bottom-3.5 text-center text-[7px] tabular-nums text-warm-gray leading-none
                                                     opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none z-10">{{ $loc }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>
    @endforeach
@endsection
