@extends('layouts.admin')

@section('title', 'Finance Report | ' . config('app.name'))
@section('header', 'Finance Overview')

@section('content')
    <div class="space-y-8">
        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-slate-900 dark:text-slate-100">Profit &amp; loss summary</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Track collected revenue alongside operating and payroll spend.</p>
                </div>
                <div class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Period: {{ $summary['range'][0] }} - {{ $summary['range'][1] }}</div>
            </div>

            <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-emerald-700 dark:border-emerald-500/40 dark:bg-emerald-500/10 dark:text-emerald-200">
                    <p class="text-xs uppercase tracking-wide">Collected revenue</p>
                    <p class="mt-2 text-2xl font-semibold">&#8377; {{ number_format($summary['revenue'], 2) }}</p>
                </div>
                <div class="rounded-xl border border-sky-200 bg-sky-50 p-4 text-sky-700 dark:border-sky-500/40 dark:bg-sky-500/10 dark:text-sky-200">
                    <p class="text-xs uppercase tracking-wide">Operational spend</p>
                    <p class="mt-2 text-2xl font-semibold">&#8377; {{ number_format($summary['operational'], 2) }}</p>
                </div>
                <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-amber-700 dark:border-amber-500/40 dark:bg-amber-500/10 dark:text-amber-200">
                    <p class="text-xs uppercase tracking-wide">Payroll</p>
                    <p class="mt-2 text-2xl font-semibold">&#8377; {{ number_format($summary['payroll'], 2) }}</p>
                </div>
                <div class="rounded-xl border {{ $summary['status'] === 'profit' ? 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-500/40 dark:bg-emerald-500/10 dark:text-emerald-200' : 'border-rose-200 bg-rose-50 text-rose-700 dark:border-rose-500/40 dark:bg-rose-500/10 dark:text-rose-200' }} p-4">
                    <p class="text-xs uppercase tracking-wide">Net {{ $summary['status'] === 'profit' ? 'profit' : 'loss' }}</p>
                    <p class="mt-2 text-2xl font-semibold">&#8377; {{ number_format($summary['net'], 2) }}</p>
                </div>
            </div>
        </section>

        <form method="GET" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="grid gap-4 md:grid-cols-4">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">From</label>
                    <input type="date" name="from" value="{{ $dateFrom }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">To</label>
                    <input type="date" name="to" value="{{ $dateTo }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                </div>
                <div class="flex items-end gap-3">
                    <button type="submit" class="w-full rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700">Apply</button>
                    <a href="{{ route('admin.reports.finance') }}" class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">Reset</a>
                </div>
            </div>
        </form>

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Revenue vs expenditure</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">Daily cash movement across the selected period.</p>
            </div>
            <div class="mt-6 h-80">
                <canvas id="finance-trend-chart" class="h-full w-full"></canvas>
            </div>
        </section>

        <div class="grid gap-6 lg:grid-cols-3">
            <section class="lg:col-span-1 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h4 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Revenue sources</h4>
                <ul class="mt-4 space-y-3 text-sm">
                    @forelse($revenueBreakdown as $entry)
                        <li class="flex items-center justify-between">
                            <span class="text-slate-600 dark:text-slate-300">{{ $entry->label }}</span>
                            <span class="font-semibold text-emerald-600 dark:text-emerald-300">&#8377; {{ number_format($entry->total, 2) }}</span>
                        </li>
                    @empty
                        <li class="text-sm text-slate-500 dark:text-slate-400">No revenue records for this period.</li>
                    @endforelse
                </ul>
            </section>

            <section class="lg:col-span-1 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h4 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Operational expenses</h4>
                <ul class="mt-4 space-y-3 text-sm">
                    @forelse($expenseBreakdown as $entry)
                        <li class="flex items-center justify-between">
                            <span class="text-slate-600 dark:text-slate-300">{{ $entry->category }}</span>
                            <span class="font-semibold text-rose-600 dark:text-rose-300">&#8377; {{ number_format($entry->total, 2) }}</span>
                        </li>
                    @empty
                        <li class="text-sm text-slate-500 dark:text-slate-400">No expense records for this period.</li>
                    @endforelse
                </ul>
            </section>

            <section class="lg:col-span-1 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h4 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Payroll obligations</h4>
                <ul class="mt-4 space-y-3 text-sm">
                    @forelse($payrollBreakdown as $entry)
                        <li class="flex items-center justify-between">
                            <span class="text-slate-600 dark:text-slate-300">{{ Str::of($entry->label)->classBasename()->replace('App\\Models\\', '') }}</span>
                            <span class="font-semibold text-amber-600 dark:text-amber-300">&#8377; {{ number_format($entry->total, 2) }}</span>
                        </li>
                    @empty
                        <li class="text-sm text-slate-500 dark:text-slate-400">No payroll records for this period.</li>
                    @endforelse
                </ul>
            </section>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const canvas = document.getElementById('finance-trend-chart');
            if (!canvas || typeof Chart === 'undefined') {
                return;
            }

            const chartConfig = @json($chart);

            const chart = new Chart(canvas, {
                type: 'line',
                data: {
                    labels: chartConfig.labels,
                    datasets: [
                        {
                            label: 'Revenue',
                            data: chartConfig.revenue,
                            borderColor: '#10b981',
                            backgroundColor: 'rgba(16, 185, 129, 0.15)',
                            tension: 0.3,
                            fill: true,
                        },
                        {
                            label: 'Operational',
                            data: chartConfig.operational,
                            borderColor: '#0ea5e9',
                            backgroundColor: 'rgba(14, 165, 233, 0.15)',
                            tension: 0.3,
                            fill: true,
                        },
                        {
                            label: 'Payroll',
                            data: chartConfig.payroll,
                            borderColor: '#f97316',
                            backgroundColor: 'rgba(249, 115, 22, 0.15)',
                            tension: 0.3,
                            fill: true,
                        }
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                font: { family: 'Inter, sans-serif', size: 12 }
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(15, 23, 42, 0.9)',
                            titleFont: { family: 'Inter, sans-serif', size: 12, weight: '600' },
                            bodyFont: { family: 'Inter, sans-serif', size: 12 },
                            callbacks: {
                                label(context) {
                                    const value = context.parsed.y ?? 0;
                                    return `${context.dataset.label}: \u20B9 ${value.toFixed(2)}`;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            ticks: {
                                callback(value) {
                                    return `\u20B9 ${Number(value).toLocaleString('en-IN')}`;
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>
@endpush

