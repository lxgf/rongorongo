@extends('layouts.public')

@section('title', $tablet->code . ' ' . $tablet->name . ' — ' . __('front.site_title'))

@section('content')
    {{-- Header --}}
    <div class="mb-8">
        <div class="flex items-baseline gap-3 mb-1">
            <span class="text-3xl sm:text-4xl font-semibold text-soviet-red">{{ $tablet->code }}</span>
            <div>
                <h1 class="text-2xl sm:text-3xl font-semibold tracking-tight leading-tight">{{ $tablet->name }}</h1>
                @if(app()->getLocale() === 'ru' && $tablet->name_ru)
                    <p class="text-[12px] text-warm-gray italic leading-snug mt-0.5">{{ $tablet->name_ru }}</p>
                @endif
            </div>
        </div>
        @if($tablet->location)
            <p class="text-sm text-warm-gray">{{ $tablet->location }}</p>
        @endif
        @if($tablet->description)
            <p class="text-sm text-warm-gray mt-1">{{ $tablet->description }}</p>
        @endif
    </div>

    {{-- Photograph gallery --}}
    @if($tablet->images->count())
        @php
            $photoUrls = $tablet->images->map(fn($img) => asset($img->path))->values();
        @endphp
        <section class="mb-10"
                 data-tablet-gallery
                 data-sources='@json($photoUrls)'
                 data-lightbox-key="tablet-{{ $tablet->code }}">

            {{-- Hidden fslightbox triggers --}}
            @foreach($tablet->images as $image)
                <a data-fslightbox="tablet-{{ $tablet->code }}"
                   href="{{ asset($image->path) }}"
                   class="hidden"
                   aria-hidden="true"></a>
            @endforeach

            {{-- Section header --}}
            <div class="flex items-center gap-4 mb-3">
                <h2 class="text-[11px] font-medium tracking-[0.15em] uppercase whitespace-nowrap">
                    {{ __('front.tablet.photographs') }}
                </h2>
                <span data-gallery-counter
                      class="text-[11px] text-warm-gray tabular-nums whitespace-nowrap">
                    01 / {{ str_pad($tablet->images->count(), 2, '0', STR_PAD_LEFT) }}
                </span>
                <div class="flex-1 border-t border-ink"></div>
            </div>

            {{-- Main image stage --}}
            <div class="gallery-stage">
                <button data-gallery-prev
                        class="gallery-arrow gallery-arrow--left"
                        type="button"
                        aria-label="Previous">&lsaquo;</button>

                <div class="gallery-viewport">
                    <img data-gallery-main
                         src="{{ asset($tablet->images->first()->path) }}"
                         class="gallery-main-img"
                         alt="{{ $tablet->code }} {{ $tablet->name }}">
                </div>

                <button data-gallery-next
                        class="gallery-arrow gallery-arrow--right"
                        type="button"
                        aria-label="Next">&rsaquo;</button>
            </div>

            {{-- Thumbnails + attribution --}}
            <div class="flex items-end gap-4 mt-2">
                <div data-gallery-thumbs class="gallery-thumbs flex-1 min-w-0">
                    @foreach($tablet->images as $i => $image)
                        <button class="gallery-thumb {{ $i === 0 ? 'gallery-thumb--active' : '' }}" type="button">
                            <img src="{{ asset($image->path) }}"
                                 alt=""
                                 loading="lazy">
                        </button>
                    @endforeach
                </div>
                <span class="text-[10px] text-warm-gray whitespace-nowrap shrink-0 pb-1">
                    Wikimedia Commons &middot;
                    <a href="https://creativecommons.org/licenses/by-sa/3.0/"
                       target="_blank"
                       rel="noopener"
                       class="hover:text-ink transition-colors">CC BY-SA 3.0</a>
                </span>
            </div>
        </section>
    @endif

    {{-- Chess grid grouped by side --}}
    @foreach($tablet->lines->groupBy('side') as $side => $lines)
        <section class="mb-10">
            <div class="flex items-center gap-4 mb-4">
                <h2 class="text-[11px] font-medium tracking-[0.15em] uppercase whitespace-nowrap">
                    {{ $side === 0 ? __('front.tablet.recto') : __('front.tablet.verso') }}
                </h2>
                <span class="text-[10px] text-warm-gray tabular-nums">
                    {{ $lines->sum(fn($l) => $l->tabletRenderings->count()) }} {{ __('front.tablet.signs') }}
                </span>
                <div class="flex-1 border-t border-ink"></div>
            </div>

            @php
                $sideMaxPos = $lines->flatMap(fn($l) => $l->tabletRenderings->pluck('position'))->max() ?? 0;
            @endphp

            <div class="overflow-x-auto pb-4">
                <table class="border-separate border-spacing-1">
                    {{-- Column numbers header --}}
                    <thead>
                        <tr>
                            <th class="sticky left-0 z-10 bg-cream min-w-[2.5rem]"></th>
                            @for($p = 1; $p <= $sideMaxPos; $p++)
                                <th class="px-0 pb-1 text-sm tabular-nums text-warm-gray font-medium text-center min-w-[7rem]">
                                    {{ $p }}
                                </th>
                            @endfor
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($lines as $line)
                            @php
                                $renderingsByPos = $line->tabletRenderings->keyBy('position');
                                $lineMaxPos = $line->tabletRenderings->max('position') ?? 0;
                            @endphp
                            <tr>
                                {{-- Row label --}}
                                <td class="sticky left-0 z-10 bg-cream pr-2 align-middle">
                                    <a href="{{ route('line', [$tablet->code, $line->side === 0 ? 'r' : 'v', $line->line]) }}"
                                       class="flex items-center gap-1 text-base font-medium text-warm-gray hover:text-soviet-red transition-colors whitespace-nowrap tabular-nums">
                                        <span>{{ $line->line }}</span>
                                        <span class="text-xs">{!! $line->direction === 'ltr' ? '&rarr;' : '&larr;' !!}</span>
                                    </a>
                                </td>

                                @for($p = 1; $p <= $sideMaxPos; $p++)
                                    @if($renderingsByPos->has($p))
                                        @php
                                            $tr = $renderingsByPos->get($p);

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

                                        <td class="p-0 align-middle">
                                            <div class="border border-rule hover:border-soviet-red transition-colors
                                                        min-w-[7rem] h-32 flex flex-col items-center justify-center relative
                                                        {{ $isCompound ? 'bg-cream-dark' : 'bg-white' }}">
                                                @if($tr->rendering_id && $tr->rendering)
                                                    @php $imgPath = $tr->preferredImagePath(); @endphp
                                                    <a href="{{ route('glyph', $tr->rendering->glyph->barthel_code) }}"
                                                       class="flex items-center justify-center w-full h-full {{ $modClasses }}"
                                                       title="{{ $tr->rendering->code }}{{ $modSymbols ? ' ['.$modSymbols.']' : '' }}">
                                                        @if($imgPath)
                                                            <img src="{{ asset($imgPath) }}"
                                                                 class="max-h-28 w-auto object-contain"
                                                                 alt="{{ $tr->rendering->glyph->barthel_code }}"
                                                                 loading="lazy">
                                                        @else
                                                            <span class="text-base text-warm-gray font-medium tabular-nums">
                                                                {{ $tr->rendering->glyph->barthel_code }}
                                                            </span>
                                                        @endif
                                                    </a>
                                                    <span class="absolute bottom-0 left-0 right-0 text-center text-[11px] tabular-nums text-warm-gray leading-tight pb-0.5">
                                                        {{ $tr->rendering->code }}
                                                    </span>

                                                @elseif($isCompound)
                                                    @php $compoundImages = $tr->images->sortBy('sort_order')->values(); @endphp
                                                    <div class="flex items-center justify-center gap-px w-full h-full {{ $modClasses }}"
                                                         title="{{ $tr->compoundGlyph->code }}{{ $modSymbols ? ' ['.$modSymbols.']' : '' }}">
                                                        @foreach($tr->compoundGlyph->parts as $pi => $part)
                                                            @php $imgPath = $compoundImages[$pi]->path ?? $part->glyph->preferredImagePath(); @endphp
                                                            <a href="{{ route('glyph', $part->glyph->barthel_code) }}"
                                                               class="inline-block"
                                                               title="{{ $part->glyph->barthel_code }}">
                                                                @if($imgPath)
                                                                    <img src="{{ asset($imgPath) }}"
                                                                         class="max-h-24 w-auto object-contain"
                                                                         alt="{{ $part->glyph->barthel_code }}"
                                                                         loading="lazy">
                                                                @else
                                                                    <span class="text-sm text-warm-gray">{{ $part->glyph->barthel_code }}</span>
                                                                @endif
                                                            </a>
                                                        @endforeach
                                                    </div>
                                                    <span class="absolute bottom-0 left-0 right-0 text-center text-[10px] tabular-nums text-deep-blue leading-tight pb-0.5">
                                                        {{ $tr->compoundGlyph->code }}
                                                    </span>
                                                @endif

                                                @if($modSymbols)
                                                    <span class="absolute -top-1.5 -right-1 text-[11px] text-soviet-red font-bold leading-none bg-cream px-0.5">
                                                        {{ $modSymbols }}
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                    @else
                                        @if($p <= $lineMaxPos)
                                            <td class="p-0 align-middle">
                                                <div class="min-w-[7rem] h-32 border border-rule/30 bg-cream-dark/50"></div>
                                            </td>
                                        @else
                                            <td class="p-0"></td>
                                        @endif
                                    @endif
                                @endfor
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    @endforeach

    {{-- Back link --}}
    <div class="mt-4">
        <a href="{{ route('tablets') }}" class="text-sm text-warm-gray hover:text-ink transition-colors">
            &larr; {{ __('front.nav.tablets') }}
        </a>
    </div>
@endsection
