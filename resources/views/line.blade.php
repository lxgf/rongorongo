@extends('layouts.public')

@section('title', $tablet->code . ' ' . ($line->side === 0 ? 'r' : 'v') . $line->line . ' — ' . __('front.site_title'))

@section('content')
    {{-- Prev / Next navigation --}}
    <div class="flex items-center justify-between mb-6">
        @if($prev)
            <a href="{{ route('line', [$tablet->code, $prev->side === 0 ? 'r' : 'v', $prev->line]) }}"
               class="text-sm text-warm-gray hover:text-ink transition-colors tabular-nums">
                &larr; {{ $prev->side === 0 ? 'r' : 'v' }}{{ $prev->line }}
            </a>
        @else
            <span></span>
        @endif

        <a href="{{ route('lines', ['tablet' => $tablet->code]) }}"
           class="text-[10px] tracking-[0.1em] uppercase text-warm-gray hover:text-soviet-red transition-colors">
            {{ __('front.lines.back_to_lines') }}
        </a>

        @if($next)
            <a href="{{ route('line', [$tablet->code, $next->side === 0 ? 'r' : 'v', $next->line]) }}"
               class="text-sm text-warm-gray hover:text-ink transition-colors tabular-nums">
                {{ $next->side === 0 ? 'r' : 'v' }}{{ $next->line }} &rarr;
            </a>
        @else
            <span></span>
        @endif
    </div>

    {{-- Header --}}
    <div class="mb-8">
        <div class="flex items-baseline gap-3 mb-1">
            <a href="{{ route('tablet', $tablet->code) }}"
               class="text-3xl sm:text-4xl font-semibold text-soviet-red hover:text-ink transition-colors">{{ $tablet->code }}</a>
            <h1 class="text-2xl sm:text-3xl font-semibold tracking-tight">
                {{ $tablet->name }}
                <span class="text-warm-gray font-normal">/</span>
                {{ $line->side === 0 ? __('front.lines.recto') : __('front.lines.verso') }}
                {{ $line->line }}
            </h1>
        </div>
        <div class="flex gap-4 text-sm text-warm-gray">
            <span>{{ $line->tabletRenderings->count() }} {{ __('front.lines.signs') }}</span>
            <span>&middot;</span>
            <span>{{ $line->direction === 'ltr' ? __('front.lines.direction_ltr') : __('front.lines.direction_rtl') }}</span>
        </div>
    </div>

    {{-- Grid of glyphs --}}
    <div class="grid grid-cols-8 gap-2">
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

                $isCompound = $tr->compound_glyph_id && $tr->compoundGlyph;
            @endphp

            <div class="relative flex flex-col items-center w-full
                        border border-rule hover:border-soviet-red transition-colors
                        {{ $isCompound ? 'bg-cream-dark' : 'bg-white' }}">
                {{-- Position number --}}
                <div class="w-full text-center text-[11px] tabular-nums text-warm-gray font-medium py-0.5 border-b border-rule/50">
                    {{ $tr->position }}
                </div>

                {{-- Glyph image --}}
                <div class="flex items-center justify-center w-full h-24 px-1">
                    @if($tr->rendering_id && $tr->rendering)
                        @php $imgPath = $tr->rendering->glyph->preferredImagePath(); @endphp
                        <a href="{{ route('glyph', $tr->rendering->glyph->barthel_code) }}"
                           class="flex items-center justify-center w-full h-full {{ $modClasses }}"
                           title="{{ $tr->rendering->code }}{{ $modSymbols ? ' ['.$modSymbols.']' : '' }}">
                            @if($imgPath)
                                <img src="{{ asset($imgPath) }}"
                                     class="max-h-20 w-auto object-contain"
                                     alt="{{ $tr->rendering->glyph->barthel_code }}"
                                     loading="lazy">
                            @else
                                <span class="text-sm text-warm-gray font-medium tabular-nums">
                                    {{ $tr->rendering->glyph->barthel_code }}
                                </span>
                            @endif
                        </a>

                    @elseif($isCompound)
                        <div class="flex items-center justify-center gap-px w-full h-full {{ $modClasses }}"
                             title="{{ $tr->compoundGlyph->code }}{{ $modSymbols ? ' ['.$modSymbols.']' : '' }}">
                            @foreach($tr->compoundGlyph->parts as $part)
                                @php $imgPath = $part->glyph->preferredImagePath(); @endphp
                                <a href="{{ route('glyph', $part->glyph->barthel_code) }}"
                                   class="inline-block"
                                   title="{{ $part->glyph->barthel_code }}">
                                    @if($imgPath)
                                        <img src="{{ asset($imgPath) }}"
                                             class="max-h-16 w-auto object-contain"
                                             alt="{{ $part->glyph->barthel_code }}"
                                             loading="lazy">
                                    @else
                                        <span class="text-xs text-warm-gray">{{ $part->glyph->barthel_code }}</span>
                                    @endif
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Code label --}}
                <div class="w-full text-center text-[10px] tabular-nums leading-tight pb-0.5
                            {{ $isCompound ? 'text-deep-blue' : 'text-warm-gray' }}">
                    @if($tr->rendering_id && $tr->rendering)
                        {{ $tr->rendering->code }}
                    @elseif($isCompound)
                        {{ $tr->compoundGlyph->code }}
                    @endif
                </div>

                {{-- Modifier badge --}}
                @if($modSymbols)
                    <span class="absolute -top-1.5 -right-1 text-[8px] text-soviet-red font-bold leading-none bg-cream px-0.5">
                        {{ $modSymbols }}
                    </span>
                @endif
            </div>
        @endforeach
    </div>

    {{-- Back link --}}
    <div class="mt-8">
        <a href="{{ route('tablet', $tablet->code) }}" class="text-sm text-warm-gray hover:text-ink transition-colors">
            &larr; {{ $tablet->name }}
        </a>
    </div>
@endsection
