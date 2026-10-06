<x-admin.layout title="Assessment results" subtitle="Every self-assessment completed in the app.">
    <x-slot:actions>
        <a href="{{ route('admin.assessments.export', request()->only(['risk', 'role', 'search'])) }}" class="admin-btn-primary h-11">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
            Download CSV
        </a>
    </x-slot:actions>

    <x-admin.filter-bar>
        <div class="min-w-56 flex-1">
            <label for="search" class="admin-label">User</label>
            <div class="relative">
                <svg class="pointer-events-none absolute top-1/2 left-3.5 h-5 w-5 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg>
                <input id="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Name or email" class="admin-input pl-11">
            </div>
        </div>
        <div class="w-40">
            <label for="risk" class="admin-label">Result</label>
            <select id="risk" name="risk" class="admin-input">
                <option value="">All</option>
                @foreach (['LOW' => 'Mild', 'MEDIUM' => 'Moderate', 'HIGH' => 'Severe'] as $value => $label)
                    <option value="{{ $value }}" @selected(($filters['risk'] ?? '') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="w-40">
            <label for="role" class="admin-label">User type</label>
            <select id="role" name="role" class="admin-input">
                <option value="">All</option>
                <option value="student" @selected(($filters['role'] ?? '') === 'student')>Students</option>
                <option value="professional" @selected(($filters['role'] ?? '') === 'professional')>Professionals</option>
            </select>
        </div>
    </x-admin.filter-bar>

    <div class="mt-6 grid gap-6 min-[1380px]:grid-cols-3">
        <section class="admin-card overflow-hidden p-0 min-[1380px]:col-span-2">
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead><tr><th class="admin-th pl-6">Date</th><th class="admin-th">User</th><th class="admin-th">Type</th><th class="admin-th">Result</th><th class="admin-th pr-6"><span class="sr-only">Answers</span></th></tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($assessments as $assessment)
                            <tr class="transition hover:bg-slate-50/70">
                                <td class="admin-td pl-6 whitespace-nowrap text-navy">{{ $assessment->created_at?->copy()->timezone(config('app.display_timezone'))->format('M j, Y') }}<span class="block text-xs text-slate-500">{{ $assessment->created_at?->copy()->timezone(config('app.display_timezone'))->format('g:i A') }}</span></td>
                                <td class="admin-td">
                                    @if ($assessment->user)
                                        <a href="{{ route('admin.users.show', $assessment->user) }}" class="flex items-center gap-3">
                                            <x-admin.avatar :name="$assessment->user->name" />
                                            <span class="min-w-0">
                                                <span class="block font-semibold text-navy hover:text-brand">{{ $assessment->user->name }}</span>
                                                <span class="block max-w-48 truncate text-xs text-slate-500">{{ $assessment->user->email }}</span>
                                            </span>
                                        </a>
                                    @else
                                        <span class="text-slate-400">Deleted user</span>
                                    @endif
                                </td>
                                <td class="admin-td">{{ ucfirst($assessment->user?->role ?? '—') }}</td>
                                <td class="admin-td"><x-admin.risk :risk="$assessment->risk_level" :score="$assessment->total_score" :max="$assessment->max_score" /></td>
                                <td class="admin-td pr-6 text-right whitespace-nowrap">
                                    <a href="{{ route('admin.assessments.show', $assessment) }}" class="text-sm font-semibold text-brand hover:text-brand-600">View →</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="admin-td py-12 text-center text-slate-500">No results match these filters.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($assessments->hasPages())
                <div class="border-t border-slate-100 px-6 py-4">{{ $assessments->links() }}</div>
            @endif
        </section>

        <section class="admin-card self-start">
            <div class="flex items-center gap-3">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-100 text-brand">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z"/></svg>
                </span>
                <div>
                    <h2 class="admin-card-title">Quick summary</h2>
                    <p class="admin-card-subtitle">{{ array_filter(request()->only(['risk', 'role', 'search'])) ? 'For the filtered results' : 'All results to date' }}</p>
                </div>
            </div>
            <x-admin.strain-summary :risk-counts="$riskCounts" :students="$students" :professionals="$professionals" label="results" class="mt-6" />
        </section>
    </div>
</x-admin.layout>
