<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">Parent Dashboard</h2>
    </x-slot>

    <div class="space-y-6 p-6">
        @if (session('status'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-500/40 dark:bg-emerald-500/10 dark:text-emerald-200">
                {{ session('status') }}
            </div>
        @endif

        @if ($children->isEmpty())
            <div class="rounded-2xl border border-gray-100 bg-white p-6 text-sm text-gray-600 shadow-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                You do not have any linked students yet. Please contact the administrator for assistance.
            </div>
        @else
            <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Guardian</p>
                        <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $guardian->name }}</h1>
                    </div>
                    <form method="GET" action="{{ route('parent.dashboard') }}" class="flex items-center gap-3">
                        <label for="student_user_id" class="text-sm text-gray-500 dark:text-gray-400">Viewing student</label>
                        <select id="student_user_id" name="student_user_id" data-behavior="student-search" class="rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100" onchange="this.form.submit()">
                            @foreach ($children as $child)
                                <option value="{{ $child->id }}" @selected(optional($selectedChild)->id === $child->id)>{{ $child->name }}</option>
                            @endforeach
                        </select>
                    </form>
                </div>
            </div>

            @if ($selectedStudent)
                <div class="rounded-2xl border border-gray-100 bg-white px-6 py-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Student</p>
                            <h2 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $selectedStudent->name }}</h2>
                            @if ($classGroup)
                                <p class="text-sm text-gray-500 dark:text-gray-400">Class Group: {{ $classGroup->name }}</p>
                            @else
                                <p class="text-sm text-gray-500 dark:text-gray-400">Class group not assigned yet.</p>
                            @endif
                        </div>
                        <div class="rounded-xl bg-indigo-50 px-4 py-3 text-right text-sm text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-200">
                            <p>Attendance Rate</p>
                            <p class="text-2xl font-semibold">{{ $attendanceSummary['percentage'] !== null ? $attendanceSummary['percentage'].'%' : 'N/A' }}</p>
                        </div>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Total Sessions</p>
                        <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $attendanceSummary['total'] }}</p>
                    </div>
                    <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Present</p>
                        <p class="mt-2 text-2xl font-semibold text-green-600 dark:text-green-400">{{ $attendanceSummary['present'] }}</p>
                    </div>
                    <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Absent</p>
                        <p class="mt-2 text-2xl font-semibold text-red-600 dark:text-red-400">{{ $attendanceSummary['absent'] }}</p>
                    </div>
                    <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Last Attendance</p>
                        <p class="mt-2 text-lg font-medium text-gray-900 dark:text-gray-100">{{ optional(optional($recentAttendance->first())->date)->format('M d, Y') ?? 'No records' }}</p>
                    </div>
                </div>

                <div class="grid gap-6 xl:grid-cols-2">
                    <section class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Weekly Timetable</h3>
                            @if ($classGroup)
                                <span class="text-sm text-gray-500 dark:text-gray-400">{{ $classGroup->name }}</span>
                            @endif
                        </div>

                        @if ($timetableByDay->isEmpty())
                            <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">Timetable details will appear here once scheduled.</p>
                        @else
                            <div class="mt-4 space-y-4">
                                @foreach ($timetableByDay as $day => $entries)
                                    <div>
                                        <h4 class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ $day }}</h4>
                                        <ul class="mt-2 space-y-2">
                                            @foreach ($entries as $entry)
                                                <li class="rounded-lg border border-gray-100 px-3 py-2 text-sm shadow-sm dark:border-gray-700 dark:bg-gray-900/40">
                                                    <div class="flex items-center justify-between">
                                                        <div>
                                                            <p class="font-medium text-gray-900 dark:text-gray-100">{{ optional($entry->subject)->name ?? 'Subject TBD' }}</p>
                                                            <p class="text-xs text-gray-500 dark:text-gray-400">Teacher: {{ optional($entry->teacher)->name ?? 'TBD' }}</p>
                                                        </div>
                                                        <div class="text-xs font-medium text-gray-500 dark:text-gray-400">
                                                            {{ optional($entry->start_time)->format('H:i') ?? '--:--' }} - {{ optional($entry->end_time)->format('H:i') ?? '--:--' }}
                                                        </div>
                                                    </div>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </section>

                    <section class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Recent Attendance</h3>
                            <span class="text-sm text-gray-500 dark:text-gray-400">Last 10 sessions</span>
                        </div>

                        @if ($recentAttendance->isEmpty())
                            <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">Attendance entries will appear here once available.</p>
                        @else
                            <div class="mt-4 overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700">
                                <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
                                    <thead class="bg-gray-50 dark:bg-gray-900/60">
                                        <tr>
                                            <th scope="col" class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Date</th>
                                            <th scope="col" class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                        @foreach ($recentAttendance as $record)
                                            <tr class="bg-white dark:bg-gray-900/40">
                                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ optional($record->date)->format('M d, Y') }}</td>
                                                <td class="px-4 py-3">
                                                    @if ($record->present)
                                                        <span class="inline-flex items-center rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700 dark:bg-green-900/50 dark:text-green-200">Present</span>
                                                    @else
                                                        <span class="inline-flex items-center rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-700 dark:bg-red-900/50 dark:text-red-200">Absent</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </section>
                </div>

                <section class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Fee Records</h3>
                        <span class="text-sm text-gray-500 dark:text-gray-400">Overview & receipts</span>
                    </div>

                    @if ($feeRecords->isEmpty())
                        <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">No fee records are available for this student yet.</p>
                    @else
                        <div class="mt-4 overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700">
                            <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-900/60">
                                    <tr>
                                        <th scope="col" class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Fee</th>
                                        <th scope="col" class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Status</th>
                                        <th scope="col" class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Outstanding</th>
                                        <th scope="col" class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Next Installment</th>
                                        <th scope="col" class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Receipts</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach ($feeRecords as $record)
                                        @php
                                            $nextInstallment = $record->installments->first(fn ($installment) => !$installment->is_settled);
                                            $latestPaidInstallment = $record->installments
                                                ->filter(fn ($installment) => $installment->is_settled)
                                                ->sortByDesc(fn ($installment) => $installment->paid_at ?? $installment->updated_at)
                                                ->first();
                                        @endphp
                                        <tr class="bg-white dark:bg-gray-900/40">
                                            <td class="px-4 py-3">
                                                <div class="font-medium text-gray-900 dark:text-gray-100">{{ optional($record->feeStructure)->name ?? 'Fee Record #'.$record->id }}</div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400">Base total: {{ number_format((float) $record->total_amount, 2) }} • Net: {{ number_format((float) $record->net_amount, 2) }}</div>
                                            </td>
                                            <td class="px-4 py-3">
                                                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold
                                                    @class([
                                                        'bg-green-100 text-green-700 dark:bg-green-900/50 dark:text-green-200' => $record->status === 'paid',
                                                        'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/50 dark:text-yellow-200' => $record->status === 'partial',
                                                        'bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-200' => $record->status === 'overdue',
                                                        'bg-gray-100 text-gray-700 dark:bg-gray-900/50 dark:text-gray-200' => !in_array($record->status, ['paid','partial','overdue']),
                                                    ])
                                                ">
                                                    {{ ucfirst($record->status) }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ number_format((float) $record->net_outstanding, 2) }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">
                                                @if ($nextInstallment)
                                                    <div>Due {{ optional($nextInstallment->due_date)->format('M d, Y') ?? 'TBD' }}</div>
                                                    <div class="text-xs text-gray-500 dark:text-gray-400">Amount: {{ number_format((float) $nextInstallment->amount, 2) }}</div>
                                                @else
                                                    <span class="text-xs text-gray-500 dark:text-gray-400">All installments settled</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3">
                                                <div class="flex flex-wrap gap-2">
                                                    @if ($latestPaidInstallment)
                                                        <a href="{{ route('parent.installments.receipt', $latestPaidInstallment) }}" class="inline-flex items-center rounded-lg border border-indigo-200 px-3 py-1 text-xs font-semibold text-indigo-700 transition hover:bg-indigo-50 dark:border-indigo-700 dark:text-indigo-200 dark:hover:bg-indigo-900/40">View</a>
                                                        <a href="{{ route('parent.installments.receipt', [$latestPaidInstallment, 'download' => 1]) }}" class="inline-flex items-center rounded-lg border border-gray-200 px-3 py-1 text-xs font-semibold text-gray-600 transition hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-900/40">Download</a>
                                                    @else
                                                        <span class="text-xs text-gray-500 dark:text-gray-400">No receipts yet</span>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </section>


<section class="mt-6 rounded-xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Recent Announcements</h3>
    <p class="text-sm text-gray-500 dark:text-gray-400">Updates shared with your child or their class.</p>
    <div class="mt-4 space-y-3">
        @forelse($announcements as $announcement)
            <article class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-900/50">
                <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $announcement->title }}</h4>
                <p class="text-xs text-gray-500 dark:text-gray-400">Published {{ optional($announcement->published_at)->format('d M Y') ?? $announcement->created_at->format('d M Y') }}</p>
                <p class="mt-2 text-sm text-gray-700 dark:text-gray-200">{{ \Illuminate\Support\Str::limit($announcement->body, 180) }}</p>
            </article>
        @empty
            <p class="rounded-lg border border-dashed border-gray-300 p-4 text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">No recent announcements for this student.</p>
        @endforelse
    </div>
</section>

<section class="mt-6 rounded-xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
    <div class="flex flex-col gap-4 lg:flex-row">
        <div class="flex-1">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Student Documents</h3>
                <span class="text-sm text-gray-500 dark:text-gray-400">Shared files</span>
            </div>

            @if ($documents->isEmpty())
                <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">No documents have been uploaded yet.</p>
            @else
                <div class="mt-4 space-y-3">
                    @foreach ($documents as $media)
                        @php
                            $meta = $media->custom_properties ?? [];
                            $label = $meta['label'] ?? $meta['type_label'] ?? pathinfo($media->file_name, PATHINFO_FILENAME);
                            $description = $meta['description'] ?? null;
                            $uploader = $meta['uploaded_by_name'] ?? 'School';
                            $when = $media->created_at->format('d M Y, h:i A');
                        @endphp
                        <article class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-900/50">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $label }}</h4>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Uploaded by {{ $uploader }} on {{ $when }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Size: {{ \Illuminate\Support\Number::fileSize($media->size) }}</p>
                                    @if ($description)
                                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">{{ $description }}</p>
                                    @endif
                                </div>
                                <a href="{{ route('parent.students.documents.download', [$selectedStudent, $media]) }}"
                                   class="rounded border border-gray-300 px-3 py-1 text-xs font-medium text-gray-700 transition hover:bg-gray-100 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800">
                                    Download
                                </a>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>

        @if ($selectedStudent)
            <div class="lg:w-80 lg:border-l lg:border-gray-200 lg:pl-5 dark:lg:border-gray-700">
                <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Upload a document</h4>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Supported files up to 20&nbsp;MB.</p>
                <form method="POST" action="{{ route('parent.students.documents.store', $selectedStudent) }}" enctype="multipart/form-data" class="mt-3 space-y-3">
                    @csrf
                    <div>
                        <label for="parent_document_type" class="block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Document type</label>
                        <select id="parent_document_type" name="document_type" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
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
                        <label for="parent_document_label" class="block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Custom label</label>
                        <input id="parent_document_label" name="document_label" type="text"
                               value="{{ old('document_label') }}"
                               class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                        @error('document_label')
                            <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="parent_document_note" class="block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Notes (optional)</label>
                        <textarea id="parent_document_note" name="description" rows="2" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">{{ old('description') }}</textarea>
                        @error('description')
                            <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="parent_document_files" class="block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Choose file(s)</label>
                        <input id="parent_document_files" name="documents[]" type="file" multiple required
                               class="mt-1 block w-full text-sm text-gray-700 file:mr-4 file:rounded-lg file:border-0 file:bg-indigo-600 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-indigo-700 dark:text-gray-200">
                        @error('documents')
                            <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                        @enderror
                        @error('documents.*')
                            <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700">Upload</button>
                    </div>
                </form>
            </div>
        @endif
    </div>
</section>

<section class="mt-6 rounded-xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Share Feedback</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Have suggestions or concerns? Let the management team know.</p>
                    <form method="POST" action="{{ route('parent.feedback.store') }}" class="mt-4 space-y-3">
                        @csrf
                        <div class="grid gap-3 md:grid-cols-2">
                            <div>
                                <label class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Category</label>
                                <input type="text" name="category" value="{{ old('category') }}" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100" placeholder="e.g. Academics, Facilities">
                                @error('category')
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Subject<span class="text-red-500">*</span></label>
                                <input type="text" name="subject" value="{{ old('subject') }}" required class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                                @error('subject')
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <div>
                            <label class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Message<span class="text-red-500">*</span></label>
                            <textarea name="message" rows="4" required class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">{{ old('message') }}</textarea>
                            @error('message')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700">Submit feedback</button>
                        </div>
                    </form>
                </section>
            @else
                <div class="rounded-2xl border border-gray-100 bg-white p-6 text-sm text-gray-600 shadow-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                    The selected student does not have a profile yet. Please contact the administrator.
                </div>
            @endif
        @endif
    </div>
</x-app-layout>
