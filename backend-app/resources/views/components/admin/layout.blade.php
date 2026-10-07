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

    // The sidebar item this page belongs to: gives the breadcrumb, the section label and the banner icon.
    $current = ['section' => 'Account', 'label' => null, 'route' => null, 'icon' => 'person-circle'];
    foreach ($sections as $section => $links) {
        foreach ($links as [$route, $pattern, $label, $icon]) {
            if (request()->routeIs($pattern)) {
                $current = ['section' => $section, 'label' => $label, 'route' => $route, 'icon' => $icon];
            }
        }
    }
    if (request()->routeIs('admin.search')) {
        $current = ['section' => 'Overview', 'label' => null, 'route' => null, 'icon' => 'search'];
    }
    $isDashboard = request()->routeIs('admin.dashboard');

    // Live figures shown as chips in the page banner: [icon, text, highlighted].
    $chip = fn (string $icon, string $text, bool $highlight = false): array => [$icon, $text, $highlight];
    $plural = fn (int $count, string $word): string => number_format($count).' '.\Illuminate\Support\Str::plural($word, $count);
    $bannerChips = match (true) {
        $isDashboard => [
            $chip('pulse', 'Live data', true),
            $chip('bar-chart-outline', $plural($sidebar['results'], 'result')),
            $chip('people-outline', $plural($sidebar['users'], 'user')),
            $chip('flash-outline', $sidebar['activeToday'].' active today'),
        ],
        request()->routeIs('admin.assessments.index') => [
            $chip('bar-chart-outline', $plural($sidebar['results'], 'result').' in total'),
            $chip('today-outline', $sidebar['resultsToday'].' today', $sidebar['resultsToday'] > 0),
        ],
        request()->routeIs('admin.users.index') => [
            $chip('people-outline', $plural($sidebar['users'], 'user')),
            $chip('person-add-outline', $sidebar['usersToday'].' new today', $sidebar['usersToday'] > 0),
            $chip('flash-outline', $sidebar['activeToday'].' active today'),
        ],
        request()->routeIs('admin.questions.index') => [
            $chip('checkmark-circle-outline', $plural($sidebar['questions'], 'active question')),
            $chip('language-outline', 'English · Filipino · Cebuano'),
        ],
        request()->routeIs('admin.tips.index') => [
            $chip('language-outline', 'English · Filipino · Cebuano'),
            $chip('phone-portrait-outline', 'Changes reach the app right away'),
        ],
        request()->routeIs('admin.activity') => [
            $chip('finger-print-outline', 'Every change, with who and when'),
        ],
        request()->routeIs('admin.security') => [
            $chip($admin->hasTwoFactor() ? 'shield-checkmark-outline' : 'shield-half-outline', $admin->hasTwoFactor() ? 'Your two-factor sign-in is on' : 'Your two-factor sign-in is off', ! $admin->hasTwoFactor()),
            $chip('warning-outline', $plural($sidebar['security'], 'serious event').' in 24 hours', $sidebar['security'] > 0),
        ],
        default => [],
    };
    $today = now(config('app.display_timezone'));
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
            {{-- Top bar: stays in view while scrolling --}}
            <div class="sticky top-0 z-30 border-b border-slate-200/70 bg-[#F6F4F2]/80 backdrop-blur-xl">
                <div class="flex h-16 w-full max-w-[1760px] items-center gap-3 px-4 sm:px-8">
                    <nav aria-label="Breadcrumb" class="hidden min-w-0 items-center gap-1.5 text-sm md:flex">
                        <a href="{{ route('admin.dashboard') }}" class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-slate-500 transition hover:bg-white hover:text-brand" title="Dashboard">
                            <ion-icon name="home-outline" class="text-base"></ion-icon>
                        </a>
                        <ion-icon name="chevron-forward" class="shrink-0 text-xs text-slate-300"></ion-icon>
                        <span class="shrink-0 text-slate-500">{{ $current['section'] }}</span>
                        @if ($current['label'] && $current['label'] !== $title)
                            <ion-icon name="chevron-forward" class="shrink-0 text-xs text-slate-300"></ion-icon>
                            <a href="{{ route($current['route']) }}" class="shrink-0 text-slate-500 transition hover:text-brand">{{ $current['label'] }}</a>
                        @endif
                        <ion-icon name="chevron-forward" class="shrink-0 text-xs text-slate-300"></ion-icon>
                        <span class="truncate font-semibold text-navy" aria-current="page">{{ $title }}</span>
                    </nav>

                    <div class="ml-auto flex items-center gap-2 sm:gap-3">
                        <form method="GET" action="{{ route('admin.search') }}" class="group relative" role="search">
                            <ion-icon name="search-outline" class="pointer-events-none absolute top-1/2 left-3.5 -translate-y-1/2 text-base text-slate-400 transition group-focus-within:text-brand"></ion-icon>
                            <label for="admin-search" class="sr-only">Search</label>
                            <input id="admin-search" type="search" name="q" value="{{ request()->routeIs('admin.search') ? request('q') : '' }}" placeholder="Search anything…"
                                   class="w-48 rounded-xl border border-slate-200 bg-white/90 py-2.5 pr-16 pl-10 text-sm shadow-xs transition-all placeholder:text-slate-400 focus:w-64 focus:border-brand focus:bg-white focus:ring-4 focus:ring-brand/15 focus:outline-none sm:w-72 sm:focus:w-96">
                            <kbd class="pointer-events-none absolute top-1/2 right-2.5 hidden -translate-y-1/2 rounded-md border border-slate-200 bg-slate-50 px-1.5 py-0.5 font-sans text-[10px] font-semibold text-slate-500 sm:block">Ctrl K</kbd>
                        </form>

                        <span class="hidden items-center gap-2 rounded-xl border border-slate-200 bg-white/90 px-3 py-2.5 text-xs font-medium text-slate-600 shadow-xs xl:flex">
                            <ion-icon name="calendar-clear-outline" class="text-sm text-brand"></ion-icon>{{ $today->format('D, M j') }}
                        </span>

                        <details class="relative" data-dropdown>
                            <summary class="relative flex h-11 w-11 cursor-pointer list-none items-center justify-center rounded-xl border border-slate-200 bg-white/90 text-navy shadow-xs transition hover:border-brand/40 hover:text-brand [&::-webkit-details-marker]:hidden" aria-label="Notifications">
                                <ion-icon name="notifications-outline" class="text-xl"></ion-icon>
                                @if ($notifications->isNotEmpty())
                                    <span class="absolute -top-1 -right-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-brand px-1 text-[10px] font-bold text-white ring-2 ring-[#F6F4F2]">{{ $notifications->count() }}</span>
                                    <span class="absolute -top-1 -right-1 h-5 w-5 animate-ping rounded-full bg-brand/40"></span>
                                @endif
                            </summary>
                            <div class="absolute right-0 z-40 mt-2 w-[22rem] overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl shadow-navy/10">
                                <div class="flex items-center justify-between border-b border-slate-100 bg-gradient-to-r from-brand-50 to-white px-4 py-3">
                                    <p class="text-sm font-semibold text-navy">Notifications</p>
                                    <span class="rounded-full bg-white px-2 py-0.5 text-[11px] font-medium text-slate-500 ring-1 ring-slate-200">Last 24 hours</span>
                                </div>
                                <div class="max-h-96 overflow-y-auto p-2">
                                    @forelse ($notifications as $notification)
                                        <a href="{{ $notification['url'] }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 transition hover:bg-slate-50">
                                            @if ($notification['name'] === 'Security alert')
                                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-red-50 text-red-600"><ion-icon name="warning" class="text-base"></ion-icon></span>
                                            @else
                                                <x-admin.avatar :name="$notification['name']" size="h-8 w-8 text-[11px]" />
                                            @endif
                                            <span class="min-w-0 flex-1">
                                                <span class="block truncate text-sm font-medium text-navy">{{ $notification['name'] }}</span>
                                                <span class="block truncate text-xs text-slate-500">{{ $notification['text'] }} · {{ $notification['time']->diffForHumans() }}</span>
                                            </span>
                                        </a>
                                    @empty
                                        <div class="px-3 py-8 text-center">
                                            <ion-icon name="notifications-off-outline" class="text-3xl text-slate-300"></ion-icon>
                                            <p class="mt-1 text-sm text-slate-500">Nothing new in the last 24 hours.</p>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </details>

                        <details class="relative" data-dropdown>
                            <summary class="flex cursor-pointer list-none items-center gap-2.5 rounded-xl border border-slate-200 bg-white/90 py-1.5 pr-2.5 pl-1.5 shadow-xs transition hover:border-brand/40 [&::-webkit-details-marker]:hidden" aria-label="Account menu">
                                <span class="relative">
                                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-brand to-[#FF9A3D] text-xs font-bold text-white">{{ $initials }}</span>
                                    @if ($admin->hasTwoFactor())
                                        <span class="absolute -right-1 -bottom-1 flex h-4 w-4 items-center justify-center rounded-full bg-emerald-500 text-white ring-2 ring-white" title="Two-factor sign-in is on"><ion-icon name="shield-checkmark" class="text-[9px]"></ion-icon></span>
                                    @endif
                                </span>
                                <span class="hidden text-left leading-tight lg:block">
                                    <span class="block max-w-32 truncate text-sm font-semibold text-navy">{{ $admin->name }}</span>
                                    <span class="block text-[11px] text-slate-500">Administrator</span>
                                </span>
                                <ion-icon name="chevron-down" class="hidden text-xs text-slate-400 lg:block"></ion-icon>
                            </summary>
                            <div class="absolute right-0 z-40 mt-2 w-64 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl shadow-navy/10">
                                <div class="bg-gradient-to-br from-navy to-navy-700 px-4 py-4 text-white">
                                    <p class="truncate text-sm font-semibold">{{ $admin->name }}</p>
                                    <p class="truncate text-xs text-slate-300">{{ $admin->email }}</p>
                                    <p @class(['mt-2 inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-semibold', 'bg-emerald-400/20 text-emerald-200' => $admin->hasTwoFactor(), 'bg-amber-400/20 text-amber-200' => ! $admin->hasTwoFactor()])>
                                        <ion-icon name="{{ $admin->hasTwoFactor() ? 'shield-checkmark' : 'shield-half' }}"></ion-icon>{{ $admin->hasTwoFactor() ? 'Two-factor on' : 'Two-factor off' }}
                                    </p>
                                </div>
                                <div class="p-2">
                                    @foreach ([['admin.profile', 'My profile', 'person-circle-outline'], ['admin.security', 'Security', 'shield-checkmark-outline'], ['admin.activity', 'Activity log', 'time-outline']] as [$route, $label, $icon])
                                        <a href="{{ route($route) }}" class="flex items-center gap-3 rounded-xl px-3 py-2 text-sm text-slate-700 transition hover:bg-slate-50 hover:text-navy">
                                            <ion-icon name="{{ $icon }}" class="text-lg text-slate-400"></ion-icon>{{ $label }}
                                        </a>
                                    @endforeach
                                    <form method="POST" action="{{ route('admin.logout') }}" class="mt-1 border-t border-slate-100 pt-1">
                                        @csrf
                                        <button class="flex w-full items-center gap-3 rounded-xl px-3 py-2 text-sm text-red-600 transition hover:bg-red-50">
                                            <ion-icon name="log-out-outline" class="text-lg"></ion-icon>Sign out
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </details>
                    </div>
                </div>
            </div>

            {{-- Page banner --}}
            <header class="w-full max-w-[1760px] px-4 pt-6 sm:px-8">
                <div class="relative overflow-hidden rounded-3xl bg-white px-6 py-6 shadow-[0_1px_2px_rgba(6,42,60,0.04),0_12px_32px_-16px_rgba(6,42,60,0.18)] ring-1 ring-slate-200/70 sm:px-8">
                    {{-- Decoration: dotted texture and soft brand shapes --}}
                    <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_1px_1px,rgba(6,42,60,0.07)_1px,transparent_0)] [background-size:18px_18px] [mask-image:linear-gradient(to_left,black,transparent_60%)]"></div>
                    <div class="pointer-events-none absolute -top-24 -right-16 h-64 w-64 rounded-full bg-brand/10 blur-2xl"></div>
                    <div class="pointer-events-none absolute -right-10 -bottom-28 h-56 w-56 rounded-full border-[28px] border-brand/10"></div>
                    <div class="pointer-events-none absolute top-0 left-0 h-1 w-full bg-gradient-to-r from-brand via-[#FF9A3D] to-transparent"></div>

                    <div class="relative flex flex-wrap items-center justify-between gap-5">
                        <div class="flex min-w-0 items-center gap-4 sm:gap-5">
                            <span class="relative hidden h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-brand to-[#FF9A3D] text-white shadow-lg shadow-brand/30 sm:flex">
                                <ion-icon name="{{ $current['icon'] }}" class="text-2xl"></ion-icon>
                                <span class="absolute inset-0 rounded-2xl ring-1 ring-white/30 ring-inset"></span>
                            </span>
                            <div class="min-w-0">
                                <p class="flex flex-wrap items-center gap-2 text-xs font-semibold tracking-wide text-brand">
                                    @if ($isDashboard)
                                        <span aria-hidden="true">{{ $greetingIcon }}</span>
                                        <span class="normal-case">{{ $greeting }}, {{ $admin->name }}</span>
                                    @else
                                        <span class="uppercase">{{ $current['section'] }}</span>
                                        @if ($current['label'] && $current['label'] !== $title)
                                            <span class="text-slate-300">/</span><span class="text-slate-500 uppercase">{{ $current['label'] }}</span>
                                        @endif
                                    @endif
                                </p>
                                <h1 class="mt-1 truncate text-2xl font-extrabold tracking-tight text-navy sm:text-[1.9rem]">{{ $title }}</h1>
                                @if ($subtitle || $isDashboard)
                                    <p class="mt-1 text-sm text-slate-500">{{ $isDashboard ? 'Overview for '.$today->format('l, F j, Y') : $subtitle }}</p>
                                @endif
                                @if ($bannerChips)
                                    <ul class="mt-3 flex flex-wrap gap-2">
                                        @foreach ($bannerChips as [$chipIcon, $chipText, $chipHighlight])
                                            <li @class([
                                                'inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium ring-1',
                                                'bg-brand-50 text-brand-600 ring-brand-100' => $chipHighlight,
                                                'bg-slate-50 text-slate-600 ring-slate-200/80' => ! $chipHighlight,
                                            ])>
                                                @if ($chipIcon === 'pulse')
                                                    <span class="relative flex h-2 w-2"><span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-brand opacity-60"></span><span class="relative inline-flex h-2 w-2 rounded-full bg-brand"></span></span>
                                                @else
                                                    <ion-icon name="{{ $chipIcon }}" class="text-sm"></ion-icon>
                                                @endif
                                                {{ $chipText }}
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        </div>

                        @if (isset($actions) && trim((string) $actions) !== '')
                            <div class="flex flex-wrap items-center gap-3">{{ $actions }}</div>
                        @endif
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

        // Header menus: only one open at a time; close on a click elsewhere or Escape.
        const dropdowns = [...document.querySelectorAll('details[data-dropdown]')];
        dropdowns.forEach((menu) => menu.addEventListener('toggle', () => {
            if (menu.open) dropdowns.filter((other) => other !== menu).forEach((other) => { other.open = false; });
        }));
        document.addEventListener('click', (event) => dropdowns.forEach((menu) => { if (!menu.contains(event.target)) menu.open = false; }));

        // Ctrl+K (or / outside a text field) jumps to search.
        const search = document.getElementById('admin-search');
        document.addEventListener('keydown', (event) => {
            const typing = ['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement?.tagName) || document.activeElement?.isContentEditable;
            if (((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k') || (event.key === '/' && !typing)) {
                event.preventDefault();
                search.focus();
                search.select();
            }
            if (event.key === 'Escape') dropdowns.forEach((menu) => { menu.open = false; });
        });
    </script>
</body>
</html>
