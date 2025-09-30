<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">Reception Dashboard</h2>
    </x-slot>

    <div class="space-y-8 p-6">
        @if (session('status'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 shadow-sm dark:border-emerald-700/50 dark:bg-emerald-900/40 dark:text-emerald-200">
                {{ session('status') }}
            </div>
        @endif

        <section class="rounded-2xl border border-gray-100 bg-white px-6 py-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="flex flex-wrap items-center justify-between gap-6">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Welcome back</p>
                    <h1 class="text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ $user->name }}</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $user->email }}</p>
                </div>
                <div class="rounded-xl bg-sky-50 px-4 py-3 text-right text-sm text-sky-700 dark:bg-sky-900/50 dark:text-sky-200">
                    <p class="text-xs uppercase tracking-wide">Enquiries</p>
                    <p class="text-3xl font-semibold">{{ $enquiryStats['open'] }} open</p>
                    <p class="mt-1 text-xs text-sky-500/80 dark:text-sky-200/80">{{ $enquiryStats['total'] }} total / {{ $enquiryStats['followUps'] }} follow-ups</p>
                </div>
            </div>
        </section>

        <section aria-label="Quick navigation" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @if ($canRecordAttendance)
                <a href="#attendance" class="group flex items-center justify-between rounded-xl border border-gray-200 bg-white px-4 py-3 shadow-sm transition hover:-translate-y-1 hover:border-emerald-400 hover:shadow-lg dark:border-gray-700 dark:bg-gray-800">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Class attendance</p>
                        <p class="mt-1 text-sm font-medium text-gray-900 dark:text-gray-100">Mark today&apos;s presence</p>
                    </div>
                    <span class="text-emerald-500 transition group-hover:translate-x-1">&rarr;</span>
                </a>
            @endif
            @if ($canManageEnquiry)
                <a href="#enquiries" class="group flex items-center justify-between rounded-xl border border-gray-200 bg-white px-4 py-3 shadow-sm transition hover:-translate-y-1 hover:border-indigo-400 hover:shadow-lg dark:border-gray-700 dark:bg-gray-800">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Enquiries</p>
                        <p class="mt-1 text-sm font-medium text-gray-900 dark:text-gray-100">Recent follow-ups</p>
                    </div>
                    <span class="text-indigo-500 transition group-hover:translate-x-1">&rarr;</span>
                </a>
            @endif
            <a href="#birthdays" class="group flex items-center justify-between rounded-xl border border-gray-200 bg-white px-4 py-3 shadow-sm transition hover:-translate-y-1 hover:border-rose-400 hover:shadow-lg dark:border-gray-700 dark:bg-gray-800">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Upcoming birthdays</p>
                    <p class="mt-1 text-sm font-medium text-gray-900 dark:text-gray-100">Celebrate with students</p>
                </div>
                <span class="text-rose-500 transition group-hover:translate-x-1">&rarr;</span>
            </a>
            <a href="#subjects" class="group flex items-center justify-between rounded-xl border border-gray-200 bg-white px-4 py-3 shadow-sm transition hover:-translate-y-1 hover:border-amber-400 hover:shadow-lg dark:border-gray-700 dark:bg-gray-800">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Subjects</p>
                    <p class="mt-1 text-sm font-medium text-gray-900 dark:text-gray-100">Quick directory</p>
                </div>
                <span class="text-amber-500 transition group-hover:translate-x-1">&rarr;</span>
            </a>
        </section>

        <section aria-label="Enquiry metrics" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Total enquiries</p>
                <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $enquiryStats['total'] }}</p>
            </div>
            <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Open enquiries</p>
                <p class="mt-2 text-2xl font-semibold text-indigo-600 dark:text-indigo-300">{{ $enquiryStats['open'] }}</p>
            </div>
            <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Awaiting follow-up</p>
                <p class="mt-2 text-2xl font-semibold text-amber-600 dark:text-amber-300">{{ $enquiryStats['followUps'] }}</p>
            </div>
        </section>

        <div class="grid gap-6 xl:grid-cols-3">
            @if ($canRecordAttendance)
                <section id="attendance" class="xl:col-span-2 space-y-4 rounded-2xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Class attendance</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Select a class group to record attendance for {{ \Illuminate\Support\Carbon::parse($selectedDate)->format('M d, Y') }}</p>
                        </div>
                    </div>

                    <form method="GET" action="{{ route('reception.dashboard') }}" class="mt-2 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
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
                        <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">Select a class group to view and record attendance.</p>
                    @else
                        <form method="POST" action="{{ route('reception.attendance.store') }}" class="space-y-4">
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

            <section id="birthdays" class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
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

        @if ($canManageEnquiry)
            <section id="enquiries" class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="flex items-center justify-between gap-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Recent enquiries</h3>
                    <span class="text-sm text-gray-500 dark:text-gray-400">Latest 6 entries</span>
                </div>

                @if ($recentEnquiries->isEmpty())
                    <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">New admission enquiries will appear here for quick follow-up.</p>
                @else
                    <ul class="mt-4 space-y-3 text-sm">
                        @foreach ($recentEnquiries as $enquiry)
                            <li class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-900/40">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-gray-100">{{ $enquiry->student_name }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ optional($enquiry->classGroup)->name ?? 'Class TBD' }}</p>
                                    </div>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">{{ $enquiry->created_at->diffForHumans() }}</span>
                                </div>
                                @if ($enquiry->notes)
                                    <p class="mt-2 text-sm text-gray-700 dark:text-gray-200">{{ \Illuminate\Support\Str::limit($enquiry->notes, 160) }}</p>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>
        @endif

        <section id="subjects" class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="flex items-center justify-between gap-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Subjects directory</h3>
                <span class="text-sm text-gray-500 dark:text-gray-400">{{ $subjects->count() }} subjects</span>
            </div>

            @if ($subjects->isEmpty())
                <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">Subjects will appear once they are created.</p>
            @else
                <div class="mt-4 flex flex-wrap gap-2">
                    @foreach ($subjects as $subject)
                        <span class="inline-flex items-center rounded-full bg-indigo-50 px-3 py-1 text-xs font-medium text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-200">{{ $subject->name }}</span>
                    @endforeach
                </div>
            @endif
        </section>

        @if ($studentProfile && $studentData)
            <section class="space-y-4 rounded-2xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="flex items-center justify-between gap-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Student view</h3>
                    <span class="text-sm text-gray-500 dark:text-gray-400">As {{ $studentProfile->name }}</span>
                </div>

                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900/40">
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Total sessions</p>
                        <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $studentData['attendanceSummary']['total'] }}</p>
                    </div>
                    <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900/40">
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Present</p>
                        <p class="mt-2 text-2xl font-semibold text-emerald-600 dark:text-emerald-400">{{ $studentData['attendanceSummary']['present'] }}</p>
                    </div>
                    <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900/40">
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Absent</p>
                        <p class="mt-2 text-2xl font-semibold text-rose-600 dark:text-rose-400">{{ $studentData['attendanceSummary']['absent'] }}</p>
                    </div>
                    <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900/40">
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Attendance %</p>
                        <p class="mt-2 text-2xl font-semibold text-indigo-600 dark:text-indigo-300">{{ $studentData['attendanceSummary']['percentage'] !== null ? $studentData['attendanceSummary']['percentage'].'%' : 'N/A' }}</p>
                    </div>
                </div>

                <div class="grid gap-6 lg:grid-cols-2">
                    <div>
                        <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Upcoming classes</h4>
                        @if ($studentData['timetableByDay']->isEmpty())
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">No timetable available.</p>
                        @else
                            <ul class="mt-2 space-y-2 text-sm">
                                @foreach ($studentData['timetableByDay']->take(3) as $day => $entries)
                                    @php $firstEntry = $entries->first(); @endphp
                                    <li class="rounded-lg border border-gray-100 px-3 py-2 dark:border-gray-700 dark:bg-gray-900/40">
                                        <div class="font-medium text-gray-900 dark:text-gray-100">{{ $day }} - {{ optional($firstEntry->subject)->name ?? 'Subject' }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ optional($firstEntry->start_time)->format('H:i') ?? '--:--' }} - {{ optional($firstEntry->end_time)->format('H:i') ?? '--:--' }}</div>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                    <div>
                        <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Recent attendance</h4>
                        @if ($studentData['recentAttendance']->isEmpty())
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">No attendance records yet.</p>
                        @else
                            <ul class="mt-2 space-y-2 text-sm">
                                @foreach ($studentData['recentAttendance']->take(5) as $record)
                                    <li class="flex items-center justify-between rounded-lg border border-gray-100 px-3 py-2 dark:border-gray-700 dark:bg-gray-900/40">
                                        <span class="text-gray-700 dark:text-gray-200">{{ optional($record->date)->format('M d, Y') }}</span>
                                        <span class="text-xs font-semibold {{ $record->present ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">{{ $record->present ? 'Present' : 'Absent' }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            </section>
        @endif
    </div>
</x-app-layout>
