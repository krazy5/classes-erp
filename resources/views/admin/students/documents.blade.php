@extends('layouts.admin')

@section('title', 'Student Documents | ' . config('app.name'))
@section('header', 'Documents for ' . $student->name)

@section('content')
    <div class="space-y-6">
        @if (session('status'))
            <div class="rounded-lg border border-emerald-300 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-500/40 dark:bg-emerald-500/10 dark:text-emerald-200">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-lg border border-rose-300 bg-rose-50 px-4 py-3 text-sm text-rose-700 dark:border-rose-500/40 dark:bg-rose-500/10 dark:text-rose-200">
                <ul class="list-disc space-y-1 pl-4">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="text-sm text-gray-500 dark:text-gray-300">
                <a href="{{ route('admin.students.show', $student) }}" class="text-indigo-600 hover:text-indigo-500">&larr; Back to profile</a>
            </div>
            <div class="flex items-center gap-2">
                @if ($onboarding)
                    <a href="{{ route('admin.students.add-fee-plan', $student) }}"
                       class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-100 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800">
                        Skip for now
                    </a>
                @endif
            </div>
        </div>

        @if ($onboarding)
            <div class="rounded-lg border border-indigo-200 bg-indigo-50 px-4 py-3 text-sm text-indigo-700 dark:border-indigo-500/40 dark:bg-indigo-500/10 dark:text-indigo-200">
                Step 3 of 4: Upload key admission documents for <strong>{{ $student->name }}</strong>. You can add more later if needed.
            </div>
        @endif

        <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Upload documents</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Accepted formats include PDF, images, and common office files. Maximum size per file: 20&nbsp;MB.</p>

            <form method="POST" action="{{ route('admin.students.documents.store', $student) }}" enctype="multipart/form-data" class="mt-5 space-y-5">
                @csrf
                @if ($onboarding)
                    <input type="hidden" name="onboarding" value="1">
                @endif

                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label for="document_type" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Document type</label>
                        <select id="document_type" name="document_type"
                                class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                            <option value="">Select type (optional)</option>
                            @foreach ($documentTypes as $value => $label)
                                <option value="{{ $value }}" @selected(old('document_type') === $value)>{{ $label }}</option>
                            @endforeach
                            <option value="other" @selected(old('document_type') === 'other')>Other (specify below)</option>
                        </select>
                        @error('document_type')
                            <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="document_label" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Custom label</label>
                        <input id="document_label" name="document_label" type="text"
                               value="{{ old('document_label') }}"
                               placeholder="e.g. Aadhaar (Father)"
                               class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                        @error('document_label')
                            <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Required if you select Other. Otherwise optional.</p>
                    </div>

                    <div class="md:col-span-2">
                        <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Notes (optional)</label>
                        <textarea id="description" name="description" rows="3"
                                  class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">{{ old('description') }}</textarea>
                        @error('description')
                            <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="documents" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Select files</label>
                    <input id="documents" name="documents[]" type="file" multiple
                           class="mt-1 block w-full text-sm text-gray-700 file:mr-4 file:rounded-lg file:border-0 file:bg-indigo-600 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-indigo-700 dark:text-gray-200">
                    @error('documents')
                        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                    @enderror
                    @error('documents.*')
                        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex flex-wrap items-center justify-end gap-3">
                    @if ($onboarding)
                        <a href="{{ route('admin.students.add-fee-plan', $student) }}"
                           class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-100 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800">
                            Continue without upload
                        </a>
                    @endif
                    <button type="submit"
                            class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700">
                        Upload documents
                    </button>
                </div>
            </form>
        </div>

        <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Uploaded documents</h3>
                <span class="text-sm text-gray-500 dark:text-gray-400">Manage existing records</span>
            </div>

            @php
                $documents = $student->getMedia('documents')->sortByDesc('created_at');
            @endphp

            @if ($documents->isEmpty())
                <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">No documents uploaded yet.</p>
            @else
                <div class="mt-4 overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700">
                    <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900/60">
                            <tr>
                                <th class="px-4 py-2 text-left font-semibold text-gray-600 dark:text-gray-300">Document</th>
                                <th class="px-4 py-2 text-left font-semibold text-gray-600 dark:text-gray-300">Uploaded</th>
                                <th class="px-4 py-2 text-left font-semibold text-gray-600 dark:text-gray-300">Notes</th>
                                <th class="px-4 py-2 text-right font-semibold text-gray-600 dark:text-gray-300">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach ($documents as $media)
                                @php
                                    $meta = $media->custom_properties ?? [];
                                    $label = $meta['label'] ?? $meta['type_label'] ?? pathinfo($media->file_name, PATHINFO_FILENAME);
                                    $description = $meta['description'] ?? null;
                                    $uploader = $meta['uploaded_by_name'] ?? 'System';
                                    $uploaderRole = $meta['uploaded_by_role'] ?? null;
                                    $typeLabel = $meta['type_label'] ?? null;
                                @endphp
                                <tr class="bg-white dark:bg-gray-900/40">
                                    <td class="px-4 py-3 align-top">
                                        <div class="font-medium text-gray-900 dark:text-gray-100">{{ $label }}</div>
                                        @if ($typeLabel)
                                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ $typeLabel }}</div>
                                        @endif
                                        <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                            Uploaded by {{ $uploader }} @if($uploaderRole) ({{ ucfirst($uploaderRole) }}) @endif · {{ $media->created_at->format('d M Y, h:i A') }}
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">
                                            Size: {{ \Illuminate\Support\Number::fileSize($media->size) }} · Original: {{ $meta['original_name'] ?? $media->file_name }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 align-top text-sm text-gray-600 dark:text-gray-300">
                                        {{ $media->created_at->diffForHumans() }}
                                    </td>
                                    <td class="px-4 py-3 align-top text-sm text-gray-600 dark:text-gray-300">
                                        {{ $description ?: '—' }}
                                    </td>
                                    <td class="px-4 py-3 align-top text-right">
                                        <div class="flex justify-end gap-2">
                                            <a href="{{ route('admin.students.documents.download', [$student, $media]) }}"
                                               class="rounded border border-gray-300 px-3 py-1 text-xs font-medium text-gray-700 transition hover:bg-gray-100 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800">
                                                Download
                                            </a>
                                            <form action="{{ route('admin.students.documents.destroy', [$student, $media]) }}" method="POST"
                                                  onsubmit="return confirm('Remove this document?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="rounded border border-rose-300 px-3 py-1 text-xs font-semibold text-rose-600 transition hover:bg-rose-50 dark:border-rose-500/40 dark:text-rose-200 dark:hover:bg-rose-500/10">
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        @if ($onboarding)
            <div class="flex justify-end">
                <a href="{{ route('admin.students.add-fee-plan', $student) }}"
                   class="rounded-lg bg-indigo-600 px-5 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700">
                    Proceed to fee plan
                </a>
            </div>
        @endif
    </div>
@endsection
