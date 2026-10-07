@props(['title', 'subtitle' => null])

@php
    $sections = [
        'Overview' => [
            ['admin.dashboard', 'admin.dashboard', 'Dashboard', 'grid', null],
            ['admin.assessments.index', 'admin.assessments.*', 'Results', 'bar-chart', 'results'],
        ],
        'Manage' => [
            ['admin.users.index', 'admin.users.*', 'Users', 'people', 'users'],
            ['admin.questions.index', 'admin.questions.*', 'Questions', 'clipboard', 'questions'],
            ['admin.tips.index', 'admin.tips.*', 'App Content', 'book', null],
        ],
        'System' => [
            ['admin.activity', 'admin.activity', 'Activity Log', 'time', null],
            ['admin.security', 'admin.security', 'Security', 'shield-checkmark', 'security'],
        ],
    ];
    $newToday = ['results' => $sidebar['resultsToday'], 'users' => $sidebar['usersToday'], 'security' => $sidebar['security']];
    $admin = auth()->user();
    $hour = (int) now(config('app.display_timezone'))->format('G');
    [$greeting, $greetingIcon] = $hour < 12 ? ['Good morning', '☀️'] : ($hour < 18 ? ['Good afternoon', '🌤️'] : ['Good evening', '🌙']);
    $initials = collect(preg_split('/\s+/', trim($admin->name)))->map(fn ($part) => mb_substr($part, 0, 1))->take(2)->implode('');
@endphp

<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} · MataKo Admin</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet">
    @vite(['resources/css/app.css'])
    {{-- The icon set the mobile app uses, so content icons look the same here. --}}
    <script type="module" src="{{ asset('ionicons/ionicons.esm.js') }}" @nonce></script>
</head>
<body class="h-full bg-[#F6F4F2] font-admin text-slate-900 antialiased">
    <div class="min-h-full lg:flex">
        {{-- Sidebar --}}
        <aside class="relative flex flex-col overflow-hidden bg-gradient-to-b from-navy-900 via-navy to-navy-800 text-white lg:fixed lg:inset-y-0 lg:w-64">
            {{-- Soft brand glow --}}
            <div class="pointer-events-none absolute -top-24 -left-20 h-56 w-56 rounded-full bg-brand/20 blur-3xl"></div>
            <div class="pointer-events-none absolute -right-24 bottom-24 h-56 w-56 rounded-full bg-sky-400/10 blur-3xl"></div>

            <a href="{{ route('admin.dashboard') }}" class="relative flex items-center gap-3 px-5 pt-6 pb-5">
                <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-white/10 ring-1 ring-white/15 backdrop-blur">
                    <img src="{{ asset('images/matako-eye.png') }}" alt="" class="h-5 w-auto">
                </span>
                <span class="leading-none">
                    <span class="block text-xl font-extrabold tracking-tight">MataKo</span>
                    <span class="mt-1 block text-xs font-medium text-orange-300">Admin Console</span>
                </span>
            </a>

            <nav class="relative flex gap-1 overflow-x-auto px-3 pb-4 lg:flex-1 lg:flex-col lg:gap-5 lg:overflow-y-auto lg:pt-1" aria-label="Admin">
                @foreach ($sections as $section => $links)
                    <div class="flex gap-1 lg:block lg:space-y-1">
                        <p class="hidden items-center gap-2 px-3 pb-1.5 text-[10px] font-semibold tracking-[0.18em] text-slate-400/70 uppercase lg:flex">
                            {{ $section }}<span class="h-px flex-1 bg-white/10"></span>
                        </p>
                        @foreach ($links as [$route, $pattern, $label, $icon, $countKey])
                            @php($active = request()->routeIs($pattern))
                            <a href="{{ route($route) }}" @class([
                                'group relative flex shrink-0 items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition',
                                'bg-gradient-to-r from-brand to-[#FF9A3D] text-white shadow-lg shadow-brand/30' => $active,
                                'text-slate-300 hover:bg-white/[0.06] hover:text-white' => ! $active,
                            ]) @if ($active) aria-current="page" @endif>
                                <span @class(['flex h-8 w-8 shrink-0 items-center justify-center rounded-lg transition', 'bg-white/20' => $active, 'bg-white/5 group-hover:bg-white/10' => ! $active])>
                                    <ion-icon name="{{ $active ? $icon : $icon.'-outline' }}" class="text-[17px]"></ion-icon>
                                </span>
                                <span class="flex-1">{{ $label }}</span>
                                @if ($countKey)
                                    <span @class(['hidden min-w-6 rounded-full px-1.5 py-0.5 text-center text-[11px] font-semibold lg:inline-block', 'bg-white/25 text-white' => $active, 'bg-white/10 text-slate-300' => ! $active])>{{ $sidebar[$countKey] }}</span>
                                @endif
                                @if (($newToday[$countKey] ?? 0) > 0 && ! $active)
                                    <span class="absolute top-2 left-9 h-2 w-2 rounded-full bg-brand ring-2 ring-navy" title="{{ $newToday[$countKey] }} new today"></span>
                                @endif
                            </a>
                        @endforeach
                    </div>
                @endforeach
            </nav>

            {{-- Today snapshot --}}
            <div class="relative mx-4 mb-3 hidden rounded-2xl border border-white/10 bg-white/[0.06] p-4 lg:block">
                <p class="flex items-center justify-between text-xs font-semibold text-slate-300">
                    <span class="flex items-center gap-1.5"><span class="relative flex h-2 w-2"><span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-60"></span><span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-400"></span></span>Today</span>
                    <span class="font-normal text-slate-400">{{ now(config('app.display_timezone'))->format('M j') }}</span>
                </p>
                <dl class="mt-3 grid grid-cols-3 gap-2 text-center">
                    @foreach ([['Results', $sidebar['resultsToday']], ['New users', $sidebar['usersToday']], ['Active', $sidebar['activeToday']]] as [$statLabel, $statValue])
                        <div class="flex flex-col-reverse rounded-xl bg-white/5 py-2">
                            <dt class="text-[10px] text-slate-400">{{ $statLabel }}</dt>
                            <dd class="text-lg font-bold">{{ $statValue }}</dd>
                        </div>
                    @endforeach
                </dl>
            </div>

            <div class="relative hidden border-t border-white/10 p-4 lg:block">
                <div class="rounded-2xl bg-white/5 p-3 ring-1 ring-white/10">
                    <a href="{{ route('admin.profile') }}" class="group flex items-center gap-3" title="My profile">
                        <span class="relative shrink-0">
                            <span class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-brand to-[#FF9A3D] text-sm font-bold text-white">{{ $initials }}</span>
                            <span class="absolute right-0 bottom-0 h-2.5 w-2.5 rounded-full bg-emerald-400 ring-2 ring-navy" title="Signed in"></span>
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="block truncate text-sm font-semibold group-hover:text-orange-300">{{ $admin->name }}</span>
                            <span class="block truncate text-xs text-slate-400">{{ $admin->email }}</span>
                        </span>
                    </a>
                    <div class="mt-3 grid grid-cols-2 gap-2">
                        <a href="{{ route('admin.profile') }}" @class(['flex items-center justify-center gap-1.5 rounded-lg py-2 text-xs font-semibold transition', 'bg-white/20 text-white' => request()->routeIs('admin.profile'), 'bg-white/5 text-slate-300 hover:bg-white/10 hover:text-white' => ! request()->routeIs('admin.profile')])>
                            <ion-icon name="settings-outline" class="text-base"></ion-icon>Profile
                        </a>
                        <form method="POST" action="{{ route('admin.logout') }}">
                            @csrf
                            <button class="flex w-full items-center justify-center gap-1.5 rounded-lg bg-white/5 py-2 text-xs font-semibold text-slate-300 transition hover:bg-red-500/15 hover:text-red-300">
                                <ion-icon name="log-out-outline" class="text-base"></ion-icon>Sign out
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </aside>

        {{-- Main --}}
        <div class="flex min-h-full flex-1 flex-col lg:pl-64">
            <header class="w-full max-w-[1760px] px-4 pt-7 sm:px-8">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div class="min-w-0">
                        @if (request()->routeIs('admin.dashboard'))
                            <p class="flex items-center gap-2 text-sm font-medium text-slate-600">
                                <span aria-hidden="true">{{ $greetingIcon }}</span> {{ $greeting }}, {{ $admin->name }}
                            </p>
                        @else
                            <nav aria-label="Breadcrumb" class="text-xs font-medium text-slate-500">
                                <a href="{{ route('admin.dashboard') }}" class="hover:text-brand">MataKo Admin</a>
                                <span class="mx-1.5 text-slate-300">/</span>
                                <span class="text-slate-700">{{ $title }}</span>
                            </nav>
                        @endif
                        <h1 class="mt-1 truncate text-3xl font-bold tracking-tight text-navy">{{ $title }}</h1>
                        @if ($subtitle)
                            <p class="mt-1 text-sm text-slate-500">{{ $subtitle }}</p>
                        @endif
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <form method="GET" action="{{ route('admin.search') }}" class="relative" role="search">
                            <svg class="pointer-events-none absolute top-1/2 left-3.5 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                            <label for="admin-search" class="sr-only">Search</label>
                            <input id="admin-search" type="search" name="q" value="{{ request()->routeIs('admin.search') ? request('q') : '' }}" placeholder="Search users, questions, activity…" class="w-80 rounded-xl border border-slate-200 bg-white py-2.5 pr-3 pl-10 text-sm shadow-xs placeholder:text-slate-400 focus:border-brand focus:ring-4 focus:ring-brand/15 focus:outline-none">
                        </form>

                        <details class="relative">
                            <summary class="relative flex h-11 w-11 cursor-pointer list-none items-center justify-center rounded-xl border border-slate-200 bg-white text-navy shadow-xs transition hover:bg-slate-50 [&::-webkit-details-marker]:hidden" aria-label="Notifications">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0"/></svg>
                                @if ($notifications->isNotEmpty())
                                    <span class="absolute -top-1 -right-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-brand px-1 text-[10px] font-bold text-white ring-2 ring-white">{{ $notifications->count() }}</span>
                                @endif
                            </summary>
                            <div class="absolute right-0 z-20 mt-2 w-80 rounded-2xl border border-slate-200 bg-white p-2 shadow-xl">
                                <p class="px-3 pt-2 pb-1 text-xs font-semibold tracking-wider text-slate-500 uppercase">Last 24 hours</p>
                                @forelse ($notifications as $notification)
                                    <a href="{{ $notification['url'] }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 transition hover:bg-slate-50">
                                        <x-admin.avatar :name="$notification['name']" size="h-8 w-8 text-[11px]" />
                                        <span class="min-w-0 flex-1">
                                            <span class="block truncate text-sm font-medium text-navy">{{ $notification['name'] }}</span>
                                            <span class="block truncate text-xs text-slate-500">{{ $notification['text'] }} · {{ $notification['time']->diffForHumans() }}</span>
                                        </span>
                                    </a>
                                @empty
                                    <p class="px-3 py-6 text-center text-sm text-slate-500">Nothing new in the last 24 hours.</p>
                                @endforelse
                            </div>
                        </details>

                        {{ $actions ?? '' }}
                        <form method="POST" action="{{ route('admin.logout') }}" class="lg:hidden">
                            @csrf
                            <button class="admin-btn-secondary">Sign out</button>
                        </form>
                    </div>
                </div>
            </header>

            <main class="w-full max-w-[1760px] flex-1 px-4 py-6 sm:px-8">
                @if (auth()->user() && ! auth()->user()->hasTwoFactor() && ! request()->routeIs('admin.profile'))
                    <div class="mb-6 flex flex-wrap items-center gap-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900" role="note">
                        <ion-icon name="shield-half-outline" class="text-xl text-amber-600"></ion-icon>
                        <span class="flex-1"><strong>Protect the admin account:</strong> turn on two-factor sign-in so a stolen password alone cannot open the dashboard.</span>
                        <a href="{{ route('admin.profile') }}#two-factor" class="admin-btn-secondary py-1.5 text-xs">Set it up</a>
                    </div>
                @endif
                @if (session('status'))
                    <div class="mb-6 flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="status">
                        <svg class="mt-0.5 h-5 w-5 shrink-0 text-emerald-600" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                        <span>{{ session('status') }}</span>
                    </div>
                @endif
                @if ($errors->any())
                    <div class="mb-6 flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">
                        <svg class="mt-0.5 h-5 w-5 shrink-0 text-red-600" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg>
                        <ul class="space-y-0.5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{ $slot }}
            </main>

            <footer class="w-full max-w-[1760px] px-4 pb-6 text-xs text-slate-400 sm:px-8">MataKo · Digital eye strain self-assessment · Admin console</footer>
        </div>
    </div>
    <script @nonce>
        // Filters marked data-autosubmit apply as soon as they change (inline onchange="" is blocked by the security policy).
        document.querySelectorAll('[data-autosubmit]').forEach((field) => field.addEventListener('change', () => field.form.requestSubmit()));
    </script>
</body>
</html>
