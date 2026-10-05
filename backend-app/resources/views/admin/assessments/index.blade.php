<x-admin.layout title="Assessment results" subtitle="Every self-assessment completed in the app.">
    <x-slot:actions>
        <a href="{{ route('admin.assessments.export', request()->only(['risk', 'role', 'search'])) }}" class="admin-btn-navy">Download CSV</a>
    </x-slot:actions>

    <form method="GET" class="admin-card mb-5 flex flex-wrap items-end gap-3">
        <div class="min-w-48 flex-1">
            <label for="search" class="admin-label">User</label>
            <input id="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Name or email" class="admin-input">
        </div>
        <div>
            <label for="risk" class="admin-label">Result</label>
            <select id="risk" name="risk" class="admin-input">
                <option value="">All</option>
                @foreach (['LOW' => 'Mild', 'MEDIUM' => 'Moderate', 'HIGH' => 'Severe'] as $value => $label)
                    <option value="{{ $value }}" @selected(($filters['risk'] ?? '') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="role" class="admin-label">User type</label>
            <select id="role" name="role" class="admin-input">
                <option value="">All</option>
                <option value="student" @selected(($filters['role'] ?? '') === 'student')>Students</option>
                <option value="professional" @selected(($filters['role'] ?? '') === 'professional')>Professionals</option>
            </select>
        </div>
        <button class="admin-btn-navy">Filter</button>
    </form>

    <div class="admin-card overflow-x-auto p-0">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50"><tr><th class="admin-th">Date</th><th class="admin-th">User</th><th class="admin-th">Type</th><th class="admin-th">Result</th><th class="admin-th"></th></tr></thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($assessments as $assessment)
                    <tr class="hover:bg-slate-50">
                        <td class="admin-td">{{ $assessment->created_at?->format('M j, Y g:i A') }}</td>
                        <td class="admin-td">
                            @if ($assessment->user)
                                <a href="{{ route('admin.users.show', $assessment->user) }}" class="flex items-center gap-3">
                                    <x-admin.avatar :name="$assessment->user->name" />
                                    <span class="min-w-0">
                                        <span class="block font-semibold text-slate-900 hover:text-brand">{{ $assessment->user->name }}</span>
                                        <span class="block text-xs text-slate-500">{{ $assessment->user->email }}</span>
                                    </span>
                                </a>
                            @else
                                <span class="text-slate-400">Deleted user</span>
                            @endif
                        </td>
                        <td class="admin-td">{{ ucfirst($assessment->user?->role ?? '') }}</td>
                        <td class="admin-td"><x-admin.risk :risk="$assessment->risk_level" :score="$assessment->total_score" :max="$assessment->max_score" /></td>
                        <td class="admin-td text-right"><a href="{{ route('admin.assessments.show', $assessment) }}" class="text-brand hover:underline">View answers</a></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="admin-td py-8 text-center text-slate-500">No results match these filters.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $assessments->links() }}</div>
</x-admin.layout>
