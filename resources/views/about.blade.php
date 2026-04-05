@extends('layouts.public')

@section('title', __('front.about.title') . ' — ' . __('front.site_title'))

@section('content')
    {{-- Hero --}}
    <div class="mb-10 border-t-[3px] border-soviet-red pt-4 pb-5 border-b border-rule">
        <div class="flex items-start gap-4 sm:gap-6">
            <span class="text-[40px] sm:text-[52px] font-bold leading-none text-soviet-red select-none tracking-tight" aria-hidden="true">R</span>
            <div class="pt-0.5">
                <h1 class="text-[11px] font-medium tracking-[0.2em] uppercase mb-2">{{ __('front.about.title') }}</h1>
                <p class="text-sm leading-relaxed text-ink/80 max-w-2xl">{{ __('front.about.intro') }}</p>
            </div>
        </div>
    </div>

    {{-- Stats --}}
    <section class="mb-10">
        <div class="grid grid-cols-3 sm:grid-cols-6 gap-px bg-rule">
            @foreach([
                ['tablets', $stats['tablets']],
                ['lines', $stats['lines']],
                ['glyphs', $stats['glyphs']],
                ['renderings', $stats['renderings']],
                ['ligatures', $stats['ligatures']],
                ['occurrences', $stats['occurrences']],
            ] as [$key, $value])
                <div class="bg-cream py-4 px-3 text-center">
                    <span class="block text-2xl sm:text-3xl font-semibold tabular-nums text-soviet-red">{{ number_format($value) }}</span>
                    <span class="text-[10px] tracking-[0.12em] uppercase text-warm-gray font-medium">{{ $key }}</span>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Corpus --}}
    <section class="mb-10">
        <div class="flex items-center gap-4 mb-4">
            <h2 class="text-[11px] font-medium tracking-[0.15em] uppercase whitespace-nowrap">
                {{ __('front.about.corpus_title') }}
            </h2>
            <div class="flex-1 border-t border-ink"></div>
        </div>
        <p class="text-sm leading-relaxed text-ink/80 max-w-2xl">{{ __('front.about.corpus_text') }}</p>
    </section>

    {{-- Notation --}}
    <section class="mb-10">
        <div class="flex items-center gap-4 mb-4">
            <h2 class="text-[11px] font-medium tracking-[0.15em] uppercase whitespace-nowrap">
                {{ __('front.about.notation_title') }}
            </h2>
            <div class="flex-1 border-t border-ink"></div>
        </div>
        <div class="space-y-4 text-sm leading-relaxed text-ink/80 max-w-2xl">
            <p>{{ __('front.about.notation_glyphs') }}</p>
            <p>{{ __('front.about.notation_renderings') }}</p>
            <div>
                <p class="mb-2">{{ __('front.about.notation_modifiers') }}</p>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-x-6 gap-y-1 text-[12px] ml-4">
                    @foreach(__('front.renderings.modifiers') as $key => $label)
                        <div class="flex items-baseline gap-2">
                            <span class="text-soviet-red font-semibold tabular-nums w-4 text-center">{{ __("front.renderings.modifier_symbols.$key") }}</span>
                            <span class="text-warm-gray">{{ $label }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
            <p>{{ __('front.about.notation_ligatures') }}</p>
            <p>{{ __('front.about.notation_boustrophedon') }}</p>
        </div>
    </section>

    {{-- Sources --}}
    <section class="mb-10">
        <div class="flex items-center gap-4 mb-4">
            <h2 class="text-[11px] font-medium tracking-[0.15em] uppercase whitespace-nowrap">
                {{ __('front.about.sources_title') }}
            </h2>
            <div class="flex-1 border-t border-ink"></div>
        </div>
        <div class="space-y-3 max-w-2xl">
            @foreach(['source_spaelti', 'source_barthel', 'source_fischer', 'source_wikimedia', 'source_rongopy'] as $src)
                <div class="flex items-start gap-3 text-sm leading-relaxed">
                    <span class="text-soviet-red font-bold mt-0.5 select-none shrink-0">&mdash;</span>
                    <p class="text-ink/80">{{ __("front.about.$src") }}</p>
                </div>
            @endforeach
            <p class="text-[12px] text-warm-gray leading-relaxed mt-4 border-l-2 border-soviet-red/30 pl-4">
                {{ __('front.about.fair_use') }}
            </p>
        </div>
    </section>

    {{-- Tech --}}
    <section class="mb-10">
        <div class="flex items-center gap-4 mb-4">
            <h2 class="text-[11px] font-medium tracking-[0.15em] uppercase whitespace-nowrap">
                {{ __('front.about.tech_title') }}
            </h2>
            <div class="flex-1 border-t border-ink"></div>
        </div>
        <p class="text-sm leading-relaxed text-ink/80 max-w-2xl">{{ __('front.about.tech_text') }}</p>
    </section>

    {{-- Contact --}}
    <section class="mb-10">
        <div class="flex items-center gap-4 mb-4">
            <h2 class="text-[11px] font-medium tracking-[0.15em] uppercase whitespace-nowrap">
                {{ __('front.about.contact_title') }}
            </h2>
            <div class="flex-1 border-t border-ink"></div>
        </div>
        <p class="text-sm leading-relaxed text-ink/80">
            {{ __('front.about.contact_text') }}
            <a href="mailto:d.shaludnyov@gmail.com?subject=RONGO"
               class="text-ink hover:text-soviet-red transition-colors font-medium">d.shaludnyov@gmail.com</a>
        </p>
    </section>
@endsection
