<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', __('front.site_title'))</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Jost:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-cream text-ink font-sans antialiased">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Header --}}
        <header class="pt-8 pb-2">
            <div class="flex items-baseline justify-between">
                <a href="{{ route('alphabet') }}" class="text-base sm:text-lg tracking-[0.3em] font-light uppercase hover:text-soviet-red transition-colors">
                    {{ __('front.site_title') }}
                </a>
                <div class="flex gap-2 text-xs tracking-wider">
                    @foreach (config('app.supported_locales') as $code => $meta)
                        @unless ($loop->first)
                            <span class="text-rule">|</span>
                        @endunless
                        <a href="{{ route('locale.switch', $code) }}"
                           class="{{ app()->getLocale() === $code ? 'font-semibold text-ink' : 'text-warm-gray hover:text-ink' }} transition-colors">{{ strtoupper($code) }}</a>
                    @endforeach
                </div>
            </div>
            <div class="border-t border-ink mt-3 mb-3"></div>
            <nav class="flex gap-8">
                <a href="{{ route('alphabet') }}"
                   class="text-[11px] tracking-[0.15em] uppercase font-medium transition-colors
                          {{ request()->routeIs('alphabet') || request()->routeIs('glyph') ? 'text-soviet-red' : 'text-ink hover:text-soviet-red' }}">
                    {{ __('front.nav.alphabet') }}
                </a>
                <a href="{{ route('ligatures') }}"
                   class="text-[11px] tracking-[0.15em] uppercase font-medium transition-colors
                          {{ request()->routeIs('ligatures') ? 'text-soviet-red' : 'text-ink hover:text-soviet-red' }}">
                    {{ __('front.nav.ligatures') }}
                </a>
                <a href="{{ route('renderings') }}"
                   class="text-[11px] tracking-[0.15em] uppercase font-medium transition-colors
                          {{ request()->routeIs('renderings') || request()->routeIs('rendering') ? 'text-soviet-red' : 'text-ink hover:text-soviet-red' }}">
                    {{ __('front.nav.renderings') }}
                </a>
                <a href="{{ route('lines') }}"
                   class="text-[11px] tracking-[0.15em] uppercase font-medium transition-colors
                          {{ request()->routeIs('lines') || request()->routeIs('line') ? 'text-soviet-red' : 'text-ink hover:text-soviet-red' }}">
                    {{ __('front.nav.lines') }}
                </a>
                <a href="{{ route('tablets') }}"
                   class="text-[11px] tracking-[0.15em] uppercase font-medium transition-colors
                          {{ request()->routeIs('tablets') || request()->routeIs('tablet') ? 'text-soviet-red' : 'text-ink hover:text-soviet-red' }}">
                    {{ __('front.nav.tablets') }}
                </a>
                <a href="{{ route('about') }}"
                   class="text-[11px] tracking-[0.15em] uppercase font-medium transition-colors
                          {{ request()->routeIs('about') ? 'text-soviet-red' : 'text-ink hover:text-soviet-red' }}">
                    {{ __('front.about.title') }}
                </a>
            </nav>
        </header>

        {{-- Content --}}
        <main class="py-8">
            @yield('content')
        </main>

        {{-- Footer --}}
        <footer class="pt-8 pb-6">
            {{-- Colophon --}}
            <div class="border-t-[2px] border-soviet-red pt-4 mb-6">
                <p class="text-[11px] leading-relaxed text-warm-gray max-w-xl">
                    {{ __('front.colophon.disclaimer') }}
                </p>
                <p class="text-[11px] leading-relaxed text-warm-gray mt-2">
                    {{ __('front.colophon.feedback') }}
                    <a href="mailto:d.shaludnyov@gmail.com?subject=RONGO"
                       class="text-ink hover:text-soviet-red transition-colors font-medium">d.shaludnyov@gmail.com</a>.
                    {{ __('front.colophon.subject') }}
                </p>
                <p class="text-[11px] leading-relaxed text-warm-gray/70 mt-2 max-w-xl">
                    {{ __('front.colophon.attribution') }}
                </p>
            </div>

            {{-- Page-specific footer + copyright --}}
            <div class="border-t border-rule pt-4 flex items-baseline justify-between">
                <div>
                    @yield('footer')
                </div>
                <p class="text-[11px] text-warm-gray tracking-wider">
                    {{ __('front.site_title') }} &middot; {{ date('Y') }}
                </p>
            </div>
        </footer>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/fslightbox@3.4.1/index.min.js" defer></script>
</body>
</html>
