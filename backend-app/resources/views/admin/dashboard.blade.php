<x-admin.layout title="Dashboard" :subtitle="'Overview for '.now()->format('l, F j, Y')">
    <x-slot:actions>
        <a href="{{ route('admin.assessments.export') }}" class="admin-btn-secondary">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
            Export results
        </a>
        <a href="{{ route('admin.questions.create') }}" class="admin-btn-primary">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            New question
        </a>
    </x-slot:actions>

    @php
        $cards = [
            ['Total users', $stats['users'], "{$stats['students']} students · {$stats['professionals']} professionals", route('admin.users.index'), 'bg-sky-50 text-sky-600', 'M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z'],
            ['Active today', $stats['activeToday'], "+{$stats['newThisWeek']} new users this week", route('admin.users.index'), 'bg-emerald-50 text-emerald-600', 'M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75Z'],
            ['Assessments', $stats['assessments'], "{$stats['assessmentsThisWeek']} this week · {$assessedUsers} users assessed", route('admin.assessments.index'), 'bg-brand-50 text-brand', 'M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z'],
            ['Average score', $averageScore === null ? '—' : "{$averageScore}%", "Across {$stats['questions']} active questions", route('admin.assessments.index'), 'bg-violet-50 text-violet-600', 'M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z'],
        ];
        $riskLabels = ['LOW' => ['Mild', '#16a34a', 'bg-green-600'], 'MEDIUM' => ['Moderate', '#f58216', 'bg-brand'], 'HIGH' => ['Severe', '#dc2626', 'bg-red-600']];
        $riskTotal = array_sum($riskCounts);
        // Conic-gradient stops for the ring chart.
        $stops = [];
        $start = 0;
        foreach ($riskLabels as $risk => [, $color]) {
            $end = $riskTotal ? $start + $riskCounts[$risk] / $riskTotal * 100 : $start;
            $stops[] = "{$color} {$start}% {$end}%";
            $start = $end;
        }
        $ring = $riskTotal ? 'conic-gradient('.implode(', ', $stops).')' : 'conic-gradient(#e2e8f0 0 100%)';
        $dailyMax = max(1, $daily->max('total'));
        $dailyTotal = $daily->sum('total');
    @endphp

    {{-- Key figures --}}
    <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($cards as [$label, $value, $detail, $link, $iconClasses, $iconPath])
            <a href="{{ $link }}" class="admin-card group transition hover:-translate-y-0.5 hover:shadow-md">
                <div class="flex items-start justify-between">
                    <p class="text-sm font-medium text-slate-500">{{ $label }}</p>
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl {{ $iconClasses }}">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true"><path d="{{ $iconPath }}"/></svg>
                    </span>
                </div>
                <p class="mt-3 text-3xl font-bold tracking-tight text-slate-900">{{ is_numeric($value) ? number_format($value) : $value }}</p>
                <p class="mt-1 text-xs text-slate-500">{{ $detail }}</p>
            </a>
        @endforeach
    </div>

    @if ($stats['disabled'] > 0)
        <p class="mt-4 text-sm text-slate-500">
            <span class="admin-badge bg-red-50 text-red-700">{{ $stats['disabled'] }} disabled {{ Str::plural('account', $stats['disabled']) }}</span>
        </p>
    @endif

    <div class="mt-6 grid gap-6 xl:grid-cols-3">
        {{-- Activity chart --}}
        <section class="admin-card xl:col-span-2">
            <div class="flex flex-wrap items-start justify-between gap-2">
                <div>
                    <h2 class="admin-card-title">Assessments in the last 14 days</h2>
                    <p class="admin-card-subtitle">{{ $dailyTotal }} completed · busiest day {{ $daily->sortByDesc('total')->first()['total'] ? $daily->sortByDesc('total')->first()['date']->format('M j') : '—' }}</p>
                </div>
                <a href="{{ route('admin.assessments.index') }}" class="text-sm font-semibold text-brand hover:text-brand-600">View all results →</a>
            </div>
            <div class="mt-6 flex h-52 items-end gap-2 border-b border-slate-200 pb-px" role="img" aria-label="Bar chart of assessments per day">
                @foreach ($daily as $day)
                    <div class="group relative flex h-full flex-1 flex-col justify-end">
                        <span class="pointer-events-none absolute -top-1 left-1/2 z-10 -translate-x-1/2 -translate-y-full rounded-md bg-slate-900 px-2 py-1 text-xs whitespace-nowrap text-white opacity-0 shadow transition group-hover:opacity-100">{{ $day['date']->format('M j') }}: {{ $day['total'] }}</span>
                        <div @class(['w-full rounded-t-md transition', 'bg-brand group-hover:bg-brand-600' => $day['total'] > 0, 'bg-slate-100' => $day['total'] === 0])
                             style="height: {{ $day['total'] > 0 ? max(6, $day['total'] / $dailyMax * 100) : 3 }}%"></div>
                    </div>
                @endforeach
            </div>
            <div class="mt-2 flex gap-2 text-[11px] text-slate-400">
                @foreach ($daily as $day)
                    <span class="flex-1 text-center">{{ $loop->index % 2 === 0 || $loop->last ? $day['date']->format('j') : '' }}</span>
                @endforeach
            </div>
        </section>

        {{-- Risk distribution --}}
        <section class="admin-card">
            <h2 class="admin-card-title">Results by strain level</h2>
            <p class="admin-card-subtitle">All assessments to date</p>
            <div class="mt-6 flex items-center gap-6">
                <div class="relative h-32 w-32 shrink-0 rounded-full" style="background: {{ $ring }}">
                    <div class="absolute inset-4 flex flex-col items-center justify-center rounded-full bg-white">
                        <span class="text-2xl font-bold text-slate-900">{{ $riskTotal }}</span>
                        <span class="text-xs text-slate-500">results</span>
                    </div>
                </div>
                <ul class="flex-1 space-y-3">
                    @foreach ($riskLabels as $risk => [$label, , $dot])
                        <li class="flex items-center justify-between text-sm">
                            <span class="flex items-center gap-2 text-slate-600"><span class="h-2.5 w-2.5 rounded-full {{ $dot }}"></span>{{ $label }}</span>
                            <span class="font-semibold text-slate-900">{{ $riskCounts[$risk] }} <span class="font-normal text-slate-400">· {{ $riskTotal ? round($riskCounts[$risk] / $riskTotal * 100) : 0 }}%</span></span>
                        </li>
                    @endforeach
                </ul>
            </div>
            <div class="mt-6 grid grid-cols-2 gap-3 border-t border-slate-100 pt-5 text-center">
                <div class="rounded-xl bg-slate-50 py-3">
                    <p class="text-lg font-bold text-slate-900">{{ $stats['students'] }}</p>
                    <p class="text-xs text-slate-500">Students</p>
                </div>
                <div class="rounded-xl bg-slate-50 py-3">
                    <p class="text-lg font-bold text-slate-900">{{ $stats['professionals'] }}</p>
                    <p class="text-xs text-slate-500">Professionals</p>
                </div>
            </div>
        </section>
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-3">
        {{-- Latest results --}}
        <section class="admin-card overflow-hidden p-0 xl:col-span-2">
            <div class="flex items-center justify-between px-6 pt-6 pb-4">
                <div>
                    <h2 class="admin-card-title">Latest assessments</h2>
                    <p class="admin-card-subtitle">The most recent self-assessments from the app</p>
                </div>
                <a href="{{ route('admin.assessments.index') }}" class="text-sm font-semibold text-brand hover:text-brand-600">View all</a>
            </div>
            <table class="min-w-full divide-y divide-slate-100">
                <thead><tr><th class="admin-th">User</th><th class="admin-th">Type</th><th class="admin-th">When</th><th class="admin-th text-right">Result</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($recentAssessments as $assessment)
                        <tr class="transition hover:bg-slate-50/70">
                            <td class="admin-td">
                                <a href="{{ route('admin.assessments.show', $assessment) }}" class="flex items-center gap-3">
                                    <x-admin.avatar :name="$assessment->user?->name ?? '?'" />
                                    <span class="font-medium text-slate-900">{{ $assessment->user?->name ?? 'Deleted user' }}</span>
                                </a>
                            </td>
                            <td class="admin-td">{{ ucfirst($assessment->user?->role ?? '—') }}</td>
                            <td class="admin-td text-slate-500">{{ $assessment->created_at?->diffForHumans() }}</td>
                            <td class="admin-td text-right"><x-admin.risk :risk="$assessment->risk_level" :score="$assessment->total_score" :max="$assessment->max_score" /></td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="admin-td py-10 text-center text-slate-500">No assessments yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </section>

        <div class="space-y-6">
            {{-- Quick actions --}}
            <section class="admin-card">
                <h2 class="admin-card-title">Quick actions</h2>
                <div class="mt-4 grid grid-cols-2 gap-3">
                    @foreach ([
                        ['Add question', route('admin.questions.create'), 'M12 4.5v15m7.5-7.5h-15'],
                        ['Manage users', route('admin.users.index'), 'M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z'],
                        ["Today's tip", route('admin.tips.index', ['tab' => 'daily_tip']), 'M12 18v-5.25m0 0a6.01 6.01 0 0 0 1.5-.189m-1.5.189a6.01 6.01 0 0 1-1.5-.189m3.75 7.478a12.06 12.06 0 0 1-4.5 0m3.75 2.383a14.406 14.406 0 0 1-3 0M14.25 18v-.192c0-.983.658-1.823 1.508-2.316a7.5 7.5 0 1 0-7.517 0c.85.493 1.509 1.333 1.509 2.316V18'],
                        ['Translations', route('admin.tips.index', ['tab' => 'topics']), 'm10.5 21 5.25-11.25L21 21m-9-3h7.5M3 5.621a48.474 48.474 0 0 1 6-.371m0 0c1.12 0 2.233.038 3.334.114M9 5.25V3m3.334 2.364C11.176 10.658 7.69 15.08 3 17.502m9.334-12.138c.896.061 1.785.147 2.666.257m-4.589 8.495a18.023 18.023 0 0 1-3.827-5.802'],
                    ] as [$label, $link, $path])
                        <a href="{{ $link }}" class="flex flex-col items-start gap-2 rounded-xl border border-slate-200 p-3 text-sm font-medium text-slate-700 transition hover:border-brand/40 hover:bg-brand-50 hover:text-navy">
                            <svg class="h-5 w-5 text-brand" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true"><path d="{{ $path }}"/></svg>
                            {{ $label }}
                        </a>
                    @endforeach
                </div>
            </section>

            {{-- Admin activity --}}
            <section class="admin-card">
                <div class="flex items-center justify-between">
                    <h2 class="admin-card-title">Recent admin activity</h2>
                    <a href="{{ route('admin.activity') }}" class="text-sm font-semibold text-brand hover:text-brand-600">View all</a>
                </div>
                <ol class="mt-4 space-y-4">
                    @forelse ($recentActivity as $activity)
                        <li class="relative flex gap-3 text-sm">
                            <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-brand ring-4 ring-brand-50"></span>
                            <div class="min-w-0">
                                <p class="text-slate-700"><span class="font-semibold text-slate-900">{{ $activity->admin?->name ?? 'Removed admin' }}</span> · {{ $activity->description }}</p>
                                <p class="text-xs text-slate-400">{{ $activity->created_at?->diffForHumans() }}</p>
                            </div>
                        </li>
                    @empty
                        <li class="py-4 text-center text-sm text-slate-500">No activity yet.</li>
                    @endforelse
                </ol>
            </section>
        </div>
    </div>
</x-admin.layout>
