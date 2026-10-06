<x-admin.layout title="Dashboard" :subtitle="'Overview for '.now(config('app.display_timezone'))->format('l, F j, Y')">
    <x-slot:actions>
        <a href="{{ route('admin.questions.create') }}" class="admin-btn-primary h-11">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            New question
        </a>
    </x-slot:actions>

    @php
        $tz = config('app.display_timezone');
        $change = fn (int $count, string $unit) => $count > 0 ? ['up', "{$count} new this week"] : ['flat', "No change this week"];
        $cards = [
            [$stats['students'], 'Total Students', $change($stats['newStudents'], 'student'), $sparklines['students'], '#F58216', 'bg-brand-100 text-brand', route('admin.users.index', ['role' => 'student']),
                'M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z'],
            [$stats['professionals'], 'Professional Users', $change($stats['newProfessionals'], 'professional'), $sparklines['professionals'], '#062A3C', 'bg-navy/10 text-navy', route('admin.users.index', ['role' => 'professional']),
                'M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 0 0 .75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 0 0-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0 1 12 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 0 1-.673-.38m0 0A2.18 2.18 0 0 1 3 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 0 1 3.413-.387m7.5 0V5.25A2.25 2.25 0 0 0 13.5 3h-3a2.25 2.25 0 0 0-2.25 2.25v.894m7.5 0a48.667 48.667 0 0 0-7.5 0M12 12.75h.008v.008H12v-.008Z'],
            [$stats['assessments'], 'Total Assessments', $stats['assessmentsThisWeek'] > 0 ? ['up', "+{$stats['assessmentsThisWeek']} this week"] : ['flat', 'None completed this week'], $sparklines['assessments'], '#1DB815', 'bg-emerald-50 text-emerald-600', route('admin.assessments.index'),
                'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9-6h.008v.008H12v-.008ZM12 15h.008v.008H12V15Zm0 2.25h.008v.008H12v-.008ZM9.75 15h.008v.008H9.75V15Zm0 2.25h.008v.008H9.75v-.008ZM7.5 15h.008v.008H7.5V15Zm0 2.25h.008v.008H7.5v-.008Zm6.75-4.5h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V15Zm0 2.25h.008v.008h-.008v-.008Zm2.25-4.5h.008v.008H16.5v-.008Zm0 2.25h.008v.008H16.5V15Z'],
            [$stats['averageScore'] === null ? '—' : $stats['averageScore'].'%', 'Average Strain Score', $stats['averageChange'] === null ? ['flat', 'No data last week'] : ($stats['averageChange'] === 0 ? ['flat', 'Same as last week'] : [$stats['averageChange'] > 0 ? 'bad' : 'good', ($stats['averageChange'] > 0 ? '+' : '').$stats['averageChange'].'% from last week']), $sparklines['score'], '#E5A000', 'bg-amber-50 text-amber-600', route('admin.assessments.index'),
                'm8.99 14.993 6-6m6 3.001c0 1.268-.63 2.39-1.593 3.069a3.746 3.746 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043 3.745 3.745 0 0 1-3.068 1.593c-1.268 0-2.39-.63-3.068-1.593a3.745 3.745 0 0 1-3.296-1.043 3.746 3.746 0 0 1-1.043-3.297 3.746 3.746 0 0 1-1.593-3.068c0-1.268.63-2.39 1.593-3.068a3.746 3.746 0 0 1 1.043-3.297 3.745 3.745 0 0 1 3.296-1.042 3.745 3.745 0 0 1 3.068-1.594c1.268 0 2.39.63 3.068 1.593a3.745 3.745 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.297 3.746 3.746 0 0 1 1.593 3.068ZM9.74 9.743h.008v.007H9.74v-.007Zm.374 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm4.125 4.5h.008v.008h-.008v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z'],
        ];

        $riskTotal = array_sum($riskCounts);

        $dailyMax = max(1, max($daily));
        $axisMax = max(4, (int) ceil($dailyMax / 4) * 4);
        $busiestIndex = array_search(max($daily), $daily, true);
        $dailyTotal = (int) array_sum($daily);
    @endphp

    {{-- Key figures --}}
    <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($cards as [$value, $label, [$trend, $trendText], $spark, $color, $iconClasses, $link, $iconPath])
            <a href="{{ $link }}" class="admin-card flex flex-col gap-4 transition hover:-translate-y-0.5 hover:shadow-md">
                <div class="flex items-center gap-4">
                    <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full {{ $iconClasses }}">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true"><path d="{{ $iconPath }}"/></svg>
                    </span>
                    <div class="min-w-0">
                        <p class="text-3xl leading-tight font-bold tracking-tight text-navy">{{ is_numeric($value) ? number_format($value) : $value }}</p>
                        <p class="truncate text-sm font-medium text-slate-600">{{ $label }}</p>
                    </div>
                </div>
                <div class="flex items-end justify-between gap-3">
                    <p @class(['flex min-w-0 items-center gap-1 text-xs', 'text-emerald-600' => in_array($trend, ['up', 'good'], true), 'text-red-600' => $trend === 'bad', 'text-slate-500' => $trend === 'flat'])>
                        @if (in_array($trend, ['up', 'bad'], true))<span aria-hidden="true">▲</span>@elseif ($trend === 'good')<span aria-hidden="true">▼</span>@endif
                        <span class="truncate">{{ $trendText }}</span>
                    </p>
                    <x-admin.sparkline :values="$spark" :color="$color" class="h-8 w-24 shrink-0" />
                </div>
            </a>
        @endforeach
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-3">
        {{-- Assessments chart --}}
        <section class="admin-card xl:col-span-2">
            <div class="flex flex-wrap items-start justify-between gap-2">
                <div class="flex items-start gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-100 text-brand">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg>
                    </span>
                    <div>
                        <h2 class="admin-card-title">Assessments in the last 14 days</h2>
                        <p class="admin-card-subtitle">{{ $dailyTotal }} {{ Str::plural('completion', $dailyTotal) }}@if ($dailyTotal) · Busiest, {{ $days[$busiestIndex]->format('M j') }}@endif</p>
                    </div>
                </div>
                <a href="{{ route('admin.assessments.index') }}" class="text-sm font-semibold text-brand hover:text-brand-600">View all results →</a>
            </div>

            <div class="mt-6 flex gap-3">
                {{-- Y axis --}}
                <div class="flex h-56 flex-col justify-between pb-6 text-right text-xs text-slate-400">
                    @foreach (range(4, 0) as $step)
                        <span class="-translate-y-1.5">{{ (int) ($axisMax / 4 * $step) }}</span>
                    @endforeach
                </div>
                <div class="relative flex-1">
                    <div class="pointer-events-none absolute inset-x-0 top-0 bottom-6 flex flex-col justify-between">
                        @foreach (range(1, 5) as $line)
                            <span class="border-t border-dashed border-slate-200"></span>
                        @endforeach
                    </div>
                    <div class="relative flex h-56 items-end gap-2 pb-6" role="img" aria-label="Bar chart of assessments per day for the last 14 days">
                        @foreach ($daily as $index => $count)
                            @php($highlight = $dailyTotal > 0 && $index === $busiestIndex)
                            <div class="group relative flex h-full flex-1 flex-col items-center justify-end">
                                @if ($highlight)
                                    <span class="absolute -top-1 z-10 -translate-y-full rounded-md bg-navy px-2 py-1 text-[11px] font-semibold whitespace-nowrap text-white shadow">{{ (int) $count }} {{ Str::plural('result', (int) $count) }}</span>
                                @else
                                    <span class="pointer-events-none absolute -top-1 z-10 -translate-y-full rounded-md bg-slate-900 px-2 py-1 text-[11px] whitespace-nowrap text-white opacity-0 shadow transition group-hover:opacity-100">{{ (int) $count }} {{ Str::plural('result', (int) $count) }}</span>
                                @endif
                                <div @class(['w-full max-w-9 rounded-t-md transition', 'bg-brand' => $highlight, 'bg-brand-100 group-hover:bg-brand/60' => ! $highlight && $count > 0, 'bg-transparent' => $count == 0])
                                     style="height: {{ $count > 0 ? max(4, $count / $axisMax * 100) : 0 }}%"></div>
                                <span class="absolute -bottom-0.5 translate-y-full text-[11px] whitespace-nowrap text-slate-400">{{ $days[$index]->format('M j') }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        {{-- Results by strain level --}}
        <section class="admin-card">
            <div class="flex items-start gap-3">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-navy/10 text-navy">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"/></svg>
                </span>
                <div>
                    <h2 class="admin-card-title">Results by strain level</h2>
                    <p class="admin-card-subtitle">All assessments · to date</p>
                </div>
            </div>
            <x-admin.strain-summary :risk-counts="$riskCounts" :students="$stats['students']" :professionals="$stats['professionals']" class="mt-6" />
        </section>
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-3">
        {{-- Latest assessments --}}
        <section class="admin-card overflow-hidden p-0 xl:col-span-2">
            <div class="flex items-start justify-between gap-3 px-6 pt-6 pb-4">
                <div class="flex items-start gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-100 text-brand">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
                    </span>
                    <div>
                        <h2 class="admin-card-title">Latest assessments</h2>
                        <p class="admin-card-subtitle">The most recent self-assessments from the app</p>
                    </div>
                </div>
                <a href="{{ route('admin.assessments.index') }}" class="text-sm font-semibold text-brand hover:text-brand-600">View all →</a>
            </div>
            <div class="overflow-x-auto px-6 pb-4">
                <table class="min-w-full">
                    <thead><tr><th class="admin-th rounded-l-lg">User</th><th class="admin-th">Type</th><th class="admin-th">When</th><th class="admin-th">Result</th><th class="admin-th rounded-r-lg"><span class="sr-only">Open</span></th></tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($recentAssessments as $assessment)
                            <tr class="group transition hover:bg-slate-50/70">
                                <td class="admin-td">
                                    <span class="flex items-center gap-3">
                                        <x-admin.avatar :name="$assessment->user?->name ?? '?'" />
                                        <span class="font-medium text-navy">{{ $assessment->user?->name ?? 'Deleted user' }}</span>
                                    </span>
                                </td>
                                <td class="admin-td">{{ ucfirst($assessment->user?->role ?? '—') }}</td>
                                <td class="admin-td text-slate-500">{{ $assessment->created_at?->diffForHumans() }}</td>
                                <td class="admin-td"><x-admin.risk :risk="$assessment->risk_level" :score="$assessment->total_score" :max="$assessment->max_score" /></td>
                                <td class="admin-td text-right">
                                    <a href="{{ route('admin.assessments.show', $assessment) }}" class="inline-flex rounded-lg p-1.5 text-slate-400 transition group-hover:text-brand" aria-label="View answers">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="admin-td py-10 text-center text-slate-500">No assessments yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <div class="space-y-6">
            <x-admin.quick-actions />

            <x-admin.recent-activity :activities="$recentActivity" />
        </div>
    </div>
</x-admin.layout>
