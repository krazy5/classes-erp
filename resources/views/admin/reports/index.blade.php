@extends('layouts.admin')

@section('title', 'Reports Hub | ' . config('app.name'))
@section('header', 'Reports Hub')

@section('content')
    <div class="max-w-5xl space-y-8">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h2 class="text-2xl font-semibold text-slate-900 dark:text-slate-100">Insights at a glance</h2>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">Select a report area to dive into interactive dashboards with advanced filters, engaging visuals, and export-ready tables.</p>
        </div>

        <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-4">
            <article class="group rounded-2xl border border-indigo-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-md dark:border-indigo-500/40 dark:bg-slate-900">
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-indigo-500/10 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.5l6.318-6.318a4.5 4.5 0 016.364 0L21 12m-6-2.25l-1.5 1.5"/></svg>
                </div>
                <h3 class="mt-4 text-lg font-semibold text-slate-900 dark:text-slate-100">Attendance Reports</h3>
                <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">View student and batch attendance trends by date range.</p>
                <a href="{{ route('admin.reports.attendance') }}"
                   class="mt-6 inline-flex items-center gap-2 rounded-lg border border-indigo-300 px-4 py-2 text-sm font-semibold text-indigo-600 transition hover:bg-indigo-50 dark:border-indigo-500 dark:text-indigo-200 dark:hover:bg-indigo-500/10">
                    View Attendance Reports
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12l-7.5 7.5M21 12H3"/></svg>
                </a>
            </article>

            <article class="group rounded-2xl border border-emerald-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-md dark:border-emerald-500/40 dark:bg-slate-900">
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-emerald-500/10 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.66 0-3 .9-3 2s1.34 2 3 2 3 .9 3 2-1.34 2-3 2m0-10c1.66 0 3 .9 3 2m-9 4v6a2 2 0 002 2h10a2 2 0 002-2v-6"/></svg>
                </div>
                <h3 class="mt-4 text-lg font-semibold text-slate-900 dark:text-slate-100">Fee Collection Reports</h3>
                <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">Analyze fee payments, outstanding amounts, and revenue.</p>
                <a href="{{ route('admin.reports.fees') }}"
                   class="mt-6 inline-flex items-center gap-2 rounded-lg border border-emerald-300 px-4 py-2 text-sm font-semibold text-emerald-600 transition hover:bg-emerald-50 dark:border-emerald-500 dark:text-emerald-200 dark:hover:bg-emerald-500/10">
                    View Fee Reports
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12l-7.5 7.5M21 12H3"/></svg>
                </a>
            </article>

            <article class="group rounded-2xl border border-orange-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-md dark:border-orange-500/40 dark:bg-slate-900">
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-orange-500/10 text-orange-600 dark:bg-orange-500/20 dark:text-orange-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6.633 10.5c.806 0 1.533-.446 2.031-1.08A9.04 9.04 0 0012 9c1.155 0 2.262.21 3.336.596.498.634 1.225 1.08 2.031 1.08a2.25 2.25 0 100-4.5c-.806 0-1.533.446-2.031 1.08A9.04 9.04 0 0012 6a9.04 9.04 0 00-3.336.596c-.498-.634-1.225-1.08-2.031-1.08a2.25 2.25 0 100 4.5z"/><path stroke-linecap="round" stroke-linejoin="round" d="M3 5.25v10.5A2.25 2.25 0 005.25 18h13.5A2.25 2.25 0 0021 15.75V5.25"/></svg>
                </div>
                <h3 class="mt-4 text-lg font-semibold text-slate-900 dark:text-slate-100">Enquiry Reports</h3>
                <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">Track enquiry sources, conversion rates, and follow-up success.</p>
                <a href="{{ route('admin.reports.enquiries') }}"
                   class="mt-6 inline-flex items-center gap-2 rounded-lg border border-orange-300 px-4 py-2 text-sm font-semibold text-orange-600 transition hover:bg-orange-50 dark:border-orange-500 dark:text-orange-200 dark:hover:bg-orange-500/10">
                    View Enquiry Reports
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12l-7.5 7.5M21 12H3"/></svg>
                </a>
            </article>

            <article class="group rounded-2xl border border-sky-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-md dark:border-sky-500/40 dark:bg-slate-900">
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-sky-500/10 text-sky-600 dark:bg-sky-500/20 dark:text-sky-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c1.657 0 3-.895 3-2s-1.343-2-3-2-3 .895-3 2 1.343 2 3 2zm0 6c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2-1.343-2-3-2zm0-3c4.418 0 8-1.343 8-3s-3.582-3-8-3-8 1.343-8 3 3.582 3 8 3zm0 3c-4.418 0-8 1.343-8 3s3.582 3 8 3 8-1.343 8-3-3.582-3-8-3z"/></svg>
                </div>
                <h3 class="mt-4 text-lg font-semibold text-slate-900 dark:text-slate-100">Finance Report</h3>
                <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">Review revenue, operating expenses, payroll, and net performance.</p>
                <a href="{{ route('admin.reports.finance') }}"
                   class="mt-6 inline-flex items-center gap-2 rounded-lg border border-sky-300 px-4 py-2 text-sm font-semibold text-sky-600 transition hover:bg-sky-50 dark:border-sky-500 dark:text-sky-200 dark:hover:bg-sky-500/10">
                    View Finance Report
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12l-7.5 7.5M21 12H3"/></svg>
                </a>
            </article>
        </div>
    </div>
@endsection