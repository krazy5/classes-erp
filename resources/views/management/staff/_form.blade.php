<form method="POST" action="{{ $route }}" class="mt-6 space-y-6">
    @csrf
    @if($method !== 'POST')
        @method($method)
    @endif

    <div class="grid gap-4 md:grid-cols-2">
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Full name</label>
            <input id="name" name="name" type="text" required
                   value="{{ old('name', $staff->name) }}"
                   class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
            @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Email</label>
            <input id="email" name="email" type="email" required
                   value="{{ old('email', $staff->email) }}"
                   class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
            @error('email') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="date_of_birth" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Date of birth</label>
            <input id="date_of_birth" name="date_of_birth" type="date" required
                   value="{{ old('date_of_birth', optional($staff->date_of_birth)->format('Y-m-d')) }}"
                   class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
            @error('date_of_birth') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="role" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Role</label>
            <select id="role" name="role" required
                    class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                <option value="">Select role</option>
                @foreach($roles as $role)
                    <option value="{{ $role }}" @selected(old('role', $staff->getRoleNames()->first()) === $role)>{{ ucfirst($role) }}</option>
                @endforeach
            </select>
            @error('role') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

        @if(auth()->user()->hasAnyRole(['admin','manager']))
            <div class="md:col-span-2">
                <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Set or reset password</label>
                <input id="password" name="password" type="password"
                       class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                       placeholder="Leave blank to keep current password">
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Defaults to the birth date in ddmmyyyy format when left empty on create.</p>
                @error('password') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
        @endif
    </div>

    <div class="flex items-center justify-end gap-3">
        <a href="{{ route('management.staff.index') }}"
           class="rounded border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-100 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800">Cancel</a>
        <button type="submit"
                class="rounded bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700">
            {{ $submitLabel }}
        </button>
    </div>
</form>
