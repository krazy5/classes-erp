<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">Student Test Performance</h2>
    </x-slot>

    <div class="space-y-6 p-6">
        @include('academics.test-performances.index-content')
    </div>
</x-app-layout>
