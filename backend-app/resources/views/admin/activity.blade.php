@php
    $tz = config('app.display_timezone');
    $types = \App\Http\Controllers\Admin\ActivityController::TYPES;
    $ranges = \App\Http\Controllers\Admin\ActivityController::RANGES;
    // Link that keeps the other filters and changes one.
    $link = fn (array $changes) => route('admin.activity', array_filter(array_merge($filters, $changes), fn ($value) => $value !== null && $value !== '' && $value !== 'all'));

    // Icon (Ionicons) and colours for each kind of action.
    $style = fn (string $action): array => match (true) {
        $action === 'login' => ['log-in-outline', 'bg-navy/10 text-navy'],
        str_ends_with($action, '.deleted') => ['trash-outline', 'bg-red-50 text-red-600'],
        str_ends_with($action, '.created') => ['add-circle-outline', 'bg-emerald-50 text-emerald-600'],
        str_ends_with($action, '.disabled') => ['ban-outline', 'bg-red-50 text-red-600'],
        str_ends_with($action, '.enabled') => ['checkmark-circle-outline', 'bg-emerald-50 text-emerald-600'],
        str_ends_with($action, '.password_reset') => ['key-outline', 'bg-amber-50 text-amber-600'],
        str_ends_with($action, '.exported') => ['download-outline', 'bg-sky-50 text-sky-600'],
        str_ends_with($action, '.moved') => ['swap-vertical-outline', 'bg-violet-50 text-violet-600'],
        default => ['create-outline', 'bg-brand-100 text-brand'],
    };
    $typeIcons = ['signins' => 'log-in-outline', 'users' => 'people-outline', 'questions' => 'help-circle-outline', 'content' => 'book-outline', 'results' => 'bar-chart-outline'];

    $grouped = $activities->getCollection()->groupBy(fn ($activity) => $activity->created_at->copy()->timezone($tz)->toDateString());
    $dailyMax = max(1, $daily->max('total'));
    $maxCount = max(1, max($counts));
    $filtered = $filters['type'] || $filters['admin'] || $filters['search'] !== '' || $filters['range'] !== 'all';
@endphp

<x-admin.layout title="Activity log" subtitle="Every change made in this dashboard, newest first.">
    <x-slot:actions>
        <a href="{{ route('admin.activity.export', array_filter($filters)) }}" class="admin-btn-primary h-11">
            <ion-icon name="download-outline" class="text-lg"></ion-icon>
            Download CSV
        </a>
    </x-slot:actions>

    {{-- Key figures --}}
    <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ([
            ['Total actions', $stats['total'], 'All time', 'layers', 'bg-brand-100 text-brand'],
            ['Today', $stats['today'], 'Since midnight', 'today', 'bg-emerald-50 text-emerald-600'],
            ['This week', $stats['week'], 'Last 7 days', 'calendar', 'bg-sky-50 text-sky-600'],
            ['Content changes', $stats['changes'], 'Edits, additions and deletions', 'create', 'bg-violet-50 text-violet-600'],
        ] as [$label, $value, $caption, $icon, $colors])
            <div class="admin-card flex items-center gap-4">
                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full {{ $colors }}"><ion-icon name="{{ $icon }}" class="text-2xl"></ion-icon></span>
                <div><p class="text-2xl font-bold text-navy">{{ number_format($value) }}</p><p class="text-sm font-medium text-slate-600">{{ $label }}</p><p class="text-xs text-slate-400">{{ $caption }}</p></div>
            </div>
        @endforeach
    </div>

    <div class="mt-6 grid gap-6 min-[1380px]:grid-cols-3">
        <div class="min-w-0 space-y-6 min-[1380px]:col-span-2">
            {{-- Activity chart --}}
            <section class="admin-card">
                <div class="flex flex-wrap items-start justify-between gap-2">
                    <div class="flex items-center gap-3">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-100 text-brand"><ion-icon name="stats-chart" class="text-lg"></ion-icon></span>
                        <div>
                            <h2 class="admin-card-title">Activity in the last 14 days</h2>
                            <p class="admin-card-subtitle">{{ $daily->sum('total') }} {{ Str::plural('action', $daily->sum('total')) }} · busiest {{ $daily->sortByDesc('total')->first()['total'] ? $daily->sortByDesc('total')->first()['date']->format('M j') : '—' }}</p>
                        </div>
                    </div>
                </div>
                <div class="mt-5 flex h-36 items-end gap-2 border-b border-slate-200 pb-px" role="img" aria-label="Bar chart of admin actions per day">
                    @foreach ($daily as $day)
                        <div class="group relative flex h-full flex-1 flex-col justify-end">
                            <span class="pointer-events-none absolute -top-1 left-1/2 z-10 -translate-x-1/2 -translate-y-full rounded-md bg-navy px-2 py-1 text-[11px] whitespace-nowrap text-white opacity-0 shadow transition group-hover:opacity-100">{{ $day['date']->format('M j') }}: {{ $day['total'] }}</span>
                            <div @class(['w-full rounded-t-md transition', 'bg-brand group-hover:bg-brand-600' => $day['total'] > 0, 'bg-slate-100' => $day['total'] === 0])
                                 style="height: {{ $day['total'] > 0 ? max(6, $day['total'] / $dailyMax * 100) : 4 }}%"></div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-2 flex gap-2 text-[11px] text-slate-400">
                    @foreach ($daily as $day)
                        <span class="flex-1 text-center">{{ $day['date']->isToday() ? 'Today' : $day['date']->format('M j') }}</span>
                    @endforeach
                </div>
            </section>

            <section class="admin-card p-0">
                {{-- Filters --}}
                <div class="space-y-3 border-b border-slate-100 px-6 py-4">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <nav class="flex flex-wrap gap-1.5" aria-label="Filter by type">
                            <a href="{{ $link(['type' => null]) }}" @class(['inline-flex items-center gap-1.5 rounded-full px-3.5 py-1.5 text-sm font-medium transition', 'bg-navy text-white' => ! $filters['type'], 'bg-slate-100 text-slate-600 hover:bg-slate-200' => $filters['type']])>All</a>
                            @foreach ($types as $key => [$label])
                                <a href="{{ $link(['type' => $key]) }}" @class(['inline-flex items-center gap-1.5 rounded-full px-3.5 py-1.5 text-sm font-medium transition', 'bg-navy text-white' => $filters['type'] === $key, 'bg-slate-100 text-slate-600 hover:bg-slate-200' => $filters['type'] !== $key])>
                                    <ion-icon name="{{ $typeIcons[$key] }}"></ion-icon>{{ $label }}
                                    <span @class(['text-xs', 'text-white/70' => $filters['type'] === $key, 'text-slate-400' => $filters['type'] !== $key])>{{ $counts[$key] }}</span>
                                </a>
                            @endforeach
                        </nav>
                    </div>
                    <form method="GET" class="flex flex-wrap items-center gap-3">
                        @if ($filters['type'])<input type="hidden" name="type" value="{{ $filters['type'] }}">@endif
                        <div class="relative min-w-56 flex-1">
                            <ion-icon name="search-outline" class="pointer-events-none absolute top-1/2 left-3 -translate-y-1/2 text-slate-400"></ion-icon>
                            <label for="activity-search" class="sr-only">Search activity</label>
                            <input id="activity-search" name="search" value="{{ $filters['search'] }}" placeholder="Search activity…" class="admin-input py-2 pl-9">
                        </div>
                        <label class="sr-only" for="activity-range">Time range</label>
                        <select id="activity-range" name="range" class="admin-input w-40 py-2" data-autosubmit>
                            @foreach ($ranges as $key => [$label])
                                <option value="{{ $key }}" @selected($filters['range'] === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <label class="sr-only" for="activity-admin">Admin</label>
                        <select id="activity-admin" name="admin" class="admin-input w-44 py-2" data-autosubmit>
                            <option value="">All admins</option>
                            @foreach ($admins as $admin)
                                <option value="{{ $admin->id }}" @selected($filters['admin'] === $admin->id)>{{ $admin->name }}</option>
                            @endforeach
                        </select>
                        <button class="admin-btn-navy py-2">Apply</button>
                        @if ($filtered)
                            <a href="{{ route('admin.activity') }}" class="text-sm font-medium text-slate-500 hover:text-brand">Clear</a>
                        @endif
                    </form>
                </div>

                {{-- Timeline grouped by day --}}
                <div class="px-6 py-5">
                    @forelse ($grouped as $day => $entries)
                        @php($date = \Illuminate\Support\Carbon::parse($day, $tz))
                        <div class="mb-6 last:mb-0">
                            <h3 class="mb-3 flex items-center gap-3 text-xs font-semibold tracking-wider text-slate-500 uppercase">
                                <span class="rounded-full bg-slate-100 px-2.5 py-1 text-slate-600">{{ $date->isToday() ? 'Today' : ($date->isYesterday() ? 'Yesterday' : $date->format('l, F j, Y')) }}</span>
                                <span class="h-px flex-1 bg-slate-200"></span>
                                <span class="font-medium tracking-normal text-slate-400 normal-case">{{ $entries->count() }} {{ Str::plural('action', $entries->count()) }}</span>
                            </h3>
                            <ol class="relative space-y-1 before:absolute before:top-3 before:bottom-3 before:left-[1.4rem] before:w-px before:bg-slate-200">
                                @foreach ($entries as $activity)
                                    @php([$icon, $colors] = $style($activity->action))
                                    <li class="relative flex items-start gap-4 rounded-xl p-2 transition hover:bg-slate-50">
                                        <span class="relative z-10 ml-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-full ring-4 ring-white {{ $colors }}"><ion-icon name="{{ $icon }}" class="text-lg"></ion-icon></span>
                                        <div class="min-w-0 flex-1 pt-1">
                                            <p class="text-sm font-medium text-navy">{{ $activity->description }}</p>
                                            <p class="mt-1 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-slate-500">
                                                <span class="inline-flex items-center gap-1.5">
                                                    <x-admin.avatar :name="$activity->admin?->name ?? 'Removed admin'" size="h-5 w-5 text-[9px]" />
                                                    <span class="font-medium text-slate-700">{{ $activity->admin?->name ?? 'Removed admin' }}</span>
                                                </span>
                                                <span class="text-slate-300">·</span>
                                                <span class="admin-badge bg-slate-100 py-0 text-slate-600">{{ \App\Http\Controllers\Admin\ActivityController::typeOf($activity->action) }}</span>
                                            </p>
                                        </div>
                                        <time datetime="{{ $activity->created_at->toIso8601String() }}" title="{{ $activity->created_at->copy()->timezone($tz)->format('M j, Y g:i:s A') }}" class="shrink-0 pt-1 text-right text-xs text-slate-400">
                                            <span class="block font-semibold text-slate-600">{{ $activity->created_at->copy()->timezone($tz)->format('g:i A') }}</span>
                                            {{ $activity->created_at->diffForHumans(short: true) }}
                                        </time>
                                    </li>
                                @endforeach
                            </ol>
                        </div>
                    @empty
                        <div class="py-16 text-center">
                            <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-slate-100 text-slate-400"><ion-icon name="time-outline" class="text-3xl"></ion-icon></span>
                            <p class="mt-3 font-semibold text-navy">No activity found</p>
                            <p class="text-sm text-slate-500">{{ $filtered ? 'Try another filter or clear the filters.' : 'Changes made in the dashboard will appear here.' }}</p>
                        </div>
                    @endforelse
                </div>

                @if ($activities->hasPages())
                    <div class="border-t border-slate-100 px-6 py-4">{{ $activities->links() }}</div>
                @endif
            </section>
        </div>

        <div class="grid gap-6 self-start md:max-[1379px]:grid-cols-2">
            {{-- Breakdown --}}
            <section class="admin-card">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-navy/10 text-navy"><ion-icon name="pie-chart" class="text-lg"></ion-icon></span>
                    <div><h2 class="admin-card-title">Activity by type</h2><p class="admin-card-subtitle">{{ $ranges[$filters['range']][0] }}{{ $filters['admin'] ? ' · one admin' : '' }}</p></div>
                </div>
                <ul class="mt-5 space-y-4">
                    @foreach ($types as $key => [$label])
                        <li>
                            <a href="{{ $link(['type' => $key]) }}" class="group block">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="flex items-center gap-2 text-slate-700 group-hover:text-brand"><ion-icon name="{{ $typeIcons[$key] }}" class="text-slate-400"></ion-icon>{{ $label }}</span>
                                    <span class="font-semibold text-navy">{{ $counts[$key] }}</span>
                                </div>
                                <div class="mt-1.5 h-2 rounded-full bg-slate-100"><div class="h-2 rounded-full bg-brand" style="width: {{ $counts[$key] / $maxCount * 100 }}%"></div></div>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </section>

            {{-- Most active admins --}}
            <section class="admin-card">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50 text-amber-600"><ion-icon name="trophy" class="text-lg"></ion-icon></span>
                    <div><h2 class="admin-card-title">Most active admins</h2><p class="admin-card-subtitle">All time</p></div>
                </div>
                <ul class="mt-4 divide-y divide-slate-100">
                    @forelse ($topAdmins as $row)
                        <li>
                            <a href="{{ $link(['admin' => $row->admin_id]) }}" class="flex items-center gap-3 rounded-xl py-3 transition hover:bg-slate-50">
                                <x-admin.avatar :name="$row->admin?->name ?? 'Removed admin'" />
                                <span class="min-w-0 flex-1">
                                    <span class="block truncate text-sm font-semibold text-navy">{{ $row->admin?->name ?? 'Removed admin' }}</span>
                                    <span class="block text-xs text-slate-400">Last active {{ \Illuminate\Support\Carbon::parse($row->last_at)->diffForHumans() }}</span>
                                </span>
                                <span class="admin-badge bg-brand-50 text-brand-600">{{ $row->total }} {{ Str::plural('action', $row->total) }}</span>
                            </a>
                        </li>
                    @empty
                        <li class="py-4 text-center text-sm text-slate-500">No activity yet.</li>
                    @endforelse
                </ul>
            </section>
        </div>
    </div>
</x-admin.layout>
