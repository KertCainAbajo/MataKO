<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign in · MataKo Admin</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800" rel="stylesheet">
    @vite(['resources/css/app.css'])
</head>
<body class="h-full bg-[#F2EFED] font-admin text-navy antialiased">
    <div class="grid min-h-full lg:grid-cols-2">
        {{-- Brand panel --}}
        <section class="relative hidden overflow-hidden bg-gradient-to-br from-white via-[#FBF8F5] to-[#F6EDE4] px-12 py-10 lg:flex lg:flex-col xl:px-16">
            {{-- Soft decorative curves in the app's orange --}}
            <div class="pointer-events-none absolute -right-40 -bottom-48 h-[34rem] w-[34rem] rounded-full bg-brand/10"></div>
            <div class="pointer-events-none absolute -right-24 -bottom-32 h-[26rem] w-[26rem] rounded-full bg-brand/10"></div>
            <div class="pointer-events-none absolute top-1/3 -left-32 h-72 w-72 rounded-full bg-navy/5 blur-2xl"></div>

            <x-admin.brand class="relative" />

            <div class="relative mt-12 grid flex-1 grid-cols-[minmax(0,1fr)_auto] items-center gap-4 xl:mt-16">
                <div class="max-w-lg">
                    <h2 class="text-5xl leading-[1.05] font-extrabold tracking-tight whitespace-nowrap text-navy xl:text-[3.25rem] 2xl:text-6xl">
                        Smarter vision.<br>
                        <span class="text-brand">Better care.</span>
                    </h2>
                    <p class="mt-6 max-w-md text-lg leading-relaxed text-slate-600">
                        Manage users, assessment questions, results and eye care content for the MataKo app — in English, Filipino and Cebuano.
                    </p>

                    <ul class="mt-10 grid max-w-md grid-cols-3 gap-5">
                        @foreach ([
                            ['Self-assessments', 'Track eye health', 'M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z'],
                            ['Eye care content', 'Learn & improve', 'M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z'],
                            ['User management', 'Keep it secure', 'M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z'],
                        ] as [$title, $caption, $path])
                            <li>
                                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-brand-100 text-brand">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true"><path d="{{ $path }}"/></svg>
                                </span>
                                <p class="mt-3 text-sm font-semibold text-navy">{{ $title }}</p>
                                <p class="mt-0.5 text-xs text-slate-500">{{ $caption }}</p>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="relative hidden xl:block">
                    <div class="absolute inset-x-6 bottom-2 h-6 rounded-[50%] bg-navy/10 blur-md"></div>
                    <img src="{{ asset('images/mascot.png') }}" alt="The MataKo mascot" class="relative w-48 drop-shadow-xl 2xl:w-64">
                </div>
            </div>

            <p class="relative mt-10 flex items-center gap-3 text-[11px] font-semibold tracking-[0.25em] text-slate-500 uppercase">
                <span class="h-0.5 w-8 rounded-full bg-brand"></span>
                Healthier eyes. Brighter futures.
            </p>
        </section>

        {{-- Sign-in --}}
        <main class="relative flex items-center justify-center overflow-hidden px-6 py-12">
            <div class="pointer-events-none absolute -top-40 -right-40 h-[28rem] w-[28rem] rounded-full border-[3rem] border-white/60"></div>
            <div class="pointer-events-none absolute -bottom-52 -left-24 h-[26rem] w-[26rem] rounded-full bg-white/50"></div>

            <div class="relative w-full max-w-md rounded-3xl bg-white p-8 shadow-[0_24px_60px_-20px_rgba(6,42,60,0.25)] ring-1 ring-navy/5 sm:p-10">
                <x-admin.brand />

                <h1 class="mt-8 text-2xl font-bold tracking-tight text-navy">Welcome back</h1>
                <p class="mt-1.5 text-sm text-slate-500">Sign in to your MataKo admin console.</p>

                <form method="POST" action="{{ route('admin.login.store') }}" class="mt-8 space-y-5">
                    @csrf
                    <div>
                        <label for="email" class="admin-label">Email address</label>
                        <div class="relative">
                            <svg class="pointer-events-none absolute top-1/2 left-3.5 h-5 w-5 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/></svg>
                            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="you@example.com" class="admin-input pl-11">
                        </div>
                        @error('email')
                            <p class="mt-2 flex items-center gap-1.5 text-sm text-red-600" role="alert">
                                <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.008M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="admin-label">Password</label>
                        <div class="relative">
                            <svg class="pointer-events-none absolute top-1/2 left-3.5 h-5 w-5 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/></svg>
                            <input id="password" name="password" type="password" required autocomplete="current-password" placeholder="Enter your password" class="admin-input pr-11 pl-11">
                            <button type="button" id="toggle-password" class="absolute top-1/2 right-2 -translate-y-1/2 rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-navy" aria-label="Show password" aria-pressed="false">
                                <svg data-icon="show" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                                <svg data-icon="hide" class="hidden h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88"/></svg>
                            </button>
                        </div>
                    </div>

                    <label class="flex items-center gap-2.5 text-sm text-slate-600">
                        <input type="checkbox" name="remember" value="1" @checked(old('remember', true)) class="h-4 w-4 rounded border-slate-300 accent-brand"> Keep me signed in
                    </label>

                    <button class="admin-btn-primary w-full py-3 text-base">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l3 3m0 0-3 3m3-3H2.25"/></svg>
                        Sign in
                    </button>
                </form>

                <div class="my-6 flex items-center gap-3 text-xs font-medium tracking-wider text-slate-400">
                    <span class="h-px flex-1 bg-slate-200"></span>OR<span class="h-px flex-1 bg-slate-200"></span>
                </div>

                <div class="flex gap-3 rounded-2xl bg-brand-50 p-4 text-sm text-slate-600 ring-1 ring-brand/10">
                    <svg class="mt-0.5 h-5 w-5 shrink-0 text-brand" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"/></svg>
                    <p>Admin accounts are created by the system owner with
                        <code class="mt-1 inline-block rounded-md bg-white px-1.5 py-0.5 text-xs font-medium text-navy ring-1 ring-slate-200">php artisan matako:make-admin</code>
                    </p>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Show or hide the password.
        const toggle = document.getElementById('toggle-password');
        const password = document.getElementById('password');
        toggle.addEventListener('click', () => {
            const showing = password.type === 'text';
            password.type = showing ? 'password' : 'text';
            toggle.setAttribute('aria-pressed', String(!showing));
            toggle.setAttribute('aria-label', showing ? 'Show password' : 'Hide password');
            toggle.querySelector('[data-icon="show"]').classList.toggle('hidden', !showing);
            toggle.querySelector('[data-icon="hide"]').classList.toggle('hidden', showing);
        });
    </script>
</body>
</html>
