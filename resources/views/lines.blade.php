@extends('layouts.public')

@section('title', __('front.lines.title') . ' — ' . __('front.site_title'))

@section('content')
    {{-- Definition block — Soviet primer rule style --}}
    <div class="mb-10 border-t-[3px] border-soviet-red pt-4 pb-5 border-b border-rule">
        <div class="flex items-start gap-4 sm:gap-6">
            <span class="text-[40px] sm:text-[52px] font-bold leading-none text-soviet-red select-none tracking-tight" aria-hidden="true">{{ mb_substr(__('front.lines.title'), 0, 1) }}</span>
            <div class="pt-0.5">
                <h1 class="text-[11px] font-medium tracking-[0.2em] uppercase mb-2">{{ __('front.lines.title') }}</h1>
                <p class="text-sm leading-relaxed text-ink/80 max-w-2xl">{{ __('front.lines.definition') }}</p>
            </div>
        </div>
    </div>

    {{-- Tablet filter --}}
    <div class="flex justify-end mb-8">
        <div class="relative">
            <select id="tablet-filter"
                    onchange="window.location = this.value ? '?tablet=' + this.value : '{{ route('lines') }}'"
                    class="bg-transparent border-b border-ink pb-1 text-sm focus:outline-none
                           focus:border-soviet-red transition-colors cursor-pointer pr-6
                           appearance-none">
                <option value="">{{ __('front.lines.all_tablets') }}</option>
                @foreach($tablets as $t)
                    <option value="{{ $t->code }}" {{ request('tablet') === $t->code ? 'selected' : '' }}>
                        {{ $t->code }} — {{ $t->name }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- Lines grouped by tablet --}}
    @foreach($lines as $tabletCode => $tabletLines)
        <section class="mb-10">
            <div class="flex items-center gap-4 mb-4">
                <a href="{{ route('tablet', $tabletCode) }}"
                   class="text-[11px] font-medium tracking-[0.15em] uppercase whitespace-nowrap hover:text-soviet-red transition-colors">
                    {{ $tabletLines->first()->tablet->name }}
                    <span class="text-soviet-red ml-1">{{ $tabletCode }}</span>
                </a>
                <div class="flex-1 border-t border-ink"></div>
            </div>

            {{-- Table header --}}
            <div class="hidden sm:flex items-center gap-3 py-1.5 text-[10px] font-medium tracking-[0.05em] uppercase text-warm-gray border-b border-ink mb-1">
                <div class="w-8 text-right flex-shrink-0">#</div>
                <div class="w-16 text-center flex-shrink-0">{{ __('front.lines.side') }}</div>
                <div class="w-6 text-center flex-shrink-0">{{ __('front.lines.dir') }}</div>
                <div class="flex-1"></div>
            </div>

            @foreach($tabletLines as $line)
                <div class="flex items-start gap-3 py-2 border-b border-rule hover:bg-cream-dark transition-colors">
                    {{-- Line number --}}
                    <div class="flex-shrink-0 w-8 text-right text-[11px] text-warm-gray font-medium tabular-nums pt-1.5">
                        {{ $line->line }}
                    </div>

                    {{-- Side --}}
                    <div class="flex-shrink-0 w-16 text-center text-[10px] text-warm-gray pt-1.5">
                        {{ $line->side === 0 ? __('front.lines.recto') : __('front.lines.verso') }}
                    </div>

                    {{-- Direction arrow --}}
                    <div class="flex-shrink-0 w-6 text-center text-[11px] text-warm-gray pt-1.5">
                        {!! $line->direction === 'ltr' ? '&rarr;' : '&larr;' !!}
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

                    {{-- Sign count --}}
                    <div class="flex-shrink-0 text-[10px] text-warm-gray tabular-nums pt-1.5 ml-auto">
                        {{ $line->tablet_renderings_count }}
                    </div>
                </div>
            @endforeach
        </section>
    @endforeach
@endsection
