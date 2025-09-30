<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">Teacher Dashboard</h2>
    </x-slot>

    <div class="space-y-8 p-6">
        <section class="rounded-2xl border border-gray-100 bg-white px-6 py-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="flex flex-wrap items-center justify-between gap-6">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Welcome back</p>
                    <h1 class="text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ $teacher->name }}</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $classGroups->count() ? $classGroups->pluck('name')->join(', ') : 'No class groups assigned yet' }}</p>
                </div>
                <div class="rounded-xl bg-sky-50 px-4 py-3 text-right text-sm text-sky-700 dark:bg-sky-900/50 dark:text-sky-200">
                    <p class="text-xs uppercase tracking-wide">Today</p>
                    <p class="text-3xl font-semibold">{{ now()->format('D, M d') }}</p>
                    <p class="mt-1 text-xs text-sky-500/80 dark:text-sky-200/80">{{ $weeklySessions }} session{{ $weeklySessions === 1 ? '' : 's' }} scheduled this week</p>
                </div>
            </div>
        </section>

        <section aria-label="Quick navigation" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @if ($canRecordAttendance)
                <a href="#attendance" class="group flex items-center justify-between rounded-xl border border-gray-200 bg-white px-4 py-3 shadow-sm transition hover:-translate-y-1 hover:border-emerald-400 hover:shadow-lg dark:border-gray-700 dark:bg-gray-800">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Record attendance</p>
                        <p class="mt-1 text-sm font-medium text-gray-900 dark:text-gray-100">Quick mark for today</p>
                    </div>
                    <span class="text-emerald-500 transition group-hover:translate-x-1">&rarr;</span>
                </a>
            @endif
            <a href="#timetable" class="group flex items-center justify-between rounded-xl border border-gray-200 bg-white px-4 py-3 shadow-sm transition hover:-translate-y-1 hover:border-indigo-400 hover:shadow-lg dark:border-gray-700 dark:bg-gray-800">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Timetable</p>
                    <p class="mt-1 text-sm font-medium text-gray-900 dark:text-gray-100">Weekly overview</p>
                </div>
                <span class="text-indigo-500 transition group-hover:translate-x-1">&rarr;</span>
            </a>
            <a href="{{ route('teacher.test-performances.index') }}" class="group flex items-center justify-between rounded-xl border border-gray-200 bg-white px-4 py-3 shadow-sm transition hover:-translate-y-1 hover:border-amber-400 hover:shadow-lg dark:border-gray-700 dark:bg-gray-800">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Assessments</p>
                    <p class="mt-1 text-sm font-medium text-gray-900 dark:text-gray-100">Test performance</p>
                </div>
                <span class="text-amber-500 transition group-hover:translate-x-1">&rarr;</span>
            </a>
            <a href="#birthdays" class="group flex items-center justify-between rounded-xl border border-gray-200 bg-white px-4 py-3 shadow-sm transition hover:-translate-y-1 hover:border-rose-400 hover:shadow-lg dark:border-gray-700 dark:bg-gray-800">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Upcoming birthdays</p>
                    <p class="mt-1 text-sm font-medium text-gray-900 dark:text-gray-100">Celebrate with your class</p>
                </div>
                <span class="text-rose-500 transition group-hover:translate-x-1">&rarr;</span>
            </a>
        </section>

        <section aria-label="Teaching summary" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Class groups</p>
                <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $classGroups->count() }}</p>
            </div>
            <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Students taught</p>
                <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $totalStudents }}</p>
            </div>
            <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Sessions this week</p>
                <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $weeklySessions }}</p>
            </div>
            <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Selected class</p>
                <p class="mt-2 text-lg font-medium text-gray-900 dark:text-gray-100">{{ optional($selectedClassGroup)->name ?? 'Select from below' }}</p>
            </div>
        </section>

        <div class="grid gap-6 xl:grid-cols-3">
            <section id="timetable" class="xl:col-span-2 rounded-xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="flex items-center justify-between gap-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Weekly timetable</h3>
                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ $teacher->name }}</span>
                </div>

                @if ($timetableByDay->isEmpty())
                    <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">No timetable has been assigned to you yet.</p>
                @else
                    <div class="mt-4 space-y-5">
                        @foreach ($timetableByDay as $day => $entries)
                            <div>
                                <h4 class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ $day }}</h4>
                                <ul class="mt-2 space-y-2">
                                    @foreach ($entries as $entry)
                                        <li class="flex items-center justify-between gap-4 rounded-lg border border-gray-100 px-3 py-2 text-sm shadow-sm dark:border-gray-700 dark:bg-gray-900/40">
                                            <div>
                                                <p class="font-medium text-gray-900 dark:text-gray-100">{{ optional($entry->subject)->name ?? 'Subject TBD' }}</p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ optional($entry->classGroup)->name ?? 'Class TBD' }}</p>
                                            </div>
                                            <div class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ optional($entry->start_time)->format('H:i') ?? '--:--' }} - {{ optional($entry->end_time)->format('H:i') ?? '--:--' }}</div>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>

            <section id="birthdays" class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="flex items-center justify-between gap-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Upcoming birthdays</h3>
                    <span class="text-sm text-gray-500 dark:text-gray-400">Next 3 weeks</span>
                </div>

                @if ($upcomingBirthdays->isEmpty())
                    <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">No birthdays in the next few weeks.</p>
                @else
                    <ul class="mt-4 space-y-3 text-sm">
                        @foreach ($upcomingBirthdays as $birthdayStudent)
                            <li class="flex items-center justify-between rounded-lg border border-gray-200 px-3 py-2 dark:border-gray-700 dark:bg-gray-900/40">
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-gray-100">{{ $birthdayStudent->name }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ optional($birthdayStudent->classGroup)->name ?? 'Class TBD' }}</p>
                                </div>
                                <div class="text-right text-xs text-gray-500 dark:text-gray-400">
                                    <p>{{ $birthdayStudent->next_birthday->format('M d') }}</p>
                                    <p>{{ $birthdayStudent->days_until_birthday }} day{{ $birthdayStudent->days_until_birthday === 1 ? '' : 's' }}</p>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>
        </div>

        @if ($canRecordAttendance)
            <section id="attendance" class="space-y-4 rounded-2xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Take attendance</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Select a class group and mark attendance for {{ \Illuminate\Support\Carbon::parse($selectedDate)->format('M d, Y') }}</p>
                    </div>
                </div>

                <form method="GET" action="{{ route('teacher.dashboard') }}" class="mt-2 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div>
                        <label for="class_group_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Class group</label>
                        <select id="class_group_id" name="class_group_id" class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                            @foreach ($classGroups as $group)
                                <option value="{{ $group->id }}" @selected($group->id === $selectedClassGroupId)>{{ $group->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="attendance_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Date</label>
                        <input id="attendance_date" type="date" name="date" value="{{ $selectedDate }}" class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100" />
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="inline-flex w-full items-center justify-center rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:bg-gray-100 dark:text-gray-900 dark:hover:bg-gray-200">
                            Update selection
                        </button>
                    </div>
                </form>

                @if ($students->isEmpty())
                    <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">Select one of your class groups to view and mark attendance.</p>
                @else
                    <form method="POST" action="{{ route('teacher.attendance.store') }}" class="space-y-4">
                        @csrf
                        <input type="hidden" name="class_group_id" value="{{ $selectedClassGroupId }}">
                        <input type="hidden" name="date" value="{{ $selectedDate }}">

                        <div class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700">
                            <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-900/60">
                                    <tr>
                                        <th scope="col" class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Student</th>
                                        <th scope="col" class="px-4 py-3 text-center font-semibold text-gray-600 dark:text-gray-300">Present</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach ($students as $student)
                                        @php
                                            $attendance = $attendanceRecords->get($student->id);
                                            $isPresent = $attendance ? (bool) $attendance->present : false;
                                        @endphp
                                        <tr class="bg-white dark:bg-gray-900/40">
                                            <td class="px-4 py-3">
                                                <div class="font-medium text-gray-900 dark:text-gray-100">{{ $student->name }}</div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $student->email ?? 'N/A' }}</div>
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                                <input type="checkbox" name="present[]" value="{{ $student->id }}" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900" @checked($isPresent)>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <span class="text-xs text-gray-500 dark:text-gray-400">Checked students will be marked present for {{ \Illuminate\Support\Carbon::parse($selectedDate)->format('M d, Y') }}.</span>
                            <button type="submit" class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                Save attendance
                            </button>
                        </div>
                    </form>
                @endif
            </section>
        @endif
    </div>
</x-app-layout>
