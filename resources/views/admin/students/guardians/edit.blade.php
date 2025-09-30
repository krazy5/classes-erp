@extends('layouts.admin')

@section('title', 'Edit Guardian | ' . config('app.name'))
@section('header', 'Edit Guardian')

@section('content')
    <div class="mx-auto max-w-3xl">
        <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-900">
            <div class="mb-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $guardian->name }}</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Updating guardian linked to
                    <strong class="text-gray-900 dark:text-gray-200">{{ $student->name }}</strong>
                </p>
            </div>

            @php
                // Get current relationship from the pivot for this specific student-guardian pair
                $relationshipType = optional(
                    $student->guardians->firstWhere('id', $guardian->id)
                )->pivot->relationship_type ?? '';
            @endphp

            <form method="POST"
                  action="{{ route('admin.students.guardians.update', ['student' => $student->id, 'guardian' => $guardian->id]) }}"
                  class="space-y-6">
                @csrf
                @method('PUT')

                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Full name</label>
                    <input id="name" name="name" type="text"
                           value="{{ old('name', $guardian->name) }}" required
                           class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                    @error('name')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                    <input id="email" name="email" type="email"
                           value="{{ old('email', $guardian->email) }}" required
                           class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                    @error('email')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="relationship_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Relationship</label>
                    <input id="relationship_type" name="relationship_type" type="text"
                           value="{{ old('relationship_type', $relationshipType) }}"
                           placeholder="e.g. Mother, Father, Guardian"
                           class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                    @error('relationship_type')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid gap-6 md:grid-cols-2">
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">New password</label>
                        <input id="password" name="password" type="password"
                               class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Leave blank to keep the current password.</p>
                        @error('password')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Confirm password</label>
                        <input id="password_confirmation" name="password_confirmation" type="password"
                               class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3">
                    <a href="{{ route('admin.students.show', $student->id) }}"
                       class="rounded border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-100 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800">Cancel</a>
                    <button type="submit"
                            class="rounded bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700">
                        Save changes
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
