@props(['title', 'subtitle' => null])

@php
    $sections = [
        'Overview' => [
            ['admin.dashboard', 'admin.dashboard', 'Dashboard', 'M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6Zm0 9.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6Zm0 9.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z'],
            ['admin.assessments.index', 'admin.assessments.*', 'Results', 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z'],
        ],
        'Manage' => [
            ['admin.users.index', 'admin.users.*', 'Users', 'M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z'],
            ['admin.questions.index', 'admin.questions.*', 'Questions', 'M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z'],
            ['admin.tips.index', 'admin.tips.*', 'App Content', 'M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25'],
        ],
        'System' => [
            ['admin.activity', 'admin.activity', 'Activity Log', 'M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
        ],
    ];
    $admin = auth()->user();
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
</head>
<body class="h-full bg-slate-100/70 font-admin text-slate-900 antialiased">
    <div class="min-h-full lg:flex">
        {{-- Sidebar --}}
        <aside class="relative flex flex-col bg-gradient-to-b from-navy-900 via-navy to-navy-800 text-white lg:fixed lg:inset-y-0 lg:w-64">
            <div class="flex items-center gap-3 px-6 pt-6 pb-5">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white shadow-lg shadow-black/20">
                    <img src="{{ asset('images/matako-eye.png') }}" alt="" class="h-5 w-8 object-contain">
                </div>
                <div class="leading-tight">
                    <p class="text-base font-bold tracking-tight">MataKo</p>
                    <p class="text-xs font-medium text-orange-300/90">Admin Console</p>
                </div>
            </div>

            <nav class="flex gap-1 overflow-x-auto px-3 pb-4 lg:flex-1 lg:flex-col lg:gap-6 lg:overflow-y-auto lg:pt-2">
                @foreach ($sections as $section => $links)
                    <div class="flex gap-1 lg:block lg:space-y-1">
                        <p class="hidden px-3 pb-1 text-[11px] font-semibold tracking-widest text-slate-400/80 uppercase lg:block">{{ $section }}</p>
                        @foreach ($links as [$route, $pattern, $label, $path])
                            @php($active = request()->routeIs($pattern))
                            <a href="{{ route($route) }}" @class([
                                'group relative flex shrink-0 items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition',
                                'bg-white/10 text-white shadow-inner shadow-white/5' => $active,
                                'text-slate-300 hover:bg-white/5 hover:text-white' => ! $active,
                            ]) @if ($active) aria-current="page" @endif>
                                @if ($active)
                                    <span class="absolute inset-y-2 left-0 hidden w-1 rounded-r-full bg-brand lg:block"></span>
                                @endif
                                <svg @class(['h-5 w-5 shrink-0', 'text-brand' => $active, 'text-slate-400 group-hover:text-slate-200' => ! $active]) fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true"><path d="{{ $path }}"/></svg>
                                {{ $label }}
                            </a>
                        @endforeach
                    </div>
                @endforeach
            </nav>

            <div class="hidden border-t border-white/10 p-4 lg:block">
                <div class="flex items-center gap-3 rounded-xl bg-white/5 p-3">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-brand text-sm font-bold text-white">{{ $initials }}</span>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-semibold">{{ $admin->name }}</p>
                        <p class="truncate text-xs text-slate-400">{{ $admin->email }}</p>
                    </div>
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button class="rounded-lg p-2 text-slate-400 transition hover:bg-white/10 hover:text-white" title="Sign out" aria-label="Sign out">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true"><path d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9"/></svg>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        {{-- Main --}}
        <div class="flex min-h-full flex-1 flex-col lg:pl-64">
            <header class="sticky top-0 z-10 border-b border-slate-200/80 bg-white/80 backdrop-blur">
                <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-4 px-4 py-4 sm:px-8">
                    <div class="min-w-0">
                        <p class="text-xs font-medium text-slate-500">MataKo Admin <span class="mx-1 text-slate-300">/</span> {{ $title }}</p>
                        <h1 class="mt-0.5 truncate text-2xl font-bold tracking-tight text-slate-900">{{ $title }}</h1>
                        @if ($subtitle)
                            <p class="mt-0.5 text-sm text-slate-500">{{ $subtitle }}</p>
                        @endif
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        {{ $actions ?? '' }}
                        <form method="POST" action="{{ route('admin.logout') }}" class="lg:hidden">
                            @csrf
                            <button class="admin-btn-secondary">Sign out</button>
                        </form>
                    </div>
                </div>
            </header>

            <main class="mx-auto w-full max-w-7xl flex-1 px-4 py-8 sm:px-8">
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

            <footer class="mx-auto w-full max-w-7xl px-4 pb-6 text-xs text-slate-400 sm:px-8">MataKo · Digital eye strain self-assessment · Admin console</footer>
        </div>
    </div>
</body>
</html>
