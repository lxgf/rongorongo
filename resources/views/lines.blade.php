@extends('layouts.public')

@section('title', __('front.lines.title') . ' — ' . __('front.site_title'))

@section('content')
    {{-- Definition block --}}
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
                <div class="w-10 text-center flex-shrink-0">{{ __('front.lines.dir') }}</div>
                <div class="flex-1">{{ __('front.lines.signs') }}</div>
                <div class="w-16"></div>
            </div>

            @foreach($tabletLines as $line)
                <div class="flex items-center gap-3 py-2 border-b border-rule hover:bg-cream-dark transition-colors">
                    {{-- Line number --}}
                    <div class="flex-shrink-0 w-8 text-right text-[11px] text-warm-gray font-medium tabular-nums">
                        {{ $line->line }}
                    </div>

                    {{-- Side --}}
                    <div class="flex-shrink-0 w-16 text-center text-[10px] text-warm-gray">
                        {{ $line->side === 0 ? __('front.lines.recto') : __('front.lines.verso') }}
                    </div>

                    {{-- Direction arrow --}}
                    <div class="flex-shrink-0 w-10 text-center text-[11px] text-warm-gray">
                        {!! $line->direction === 'ltr' ? '&rarr;' : '&larr;' !!}
                    </div>

                    {{-- Sign count --}}
                    <div class="flex-1 text-[11px] tabular-nums text-ink">
                        {{ $line->tablet_renderings_count }}
                    </div>

                    {{-- View link --}}
                    <div class="flex-shrink-0 text-right pr-1">
                        <a href="{{ route('line', [$tabletCode, $line->side === 0 ? 'r' : 'v', $line->line]) }}"
                           class="text-[10px] tracking-[0.05em] uppercase font-medium text-deep-blue hover:text-soviet-red transition-colors">
                            {{ __('front.lines.view_line') }} &rarr;
                        </a>
                    </div>
                </div>
            @endforeach
        </section>
    @endforeach
@endsection
