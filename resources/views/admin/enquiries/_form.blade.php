@csrf
<div class="space-y-4">
    <div class="grid gap-4 md:grid-cols-2">
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200" for="name">Name</label>
            <input type="text" id="name" name="name" value="{{ old('name', $enquiry->name ?? '') }}" required
                   class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
            @error('name')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200" for="phone">Phone</label>
                <input type="text" id="phone" name="phone" value="{{ old('phone', $enquiry->phone ?? '') }}"
                       class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                @error('phone')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200" for="email">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email', $enquiry->email ?? '') }}"
                       class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-2">
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200" for="class_group_id">Interested Class</label>
            <select id="class_group_id" name="class_group_id"
                    class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                <option value="">— Optional —</option>
                @foreach($classGroups as $id => $label)
                    <option value="{{ $id }}" @selected(old('class_group_id', $enquiry->class_group_id ?? null) == $id)>{{ $label }}</option>
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
                    <option value="{{ $id }}" @selected(old('subject_id', $enquiry->subject_id ?? null) == $id)>{{ $label }}</option>
                @endforeach
            </select>
            @error('subject_id')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-3">
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200" for="source">Source</label>
            <input type="text" id="source" name="source" value="{{ old('source', $enquiry->source ?? '') }}"
                   class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
            @error('source')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200" for="status">Status</label>
            <select id="status" name="status"
                    class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                @foreach($statuses as $statusOption)
                    <option value="{{ $statusOption }}" @selected(old('status', $enquiry->status ?? 'new') === $statusOption)>{{ ucfirst($statusOption) }}</option>
                @endforeach
            </select>
            @error('status')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200" for="assigned_to">Assigned To</label>
            <select id="assigned_to" name="assigned_to"
                    class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                <option value="">— Unassigned —</option>
                @foreach($staff as $id => $label)
                    <option value="{{ $id }}" @selected(old('assigned_to', $enquiry->assigned_to ?? null) == $id)>{{ $label }}</option>
                @endforeach
            </select>
            @error('assigned_to')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-2">
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200" for="follow_up_at">Follow-up Date</label>
            <input type="datetime-local" id="follow_up_at" name="follow_up_at" value="{{ old('follow_up_at', optional($enquiry->follow_up_at)->format('Y-m-d\TH:i')) }}"
                   class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
            @error('follow_up_at')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200" for="closed_at">Closed Date</label>
            <input type="datetime-local" id="closed_at" name="closed_at" value="{{ old('closed_at', optional($enquiry->closed_at)->format('Y-m-d\TH:i')) }}"
                   class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
            @error('closed_at')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200" for="notes">Notes</label>
        <textarea id="notes" name="notes" rows="4"
                  class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">{{ old('notes', $enquiry->notes ?? '') }}</textarea>
        @error('notes')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="mt-6 flex items-center justify-end gap-3">
    <a href="{{ route('admin.enquiries.index') }}" class="inline-flex items-center rounded border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-100 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800">Cancel</a>
    <button type="submit" class="inline-flex items-center rounded bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700">Save</button>
</div>
