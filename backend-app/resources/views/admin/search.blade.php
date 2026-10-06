<x-admin.layout title="Search" :subtitle="$term === '' ? 'Search users, questions and admin activity.' : 'Results for “'.$term.'”'">
    @if ($term === '')
        <p class="admin-card text-center text-sm text-slate-500">Type a name, email, question or activity in the search box above.</p>
    @else
        <div class="grid gap-6 xl:grid-cols-3">
            <section class="admin-card">
                <h2 class="admin-card-title">Users <span class="font-normal text-slate-400">({{ $users->count() }})</span></h2>
                <ul class="mt-4 space-y-1">
                    @forelse ($users as $user)
                        <li>
                            <a href="{{ route('admin.users.show', $user) }}" class="flex items-center gap-3 rounded-xl p-2 transition hover:bg-slate-50">
                                <x-admin.avatar :name="$user->name" />
                                <span class="min-w-0"><span class="block truncate text-sm font-semibold text-navy">{{ $user->name }}</span><span class="block truncate text-xs text-slate-500">{{ $user->email }}</span></span>
                            </a>
                        </li>
                    @empty
                        <li class="py-4 text-sm text-slate-500">No users found.</li>
                    @endforelse
                </ul>
            </section>

            <section class="admin-card">
                <h2 class="admin-card-title">Questions <span class="font-normal text-slate-400">({{ $questions->count() }})</span></h2>
                <ul class="mt-4 space-y-1">
                    @forelse ($questions as $question)
                        <li>
                            <a href="{{ route('admin.questions.edit', $question) }}" class="block rounded-xl p-2 transition hover:bg-slate-50">
                                <span class="block text-sm text-navy">{{ $question->question }}</span>
                                <span class="block text-xs text-slate-500">{{ ucfirst($question->audience) }} · {{ $question->symptom }}</span>
                            </a>
                        </li>
                    @empty
                        <li class="py-4 text-sm text-slate-500">No questions found.</li>
                    @endforelse
                </ul>
            </section>

            <section class="admin-card">
                <h2 class="admin-card-title">Admin activity <span class="font-normal text-slate-400">({{ $activities->count() }})</span></h2>
                <ul class="mt-4 space-y-3">
                    @forelse ($activities as $activity)
                        <li class="text-sm">
                            <p class="text-navy"><span class="font-semibold">{{ $activity->admin?->name ?? 'Removed admin' }}</span> · {{ $activity->description }}</p>
                            <p class="text-xs text-slate-400">{{ $activity->created_at?->diffForHumans() }}</p>
                        </li>
                    @empty
                        <li class="py-4 text-sm text-slate-500">No activity found.</li>
                    @endforelse
                </ul>
            </section>
        </div>
    @endif
</x-admin.layout>
