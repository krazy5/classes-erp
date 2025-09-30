<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">Student Dashboard</h2>
    </x-slot>

    <div class="space-y-8 p-6">
        <section class="rounded-2xl border border-gray-100 bg-white px-6 py-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="flex flex-wrap items-center justify-between gap-6">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Welcome back</p>
                    <h1 class="text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ $student->name }}</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ $classGroup ? 'Class Group: ' . $classGroup->name : 'Class group not assigned yet' }}
                    </p>
                </div>
                <div class="rounded-xl bg-indigo-50 px-4 py-3 text-right text-sm text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-200">
                    <p class="text-xs uppercase tracking-wide">Attendance rate</p>
                    <p class="text-3xl font-semibold">{{ $attendanceSummary['percentage'] !== null ? $attendanceSummary['percentage'] . '%' : 'N/A' }}</p>
                    <p class="mt-1 text-xs text-indigo-500/80 dark:text-indigo-200/80">Across {{ $attendanceSummary['total'] }} sessions</p>
                </div>
            </div>
        </section>

        <section aria-label="Quick navigation" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <a href="#timetable" class="group flex items-center justify-between rounded-xl border border-gray-200 bg-white px-4 py-3 shadow-sm transition hover:-translate-y-1 hover:border-indigo-400 hover:shadow-lg dark:border-gray-700 dark:bg-gray-800">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Timetable</p>
                    <p class="mt-1 text-sm font-medium text-gray-900 dark:text-gray-100">Weekly schedule</p>
                </div>
                <span class="text-indigo-500 transition group-hover:translate-x-1">&rarr;</span>
            </a>
            <a href="#attendance" class="group flex items-center justify-between rounded-xl border border-gray-200 bg-white px-4 py-3 shadow-sm transition hover:-translate-y-1 hover:border-emerald-400 hover:shadow-lg dark:border-gray-700 dark:bg-gray-800">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Attendance</p>
                    <p class="mt-1 text-sm font-medium text-gray-900 dark:text-gray-100">Latest 10 sessions</p>
                </div>
                <span class="text-emerald-500 transition group-hover:translate-x-1">&rarr;</span>
            </a>
            <a href="{{ route('student.test-performances.index') }}" class="group flex items-center justify-between rounded-xl border border-gray-200 bg-white px-4 py-3 shadow-sm transition hover:-translate-y-1 hover:border-amber-400 hover:shadow-lg dark:border-gray-700 dark:bg-gray-800">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Assessments</p>
                    <p class="mt-1 text-sm font-medium text-gray-900 dark:text-gray-100">View test performance</p>
                </div>
                <span class="text-amber-500 transition group-hover:translate-x-1">&rarr;</span>
            </a>
            <a href="#announcements" class="group flex items-center justify-between rounded-xl border border-gray-200 bg-white px-4 py-3 shadow-sm transition hover:-translate-y-1 hover:border-rose-400 hover:shadow-lg dark:border-gray-700 dark:bg-gray-800">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Announcements</p>
                    <p class="mt-1 text-sm font-medium text-gray-900 dark:text-gray-100">Latest updates</p>
                </div>
                <span class="text-rose-500 transition group-hover:translate-x-1">&rarr;</span>
            </a>
        </section>

        <section aria-label="Attendance summary" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Total sessions</p>
                <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $attendanceSummary['total'] }}</p>
            </div>
            <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Present</p>
                <p class="mt-2 text-2xl font-semibold text-emerald-600 dark:text-emerald-400">{{ $attendanceSummary['present'] }}</p>
            </div>
            <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Absent</p>
                <p class="mt-2 text-2xl font-semibold text-rose-600 dark:text-rose-400">{{ $attendanceSummary['absent'] }}</p>
            </div>
            <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Last recorded</p>
                <p class="mt-2 text-lg font-medium text-gray-900 dark:text-gray-100">{{ optional(optional($recentAttendance->first())->date)->format('M d, Y') ?? 'No records' }}</p>
            </div>
        </section>

        <div class="grid gap-6 lg:grid-cols-3">
            <section id="timetable" class="lg:col-span-2 rounded-xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="flex items-center justify-between gap-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Weekly timetable</h3>
                    @if ($classGroup)
                        <span class="text-sm text-gray-500 dark:text-gray-400">{{ $classGroup->name }}</span>
                    @endif
                </div>

                @if (!$classGroup)
                    <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">Your class group has not been assigned yet.</p>
                @elseif ($timetableByDay->isEmpty())
                    <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">Timetable data will appear once scheduled.</p>
                @else
                    <div class="mt-4 space-y-4">
                        @foreach ($timetableByDay as $day => $entries)
                            <div>
                                <h4 class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ $day }}</h4>
                                <ul class="mt-2 space-y-2">
                                    @foreach ($entries as $entry)
                                        <li class="rounded-lg border border-gray-100 px-3 py-2 text-sm shadow-sm dark:border-gray-700 dark:bg-gray-900/40">
                                            <div class="flex items-center justify-between gap-4">
                                                <div>
                                                    <p class="font-medium text-gray-900 dark:text-gray-100">{{ optional($entry->subject)->name ?? 'Subject TBD' }}</p>
                                                    <p class="text-xs text-gray-500 dark:text-gray-400">Teacher: {{ optional($entry->teacher)->name ?? 'TBD' }}</p>
                                                </div>
                                                <div class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ optional($entry->start_time)->format('H:i') ?? '--:--' }} - {{ optional($entry->end_time)->format('H:i') ?? '--:--' }}</div>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>

            <section id="attendance" class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="flex items-center justify-between gap-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Recent attendance</h3>
                    <span class="text-sm text-gray-500 dark:text-gray-400">Last 10 sessions</span>
                </div>

                @if ($recentAttendance->isEmpty())
                    <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">Attendance records will appear once available.</p>
                @else
                    <div class="mt-4 overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700">
                        <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-900/60">
                                <tr>
                                    <th scope="col" class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Date</th>
                                    <th scope="col" class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach ($recentAttendance as $record)
                                    <tr class="bg-white dark:bg-gray-900/40">
                                        <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ optional($record->date)->format('M d, Y') }}</td>
                                        <td class="px-4 py-3">
                                            @if ($record->present)
                                                <span class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-200">Present</span>
                                            @else
                                                <span class="inline-flex items-center rounded-full bg-rose-100 px-3 py-1 text-xs font-semibold text-rose-700 dark:bg-rose-900/50 dark:text-rose-200">Absent</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>
        </div>

        <section id="announcements" class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="flex items-center justify-between gap-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Recent announcements</h3>
                <span class="text-sm text-gray-500 dark:text-gray-400">Shared with you</span>
            </div>

            @if ($announcements->isEmpty())
                <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">Announcements for your class or sent directly to you will show up here.</p>
            @else
                <div class="mt-4 space-y-3">
                    @foreach ($announcements as $announcement)
                        <article class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-900/50">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $announcement->title }}</h4>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Published {{ optional($announcement->published_at)->format('d M Y') ?? $announcement->created_at->format('d M Y') }}</p>
                                </div>
                            </div>
                            <p class="mt-2 text-sm text-gray-700 dark:text-gray-200">{{ \Illuminate\Support\Str::limit($announcement->body, 200) }}</p>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>
    </div>
</x-app-layout>
