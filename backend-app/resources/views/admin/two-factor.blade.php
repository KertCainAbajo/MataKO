<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Two-factor sign-in · MataKo Admin</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800" rel="stylesheet">
    @vite(['resources/css/app.css'])
</head>
<body class="h-full bg-[#F2EFED] font-admin text-navy antialiased">
    <main class="relative flex min-h-full items-center justify-center overflow-hidden px-6 py-12">
        <div class="pointer-events-none absolute -top-40 -right-40 h-[28rem] w-[28rem] rounded-full border-[3rem] border-white/60"></div>
        <div class="pointer-events-none absolute -bottom-52 -left-24 h-[26rem] w-[26rem] rounded-full bg-white/50"></div>

        <div class="relative w-full max-w-md rounded-3xl bg-white p-8 shadow-[0_24px_60px_-20px_rgba(6,42,60,0.25)] ring-1 ring-navy/5 sm:p-10">
            <x-admin.brand />

            <span class="mt-8 flex h-12 w-12 items-center justify-center rounded-2xl bg-brand-100 text-brand">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 0 0 6 3.75v16.5a2.25 2.25 0 0 0 2.25 2.25h7.5A2.25 2.25 0 0 0 18 20.25V3.75a2.25 2.25 0 0 0-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3"/></svg>
            </span>
            <h1 class="mt-4 text-2xl font-bold tracking-tight text-navy">Enter your code</h1>
            <p class="mt-1.5 text-sm text-slate-500">Open your authenticator app and enter the 6-digit code for <strong>MataKo Admin</strong>.</p>

            <form method="POST" action="{{ route('admin.two-factor.verify') }}" class="mt-8 space-y-5">
                @csrf
                <div>
                    <label for="code" class="admin-label">Authentication code</label>
                    <input id="code" name="code" required autofocus autocomplete="one-time-code" inputmode="numeric" maxlength="20" placeholder="123 456"
                           class="admin-input text-center text-2xl font-semibold tracking-[0.4em]">
                    @error('code')
                        <p class="mt-2 text-sm text-red-600" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <button class="admin-btn-primary w-full py-3 text-base">Verify and sign in</button>
            </form>

            <details class="mt-6 rounded-xl bg-slate-50 px-4 py-3 text-sm text-slate-600">
                <summary class="cursor-pointer font-medium text-navy">Lost your phone?</summary>
                <p class="mt-2">Type one of the recovery codes you saved when you turned on two-factor sign-in (for example <span class="font-mono">AB12C-DE34F</span>) in the box above. Each code works once.</p>
            </details>

            <a href="{{ route('admin.login') }}" class="mt-6 inline-block text-sm font-medium text-slate-500 hover:text-navy">← Back to sign in</a>
        </div>
    </main>
</body>
</html>
