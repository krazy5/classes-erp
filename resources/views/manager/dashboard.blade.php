@extends('layouts.admin')

@section('title', 'Manager Dashboard | ' . config('app.name'))
@section('header', 'Manager Dashboard')

@section('content')
    @php
        $formatNumber = fn ($value) => number_format((int) $value);
        $formatCurrency = fn ($value) => number_format((float) $value, 2);
        $currencySymbol = config('app.currency_symbol', '₹');
    @endphp

    <div class="space-y-10">
        <livewire:qr-codes.manage-daily-qr-code />
        <section class="space-y-4">
            <div>
                <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Student & Fee Management</h2>
                <p class="text-sm text-zinc-500 dark:text-zinc-400">Track enrollment, fee health, and upcoming payments at a glance.</p>
            </div>
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <x-admin.metric-card
                    title="Total students"
                    :value="$formatNumber($studentCount)"
                    :subtitle="'Enrolled this month: ' . $formatNumber($newStudentsThisMonth)"
                    icon="heroicon-o-user-group"
                />

                <x-admin.metric-card
                    title="Outstanding fees"
                    :value="$currencySymbol . $formatCurrency($feeOutstanding)"
                    :subtitle="'Open invoices: ' . $formatNumber($feeOutstandingCount)"
                    icon="heroicon-o-banknotes"
                />

                <x-admin.metric-card
                    title="Collected this month"
                    :value="$currencySymbol . $formatCurrency($feesCollectedThisMonth)"
                    subtitle="Month-to-date collections"
                    icon="heroicon-o-chart-bar"
                />

                <x-admin.metric-card
                    title="Active enquiries"
                    :value="$formatNumber($openEnquiries)"
                    :subtitle="'Follow-ups due (7d): ' . $formatNumber($upcomingFollowUpsCount)"
                    icon="heroicon-o-chat-bubble-bottom-center-text"
                />
            </div>
            <div class="flex flex-wrap items-center gap-2 md:gap-3 pt-3">
                <a href="{{ route('admin.students.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-zinc-200 px-3 py-1.5 text-xs font-medium text-zinc-600 transition hover:bg-zinc-100 dark:border-zinc-700 dark:text-zinc-300 dark:hover:bg-zinc-800">
                    @svg('heroicon-o-user-group', 'h-4 w-4')
                    <span>Manage students</span>
                </a>
                <a href="{{ route('admin.payments.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-zinc-200 px-3 py-1.5 text-xs font-medium text-zinc-600 transition hover:bg-zinc-100 dark:border-zinc-700 dark:text-zinc-300 dark:hover:bg-zinc-800">
                    @svg('heroicon-o-banknotes', 'h-4 w-4')
                    <span>Review payments</span>
                </a>
                <a href="{{ route('admin.enquiries.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-zinc-200 px-3 py-1.5 text-xs font-medium text-zinc-600 transition hover:bg-zinc-100 dark:border-zinc-700 dark:text-zinc-300 dark:hover:bg-zinc-800">
                    @svg('heroicon-o-chat-bubble-left-right', 'h-4 w-4')
                    <span>Open enquiries</span>
                </a>
                <a href="{{ route('admin.reports.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-zinc-200 px-3 py-1.5 text-xs font-medium text-zinc-600 transition hover:bg-zinc-100 dark:border-zinc-700 dark:text-zinc-300 dark:hover:bg-zinc-800">
                    @svg('heroicon-o-chart-bar', 'h-4 w-4')
                    <span>View reports</span>
                </a>
            </div>
        </section>

        <section class="grid gap-6 lg:grid-cols-2">
            <div class="rounded-2xl border border-zinc-200 bg-white/95 p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900/90">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">Upcoming installments</h3>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">Due within the next 7 days.</p>
                        <a href="{{ route('admin.payments.index') }}" class="mt-2 inline-flex items-center text-xs font-medium text-amber-700 hover:underline dark:text-amber-300">Go to payments</a>
                    </div>
                    <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-medium text-amber-700 dark:bg-amber-500/20 dark:text-amber-300">{{ $formatNumber($upcomingInstallmentCount) }} due soon</span>
                </div>

                <ul class="mt-4 space-y-3">
                    @forelse ($upcomingInstallments as $installment)
                        <li class="flex items-center justify-between gap-3 rounded-xl border border-zinc-200 px-3 py-3 text-sm dark:border-zinc-800">
                            <div>
                                <p class="font-medium text-zinc-900 dark:text-zinc-100">{{ optional($installment->feeRecord?->student)->name ?? 'Unknown student' }}</p>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">Due {{ optional($installment->due_date)->format('d M, Y') ?? '—' }}</p>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $currencySymbol }}{{ $formatCurrency($installment->balance) }}</p>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">Sequence #{{ $installment->sequence }}</p>
                            </div>
                        </li>
                    @empty
                        <li class="rounded-xl border border-dashed border-zinc-200 px-4 py-6 text-center text-sm text-zinc-500 dark:border-zinc-700 dark:text-zinc-400">
                            All clear! No installments due in the next week.
                        </li>
                    @endforelse
                </ul>
            </div>

            <div class="rounded-2xl border border-zinc-200 bg-white/95 p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900/90">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">Overdue installments</h3>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">Payments that need immediate attention.</p>
                        <a href="{{ route('admin.payments.index') }}" class="mt-2 inline-flex items-center text-xs font-medium text-rose-700 hover:underline dark:text-rose-300">Contact families</a>
                    </div>
                    <span class="rounded-full bg-rose-100 px-3 py-1 text-xs font-medium text-rose-700 dark:bg-rose-500/20 dark:text-rose-300">{{ $formatNumber($overdueInstallmentCount) }} overdue</span>
                </div>

                <ul class="mt-4 space-y-3">
                    @forelse ($overdueInstallments as $installment)
                        <li class="flex items-center justify-between gap-3 rounded-xl border border-rose-200/70 px-3 py-3 text-sm dark:border-rose-500/30">
                            <div>
                                <p class="font-medium text-zinc-900 dark:text-zinc-100">{{ optional($installment->feeRecord?->student)->name ?? 'Unknown student' }}</p>
                                <p class="text-xs text-rose-600 dark:text-rose-300">Was due {{ optional($installment->due_date)->format('d M, Y') ?? '—' }}</p>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold text-rose-600 dark:text-rose-300">{{ $currencySymbol }}{{ $formatCurrency($installment->balance) }}</p>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">Sequence #{{ $installment->sequence }}</p>
                            </div>
                        </li>
                    @empty
                        <li class="rounded-xl border border-dashed border-zinc-200 px-4 py-6 text-center text-sm text-zinc-500 dark:border-zinc-700 dark:text-zinc-400">
                            Fantastic! No overdue installments right now.
                        </li>
                    @endforelse
                </ul>
            </div>
        </section>

        <section class="grid gap-6 lg:grid-cols-2">
            <div class="rounded-2xl border border-zinc-200 bg-white/95 p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900/90">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">Upcoming follow-ups</h3>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">Stay on top of enquiries scheduled for this week.</p>
                        <a href="{{ route('admin.enquiries.index') }}" class="mt-2 inline-flex items-center text-xs font-medium text-sky-700 hover:underline dark:text-sky-300">Open enquiry board</a>
                    </div>
                    <span class="rounded-full bg-sky-100 px-3 py-1 text-xs font-medium text-sky-700 dark:bg-sky-500/20 dark:text-sky-300">{{ $formatNumber($upcomingFollowUpsCount) }} due</span>
                </div>

                <ul class="mt-4 space-y-3">
                    @forelse ($upcomingFollowUps as $enquiry)
                        <li class="flex items-center justify-between gap-3 rounded-xl border border-zinc-200 px-3 py-3 text-sm dark:border-zinc-800">
                            <div>
                                <p class="font-medium text-zinc-900 dark:text-zinc-100">{{ $enquiry->name }}</p>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ optional($enquiry->follow_up_at)->format('d M, h:i A') }} • {{ $enquiry->status ? ucfirst($enquiry->status) : 'status pending' }}</p>
                            </div>
                            @if ($enquiry->assignee)
                                <span class="rounded-full bg-sky-100 px-3 py-1 text-xs font-medium text-sky-700 dark:bg-sky-500/20 dark:text-sky-300">{{ $enquiry->assignee->name }}</span>
                            @endif
                        </li>
                    @empty
                        <li class="rounded-xl border border-dashed border-zinc-200 px-4 py-6 text-center text-sm text-zinc-500 dark:border-zinc-700 dark:text-zinc-400">
                            No follow-ups scheduled for the next 7 days.
                        </li>
                    @endforelse
                </ul>
            </div>

            <div class="rounded-2xl border border-zinc-200 bg-white/95 p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900/90">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">Overdue follow-ups</h3>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">Inactive enquiries that need a quick nudge.</p>
                        <a href="{{ route('admin.enquiries.index') }}" class="mt-2 inline-flex items-center text-xs font-medium text-rose-700 hover:underline dark:text-rose-300">Reach out now</a>
                    </div>
                    <span class="rounded-full bg-rose-100 px-3 py-1 text-xs font-medium text-rose-700 dark:bg-rose-500/20 dark:text-rose-300">{{ $formatNumber($overdueFollowUpsCount) }} overdue</span>
                </div>

                <ul class="mt-4 space-y-3">
                    @forelse ($overdueFollowUps as $enquiry)
                        <li class="flex items-center justify-between gap-3 rounded-xl border border-rose-200/70 px-3 py-3 text-sm dark:border-rose-500/30">
                            <div>
                                <p class="font-medium text-zinc-900 dark:text-zinc-100">{{ $enquiry->name }}</p>
                                <p class="text-xs text-rose-600 dark:text-rose-300">Missed on {{ optional($enquiry->follow_up_at)->format('d M, h:i A') }}</p>
                            </div>
                            @if ($enquiry->assignee)
                                <span class="rounded-full bg-rose-100 px-3 py-1 text-xs font-medium text-rose-700 dark:bg-rose-500/20 dark:text-rose-300">{{ $enquiry->assignee->name }}</span>
                            @endif
                        </li>
                    @empty
                        <li class="rounded-xl border border-dashed border-zinc-200 px-4 py-6 text-center text-sm text-zinc-500 dark:border-zinc-700 dark:text-zinc-400">
                            Great job! No overdue follow-ups outstanding.
                        </li>
                    @endforelse
                </ul>
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-3">
            <div class="rounded-2xl border border-zinc-200 bg-white/95 p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900/90 xl:col-span-2">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">Attendance overview</h3>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">Last 7 teaching days</p>
                    </div>
                    <a href="{{ route('admin.attendances.index') }}" class="inline-flex items-center text-xs font-medium text-indigo-600 hover:underline dark:text-indigo-300">Review attendance</a>
                </div>

                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-200 text-sm dark:divide-zinc-800">
                        <thead class="bg-zinc-50 text-xs uppercase text-zinc-500 dark:bg-zinc-800 dark:text-zinc-300">
                            <tr>
                                <th class="px-3 py-2 text-left">Date</th>
                                <th class="px-3 py-2 text-center">Present</th>
                                <th class="px-3 py-2 text-center">Absent</th>
                                <th class="px-3 py-2 text-center">Attendance %</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                            @forelse ($attendanceSummary as $day)
                                <tr class="text-zinc-700 dark:text-zinc-200">
                                    <td class="px-3 py-2">{{ $day['date'] }}</td>
                                    <td class="px-3 py-2 text-center">{{ $day['present'] }}</td>
                                    <td class="px-3 py-2 text-center">{{ $day['absent'] }}</td>
                                    <td class="px-3 py-2 text-center">
                                        @php
                                            $total = max(1, $day['total']);
                                            $percentage = round(($day['present'] / $total) * 100);
                                        @endphp
                                        {{ $percentage }}%
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-3 py-6 text-center text-sm text-zinc-500 dark:text-zinc-400">Attendance data will appear once classes record attendance.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="rounded-2xl border border-zinc-200 bg-white/95 p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900/90">
                <h3 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">Today's timetable</h3>
                <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ now()->format('l, d M Y') }}</p>

                <ul class="mt-4 space-y-3">
                    @forelse ($todayClasses as $slot)
                        <li class="rounded-xl border border-zinc-200 px-3 py-3 text-sm dark:border-zinc-800">
                            <p class="font-medium text-zinc-900 dark:text-zinc-100">{{ $slot->classGroup->name ?? 'Class group' }} • {{ $slot->subject->name ?? 'Subject' }}</p>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ optional($slot->start_time)->format('H:i') }} - {{ optional($slot->end_time)->format('H:i') }} • {{ $slot->teacher->name ?? 'TBD' }}</p>
                        </li>
                    @empty
                        <li class="rounded-xl border border-dashed border-zinc-200 px-4 py-6 text-center text-sm text-zinc-500 dark:border-zinc-700 dark:text-zinc-400">
                            No classes scheduled for today.
                        </li>
                    @endforelse
                </ul>
            </div>
        </section>

        <section class="grid gap-6 lg:grid-cols-3">
            <div class="rounded-2xl border border-zinc-200 bg-white/95 p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900/90 lg:col-span-2">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">Latest announcements</h3>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">Keep your community informed.</p>
                    </div>
                </div>

                <ul class="mt-4 space-y-3">
                    @forelse ($latestAnnouncements as $announcement)
                        <li class="rounded-xl border border-zinc-200 px-4 py-4 dark:border-zinc-800">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{ $announcement->title }}</p>
                                    <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">{{ \Illuminate\Support\Str::limit($announcement->body, 140) }}</p>
                                </div>
                                <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ optional($announcement->published_at)->format('d M, Y') ?? 'Draft' }}</span>
                            </div>
                        </li>
                    @empty
                        <li class="rounded-xl border border-dashed border-zinc-200 px-4 py-6 text-center text-sm text-zinc-500 dark:border-zinc-700 dark:text-zinc-400">
                            No announcements yet. Share updates with students, parents, or staff from here.
                        </li>
                    @endforelse
                </ul>
            </div>

            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-6 text-sm text-amber-900 shadow-sm dark:border-amber-500/40 dark:bg-amber-500/10 dark:text-amber-100">
                <h3 class="text-base font-semibold">Manager guardrails</h3>
                <ul class="mt-3 space-y-2">
                    <li><strong class="font-semibold">Can do:</strong> manage students, invoices, payments, enquiries, attendance, timetables, and announcements.</li>
                    <li><strong class="font-semibold">Restricted:</strong> no access to tenant settings, fee structures, payroll, or admin account management.</li>
                    <li>All changes remain scoped to your tenant to keep operations secure.</li>
                </ul>
            </div>
        </section>
    </div>
@endsection

