@props(['name' => 'circle', 'class' => 'h-5 w-5'])

@php
    $icons = [
        'home' => 'M2.25 12l8.954-8.955a.75.75 0 0 1 1.061 0L21.219 12M4.5 9.75V19.5a1.5 1.5 0 0 0 1.5 1.5h4.5v-6h3v6h4.5a1.5 1.5 0 0 0 1.5-1.5V9.75M8.25 21h8.25',
        'chat-bubble-left-right' => 'M7.5 6h9a2.25 2.25 0 0 1 2.25 2.25v8.25a2.25 2.25 0 0 1-2.25 2.25H12l-4.5 3v-3H7.5A2.25 2.25 0 0 1 5.25 16.5V8.25A2.25 2.25 0 0 1 7.5 6z M15 3H6a2.25 2.25 0 0 0-2.25 2.25V13.5',
        'receipt-percent' => 'M9 9l6 6m0-6l-6 6M7.5 3.75h9A1.5 1.5 0 0 1 18 5.25v13.5l-3-1.5-3 1.5-3-1.5-3 1.5V5.25A1.5 1.5 0 0 1 7.5 3.75z',
        'users' => 'M4.5 16.5A3 3 0 0 1 7.5 13.5h9a3 3 0 0 1 3 3v2.25H4.5V16.5zM8.25 7.5a3 3 0 1 0 6 0 3 3 0 0 0-6 0z',
        'calendar' => 'M6.75 3v1.5m10.5-1.5V4.5M4.5 7.5h15M6 9.75h12v8.25a1.5 1.5 0 0 1-1.5 1.5H7.5A1.5 1.5 0 0 1 6 18V9.75z',
        'banknotes' => 'M3 6.75A1.5 1.5 0 0 1 4.5 5.25h12a1.5 1.5 0 0 1 1.5 1.5V15a1.5 1.5 0 0 1-1.5 1.5h-12A1.5 1.5 0 0 1 3 15V6.75zm1.5 6.75h12M6 9.75a1.5 1.5 0 1 0 0 3m9-3a1.5 1.5 0 1 1 0 3',
        'bars-3' => 'M3.75 5.25h16.5M3.75 12h16.5M3.75 18.75h16.5',
        'circle' => 'M12 21.75a9.75 9.75 0 1 1 0-19.5 9.75 9.75 0 0 1 0 19.5z',
    ];

    $path = $icons[$name] ?? $icons['circle'];
@endphp

<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" {{ $attributes->merge(['class' => $class]) }}>
    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $path }}" />
</svg>
