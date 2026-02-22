@extends('layouts.public')

@section('title', $tablet->code . ' ' . $tablet->name . ' — ' . __('front.site_title'))

@section('content')
    {{-- Header --}}
    <div class="mb-8">
        <div class="flex items-baseline gap-3 mb-1">
            <span class="text-3xl sm:text-4xl font-semibold text-soviet-red">{{ $tablet->code }}</span>
            <h1 class="text-2xl sm:text-3xl font-semibold tracking-tight">{{ $tablet->name }}</h1>
        </div>
        @if($tablet->location)
            <p class="text-sm text-warm-gray">{{ $tablet->location }}</p>
        @endif
        @if($tablet->description)
            <p class="text-sm text-warm-gray mt-1">{{ $tablet->description }}</p>
        @endif
    </div>

    {{-- Lines grouped by side --}}
    @foreach($tablet->lines->groupBy('side') as $side => $lines)
        <section class="mb-10">
            <div class="flex items-center gap-4 mb-4">
                <h2 class="text-[11px] font-medium tracking-[0.15em] uppercase whitespace-nowrap">
                    {{ $side === 0 ? __('front.tablet.recto') : __('front.tablet.verso') }}
                </h2>
                <div class="flex-1 border-t border-ink"></div>
            </div>

            @foreach($lines as $line)
                <div class="flex items-start gap-2 py-2 border-b border-rule">
                    {{-- Line number --}}
                    <div class="flex-shrink-0 w-8 text-right text-[11px] text-warm-gray font-medium tabular-nums pt-1.5">
                        {{ $line->line }}
                    </div>
                    {{-- Direction arrow --}}
                    <div class="flex-shrink-0 text-[11px] text-warm-gray pt-1.5">
                        {{ $line->direction === 'ltr' ? '&rarr;' : '&larr;' }}
                    </div>
                    {{-- Glyph sequence --}}
                    <div class="flex flex-wrap items-center gap-0.5 min-h-[2.5rem]
                                {{ $line->direction === 'rtl' ? 'flex-row-reverse' : '' }}">
                        @foreach($line->tabletRenderings as $tr)
                            @php
                                $modClasses = collect([
                                    'is_inverted' => 'mod-inverted',
                                    'is_mirrored' => 'mod-mirrored',
                                    'is_small' => 'mod-small',
                                    'is_enlarged' => 'mod-enlarged',
                                    'is_uncertain' => 'mod-uncertain',
                                    'is_nonstandard' => 'mod-nonstandard',
                                    'is_distorted' => 'mod-distorted',
                                    'is_truncated' => 'mod-truncated',
                                ])->filter(fn($cls, $field) => $tr->$field)->values()->join(' ');

                                $modSymbols = collect([
                                    'is_inverted' => 'f', 'is_mirrored' => 'b', 'is_small' => 's',
                                    'is_enlarged' => 'V', 'is_truncated' => 't', 'is_distorted' => 'y',
                                    'is_uncertain' => '?', 'is_nonstandard' => 'x',
                                ])->filter(fn($sym, $field) => $tr->$field)->values()->join('');
                            @endphp

                            @if($tr->rendering_id && $tr->rendering)
                                {{-- Single glyph rendering --}}
                                @php $img = $tr->rendering->glyph->images->first(); @endphp
                                <a href="{{ route('glyph', $tr->rendering->glyph->barthel_code) }}"
                                   class="relative inline-block hover:outline hover:outline-1 hover:outline-soviet-red transition-all {{ $modClasses }}"
                                   title="{{ $tr->rendering->code }}{{ $modSymbols ? ' ['.$modSymbols.']' : '' }}">
                                    @if($img)
                                        <img src="{{ asset($img->path) }}"
                                             class="h-8 w-auto object-contain"
                                             alt="{{ $tr->rendering->glyph->barthel_code }}"
                                             loading="lazy">
                                    @else
                                        <span class="inline-flex items-center justify-center h-8 px-1 text-[9px] text-warm-gray font-medium">
                                            {{ $tr->rendering->glyph->barthel_code }}
                                        </span>
                                    @endif
                                    @if($modSymbols)
                                        <span class="absolute -top-2 -right-1 text-[7px] text-soviet-red font-bold leading-none">
                                            {{ $modSymbols }}
                                        </span>
                                    @endif
                                </a>

                            @elseif($tr->compound_glyph_id && $tr->compoundGlyph)
                                {{-- Compound glyph (ligature) --}}
                                <span class="inline-flex items-center gap-px border-b-2 border-deep-blue/40 pb-0.5 {{ $modClasses }}"
                                      title="{{ $tr->compoundGlyph->code }}{{ $modSymbols ? ' ['.$modSymbols.']' : '' }}">
                                    @foreach($tr->compoundGlyph->parts as $part)
                                        @php $img = $part->glyph->images->first(); @endphp
                                        <a href="{{ route('glyph', $part->glyph->barthel_code) }}"
                                           class="inline-block hover:outline hover:outline-1 hover:outline-soviet-red transition-all"
                                           title="{{ $part->glyph->barthel_code }}">
                                            @if($img)
                                                <img src="{{ asset($img->path) }}"
                                                     class="h-8 w-auto object-contain"
                                                     alt="{{ $part->glyph->barthel_code }}"
                                                     loading="lazy">
                                            @else
                                                <span class="inline-flex items-center justify-center h-8 px-1 text-[9px] text-warm-gray font-medium">
                                                    {{ $part->glyph->barthel_code }}
                                                </span>
                                            @endif
                                        </a>
                                    @endforeach
                                    @if($modSymbols)
                                        <span class="text-[7px] text-soviet-red font-bold leading-none ml-0.5">
                                            {{ $modSymbols }}
                                        </span>
                                    @endif
                                </span>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endforeach
        </section>
    @endforeach

    {{-- Back link --}}
    <div class="mt-4">
        <a href="{{ route('tablets') }}" class="text-sm text-warm-gray hover:text-ink transition-colors">
            &larr; {{ __('front.nav.tablets') }}
        </a>
    </div>
@endsection
