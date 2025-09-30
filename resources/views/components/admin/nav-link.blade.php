@props(['href', 'icon' => 'dot', 'active' => false])

@php
    $glyphs = [
        'home' => '??',
        'chat-bubble-left-right' => '??',
        'receipt-percent' => '??',
        'users' => '??',
        'calendar' => '??',
        'banknotes' => '??',
        'dot' => '•',
    ];
    $glyph = $glyphs[$icon] ?? $glyphs['dot'];

    $classes = $active
        ? 'group flex items-center gap-3 rounded-lg bg-indigo-50 px-4 py-2 text-sm font-semibold text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-300'
        : 'group flex items-center gap-3 rounded-lg px-4 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-100 hover:text-indigo-600 dark:text-slate-300 dark:hover:bg-slate-800/80 dark:hover:text-indigo-300';
@endphp

<a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500/10 to-indigo-500/20 text-lg">{{ $glyph }}</span>
    <span class="flex-1">{{ $slot }}</span>
</a>
