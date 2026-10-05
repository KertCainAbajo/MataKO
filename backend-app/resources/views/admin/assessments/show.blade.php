<x-admin.layout :title="'Assessment #'.$assessment->id">
    @php($answerLabels = $assessment->max_score === 15 ? ['None', 'Sometimes', 'Often', 'Always'] : ['Never', 'Occasionally', 'Often or Always'])

    <div class="grid gap-6 xl:grid-cols-3">
        <section class="admin-card">
            <h2 class="admin-card-title">Summary</h2>
            <dl class="mt-4 space-y-3 text-sm">
                <div class="flex justify-between"><dt class="text-slate-500">User</dt><dd class="font-medium">
                    @if ($assessment->user)
                        <a href="{{ route('admin.users.show', $assessment->user) }}" class="text-navy hover:text-brand">{{ $assessment->user->name }}</a>
                    @else
                        Deleted user
                    @endif
                </dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Type</dt><dd class="font-medium">{{ ucfirst($assessment->user?->role ?? '—') }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Date</dt><dd class="font-medium">{{ $assessment->created_at?->format('M j, Y g:i A') }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Result</dt><dd><x-admin.risk :risk="$assessment->risk_level" :score="$assessment->total_score" :max="$assessment->max_score" /></dd></div>
            </dl>
            <details class="mt-6">
                <summary class="cursor-pointer text-sm font-semibold text-red-700">Delete this result</summary>
                <form method="POST" action="{{ route('admin.assessments.destroy', $assessment) }}" class="mt-3">
                    @csrf @method('DELETE')
                    <button class="admin-btn-danger">Delete result</button>
                </form>
            </details>
        </section>

        <section class="admin-card overflow-x-auto p-0 xl:col-span-2">
            <h2 class="admin-card-title px-6 pt-6">Answers</h2>
            <table class="mt-3 min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50"><tr><th class="admin-th">Symptom</th><th class="admin-th">Answer</th><th class="admin-th">Points</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($assessment->symptoms as $symptom)
                        <tr>
                            <td class="admin-td">{{ $symptom->symptom_name }}</td>
                            <td class="admin-td">{{ $answerLabels[$symptom->value] ?? $symptom->value }}</td>
                            <td class="admin-td">{{ $symptom->value }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>
    </div>
</x-admin.layout>
