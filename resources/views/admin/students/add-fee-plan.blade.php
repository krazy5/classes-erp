@extends('layouts.admin')

@section('title', 'Set Fee Plan | ' . config('app.name'))
@section('header', 'Set Fee Plan for ' . $student->name)

@section('content')
    <div class="p-4 sm:p-6">
        <div class="mx-auto max-w-xl">
            @if(session('status'))
                <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('admin.students.store-fee-plan', $student) }}" class="space-y-6">
                @csrf

                <div>
                    <label for="fee_structure_id" class="block text-sm font-medium">Fee Structure</label>
                    <select id="fee_structure_id" name="fee_structure_id" required class="mt-1 w-full rounded-lg border">
                        <option value="">Select a fee plan</option>
                        @foreach($feeStructures as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                    @error('fee_structure_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="notes" class="block text-sm font-medium">Notes (Optional)</label>
                    <textarea id="notes" name="notes" rows="4" class="mt-1 w-full rounded-lg border"></textarea>
                    @error('notes') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center justify-end gap-3 pt-4">
                     <a href="{{ route('admin.students.index') }}" class="rounded border px-4 py-2 text-sm font-medium text-gray-700">Skip & Finish Later</a>
                    <button type="submit" class="rounded bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">
                        Confirm & Complete Admission
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection