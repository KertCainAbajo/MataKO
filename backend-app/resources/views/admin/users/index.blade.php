<x-admin.layout title="Users" subtitle="Everyone who has signed up for the MataKo app.">
    <form method="GET" class="admin-card mb-5 flex flex-wrap items-end gap-3">
        <div class="min-w-48 flex-1">
            <label for="search" class="admin-label">Search</label>
            <input id="search" name="search" value="{{ $search }}" placeholder="Name or email" class="admin-input">
        </div>
        <div>
            <label for="role" class="admin-label">Type</label>
            <select id="role" name="role" class="admin-input">
                <option value="">All</option>
                @foreach (['student' => 'Students', 'professional' => 'Professionals', 'admin' => 'Admins'] as $value => $label)
                    <option value="{{ $value }}" @selected($role === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="status" class="admin-label">Status</label>
            <select id="status" name="status" class="admin-input">
                <option value="">All</option>
                <option value="active" @selected($status === 'active')>Active</option>
                <option value="disabled" @selected($status === 'disabled')>Disabled</option>
            </select>
        </div>
        <button class="admin-btn-navy">Filter</button>
    </form>

    <div class="admin-card overflow-x-auto p-0">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="admin-th">User</th>
                    <th class="admin-th">Type</th>
                    <th class="admin-th">Assessments</th>
                    <th class="admin-th">Last active</th>
                    <th class="admin-th">Joined</th>
                    <th class="admin-th">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($users as $user)
                    @php($lastActive = collect([$user->last_login_at, $user->last_active_at ? \Illuminate\Support\Carbon::parse($user->last_active_at) : null])->filter()->max())
                    <tr class="hover:bg-slate-50">
                        <td class="admin-td">
                            <a href="{{ route('admin.users.show', $user) }}" class="flex items-center gap-3">
                                <x-admin.avatar :name="$user->name" />
                                <span class="min-w-0">
                                    <span class="block font-semibold text-slate-900 hover:text-brand">{{ $user->name }}</span>
                                    <span class="block text-xs text-slate-500">{{ $user->email }}</span>
                                </span>
                            </a>
                        </td>
                        <td class="admin-td">
                            {{ ucfirst($user->role) }}
                            @if ($user->is_admin)
                                <span class="admin-badge ml-1 bg-navy text-white">Admin</span>
                            @endif
                        </td>
                        <td class="admin-td">{{ $user->assessments_count }}</td>
                        <td class="admin-td">{{ $lastActive?->diffForHumans() ?? 'Never' }}</td>
                        <td class="admin-td">{{ $user->created_at?->format('M j, Y') }}</td>
                        <td class="admin-td">
                            @if ($user->isDisabled())
                                <span class="admin-badge bg-red-50 text-red-700"><span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>Disabled</span>
                            @else
                                <span class="admin-badge bg-emerald-50 text-emerald-700"><span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>Active</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="admin-td py-8 text-center text-slate-500">No users match these filters.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $users->links() }}</div>
</x-admin.layout>
