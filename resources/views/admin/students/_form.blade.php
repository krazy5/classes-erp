@php
    $genderOptions = [
        'male' => 'Male',
        'female' => 'Female',
        'other' => 'Other',
    ];
@endphp

<form method="POST" action="{{ $route }}" enctype="multipart/form-data" class="mt-6 space-y-6">
    @csrf
    @if($method !== 'POST')
        @method($method)
    @endif

    <div class="grid gap-4 md:grid-cols-2">
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Full name</label>
            <input id="name" name="name" type="text" required
                   value="{{ old('name', $student->name) }}"
                   class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
            @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Email</label>
            <input id="email" name="email" type="email" required
                   value="{{ old('email', $student->email) }}"
                   class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
            @error('email') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Phone</label>
            <input id="phone" name="phone" type="text"
                   value="{{ old('phone', $student->phone) }}"
                   class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
            @error('phone') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="dob" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Date of birth</label>
            <input id="dob" name="dob" type="date" required
                   value="{{ old('dob', optional($student->dob)->format('Y-m-d')) }}"
                   class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
            @error('dob') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="gender" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Gender</label>
            <select id="gender" name="gender"
                    class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                <option value="">Select gender</option>
                @foreach($genderOptions as $value => $label)
                    <option value="{{ $value }}" @selected(old('gender', $student->gender) === $value)>{{ $label }}</option>
                @endforeach
            </select>
            @error('gender') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="class_group_id" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Class group</label>
            <select id="class_group_id" name="class_group_id" required
                    class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                <option value="">Select class</option>
                @foreach($classGroups as $id => $label)
                    <option value="{{ $id }}" @selected((int) old('class_group_id', $student->class_group_id) === (int) $id)>{{ $label }}</option>
                @endforeach
            </select>
            @error('class_group_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

        <div class="md:col-span-2">
            <label for="address" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Address</label>
            <textarea id="address" name="address" rows="3"
                      class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">{{ old('address', $student->address) }}</textarea>
            @error('address') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

        @if(auth()->user()->hasAnyRole(['admin','manager']))
            <div class="md:col-span-2">
                <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Set or reset password</label>
                <input id="password" name="password" type="password"
                       class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                       placeholder="Leave blank to keep current password">
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Defaults to the student's birth date in ddmmyyyy format when left empty on create.</p>
                @error('password') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
        @endif
    </div>

    <div>
        <label for="photo" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Student photo</label>
        <input id="photo" name="photo" type="file" accept="image/*"
               class="mt-1 block w-full text-sm text-gray-700 file:mr-4 file:rounded-lg file:border-0 file:bg-indigo-600 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-indigo-700 dark:text-gray-200">
        @error('photo') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror

        @if($student->exists && $student->getFirstMediaUrl('photo'))
            <div class="mt-3 flex items-center gap-4">
                <img src="{{ $student->getFirstMediaUrl('photo') }}" alt="{{ $student->name }}" class="h-16 w-16 rounded-full object-cover">
                <label class="inline-flex items-center gap-2 text-xs text-gray-600 dark:text-gray-300">
                    <input type="checkbox" name="remove_photo" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                    Remove current photo
                </label>
            </div>
        @endif
    </div>

    <div class="flex items-center justify-end gap-3">
        <a href="{{ route('admin.students.index') }}"
           class="rounded border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-100 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800">Cancel</a>
        <button type="submit"
                class="rounded bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700">
            {{ $submitLabel }}
        </button>
    </div>
</form>
