@extends('layouts.admin')

@section('title', 'Add Guardian | ' . config('app.name'))
@section('header', 'Add Guardian for ' . $student->name)

@section('content')
<div class="p-4 sm:p-6">
    <div class="mx-auto max-w-2xl">
        {{-- Status flash --}}
        @if(session('status'))
            <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-600 dark:bg-green-900/40 dark:text-green-300">
                {{ session('status') }}
            </div>
        @endif

        {{-- Global errors --}}
        @if ($errors->any())
            <div class="mb-4 rounded-lg border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-500 dark:bg-red-900/40 dark:text-red-200">
                <ul class="list-disc space-y-1 pl-4">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST"
              action="{{ route('admin.students.store-guardian', $student) }}"
              x-data="{ type: '{{ old('guardian_type', 'new') }}', showPwdNew: false, showPwdExist: false }">
            @csrf

            <div class="rounded-xl border bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                {{-- Guardian Type --}}
                <div>
                    <h3 class="text-base font-semibold">Guardian Type</h3>
                    <div class="mt-2 flex gap-6">
                        <label class="inline-flex items-center gap-2">
                            <input type="radio" class="accent-blue-600" name="guardian_type" value="new" x-model="type">
                            <span>New guardian</span>
                        </label>
                        <label class="inline-flex items-center gap-2">
                            <input type="radio" class="accent-blue-600" name="guardian_type" value="existing" x-model="type">
                            <span>Existing guardian</span>
                        </label>
                    </div>
                </div>

                {{-- Existing guardian picker + optional reset password --}}
                <div x-show="type === 'existing'" x-cloak class="mt-6 grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label for="existing_guardian_id" class="mb-1 block text-sm font-medium">Select guardian</label>
                        <select id="existing_guardian_id" name="existing_guardian_id"
                                class="w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2 dark:border-gray-700 dark:bg-gray-950">
                            <option value="">— Choose —</option>
                            @foreach($existingGuardians as $g)
                                <option value="{{ $g->id }}" @selected(old('existing_guardian_id') == $g->id)>
                                    {{ $g->name }} — {{ $g->email }}
                                </option>
                            @endforeach
                        </select>
                        @error('existing_guardian_id')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-sm font-medium">Set New Password (optional)</label>
                        <div class="relative" x-data>
                            <input :type="showPwdExist ? 'text' : 'password'"
                                   name="password"
                                   class="w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2 dark:border-gray-700 dark:bg-gray-950">
                            <button type="button"
                                    @click="showPwdExist = !showPwdExist"
                                    class="absolute inset-y-0 right-2 text-xs text-gray-600 dark:text-gray-300">
                                <span x-text="showPwdExist ? 'Hide' : 'Show'"></span>
                            </button>
                        </div>
                        @error('password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        <p class="mt-1 text-xs text-gray-500">
                            If provided, this will reset the guardian’s password. Leave blank to keep current password.
                            Confirm below to proceed.
                        </p>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-sm font-medium">Confirm Password</label>
                        <input type="password" name="password_confirmation"
                               class="w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2 dark:border-gray-700 dark:bg-gray-950">
                    </div>
                </div>

                {{-- New guardian inputs --}}
                <div x-show="type === 'new'" x-cloak class="mt-6 grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-sm font-medium">Guardian name</label>
                        <input type="text" name="name" value="{{ old('name') }}"
                               class="w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2 dark:border-gray-700 dark:bg-gray-950">
                        @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-sm font-medium">Guardian email</label>
                        <input type="email" name="email" value="{{ old('email') }}"
                               class="w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2 dark:border-gray-700 dark:bg-gray-950">
                        @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        <p class="mt-1 text-xs text-gray-500">
                            If you don’t set a password below, the default will be this email (they can change it later).
                        </p>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-sm font-medium">Relationship (e.g., Father, Mother, Guardian)</label>
                        <input type="text" name="relationship_type" value="{{ old('relationship_type') }}"
                               class="w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2 dark:border-gray-700 dark:bg-gray-950">
                        @error('relationship_type') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-sm font-medium">Password (optional)</label>
                        <div class="relative">
                            <input :type="showPwdNew ? 'text' : 'password'"
                                   name="password"
                                   class="w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2 dark:border-gray-700 dark:bg-gray-950">
                            <button type="button"
                                    @click="showPwdNew = !showPwdNew"
                                    class="absolute inset-y-0 right-2 text-xs text-gray-600 dark:text-gray-300">
                                <span x-text="showPwdNew ? 'Hide' : 'Show'"></span>
                            </button>
                        </div>
                        @error('password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        <p class="mt-1 text-xs text-gray-500">
                            Leave blank to use the guardian’s email as the default password.
                        </p>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-sm font-medium">Confirm Password</label>
                        <input type="password" name="password_confirmation"
                               class="w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2 dark:border-gray-700 dark:bg-gray-950">
                    </div>
                </div>

                {{-- Footer actions --}}
                <div class="mt-8 flex items-center justify-between">
                    <a href="{{ route('admin.students.edit', $student) }}"
                       class="text-sm text-gray-600 underline hover:text-gray-800 dark:text-gray-300 dark:hover:text-white">
                        ← Back to Student
                    </a>
                    <button type="submit"
                            class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                        Save & proceed to Documents
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
