@php
    $currentLocale = app()->getLocale();
    $locales = config('app.supported_locales', []);
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
                :href="route('locale.switch', $code)"
                :icon="$currentLocale === $code ? 'heroicon-m-check' : null"
                :color="$currentLocale === $code ? 'primary' : 'gray'"
            >
                {{ $meta['native'] }}
            </x-filament::dropdown.list.item>
        @endforeach
    </x-filament::dropdown.list>
</x-filament::dropdown>
