@extends('layouts.public')

@section('title', 'Rongorongo Glyph ' . $glyph->barthel_code . ' — Easter Island Script Sign')
@section('meta_description', 'Rongorongo glyph ' . $glyph->barthel_code . ' (Barthel code) from the Easter Island undeciphered script. ' . $glyph->renderings->count() . ' graphic variants, ' . $occurrences->count() . ' occurrences across Rapa Nui tablets.')
@section('canonical', route('glyph', $glyph->barthel_code))
@section('og_type', 'article')
@section('og_image', $glyph->preferredImagePath() ? asset($glyph->preferredImagePath()) : '')

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
            @if($imgPath = $glyph->preferredImagePath())
                <img src="{{ asset($imgPath) }}"
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

    {{-- Meaning --}}
    @if($glyph->meaning)
        <section class="mb-10">
            <div class="flex items-center gap-4 mb-4">
                <h2 class="text-[11px] font-medium tracking-[0.15em] uppercase whitespace-nowrap">
                    Meaning
                </h2>
                @if($glyph->meaning_status === 'confirmed')
                    <span class="text-[9px] font-semibold tracking-wider uppercase px-2 py-0.5 bg-green-600 text-white">Confirmed</span>
                @else
                    <span class="text-[9px] font-semibold tracking-wider uppercase px-2 py-0.5 bg-amber-500 text-white">Proposed</span>
                @endif
                <div class="flex-1 border-t border-ink"></div>
            </div>
            <div class="border-l-2 {{ $glyph->meaning_status === 'confirmed' ? 'border-green-600' : 'border-amber-500' }} pl-4 max-w-2xl">
                <p class="text-base font-semibold text-ink">{{ $glyph->meaning }}</p>
                @if($glyph->meaning_source)
                    <p class="text-sm text-warm-gray mt-1">{{ $glyph->meaning_source }}</p>
                @endif
            </div>
        </section>
    @endif

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

    {{-- Occurrences — compact specimen sheet --}}
    @if($occurrences->isNotEmpty())
        <section class="mb-10">
            <div class="flex items-center gap-4 mb-4">
                <h2 class="text-[11px] font-medium tracking-[0.15em] uppercase whitespace-nowrap">
                    {{ __('front.glyph.occurrences') }}
                </h2>
                <span class="text-[10px] text-warm-gray tabular-nums">{{ $occurrences->count() }}</span>
                <div class="flex-1 border-t border-ink"></div>
            </div>

            <div class="space-y-3">
                @foreach($occurrences->groupBy(fn($o) => $o->tabletLine->tablet->code) as $tabletCode => $tabletOccs)
                    <div class="flex items-start gap-3">
                        {{-- Tablet label --}}
                        <div class="w-8 shrink-0 pt-1.5 text-right">
                            <span class="text-[11px] font-semibold text-soviet-red tabular-nums leading-none">{{ $tabletCode }}</span>
                            <span class="block text-[8px] text-warm-gray tabular-nums leading-none mt-0.5">{{ $tabletOccs->count() }}</span>
                        </div>

                        {{-- Glyph specimens --}}
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
                                   title="{{ $occ->rendering->code }}{{ $modSymbols ? ' ['.$modSymbols.']' : '' }} — {{ $tabletCode }}{{ $loc }}">
                                    @if($imgPath)
                                        <img src="{{ asset($imgPath) }}"
                                             class="size-8 object-contain group-hover:scale-110 transition-transform"
                                             alt="{{ $loc }}"
                                             loading="lazy">
                                    @else
                                        <span class="text-[8px] text-warm-gray tabular-nums">{{ $occ->rendering->code }}</span>
                                    @endif
                                    <span class="absolute inset-x-0 -bottom-3.5 text-center text-[7px] tabular-nums text-warm-gray leading-none
                                                 opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none z-10">{{ $loc }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endforeach
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
