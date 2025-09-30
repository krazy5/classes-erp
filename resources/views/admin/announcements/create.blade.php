@extends('layouts.admin')

@section('title', 'New Announcement | ' . config('app.name'))
@section('header', 'Publish Announcement')

@section('content')
    <div class="mx-auto max-w-4xl rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <form method="POST" action="{{ route('admin.announcements.store') }}" class="space-y-6">
            @csrf
            <div>
                <label class="text-sm font-semibold text-slate-700 dark:text-slate-200">Title<span class="text-rose-500">*</span></label>
                <input type="text" name="title" value="{{ old('title') }}" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                @error('title')
                    <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="text-sm font-semibold text-slate-700 dark:text-slate-200">Message<span class="text-rose-500">*</span></label>
                <textarea name="body" rows="6" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">{{ old('body') }}</textarea>
                @error('body')
                    <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid gap-4 md:grid-cols-3">
                <label class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
                    <input type="hidden" name="publish_now" value="0">
                    <input type="checkbox" name="publish_now" value="1" @checked(old('publish_now', true)) class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900">
                    Publish now
                </label>
                <div class="md:col-span-2">
                    <label class="text-sm font-semibold text-slate-700 dark:text-slate-200">Schedule (optional)</label>
                    <input type="datetime-local" name="scheduled_for" value="{{ old('scheduled_for') }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                </div>
            </div>

            <div class="rounded-lg border border-slate-200 p-4 dark:border-slate-800">
                <label class="flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-200">
                    <input type="hidden" name="send_to_all" value="0">
                    <input type="checkbox" name="send_to_all" value="1" @checked(old('send_to_all')) class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900">
                    Send to entire institute
                </label>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">When selected, class and student filters are ignored.</p>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <div>
                    <label class="text-sm font-semibold text-slate-700 dark:text-slate-200">Target class groups</label>
                    <div class="mt-1 max-h-48 overflow-y-auto rounded-lg border border-slate-300 dark:border-slate-700">
                        <ul class="divide-y divide-slate-200 dark:divide-slate-800">
                            @foreach($classGroups as $classGroup)
                                <li class="flex items-center gap-2 px-3 py-2 text-sm text-slate-600 dark:text-slate-200">
                                    <input type="checkbox" name="class_group_ids[]" value="{{ $classGroup->id }}" @checked(in_array($classGroup->id, old('class_group_ids', []))) class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900">
                                    {{ $classGroup->name }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    @error('class_group_ids.*')
                        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="text-sm font-semibold text-slate-700 dark:text-slate-200">Target students</label>
                    <input type="text" id="student-filter" placeholder="Search students or guardians" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                    <div class="mt-1 max-h-48 overflow-y-auto rounded-lg border border-slate-300 dark:border-slate-700">
                        <ul class="divide-y divide-slate-200 dark:divide-slate-800">
                            @foreach($students as $student)
                                <li class="flex items-center gap-2 px-3 py-2 text-sm text-slate-600 dark:text-slate-200" data-student-item data-search="{{ $student->search_label }}">
                                    <input type="checkbox" name="student_ids[]" value="{{ $student->id }}" @checked(in_array($student->id, old('student_ids', []))) class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900">
                                    <span>{{ $student->display_label }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    @error('student_ids.*')
                        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('admin.announcements.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">Cancel</a>
                <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700">Publish announcement</button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const input = document.getElementById('student-filter');
            if (!input) return;

            const items = Array.from(document.querySelectorAll('[data-student-item]'));
            input.addEventListener('input', () => {
                const term = input.value.trim().toLowerCase();
                items.forEach((item) => {
                    const haystack = item.dataset.search || '';
                    const matches = term === '' || haystack.includes(term);
                    item.classList.toggle('hidden', !matches);
                });
            });
        });
    </script>
@endpush
