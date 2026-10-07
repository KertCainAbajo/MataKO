@php
    $tz = config('app.display_timezone');
    $severityStyle = [
        'danger' => ['alert-circle', 'bg-red-50 text-red-600', 'bg-red-50 text-red-700'],
        'warning' => ['warning', 'bg-amber-50 text-amber-600', 'bg-amber-50 text-amber-700'],
        'info' => ['information-circle', 'bg-sky-50 text-sky-600', 'bg-slate-100 text-slate-600'],
    ];
    $layersOn = collect($layers)->where(2, true)->count();
@endphp

<x-admin.layout title="Security" subtitle="How MataKo is protected, and every sign-in problem in the last 90 days.">
    {{-- Key figures --}}
    <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ([
            ['Failed sign-ins', $stats['failed'], 'Last 24 hours', 'close-circle', $stats['failed'] >= 10 ? 'bg-red-50 text-red-600' : 'bg-amber-50 text-amber-600'],
            ['Accounts locked', $stats['locked'], 'Last 24 hours, 15 minutes each', 'lock-closed', $stats['locked'] ? 'bg-red-50 text-red-600' : 'bg-emerald-50 text-emerald-600'],
            ['Admins with two-factor', $stats['adminsWithTwoFactor'].' / '.$stats['admins'], 'Code from an authenticator app', 'phone-portrait', $stats['adminsWithTwoFactor'] === $stats['admins'] ? 'bg-emerald-50 text-emerald-600' : 'bg-amber-50 text-amber-600'],
            ['App sign-ins active', $stats['tokens'], 'Each expires after 30 days', 'phone-landscape', 'bg-sky-50 text-sky-600'],
        ] as [$label, $value, $caption, $icon, $colors])
            <div class="admin-card flex items-center gap-4">
                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full {{ $colors }}"><ion-icon name="{{ $icon }}" class="text-2xl"></ion-icon></span>
                <div><p class="text-2xl font-bold text-navy">{{ is_int($value) ? number_format($value) : $value }}</p><p class="text-sm font-medium text-slate-600">{{ $label }}</p><p class="text-xs text-slate-400">{{ $caption }}</p></div>
            </div>
        @endforeach
    </div>

    <div class="mt-6 grid gap-6 min-[1380px]:grid-cols-3">
        {{-- Events --}}
        <section class="admin-card min-w-0 p-0 min-[1380px]:col-span-2">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-6 py-5">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-navy/10 text-navy"><ion-icon name="list" class="text-lg"></ion-icon></span>
                    <div><h2 class="admin-card-title">Security events</h2><p class="admin-card-subtitle">Newest first. Kept for 90 days.</p></div>
                </div>
                <form method="GET">
                    <label for="type" class="sr-only">Event type</label>
                    <select id="type" name="type" class="admin-input w-64 py-2" data-autosubmit>
                        <option value="">All events</option>
                        @foreach (\App\Models\SecurityEvent::TYPES as $key => [$label])
                            <option value="{{ $key }}" @selected($type === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </form>
            </div>

            <ol class="divide-y divide-slate-100">
                @forelse ($events as $event)
                    @php([$icon, $iconColors, $badgeColors] = $severityStyle[$event->severity()])
                    <li class="flex items-start gap-4 px-6 py-4">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full {{ $iconColors }}"><ion-icon name="{{ $icon }}" class="text-lg"></ion-icon></span>
                        <div class="min-w-0 flex-1">
                            <p class="flex flex-wrap items-center gap-2 text-sm font-semibold text-navy">
                                {{ $event->label() }}
                                <span class="admin-badge {{ $badgeColors }}">{{ ucfirst($event->severity()) }}</span>
                            </p>
                            <p class="mt-0.5 truncate text-sm text-slate-600">
                                @if ($event->user)
                                    <a href="{{ route('admin.users.show', $event->user_id) }}" class="font-medium hover:text-brand">{{ $event->user->name }}</a> ·
                                @endif
                                {{ $event->email ?? 'Unknown account' }}
                                @if ($event->details) · {{ $event->details }} @endif
                            </p>
                            <p class="mt-0.5 truncate text-xs text-slate-400">From {{ $event->ip ?? 'unknown address' }} · {{ \Illuminate\Support\Str::limit($event->user_agent ?? 'unknown device', 70) }}</p>
                        </div>
                        <time class="shrink-0 text-right text-xs text-slate-500" datetime="{{ $event->created_at->toIso8601String() }}">
                            {{ $event->created_at->copy()->timezone($tz)->format('M j, g:i A') }}<br><span class="text-slate-400">{{ $event->created_at->diffForHumans() }}</span>
                        </time>
                    </li>
                @empty
                    <li class="px-6 py-12 text-center">
                        <ion-icon name="shield-checkmark" class="text-4xl text-emerald-400"></ion-icon>
                        <p class="mt-2 text-sm font-semibold text-navy">No security events{{ $type ? ' of this type' : '' }}</p>
                        <p class="text-xs text-slate-500">Failed sign-ins, lockouts and two-factor changes appear here.</p>
                    </li>
                @endforelse
            </ol>
            @if ($events->hasPages())
                <div class="border-t border-slate-100 px-6 py-4">{{ $events->links() }}</div>
            @endif
        </section>

        <div class="space-y-6">
            {{-- Protection layers --}}
            <section class="admin-card">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600"><ion-icon name="shield-checkmark" class="text-lg"></ion-icon></span>
                        <h2 class="admin-card-title">Protection layers</h2>
                    </div>
                    <span @class(['admin-badge', 'bg-emerald-50 text-emerald-700' => $layersOn === count($layers), 'bg-amber-50 text-amber-700' => $layersOn < count($layers)])>{{ $layersOn }}/{{ count($layers) }}</span>
                </div>
                <ul class="mt-5 space-y-4">
                    @foreach ($layers as $index => [$name, $description, $on, $status])
                        <li class="flex items-start gap-3">
                            <ion-icon name="{{ $on ? 'checkmark-circle' : 'alert-circle' }}" @class(['mt-0.5 shrink-0 text-xl', 'text-emerald-500' => $on, 'text-amber-500' => ! $on])></ion-icon>
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-navy">{{ $index + 1 }}. {{ $name }}</p>
                                <p class="text-xs text-slate-500">{{ $description }}</p>
                                <p @class(['mt-0.5 text-xs font-medium', 'text-emerald-600' => $on, 'text-amber-600' => ! $on])>{{ $status }}</p>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </section>

            @if ($adminsWithoutTwoFactor->isNotEmpty())
                <section class="admin-card border-amber-200">
                    <h2 class="admin-card-title flex items-center gap-2"><ion-icon name="phone-portrait-outline" class="text-amber-500"></ion-icon>Admins without two-factor</h2>
                    <p class="admin-card-subtitle">Each admin turns it on from their own profile.</p>
                    <ul class="mt-4 space-y-2 text-sm">
                        @foreach ($adminsWithoutTwoFactor as $admin)
                            <li class="flex items-center justify-between gap-2"><span class="truncate font-medium text-navy">{{ $admin->name }}</span><span class="truncate text-xs text-slate-500">{{ $admin->email }}</span></li>
                        @endforeach
                    </ul>
                    @if ($adminsWithoutTwoFactor->contains('id', auth()->id()))
                        <a href="{{ route('admin.profile') }}#two-factor" class="admin-btn-primary mt-4 w-full justify-center">Set up mine now</a>
                    @endif
                </section>
            @endif
        </div>
    </div>
</x-admin.layout>
