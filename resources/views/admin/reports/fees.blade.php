@extends('layouts.admin')

@section('title', 'Fee Reports | ' . config('app.name'))
@section('header', 'Fee Collection Reports')

@section('content')
    <div class="space-y-8">
        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-slate-900 dark:text-slate-100">Fee collection performance</h2>
                    <p class="text-sm text-slate-600 dark:text-slate-400">Track payments, outstanding balances, and settlement trends by class, batch, or student.</p>
                </div>
                <div class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Period: {{ $summary['range'][0] }} → {{ $summary['range'][1] }}</div>
            </div>

            <div class="mt-6 grid gap-4 md:grid-cols-4">
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-emerald-700 dark:border-emerald-500/40 dark:bg-emerald-500/10 dark:text-emerald-200">
                    <p class="text-xs uppercase tracking-wide">Segments</p>
                    <p class="mt-2 text-2xl font-semibold">{{ number_format($summary['segments']) }}</p>
                </div>
                <div class="rounded-xl border border-indigo-200 bg-indigo-50 p-4 text-indigo-700 dark:border-indigo-500/40 dark:bg-indigo-500/10 dark:text-indigo-200">
                    <p class="text-xs uppercase tracking-wide">Collection Rate</p>
                    <p class="mt-2 text-2xl font-semibold">{{ $summary['average'] }}%</p>
                </div>
                <div class="rounded-xl border border-blue-200 bg-blue-50 p-4 text-blue-700 dark:border-blue-500/40 dark:bg-blue-500/10 dark:text-blue-200">
                    <p class="text-xs uppercase tracking-wide">Collected (period)</p>
                    <p class="mt-2 text-2xl font-semibold">{{ number_format($summary['collected_period'], 2) }}</p>
                </div>
                <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-rose-700 dark:border-rose-500/40 dark:bg-rose-500/10 dark:text-rose-200">
                    <p class="text-xs uppercase tracking-wide">Outstanding (to date)</p>
                    <p class="mt-2 text-2xl font-semibold">{{ number_format($summary['outstanding'], 2) }}</p>
                </div>
            </div>

            <p class="mt-4 text-xs text-slate-500 dark:text-slate-400">Total due this period: {{ number_format($summary['due'], 2) }}</p>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900" x-data="{ type: '{{ $reportType }}' }">
            <form method="GET" class="grid gap-4 md:grid-cols-4 lg:grid-cols-6">
                <div class="md:col-span-2">
                    <label for="from" class="block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">From</label>
                    <input id="from" name="from" type="date" value="{{ $dateFrom }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                </div>
                <div class="md:col-span-2">
                    <label for="to" class="block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">To</label>
                    <input id="to" name="to" type="date" value="{{ $dateTo }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                </div>
                <div>
                    <label for="type" class="block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Report Type</label>
                    <select id="type" name="type" x-model="type" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                        <option value="class" @selected($reportType === 'class')>Class-wise</option>
                        <option value="batch" @selected($reportType === 'batch')>Batch-wise</option>
                        <option value="student" @selected($reportType === 'student')>Student-wise</option>
                    </select>
                </div>
                <template x-if="type !== 'student'">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400"><span x-text="type === 'batch' ? 'Batch' : 'Class'"></span></label>
                        <select name="class_group_id" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                            <option value="">All</option>
                            @foreach($classGroups as $id => $name)
                                <option value="{{ $id }}" @selected((string) $selectedClassGroup === (string) $id)>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                </template>
                <template x-if="type === 'student'">
                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Student</label>
                        <select name="student_id" data-behavior="student-search" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                            <option value="">All</option>
                            @foreach($students as $id => $name)
                                <option value="{{ $id }}" @selected((string) $selectedStudent === (string) $id)>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                </template>
                <div class="md:col-span-2 lg:col-span-1 flex items-end">
                    <button type="submit" class="inline-flex w-full items-center justify-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700">Apply Filters</button>
                </div>
            </form>

            <div class="mt-8 grid gap-6 lg:grid-cols-5">
                <div class="lg:col-span-2">
                    <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-6 shadow-sm dark:border-emerald-500/40 dark:bg-emerald-500/10">
                        <h3 class="text-lg font-semibold text-emerald-900 dark:text-emerald-100">Collection rate visualisation</h3>
                        <p class="mt-2 text-sm text-emerald-700 dark:text-emerald-200">How effectively dues were settled for the selected window.</p>

                        @if($rows->isEmpty())
                            <div class="mt-8 text-sm text-emerald-700 dark:text-emerald-200">No data available for the selected filters. Adjust the range to view collections.</div>
                        @else
                            <div class="mt-6 h-60">
                                <canvas id="fees-collection-chart"></canvas>
                            </div>
                            <p class="mt-4 text-xs text-emerald-700/80 dark:text-emerald-200/80">Click legend entries to toggle segments and focus on specific cohorts.</p>
                        @endif
                    </div>
                </div>

                <div class="lg:col-span-3">
                    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <div class="border-b border-slate-200 px-6 py-4 dark:border-slate-800">
                            <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Fee collection details</h3>
                            <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">Compare amounts due, collected, and outstanding for each segment.</p>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                                <thead class="bg-slate-50 text-left uppercase tracking-wide text-slate-500 dark:bg-slate-800 dark:text-slate-400">
                                    <tr>
                                        <th class="px-4 py-2">Segment</th>
                                        <th class="px-4 py-2">Segment Type / Class</th>
                                        <th class="px-4 py-2 text-right">Collected (period)</th>
                                        <th class="px-4 py-2 text-right">Settled</th>
                                        <th class="px-4 py-2 text-right">Outstanding</th>
                                        <th class="px-4 py-2 text-right">Due</th>
                                        <th class="px-4 py-2 text-right">Collection %</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                                    @forelse($rows as $row)
                                        <tr class="text-slate-700 dark:text-slate-200">
                                            <td class="px-4 py-2 font-medium text-slate-900 dark:text-slate-100">{{ $row['label'] }}</td>
                                            <td class="px-4 py-2">
                                                @if(($row['segment'] ?? null) === 'Student')
                                                    {{ $row['class_name'] ?? '—' }}
                                                @else
                                                    {{ $row['segment'] ?? '—' }}
                                                @endif
                                            </td>
                                            <td class="px-4 py-2 text-right">{{ number_format($row['collected_period'], 2) }}</td>
                                            <td class="px-4 py-2 text-right">{{ number_format($row['collected_total'], 2) }}</td>
                                            <td class="px-4 py-2 text-right">{{ number_format($row['outstanding'], 2) }}</td>
                                            <td class="px-4 py-2 text-right">{{ number_format($row['due'], 2) }}</td>
                                            <td class="px-4 py-2 text-right">{{ number_format($row['rate'], 1) }}%</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="px-4 py-6 text-center text-sm text-slate-500 dark:text-slate-400">No fee collection data to display for the chosen filters.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
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
                const canvas = document.getElementById('fees-collection-chart');
                if (!canvas || typeof Chart === 'undefined') {
                    console.warn('Chart.js failed to load or canvas missing.');
                    return;
                }

                const chartConfig = @json($chart);
                const datasetValues = chartConfig.data.map(value => Number(value));
                const totalAmount = datasetValues.reduce((sum, value) => sum + value, 0);
                const paidAmount = datasetValues[0] ?? 0;
                const outstandingAmount = datasetValues[1] ?? 0;
                const paidShare = totalAmount > 0 ? Math.round((paidAmount / totalAmount) * 100) : 0;
                const formatCurrency = value => new Intl.NumberFormat('en-IN', {
                    style: 'currency',
                    currency: 'INR',
                    maximumFractionDigits: value >= 100000 ? 0 : 2,
                }).format(value);

                Chart.register({
                    id: 'collectionCenterLabel',
                    afterDraw(chart, args, opts) {
                        const meta = chart.getDatasetMeta(0).data[0];
                        if (!meta) {
                            return;
                        }

                        const { ctx } = chart;
                        const { x: targetX, y: targetY } = meta;

                        ctx.save();
                        ctx.font = '600 18px "Inter", sans-serif';
                        ctx.fillStyle = opts.textColor || '#0f172a';
                        ctx.textAlign = 'center';
                        ctx.textBaseline = 'middle';
                        ctx.fillText(formatCurrency(paidAmount), targetX, targetY - 8);
                        ctx.font = '500 12px "Inter", sans-serif';
                        ctx.fillStyle = opts.subTextColor || '#2563eb';
                        ctx.fillText(`${paidShare}% collected`, targetX, targetY + 12);
                        ctx.restore();
                    }
                });

                const chart = new Chart(canvas, {
                    type: 'doughnut',
                    data: {
                        labels: chartConfig.labels,
                        datasets: [{
                            label: 'Amount',
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
                        cutout: '58%',
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    usePointStyle: true,
                                    pointStyle: 'circle',
                                    padding: 12,
                                    font: {
                                        family: 'Inter, sans-serif',
                                        size: 12,
                                        weight: '500'
                                    }
                                },
                                generateLabels(chartInstance) {
                                    const data = chartInstance.data;
                                    if (!data.labels.length) {
                                        return [];
                                    }
                                    return data.labels.map((label, i) => {
                                        const value = datasetValues[i] ?? 0;
                                        const percentage = totalAmount > 0 ? ((value / totalAmount) * 100).toFixed(1) : 0;
                                        return {
                                            text: `${label}: ${formatCurrency(value)} (${percentage}%)`,
                                            fillStyle: data.datasets[0].backgroundColor[i],
                                            strokeStyle: '#ffffff',
                                            lineWidth: 1,
                                            index: i,
                                        };
                                    });
                                }
                            },
                            tooltip: {
                                backgroundColor: 'rgba(15, 23, 42, 0.9)',
                                titleFont: { family: 'Inter, sans-serif', size: 12, weight: '600' },
                                bodyFont: { family: 'Inter, sans-serif', size: 12 },
                                callbacks: {
                                    label(context) {
                                        const value = context.parsed ?? 0;
                                        const share = totalAmount > 0 ? ((value / totalAmount) * 100).toFixed(1) : 0;
                                        return `${context.label}: ${formatCurrency(value)} (${share}%)`;
                                    }
                                }
                            },
                            collectionCenterLabel: {
                                textColor: '#0f172a',
                                subTextColor: '#2563eb'
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
