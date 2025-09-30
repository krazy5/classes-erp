<div class="grid gap-4 md:grid-cols-2">
    <div>
        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Category<span class="text-rose-500">*</span></label>
        <input type="text" name="category" value="{{ old('category', $expense->category) }}" list="expense-category-suggestions" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
        @isset($categorySuggestions)
            <datalist id="expense-category-suggestions">
                @foreach($categorySuggestions as $suggestion)
                    <option value="{{ $suggestion }}" />
                @endforeach
            </datalist>
        @endisset
        @error('category')
            <p class="mt-2 text-sm text-rose-500">{{ $message }}</p>
        @enderror
    </div>
    <div>
        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Title<span class="text-rose-500">*</span></label>
        <input type="text" name="title" value="{{ old('title', $expense->title) }}" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
        @error('title')
            <p class="mt-2 text-sm text-rose-500">{{ $message }}</p>
        @enderror
    </div>
    <div>
        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Amount (?)<span class="text-rose-500">*</span></label>
        <input type="number" name="amount" value="{{ old('amount', $expense->amount) }}" min="0" step="0.01" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
        @error('amount')
            <p class="mt-2 text-sm text-rose-500">{{ $message }}</p>
        @enderror
    </div>
    <div>
        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Incurred on<span class="text-rose-500">*</span></label>
        <input type="date" name="incurred_on" value="{{ old('incurred_on', optional($expense->incurred_on)->toDateString()) }}" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
        @error('incurred_on')
            <p class="mt-2 text-sm text-rose-500">{{ $message }}</p>
        @enderror
    </div>
    <div>
        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Payment method</label>
        <input type="text" name="payment_method" value="{{ old('payment_method', $expense->payment_method) }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
        @error('payment_method')
            <p class="mt-2 text-sm text-rose-500">{{ $message }}</p>
        @enderror
    </div>
    <div>
        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Reference</label>
        <input type="text" name="reference" value="{{ old('reference', $expense->reference) }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
        @error('reference')
            <p class="mt-2 text-sm text-rose-500">{{ $message }}</p>
        @enderror
    </div>
</div>
<div class="grid gap-4 md:grid-cols-2">
    <div>
        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Notes</label>
        <textarea name="notes" rows="3" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">{{ old('notes', $expense->notes) }}</textarea>
        @error('notes')
            <p class="mt-2 text-sm text-rose-500">{{ $message }}</p>
        @enderror
    </div>
    <div>
        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Supporting documents</label>
        <input type="file" name="attachments[]" accept="application/pdf,image/jpeg,image/png,image/webp" multiple class="mt-1 w-full rounded-lg border border-dashed border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Attach receipts or bills (PDF, JPG, PNG, WEBP up to 5 MB each).</p>
        @error('attachments.*')
            <p class="mt-2 text-sm text-rose-500">{{ $message }}</p>
        @enderror
    </div>
</div>

@if($expense->exists && $expense->media->isNotEmpty())
    <div class="mt-4 rounded-lg border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-900/40">
        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Uploaded documents</p>
        <ul class="mt-3 space-y-2 text-sm text-slate-700 dark:text-slate-200">
            @foreach($expense->media as $media)
                <li class="flex items-center justify-between gap-3 rounded-lg bg-white px-3 py-2 shadow-sm dark:bg-slate-800">
                    <div class="flex items-center gap-3">
                        @if(str_starts_with($media->mime_type, 'image/'))
                            <img src="{{ $media->getUrl('thumb') }}" alt="Receipt preview" class="h-12 w-12 rounded object-cover">
                        @else
                            <span class="inline-flex h-12 w-12 items-center justify-center rounded bg-indigo-100 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-200">PDF</span>
                        @endif
                        <div>
                            <p class="font-medium">{{ $media->file_name }}</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ $media->human_readable_size }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <a href="{{ route('admin.expenses.attachments.download', [$expense, $media]) }}" class="rounded-lg border border-slate-300 px-3 py-1 text-xs font-medium text-slate-600 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">Download</a>
                        <label class="inline-flex items-center gap-2 text-xs text-rose-600">
                            <input type="checkbox" name="remove_attachments[]" value="{{ $media->id }}" class="h-4 w-4 rounded border-slate-300 text-rose-600 focus:ring-rose-500 dark:border-slate-600 dark:bg-slate-900">
                            <span>Remove</span>
                        </label>
                    </div>
                </li>
            @endforeach
        </ul>
    </div>
@endif
