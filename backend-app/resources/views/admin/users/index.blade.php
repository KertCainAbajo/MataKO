<x-admin.layout title="Users" subtitle="Everyone who has signed up for the MataKo app.">
    <x-admin.filter-bar>
        <div class="min-w-56 flex-1">
            <label for="search" class="admin-label">Search</label>
            <div class="relative">
                <svg class="pointer-events-none absolute top-1/2 left-3.5 h-5 w-5 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                <input id="search" name="search" value="{{ $search }}" placeholder="Name or email" class="admin-input pl-11">
            </div>
        </div>
        <div class="w-40">
            <label for="role" class="admin-label">Type</label>
            <select id="role" name="role" class="admin-input">
                <option value="">All</option>
                @foreach (['student' => 'Students', 'professional' => 'Professionals', 'admin' => 'Admins'] as $value => $label)
                    <option value="{{ $value }}" @selected($role === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="w-40">
            <label for="status" class="admin-label">Status</label>
            <select id="status" name="status" class="admin-input">
                <option value="">All</option>
                <option value="active" @selected($status === 'active')>Active</option>
                <option value="disabled" @selected($status === 'disabled')>Disabled</option>
            </select>
        </div>
    </x-admin.filter-bar>

    <div class="mt-6 grid gap-6 min-[1380px]:grid-cols-3">
        <section class="admin-card overflow-hidden p-0 min-[1380px]:col-span-2 self-start">
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr>
                            <th class="admin-th pl-6">User</th>
                            <th class="admin-th">Type</th>
                            <th class="admin-th px-2 text-center">Results</th>
                            <th class="admin-th whitespace-nowrap">Last active</th>
                            <th class="admin-th hidden 2xl:table-cell">Joined</th>
                            <th class="admin-th">Status</th>
                            <th class="admin-th pr-4 pl-0"><span class="sr-only">Open</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($users as $user)
                            @php($lastActive = collect([$user->last_login_at, $user->last_active_at ? \Illuminate\Support\Carbon::parse($user->last_active_at) : null])->filter()->max())
                            <tr class="group transition hover:bg-slate-50/70">
                                <td class="admin-td pl-6">
                                    <a href="{{ route('admin.users.show', $user) }}" class="flex items-center gap-3">
                                        <x-admin.avatar :name="$user->name" />
                                        <span class="min-w-0">
                                            <span class="block font-semibold text-navy group-hover:text-brand">{{ $user->name }}</span>
                                            <span class="block max-w-44 truncate text-xs text-slate-500">{{ $user->email }}</span>
                                        </span>
                                    </a>
                                </td>
                                <td class="admin-td">
                                    <div class="flex flex-wrap gap-1">
                                    <span @class(['admin-badge', 'bg-brand-50 text-brand-600' => $user->role === 'student', 'bg-navy/10 text-navy' => $user->role !== 'student'])>{{ ucfirst($user->role) }}</span>
                                    @if ($user->is_admin)
                                        <span class="admin-badge bg-navy text-white">Admin</span>
                                    @endif
                                    </div>
                                </td>
                                <td class="admin-td px-2 text-center">{{ $user->assessments_count }}</td>
                                <td class="admin-td whitespace-nowrap text-slate-500">{{ $lastActive?->diffForHumans(short: true) ?? 'Never' }}</td>
                                <td class="admin-td hidden whitespace-nowrap text-slate-500 2xl:table-cell">{{ $user->created_at?->format('M j, Y') }}</td>
                                <td class="admin-td">
                                    @if ($user->isDisabled())
                                        <span class="admin-badge bg-red-50 text-red-700"><span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>Disabled</span>
                                    @else
                                        <span class="admin-badge bg-emerald-50 text-emerald-700"><span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>Active</span>
                                    @endif
                                </td>
                                <td class="admin-td pr-4 pl-0 text-right">
                                    <a href="{{ route('admin.users.show', $user) }}" class="inline-flex rounded-lg p-1 text-slate-400 transition group-hover:text-brand" aria-label="Open {{ $user->name }}">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="admin-td py-12 text-center text-slate-500">No users match these filters.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($users->hasPages())
                <div class="border-t border-slate-100 px-6 py-4">{{ $users->links() }}</div>
            @endif
        </section>

        <div class="grid gap-6 self-start md:max-[1379px]:grid-cols-2">
            <x-admin.quick-actions />
            <x-admin.recent-activity :activities="$recentActivity" />
        </div>
    </div>
</x-admin.layout>
