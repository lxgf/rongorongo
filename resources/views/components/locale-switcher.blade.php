@php
    $currentLocale = app()->getLocale();
    $otherLocale = $currentLocale === 'ru' ? 'en' : 'ru';
    $flags = ['en' => '🇬🇧', 'ru' => '🇷🇺'];
@endphp

<a href="{{ route('locale.switch', $otherLocale) }}"
   class="flex items-center justify-center rounded-lg p-2 text-sm font-medium outline-none transition duration-75
          text-gray-400 hover:text-gray-500 hover:bg-gray-50
          dark:text-gray-500 dark:hover:text-gray-400 dark:hover:bg-white/5
          focus-visible:bg-gray-50 dark:focus-visible:bg-white/5"
   title="{{ $currentLocale === 'ru' ? 'Switch to English' : 'Переключить на русский' }}"
>
    <span class="text-base leading-none">{{ $flags[$otherLocale] }}</span>
</a>
