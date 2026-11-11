@php
    $currentLocale = app()->getLocale();
    $locales = config('app.supported_locales', []);

    // Locale is chosen by subdomain (see SetLocale): ru.<host> → RU, <host> → EN
    $host = request()->getHost();
    $baseHost = str_starts_with($host, 'ru.') ? substr($host, 3) : $host;
    $port = request()->getPort();
    $portSuffix = in_array($port, [80, 443], true) ? '' : ':'.$port;
    $localeUrl = fn (string $code) => request()->getScheme().'://'
        .($code === 'ru' ? 'ru.'.$baseHost : $baseHost)
        .$portSuffix.request()->getRequestUri();
@endphp

<x-filament::dropdown placement="bottom-end">
    <x-slot name="trigger">
        <button type="button" class="fi-user-menu-trigger">
            {{ strtoupper($currentLocale) }} &#9662;
        </button>
    </x-slot>

    <x-filament::dropdown.list>
        @foreach ($locales as $code => $meta)
            <x-filament::dropdown.list.item
                tag="a"
                :href="$localeUrl($code)"
                :icon="$currentLocale === $code ? 'heroicon-m-check' : null"
                :color="$currentLocale === $code ? 'primary' : 'gray'"
            >
                {{ $meta['native'] }}
            </x-filament::dropdown.list.item>
        @endforeach
    </x-filament::dropdown.list>
</x-filament::dropdown>
