<x-admin.layout :title="$user->name">
    <x-slot:actions>
        <a href="{{ route('admin.users.edit', $user) }}" class="admin-btn-navy">Edit</a>
    </x-slot:actions>

    @php($isSelf = $user->is(auth()->user()))
    @php($lastActive = collect([$user->last_login_at, $user->last_active_at ? \Illuminate\Support\Carbon::parse($user->last_active_at) : null])->filter()->max())

    <section class="admin-card mb-6 flex flex-wrap items-center gap-5">
        <x-admin.avatar :name="$user->name" size="h-16 w-16 text-xl" />
        <div class="min-w-0 flex-1">
            <h2 class="text-xl font-bold tracking-tight text-slate-900">{{ $user->name }}</h2>
            <p class="text-sm text-slate-500">{{ $user->email }}</p>
            <div class="mt-2 flex flex-wrap gap-2">
                <span class="admin-badge bg-slate-100 text-slate-700">{{ ucfirst($user->role) }}</span>
                @if ($user->is_admin)<span class="admin-badge bg-navy text-white">Admin</span>@endif
                @if ($user->isDisabled())
                    <span class="admin-badge bg-red-50 text-red-700"><span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>Disabled</span>
                @else
                    <span class="admin-badge bg-emerald-50 text-emerald-700"><span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>Active</span>
                @endif
            </div>
        </div>
        <div class="grid grid-cols-2 gap-3 text-center">
            <div class="rounded-xl bg-slate-50 px-5 py-3"><p class="text-xl font-bold text-slate-900">{{ $user->assessments_count }}</p><p class="text-xs text-slate-500">Assessments</p></div>
            <div class="rounded-xl bg-slate-50 px-5 py-3"><p class="text-xl font-bold text-slate-900">{{ $lastActive?->diffForHumans(short: true) ?? '—' }}</p><p class="text-xs text-slate-500">Last active</p></div>
        </div>
    </section>

    <div class="grid gap-6 xl:grid-cols-3">
        <section class="admin-card">
            <h2 class="admin-card-title">Profile</h2>
            <dl class="mt-4 space-y-3 text-sm">
                @foreach ([
                    'Email' => $user->email,
                    'Phone' => $user->phone ?: '—',
                    'Age' => $user->age ?: '—',
                    'Type' => ucfirst($user->role).($user->is_admin ? ' · Admin' : ''),
                    'Status' => $user->isDisabled() ? 'Disabled since '.$user->disabled_at->format('M j, Y') : 'Active',
                    'Joined' => $user->created_at?->format('M j, Y g:i A'),
                    'Last active' => $lastActive?->diffForHumans() ?? 'Never',
                    'Assessments' => $user->assessments_count,
                ] as $label => $value)
                    <div class="flex justify-between gap-4">
                        <dt class="text-slate-500">{{ $label }}</dt>
                        <dd class="text-right font-medium text-slate-800">{{ $value }}</dd>
                    </div>
                @endforeach
            </dl>
        </section>

        <section class="admin-card space-y-6 xl:col-span-2">
            <div @class(['flex flex-wrap items-center gap-4 rounded-2xl p-4 ring-1', 'bg-navy text-white ring-navy' => $user->is_admin, 'bg-slate-50 ring-slate-200' => ! $user->is_admin])>
                <span @class(['flex h-11 w-11 shrink-0 items-center justify-center rounded-xl', 'bg-brand text-white' => $user->is_admin, 'bg-white text-navy ring-1 ring-slate-200' => ! $user->is_admin])>
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true"><path d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z"/></svg>
                </span>
                <div class="min-w-0 flex-1">
                    <h2 class="font-bold">Admin access</h2>
                    <p @class(['text-sm', 'text-white/70' => $user->is_admin, 'text-slate-500' => ! $user->is_admin])>
                        @if ($isSelf)
                            You are an admin. You cannot remove your own access.
                        @elseif ($user->is_admin)
                            Can sign in to this console and manage everything in it.
                        @else
                            App user only. Making them an admin lets them sign in here with their app password.
                        @endif
                    </p>
                </div>
                @unless ($isSelf)
                    <form method="POST" action="{{ route('admin.users.admin', $user) }}"
                          data-confirm="{{ $user->is_admin ? "{$user->name} will no longer be able to sign in to the admin console." : "{$user->name} will be able to sign in to this console and manage users, questions and content." }}"
                          data-confirm-title="{{ $user->is_admin ? 'Remove admin access?' : 'Make this user an admin?' }}"
                          data-confirm-button="{{ $user->is_admin ? 'Remove access' : 'Make admin' }}"
                          @if ($user->is_admin) data-confirm-tone="danger" @endif>
                        @csrf @method('PATCH')
                        <button @class(['admin-btn-secondary' => $user->is_admin, 'admin-btn-primary' => ! $user->is_admin])>{{ $user->is_admin ? 'Remove admin' : 'Make admin' }}</button>
                    </form>
                @endunless
            </div>

            @unless ($isSelf)
                <div>
                    <h2 class="admin-card-title">Account access</h2>
                    <p class="mt-1 text-sm text-slate-500">{{ $user->isDisabled() ? 'This user cannot sign in to the app.' : 'Disabling signs the user out of the app on every device and blocks new sign-ins.' }}</p>
                    <form method="POST" action="{{ route('admin.users.status', $user) }}" class="mt-3">
                        @csrf @method('PATCH')
                        <button class="{{ $user->isDisabled() ? 'admin-btn-primary' : 'admin-btn-secondary' }}">{{ $user->isDisabled() ? 'Enable account' : 'Disable account' }}</button>
                    </form>
                </div>
            @endunless

            <div>
                <h2 class="admin-card-title">Set a new password</h2>
                <p class="mt-1 text-sm text-slate-500">Use this when a user forgets their password. They will need to sign in again with the new one.</p>
                <form method="POST" action="{{ route('admin.users.password', $user) }}" class="mt-3 grid gap-3 sm:grid-cols-3 sm:items-end">
                    @csrf @method('PUT')
                    <div>
                        <label for="password" class="admin-label">New password</label>
                        <input id="password" name="password" type="password" required minlength="8" autocomplete="new-password" class="admin-input">
                    </div>
                    <div>
                        <label for="password_confirmation" class="admin-label">Confirm</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" required minlength="8" autocomplete="new-password" class="admin-input">
                    </div>
                    <button class="admin-btn-navy">Change password</button>
                </form>
            </div>

            @unless ($isSelf)
                <div class="flex flex-wrap items-center gap-4 rounded-2xl border border-red-200 bg-red-50/50 p-4">
                    <div class="min-w-0 flex-1">
                        <h2 class="font-bold text-red-700">Delete this account</h2>
                        <p class="text-sm text-slate-600">Permanently deletes {{ $user->name }} and all of their assessment results.</p>
                    </div>
                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                          data-confirm="{{ $user->name }} ({{ $user->email }}) and all of their assessment results will be deleted. This cannot be undone."
                          data-confirm-title="Delete this account?" data-confirm-button="Delete account" data-confirm-tone="danger">
                        @csrf @method('DELETE')
                        <button class="admin-btn-danger">Delete account</button>
                    </form>
                </div>
            @endunless
        </section>
    </div>

    <section class="admin-card mt-6 overflow-x-auto p-0">
        <h2 class="admin-card-title px-6 pt-6">Assessment history</h2>
        <table class="mt-3 min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50"><tr><th class="admin-th">Date</th><th class="admin-th">Result</th><th class="admin-th"></th></tr></thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($assessments as $assessment)
                    <tr>
                        <td class="admin-td">{{ $assessment->created_at?->format('M j, Y g:i A') }}</td>
                        <td class="admin-td"><x-admin.risk :risk="$assessment->risk_level" :score="$assessment->total_score" :max="$assessment->max_score" /></td>
                        <td class="admin-td text-right"><a href="{{ route('admin.assessments.show', $assessment) }}" class="text-brand hover:underline">View answers</a></td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="admin-td py-8 text-center text-slate-500">No assessments yet.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4">{{ $assessments->links() }}</div>
    </section>
</x-admin.layout>
