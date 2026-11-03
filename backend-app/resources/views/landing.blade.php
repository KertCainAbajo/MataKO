@php
    $img = fn (string $name) => asset('images/showcase/'.$name.'.webp');
    $phone = fn (string $image, string $alt, string $extra = '') => '<div class="rounded-[2.4rem] bg-[#0b2230] p-2.5 shadow-[0_40px_80px_-24px_rgba(0,0,0,0.55)] ring-1 ring-white/10 '.$extra.'"><img src="'.asset('images/showcase/'.$image.'.webp').'" alt="'.e($alt).'" loading="lazy" width="480" height="1004" class="block w-full rounded-[1.9rem]"></div>';
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
    $stack = [['logo-react', 'React Native'], ['phone-portrait-outline', 'Expo'], ['logo-laravel', 'Laravel 13'], ['code-slash', 'PHP 8.4'], ['server-outline', 'PostgreSQL'], ['color-palette-outline', 'Tailwind CSS'], ['logo-docker', 'Docker'], ['key-outline', 'Sanctum'], ['logo-google', 'Google Sign-In'], ['language-outline', 'English · Filipino · Cebuano']];
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MataKo — Your vision, your power</title>
    <meta name="description" content="MataKo is a digital eye strain self-assessment and eye care app for students and professionals, in English, Filipino and Cebuano.">
    <meta property="og:title" content="MataKo — Your vision, your power">
    <meta property="og:description" content="Check your eyes for digital eye strain, follow care plans and guided eye exercises — in English, Filipino and Cebuano.">
    <meta property="og:image" content="{{ $img('home') }}">
    <meta name="theme-color" content="#041f2d">
    <link rel="icon" href="{{ asset('images/matako-eye.png') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800,900" rel="stylesheet">
    <script @nonce>document.documentElement.classList.add('js');</script>
    @vite(['resources/css/app.css', 'resources/js/landing.js'])
    <script type="module" src="{{ asset('ionicons/ionicons.esm.js') }}" @nonce></script>
</head>
<body class="bg-[#F6F4F2] font-admin text-navy antialiased selection:bg-brand/30">
    {{-- Header: sits on top of the dark hero and scrolls away with it --}}
    <header id="site-header" class="absolute inset-x-0 top-0 z-50 px-3">
        <div class="mx-auto mt-3 flex h-14 max-w-6xl items-center gap-6 rounded-2xl px-4 transition-all duration-300 sm:px-5">
            <a href="#top" class="flex items-center gap-2.5">
                <img src="{{ asset('images/matako-eye.png') }}" alt="" class="h-6 w-auto">
                <span class="text-lg font-extrabold tracking-tight text-white">MataKo</span>
            </a>
            <nav class="ml-6 hidden items-center gap-1 text-sm font-medium md:flex" aria-label="Sections">
                @foreach ([['#features', 'Features'], ['#how', 'How it works'], ['#screens', 'Screens'], ['#admin', 'Admin console'], ['#security', 'Security']] as [$href, $label])
                    <a href="{{ $href }}" class="rounded-lg px-3 py-1.5 text-white/70 transition hover:bg-white/10 hover:text-white">{{ $label }}</a>
                @endforeach
            </nav>
            <a href="#download" class="ml-auto inline-flex items-center gap-1.5 rounded-xl bg-brand px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-brand/30 transition hover:-translate-y-0.5 hover:bg-brand-600">
                <ion-icon name="logo-android"></ion-icon> Get the app
            </a>
        </div>
    </header>

    <main id="top">
        {{-- Hero: centred headline over an "iris" of rings, with three phones rising from the bottom edge --}}
        <section class="relative isolate overflow-hidden rounded-b-[2.5rem] bg-[#03121B] text-white sm:rounded-b-[4rem]">
            <div class="pointer-events-none absolute inset-0 -z-10" aria-hidden="true">
                <div class="absolute inset-0 bg-[radial-gradient(ellipse_70%_55%_at_50%_-8%,rgba(64,150,196,0.30),transparent_70%)]"></div>
                <div class="absolute inset-0 bg-[radial-gradient(rgba(255,255,255,0.09)_1px,transparent_1px)] [background-size:26px_26px] [mask-image:radial-gradient(ellipse_65%_55%_at_50%_30%,black,transparent_75%)]"></div>
                {{-- Iris rings centred behind the phones --}}
                <div class="absolute top-[58rem] left-1/2 sm:top-[60rem]">
                    <div class="absolute top-1/2 left-1/2 h-[56rem] w-[56rem] -translate-x-1/2 -translate-y-1/2 rounded-full bg-[radial-gradient(circle,rgba(18,82,114,0.55),transparent_62%)]"></div>
                    @foreach (['h-[22rem] w-[22rem] border-white/[0.08]', 'h-[30rem] w-[30rem] border-brand/30', 'h-[46rem] w-[46rem] border-white/[0.06]', 'h-[60rem] w-[60rem] border-white/[0.04]'] as $ring)
                        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 rounded-full border {{ $ring }}"></div>
                    @endforeach
                    <div class="animate-orbit absolute -top-[15rem] -left-[15rem] h-[30rem] w-[30rem] rounded-full bg-[conic-gradient(from_0deg,transparent_0deg,transparent_250deg,#FFB067_330deg,#F58216_360deg)] [mask-image:radial-gradient(farthest-side,transparent_calc(100%-2px),black_calc(100%-2px))]"></div>
                    <div class="animate-orbit-reverse absolute -top-[23rem] -left-[23rem] h-[46rem] w-[46rem] rounded-full bg-[conic-gradient(from_180deg,transparent_0deg,transparent_290deg,rgba(125,200,240,0.7)_360deg)] [mask-image:radial-gradient(farthest-side,transparent_calc(100%-1px),black_calc(100%-1px))]"></div>
                </div>
            </div>

            <div class="mx-auto max-w-4xl px-5 pt-32 text-center sm:pt-36" data-reveal>
                <p class="inline-flex items-center gap-2 rounded-full bg-white/[0.06] py-1.5 pr-4 pl-1.5 text-xs font-semibold text-slate-200 ring-1 ring-white/10 backdrop-blur">
                    <span class="rounded-full bg-brand px-2.5 py-0.5 text-[11px] font-bold text-white">Free</span>
                    Digital eye strain self-assessment for Android
                </p>
                <h1 class="mt-7 text-[3.4rem] leading-[0.95] font-black tracking-[-0.04em] sm:text-8xl">
                    Your vision,<br>
                    <span class="animate-shimmer bg-[linear-gradient(100deg,#FFC48A,#F58216_35%,#FFB067_55%,#F58216_80%)] [background-size:200%_auto] bg-clip-text pr-1 text-transparent">your power.</span>
                </h1>
                <p class="mx-auto mt-7 max-w-2xl text-lg leading-relaxed text-slate-300/90 sm:text-xl">
                    Spot digital eye strain early. A quick self-assessment, a clear result with a care plan, and guided eye exercises — for students and professionals.
                </p>
                <div class="mt-10 flex flex-wrap justify-center gap-3">
                    <a href="{{ $apkUrl ?: '#download' }}" class="group relative inline-flex items-center gap-2.5 overflow-hidden rounded-full bg-gradient-to-b from-[#FF9634] to-brand px-7 py-4 font-bold text-white shadow-[0_0_0_1px_rgba(255,255,255,0.15)_inset,0_18px_40px_-12px_rgba(245,130,22,0.8)] transition hover:-translate-y-0.5">
                        <span class="absolute inset-0 -translate-x-full bg-gradient-to-r from-transparent via-white/30 to-transparent transition duration-700 group-hover:translate-x-full"></span>
                        <ion-icon name="logo-android" class="text-xl"></ion-icon> Download for Android
                        <ion-icon name="arrow-forward" class="transition group-hover:translate-x-1"></ion-icon>
                    </a>
                    <a href="#screens" class="inline-flex items-center gap-2 rounded-full bg-white/[0.06] px-7 py-4 font-bold text-white ring-1 ring-white/15 backdrop-blur transition hover:-translate-y-0.5 hover:bg-white/10">
                        Explore the app <ion-icon name="chevron-down" class="text-lg"></ion-icon>
                    </a>
                </div>
                <dl class="mx-auto mt-10 flex max-w-xl justify-center divide-x divide-white/10">
                    @foreach ([[$stats['questions'], 'Question check-up'], [$stats['exercises'], 'Guided exercises'], [3, 'Languages']] as [$value, $label])
                        <div class="flex flex-1 flex-col items-center px-3">
                            <dd class="text-3xl font-black tabular-nums sm:text-4xl" data-count="{{ $value }}">{{ $value }}</dd>
                            <dt class="order-last mt-1 text-xs font-medium tracking-wide text-slate-400 sm:text-sm">{{ $label }}</dt>
                        </div>
                    @endforeach
                </dl>
            </div>

            {{-- Phone fan --}}
            <div class="relative mx-auto mt-14 h-[24rem] max-w-6xl sm:h-[30rem]" data-reveal>
                <div class="absolute top-16 left-1/2 w-40 -translate-x-[128%] -rotate-[9deg] opacity-80 sm:w-52">{!! $phone('question', 'Assessment question screen') !!}</div>
                <div class="absolute top-16 left-1/2 w-40 translate-x-[28%] rotate-[9deg] opacity-80 sm:w-52">{!! $phone('result', 'Assessment result screen') !!}</div>
                <div class="absolute top-0 left-1/2 z-10 w-52 -translate-x-1/2 sm:w-64">{!! $phone('home', 'MataKo home screen', 'shadow-[0_40px_120px_-20px_rgba(245,130,22,0.35)]') !!}</div>

                <div class="animate-float absolute top-12 left-[calc(50%-31rem)] z-20 hidden w-56 rounded-2xl bg-[#0A2231]/90 p-4 text-left shadow-2xl ring-1 ring-white/10 backdrop-blur-xl lg:block">
                    <div class="flex items-center gap-3">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-red-500/15 text-red-300 ring-1 ring-red-400/30"><ion-icon name="pulse" class="text-xl"></ion-icon></span>
                        <div><p class="text-[11px] font-semibold tracking-wider text-slate-400 uppercase">Result ready</p><p class="text-sm font-bold">Severe Eye Strain</p></div>
                    </div>
                    <div class="mt-3 h-1.5 overflow-hidden rounded-full bg-white/10"><div class="h-full w-[82%] rounded-full bg-gradient-to-r from-amber-400 to-red-400"></div></div>
                    <p class="mt-2 text-xs text-slate-400">Care plan included</p>
                </div>
                <div class="animate-float-slow absolute top-40 right-[calc(50%-31rem)] z-20 hidden w-52 rounded-2xl bg-[#0A2231]/90 p-4 text-left shadow-2xl ring-1 ring-white/10 backdrop-blur-xl [animation-delay:-3s] lg:block">
                    <p class="flex items-center gap-2 text-sm font-bold"><span class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand/15 text-orange-300 ring-1 ring-brand/30"><ion-icon name="timer-outline"></ion-icon></span>Eye Palming</p>
                    <p class="mt-3 font-mono text-3xl font-black tracking-tight text-white">01:00</p>
                    <div class="mt-3 flex gap-1.5">
                        @foreach (['EN', 'FIL', 'CEB'] as $index => $code)
                            <span @class(['rounded-md px-2 py-0.5 text-[11px] font-bold', 'bg-brand text-white' => $index === 0, 'bg-white/10 text-slate-300' => $index > 0])>{{ $code }}</span>
                        @endforeach
                    </div>
                </div>
                <div class="absolute inset-x-0 bottom-0 z-10 h-28 bg-gradient-to-t from-[#03121B] to-transparent"></div>
            </div>
        </section>

        {{-- Built with (endless strip) --}}
        <section class="relative overflow-hidden py-8 [mask-image:linear-gradient(90deg,transparent,black_12%,black_88%,transparent)]" aria-label="Built with">
            <div class="animate-marquee flex w-max gap-4 hover:[animation-play-state:paused]">
                @foreach ([1, 2] as $copy)
                    @foreach ($stack as [$icon, $name])
                        <span class="inline-flex items-center gap-2 rounded-full bg-white px-4 py-2 text-sm font-semibold whitespace-nowrap text-slate-600 shadow-sm ring-1 ring-navy/5" @if ($copy === 2) aria-hidden="true" @endif>
                            <ion-icon name="{{ $icon }}" class="text-base text-brand"></ion-icon>{{ $name }}
                        </span>
                    @endforeach
                @endforeach
            </div>
        </section>

        {{-- Features: bento grid --}}
        <section id="features" class="mx-auto max-w-6xl scroll-mt-24 px-5 py-20">
            <div class="max-w-2xl" data-reveal>
                <p class="text-sm font-bold tracking-[0.2em] text-brand uppercase">Features</p>
                <h2 class="mt-3 text-4xl font-black tracking-tight sm:text-5xl">Everything for healthier eyes, in one gentle app.</h2>
            </div>
            <div class="mt-12 grid gap-5 lg:grid-cols-6">
                {{-- Big: assessment --}}
                <article class="group relative min-h-[26rem] overflow-hidden rounded-[2rem] bg-gradient-to-br from-navy to-navy-700 p-8 text-white shadow-xl lg:col-span-3 lg:row-span-2" data-reveal>
                    <div class="pointer-events-none absolute -top-24 -right-24 h-72 w-72 rounded-full bg-brand/30 blur-3xl transition duration-700 group-hover:scale-125"></div>
                    <span class="relative flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-brand to-[#FF9A3D] shadow-lg shadow-brand/40"><ion-icon name="clipboard" class="text-2xl"></ion-icon></span>
                    <h3 class="relative mt-6 max-w-[13rem] text-2xl font-bold">Guided self-assessment</h3>
                    <p class="relative mt-2 max-w-xs text-slate-300 lg:max-w-[13rem]">A {{ $stats['questions'] }}-question eye strain check with a friendly character for every symptom, tailored to students or professionals.</p>
                    <div class="relative mx-auto mt-8 w-52 transition duration-500 group-hover:-translate-y-3 lg:absolute lg:right-6 lg:-bottom-20 lg:mt-0 lg:w-52">{!! $phone('question', 'Assessment question') !!}</div>
                </article>
                <article class="group rounded-[2rem] bg-white p-7 shadow-sm ring-1 ring-navy/5 transition duration-300 hover:-translate-y-1 hover:shadow-2xl hover:shadow-brand/10 hover:ring-brand/30 lg:col-span-3" data-reveal>
                    <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-brand-100 text-brand transition group-hover:bg-gradient-to-br group-hover:from-brand group-hover:to-[#FF9A3D] group-hover:text-white"><ion-icon name="pulse" class="text-2xl"></ion-icon></span>
                    <h3 class="mt-5 text-lg font-bold">Clear results &amp; care plans</h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600">Mild, Moderate or Severe — with a score breakdown and practical tips matched to the result.</p>
                    <div class="mt-5 flex gap-2 text-xs font-bold">
                        <span class="rounded-full bg-emerald-50 px-3 py-1 text-emerald-700 ring-1 ring-emerald-200">Mild</span>
                        <span class="rounded-full bg-amber-50 px-3 py-1 text-amber-700 ring-1 ring-amber-200">Moderate</span>
                        <span class="rounded-full bg-red-50 px-3 py-1 text-red-700 ring-1 ring-red-200">Severe</span>
                    </div>
                </article>
                @foreach ([
                    ['trending-up', 'Progress tracking', 'Screen breaks, time in the app, eye-health history and milestones.', 'lg:col-span-3'],
                    ['eye', 'Eye exercises', $stats['exercises'].' guided exercises with timers — focus shifting, palming, blinking and more.', 'lg:col-span-2'],
                ] as [$icon, $title, $text, $span])
                    <article class="group rounded-[2rem] bg-white p-7 shadow-sm ring-1 ring-navy/5 transition duration-300 hover:-translate-y-1 hover:shadow-2xl hover:shadow-brand/10 hover:ring-brand/30 {{ $span }}" data-reveal>
                        <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-brand-100 text-brand transition group-hover:bg-gradient-to-br group-hover:from-brand group-hover:to-[#FF9A3D] group-hover:text-white"><ion-icon name="{{ $icon }}" class="text-2xl"></ion-icon></span>
                        <h3 class="mt-5 text-lg font-bold">{{ $title }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ $text }}</p>
                    </article>
                @endforeach
                <article class="group rounded-[2rem] bg-white p-7 shadow-sm ring-1 ring-navy/5 transition duration-300 hover:-translate-y-1 hover:shadow-2xl hover:shadow-brand/10 hover:ring-brand/30 lg:col-span-2" data-reveal>
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-bold">Comfort display modes</h3>
                            <p class="mt-2 text-sm text-slate-600">Dark mode, high contrast, monochrome and a text-contrast slider for tired eyes.</p>
                        </div>
                        <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-brand-100 text-brand"><ion-icon name="contrast" class="text-2xl"></ion-icon></span>
                    </div>
                    <div class="mt-5 grid grid-cols-3 gap-2 text-center text-xs font-semibold">
                        <span class="rounded-xl bg-[#F6F4F2] py-3 text-navy ring-1 ring-navy/10">Light</span>
                        <span class="rounded-xl bg-navy py-3 text-white">Dark</span>
                        <span class="rounded-xl bg-gradient-to-r from-slate-300 to-slate-500 py-3 text-white">Mono</span>
                    </div>
                </article>
                <article class="group rounded-[2rem] bg-gradient-to-br from-brand to-[#FF9A3D] p-7 text-white shadow-xl shadow-brand/20 transition duration-300 hover:-translate-y-1 lg:col-span-2" data-reveal>
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-bold">English, Filipino &amp; Cebuano</h3>
                            <p class="mt-2 text-sm text-white/90">Every screen, question and tip in three languages — switch any time.</p>
                        </div>
                        <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-white/20"><ion-icon name="language" class="text-2xl"></ion-icon></span>
                    </div>
                    <div class="mt-5 flex flex-wrap gap-2 text-sm">
                        @foreach (['“Your vision, your power.”', '“Imong panan-aw, imong kusog.”'] as $line)
                            <span class="rounded-full bg-white/20 px-3 py-1.5 font-medium backdrop-blur">{{ $line }}</span>
                        @endforeach
                    </div>
                </article>
            </div>
        </section>

        {{-- How it works --}}
        <section id="how" class="relative scroll-mt-24 overflow-hidden bg-white py-24">
            <div class="mx-auto max-w-6xl px-5">
                <div class="mx-auto max-w-2xl text-center" data-reveal>
                    <p class="text-sm font-bold tracking-[0.2em] text-brand uppercase">How it works</p>
                    <h2 class="mt-3 text-4xl font-black tracking-tight sm:text-5xl">From check-up to care plan in minutes.</h2>
                </div>
                <ol class="relative mt-16 grid gap-14 md:grid-cols-3">
                    @foreach ([
                        ['profile', 'Choose your profile', 'Student or professional — the questions and tips follow your day.'],
                        ['question', 'Answer the check-up', 'Rate how often you feel each symptom: Never, Occasionally, or Often.'],
                        ['result', 'Get your result', 'See your level, the score breakdown and a care plan to follow.'],
                    ] as $index => [$image, $title, $text])
                        <li class="relative flex flex-col items-center text-center" data-reveal @style(['transition-delay: '.($index * 120).'ms'])>
                            <div class="w-52 transition duration-500 hover:-translate-y-2">{!! $phone($image, $title) !!}</div>
                            @if ($index < 2)
                                <span class="absolute top-[24.6rem] left-[calc(50%+2rem)] hidden h-0.5 w-[calc(100%-1rem)] bg-[linear-gradient(90deg,#F58216_0_8px,transparent_8px_16px)] [background-size:16px_2px] md:block" aria-hidden="true"></span>
                            @endif
                            <span class="relative z-10 mt-8 flex h-11 w-11 items-center justify-center rounded-full bg-gradient-to-br from-brand to-[#FF9A3D] text-base font-black text-white shadow-lg shadow-brand/40 ring-4 ring-white">{{ $index + 1 }}</span>
                            <h3 class="mt-4 text-lg font-bold">{{ $title }}</h3>
                            <p class="mt-1 max-w-xs text-sm text-slate-600">{{ $text }}</p>
                        </li>
                    @endforeach
                </ol>
            </div>
        </section>

        {{-- Screens --}}
        <section id="screens" class="scroll-mt-24 py-24">
            <div class="mx-auto flex max-w-6xl flex-wrap items-end justify-between gap-6 px-5" data-reveal>
                <div>
                    <p class="text-sm font-bold tracking-[0.2em] text-brand uppercase">Screens</p>
                    <h2 class="mt-3 text-4xl font-black tracking-tight sm:text-5xl">A calm, eye-friendly design.</h2>
                    <p class="mt-3 max-w-2xl text-slate-600">Warm low-glare surfaces, soft shadows and readable type — with dark mode, high contrast and monochrome when your eyes need a rest.</p>
                </div>
                <div class="flex gap-2">
                    <button type="button" data-gallery="-1" class="flex h-12 w-12 items-center justify-center rounded-full bg-white text-navy shadow-sm ring-1 ring-navy/10 transition hover:bg-navy hover:text-white" aria-label="Previous screens"><ion-icon name="arrow-back" class="text-lg"></ion-icon></button>
                    <button type="button" data-gallery="1" class="flex h-12 w-12 items-center justify-center rounded-full bg-brand text-white shadow-lg shadow-brand/30 transition hover:bg-brand-600" aria-label="Next screens"><ion-icon name="arrow-forward" class="text-lg"></ion-icon></button>
                </div>
            </div>
            <div class="relative mt-12 [mask-image:linear-gradient(90deg,transparent,black_5%,black_95%,transparent)]">
                <div id="gallery" class="flex snap-x snap-mandatory scroll-px-5 gap-7 overflow-x-auto px-5 pt-4 pb-8 [scrollbar-width:none] lg:scroll-px-[max(1.25rem,calc((100vw_-_72rem)/2_+_1.25rem))] lg:px-[max(1.25rem,calc((100vw_-_72rem)/2_+_1.25rem))] [&::-webkit-scrollbar]:hidden">
                    @foreach ($screens as [$image, $caption])
                        <figure class="group w-56 shrink-0 snap-start">
                            <div class="transition duration-500 group-hover:-translate-y-3 group-hover:rotate-1">{!! $phone($image, $caption) !!}</div>
                            <figcaption class="mt-5 text-center text-sm font-semibold text-slate-600 transition group-hover:text-brand">{{ $caption }}</figcaption>
                        </figure>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- Admin console --}}
        <section id="admin" class="relative isolate scroll-mt-24 overflow-hidden bg-[#03121B] text-white">
            <div class="pointer-events-none absolute inset-0 -z-10" aria-hidden="true">
                <div class="absolute inset-0 bg-[radial-gradient(ellipse_55%_65%_at_72%_45%,rgba(40,120,165,0.35),transparent_70%)]"></div>
                <div class="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.04)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.04)_1px,transparent_1px)] [background-size:56px_56px] [mask-image:radial-gradient(ellipse_at_70%_50%,black,transparent_70%)]"></div>
            </div>
            <div class="mx-auto grid max-w-6xl items-center gap-16 px-5 py-28 lg:grid-cols-[0.85fr_1.15fr]">
                <div data-reveal>
                    <p class="text-sm font-bold tracking-[0.2em] text-orange-300 uppercase">Admin console</p>
                    <h2 class="mt-3 text-4xl font-black tracking-tight sm:text-5xl">Run the whole app from the web.</h2>
                    <ul class="mt-9 space-y-5">
                        @foreach ([
                            ['create', 'Edit questions, tips, exercises and results — changes reach the app right away.'],
                            ['sparkles', 'Picks a matching mascot picture as you type a new question.'],
                            ['language', 'Built-in machine translation drafts Filipino and Cebuano for review.'],
                            ['bar-chart', 'Results, users, CSV exports and a full activity log.'],
                            ['shield-checkmark', 'A security center with sign-in alerts and protection status.'],
                        ] as [$icon, $text])
                            <li class="flex gap-4"><span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-white/10 ring-1 ring-white/15"><ion-icon name="{{ $icon }}" class="text-lg text-orange-300"></ion-icon></span><span class="pt-1.5 text-slate-200">{{ $text }}</span></li>
                        @endforeach
                    </ul>
                </div>
                <div class="relative [perspective:1800px]" data-reveal>
                    <figure class="overflow-hidden rounded-2xl bg-white shadow-[0_50px_100px_-30px_rgba(0,0,0,0.7)] ring-1 ring-white/10 transition duration-700 [transform:rotateY(-10deg)_rotateX(4deg)] hover:[transform:none]">
                        <div class="flex items-center gap-1.5 bg-slate-100 px-4 py-2.5">
                            <span class="h-2.5 w-2.5 rounded-full bg-red-400"></span><span class="h-2.5 w-2.5 rounded-full bg-amber-400"></span><span class="h-2.5 w-2.5 rounded-full bg-emerald-400"></span>
                            <span class="ml-3 flex items-center gap-1.5 truncate rounded-md bg-white px-3 py-0.5 text-[11px] text-slate-500"><ion-icon name="lock-closed" class="text-emerald-500"></ion-icon>{{ parse_url(url('/'), PHP_URL_HOST) }}/admin</span>
                        </div>
                        <img src="{{ $img('admin-questions') }}" alt="Admin console: questions" loading="lazy" width="1600" height="1000" class="block w-full">
                    </figure>
                    <figure class="animate-float-slow absolute -bottom-12 -left-10 hidden w-1/2 overflow-hidden rounded-xl shadow-2xl ring-4 ring-navy md:block">
                        <img src="{{ $img('admin-question-edit') }}" alt="Admin console: editing a question with translations" loading="lazy" width="1600" height="1000" class="block w-full">
                    </figure>
                </div>
            </div>
        </section>

        {{-- Security --}}
        <section id="security" class="mx-auto max-w-6xl scroll-mt-24 px-5 py-24">
            <div class="max-w-2xl" data-reveal>
                <p class="text-sm font-bold tracking-[0.2em] text-brand uppercase">Security &amp; quality</p>
                <h2 class="mt-3 text-4xl font-black tracking-tight sm:text-5xl">Built to protect the people who use it.</h2>
            </div>
            <div class="mt-12 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($security as $index => [$icon, $title, $text])
                    <article class="group relative overflow-hidden rounded-[1.75rem] bg-white p-6 ring-1 ring-navy/5 transition duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-emerald-900/5 hover:ring-emerald-300" data-reveal @style(['transition-delay: '.(($index % 3) * 90).'ms'])>
                        <div class="pointer-events-none absolute -top-16 -right-16 h-40 w-40 rounded-full bg-emerald-100 opacity-0 blur-2xl transition duration-500 group-hover:opacity-100"></div>
                        <span class="relative flex h-11 w-11 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600 ring-1 ring-emerald-100"><ion-icon name="{{ $icon }}" class="text-xl"></ion-icon></span>
                        <h3 class="relative mt-4 font-bold">{{ $title }}</h3>
                        <p class="relative mt-1.5 text-sm leading-relaxed text-slate-600">{{ $text }}</p>
                    </article>
                @endforeach
            </div>
        </section>

        {{-- Download --}}
        <section id="download" class="scroll-mt-24 px-5 pb-28">
            <div class="relative isolate mx-auto max-w-6xl overflow-hidden rounded-[2.75rem] bg-[#03121B] px-8 py-16 text-white shadow-2xl sm:px-16" data-reveal>
                <div class="pointer-events-none absolute inset-0 -z-10" aria-hidden="true">
                    <div class="absolute inset-0 bg-[radial-gradient(ellipse_60%_80%_at_80%_50%,rgba(40,120,165,0.38),transparent_70%)]"></div>
                    <div class="absolute top-1/2 right-[6%] h-[30rem] w-[30rem] -translate-y-1/2 rounded-full border border-white/[0.07]"></div>
                    <div class="absolute top-1/2 right-[calc(6%+5rem)] h-[20rem] w-[20rem] -translate-y-1/2 rounded-full border border-brand/25"></div>
                    <div class="animate-orbit absolute top-1/2 right-[6%] -mt-[15rem] h-[30rem] w-[30rem] rounded-full bg-[conic-gradient(from_0deg,transparent_0deg,transparent_260deg,#FFB067_330deg,#F58216_360deg)] [mask-image:radial-gradient(farthest-side,transparent_calc(100%-2px),black_calc(100%-2px))]"></div>
                </div>
                <div class="grid items-center gap-12 md:grid-cols-[1fr_auto]">
                    <div>
                        <h2 class="text-4xl font-black tracking-tight sm:text-5xl">Give your eyes <span class="animate-shimmer bg-[linear-gradient(110deg,#F58216,#FFB067,#F58216,#FFB067)] [background-size:200%_auto] bg-clip-text text-transparent">a break.</span></h2>
                        <p class="mt-4 max-w-xl text-lg text-slate-300">Free for Android. Check your eyes in a few minutes, in English, Filipino or Cebuano.</p>
                        <div class="mt-9 flex flex-wrap items-center gap-5">
                            @if ($apkUrl)
                                <a href="{{ $apkUrl }}" class="group inline-flex items-center gap-3 rounded-2xl bg-brand px-7 py-4 text-lg font-bold text-white shadow-[0_14px_44px_-8px_rgba(245,130,22,0.75)] transition hover:-translate-y-0.5 hover:bg-brand-600">
                                    <ion-icon name="logo-android" class="text-2xl"></ion-icon>
                                    <span class="text-left leading-tight"><span class="block text-[11px] font-semibold tracking-wide text-white/80 uppercase">Free for Android</span>Download the APK</span>
                                    <ion-icon name="download-outline" class="text-xl transition group-hover:translate-y-0.5"></ion-icon>
                                </a>
                            @else
                                <span class="inline-flex items-center gap-2 rounded-2xl bg-white/10 px-6 py-4 font-semibold ring-1 ring-white/20"><ion-icon name="time-outline" class="text-xl"></ion-icon> Android download coming soon</span>
                            @endif
                            <span class="flex items-center gap-2 text-sm text-slate-400"><ion-icon name="shield-checkmark-outline" class="text-lg text-emerald-400"></ion-icon>Asks only for Internet access</span>
                        </div>
                        <p class="mt-6 flex items-center gap-1.5 text-xs text-slate-400"><ion-icon name="information-circle-outline"></ion-icon>Hosted on a free plan: after a quiet spell, the first load can take up to a minute.</p>
                    </div>
                    <div class="relative mx-auto w-48 md:w-52">
                        <div class="animate-float">{!! $phone('welcome', 'MataKo welcome screen') !!}</div>
                        <img src="{{ asset('images/mascot.png') }}" alt="The MataKo mascot" class="animate-float-slow absolute -bottom-6 -left-14 w-24 drop-shadow-[0_20px_30px_rgba(0,0,0,0.5)] [animation-delay:-4s]">
                    </div>
                </div>
            </div>
        </section>
    </main>

    <script @nonce>
        // Sections fade up as they come into view; numbers count up once.
        const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        const countUp = (element) => {
            const target = Number(element.dataset.count);
            if (reduce || !target) return;
            const start = performance.now();
            const step = (now) => {
                const progress = Math.min(1, (now - start) / 1200);
                element.textContent = Math.round(target * (1 - Math.pow(1 - progress, 3)));
                if (progress < 1) requestAnimationFrame(step);
            };
            requestAnimationFrame(step);
        };
        const observer = new IntersectionObserver((entries) => entries.forEach((entry) => {
            if (!entry.isIntersecting) return;
            entry.target.classList.add('is-visible');
            entry.target.querySelectorAll('[data-count]').forEach(countUp);
            observer.unobserve(entry.target);
        }), { threshold: 0.12 });
        document.querySelectorAll('[data-reveal]').forEach((element) => observer.observe(element));

        // Screen gallery arrows.
        const gallery = document.getElementById('gallery');
        document.querySelectorAll('[data-gallery]').forEach((button) => button.addEventListener('click', () => {
            gallery.scrollBy({ left: Number(button.dataset.gallery) * gallery.clientWidth * 0.8, behavior: reduce ? 'auto' : 'smooth' });
        }));
    </script>
</body>
</html>
