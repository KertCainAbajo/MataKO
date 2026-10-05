<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign in · MataKo Admin</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet">
    @vite(['resources/css/app.css'])
</head>
<body class="h-full bg-white font-admin text-slate-900 antialiased">
    <div class="grid min-h-full lg:grid-cols-2">
        {{-- Brand panel --}}
        <section class="relative hidden overflow-hidden bg-gradient-to-br from-navy-900 via-navy to-navy-700 p-12 text-white lg:flex lg:flex-col lg:justify-between">
            <div class="pointer-events-none absolute -top-32 -right-32 h-96 w-96 rounded-full bg-brand/20 blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-40 -left-24 h-96 w-96 rounded-full bg-sky-400/10 blur-3xl"></div>

            <div class="relative flex items-center gap-3">
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-white shadow-lg shadow-black/20">
                    <img src="{{ asset('images/matako-eye.png') }}" alt="" class="h-5 w-8 object-contain">
                </div>
                <div class="leading-tight">
                    <p class="text-lg font-bold tracking-tight">MataKo</p>
                    <p class="text-xs font-medium text-orange-300/90">Admin Console</p>
                </div>
            </div>

            <div class="relative flex flex-1 items-center justify-center py-10">
                <img src="{{ asset('images/mascot.png') }}" alt="The MataKo mascot" class="max-h-80 drop-shadow-2xl">
            </div>

            <div class="relative max-w-md">
                <h2 class="text-3xl font-bold tracking-tight">Your vision, your power.</h2>
                <p class="mt-3 text-base leading-relaxed text-slate-300">Manage users, assessment questions, results and eye care content for the MataKo app — in English, Filipino and Cebuano.</p>
                <div class="mt-8 flex gap-6 text-sm text-slate-300">
                    <span class="flex items-center gap-2"><span class="h-2 w-2 rounded-full bg-brand"></span>Self-assessments</span>
                    <span class="flex items-center gap-2"><span class="h-2 w-2 rounded-full bg-emerald-400"></span>Eye care content</span>
                    <span class="flex items-center gap-2"><span class="h-2 w-2 rounded-full bg-sky-400"></span>User management</span>
                </div>
            </div>
        </section>

        {{-- Sign-in form --}}
        <main class="flex items-center justify-center bg-slate-50 px-6 py-12 lg:bg-white">
            <div class="w-full max-w-sm">
                <img src="{{ asset('matako-logo.png') }}" alt="MataKo" class="h-10 lg:hidden">
                <h1 class="mt-8 text-2xl font-bold tracking-tight text-slate-900 lg:mt-0">Welcome back</h1>
                <p class="mt-2 text-sm text-slate-500">Sign in to the MataKo admin console.</p>

                <form method="POST" action="{{ route('admin.login.store') }}" class="mt-8 space-y-5">
                    @csrf
                    <div>
                        <label for="email" class="admin-label">Email address</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="you@example.com" class="admin-input">
                        @error('email')
                            <p class="mt-2 flex items-center gap-1.5 text-sm text-red-600">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.008M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                    <div>
                        <label for="password" class="admin-label">Password</label>
                        <input id="password" name="password" type="password" required autocomplete="current-password" placeholder="••••••••" class="admin-input">
                    </div>
                    <label class="flex items-center gap-2.5 text-sm text-slate-600">
                        <input type="checkbox" name="remember" value="1" class="h-4 w-4 rounded border-slate-300 accent-brand"> Keep me signed in
                    </label>
                    <button class="admin-btn-primary w-full py-3">Sign in</button>
                </form>

                <p class="mt-10 rounded-xl bg-slate-100 px-4 py-3 text-xs leading-relaxed text-slate-500">
                    Admin accounts are created by the system owner with
                    <code class="rounded bg-white px-1 py-0.5 text-[11px] text-slate-700">php artisan matako:make-admin</code>.
                </p>
            </div>
        </main>
    </div>
</body>
</html>
