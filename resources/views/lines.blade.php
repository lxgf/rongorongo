@extends('layouts.public')

@section('title', __('front.lines.title') . ($selectedTablet ? ' — ' . $selectedTablet->code : '') . ' — ' . __('front.site_title'))

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

    {{-- Tablet selector --}}
    <div class="flex items-center gap-px mb-8 flex-wrap">
        @foreach($tablets as $t)
            @php $isCurrent = $selectedTablet && $selectedTablet->code === $t->code; @endphp
            <a href="{{ route('lines', ['tablet' => $t->code]) }}"
               class="w-10 h-10 flex items-center justify-center border transition-colors
                      {{ $isCurrent
                          ? 'bg-soviet-red text-white border-soviet-red font-semibold'
                          : 'bg-white text-ink border-rule hover:border-soviet-red hover:text-soviet-red' }}">
                <span class="text-[13px] tabular-nums leading-none">{{ $t->code }}</span>
            </a>
        @endforeach
    </div>

    @if($selectedTablet)
        {{-- Tablet header --}}
        <div class="flex items-baseline gap-3 mb-6">
            <span class="text-2xl font-semibold text-soviet-red">{{ $selectedTablet->code }}</span>
            <span class="text-lg font-semibold">{{ $selectedTablet->name }}</span>
            @if($selectedTablet->location)
                <span class="text-sm text-warm-gray hidden sm:inline">&middot; {{ $selectedTablet->location }}</span>
            @endif
        </div>

        {{-- Sides + lines --}}
        @foreach($sides as $sideNum => $sideLines)
            <section class="mb-8">
                <div class="flex items-center gap-4 mb-3">
                    <h2 class="text-[11px] font-medium tracking-[0.15em] uppercase whitespace-nowrap">
                        {{ $sideLines->first()->sideLabel }}
                    </h2>
                    <span class="text-[10px] text-warm-gray tabular-nums">{{ $sideLines->count() }} {{ __('front.lines.line') }}</span>
                    <div class="flex-1 border-t border-ink"></div>
                </div>

                <div class="grid grid-cols-4 sm:grid-cols-6 md:grid-cols-8 lg:grid-cols-10 gap-2">
                    @foreach($sideLines as $line)
                        @php
                            $sideChar = chr(ord('a') + $line->side);
                        @endphp
                        <a href="{{ route('line', [$selectedTablet->code, $sideChar, $line->line]) }}"
                           class="group border border-rule hover:border-soviet-red transition-colors bg-white">
                            <div class="py-3 px-2 text-center">
                                <span class="block text-lg font-semibold tabular-nums group-hover:text-soviet-red transition-colors">{{ $line->line }}</span>
                                <span class="block text-[9px] text-warm-gray tabular-nums mt-0.5">{{ $line->tablet_renderings_count }} {{ __('front.lines.signs') }}</span>
                            </div>
                            <div class="border-t border-rule px-2 py-1 text-center">
                                <span class="text-[9px] text-warm-gray">
                                    {!! $line->direction === 'ltr' ? '&rarr;' : '&larr;' !!}
                                </span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
        @endforeach
    @else
        {{-- No tablet selected — prompt --}}
        <p class="text-sm text-warm-gray">{{ __('front.lines.select_tablet') }}</p>
    @endif
@endsection
