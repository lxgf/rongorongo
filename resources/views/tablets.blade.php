@extends('layouts.public')

@section('title', __('front.tablets.title') . ' — ' . __('front.site_title'))

@section('content')
    {{-- Definition block — Soviet primer rule style --}}
    <div class="mb-10 border-t-[3px] border-soviet-red pt-4 pb-5 border-b border-rule">
        <div class="flex items-start gap-4 sm:gap-6">
            <span class="text-[40px] sm:text-[52px] font-bold leading-none text-soviet-red select-none tracking-tight" aria-hidden="true">{{ mb_substr(__('front.tablets.title'), 0, 1) }}</span>
            <div class="pt-0.5">
                <h1 class="text-[11px] font-medium tracking-[0.2em] uppercase mb-2">{{ __('front.tablets.title') }}</h1>
                <p class="text-sm leading-relaxed text-ink/80 max-w-2xl">{{ __('front.tablets.definition') }}</p>
            </div>
        </div>
    </div>

    <div class="border-t border-ink">
        @foreach($tablets as $tablet)
            <a href="{{ route('tablet', $tablet->code) }}"
               class="group block py-5 border-b border-rule hover:bg-cream-dark transition-colors -mx-4 px-4">
                <div class="flex items-baseline gap-4">
                    <span class="text-2xl sm:text-3xl font-semibold text-soviet-red tabular-nums min-w-[2.5rem]">
                        {{ $tablet->code }}
                    </span>
                    <div class="flex-1 border-t border-dotted border-warm-gray self-center hidden sm:block"></div>
                    <span class="text-right">
                        <span class="block text-base sm:text-lg font-medium group-hover:text-soviet-red transition-colors">{{ $tablet->name }}</span>
                        @if(app()->getLocale() === 'ru' && $tablet->name_ru)
                            <span class="block text-[11px] text-warm-gray italic leading-tight">{{ $tablet->name_ru }}</span>
                        @endif
                    </span>
                </div>
                <div class="flex flex-wrap gap-x-3 gap-y-0.5 mt-1.5 text-sm text-warm-gray ml-0 sm:ml-[3.5rem]">
                    @if($tablet->location)
                        <span>{{ $tablet->location }}</span>
                        <span class="hidden sm:inline">&middot;</span>
                    @endif
                    <span class="tabular-nums">{{ $tablet->lines_count }} {{ __('front.tablets.lines') }}</span>
                    <span>&middot;</span>
                    <span class="tabular-nums">{{ $renderingCounts[$tablet->id] ?? 0 }} {{ __('front.tablets.signs') }}</span>
                </div>
                @if($tablet->description)
                    <p class="mt-1 text-sm text-warm-gray/70 ml-0 sm:ml-[3.5rem] line-clamp-1">
                        {{ $tablet->description }}
                    </p>
                @endif
            </a>
        @endforeach
    </div>
@endsection
