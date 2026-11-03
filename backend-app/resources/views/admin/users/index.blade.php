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
                            <th class="admin-th pr-4 pl-0 text-right"><span class="sr-only">Actions</span></th>
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
                                <td class="admin-td pr-4 pl-0">
                                    <div class="flex items-center justify-end gap-1">
                                        @unless ($user->is(auth()->user()))
                                            <form method="POST" action="{{ route('admin.users.admin', $user) }}"
                                                  data-confirm="{{ $user->is_admin ? "{$user->name} will no longer be able to sign in to the admin console." : "{$user->name} will be able to sign in to this console and manage users, questions and content." }}"
                                                  data-confirm-title="{{ $user->is_admin ? 'Remove admin access?' : 'Make this user an admin?' }}"
                                                  data-confirm-button="{{ $user->is_admin ? 'Remove access' : 'Make admin' }}"
                                                  @if ($user->is_admin) data-confirm-tone="danger" @endif>
                                                @csrf @method('PATCH')
                                                <button title="{{ $user->is_admin ? 'Remove admin' : 'Make admin' }}" aria-label="{{ $user->is_admin ? 'Remove admin access from' : 'Make admin:' }} {{ $user->name }}"
                                                        @class(['rounded-lg p-1.5 transition', 'bg-navy text-white hover:bg-navy-700' => $user->is_admin, 'text-slate-400 hover:bg-brand-50 hover:text-brand' => ! $user->is_admin])>
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true"><path d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z"/></svg>
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                                  data-confirm="{{ $user->name }} ({{ $user->email }}) and all of their assessment results will be deleted. This cannot be undone."
                                                  data-confirm-title="Delete this account?" data-confirm-button="Delete account" data-confirm-tone="danger">
                                                @csrf @method('DELETE')
                                                <button title="Delete account" aria-label="Delete {{ $user->name }}" class="rounded-lg p-1.5 text-slate-400 transition hover:bg-red-50 hover:text-red-600">
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true"><path d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                                                </button>
                                            </form>
                                        @endunless
                                        <a href="{{ route('admin.users.show', $user) }}" class="inline-flex rounded-lg p-1.5 text-slate-400 transition group-hover:text-brand" aria-label="Open {{ $user->name }}">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                                        </a>
                                    </div>
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
