<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign in · MataKo Admin</title>
    <meta name="theme-color" content="#05163A">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800,900" rel="stylesheet">
    @vite(['resources/css/app.css'])
</head>
<body class="h-full bg-white font-admin text-navy antialiased">
    <div class="grid min-h-full lg:grid-cols-[1.08fr_1fr]">
        {{-- Vision panel: an eye in the MataKo style that watches the pointer, blinks, and looks away while a password is typed --}}
        <section class="relative isolate hidden overflow-hidden bg-[#05163A] px-12 py-10 text-white lg:flex lg:flex-col xl:px-16">
            <div class="pointer-events-none absolute inset-0 -z-10" aria-hidden="true">
                <div class="absolute inset-0 bg-[radial-gradient(ellipse_70%_55%_at_50%_35%,rgba(30,79,216,0.55),transparent_70%)]"></div>
                <div class="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.05)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.05)_1px,transparent_1px)] [background-size:64px_64px] [mask-image:radial-gradient(ellipse_65%_55%_at_50%_38%,black,transparent_80%)]"></div>
                {{-- Eye chart letters, as on an optometrist's wall --}}
                <div class="absolute top-[13%] left-8 text-center leading-[1.15] font-black text-white/[0.06] select-none">
                    <p class="text-[7rem]">E</p>
                    <p class="text-[4.2rem] tracking-[0.15em]">FP</p>
                    <p class="text-[2.8rem] tracking-[0.2em]">TOZ</p>
                    <p class="text-[2rem] tracking-[0.25em]">LPED</p>
                    <p class="text-[1.4rem] tracking-[0.3em]">PECFD</p>
                </div>
                <div class="absolute top-[13%] right-8 text-center leading-[1.15] font-black text-white/[0.06] select-none">
                    <p class="text-[1.4rem] tracking-[0.3em]">EDFCZ</p>
                    <p class="text-[2rem] tracking-[0.25em]">FELO</p>
                    <p class="text-[2.8rem] tracking-[0.2em]">DPO</p>
                    <p class="text-[4.2rem] tracking-[0.15em]">TZ</p>
                    <p class="text-[7rem]">E</p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <img src="{{ asset('images/matako-eye.png') }}" alt="" class="h-9 w-auto">
                <div class="leading-none">
                    <p class="text-2xl font-extrabold tracking-tight">MataKo</p>
                    <p class="mt-1 text-sm font-medium text-white/60">Admin Console</p>
                </div>
            </div>

            <div class="flex flex-1 flex-col items-center justify-center py-8 text-center">
                <div class="group/eye relative aspect-square w-[17rem] xl:w-[19rem]" data-eye aria-hidden="true">
                    {{-- Measurement dial and orbit --}}
                    <div class="animate-dial absolute -inset-10 rounded-full border-2 border-dashed border-white/15"></div>
                    <div class="absolute -inset-4 rounded-full border border-white/10"></div>
                    <div class="animate-orbit absolute -inset-4 rounded-full">
                        <span class="absolute top-1/2 -right-[6px] h-3 w-3 -translate-y-1/2 rounded-full bg-[#F58216] shadow-[0_0_0_4px_rgba(245,130,22,0.25),0_0_20px_5px_rgba(245,130,22,0.55)]"></span>
                    </div>
                    {{-- The eye --}}
                    <div class="absolute inset-0 overflow-hidden rounded-full bg-white shadow-[0_0_0_10px_rgba(255,255,255,0.06),0_40px_90px_-20px_rgba(0,0,0,0.7)]">
                        <div class="absolute inset-[13%] rounded-full bg-[radial-gradient(circle,#FFB067_0%,#F58216_48%,#C25E00_74%,#05163A_76%)]">
                            <div class="absolute inset-0 rounded-full bg-[repeating-conic-gradient(rgba(255,255,255,0.22)_0deg_1.5deg,transparent_1.5deg_6deg)] [mask-image:radial-gradient(circle,transparent_32%,black_36%,black_68%,transparent_74%)]"></div>
                            <div class="absolute inset-[30%] rounded-full bg-[#05163A] shadow-[0_0_0_6px_rgba(5,22,58,0.25)] transition-[translate] duration-200 ease-out" data-pupil>
                                <span class="absolute top-[16%] left-[20%] h-[28%] w-[28%] rounded-full bg-white"></span>
                                <span class="absolute right-[22%] bottom-[22%] h-[12%] w-[12%] rounded-full bg-white/70"></span>
                            </div>
                        </div>
                        {{-- Scanner line --}}
                        <span class="animate-scan absolute inset-x-[8%] h-0.5 rounded-full bg-[#F58216] shadow-[0_0_14px_3px_rgba(245,130,22,0.6)] group-[.is-shy]/eye:hidden"></span>
                        {{-- Eyelids: blink every few seconds, close while the password is typed --}}
                        <span class="animate-blink absolute inset-x-0 top-0 h-1/2 origin-top scale-y-0 bg-[#0A2259] transition-[scale] duration-300 group-[.is-shy]/eye:scale-y-100 group-[.is-shy]/eye:animate-none"></span>
                        <span class="animate-blink absolute inset-x-0 bottom-0 h-1/2 origin-bottom scale-y-0 bg-[#0A2259] transition-[scale] duration-300 group-[.is-shy]/eye:scale-y-100 group-[.is-shy]/eye:animate-none"></span>
                        {{-- Closed-eye curve with lashes, shown while the eyelids are shut --}}
                        <span class="absolute top-[38%] left-[20%] h-[18%] w-[60%] rounded-b-full border-b-[6px] border-white opacity-0 transition-opacity delay-150 duration-300 group-[.is-shy]/eye:opacity-100">
                            @foreach (['left-[12%] rotate-[35deg]', 'left-[34%] rotate-[12deg]', 'left-[60%] -rotate-[12deg]', 'left-[82%] -rotate-[35deg]'] as $lash)
                                <span class="absolute -bottom-6 h-4 w-[5px] origin-top rounded-full bg-white {{ $lash }}"></span>
                            @endforeach
                        </span>
                    </div>
                    {{-- Readouts --}}
                    <div class="absolute top-6 -left-24 rounded-xl bg-white px-3 py-2 text-left text-[#05163A] shadow-[0_20px_40px_-15px_rgba(0,0,0,0.6)] xl:-left-32">
                        <p class="text-[10px] font-bold tracking-wider text-[#05163A]/50 uppercase">Focus</p>
                        <p class="font-mono text-lg leading-tight font-black">20/20</p>
                    </div>
                    <div class="absolute -right-24 bottom-8 rounded-xl bg-white px-3 py-2 text-left text-[#05163A] shadow-[0_20px_40px_-15px_rgba(0,0,0,0.6)] xl:-right-32">
                        <p class="text-[10px] font-bold tracking-wider text-[#05163A]/50 uppercase">Blink rate</p>
                        <p class="flex items-center gap-1.5 text-sm leading-tight font-black"><span class="h-2 w-2 rounded-full bg-[#F58216]"></span><span data-eye-state>Normal</span></p>
                    </div>
                </div>

                <h2 class="mt-16 text-5xl leading-[1.02] font-black tracking-[-0.035em] xl:text-6xl">
                    Smarter vision.<br><span class="text-[#F58216]">Better care.</span>
                </h2>
                <p class="mx-auto mt-5 max-w-md text-lg leading-relaxed text-white/70">
                    Manage users, assessment questions, results and eye care content for the MataKo app — in English, Filipino and Cebuano.
                </p>
                <ul class="mx-auto mt-8 flex flex-wrap justify-center gap-2">
                    @foreach ([['Self-assessments', 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'], ['Eye care content', 'M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z'], ['User management', 'M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z']] as [$label, $path])
                        <li class="inline-flex items-center gap-2 rounded-full bg-white/[0.07] px-3.5 py-1.5 text-sm font-semibold ring-1 ring-white/15">
                            <svg class="h-4 w-4 text-[#F58216]" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true"><path d="{{ $path }}"/></svg>{{ $label }}
                        </li>
                    @endforeach
                </ul>
            </div>

            <p class="flex items-center gap-3 text-[11px] font-semibold tracking-[0.25em] text-white/50 uppercase">
                <span class="h-0.5 w-8 rounded-full bg-[#F58216]"></span>
                Healthier eyes. Brighter futures.
            </p>
        </section>

        {{-- Sign-in --}}
        <main class="relative flex items-center justify-center overflow-hidden bg-white px-6 py-12">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="absolute -top-32 -right-32 h-[26rem] w-[26rem] rounded-full border border-[#05163A]/[0.06]"></div>
                <div class="absolute -top-16 -right-16 h-[18rem] w-[18rem] rounded-full border border-[#F58216]/20"></div>
                <div class="absolute -bottom-40 -left-40 h-[28rem] w-[28rem] rounded-full border border-[#05163A]/[0.06]"></div>
            </div>

            <div class="relative w-full max-w-md">
                {{-- Small screens get the brand here, the large panel is hidden --}}
                <div class="mb-8 flex items-center gap-3 lg:hidden">
                    <img src="{{ asset('images/matako-eye.png') }}" alt="" class="h-9 w-auto">
                    <div class="leading-none"><p class="text-2xl font-extrabold tracking-tight">MataKo</p><p class="mt-1 text-sm font-medium text-slate-500">Admin Console</p></div>
                </div>

                <div class="rounded-[2rem] bg-white p-8 shadow-[0_30px_70px_-25px_rgba(5,22,58,0.35)] ring-1 ring-[#05163A]/[0.07] sm:p-10">
                    <span class="relative flex h-14 w-14 items-center justify-center overflow-hidden rounded-2xl bg-[#05163A] shadow-lg shadow-[#05163A]/30" aria-hidden="true">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-[#F58216]"><span class="h-3.5 w-3.5 rounded-full bg-[#05163A]"></span></span>
                        <span class="animate-blink absolute inset-x-0 top-0 h-1/2 origin-top scale-y-0 bg-[#0A2259]"></span>
                        <span class="animate-blink absolute inset-x-0 bottom-0 h-1/2 origin-bottom scale-y-0 bg-[#0A2259]"></span>
                    </span>

                    <h1 class="mt-6 text-3xl font-black tracking-tight text-[#05163A]">Welcome back</h1>
                    <p class="mt-1.5 text-sm text-slate-500">Sign in to your MataKo admin console.</p>

                    @if (session('status'))
                        <p class="mt-6 flex items-start gap-2 rounded-xl bg-brand-50 px-4 py-3 text-sm text-navy ring-1 ring-brand-100" role="status">
                            <svg class="mt-0.5 h-4 w-4 shrink-0 text-brand" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.008M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                            {{ session('status') }}
                        </p>
                    @endif

                    <form method="POST" action="{{ route('admin.login.store') }}" class="mt-8 space-y-5">
                        @csrf
                        <div>
                            <label for="email" class="admin-label">Email address</label>
                            <div class="relative">
                                <svg class="pointer-events-none absolute top-1/2 left-3.5 h-5 w-5 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/></svg>
                                <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="you@example.com" class="admin-input rounded-2xl py-3.5 pl-11">
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
                                <input id="password" name="password" type="password" required autocomplete="current-password" placeholder="Enter your password" class="admin-input rounded-2xl py-3.5 pr-11 pl-11">
                                <button type="button" id="toggle-password" class="absolute top-1/2 right-2 -translate-y-1/2 rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-navy" aria-label="Show password" aria-pressed="false">
                                    <svg data-icon="show" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                                    <svg data-icon="hide" class="hidden h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88"/></svg>
                                </button>
                            </div>
                        </div>

                        <label class="flex items-center gap-2.5 text-sm text-slate-600">
                            <input type="checkbox" name="remember" value="1" @checked(old('remember', true)) class="h-4 w-4 rounded border-slate-300 accent-brand"> Keep me signed in
                        </label>

                        <button class="group inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-[#F58216] px-4 py-3.5 text-base font-bold text-white shadow-[0_14px_30px_-10px_rgba(245,130,22,0.7)] transition hover:-translate-y-0.5 hover:bg-brand-600 focus:ring-4 focus:ring-brand/25 focus:outline-none">
                            Sign in
                            <svg class="h-5 w-5 transition group-hover:translate-x-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                        </button>
                    </form>

                    <p class="mt-7 flex items-center justify-center gap-2 border-t border-slate-100 pt-6 text-xs font-medium text-slate-500">
                        <svg class="h-4 w-4 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z"/></svg>
                        Protected by lockout and optional 2FA
                    </p>
                </div>
            </div>
        </main>
    </div>

    <script @nonce>
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

        // The big eye follows the pointer, and politely closes while the password is typed.
        const eye = document.querySelector('[data-eye]');
        if (eye) {
            const pupil = eye.querySelector('[data-pupil]');
            const state = eye.querySelector('[data-eye-state]');
            const still = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            if (!still) {
                window.addEventListener('pointermove', (event) => {
                    const box = eye.getBoundingClientRect();
                    const dx = event.clientX - (box.left + box.width / 2);
                    const dy = event.clientY - (box.top + box.height / 2);
                    const reach = Math.min(1, Math.hypot(dx, dy) / 400) * box.width * 0.12;
                    const angle = Math.atan2(dy, dx);
                    pupil.style.translate = `${(Math.cos(angle) * reach).toFixed(1)}px ${(Math.sin(angle) * reach).toFixed(1)}px`;
                });
            }
            const shy = (on) => { eye.classList.toggle('is-shy', on); state.textContent = on ? 'Eyes closed' : 'Normal'; };
            password.addEventListener('focus', () => shy(true));
            password.addEventListener('blur', () => shy(false));
        }
    </script>
</body>
</html>
