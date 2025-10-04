@php
    $user = auth()->user();
    $timezone = $user->timezone ?? config('app.timezone');
    $expiresLabel = $code?->expires_at
        ? $code->expires_at->clone()->timezone($timezone)->format('h:i A')
        : 'End of day';
@endphp

<div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-900">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Today's Attendance QR Code</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400">Generate a unique code students will scan to mark attendance.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <button wire:click="refreshCode" type="button" class="inline-flex items-center rounded-lg border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 shadow-sm transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-800">
                Refresh
            </button>
            <button wire:click="generate" type="button" class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                {{ $code ? 'Regenerate code' : "Generate today's code" }}
            </button>
        </div>
    </div>

    @if ($statusMessage)
        <div class="mt-4 rounded-lg border border-emerald-300 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-600 dark:bg-emerald-900/40 dark:text-emerald-200">
            {{ $statusMessage }}
        </div>
    @endif

    <div class="mt-6 grid gap-6 lg:grid-cols-[280px,1fr]">
        <div class="flex flex-col items-center justify-center rounded-xl border border-dashed border-slate-300 bg-slate-50 p-4 dark:border-slate-600 dark:bg-slate-800/40">
            @if ($this->svgDataUri)
                <img src="{{ $this->svgDataUri }}" alt="Attendance QR code" class="h-auto w-56 max-w-full">
            @else
                <p class="text-center text-sm text-slate-500 dark:text-slate-400">Generate the code to display the QR image.</p>
            @endif
        </div>
        <div class="space-y-4">
            <dl class="grid gap-4 sm:grid-cols-2">
                <div class="rounded-lg border border-slate-200 bg-white p-4 text-sm shadow-sm dark:border-slate-700 dark:bg-slate-900/50">
                    <dt class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">For date</dt>
                    <dd class="mt-1 font-semibold text-slate-900 dark:text-slate-100">{{ optional($code?->issued_for_date)->format('d M Y') ?? now()->format('d M Y') }}</dd>
                </div>
                <div class="rounded-lg border border-slate-200 bg-white p-4 text-sm shadow-sm dark:border-slate-700 dark:bg-slate-900/50">
                    <dt class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Expires</dt>
                    <dd class="mt-1 font-semibold text-slate-900 dark:text-slate-100">{{ $expiresLabel }}</dd>
                </div>
                <div class="rounded-lg border border-slate-200 bg-white p-4 text-sm shadow-sm dark:border-slate-700 dark:bg-slate-900/50">
                    <dt class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Issued by</dt>
                    <dd class="mt-1 font-semibold text-slate-900 dark:text-slate-100">{{ $code?->issuedBy?->name ?? $user->name }}</dd>
                </div>
                <div class="rounded-lg border border-slate-200 bg-white p-4 text-sm shadow-sm dark:border-slate-700 dark:bg-slate-900/50" x-data="{ copied: false }">
                    <dt class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Token</dt>
                    <dd class="mt-1 break-all font-semibold text-slate-900 dark:text-slate-100">{{ $code?->token ?? '-' }}</dd>
                    @if ($code)
                        <button type="button" class="mt-2 inline-flex items-center text-xs font-semibold text-indigo-600 hover:text-indigo-500" x-on:click="navigator.clipboard.writeText(@js($code->token)); copied = true; setTimeout(() => copied = false, 3000);">
                            <span x-show="!copied">Copy token</span>
                            <span x-show="copied">Copied!</span>
                        </button>
                    @endif
                </div>
            </dl>
            <p class="text-xs text-slate-500 dark:text-slate-400">
                Regenerating the QR code will immediately invalidate any previously shared versions for today. Ask students to rescan the updated code after regenerating.
            </p>
        </div>
    </div>
</div>

