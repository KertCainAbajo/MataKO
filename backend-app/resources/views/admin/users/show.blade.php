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
                <details class="rounded-lg border border-red-200 p-4">
                    <summary class="cursor-pointer text-sm font-semibold text-red-700">Delete this user</summary>
                    <p class="mt-2 text-sm text-slate-600">This permanently deletes {{ $user->name }} and all of their assessment results. It cannot be undone.</p>
                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="mt-3">
                        @csrf @method('DELETE')
                        <button class="admin-btn-danger">Delete permanently</button>
                    </form>
                </details>
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
