@extends('layouts.public')

@section('title', __('front.ligatures.title') . ' — ' . __('front.site_title'))

@section('content')
    {{-- Definition block — Soviet primer rule style --}}
    <div class="mb-10 border-t-[3px] border-soviet-red pt-4 pb-5 border-b border-rule">
        <div class="flex items-start gap-4 sm:gap-6">
            <span class="text-[40px] sm:text-[52px] font-bold leading-none text-soviet-red select-none tracking-tight" aria-hidden="true">{{ mb_substr(__('front.ligatures.title'), 0, 1) }}</span>
            <div class="pt-0.5">
                <h1 class="text-[11px] font-medium tracking-[0.2em] uppercase mb-2">{{ __('front.ligatures.title') }}</h1>
                <p class="text-sm leading-relaxed text-ink/80 max-w-2xl">{{ __('front.ligatures.definition') }}</p>
            </div>
        </div>
    </div>

    {{-- Search --}}
    <div class="flex justify-end mb-8">
        <div class="relative w-full sm:w-64">
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
                    {{-- Code --}}
                    <span class="text-lg sm:text-xl font-semibold text-soviet-red tabular-nums min-w-[4rem] flex-shrink-0">
                        {{ $ligature->code }}
                    </span>

                    {{-- Component glyphs --}}
                    <div class="flex items-center gap-1 flex-shrink-0">
                        @foreach($ligature->parts as $part)
                            @php $img = $part->glyph->images->first(); @endphp
                            <a href="{{ route('glyph', $part->glyph->barthel_code) }}"
                               class="inline-block hover:outline hover:outline-1 hover:outline-soviet-red transition-all"
                               title="{{ $part->glyph->barthel_code }}">
                                @if($img)
                                    <img src="{{ asset($img->path) }}"
                                         class="h-9 w-auto object-contain"
                                         alt="{{ $part->glyph->barthel_code }}"
                                         loading="lazy">
                                @else
                                    <span class="inline-flex items-center justify-center h-9 px-2 border border-rule text-xs text-warm-gray font-medium tabular-nums">
                                        {{ $part->glyph->barthel_code }}
                                    </span>
                                @endif
                            </a>
                            @if(!$loop->last)
                                <span class="text-warm-gray text-xs">+</span>
                            @endif
                        @endforeach
                    </div>

                    {{-- Spacer --}}
                    <div class="flex-1 border-t border-dotted border-warm-gray self-center hidden sm:block"></div>

                    {{-- Component codes --}}
                    <span class="hidden sm:block text-sm text-warm-gray tabular-nums flex-shrink-0">
                        {{ $ligature->parts->pluck('glyph.barthel_code')->join(' + ') }}
                    </span>

                    {{-- Occurrence count --}}
                    <span class="text-sm text-warm-gray tabular-nums flex-shrink-0">
                        &times;{{ $ligature->tablet_renderings_count }}
                    </span>
                </div>

                @if($ligature->description)
                    <p class="mt-1 text-sm text-warm-gray/70 ml-[4rem] sm:ml-[4rem] line-clamp-1">
                        {{ $ligature->description }}
                    </p>
                @endif
            </div>
        @endforeach
    </div>

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
        {{ $ligatures->count() }} {{ __('front.ligatures.title') }}
    </p>
@endsection
