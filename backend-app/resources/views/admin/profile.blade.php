<x-admin.layout title="My profile" subtitle="Manage your admin account, sign-in email and password.">
    @php
        $tz = config('app.display_timezone');
        $passwordErrors = $errors->getBag('password');
        $detailErrors = $errors->getBag('details');
        $initials = collect(preg_split('/\s+/', trim($admin->name)))->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))->take(2)->implode('');
        $passwordChanged = $passwordChangedAt ? \Illuminate\Support\Carbon::parse($passwordChangedAt) : null;
        $checks = [
            ['Strong, unique password', (bool) $passwordChanged, $passwordChanged ? 'Changed '.$passwordChanged->diffForHumans() : 'Not changed since the account was created'],
            ['Recent sign-in', (bool) $admin->last_login_at, $admin->last_login_at ? 'Last on '.$admin->last_login_at->copy()->timezone($tz)->format('M j, g:i A') : 'No sign-in recorded'],
            ['Email you can reach', ! str_ends_with($admin->email, '.test'), $admin->email],
        ];
        $score = collect($checks)->where(1, true)->count();
        $activityIcon = fn (string $action) => match (true) {
            $action === 'login' => ['log-in-outline', 'bg-navy/10 text-navy'],
            str_ends_with($action, '.deleted') => ['trash-outline', 'bg-red-50 text-red-600'],
            str_ends_with($action, '.created') => ['add-circle-outline', 'bg-emerald-50 text-emerald-600'],
            str_ends_with($action, '.password_reset') => ['key-outline', 'bg-amber-50 text-amber-600'],
            default => ['create-outline', 'bg-brand-100 text-brand'],
        };
    @endphp

    {{-- Profile banner --}}
    <section class="admin-card overflow-hidden p-0">
        <div class="relative h-36 overflow-hidden bg-gradient-to-r from-navy-900 via-navy to-navy-700">
            <div class="pointer-events-none absolute -top-16 right-24 h-56 w-56 rounded-full bg-brand/30 blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-20 left-1/3 h-48 w-48 rounded-full bg-sky-400/20 blur-3xl"></div>
            <img src="{{ asset('images/matako-eye.png') }}" alt="" class="absolute top-1/2 right-10 hidden h-16 -translate-y-1/2 opacity-20 md:block">
        </div>
        <div class="flex flex-wrap items-end gap-6 px-8 pb-6">
            <div class="relative -mt-14 shrink-0">
                <span class="flex h-28 w-28 items-center justify-center rounded-full bg-gradient-to-br from-brand to-[#FF9A3D] text-4xl font-bold text-white shadow-xl ring-[6px] ring-white">{{ $initials }}</span>
                <span class="absolute right-2 bottom-2 h-5 w-5 rounded-full bg-emerald-500 ring-4 ring-white" title="Signed in"></span>
            </div>
            <div class="min-w-0 flex-1 pt-4">
                <div class="flex flex-wrap items-center gap-2">
                    <h2 class="text-2xl font-bold tracking-tight text-navy">{{ $admin->name }}</h2>
                    <span class="admin-badge bg-navy text-white"><ion-icon name="shield-checkmark"></ion-icon>Administrator</span>
                </div>
                <p class="mt-1 flex items-center gap-1.5 text-sm text-slate-500"><ion-icon name="mail-outline"></ion-icon>{{ $admin->email }}</p>
            </div>
            <dl class="grid grid-cols-3 gap-3 pt-4">
                @foreach ([['Actions', $actionCount], ['Sign-ins', $signInCount], ['Changes', $changeCount]] as [$label, $value])
                    <div class="flex min-w-24 flex-col-reverse rounded-2xl bg-slate-50 px-4 py-3 text-center ring-1 ring-slate-100">
                        <dt class="text-xs text-slate-500">{{ $label }}</dt>
                        <dd class="text-xl font-bold text-navy">{{ $value }}</dd>
                    </div>
                @endforeach
            </dl>
        </div>
    </section>

    <div class="mt-6 grid gap-6 lg:grid-cols-[260px_minmax(0,1fr)] 2xl:grid-cols-[260px_minmax(0,1fr)_340px]">
        {{-- Settings menu --}}
        <nav class="admin-card self-start p-3 lg:sticky lg:top-6" aria-label="Profile sections">
            <p class="px-3 pt-2 pb-2 text-[11px] font-semibold tracking-widest text-slate-400 uppercase">Settings</p>
            @foreach ([['#account', 'Account details', 'person-circle-outline', 'Name and email'], ['#security', 'Password & security', 'lock-closed-outline', 'Change your password'], ['#activity', 'Your activity', 'time-outline', 'What you did recently']] as [$href, $label, $icon, $caption])
                <a href="{{ $href }}" class="group flex items-center gap-3 rounded-xl px-3 py-2.5 transition hover:bg-brand-50">
                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-slate-100 text-slate-600 transition group-hover:bg-brand-100 group-hover:text-brand"><ion-icon name="{{ $icon }}" class="text-lg"></ion-icon></span>
                    <span><span class="block text-sm font-semibold text-navy">{{ $label }}</span><span class="block text-xs text-slate-400">{{ $caption }}</span></span>
                </a>
            @endforeach
            <div class="mt-3 border-t border-slate-100 px-3 pt-4 pb-2 text-xs text-slate-500">
                <p class="flex justify-between"><span>Admin since</span><span class="font-semibold text-navy">{{ $admin->created_at?->copy()->timezone($tz)->format('M j, Y') }}</span></p>
                <p class="mt-2 flex justify-between"><span>Last sign-in</span><span class="font-semibold text-navy">{{ $admin->last_login_at?->copy()->timezone($tz)->format('M j, g:i A') ?? '—' }}</span></p>
            </div>
        </nav>

        <div class="min-w-0 space-y-6">
            {{-- Account details --}}
            <form method="POST" action="{{ route('admin.profile.update') }}" class="admin-card scroll-mt-6 p-0" id="account">
                @csrf @method('PUT')
                <div class="flex items-center gap-3 border-b border-slate-100 px-6 py-5">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-100 text-brand"><ion-icon name="person" class="text-lg"></ion-icon></span>
                    <div><h2 class="admin-card-title">Account details</h2><p class="admin-card-subtitle">The name shown in the admin and the email you sign in with.</p></div>
                </div>
                <div class="space-y-5 px-6 py-6">
                    @if ($detailErrors->any())
                        <div class="flex items-center gap-2 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert"><ion-icon name="alert-circle" class="text-lg"></ion-icon>{{ $detailErrors->first() }}</div>
                    @endif
                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <label for="name" class="admin-label">Full name</label>
                            <div class="relative">
                                <ion-icon name="person-outline" class="pointer-events-none absolute top-1/2 left-3.5 -translate-y-1/2 text-lg text-slate-400"></ion-icon>
                                <input id="name" name="name" value="{{ old('name', $admin->name) }}" required maxlength="255" autocomplete="name" class="admin-input pl-11">
                            </div>
                        </div>
                        <div>
                            <label for="email" class="admin-label">Email address</label>
                            <div class="relative">
                                <ion-icon name="mail-outline" class="pointer-events-none absolute top-1/2 left-3.5 -translate-y-1/2 text-lg text-slate-400"></ion-icon>
                                <input id="email" name="email" type="email" value="{{ old('email', $admin->email) }}" required autocomplete="email" class="admin-input pl-11">
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-end gap-4 rounded-2xl bg-slate-50 p-4 ring-1 ring-slate-100">
                        <div class="min-w-64 flex-1">
                            <label for="details_current_password" class="admin-label flex items-center gap-1.5"><ion-icon name="shield-half-outline" class="text-brand"></ion-icon>Confirm with your current password</label>
                            <div class="relative">
                                <ion-icon name="lock-closed-outline" class="pointer-events-none absolute top-1/2 left-3.5 -translate-y-1/2 text-lg text-slate-400"></ion-icon>
                                <input id="details_current_password" name="current_password" type="password" required autocomplete="current-password" placeholder="Current password" class="admin-input pl-11">
                            </div>
                        </div>
                        <button class="admin-btn-primary h-[42px]"><ion-icon name="save-outline" class="text-lg"></ion-icon>Save details</button>
                    </div>
                    <p class="flex items-center gap-1.5 text-xs text-slate-500"><ion-icon name="information-circle-outline" class="text-base"></ion-icon>If you change your email, use the new one the next time you sign in.</p>
                </div>
            </form>

            {{-- Password --}}
            <form method="POST" action="{{ route('admin.profile.password') }}" class="admin-card scroll-mt-6 p-0" id="security">
                @csrf @method('PUT')
                <div class="flex items-center gap-3 border-b border-slate-100 px-6 py-5">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-navy/10 text-navy"><ion-icon name="key" class="text-lg"></ion-icon></span>
                    <div><h2 class="admin-card-title">Password &amp; security</h2><p class="admin-card-subtitle">Changing your password signs out every other browser.</p></div>
                </div>
                <div class="space-y-5 px-6 py-6">
                    @if ($passwordErrors->any())
                        <div class="flex items-center gap-2 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert"><ion-icon name="alert-circle" class="text-lg"></ion-icon>{{ $passwordErrors->first() }}</div>
                    @endif
                    <div class="max-w-md">
                        <label for="pw_current_password" class="admin-label">Current password</label>
                        <x-admin.password-input id="pw_current_password" name="current_password" autocomplete="current-password" />
                    </div>
                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <label for="pw_password" class="admin-label">New password</label>
                            <x-admin.password-input id="pw_password" name="password" autocomplete="new-password" minlength="8" />
                        </div>
                        <div>
                            <label for="pw_password_confirmation" class="admin-label">Confirm new password</label>
                            <x-admin.password-input id="pw_password_confirmation" name="password_confirmation" autocomplete="new-password" minlength="8" />
                            <p class="mt-1.5 hidden items-center gap-1 text-xs" id="match-hint"></p>
                        </div>
                    </div>

                    <div class="grid gap-4 rounded-2xl bg-slate-50 p-4 ring-1 ring-slate-100 md:grid-cols-2">
                        <div aria-live="polite">
                            <p class="text-xs font-semibold text-slate-600">Password strength: <span id="strength-label" class="text-slate-400">—</span></p>
                            <div class="mt-2 flex gap-1.5">
                                @foreach (range(1, 4) as $bar)
                                    <span class="h-2 flex-1 rounded-full bg-slate-200" data-strength-bar></span>
                                @endforeach
                            </div>
                        </div>
                        <ul class="grid grid-cols-2 gap-x-4 gap-y-1.5 text-xs text-slate-500">
                            @foreach ([['length', 'At least 8 characters'], ['letter', 'Contains a letter'], ['number', 'Contains a number'], ['symbol', 'Symbol or mixed case']] as [$rule, $text])
                                <li class="flex items-center gap-1.5" data-rule="{{ $rule }}"><ion-icon name="ellipse-outline" class="text-sm"></ion-icon>{{ $text }}</li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="flex justify-end">
                        <button class="admin-btn-navy"><ion-icon name="shield-checkmark-outline" class="text-lg"></ion-icon>Update password</button>
                    </div>
                </div>
            </form>
        </div>

        {{-- Side column --}}
        <div class="grid gap-6 self-start md:grid-cols-2 lg:max-2xl:col-span-2 2xl:grid-cols-1">
            <section class="admin-card">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600"><ion-icon name="shield-checkmark" class="text-lg"></ion-icon></span>
                        <h2 class="admin-card-title">Security checklist</h2>
                    </div>
                    <span @class(['admin-badge', 'bg-emerald-50 text-emerald-700' => $score === 3, 'bg-amber-50 text-amber-700' => $score < 3])>{{ $score }}/3</span>
                </div>
                <div class="mt-4 h-2 rounded-full bg-slate-100"><div @class(['h-2 rounded-full', 'bg-emerald-500' => $score === 3, 'bg-amber-400' => $score < 3]) style="width: {{ $score / 3 * 100 }}%"></div></div>
                <ul class="mt-5 space-y-4">
                    @foreach ($checks as [$label, $done, $detail])
                        <li class="flex items-start gap-3">
                            <ion-icon name="{{ $done ? 'checkmark-circle' : 'alert-circle' }}" @class(['mt-0.5 shrink-0 text-xl', 'text-emerald-500' => $done, 'text-amber-500' => ! $done])></ion-icon>
                            <div class="min-w-0"><p class="text-sm font-semibold text-navy">{{ $label }}</p><p class="truncate text-xs text-slate-500">{{ $detail }}</p></div>
                        </li>
                    @endforeach
                </ul>
            </section>

            <section class="admin-card scroll-mt-6" id="activity">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-100 text-brand"><ion-icon name="time" class="text-lg"></ion-icon></span>
                        <h2 class="admin-card-title">Your activity</h2>
                    </div>
                    <a href="{{ route('admin.activity', ['admin' => $admin->id]) }}" class="text-sm font-semibold text-brand hover:text-brand-600">View all →</a>
                </div>
                <ol class="mt-4 space-y-3">
                    @forelse ($recentActivity as $activity)
                        @php([$icon, $colors] = $activityIcon($activity->action))
                        <li class="flex items-start gap-3">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full {{ $colors }}"><ion-icon name="{{ $icon }}"></ion-icon></span>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm text-navy">{{ $activity->description }}</p>
                                <p class="text-xs text-slate-400">{{ $activity->created_at->copy()->timezone($tz)->format('M j, g:i A') }} · {{ $activity->created_at->diffForHumans(short: true) }}</p>
                            </div>
                        </li>
                    @empty
                        <li class="py-4 text-center text-sm text-slate-500">No activity yet.</li>
                    @endforelse
                </ol>
            </section>
        </div>
    </div>

    <script>
        // Show or hide each password field.
        document.querySelectorAll('[data-toggle]').forEach((button) => {
            button.addEventListener('click', () => {
                const input = document.getElementById(button.dataset.toggle);
                const show = input.type === 'password';
                input.type = show ? 'text' : 'password';
                button.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
                button.querySelector('ion-icon').setAttribute('name', show ? 'eye-off-outline' : 'eye-outline');
            });
        });

        // Live strength guide and rule checklist for the new password.
        const password = document.getElementById('pw_password');
        const confirmation = document.getElementById('pw_password_confirmation');
        const bars = document.querySelectorAll('[data-strength-bar]');
        const label = document.getElementById('strength-label');
        const hint = document.getElementById('match-hint');
        const levels = [['Too short', 'bg-red-500', 'text-red-600'], ['Weak', 'bg-red-500', 'text-red-600'], ['Fair', 'bg-amber-400', 'text-amber-600'], ['Good', 'bg-emerald-400', 'text-emerald-600'], ['Strong', 'bg-emerald-600', 'text-emerald-700']];
        const rules = {
            length: (v) => v.length >= 8,
            letter: (v) => /[a-z]/i.test(v),
            number: (v) => /\d/.test(v),
            symbol: (v) => /[^a-z0-9]/i.test(v) || (/[a-z]/.test(v) && /[A-Z]/.test(v)),
        };
        const update = () => {
            const value = password.value;
            const passed = Object.fromEntries(Object.entries(rules).map(([key, test]) => [key, test(value)]));
            document.querySelectorAll('[data-rule]').forEach((item) => {
                const ok = passed[item.dataset.rule];
                item.classList.toggle('text-emerald-600', ok);
                item.querySelector('ion-icon').setAttribute('name', ok ? 'checkmark-circle' : 'ellipse-outline');
            });
            let score = Object.values(passed).filter(Boolean).length;
            if (value.length >= 12 && score >= 3) score = Math.min(4, score + 1);
            if (!passed.length) score = 0;
            const [text, bar, colour] = levels[score];
            bars.forEach((element, index) => { element.className = 'h-2 flex-1 rounded-full ' + (index < score && value ? bar : 'bg-slate-200'); });
            label.textContent = value ? text : '—';
            label.className = value ? colour : 'text-slate-400';

            if (confirmation.value) {
                const match = confirmation.value === value;
                hint.className = 'mt-1.5 flex items-center gap-1 text-xs ' + (match ? 'text-emerald-600' : 'text-red-600');
                hint.textContent = match ? '✓ Passwords match' : 'Passwords do not match yet';
            } else {
                hint.className = 'mt-1.5 hidden';
            }
        };
        password.addEventListener('input', update);
        confirmation.addEventListener('input', update);
    </script>
</x-admin.layout>
