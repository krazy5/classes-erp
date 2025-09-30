@extends('layouts.admin')

@section('title', 'Record Attendance | ' . config('app.name'))
@section('header', 'Record Attendance')

@section('content')
    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <form method="GET" class="grid gap-4 md:grid-cols-3">
            <div>
                <label for="class_group_id" class="block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Class</label>
                <select id="class_group_id" name="class_group_id" required
                        class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                    <option value="">Select class</option>
                    @foreach($classGroups as $id => $name)
                        <option value="{{ $id }}" @selected($selectedClassGroup == $id)>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="date" class="block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Date</label>
                <input id="date" name="date" type="date" value="{{ $selectedDate }}" required
                       class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
            </div>
            <div class="flex items-end gap-3">
                <button type="submit"
                        class="inline-flex flex-1 items-center justify-center rounded bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700">Load students</button>
                <a href="{{ route('admin.attendances.entry') }}"
                   class="inline-flex items-center justify-center rounded border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-100 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800">Clear</a>
            </div>
        </form>
    </div>

    @if(session('status'))
        <div class="mt-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-600 dark:bg-green-900/40 dark:text-green-300">
            {{ session('status') }}
        </div>
    @endif

    @if($selectedClassGroup && $students->isEmpty())
        <div class="mt-6 rounded-lg border border-dashed border-gray-300 px-4 py-6 text-center text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
            No students found for the selected class.
        </div>
    @endif

    @if($students->isNotEmpty())
        <div x-data="attendanceEntry()" x-init="init()" class="mt-6 space-y-4">
            <div class="flex flex-col gap-4 rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900 md:flex-row md:items-center md:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $classGroups[$selectedClassGroup] ?? 'Class' }}</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $students->count() }} students - {{ \Carbon\Carbon::parse($selectedDate)->format('M d, Y') }}</p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <button type="button" @click="markAll(true)"
                            class="rounded border border-indigo-300 px-3 py-2 text-xs font-semibold text-indigo-700 transition hover:bg-indigo-50 dark:border-indigo-500/40 dark:text-indigo-200 dark:hover:bg-indigo-500/10">Mark all present</button>
                    <button type="button" @click="markAll(false)"
                            class="rounded border border-gray-300 px-3 py-2 text-xs font-semibold text-gray-700 transition hover:bg-gray-100 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700/40">Mark all absent</button>
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <form method="POST" action="{{ route('admin.attendances.entry.store') }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="date" value="{{ $selectedDate }}">
                    <input type="hidden" name="class_group_id" value="{{ $selectedClassGroup }}">

                    <div class="overflow-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                            <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:bg-gray-800 dark:text-gray-300">
                                <tr>
                                    <th class="px-4 py-3">Student</th>
                                    <th class="px-4 py-3">Status</th>
                                    <th class="px-4 py-3">Last update</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                                @foreach($students as $student)
                                    @php
                                        $record = $student->attendances->first();
                                    @endphp
                                    <tr class="text-sm text-gray-700 dark:text-gray-200">
                                        <td class="px-4 py-3">
                                            <div class="flex items-center gap-3">
                                                @php
                                                    $photo = $student->getFirstMediaUrl('photo');
                                                @endphp
                                                @if($photo)
                                                    <img src="{{ $photo }}" alt="{{ $student->name }}" class="h-10 w-10 rounded-full object-cover">
                                                @else
                                                    <span class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-200 text-xs font-semibold uppercase text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                                        {{ strtoupper(substr($student->name, 0, 2)) }}
                                                    </span>
                                                @endif
                                                <div>
                                                    <div class="font-semibold text-gray-900 dark:text-gray-100">{{ $student->name }}</div>
                                                    <div class="text-xs text-gray-500 dark:text-gray-400">ID #{{ $student->id }}</div>
                                                </div>
                                            </div>
                                            <input type="hidden" name="students[]" value="{{ $student->id }}">
                                        </td>
                                        <td class="px-4 py-3">
                                            <label class="inline-flex items-center gap-2 text-sm">
                                                <input type="checkbox" name="present[]" value="{{ $student->id }}" class="present-toggle rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                                       @checked(optional($record)->present)>
                                                <span class="text-gray-700 dark:text-gray-300">Present</span>
                                            </label>
                                        </td>
                                        <td class="px-4 py-3 text-xs text-gray-500 dark:text-gray-400">
                                            {{ optional(optional($record)->updated_at)->diffForHumans() ?? 'N/A' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="flex items-center justify-between">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Marked present: <span data-present-count class="font-semibold text-gray-800 dark:text-gray-200">{{ $presentCount }}</span> / {{ $students->count() }}</p>
                        <button type="submit"
                                class="inline-flex items-center rounded bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700">Save attendance</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
@endsection

@push('scripts')
<script>
    function attendanceEntry() {
        return {
            root: null,
            init() {
                this.root = this.$root || this.$el || document.currentScript?.closest('[x-data]');
                this.refreshListeners();
                this.updateCount();
            },
            refreshListeners() {
                this.listCheckboxes().forEach(checkbox => {
                    checkbox.removeEventListener('change', this._attendanceChangeHandler);
                    checkbox.addEventListener('change', this._attendanceChangeHandler = () => this.updateCount());
                });
            },
            markAll(present) {
                this.listCheckboxes().forEach(checkbox => {
                    checkbox.checked = !!present;
                });
                this.updateCount();
            },
            listCheckboxes() {
                return this.root ? Array.from(this.root.querySelectorAll('.present-toggle')) : [];
            },
            updateCount() {
                const counter = this.root?.querySelector('[data-present-count]');
                if (counter) {
                    const total = this.listCheckboxes().filter(cb => cb.checked).length;
                    counter.innerText = total;
                }
            }
        };
    }
</script>
@endpush
