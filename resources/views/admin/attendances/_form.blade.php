<form method="POST" action="{{ $route }}" class="mt-6 space-y-6">
    @csrf
    @if($method !== 'POST')
        @method($method)
    @endif

    <div class="grid gap-4 md:grid-cols-2">
        <div class="md:col-span-2">
            <label for="student_id" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Student</label>
            <select id="student_id" name="student_id" data-behavior="student-search" required
                    class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                <option value="">Select student</option>
                @foreach($students as $id => $name)
                    <option value="{{ $id }}" @selected((int) old('student_id', $attendance->student_id) === (int) $id)>{{ $name }} (ID #{{ $id }})</option>
                @endforeach
            </select>
            @error('student_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="date" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Date</label>
            <input id="date" name="date" type="date" required
                   value="{{ old('date', optional($attendance->date)->format('Y-m-d')) }}"
                   class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
            @error('date') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="present" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Status</label>
            <select id="present" name="present" required
                    class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                <option value="1" @selected(old('present', (int) $attendance->present) === 1)>Present</option>
                <option value="0" @selected(old('present', (int) $attendance->present) === 0)>Absent</option>
            </select>
            @error('present') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>
    </div>

    <div class="flex items-center justify-end gap-3">
        <a href="{{ route('admin.attendances.index') }}"
           class="rounded border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-100 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800">Cancel</a>
        <button type="submit"
                class="rounded bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700">
            {{ $submitLabel }}
        </button>
    </div>
</form>
