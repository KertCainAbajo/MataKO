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
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800,900" rel="stylesheet">
    @vite(['resources/css/app.css'])
    {{-- The icon set the mobile app uses, so content icons look the same here. --}}
    <script type="module" src="{{ asset('ionicons/ionicons.esm.js') }}" @nonce></script>
</head>
<body class="h-full bg-[#F4F6FB] font-admin text-slate-900 antialiased">
    <div class="min-h-full lg:flex">
        {{-- Sidebar: deep blue with the MataKo iris rings --}}
        <aside class="relative isolate flex flex-col overflow-hidden bg-[#05163A] text-white lg:fixed lg:inset-y-0 lg:w-[17rem]">
            <div class="pointer-events-none absolute inset-0 -z-10" aria-hidden="true">
                <div class="absolute inset-0 bg-[radial-gradient(ellipse_90%_40%_at_20%_0%,rgba(30,79,216,0.55),transparent_70%)]"></div>
                <div class="absolute -right-28 -bottom-28 h-80 w-80 rounded-full border border-white/[0.06]"></div>
                <div class="absolute -right-16 -bottom-16 h-56 w-56 rounded-full border border-[#F58216]/25"></div>
                <div class="absolute -right-4 -bottom-4 h-32 w-32 rounded-full border border-white/[0.06]"></div>
            </div>

            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-5 pt-6 pb-6">
                <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-white shadow-[0_10px_30px_-8px_rgba(0,0,0,0.6)]">
                    <img src="{{ asset('images/matako-eye.png') }}" alt="" class="h-5 w-auto">
                </span>
                <span class="leading-none">
                    <span class="block text-xl font-black tracking-tight">MataKo</span>
                    <span class="mt-1 block text-[11px] font-bold tracking-[0.18em] text-[#F58216] uppercase">Admin Console</span>
                </span>
            </a>

            <nav class="flex gap-1 overflow-x-auto px-3 pb-4 lg:flex-1 lg:flex-col lg:gap-6 lg:overflow-y-auto lg:pt-1" aria-label="Admin">
                @foreach ($sections as $section => $links)
                    <div class="flex gap-1 lg:block lg:space-y-1">
                        <p class="hidden px-3 pb-1.5 text-[10px] font-bold tracking-[0.22em] text-white/40 uppercase lg:block">{{ $section }}</p>
                        @foreach ($links as [$route, $pattern, $label, $icon, $countKey])
                            @php $active = request()->routeIs($pattern); @endphp
                            <a href="{{ route($route) }}" @class([
                                'group relative flex shrink-0 items-center gap-3 rounded-2xl px-2.5 py-2 text-sm font-semibold transition',
                                'bg-white text-[#05163A] shadow-[0_12px_30px_-12px_rgba(0,0,0,0.7)]' => $active,
                                'text-white/70 hover:bg-white/[0.07] hover:text-white' => ! $active,
                            ]) @if ($active) aria-current="page" @endif>
                                @if ($active)
                                    <span class="absolute top-1/2 -left-3 hidden h-7 w-1.5 -translate-y-1/2 rounded-r-full bg-[#F58216] lg:block"></span>
                                @endif
                                <span @class(['flex h-9 w-9 shrink-0 items-center justify-center rounded-xl transition', 'bg-[#F58216] text-white shadow-[0_8px_18px_-6px_rgba(245,130,22,0.8)]' => $active, 'bg-white/[0.07] group-hover:bg-white/[0.12]' => ! $active])>
                                    <ion-icon name="{{ $active ? $icon : $icon.'-outline' }}" class="text-[18px]"></ion-icon>
                                </span>
                                <span class="flex-1">{{ $label }}</span>
                                @if ($countKey)
                                    <span @class(['hidden min-w-7 rounded-lg px-1.5 py-0.5 text-center text-[11px] font-bold lg:inline-block', 'bg-[#05163A]/[0.08] text-[#05163A]' => $active, 'bg-white/10 text-white/80' => ! $active])>{{ $sidebar[$countKey] }}</span>
                                @endif
                                @if (($newToday[$countKey] ?? 0) > 0 && ! $active)
                                    <span class="absolute top-1.5 left-9 h-2.5 w-2.5 rounded-full bg-[#F58216] ring-2 ring-[#05163A]" title="{{ $newToday[$countKey] }} new today"></span>
                                @endif
                            </a>
                        @endforeach
                    </div>
                @endforeach
            </nav>

            {{-- Today snapshot --}}
            <div class="relative mx-4 mb-3 hidden overflow-hidden rounded-3xl bg-[#F58216] p-4 shadow-[0_18px_40px_-16px_rgba(245,130,22,0.8)] lg:block">
                <div class="pointer-events-none absolute -top-10 -right-10 h-28 w-28 rounded-full border-[14px] border-white/15"></div>
                <p class="relative flex items-center justify-between text-xs font-bold">
                    <span class="flex items-center gap-1.5"><span class="relative flex h-2 w-2"><span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-white opacity-70"></span><span class="relative inline-flex h-2 w-2 rounded-full bg-white"></span></span>Today</span>
                    <span class="font-semibold text-white/80">{{ now(config('app.display_timezone'))->format('M j') }}</span>
                </p>
                <dl class="relative mt-3 grid grid-cols-3 gap-2 text-center">
                    @foreach ([['Results', $sidebar['resultsToday']], ['New users', $sidebar['usersToday']], ['Active', $sidebar['activeToday']]] as [$statLabel, $statValue])
                        <div class="flex flex-col-reverse rounded-xl bg-white/20 py-2 backdrop-blur">
                            <dt class="text-[10px] font-semibold text-white/85">{{ $statLabel }}</dt>
                            <dd class="text-lg leading-tight font-black">{{ $statValue }}</dd>
                        </div>
                    @endforeach
                </dl>
            </div>

            <div class="hidden p-4 pt-1 lg:block">
                <div class="rounded-3xl bg-white/[0.06] p-3 ring-1 ring-white/10 backdrop-blur">
                    <a href="{{ route('admin.profile') }}" class="group flex items-center gap-3" title="My profile">
                        <span class="relative shrink-0">
                            <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-white text-sm font-black text-[#05163A]">{{ $initials }}</span>
                            <span class="absolute -right-0.5 -bottom-0.5 h-3 w-3 rounded-full bg-emerald-400 ring-2 ring-[#05163A]" title="Signed in"></span>
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="block truncate text-sm font-bold group-hover:text-[#F58216]">{{ $admin->name }}</span>
                            <span class="block truncate text-xs text-white/50">{{ $admin->email }}</span>
                        </span>
                    </a>
                    <div class="mt-3 grid grid-cols-2 gap-2">
                        <a href="{{ route('admin.profile') }}" @class(['flex items-center justify-center gap-1.5 rounded-xl py-2 text-xs font-bold transition', 'bg-white text-[#05163A]' => request()->routeIs('admin.profile'), 'bg-white/[0.07] text-white/80 hover:bg-white/15 hover:text-white' => ! request()->routeIs('admin.profile')])>
                            <ion-icon name="settings-outline" class="text-base"></ion-icon>Profile
                        </a>
                        <form method="POST" action="{{ route('admin.logout') }}">
                            @csrf
                            <button class="flex w-full items-center justify-center gap-1.5 rounded-xl bg-white/[0.07] py-2 text-xs font-bold text-white/80 transition hover:bg-red-500/20 hover:text-red-200">
                                <ion-icon name="log-out-outline" class="text-base"></ion-icon>Sign out
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </aside>

        {{-- Main --}}
        <div class="flex min-h-full flex-1 flex-col lg:pl-[17rem]">
            {{-- Top bar: stays in view while scrolling --}}
            <div class="sticky top-0 z-30 border-b border-slate-200/80 bg-white/85 backdrop-blur-xl">
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
                            <summary class="relative flex h-11 w-11 cursor-pointer list-none items-center justify-center rounded-2xl border border-slate-200 bg-white text-[#05163A] shadow-xs transition hover:border-[#F58216]/50 hover:text-[#F58216] [&::-webkit-details-marker]:hidden" aria-label="Notifications{{ $unreadNotifications ? ", {$unreadNotifications} unread" : '' }}">
                                <ion-icon name="{{ $unreadNotifications ? 'notifications' : 'notifications-outline' }}" class="text-xl"></ion-icon>
                                @if ($unreadNotifications)
                                    <span class="absolute -top-1.5 -right-1.5 h-5 w-5 animate-ping rounded-full bg-[#F58216]/40"></span>
                                    <span class="absolute -top-1.5 -right-1.5 flex h-5 min-w-5 items-center justify-center rounded-full bg-[#F58216] px-1 text-[10px] font-black text-white ring-2 ring-white">{{ $unreadNotifications }}</span>
                                @endif
                            </summary>
                            <div class="absolute right-0 z-40 mt-3 w-[min(24rem,calc(100vw-2rem))] overflow-hidden rounded-3xl bg-white shadow-[0_30px_70px_-20px_rgba(5,22,58,0.45)] ring-1 ring-slate-200">
                                <div class="relative overflow-hidden bg-[#05163A] px-5 pt-4 pb-4 text-white">
                                    <div class="pointer-events-none absolute -top-12 -right-10 h-32 w-32 rounded-full border-[16px] border-[#F58216]/25"></div>
                                    <div class="relative flex items-center justify-between gap-3">
                                        <div>
                                            <p class="text-base font-black">Notifications</p>
                                            <p class="text-xs text-white/60">{{ $unreadNotifications ? $unreadNotifications.' unread' : 'You are all caught up' }} · last 24 hours</p>
                                        </div>
                                        @if ($notifications->isNotEmpty())
                                            <div class="flex gap-1.5">
                                                @if ($unreadNotifications)
                                                    <form method="POST" action="{{ route('admin.notifications.read') }}">
                                                        @csrf
                                                        <button class="inline-flex items-center gap-1 rounded-xl bg-white/10 px-2.5 py-1.5 text-[11px] font-bold text-white ring-1 ring-white/15 transition hover:bg-white hover:text-[#05163A]" title="Mark all as read">
                                                            <ion-icon name="checkmark-done" class="text-sm"></ion-icon>Mark all read
                                                        </button>
                                                    </form>
                                                @endif
                                                <form method="POST" action="{{ route('admin.notifications.clear') }}">
                                                    @csrf
                                                    <button class="inline-flex items-center gap-1 rounded-xl bg-white/10 px-2.5 py-1.5 text-[11px] font-bold text-white ring-1 ring-white/15 transition hover:bg-red-500 hover:ring-red-500" title="Clear all notifications">
                                                        <ion-icon name="trash-outline" class="text-sm"></ion-icon>Clear all
                                                    </button>
                                                </form>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="max-h-[26rem] overflow-y-auto p-2">
                                    @forelse ($notifications as $notification)
                                        @php
                                            [$noticeIcon, $noticeTone] = match ($notification['type']) {
                                                'security' => ['warning', 'bg-red-50 text-red-600'],
                                                'signup' => ['person-add', 'bg-[#FFF1E5] text-[#F58216]'],
                                                default => ['clipboard', 'bg-[#E8EEFC] text-[#1E4FD8]'],
                                            };
                                        @endphp
                                        <a href="{{ $notification['url'] }}" @class(['group relative flex items-center gap-3 rounded-2xl px-3 py-3 transition hover:bg-slate-50', 'bg-[#FFF8F1]' => $notification['unread']])>
                                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $noticeTone }}"><ion-icon name="{{ $noticeIcon }}" class="text-lg"></ion-icon></span>
                                            <span class="min-w-0 flex-1">
                                                <span @class(['block truncate text-sm text-[#05163A]', 'font-bold' => $notification['unread'], 'font-medium' => ! $notification['unread']])>{{ $notification['name'] }}</span>
                                                <span class="block truncate text-xs text-slate-500">{{ $notification['text'] }}</span>
                                            </span>
                                            <span class="flex shrink-0 flex-col items-end gap-1.5">
                                                <span class="text-[11px] font-medium text-slate-400">{{ $notification['time']->diffForHumans(short: true) }}</span>
                                                @if ($notification['unread'])
                                                    <span class="h-2 w-2 rounded-full bg-[#F58216]" title="Unread"></span>
                                                @endif
                                            </span>
                                        </a>
                                    @empty
                                        <div class="px-4 py-10 text-center">
                                            <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-slate-50 text-slate-300"><ion-icon name="notifications-off-outline" class="text-3xl"></ion-icon></span>
                                            <p class="mt-3 text-sm font-bold text-[#05163A]">No notifications</p>
                                            <p class="mt-0.5 text-xs text-slate-500">New sign-ups, results and security alerts show up here.</p>
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
                <div @class(['relative isolate overflow-hidden rounded-3xl px-6 py-6 sm:px-8', 'bg-[#05163A] text-white shadow-[0_24px_60px_-28px_rgba(5,22,58,0.8)]' => $isDashboard, 'bg-white shadow-[0_1px_2px_rgba(5,22,58,0.04),0_12px_32px_-16px_rgba(5,22,58,0.18)] ring-1 ring-slate-200/70' => ! $isDashboard])>
                    @if ($isDashboard)
                        <div class="pointer-events-none absolute inset-0 -z-10" aria-hidden="true">
                            <div class="absolute inset-0 bg-[radial-gradient(ellipse_60%_120%_at_85%_0%,rgba(30,79,216,0.6),transparent_70%)]"></div>
                            <div class="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.05)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.05)_1px,transparent_1px)] [background-size:40px_40px] [mask-image:linear-gradient(to_left,black,transparent_70%)]"></div>
                            <div class="absolute top-1/2 right-[8%] h-[26rem] w-[26rem] -translate-y-1/2 rounded-full border border-white/[0.07]"></div>
                            <div class="absolute top-1/2 right-[calc(8%+4rem)] h-[18rem] w-[18rem] -translate-y-1/2 rounded-full border border-[#F58216]/40"></div>
                            <div class="animate-orbit absolute top-1/2 right-[calc(8%+4rem)] -mt-[9rem] h-[18rem] w-[18rem] rounded-full"><span class="absolute top-1/2 -left-[6px] h-3 w-3 -translate-y-1/2 rounded-full bg-[#F58216] shadow-[0_0_18px_4px_rgba(245,130,22,0.6)]"></span></div>
                        </div>
                    @else
                        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_1px_1px,rgba(5,22,58,0.07)_1px,transparent_0)] [background-size:18px_18px] [mask-image:linear-gradient(to_left,black,transparent_60%)]"></div>
                        <div class="pointer-events-none absolute -right-10 -bottom-28 h-56 w-56 rounded-full border-[28px] border-[#F58216]/10"></div>
                        <div class="pointer-events-none absolute top-0 left-0 h-1 w-full bg-gradient-to-r from-[#F58216] to-transparent"></div>
                    @endif

                    <div class="relative flex flex-wrap items-center justify-between gap-5">
                        <div class="flex min-w-0 items-center gap-4 sm:gap-5">
                            <span class="relative hidden h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-[#F58216] text-white shadow-[0_12px_30px_-10px_rgba(245,130,22,0.8)] sm:flex">
                                <ion-icon name="{{ $current['icon'] }}" class="text-2xl"></ion-icon>
                                <span class="absolute inset-0 rounded-2xl ring-1 ring-white/30 ring-inset"></span>
                            </span>
                            <div class="min-w-0">
                                <p @class(["flex flex-wrap items-center gap-2 text-xs font-bold tracking-wide", "text-[#F58216]" => true])>
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
                                <h1 @class(["mt-1 truncate text-2xl font-black tracking-tight sm:text-[2rem]", "text-white" => $isDashboard, "text-[#05163A]" => ! $isDashboard])>{{ $title }}</h1>
                                @if ($subtitle || $isDashboard)
                                    <p @class(["mt-1 text-sm", "text-white/65" => $isDashboard, "text-slate-500" => ! $isDashboard])>{{ $isDashboard ? 'Overview for '.$today->format('l, F j, Y') : $subtitle }}</p>
                                @endif
                                @if ($bannerChips)
                                    <ul class="mt-3 flex flex-wrap gap-2">
                                        @foreach ($bannerChips as [$chipIcon, $chipText, $chipHighlight])
                                            <li @class([
                                                'inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium ring-1',
                                                'bg-[#F58216] text-white ring-[#F58216]' => $chipHighlight && $isDashboard,
                                                'bg-white/10 text-white/85 ring-white/15' => ! $chipHighlight && $isDashboard,
                                                'bg-brand-50 text-brand-600 ring-brand-100' => $chipHighlight && ! $isDashboard,
                                                'bg-slate-50 text-slate-600 ring-slate-200/80' => ! $chipHighlight && ! $isDashboard,
                                            ])>
                                                @if ($chipIcon === 'pulse')
                                                    <span class="relative flex h-2 w-2"><span @class(["absolute inline-flex h-full w-full animate-ping rounded-full opacity-60", "bg-white" => $isDashboard, "bg-brand" => ! $isDashboard])></span><span @class(["relative inline-flex h-2 w-2 rounded-full", "bg-white" => $isDashboard, "bg-brand" => ! $isDashboard])></span></span>
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

    {{-- Confirmation for forms marked data-confirm (inline confirm() handlers are blocked by the security policy) --}}
    <dialog id="admin-confirm" class="m-auto w-[min(26rem,calc(100%-2rem))] rounded-3xl bg-white p-0 shadow-2xl backdrop:bg-navy/50 backdrop:backdrop-blur-sm">
        <div class="p-6 text-center">
            <span data-confirm-icon class="mx-auto flex h-14 w-14 items-center justify-center rounded-full"></span>
            <h2 data-confirm-title class="mt-4 text-lg font-bold text-navy"></h2>
            <p data-confirm-message class="mt-2 text-sm leading-relaxed text-slate-600"></p>
            <form method="dialog" class="mt-6 grid grid-cols-2 gap-3">
                <button value="cancel" class="admin-btn-secondary">Cancel</button>
                <button value="ok" data-confirm-ok></button>
            </form>
        </div>
    </dialog>

    <script @nonce>
        // Branded confirm step before risky actions: data-confirm="message", data-confirm-title, data-confirm-button, data-confirm-tone="danger".
        const confirmBox = document.getElementById('admin-confirm');
        const tones = {
            danger: ['bg-red-50 text-red-600', 'admin-btn-danger', 'M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0'],
            default: ['bg-brand-50 text-brand', 'admin-btn-primary', 'M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z'],
        };
        document.querySelectorAll('form[data-confirm]').forEach((form) => form.addEventListener('submit', (event) => {
            if (form.dataset.confirmed) return;
            event.preventDefault();
            const [iconTone, buttonClass, iconPath] = tones[form.dataset.confirmTone] || tones.default;
            const icon = confirmBox.querySelector('[data-confirm-icon]');
            icon.className = `mx-auto flex h-14 w-14 items-center justify-center rounded-full ${iconTone}`;
            icon.innerHTML = `<svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true"><path d="${iconPath}"/></svg>`;
            confirmBox.querySelector('[data-confirm-title]').textContent = form.dataset.confirmTitle || 'Are you sure?';
            confirmBox.querySelector('[data-confirm-message]').textContent = form.dataset.confirm;
            const ok = confirmBox.querySelector('[data-confirm-ok]');
            ok.className = buttonClass;
            ok.textContent = form.dataset.confirmButton || 'Confirm';
            confirmBox.returnValue = '';
            confirmBox.showModal();
            confirmBox.addEventListener('close', () => {
                if (confirmBox.returnValue !== 'ok') return;
                form.dataset.confirmed = '1';
                form.requestSubmit();
            }, { once: true });
        }));

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
