@extends('layouts.admin')

@section('title', 'Attendance Reports | ' . config('app.name'))
@section('header', 'Attendance Reports')

@section('content')
    <div class="space-y-8">
        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-slate-900 dark:text-slate-100">Attendance performance</h2>
                    <p class="text-sm text-slate-600 dark:text-slate-400">Analyse attendance trends by class, batch, or individual student across any date range.</p>
                </div>
                <div class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Period: {{ $summary['range'][0] }} → {{ $summary['range'][1] }}</div>
            </div>

            <div class="mt-6 grid gap-4 md:grid-cols-4">
                <div class="rounded-xl border border-indigo-200 bg-indigo-50 p-4 text-indigo-700 dark:border-indigo-500/40 dark:bg-indigo-500/10 dark:text-indigo-200">
                    <p class="text-xs uppercase tracking-wide">Segments</p>
                    <p class="mt-2 text-2xl font-semibold">{{ number_format($summary['segments']) }}</p>
                </div>
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-emerald-700 dark:border-emerald-500/40 dark:bg-emerald-500/10 dark:text-emerald-200">
                    <p class="text-xs uppercase tracking-wide">Avg. Attendance</p>
                    <p class="mt-2 text-2xl font-semibold">{{ $summary['average'] }}%</p>
                </div>
                <div class="rounded-xl border border-blue-200 bg-blue-50 p-4 text-blue-700 dark:border-blue-500/40 dark:bg-blue-500/10 dark:text-blue-200">
                    <p class="text-xs uppercase tracking-wide">Marked Present</p>
                    <p class="mt-2 text-2xl font-semibold">{{ number_format($summary['present']) }}</p>
                </div>
                <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-rose-700 dark:border-rose-500/40 dark:bg-rose-500/10 dark:text-rose-200">
                    <p class="text-xs uppercase tracking-wide">Marked Absent</p>
                    <p class="mt-2 text-2xl font-semibold">{{ number_format($summary['absent']) }}</p>
                </div>
            </div>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900" x-data="{ type: '{{ $reportType }}' }">
            <form method="GET" class="grid gap-4 md:grid-cols-4 lg:grid-cols-6">
                <div class="md:col-span-2">
                    <label for="from" class="block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">From</label>
                    <input id="from" name="from" type="date" value="{{ $dateFrom }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                </div>
                <div class="md:col-span-2">
                    <label for="to" class="block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">To</label>
                    <input id="to" name="to" type="date" value="{{ $dateTo }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                </div>
                <div>
                    <label for="type" class="block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Report Type</label>
                    <select id="type" name="type" x-model="type" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                        <option value="class" @selected($reportType === 'class')>Class-wise</option>
                        <option value="batch" @selected($reportType === 'batch')>Batch-wise</option>
                        <option value="student" @selected($reportType === 'student')>Student-wise</option>
                    </select>
                </div>
                <template x-if="type !== 'student'">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400"><span x-text="type === 'batch' ? 'Batch' : 'Class'"></span></label>
                        <select name="class_group_id" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                            <option value="">All</option>
                            @foreach($classGroups as $id => $name)
                                <option value="{{ $id }}" @selected((int) $selectedClassGroup === (int) $id)>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                </template>
                <template x-if="type === 'student'">
                    <div class="md:col-span-2 lg:col-span-2 grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Class</label>
                            <select name="class_group_id" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                                <option value="">All</option>
                                @foreach($classGroups as $id => $name)
                                    <option value="{{ $id }}" @selected((int) $selectedClassGroup === (int) $id)>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Student</label>
                            <select name="student_id" data-behavior="student-search" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                                <option value="">All students</option>
                                @foreach($students as $id => $name)
                                    <option value="{{ $id }}" @selected((int) $selectedStudent === (int) $id)>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </template>
                <div class="flex items-end gap-3">
                    <button type="submit" class="inline-flex flex-1 items-center justify-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700">Apply filters</button>
                    <a href="{{ route('admin.reports.attendance') }}" class="inline-flex items-center justify-center rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-100 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-800">Reset</a>
                </div>
            </form>
        </section>

        <section class="space-y-6">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Attendance visualisation</h3>
                        <p class="text-sm text-slate-600 dark:text-slate-400">Interactive doughnut chart—tap a legend item to hide or compare segments.</p>
                    </div>
                </div>
                <div class="mt-6">
                    @if($rows->isEmpty())
                        <div class="rounded-xl border border-dashed border-slate-300 px-4 py-10 text-center text-sm text-slate-500 dark:border-slate-700 dark:text-slate-400">
                            No attendance records found for the selected filters. Try expanding the date range or adjusting the report type.
                        </div>
                    @else
                        <div class="mx-auto max-w-4xl">
                            <canvas id="attendance-chart" class="h-96 w-full"></canvas>
                        </div>
                    @endif
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Detailed breakdown</h3>
                    <div class="text-xs text-slate-500 dark:text-slate-400">Showing {{ $rows->count() }} {{ $reportType === 'student' ? 'students' : 'segments' }}</div>
                </div>
                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
                        <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500 dark:bg-slate-800 dark:text-slate-400">
                            <tr>
                                <th class="px-4 py-2 text-left">{{ $reportType === 'student' ? 'Student' : ($reportType === 'batch' ? 'Batch' : 'Class') }}</th>
                                <th class="px-4 py-2 text-left">Class</th>
                                <th class="px-4 py-2 text-right">Present</th>
                                <th class="px-4 py-2 text-right">Absent</th>
                                <th class="px-4 py-2 text-right">Sessions</th>
                                <th class="px-4 py-2 text-right">Attendance %</th>
                                <th class="px-4 py-2 text-right">Share</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                            @forelse($rows as $row)
                            @php
                                $shareLabel = match(true) {
                                    $reportType === 'student' => 'Student',
                                    $reportType === 'batch' => 'Batch',
                                    default => 'Class',
                                };
                                $shareText = implode("\n", [
                                    $shareLabel . ': ' . ($row['label'] ?? 'N/A'),
                                    'Class: ' . ($row['class_name'] ?? 'N/A'),
                                    'Present: ' . number_format($row['present'] ?? 0),
                                    'Absent: ' . number_format($row['absent'] ?? 0),
                                    'Sessions: ' . number_format($row['total'] ?? 0),
                                    'Attendance: ' . number_format($row['rate'] ?? 0, 1) . '%',
                                    'Period: ' . ($summary['range'][0] ?? 'N/A') . ' to ' . ($summary['range'][1] ?? 'N/A'),
                                ]);
                            @endphp
                                <tr class="text-slate-700 dark:text-slate-200">
                                    <td class="px-4 py-2 font-medium text-slate-900 dark:text-slate-100">{{ $row['label'] }}</td>
                                    <td class="px-4 py-2">{{ $row['class_name'] ?? '—' }}</td>
                                    <td class="px-4 py-2 text-right">{{ number_format($row['present']) }}</td>
                                    <td class="px-4 py-2 text-right">{{ number_format($row['absent']) }}</td>
                                    <td class="px-4 py-2 text-right">{{ number_format($row['total']) }}</td>
                                    <td class="px-4 py-2 text-right">{{ number_format($row['rate'], 1) }}%</td>
                                    <td class="px-4 py-2 text-right">
                                        <button type="button" class="inline-flex items-center gap-1 rounded-lg border border-slate-300 px-2 py-1 text-xs font-medium text-slate-600 transition hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800" data-share="{{ $shareText }}" data-share-title="Attendance Summary">
                                            @svg('heroicon-s-share', 'h-3 w-3')
                                            <span>Share</span>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-6 text-center text-sm text-slate-500 dark:text-slate-400">No records to display.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    @if($rows->isNotEmpty())
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const canvas = document.getElementById('attendance-chart');
                if (!canvas || typeof Chart === 'undefined') {
                    console.warn('Chart.js failed to load or canvas missing.');
                    return;
                }

                const chartConfig = @json($chart);
                const averages = Number({{ $summary['average'] }});
                const datasetValues = chartConfig.data.map(value => Number(value));

                Chart.register({
                    id: 'centerLabel',
                    afterDraw(chart, args, opts) {
                        const { ctx, chartArea: { width, height } } = chart;
                        ctx.save();
                        ctx.font = '600 20px "Inter", sans-serif';
                        ctx.fillStyle = opts.textColor || '#1e293b';
                        ctx.textAlign = 'center';
                        ctx.textBaseline = 'middle';
                        ctx.fillText(`${averages}%`, chart.getDatasetMeta(0).data[0]?.x || width / 2, (chart.getDatasetMeta(0).data[0]?.y || height / 2) - 6);
                        ctx.font = '500 12px "Inter", sans-serif';
                        ctx.fillStyle = opts.subTextColor || '#64748b';
                        ctx.fillText('average', chart.getDatasetMeta(0).data[0]?.x || width / 2, (chart.getDatasetMeta(0).data[0]?.y || height / 2) + 12);
                        ctx.restore();
                    }
                });

                const chart = new Chart(canvas, {
                    type: 'doughnut',
                    data: {
                        labels: chartConfig.labels,
                        datasets: [{
                            label: 'Attendance %',
                            data: datasetValues,
                            backgroundColor: chartConfig.colors,
                            hoverOffset: 18,
                            borderWidth: 2,
                            borderColor: '#ffffff',
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '55%',
                        plugins: {
                            legend: {
                                position: 'right',
                                align: 'center',
                                labels: {
                                    usePointStyle: true,
                                    pointStyle: 'circle',
                                    padding: 16,
                                    font: {
                                        family: 'Inter, sans-serif',
                                        size: 12,
                                        weight: '500'
                                    }
                                }
                            },
                            tooltip: {
                                backgroundColor: 'rgba(15, 23, 42, 0.9)',
                                titleFont: { family: 'Inter, sans-serif', size: 12, weight: '600' },
                                bodyFont: { family: 'Inter, sans-serif', size: 12 },
                                callbacks: {
                                    label(context) {
                                        const value = context.parsed;
                                        return `${context.label}: ${value.toFixed(1)}%`;
                                    }
                                }
                            },
                            centerLabel: {
                                textColor: '#111827',
                                subTextColor: '#475569'
                            }
                        },
                        animation: {
                            animateRotate: true,
                            animateScale: true,
                        }
                    }
                });

                canvas.addEventListener('click', event => {
                    const segment = chart.getElementsAtEventForMode(event, 'nearest', { intersect: true }, false);
                    if (!segment.length) {
                        return;
                    }
                    const index = segment[0].index;
                    chart.toggleDataVisibility(index);
                    chart.update();
                });
            });
        </script>
    @endif
@endpush
