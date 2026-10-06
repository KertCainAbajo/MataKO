@props(['activities'])

@php($tz = config('app.display_timezone'))

{{-- The latest changes made in the admin dashboard. --}}
<section {{ $attributes->merge(['class' => 'admin-card']) }}>
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
            <svg class="h-5 w-5 text-brand" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
            <h2 class="admin-card-title">Recent admin activity</h2>
        </div>
        <a href="{{ route('admin.activity') }}" class="text-sm font-semibold text-brand hover:text-brand-600">View all →</a>
    </div>
    <ul class="mt-4 divide-y divide-slate-100">
        @forelse ($activities as $activity)
            @php($when = $activity->created_at?->copy()->timezone($tz))
            <li class="flex items-start gap-3 py-3 first:pt-0 last:pb-0">
                <x-admin.avatar :name="$activity->admin?->name ?? 'Removed admin'" size="h-9 w-9 text-xs" />
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-semibold text-navy">{{ $activity->admin?->name ?? 'Removed admin' }}</p>
                    <p class="truncate text-xs text-slate-500">{{ $activity->description }}</p>
                </div>
                <span class="shrink-0 text-xs whitespace-nowrap text-slate-400">{{ $when?->isToday() ? 'Today' : ($when?->isYesterday() ? 'Yesterday' : $when?->format('M j')) }}, {{ $when?->format('g:i A') }}</span>
            </li>
        @empty
            <li class="py-4 text-center text-sm text-slate-500">No activity yet.</li>
        @endforelse
    </ul>
</section>
