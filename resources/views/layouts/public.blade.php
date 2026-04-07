<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <title>@yield('title', __('front.site_title'))</title>
    @php $defaultDesc = app()->getLocale() === 'ru'
        ? 'Ронгоронго — исследовательская платформа нерасшифрованной письменности Рапа-Нуи (острова Пасхи). Каталог глифов, SVG-начертания, таблички и анализ корпуса.'
        : 'Rongorongo — open-source research platform for the undeciphered writing system of Rapa Nui (Easter Island). Glyph catalog, SVG renderings, tablets, and corpus analysis.'; @endphp
    <meta name="description" content="@yield('meta_description', $defaultDesc)">
    <link rel="canonical" href="@yield('canonical', url()->current())">
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:title" content="@yield('og_title', $__env->yieldContent('title', __('front.site_title')))">
    <meta property="og:description" content="@yield('og_description', $__env->yieldContent('meta_description', $defaultDesc))">
    <meta property="og:url" content="@yield('canonical', url()->current())">
    @if(trim($__env->yieldContent('og_image')))
    <meta property="og:image" content="@yield('og_image')">
    @endif
    <link rel="alternate" hreflang="en" href="https://rongorongo.top{{ request()->getPathInfo() }}">
    <link rel="alternate" hreflang="ru" href="https://ru.rongorongo.top{{ request()->getPathInfo() }}">
    <link rel="alternate" hreflang="x-default" href="https://rongorongo.top{{ request()->getPathInfo() }}">
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
                    @php
                        $currentPath = request()->getPathInfo();
                        $enUrl = 'https://rongorongo.top' . $currentPath;
                        $ruUrl = 'https://ru.rongorongo.top' . $currentPath;
                    @endphp
                    <a href="{{ $enUrl }}"
                       class="{{ app()->getLocale() === 'en' ? 'font-semibold text-ink' : 'text-warm-gray hover:text-ink' }} transition-colors">EN</a>
                    <span class="text-rule">|</span>
                    <a href="{{ $ruUrl }}"
                       class="{{ app()->getLocale() === 'ru' ? 'font-semibold text-ink' : 'text-warm-gray hover:text-ink' }} transition-colors">RU</a>
                </div>
            </div>
            <div class="border-t border-ink mt-3 mb-3"></div>
            <nav class="flex gap-4 sm:gap-8 overflow-x-auto -mx-4 px-4 sm:mx-0 sm:px-0 scrollbar-none">
                @php
                    $navItems = [
                        ['route' => 'alphabet', 'label' => __('front.nav.alphabet'), 'active' => request()->routeIs('alphabet') || request()->routeIs('glyph')],
                        ['route' => 'ligatures', 'label' => __('front.nav.ligatures'), 'active' => request()->routeIs('ligatures')],
                        ['route' => 'renderings', 'label' => __('front.nav.renderings'), 'active' => request()->routeIs('renderings') || request()->routeIs('rendering')],
                        ['route' => 'lines', 'label' => __('front.nav.lines'), 'active' => request()->routeIs('lines') || request()->routeIs('line')],
                        ['route' => 'tablets', 'label' => __('front.nav.tablets'), 'active' => request()->routeIs('tablets') || request()->routeIs('tablet')],
                        ['route' => 'about', 'label' => __('front.about.title'), 'active' => request()->routeIs('about')],
                    ];
                @endphp
                @foreach($navItems as $item)
                    <a href="{{ route($item['route']) }}"
                       class="text-[11px] tracking-[0.15em] uppercase font-medium transition-colors whitespace-nowrap shrink-0
                              {{ $item['active'] ? 'text-soviet-red' : 'text-ink hover:text-soviet-red' }}">
                        {{ $item['label'] }}
                    </a>
                @endforeach
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
