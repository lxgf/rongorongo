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
                <table class="border-collapse">
                    {{-- Column numbers header --}}
                    <thead>
                        <tr>
                            <th class="sticky left-0 z-10 bg-cream min-w-[2.5rem]"></th>
                            @for($p = 1; $p <= $sideMaxPos; $p++)
                                <th class="px-0 pb-1 text-[10px] tabular-nums text-warm-gray font-medium text-center min-w-[4.25rem]">
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
                                       class="flex items-center gap-1 text-[11px] font-medium text-warm-gray hover:text-soviet-red transition-colors whitespace-nowrap tabular-nums">
                                        <span>{{ $line->line }}</span>
                                        <span class="text-[10px]">{!! $line->direction === 'ltr' ? '&rarr;' : '&larr;' !!}</span>
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
                                                        min-w-[4.25rem] h-[4.5rem] flex flex-col items-center justify-center relative
                                                        {{ $isCompound ? 'bg-cream-dark' : 'bg-white' }}">
                                                @if($tr->rendering_id && $tr->rendering)
                                                    @php $img = $tr->rendering->glyph->images->first(); @endphp
                                                    <a href="{{ route('glyph', $tr->rendering->glyph->barthel_code) }}"
                                                       class="flex items-center justify-center w-full h-full {{ $modClasses }}"
                                                       title="{{ $tr->rendering->code }}{{ $modSymbols ? ' ['.$modSymbols.']' : '' }}">
                                                        @if($img)
                                                            <img src="{{ asset($img->path) }}"
                                                                 class="max-h-14 w-auto object-contain"
                                                                 alt="{{ $tr->rendering->glyph->barthel_code }}"
                                                                 loading="lazy">
                                                        @else
                                                            <span class="text-[11px] text-warm-gray font-medium tabular-nums">
                                                                {{ $tr->rendering->glyph->barthel_code }}
                                                            </span>
                                                        @endif
                                                    </a>
                                                    <span class="absolute bottom-0 left-0 right-0 text-center text-[7px] tabular-nums text-warm-gray leading-tight pb-px">
                                                        {{ $tr->rendering->code }}
                                                    </span>

                                                @elseif($isCompound)
                                                    <div class="flex items-center justify-center gap-px w-full h-full {{ $modClasses }}"
                                                         title="{{ $tr->compoundGlyph->code }}{{ $modSymbols ? ' ['.$modSymbols.']' : '' }}">
                                                        @foreach($tr->compoundGlyph->parts as $part)
                                                            @php $img = $part->glyph->images->first(); @endphp
                                                            <a href="{{ route('glyph', $part->glyph->barthel_code) }}"
                                                               class="inline-block"
                                                               title="{{ $part->glyph->barthel_code }}">
                                                                @if($img)
                                                                    <img src="{{ asset($img->path) }}"
                                                                         class="max-h-12 w-auto object-contain"
                                                                         alt="{{ $part->glyph->barthel_code }}"
                                                                         loading="lazy">
                                                                @else
                                                                    <span class="text-[9px] text-warm-gray">{{ $part->glyph->barthel_code }}</span>
                                                                @endif
                                                            </a>
                                                        @endforeach
                                                    </div>
                                                    <span class="absolute bottom-0 left-0 right-0 text-center text-[6px] tabular-nums text-deep-blue leading-tight pb-px">
                                                        {{ $tr->compoundGlyph->code }}
                                                    </span>
                                                @endif

                                                @if($modSymbols)
                                                    <span class="absolute -top-1.5 -right-1 text-[7px] text-soviet-red font-bold leading-none bg-cream px-0.5">
                                                        {{ $modSymbols }}
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                    @else
                                        @if($p <= $lineMaxPos)
                                            <td class="p-0 align-middle">
                                                <div class="min-w-[4.25rem] h-[4.5rem] border border-rule/30 bg-cream-dark/50"></div>
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
