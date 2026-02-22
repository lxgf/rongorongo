<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', __('front.site_title'))</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Jost:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
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
                <a href="{{ route('tablets') }}"
                   class="text-[11px] tracking-[0.15em] uppercase font-medium transition-colors
                          {{ request()->routeIs('tablets') || request()->routeIs('tablet') ? 'text-soviet-red' : 'text-ink hover:text-soviet-red' }}">
                    {{ __('front.nav.tablets') }}
                </a>
                <a href="{{ route('lines') }}"
                   class="text-[11px] tracking-[0.15em] uppercase font-medium transition-colors
                          {{ request()->routeIs('lines') ? 'text-soviet-red' : 'text-ink hover:text-soviet-red' }}">
                    {{ __('front.nav.lines') }}
                </a>
            </nav>
        </header>

        {{-- Content --}}
        <main class="py-8">
            @yield('content')
        </main>

        {{-- Footer --}}
        <footer class="py-6 border-t border-rule">
            @hasSection('footer')
                @yield('footer')
            @else
                <p class="text-[11px] text-warm-gray tracking-wider">
                    {{ __('front.site_title') }} &middot; {{ date('Y') }}
                </p>
            @endif
        </footer>

    </div>
</body>
</html>
