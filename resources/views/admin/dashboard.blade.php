@extends('layouts.admin')

@section('title', 'Admin Dashboard | ' . config('app.name'))
@section('header', 'Dashboard Overview')

@section('content')
    {{-- Main 2-column layout --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

        {{-- Main Content Column --}}
        <div class="space-y-6 lg:col-span-2">

            {{-- Finance Panel (now contains the metric cards) --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">

                {{-- KPI cards are now inside the main panel --}}
                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    {{-- We are using a simpler metric card here for a cleaner look inside the panel --}}
                    <div class="rounded-xl border border-slate-100 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-800/60">
                        <div class="flex items-center gap-x-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-indigo-100 dark:bg-indigo-500/20">
                                @svg('heroicon-o-academic-cap', 'h-5 w-5 text-indigo-600 dark:text-indigo-300')
                            </div>
                            <div>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Total Students</p>
                                <p class="text-xl font-semibold text-slate-800 dark:text-slate-100">{{ $studentCount }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="rounded-xl border border-slate-100 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-800/60">
                         <div class="flex items-center gap-x-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-indigo-100 dark:bg-indigo-500/20">
                                @svg('heroicon-o-chart-bar-square', 'h-5 w-5 text-indigo-600 dark:text-indigo-300')
                            </div>
                            <div>
                                <p class="text-xs text-slate-500 dark:text-slate-400">New This Month</p>
                                <p class="text-xl font-semibold text-slate-800 dark:text-slate-100">{{ $newStudentsThisMonth }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="rounded-xl border border-slate-100 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-800/60">
                         <div class="flex items-center gap-x-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-indigo-100 dark:bg-indigo-500/20">
                                @svg('heroicon-o-chat-bubble-left-right', 'h-5 w-5 text-indigo-600 dark:text-indigo-300')
                            </div>
                            <div>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Open Enquiries</p>
                                <p class="text-xl font-semibold text-slate-800 dark:text-slate-100">{{ $openEnquiries }}</p>
                            </div>
                        </div>
                    </div>
                     <div class="rounded-xl border border-slate-100 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-800/60">
                         <div class="flex items-center gap-x-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-indigo-100 dark:bg-indigo-500/20">
                                @svg('heroicon-o-banknotes', 'h-5 w-5 text-indigo-600 dark:text-indigo-300')
                            </div>
                            <div>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Collected (This Month)</p>
                                <p class="text-xl font-semibold text-slate-800 dark:text-slate-100">INR {{ number_format($collectedFeesThisMonth, 2) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Divider --}}
                <hr class="my-6 border-slate-200 dark:border-slate-800">

                {{-- Finance Snapshot Content --}}
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="text-base font-semibold text-slate-800 dark:text-slate-100">Finance Details</h3>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Monitor your cashflow at a glance.</p>
                    </div>
                    <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-200">Live</span>
                </div>

                <dl class="mt-6 grid gap-4 sm:grid-cols-2">
                    <div class="rounded-xl border border-slate-100 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-800/60">
                        <dt class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Outstanding Fees</dt>
                        <dd class="mt-2 text-2xl font-semibold text-slate-900 dark:text-white">INR {{ number_format($feeOutstanding, 2) }}</dd>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Stay on top of pending collections.</p>
                    </div>
                    <div class="rounded-xl border border-slate-100 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-800/60">
                        <dt class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Active Fee Plans</dt>
                        <dd class="mt-2 text-2xl font-semibold text-slate-900 dark:text-white">{{ $feeStructureCount }}</dd>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Available fee structures for admissions.</p>
                    </div>
                </dl>

                <div class="mt-6">
                    <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-200">Active Fee Structures</h4>
                    <ul class="mt-3 divide-y divide-slate-200 rounded-lg border border-slate-200 dark:divide-slate-800 dark:border-slate-800">
                        @forelse($activeFeeStructures as $structure)
                            <li class="flex items-center justify-between px-4 py-3 text-sm text-slate-700 dark:text-slate-300">
                                <div>
                                    <p class="font-medium text-slate-900 dark:text-white">{{ $structure->name }}</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">
                                        {{ $structure->classGroup->name ?? 'All Classes' }} · INR {{ number_format($structure->amount, 2) }} · {{ ucfirst(str_replace('_', ' ', $structure->frequency)) }}
                                    </p>
                                </div>
                                <span class="text-xs text-slate-400 dark:text-slate-500">Updated {{ $structure->updated_at->diffForHumans() }}</span>
                            </li>
                        @empty
                            <li class="px-4 py-4 text-center text-sm text-slate-500 dark:text-slate-400">No active fee structures yet.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>

        {{-- Sidebar Column (No changes here) --}}
        <div class="space-y-6 lg:col-span-1">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h3 class="text-base font-semibold text-slate-800 dark:text-slate-100">Upcoming Follow-ups</h3>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Stay in touch with prospective students.</p>
                <ul class="mt-4 space-y-3">
                    @forelse($upcomingFollowUps as $followUp)
                        <li class="rounded-lg border border-slate-200 px-3 py-2 text-sm dark:border-slate-800">
                            <div class="flex items-center justify-between">
                                <span class="font-medium text-slate-800 dark:text-slate-100">{{ $followUp->name }}</span>
                                <span class="text-xs text-indigo-600 dark:text-indigo-300">{{ optional($followUp->follow_up_at)->format('d M · h:i a') }}</span>
                            </div>
                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ $followUp->classGroup->name ?? 'General enquiry' }}</p>
                        </li>
                    @empty
                        <li class="rounded-lg border border-dashed border-slate-200 px-3 py-6 text-center text-sm text-slate-500 dark:border-slate-800 dark:text-slate-400">No follow-ups scheduled.</li>
                    @endforelse
                </ul>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h3 class="text-base font-semibold text-slate-800 dark:text-slate-100">Upcoming Birthdays</h3>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Send greetings and strengthen relationships.</p>
                <ul class="mt-4 space-y-3">
                    @forelse($upcomingBirthdays as $birthday)
                        <li class="flex items-center justify-between rounded-lg bg-gradient-to-r from-violet-500/10 via-indigo-500/10 to-blue-500/10 px-3 py-2 text-sm text-slate-700 dark:text-slate-200">
                            <div>
                                <p class="font-semibold text-slate-900 dark:text-white">{{ $birthday->name }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Turns {{ $birthday->turning_age }} on {{ $birthday->next_birthday->format('d M') }}</p>
                            </div>
                            <span class="rounded-full bg-indigo-500/10 px-3 py-1 text-xs font-medium text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-200">
                                {{ $birthday->days_until_birthday }} {{ \Illuminate\Support\Str::plural('day', $birthday->days_until_birthday) }}
                            </span>
                        </li>
                    @empty
                        <li class="rounded-lg border border-dashed border-slate-200 px-3 py-6 text-center text-sm text-slate-500 dark:border-slate-800 dark:text-slate-400">No birthdays in the next 30 days.</li>
                    @endforelse
                </ul>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h3 class="text-base font-semibold text-slate-800 dark:text-slate-100">Recent Enquiries</h3>
                <ul class="mt-4 space-y-3">
                    @forelse($recentEnquiries as $enquiry)
                        <li class="rounded-lg bg-slate-50 px-3 py-2 text-sm dark:bg-slate-800/60">
                            <div class="flex items-center justify-between">
                                <span class="font-medium text-slate-800 dark:text-slate-100">{{ $enquiry->name }}</span>
                                <span class="text-xs text-slate-500 dark:text-slate-400">{{ $enquiry->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                {{ $enquiry->classGroup->name ?? 'General' }} · {{ ucfirst($enquiry->status) }}
                            </p>
                        </li>
                    @empty
                        <li class="rounded-lg border border-dashed border-slate-200 px-3 py-6 text-center text-sm text-slate-500 dark:border-slate-800 dark:text-slate-400">No enquiries logged yet.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
@endsection