<x-admin.layout title="Activity log" subtitle="Every change made in this dashboard, newest first.">
    <div class="admin-card overflow-x-auto p-0">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50"><tr><th class="admin-th">When</th><th class="admin-th">Admin</th><th class="admin-th">What happened</th></tr></thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($activities as $activity)
                    <tr>
                        <td class="admin-td whitespace-nowrap" title="{{ $activity->created_at }}">{{ $activity->created_at?->diffForHumans() }}</td>
                        <td class="admin-td">{{ $activity->admin?->name ?? 'Removed admin' }}</td>
                        <td class="admin-td">{{ $activity->description }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="admin-td py-8 text-center text-slate-500">No activity yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $activities->links() }}</div>
</x-admin.layout>
