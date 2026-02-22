@extends('layouts.public')

@section('title', __('front.renderings.title') . ' — ' . __('front.site_title'))

@section('content')
    {{-- Definition block --}}
    <div class="mb-10 border-t-[3px] border-soviet-red pt-4 pb-5 border-b border-rule">
        <div class="flex items-start gap-4 sm:gap-6">
            <span class="text-[40px] sm:text-[52px] font-bold leading-none text-soviet-red select-none tracking-tight" aria-hidden="true">{{ mb_substr(__('front.renderings.title'), 0, 1) }}</span>
            <div class="pt-0.5">
                <h1 class="text-[11px] font-medium tracking-[0.2em] uppercase mb-2">{{ __('front.renderings.title') }}</h1>
                <p class="text-sm leading-relaxed text-ink/80 max-w-2xl">{{ __('front.renderings.definition') }}</p>
            </div>
        </div>
    </div>

    {{-- Notation block --}}
    <div class="mb-10">
        <div class="flex items-center gap-4 mb-4">
            <h2 class="text-[11px] font-medium tracking-[0.15em] uppercase whitespace-nowrap">
                {{ __('front.renderings.notation_title') }}
            </h2>
            <div class="flex-1 border-t border-rule"></div>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 sm:gap-8 mb-6">
            <div class="flex items-start gap-4">
                <span class="text-lg font-semibold leading-none text-soviet-red select-none tabular-nums mt-0.5">002a</span>
                <div class="border-l border-rule pl-4">
                    <p class="text-[10px] font-medium tracking-[0.15em] uppercase mb-1">{{ __('front.renderings.notation_variants_label') }}</p>
                    <p class="text-sm text-ink/70 leading-relaxed">{{ __('front.renderings.notation_variants') }}</p>
                </div>
            </div>
            <div class="flex items-start gap-4">
                <span class="text-lg font-semibold leading-none text-soviet-red select-none mt-0.5">f b s</span>
                <div class="border-l border-rule pl-4">
                    <p class="text-[10px] font-medium tracking-[0.15em] uppercase mb-1">{{ __('front.renderings.notation_modifiers_label') }}</p>
                    <p class="text-sm text-ink/70 leading-relaxed">{{ __('front.renderings.notation_modifiers') }}</p>
                </div>
            </div>
        </div>

        {{-- Modifiers table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <tbody>
                    @foreach(__('front.renderings.modifiers') as $key => $label)
                        <tr class="border-b border-rule">
                            <td class="py-1.5 pr-4 w-10 text-center text-soviet-red font-semibold tabular-nums">
                                {{ __('front.renderings.modifier_symbols.' . $key) }}
                            </td>
                            <td class="py-1.5 pr-4 text-warm-gray text-xs font-mono">
                                {{ $key }}
                            </td>
                            <td class="py-1.5">
                                {{ $label }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Search --}}
    <div class="flex justify-end mb-8">
        <div class="relative w-full sm:w-64">
            <input type="text"
                   id="rendering-search"
                   placeholder="{{ __('front.renderings.search') }}"
                   class="w-full bg-transparent border-b border-ink pb-1 text-sm
                          placeholder:text-warm-gray focus:outline-none focus:border-soviet-red
                          transition-colors tabular-nums"
                   autocomplete="off">
        </div>
    </div>

    {{-- Glyph groups --}}
    @foreach($groups as $base => $groupGlyphs)
        @php
            $start = $base === 0 ? '001' : str_pad($base, 3, '0', STR_PAD_LEFT);
            $end = str_pad($base + 99, 3, '0', STR_PAD_LEFT);
        @endphp
        <section class="mb-10 rendering-group">
            <div class="flex items-center gap-4 mb-4">
                <h2 class="text-[11px] font-medium tracking-[0.15em] uppercase text-warm-gray whitespace-nowrap">
                    {{ $start }}&mdash;{{ $end }}
                </h2>
                <div class="flex-1 border-t border-rule"></div>
            </div>

            <div class="border-t border-ink">
                @foreach($groupGlyphs as $glyph)
                    @php $img = $glyph->images->first(); @endphp
                    <a href="{{ route('rendering', $glyph->barthel_code) }}"
                       class="glyph-rendering-row group block py-3 border-b border-rule hover:bg-cream-dark transition-colors -mx-4 px-4"
                       data-code="{{ $glyph->barthel_code }}"
                       data-renderings="{{ $glyph->renderings->pluck('code')->join(' ') }}">
                        <div class="flex items-center gap-4">
                            {{-- Glyph image + code --}}
                            <div class="flex items-center gap-3 flex-shrink-0">
                                <div class="size-10 border border-rule group-hover:border-soviet-red bg-white flex items-center justify-center overflow-hidden transition-colors flex-shrink-0">
                                    @if($img)
                                        <img src="{{ asset($img->path) }}"
                                             alt="{{ $glyph->barthel_code }}"
                                             class="max-w-full max-h-full object-contain p-1 group-hover:scale-110 transition-transform duration-200"
                                             loading="lazy">
                                    @else
                                        <span class="text-xs font-light text-warm-gray">{{ $glyph->barthel_code }}</span>
                                    @endif
                                </div>
                                <span class="text-base font-semibold tabular-nums group-hover:text-soviet-red transition-colors">{{ $glyph->barthel_code }}</span>
                            </div>

                            {{-- Rendering codes --}}
                            <div class="flex flex-wrap gap-1.5 flex-1 min-w-0">
                                @foreach($glyph->renderings as $rendering)
                                    <span class="inline-flex items-baseline gap-1 px-2 py-0.5 border border-rule text-[11px] tabular-nums font-medium bg-white">
                                        {{ $rendering->code }}
                                        @if($rendering->tablet_renderings_count > 0)
                                            <span class="text-[9px] text-warm-gray">&times;{{ $rendering->tablet_renderings_count }}</span>
                                        @endif
                                    </span>
                                @endforeach
                            </div>

                            {{-- Total renderings count --}}
                            <span class="text-xs text-warm-gray tabular-nums flex-shrink-0 hidden sm:block">
                                {{ $glyph->renderings->count() }} {{ __('front.glyph.count_renderings') }}
                            </span>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @endforeach

    <script>
        document.getElementById('rendering-search').addEventListener('input', function () {
            const q = this.value.trim().toLowerCase();
            document.querySelectorAll('.glyph-rendering-row').forEach(function (el) {
                if (!q) { el.style.display = ''; return; }
                var match = el.dataset.code.includes(q) || el.dataset.renderings.toLowerCase().includes(q);
                el.style.display = match ? '' : 'none';
            });
            document.querySelectorAll('.rendering-group').forEach(function (sec) {
                var visible = sec.querySelectorAll('.glyph-rendering-row:not([style*="display: none"])');
                sec.style.display = !q || visible.length > 0 ? '' : 'none';
            });
        });
    </script>
@endsection

@section('footer')
    <p class="text-[11px] text-warm-gray tracking-wider tabular-nums">
        {{ $totalRenderings }} {{ __('front.renderings.title') }}
    </p>
@endsection
