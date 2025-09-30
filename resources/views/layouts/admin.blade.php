<!DOCTYPE html>
<html lang="{{ str_replace('_','-',app()->getLocale()) }}"
      x-data="appLayout()"
      x-init="init()">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>[x-cloak]{ display: none !important; }</style>

    
    <script>
        (() => {
            try {
                const stored = localStorage.getItem('theme');
                const prefersDark = window.matchMedia ? window.matchMedia('(prefers-color-scheme: dark)').matches : false;
                if (stored === 'dark' || (!stored && prefersDark)) {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
            } catch (error) {
                // localStorage might be unavailable; ignore
            }
        })();

        function appLayout() {
            const prefersDarkQuery = window.matchMedia ? window.matchMedia('(prefers-color-scheme: dark)') : null;
            let storedTheme;
            try {
                storedTheme = localStorage.getItem('theme');
            } catch (error) {
                storedTheme = null;
            }

            return {
                sidebarOpen: false,
                sidebarMini: false,
                dark: storedTheme ? storedTheme === 'dark' : (prefersDarkQuery ? prefersDarkQuery.matches : false),
                init() {
                    this.applyTheme(this.dark);
                    this.$watch('dark', value => this.applyTheme(value));
                },
                applyTheme(value) {
                    document.documentElement.classList.toggle('dark', value);
                    try {
                        localStorage.setItem('theme', value ? 'dark' : 'light');
                    } catch (error) {
                        // Ignore when storage is unavailable
                    }
                },
                toggleDark() {
                    this.dark = !this.dark;
                }
            };
        }
    </script>

    <title>@yield('title', config('app.name').' Admin')</title>

    @vite(['resources/css/app.css','resources/js/app.js'])
    @livewireStyles
    @stack('head')
</head>
<body class="antialiased bg-zinc-50 text-zinc-900 dark:bg-zinc-950 dark:text-zinc-100">

    <!-- Mobile overlay -->
    <div x-cloak x-show="sidebarOpen" class="fixed inset-0 z-40 bg-black/40 lg:hidden" @click="sidebarOpen=false"></div>

    <!-- Sidebar (desktop) -->
    <aside :class="sidebarMini ? 'w-20' : 'w-72'"
           class="fixed inset-y-0 left-0 z-50 hidden lg:flex flex-col bg-white/80 dark:bg-zinc-900/80 backdrop-blur border-r border-zinc-200 dark:border-zinc-800 transition-all">

        @php
            $user = auth()->user();
            $homeRoute = '#';
            $portalLabel = 'Portal';
            $tenantSettings = $tenantSettings ?? ($user?->tenant?->settings ?? []);
            $institutionName = $institutionName ?? ($tenantSettings['institute_name'] ?? config('app.name'));

            if ($user) {
                if ($user->hasRole('admin')) {
                    $homeRoute = route('admin.dashboard');
                    $portalLabel = 'Admin';
                } elseif ($user->hasRole('manager')) {
                    $homeRoute = route('manager.dashboard');
                    $portalLabel = 'Manager';
                }
            }
        @endphp

        <div class="flex items-center gap-2 px-4 h-16">
            <button @click="sidebarMini=!sidebarMini" class="p-2 rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800" title="Toggle mini">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <div class="flex items-center gap-2" x-show="!sidebarMini">
                @if(!empty($tenantSettings['institute_logo']))
                    <img src="{{ $tenantSettings['institute_logo'] }}" alt="Institute logo" class="h-8 w-8 rounded-full object-cover">
                @endif
                <div>
                    <a href="{{ $homeRoute }}" class="font-semibold block">{{ $institutionName }} {{ $portalLabel }}</a>
                    @if(!empty($tenantSettings['academic_year']))
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">Academic Year: {{ $tenantSettings['academic_year'] }}</p>
                    @endif
                </div>
            </div>
        </div>

        <nav class="flex-1 overflow-y-auto px-2 py-3 space-y-1">
            @php
                use Illuminate\Support\Facades\Route as R;
                use Illuminate\Support\Str;

                $items = [
                    ['label' => 'Dashboard', 'href' => $homeRoute, 'icon' => 'M3 12h18M3 6h18M3 18h18', 'roles' => ['admin','manager']],

                    ['label' => 'Students',
                    'href' => R::has('admin.students.index') ? route('admin.students.index') : '#',
                    'icon' => 'M15 19a4 4 0 10-6 0m9 0v-1a4 4 0 00-4-4H10a4 4 0 00-4 4v1',
                    'roles' => ['admin','manager']],

                    ['label' => 'Test Performance',
                    'href' => R::has('academics.test-performances.index') ? route('academics.test-performances.index') : '#',
                    'icon' => 'M4 6h16M4 12h16M4 18h16',
                    'roles' => ['admin','manager','reception']],

                    ['label' => 'Enquiries',
                    'href' => R::has('admin.enquiries.index') ? route('admin.enquiries.index') : '#',
                    'icon' => 'M2.25 12l8.96-8.96a1.12 1.12 0 0 1 1.6 0L21.75 12M6.75 10.5V21h10.5V10.5',
                    'roles' => ['admin','manager']],

                    ['label' => 'Fee Structures',
                    'href' => R::has('admin.fee-structures.index') ? route('admin.fee-structures.index') : '#',
                    'icon' => 'M12 6v12m6-6H6',
                    'roles' => ['admin']],

                    ['label' => 'Attendance',
                    'href' => R::has('admin.attendances.index') ? route('admin.attendances.index') : '#',
                    'icon' => 'M4 6h16M4 12h16M4 18h7',
                    'roles' => ['admin','manager']],

                    ['label' => 'Reports',
                    'href' => R::has('admin.reports.index') ? route('admin.reports.index') : '#',
                    'icon' => 'M4 4h16M4 12h16M4 20h16',
                    'roles' => ['admin','manager']],

                    ['label' => 'Data Tools',
                    'href' => route('admin.data-transfer.index'),
                    'icon' => 'M4 4h16M4 10h16M4 16h16',
                    'roles' => ['admin']],

                    ['label' => 'Payments',
                    'href' => R::has('admin.payments.index') ? route('admin.payments.index') : '#',
                    'icon' => 'M12 8c-1.66 0-3 .9-3 2s1.34 2 3 2 3 .9 3 2-1.34 2-3 2m0-10c1.66 0 3 .9 3 2',
                    'roles' => ['admin','manager']],

                    ['label' => 'Expenses',
                    'href' => R::has('admin.expenses.index') ? route('admin.expenses.index') : '#',
                    'icon' => 'M3 6h18M3 12h18M3 18h18',
                    'roles' => ['admin','manager']],

                    ['label' => 'Announcements',
                    'href' => R::has('admin.announcements.index') ? route('admin.announcements.index') : '#',
                    'icon' => 'M4 6h16M4 12h16M12 18h8',
                    'roles' => ['admin','manager']],

                    ['label' => 'Profile',
                    'href' => route('admin.profile.edit'),
                    'icon' => 'M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-3.31 0-6 2.69-6 6h12c0-3.31-2.69-6-6-6',
                    'roles' => ['admin','manager']],

                    ['label' => 'Feedback',
                    'href' => R::has('admin.feedback.index') ? route('admin.feedback.index') : '#',
                    'icon' => 'M4 4h16M4 12h10M4 20h7',
                    'roles' => ['admin','manager']],

                    ['label' => 'Teachers',
                    'href' => route('management.teachers.index'),
                    'icon' => 'M4 7h16M5 21h14a2 2 0 002-2v-8H3v8a2 2 0 002 2zm5-8v4m4-4v4',
                    'roles' => ['admin','manager']],

                    ['label' => 'Staff',
                    'href' => route('management.staff.index'),
                    'icon' => 'M16 11V7a4 4 0 10-8 0v4M5 21h14a2 2 0 002-2v-2a4 4 0 00-4-4H7a4 4 0 00-4 4v2a2 2 0 002 2',
                    'roles' => ['admin','manager']],
                ];
                $current = url()->current();
            @endphp


            @foreach ($items as $item)
                @php
                    $allowedRoles = $item['roles'] ?? [];
                    if (!empty($allowedRoles) && (!$user || !$user->hasAnyRole($allowedRoles))) {
                        continue;
                    }

                    $href = $item['href'];
                    $active = $href !== '#' && str_starts_with($current, $href);
                @endphp
                <a href="{{ $item['href'] }}"
                   class="group flex items-center gap-3 rounded-xl px-3 py-2 text-sm transition
                   {{ $active ? 'bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900' : 'hover:bg-zinc-100 dark:hover:bg-zinc-800' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="{{ $item['icon'] }}" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span x-show="!sidebarMini" class="truncate">{{ $item['label'] }}</span>
                </a>
            @endforeach
        </nav>

        <div class="p-3 border-t border-zinc-200 dark:border-zinc-800">
            <button type="button" @click="toggleDark()" class="w-full flex items-center gap-2 px-3 py-2 rounded-xl hover:bg-zinc-100 dark:hover:bg-zinc-800 text-sm" :aria-pressed="dark">
                <svg x-cloak x-show="!dark" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="4"/>
                    <path d="M12 2v2m0 16v2m10-10h-2M6 12H4m15.071-7.071l-1.414 1.414M6.343 17.657l-1.414 1.414m0-12.728l1.414 1.414m12.728 12.728l-1.414-1.414"/>
                </svg>
                <svg x-cloak x-show="dark" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/>
                </svg>
                <span x-show="!sidebarMini" class="truncate">Toggle dark mode</span>
            </button>
        </div>
    </aside>

    <!-- Sidebar (mobile slide-over) -->
    <div x-cloak x-show="sidebarOpen" class="fixed inset-y-0 left-0 z-50 w-72 bg-white dark:bg-zinc-900 border-r border-zinc-200 dark:border-zinc-800 p-3 flex flex-col lg:hidden">
        <div class="flex items-center justify-between h-12 flex-shrink-0">
            <span class="font-semibold">{{ config('app.name') }} {{ $portalLabel }}</span>
            <button @click="sidebarOpen=false" class="p-2 rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" stroke="currentColor" fill="none"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="mt-4 flex-1 overflow-y-auto space-y-1 pr-1">
            @foreach ($items as $item)
                @php
                    $allowedRoles = $item['roles'] ?? [];
                    if (!empty($allowedRoles) && (!$user || !$user->hasAnyRole($allowedRoles))) {
                        continue;
                    }
                @endphp
                <a href="{{ $item['href'] }}" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"><path d="{{ $item['icon'] }}" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <span>{{ $item['label'] }}</span>
                </a>
            @endforeach
        </div>
        <div class="mt-4 border-t border-zinc-200 pt-3 dark:border-zinc-800">
            <button type="button" @click="toggleDark()" class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium hover:bg-zinc-100 dark:hover:bg-zinc-800" :aria-pressed="dark">
                <svg x-cloak x-show="!dark" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="4"/>
                    <path d="M12 2v2m0 16v2m10-10h-2M6 12H4m15.071-7.071l-1.414 1.414M6.343 17.657l-1.414 1.414m0-12.728l1.414 1.414m12.728 12.728l-1.414-1.414"/>
                </svg>
                <svg x-cloak x-show="dark" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/>
                </svg>
                <span>Toggle dark mode</span>
            </button>
        </div>
    </div>

    <!-- Main area -->
    <div class="lg:pl-72 min-h-screen">
        <header class="h-16 flex items-center px-4 gap-3 bg-white/70 dark:bg-zinc-900/70 backdrop-blur border-b border-zinc-200 dark:border-zinc-800">
            <button class="lg:hidden p-2 rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800" @click="sidebarOpen=true" aria-label="Open menu">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"><path fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <div class="font-semibold">@yield('header','Dashboard')</div>
            <div class="ml-auto flex items-center gap-2">
                <button type="button" class="p-2 rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800 lg:hidden" @click="toggleDark()" :aria-pressed="dark" aria-label="Toggle dark mode">
                    <svg x-cloak x-show="!dark" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="4"/>
                        <path d="M12 2v2m0 16v2m10-10h-2M6 12H4m15.071-7.071l-1.414 1.414M6.343 17.657l-1.414 1.414m0-12.728l1.414 1.414m12.728 12.728l-1.414-1.414"/>
                    </svg>
                    <svg x-cloak x-show="dark" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/>
                    </svg>
                </button>

                <div x-data="{ open: false }" class="relative">
                    <button type="button" @click="open = !open" @keydown.escape.window="open = false" :aria-expanded="open" class="flex items-center gap-2 rounded-full border border-transparent px-3 py-1.5 text-sm font-medium text-zinc-700 transition hover:bg-zinc-100 dark:text-zinc-200 dark:hover:bg-zinc-800">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-500 text-xs font-semibold text-white dark:bg-indigo-400 dark:text-zinc-900">{{ strtoupper(Str::substr(auth()->user()->name, 0, 2)) }}</span>
                        <span class="hidden text-left text-xs leading-tight sm:block">
                            <span class="block font-semibold">{{ auth()->user()->name }}</span>
                            <span class="block text-zinc-500 dark:text-zinc-400">{{ auth()->user()->email }}</span>
                        </span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-zinc-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.25a.75.75 0 01-1.06 0L5.25 8.27a.75.75 0 01-.02-1.06z" clip-rule="evenodd"/></svg>
                    </button>

                    <div x-cloak x-show="open" x-transition @click.away="open = false" class="absolute right-0 z-50 mt-2 w-56 overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-lg dark:border-zinc-700 dark:bg-zinc-900">
                        <div class="px-4 py-3 text-xs text-zinc-500 dark:text-zinc-400">Signed in as
                            <span class="block text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{ auth()->user()->email }}</span>
                        </div>
                        <div class="border-t border-zinc-200 dark:border-zinc-800"></div>
                        <a href="{{ route('admin.profile.edit') }}" class="block px-4 py-2 text-sm text-zinc-700 transition hover:bg-zinc-100 dark:text-zinc-200 dark:hover:bg-zinc-800" @click="open=false">Profile &amp; security</a>
                        <form method="POST" action="{{ route('logout') }}" class="border-t border-zinc-200 dark:border-zinc-800">
                            @csrf
                            <button type="submit" class="w-full px-4 py-2 text-left text-sm text-rose-600 transition hover:bg-rose-50 dark:text-rose-300 dark:hover:bg-rose-500/10">Log out</button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <main class="p-4 lg:p-6">
            @yield('content')
        </main>
    </div>

    @livewireScripts
    @stack('scripts')
</body>
</html>


