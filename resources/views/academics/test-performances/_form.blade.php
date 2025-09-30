@php
    $selectedMetadata = $performance->metadata ?? [];
@endphp

<div class="space-y-6">
    <div class="grid gap-4 md:grid-cols-2">
        <div>
            <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400" for="student_id">Student<span class="text-rose-500">*</span></label>
            <select id="student_id" name="student_id" data-behavior="student-search" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                <option value="">Select student</option>
                @foreach ($students as $id => $name)
                    <option value="{{ $id }}" @selected((int) old('student_id', $performance->student_id) === (int) $id)>{{ $name }}</option>
                @endforeach
            </select>
            @error('student_id')
                <p class="mt-2 text-sm text-rose-500">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400" for="class_group_id">Class / Batch</label>
            <select id="class_group_id" name="class_group_id" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                <option value="">Auto detect</option>
                @foreach ($classGroups as $id => $name)
                    <option value="{{ $id }}" @selected((int) old('class_group_id', $performance->class_group_id) === (int) $id)>{{ $name }}</option>
                @endforeach
            </select>
            @error('class_group_id')
                <p class="mt-2 text-sm text-rose-500">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400" for="subject_id">Subject</label>
            <select id="subject_id" name="subject_id" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                <option value="">Select subject</option>
                @foreach ($subjects as $id => $name)
                    <option value="{{ $id }}" @selected((int) old('subject_id', $performance->subject_id) === (int) $id)>{{ $name }}</option>
                @endforeach
            </select>
            @error('subject_id')
                <p class="mt-2 text-sm text-rose-500">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400" for="test_date">Assessment date</label>
            <input id="test_date" type="date" name="test_date" value="{{ old('test_date', optional($performance->test_date)->toDateString()) }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
            @error('test_date')
                <p class="mt-2 text-sm text-rose-500">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-2">
        <div>
            <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400" for="title">Assessment title<span class="text-rose-500">*</span></label>
            <input id="title" type="text" name="title" value="{{ old('title', $performance->title) }}" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
            @error('title')
                <p class="mt-2 text-sm text-rose-500">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400" for="assessment_type">Assessment type</label>
            <input id="assessment_type" type="text" name="assessment_type" value="{{ old('assessment_type', $performance->assessment_type) }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100" placeholder="e.g. Written, Practical">
            @error('assessment_type')
                <p class="mt-2 text-sm text-rose-500">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400" for="term">Term / Exam cycle</label>
            <input id="term" type="text" name="term" value="{{ old('term', $performance->term) }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100" placeholder="e.g. Mid-term">
            @error('term')
                <p class="mt-2 text-sm text-rose-500">{{ $message }}</p>
            @enderror
        </div>
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400" for="max_score">Max score</label>
                <input id="max_score" type="number" step="0.01" min="0" name="max_score" value="{{ old('max_score', $performance->max_score) }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                @error('max_score')
                    <p class="mt-2 text-sm text-rose-500">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400" for="score">Obtained</label>
                <input id="score" type="number" step="0.01" min="0" name="score" value="{{ old('score', $performance->score) }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                @error('score')
                    <p class="mt-2 text-sm text-rose-500">{{ $message }}</p>
                @enderror
            </div>
        </div>
        <div>
            <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400" for="grade">Grade</label>
            <input id="grade" type="text" name="grade" value="{{ old('grade', $performance->grade) }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100" placeholder="e.g. A+">
            @error('grade')
                <p class="mt-2 text-sm text-rose-500">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400" for="metadata_weightage">Weightage (%)</label>
            <input id="metadata_weightage" type="number" step="0.01" min="0" max="100" name="metadata[weightage]" value="{{ old('metadata.weightage', data_get($selectedMetadata, 'weightage')) }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
            @error('metadata.weightage')
                <p class="mt-2 text-sm text-rose-500">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div>
        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400" for="remarks">Remarks</label>
        <textarea id="remarks" name="remarks" rows="4" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">{{ old('remarks', $performance->remarks) }}</textarea>
        @error('remarks')
            <p class="mt-2 text-sm text-rose-500">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400" for="attachments">Attachments</label>
        <input id="attachments" type="file" name="attachments[]" accept="application/pdf,image/*" multiple class="mt-1 w-full rounded-lg border border-dashed border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
        <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">Attach supporting documents such as scanned answer sheets or evaluation rubrics (PDF, JPG, PNG, WEBP).</p>
        @error('attachments.*')
            <p class="mt-2 text-sm text-rose-500">{{ $message }}</p>
        @enderror
    </div>

    @if ($performance->exists && $performance->media->isNotEmpty())
        <div class="rounded-lg border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-900/40">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Existing attachments</p>
            <ul class="mt-3 space-y-2 text-sm text-slate-700 dark:text-slate-200">
                @foreach ($performance->media as $media)
                    <li class="flex items-center justify-between gap-3 rounded-lg bg-white px-3 py-2 shadow-sm dark:bg-slate-800">
                        <div class="flex items-center gap-3">
                            @if (str_starts_with($media->mime_type, 'image/'))
                                <img src="{{ $media->getUrl('thumb') }}" alt="Attachment preview" class="h-12 w-12 rounded object-cover">
                            @else
                                <span class="inline-flex h-12 w-12 items-center justify-center rounded bg-indigo-100 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-200">PDF</span>
                            @endif
                            <div>
                                <p class="font-medium">{{ $media->file_name }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">{{ $media->human_readable_size }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <a href="{{ route($routePrefix.'attachments.download', [$performance, $media]) }}" class="rounded-lg border border-slate-300 px-3 py-1 text-xs font-medium text-slate-600 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">Download</a>
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
</div>
