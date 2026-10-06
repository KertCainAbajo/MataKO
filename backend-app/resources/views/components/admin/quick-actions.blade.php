{{-- Shortcuts to common admin tasks. --}}
<section {{ $attributes->merge(['class' => 'admin-card']) }}>
    <div class="flex items-center gap-2">
        <svg class="h-5 w-5 text-brand" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m3.75 13.5 10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75Z"/></svg>
        <h2 class="admin-card-title">Quick actions</h2>
    </div>
    <div class="mt-4 grid grid-cols-2 gap-3 xl:grid-cols-1 2xl:grid-cols-2">
        @foreach ([
            ['Add question', route('admin.questions.create'), 'bg-brand-100 text-brand', 'M12 4.5v15m7.5-7.5h-15'],
            ['Manage users', route('admin.users.index'), 'bg-navy/10 text-navy', 'M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z'],
            ["Today's tip", route('admin.tips.index', ['tab' => 'daily_tip']), 'bg-emerald-50 text-emerald-600', 'M12 18v-5.25m0 0a6.01 6.01 0 0 0 1.5-.189m-1.5.189a6.01 6.01 0 0 1-1.5-.189m3.75 7.478a12.06 12.06 0 0 1-4.5 0m3.75 2.383a14.406 14.406 0 0 1-3 0M14.25 18v-.192c0-.983.658-1.823 1.508-2.316a7.5 7.5 0 1 0-7.517 0c.85.493 1.509 1.333 1.509 2.316V18'],
            ['Translations', route('admin.tips.index', ['tab' => 'topics']), 'bg-amber-50 text-amber-600', 'M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 0 1 7.843 4.582M12 3a8.997 8.997 0 0 0-7.843 4.582m15.686 0A11.953 11.953 0 0 1 12 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0 1 21 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0 1 12 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 0 1 3 12c0-1.605.42-3.113 1.157-4.418'],
        ] as [$label, $link, $classes, $path])
            <a href="{{ $link }}" class="group flex items-center gap-2 rounded-xl border border-slate-200 px-2.5 py-3 text-[13px] font-medium whitespace-nowrap text-navy transition hover:border-brand/40 hover:bg-brand-50">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full {{ $classes }}">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true"><path d="{{ $path }}"/></svg>
                </span>
                <span class="min-w-0 flex-1 truncate">{{ $label }}</span>
                <svg class="h-4 w-4 text-slate-300 transition group-hover:text-brand" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
            </a>
        @endforeach
    </div>
</section>

