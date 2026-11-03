@php
    $phone = fn (string $image, string $alt, string $extra = '') => '<div class="rounded-[2.4rem] bg-navy p-2.5 shadow-[0_30px_60px_-20px_rgba(6,42,60,0.45)] ring-1 ring-navy/10 '.$extra.'"><img src="'.asset('images/showcase/'.$image.'.webp').'" alt="'.e($alt).'" loading="lazy" width="480" height="1004" class="block w-full rounded-[1.9rem]"></div>';
    $features = [
        ['clipboard', 'Guided self-assessment', 'A '.$stats['questions'].'-question eye strain check with a friendly character for every symptom, tailored to students or professionals.'],
        ['pulse', 'Clear results & care plans', 'Mild, Moderate or Severe — with a scoring breakdown and practical tips matched to the result.'],
        ['trending-up', 'Progress tracking', 'Screen breaks, time in the app, eye-health history over time and milestones to unlock.'],
        ['eye', 'Eye exercises with timers', $stats['exercises'].' guided exercises — focus shifting, palming, blinking, eye rolling and the 20-20-20 rule.'],
        ['contrast', 'Comfort display modes', 'Dark mode, high contrast, monochrome and a text-contrast slider for tired eyes.'],
        ['language', 'English, Filipino & Cebuano', 'Every screen, question and tip in three languages — switch any time.'],
    ];
    $screens = [
        ['welcome', 'Welcome'], ['profile', 'Choose your profile'], ['home', 'Home'], ['question', 'Assessment question'],
        ['result', 'Your result'], ['progress', 'Progress'], ['eye-care', 'Eye care topics'], ['tips', 'Care tips'],
        ['settings', 'Comfort settings'], ['language', 'Three languages'],
    ];
    $security = [
        ['phone-portrait', 'Two-factor admin sign-in', 'Authenticator codes with single-use recovery codes and replay protection.'],
        ['lock-closed', 'Account lockout', 'Accounts lock for 15 minutes after 5 wrong passwords, from any network.'],
        ['shield-checkmark', 'Browser shield', 'Content Security Policy with nonces, HSTS, clickjacking and sniffing protection.'],
        ['key', 'Encrypted secrets', 'Hashed passwords, encrypted 2FA secrets, and the app token kept in the phone\'s encrypted storage.'],
        ['image', 'Safe uploads', 'Every uploaded picture is rebuilt from its pixels, so hidden content never gets stored.'],
        ['checkmark-done', 'Tested end to end', '97 automated tests on SQLite and PostgreSQL, plus black-box attack tests.'],
    ];
    $stack = ['React Native', 'Expo', 'Laravel 13', 'PHP 8.4', 'PostgreSQL', 'Tailwind CSS', 'Docker', 'Sanctum', 'Google Sign-In'];
@endphp
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MataKo — Your vision, your power</title>
    <meta name="description" content="MataKo is a digital eye strain self-assessment and eye care app for students and professionals, in English, Filipino and Cebuano.">
    <meta property="og:title" content="MataKo — Your vision, your power">
    <meta property="og:description" content="Check your eyes for digital eye strain, follow care plans and guided eye exercises — in English, Filipino and Cebuano.">
    <meta property="og:image" content="{{ asset('images/showcase/home.webp') }}">
    <meta name="theme-color" content="#062A3C">
    <link rel="icon" href="{{ asset('images/matako-eye.png') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800" rel="stylesheet">
    @vite(['resources/css/app.css'])
    <script type="module" src="{{ asset('ionicons/ionicons.esm.js') }}" @nonce></script>
</head>
<body class="bg-[#F6F4F2] font-admin text-navy antialiased">
    {{-- Header --}}
    <header class="sticky top-0 z-40 border-b border-navy/5 bg-[#F6F4F2]/80 backdrop-blur-xl">
        <div class="mx-auto flex h-16 max-w-6xl items-center gap-6 px-5">
            <a href="#top" class="flex items-center gap-2.5">
                <img src="{{ asset('images/matako-eye.png') }}" alt="" class="h-6 w-auto">
                <span class="text-lg font-extrabold tracking-tight">MataKo</span>
            </a>
            <nav class="ml-6 hidden items-center gap-6 text-sm font-medium text-slate-600 md:flex" aria-label="Sections">
                <a href="#features" class="transition hover:text-brand">Features</a>
                <a href="#screens" class="transition hover:text-brand">Screens</a>
                <a href="#admin" class="transition hover:text-brand">Admin console</a>
                <a href="#security" class="transition hover:text-brand">Security</a>
            </nav>
            <div class="ml-auto flex items-center gap-2">
                <a href="{{ route('admin.login') }}" class="hidden rounded-xl px-3.5 py-2 text-sm font-semibold text-navy transition hover:bg-white sm:block">Admin</a>
                <a href="#download" class="rounded-xl bg-brand px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-brand/25 transition hover:bg-brand-600">Get the app</a>
            </div>
        </div>
    </header>

    <main id="top">
        {{-- Hero --}}
        <section class="relative overflow-hidden">
            <div class="pointer-events-none absolute -top-40 -right-40 h-[36rem] w-[36rem] rounded-full bg-brand/15 blur-3xl"></div>
            <div class="pointer-events-none absolute top-1/2 -left-48 h-96 w-96 rounded-full bg-navy/5 blur-3xl"></div>
            <div class="relative mx-auto grid max-w-6xl items-center gap-12 px-5 pt-14 pb-20 lg:grid-cols-[1.05fr_1fr] lg:pt-20">
                <div>
                    <p class="inline-flex items-center gap-2 rounded-full bg-white px-3 py-1.5 text-xs font-semibold text-brand-600 shadow-sm ring-1 ring-brand-100">
                        <ion-icon name="eye" class="text-sm"></ion-icon> Digital eye strain self-assessment
                    </p>
                    <h1 class="mt-5 text-5xl leading-[1.05] font-extrabold tracking-tight sm:text-6xl">
                        Your vision,<br><span class="text-brand">your power.</span>
                    </h1>
                    <p class="mt-6 max-w-xl text-lg leading-relaxed text-slate-600">
                        MataKo helps students and professionals spot digital eye strain early — a quick self-assessment, a clear result with a care plan, and guided eye exercises to build healthier screen habits.
                    </p>
                    <div class="mt-8 flex flex-wrap gap-3">
                        @if ($apkUrl)
                            <a href="{{ $apkUrl }}" class="inline-flex items-center gap-2 rounded-2xl bg-brand px-6 py-3.5 font-semibold text-white shadow-xl shadow-brand/30 transition hover:-translate-y-0.5 hover:bg-brand-600">
                                <ion-icon name="logo-android" class="text-xl"></ion-icon> Download for Android
                            </a>
                        @else
                            <a href="#download" class="inline-flex items-center gap-2 rounded-2xl bg-brand px-6 py-3.5 font-semibold text-white shadow-xl shadow-brand/30 transition hover:-translate-y-0.5 hover:bg-brand-600">
                                <ion-icon name="logo-android" class="text-xl"></ion-icon> Get the Android app
                            </a>
                        @endif
                        <a href="#screens" class="inline-flex items-center gap-2 rounded-2xl bg-white px-6 py-3.5 font-semibold text-navy shadow-sm ring-1 ring-navy/10 transition hover:-translate-y-0.5">
                            <ion-icon name="images-outline" class="text-xl"></ion-icon> See the screens
                        </a>
                    </div>
                    <dl class="mt-10 grid max-w-lg grid-cols-3 gap-4">
                        @foreach ([[$stats['questions'], 'question check-up'], [$stats['exercises'], 'guided exercises'], [3, 'languages']] as [$value, $label])
                            <div class="rounded-2xl bg-white/70 p-4 ring-1 ring-navy/5">
                                <dt class="text-xs text-slate-500">{{ $label }}</dt>
                                <dd class="text-2xl font-extrabold">{{ $value }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </div>

                <div class="relative mx-auto flex w-full max-w-md justify-center lg:max-w-none">
                    <div class="absolute inset-x-10 bottom-0 h-3/4 rounded-[3rem] bg-gradient-to-br from-brand to-[#FF9A3D] opacity-90"></div>
                    <div class="relative grid w-full grid-cols-2 items-end gap-4 px-4 pt-6 sm:gap-6">
                        {!! $phone('home', 'MataKo home screen', 'translate-y-6') !!}
                        {!! $phone('result', 'Assessment result screen', '-translate-y-6') !!}
                    </div>
                    <img src="{{ asset('images/mascot.png') }}" alt="" class="absolute -bottom-10 -left-4 hidden w-28 drop-shadow-xl sm:block">
                </div>
            </div>
        </section>

        {{-- Built with --}}
        <section class="border-y border-navy/5 bg-white/60">
            <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-center gap-x-8 gap-y-3 px-5 py-6 text-sm font-semibold text-slate-500">
                <span class="text-xs tracking-[0.2em] text-slate-400 uppercase">Built with</span>
                @foreach ($stack as $tech)
                    <span>{{ $tech }}</span>
                @endforeach
            </div>
        </section>

        {{-- Features --}}
        <section id="features" class="mx-auto max-w-6xl scroll-mt-20 px-5 py-24">
            <p class="text-sm font-semibold tracking-widest text-brand uppercase">Features</p>
            <h2 class="mt-2 max-w-2xl text-4xl font-extrabold tracking-tight">Everything for healthier eyes, in one gentle app.</h2>
            <div class="mt-12 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($features as [$icon, $title, $text])
                    <article class="group rounded-3xl bg-white p-6 shadow-sm ring-1 ring-navy/5 transition hover:-translate-y-1 hover:shadow-xl hover:shadow-navy/5">
                        <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-brand-100 text-brand transition group-hover:bg-brand group-hover:text-white">
                            <ion-icon name="{{ $icon }}" class="text-2xl"></ion-icon>
                        </span>
                        <h3 class="mt-5 text-lg font-bold">{{ $title }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ $text }}</p>
                    </article>
                @endforeach
            </div>
        </section>

        {{-- How it works --}}
        <section class="bg-white">
            <div class="mx-auto max-w-6xl px-5 py-24">
                <p class="text-sm font-semibold tracking-widest text-brand uppercase">How it works</p>
                <h2 class="mt-2 text-4xl font-extrabold tracking-tight">From check-up to care plan in minutes.</h2>
                <ol class="mt-14 grid gap-12 md:grid-cols-3">
                    @foreach ([
                        ['profile', 'Choose your profile', 'Student or professional — the questions and tips follow your day.'],
                        ['question', 'Answer the check-up', 'Rate how often you feel each symptom: Never, Occasionally, or Often.'],
                        ['result', 'Get your result', 'See your level, the score breakdown and a care plan to follow.'],
                    ] as $index => [$image, $title, $text])
                        <li class="flex flex-col items-center text-center">
                            <div class="w-52">{!! $phone($image, $title) !!}</div>
                            <span class="mt-8 flex h-9 w-9 items-center justify-center rounded-full bg-navy text-sm font-bold text-white">{{ $index + 1 }}</span>
                            <h3 class="mt-3 text-lg font-bold">{{ $title }}</h3>
                            <p class="mt-1 max-w-xs text-sm text-slate-600">{{ $text }}</p>
                        </li>
                    @endforeach
                </ol>
            </div>
        </section>

        {{-- Screens --}}
        <section id="screens" class="scroll-mt-20 py-24">
            <div class="mx-auto max-w-6xl px-5">
                <p class="text-sm font-semibold tracking-widest text-brand uppercase">Screens</p>
                <h2 class="mt-2 text-4xl font-extrabold tracking-tight">A calm, eye-friendly design.</h2>
                <p class="mt-3 max-w-2xl text-slate-600">Warm low-glare surfaces, soft shadows and readable type — with dark mode, high contrast and monochrome when your eyes need a rest.</p>
            </div>
            <div class="mt-12 flex snap-x snap-mandatory scroll-px-5 gap-6 overflow-x-auto px-5 pb-6 lg:scroll-px-[max(1.25rem,calc((100vw_-_72rem)/2_+_1.25rem))] [scrollbar-width:thin] lg:px-[max(1.25rem,calc((100vw_-_72rem)/2_+_1.25rem))]">
                @foreach ($screens as [$image, $caption])
                    <figure class="w-56 shrink-0 snap-start">
                        {!! $phone($image, $caption) !!}
                        <figcaption class="mt-4 text-center text-sm font-semibold text-slate-600">{{ $caption }}</figcaption>
                    </figure>
                @endforeach
            </div>
        </section>

        {{-- Admin console --}}
        <section id="admin" class="relative scroll-mt-20 overflow-hidden bg-gradient-to-br from-navy-900 via-navy to-navy-800 text-white">
            <div class="pointer-events-none absolute -top-32 right-0 h-96 w-96 rounded-full bg-brand/20 blur-3xl"></div>
            <div class="relative mx-auto grid max-w-6xl items-center gap-14 px-5 py-24 lg:grid-cols-[0.9fr_1.1fr]">
                <div>
                    <p class="text-sm font-semibold tracking-widest text-orange-300 uppercase">Admin console</p>
                    <h2 class="mt-2 text-4xl font-extrabold tracking-tight">Run the whole app from the web.</h2>
                    <ul class="mt-8 space-y-4 text-slate-200">
                        @foreach ([
                            ['create', 'Edit questions, tips, exercises and results — changes reach the app right away.'],
                            ['sparkles', 'Picks a matching mascot picture as you type a new question.'],
                            ['language', 'Built-in machine translation drafts Filipino and Cebuano for review.'],
                            ['bar-chart', 'Results, users, CSV exports and a full activity log.'],
                            ['shield-checkmark', 'A security center with sign-in alerts and protection status.'],
                        ] as [$icon, $text])
                            <li class="flex gap-3"><ion-icon name="{{ $icon }}" class="mt-0.5 shrink-0 text-xl text-orange-300"></ion-icon><span>{{ $text }}</span></li>
                        @endforeach
                    </ul>
                </div>
                <div class="relative">
                    <figure class="overflow-hidden rounded-2xl bg-white shadow-2xl ring-1 ring-white/10">
                        <div class="flex items-center gap-1.5 bg-slate-100 px-4 py-2.5">
                            <span class="h-2.5 w-2.5 rounded-full bg-red-400"></span><span class="h-2.5 w-2.5 rounded-full bg-amber-400"></span><span class="h-2.5 w-2.5 rounded-full bg-emerald-400"></span>
                            <span class="ml-3 truncate rounded-md bg-white px-3 py-0.5 text-[11px] text-slate-500">{{ parse_url(url('/admin'), PHP_URL_HOST) }}/admin/questions</span>
                        </div>
                        <img src="{{ asset('images/showcase/admin-questions.webp') }}" alt="Admin console: questions" loading="lazy" width="1600" height="1000" class="block w-full">
                    </figure>
                    <figure class="absolute -bottom-10 -left-8 hidden w-1/2 overflow-hidden rounded-xl shadow-2xl ring-4 ring-navy md:block">
                        <img src="{{ asset('images/showcase/admin-question-edit.webp') }}" alt="Admin console: editing a question with translations" loading="lazy" width="1600" height="1000" class="block w-full">
                    </figure>
                </div>
            </div>
        </section>

        {{-- Security --}}
        <section id="security" class="mx-auto max-w-6xl scroll-mt-20 px-5 py-24">
            <p class="text-sm font-semibold tracking-widest text-brand uppercase">Security &amp; quality</p>
            <h2 class="mt-2 max-w-2xl text-4xl font-extrabold tracking-tight">Built to protect the people who use it.</h2>
            <div class="mt-12 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($security as [$icon, $title, $text])
                    <article class="flex gap-4 rounded-3xl bg-white p-6 ring-1 ring-navy/5">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600"><ion-icon name="{{ $icon }}" class="text-xl"></ion-icon></span>
                        <div><h3 class="font-bold">{{ $title }}</h3><p class="mt-1 text-sm leading-relaxed text-slate-600">{{ $text }}</p></div>
                    </article>
                @endforeach
            </div>
        </section>

        {{-- Download --}}
        <section id="download" class="scroll-mt-20 px-5 pb-24">
            <div class="relative mx-auto max-w-6xl overflow-hidden rounded-[2.5rem] bg-gradient-to-br from-brand to-[#FF9A3D] px-8 py-14 text-white shadow-2xl shadow-brand/30 sm:px-14">
                <div class="pointer-events-none absolute -right-20 -bottom-24 h-80 w-80 rounded-full border-[40px] border-white/15"></div>
                <div class="relative grid items-center gap-10 md:grid-cols-[1fr_auto]">
                    <div>
                        <h2 class="text-4xl font-extrabold tracking-tight">Give your eyes a break.</h2>
                        <p class="mt-3 max-w-xl text-white/90">Free for Android. Check your eyes in a few minutes, in English, Filipino or Cebuano.</p>
                        <div class="mt-8 flex flex-wrap gap-3">
                            @if ($apkUrl)
                                <a href="{{ $apkUrl }}" class="inline-flex items-center gap-2 rounded-2xl bg-navy px-6 py-3.5 font-semibold text-white shadow-xl transition hover:-translate-y-0.5">
                                    <ion-icon name="logo-android" class="text-xl"></ion-icon> Download the APK
                                </a>
                            @else
                                <span class="inline-flex items-center gap-2 rounded-2xl bg-navy/80 px-6 py-3.5 font-semibold text-white">
                                    <ion-icon name="time-outline" class="text-xl"></ion-icon> Android download coming soon
                                </span>
                            @endif
                        </div>
                        <p class="mt-5 flex items-center gap-1.5 text-xs text-white/80"><ion-icon name="information-circle-outline"></ion-icon>Hosted on a free plan: after a quiet spell, the first load can take up to a minute.</p>
                    </div>
                    <img src="{{ asset('images/mascot.png') }}" alt="The MataKo mascot" class="mx-auto w-40 drop-shadow-2xl md:w-48">
                </div>
            </div>
        </section>
    </main>

    <footer class="border-t border-navy/5">
        <div class="mx-auto flex max-w-6xl flex-wrap items-center gap-4 px-5 py-8 text-sm text-slate-500">
            <img src="{{ asset('images/matako-eye.png') }}" alt="" class="h-5 w-auto">
            <span>© {{ now()->year }} MataKo · Designed and built by {{ $author }}</span>
            <nav class="ml-auto flex gap-5" aria-label="Footer">
                <a href="{{ route('admin.login') }}" class="hover:text-brand">Admin</a>
            </nav>
        </div>
    </footer>
</body>
</html>
