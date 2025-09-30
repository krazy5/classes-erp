@extends('layouts.admin')

@section('title', 'Data Tools | ' . config('app.name'))
@section('header', 'Data Backup & Restore')

@section('content')
    <div class="space-y-6">
        @if(session('dataTransferImport'))
            @php($importSummary = session('dataTransferImport'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800 dark:border-emerald-500/40 dark:bg-emerald-500/10 dark:text-emerald-200">
                <p class="font-semibold">Import completed for {{ ucfirst(str_replace('_', ' ', $importSummary['dataset'])) }}.</p>
                <p class="mt-1 text-xs">Imported: {{ $importSummary['summary']['imported'] ?? 0 }}, Updated: {{ $importSummary['summary']['updated'] ?? 0 }}, Skipped: {{ $importSummary['summary']['skipped'] ?? 0 }}.</p>
            </div>
        @endif

        <div class="grid gap-6 lg:grid-cols-3">
            <section class="lg:col-span-2 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Export datasets</h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Download a point-in-time snapshot as JSON (for backups) or Excel (for audits and quick reviews).</p>

                <div class="mt-6 space-y-4">
                    @foreach($datasets as $key => $dataset)
                        <div class="flex flex-col gap-4 rounded-xl border border-slate-200 p-4 dark:border-slate-700">
                            <div>
                                <h3 class="text-base font-semibold text-slate-800 dark:text-slate-100">{{ $dataset['label'] }}</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400">{{ $dataset['description'] }}</p>
                            </div>
                            <div class="flex flex-wrap gap-3">
                                <form method="POST" action="{{ route('admin.data-transfer.export') }}" class="inline">
                                    @csrf
                                    <input type="hidden" name="dataset" value="{{ $key }}">
                                    <input type="hidden" name="format" value="json">
                                    <button type="submit" class="inline-flex items-center rounded-md border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-800">
                                        Export JSON
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.data-transfer.export') }}" class="inline">
                                    @csrf
                                    <input type="hidden" name="dataset" value="{{ $key }}">
                                    <input type="hidden" name="format" value="xlsx">
                                    <button type="submit" class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
                                        Export Excel
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Import data</h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Upload a previously exported JSON or Excel file to restore or bulk update the selected dataset.</p>

                <form method="POST" action="{{ route('admin.data-transfer.import') }}" enctype="multipart/form-data" class="mt-6 space-y-4">
                    @csrf

                    <div>
                        <label for="dataset" class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-300">Dataset</label>
                        <select id="dataset" name="dataset" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                            @foreach($datasets as $key => $dataset)
                                <option value="{{ $key }}" @selected(old('dataset') === $key)>{{ $dataset['label'] }}</option>
                            @endforeach
                        </select>
                        @error('dataset')
                            <p class="mt-1 text-xs text-rose-600 dark:text-rose-300">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="file" class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-300">Data file</label>
                        <input id="file" name="file" type="file" accept=".json,.xlsx,.xls" class="mt-1 block w-full text-sm text-slate-600 file:mr-3 file:rounded-md file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-sm file:font-medium hover:file:bg-slate-200 dark:text-slate-100 dark:file:bg-slate-800 dark:hover:file:bg-slate-700" required>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Supported formats: JSON, XLSX. Keep headers unchanged for Excel imports.</p>
                        @error('file')
                            <p class="mt-1 text-xs text-rose-600 dark:text-rose-300">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit" class="w-full rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-500">Import</button>
                </form>
            </section>
        </div>
    </div>
@endsection
