@extends('layouts.admin')

@section('title', 'Staff | ' . config('app.name'))
@section('header', 'Staff Accounts')

@section('content')
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <form method="GET" class="w-full md:max-w-sm">
            <label class="sr-only" for="search">Search staff</label>
            <div class="flex rounded-lg shadow-sm">
                <input id="search" name="search" value="{{ $search }}"
                       class="w-full rounded-l-lg border border-r-0 border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                       placeholder="Search by name or email">
                <button type="submit"
                        class="rounded-r-lg bg-indigo-600 px-4 text-sm font-semibold text-white transition hover:bg-indigo-700">Search</button>
            </div>
        </form>

        <a href="{{ route('management.staff.create') }}"
           class="inline-flex items-center justify-center rounded bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700">New Staff</a>
    </div>

    @if(session('status'))
        <div class="mt-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-600 dark:bg-green-900/40 dark:text-green-300">
            {{ session('status') }}
        </div>
    @endif

    <div class="mt-6 overflow-hidden rounded-lg bg-white shadow dark:bg-gray-900">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
            <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:bg-gray-800 dark:text-gray-300">
                <tr>
                    <th class="px-4 py-3">Staff</th>
                    <th class="px-4 py-3">Role</th>
                    <th class="px-4 py-3">DOB</th>
                    <th class="px-4 py-3">Email</th>
                    <th class="px-4 py-3 text-right"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                @forelse($staff as $member)
                    <tr class="text-sm text-gray-700 dark:text-gray-200">
                        <td class="px-4 py-3">
                            <div class="font-semibold text-gray-900 dark:text-gray-100">{{ $member->name }}</div>
                        </td>
                        <td class="px-4 py-3">{{ ucfirst($member->getRoleNames()->first() ?? '-') }}</td>
                        <td class="px-4 py-3">{{ optional($member->date_of_birth)->format('M d, Y') ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $member->email }}</td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('management.staff.edit', $member) }}"
                                   class="rounded border border-gray-300 px-3 py-1 text-xs font-medium text-gray-700 transition hover:bg-gray-100 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800">Edit</a>
                                <form action="{{ route('management.staff.destroy', $member) }}" method="POST" onsubmit="return confirm('Delete this staff account?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="rounded border border-red-300 px-3 py-1 text-xs font-medium text-red-600 transition hover:bg-red-50 dark:border-red-500 dark:text-red-400 dark:hover:bg-red-600/10">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-sm text-gray-500 dark:text-gray-400">
                            No staff accounts recorded yet.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $staff->links() }}
    </div>
@endsection
