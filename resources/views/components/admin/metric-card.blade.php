@props(['title', 'value', 'subtitle', 'icon'])

<div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
    <div class="flex items-center gap-x-4">
        {{-- Icon --}}
        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-indigo-100 dark:bg-indigo-500/20">
            @svg($icon, 'h-6 w-6 text-indigo-600 dark:text-indigo-300')
        </div>

        {{-- Content --}}
        <div>
            <p class="text-sm text-slate-500 dark:text-slate-400">{{ $title }}</p>
            <p class="text-2xl font-semibold text-slate-800 dark:text-slate-100">{{ $value }}</p>
        </div>
    </div>
    <p class="mt-3 truncate text-xs text-slate-400 dark:text-slate-500">{{ $subtitle }}</p>
</div>