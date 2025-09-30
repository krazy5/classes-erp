<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">Update Test Performance</h2>
    </x-slot>

    <div class="p-6">
        <div class="mx-auto max-w-4xl rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <form method="POST" action="{{ route($routePrefix.'update', $performance) }}" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PUT')
                @include('academics.test-performances._form')
                <div class="flex items-center justify-end gap-3">
                    <a href="{{ route($routePrefix.'index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">Cancel</a>
                    <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700">
                        @svg('heroicon-s-arrow-path', 'h-4 w-4')
                        <span>Update record</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
