@php
    $selectedPayable = $selectedPayable ?? old('payable');
@endphp

<div class='grid gap-4 md:grid-cols-2'>
    <div class='md:col-span-2'>
        <label class='block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400'>Payable to<span class='text-rose-500'>*</span></label>
        <select name='payable' required class='mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100'>
            <option value=''>Select staff member</option>
            @foreach($teachers as $teacher)
                @php
                    $value = 'teacher:' . $teacher->id;
                    $name = $teacher->name ?: optional($teacher->user)->name;
                @endphp
                <option value='{{ $value }}' @selected($selectedPayable === $value)>
                    Teacher: {{ $name ?? 'Unknown' }}
                </option>
            @endforeach
            @foreach($staff as $user)
                @php
                    $value = 'user:' . $user->id;
                @endphp
                <option value='{{ $value }}' @selected($selectedPayable === $value)>
                    Staff: {{ $user->name }}
                </option>
            @endforeach
        </select>
        @error('payable')
            <p class='mt-2 text-sm text-rose-500'>{{ $message }}</p>
        @enderror
    </div>
    <div>
        <label class='block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400'>Amount (₹)<span class='text-rose-500'>*</span></label>
        <input type='number' name='amount' value='{{ old('amount', optional($payroll ?? null)->amount) }}' min='0' step='0.01' required class='mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100'>
        @error('amount')
            <p class='mt-2 text-sm text-rose-500'>{{ $message }}</p>
        @enderror
    </div>
    <div>
        <label class='block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400'>Status<span class='text-rose-500'>*</span></label>
        <select name='status' required class='mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100'>
            @foreach(['pending' => 'Pending', 'processing' => 'Processing', 'paid' => 'Paid'] as $value => $label)
                <option value='{{ $value }}' @selected(old('status', optional($payroll ?? null)->status) === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('status')
            <p class='mt-2 text-sm text-rose-500'>{{ $message }}</p>
        @enderror
    </div>
    <div>
        <label class='block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400'>Payment method</label>
        <input type='text' name='payment_method' value='{{ old('payment_method', optional($payroll ?? null)->payment_method) }}' class='mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100'>
        @error('payment_method')
            <p class='mt-2 text-sm text-rose-500'>{{ $message }}</p>
        @enderror
    </div>
    <div>
        <label class='block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400'>Reference</label>
        <input type='text' name='reference' value='{{ old('reference', optional($payroll ?? null)->reference) }}' class='mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100'>
        @error('reference')
            <p class='mt-2 text-sm text-rose-500'>{{ $message }}</p>
        @enderror
    </div>
    <div>
        <label class='block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400'>Due on</label>
        <input type='date' name='due_on' value='{{ old('due_on', optional(optional($payroll ?? null)->due_on)->format('Y-m-d')) }}' class='mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100'>
        @error('due_on')
            <p class='mt-2 text-sm text-rose-500'>{{ $message }}</p>
        @enderror
    </div>
    <div>
        <label class='block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400'>Period start</label>
        <input type='date' name='period_start' value='{{ old('period_start', optional(optional($payroll ?? null)->period_start)->format('Y-m-d')) }}' class='mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100'>
        @error('period_start')
            <p class='mt-2 text-sm text-rose-500'>{{ $message }}</p>
        @enderror
    </div>
    <div>
        <label class='block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400'>Period end</label>
        <input type='date' name='period_end' value='{{ old('period_end', optional(optional($payroll ?? null)->period_end)->format('Y-m-d')) }}' class='mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100'>
        @error('period_end')
            <p class='mt-2 text-sm text-rose-500'>{{ $message }}</p>
        @enderror
    </div>
</div>
<div>
    <label class='block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400'>Notes</label>
    <textarea name='notes' rows='3' class='mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100'>{{ old('notes', optional($payroll ?? null)->notes) }}</textarea>
    @error('notes')
        <p class='mt-2 text-sm text-rose-500'>{{ $message }}</p>
    @enderror
</div>
