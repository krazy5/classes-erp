@csrf
<div class="space-y-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200" for="name">Name</label>
        <input type="text" id="name" name="name" value="{{ old('name', $feeStructure->name ?? '') }}" required
               class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
        @error('name')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200" for="description">Description</label>
        <textarea id="description" name="description" rows="3"
                  class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">{{ old('description', $feeStructure->description ?? '') }}</textarea>
        @error('description')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="grid gap-4 md:grid-cols-2">
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200" for="class_group_id">Class Group</label>
            <select id="class_group_id" name="class_group_id"
                    class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                <option value="">— Optional —</option>
                @foreach($classGroups as $id => $label)
                    <option value="{{ $id }}" @selected(old('class_group_id', $feeStructure->class_group_id ?? null) == $id)>{{ $label }}</option>
                @endforeach
            </select>
            @error('class_group_id')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200" for="subject_id">Subject</label>
            <select id="subject_id" name="subject_id"
                    class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                <option value="">— Optional —</option>
                @foreach($subjects as $id => $label)
                    <option value="{{ $id }}" @selected(old('subject_id', $feeStructure->subject_id ?? null) == $id)>{{ $label }}</option>
                @endforeach
            </select>
            @error('subject_id')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-3">
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200" for="amount">Amount</label>
            <input type="number" step="0.01" id="amount" name="amount" value="{{ old('amount', $feeStructure->amount ?? '') }}" required
                   class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
            @error('amount')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200" for="frequency">Frequency</label>
            <select id="frequency" name="frequency" required
                    class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                @foreach(['one_time' => 'One time', 'monthly' => 'Monthly', 'quarterly' => 'Quarterly'] as $value => $label)
                    <option value="{{ $value }}" @selected(old('frequency', $feeStructure->frequency ?? 'one_time') == $value)>{{ $label }}</option>
                @endforeach
            </select>
            @error('frequency')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div class="flex items-center gap-2 pt-6">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" id="is_active" name="is_active" value="1" @checked(old('is_active', $feeStructure->is_active ?? true))
                   class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
            <label for="is_active" class="text-sm font-medium text-gray-700 dark:text-gray-200">Active</label>
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-2">
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200" for="effective_from">Effective From</label>
            <input type="date" id="effective_from" name="effective_from" value="{{ old('effective_from', optional($feeStructure->effective_from ?? null)->format('Y-m-d')) }}"
                   class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
            @error('effective_from')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200" for="effective_to">Effective To</label>
            <input type="date" id="effective_to" name="effective_to" value="{{ old('effective_to', optional($feeStructure->effective_to ?? null)->format('Y-m-d')) }}"
                   class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
            @error('effective_to')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>
</div>

<div class="mt-6 flex items-center justify-end gap-3">
    <a href="{{ route('admin.fee-structures.index') }}" class="inline-flex items-center rounded border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-100 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800">Cancel</a>
    <button type="submit" class="inline-flex items-center rounded bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700">Save</button>
</div>

