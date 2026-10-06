@php
    $config = \App\Models\Tip::KINDS[$kind];
    $kinds = \App\Models\Tip::KINDS;
    $languages = \App\Models\Tip::LANGUAGES;
    $childKinds = collect($kinds)->filter(fn ($child) => ($child['parent'] ?? null) === $kind)->keys();

    $tabMeta = [
        'student' => ['Student Tips', 'The expandable sections on the student Tips page.', 'school', 'bg-brand-100 text-brand'],
        'professional' => ['Professional Tips', 'The expandable sections on the professional Tips page.', 'briefcase', 'bg-navy/10 text-navy'],
        'daily_tip' => ["Today's Eye Tip", 'One tip is shown each day on Home and Tips, rotating through the list.', 'bulb', 'bg-amber-50 text-amber-600'],
        'fact' => ['Did You Know?', 'Short facts shown at the bottom of the Tips page.', 'sparkles', 'bg-sky-50 text-sky-600'],
        'topics' => ['Eye Health Topics', 'Topics on the Eye Care tab, each with its own page of symptoms and relief tips.', 'eye', 'bg-emerald-50 text-emerald-600'],
        'exercises' => ['Exercises', 'Eye exercises on the Eye Care tab. The five built-in ones keep their special screens.', 'fitness', 'bg-violet-50 text-violet-600'],
        'results' => ['Result Screens', 'What users see after a self-assessment, for each strain level.', 'ribbon', 'bg-rose-50 text-rose-600'],
    ];
    [$tabTitle, $tabDescription, $tabIcon, $tabColors] = $tabMeta[$tab];

    // Text in the preview language, falling back to English.
    $text = function ($tip, string $field) use ($lang) {
        $english = $tip->field($field);
        if ($lang === 'en') {
            return [$english, false];
        }
        $translated = $tip->translations[$lang][$field] ?? null;

        return filled($translated) ? [$translated, false] : [$english, filled($english)];
    };
    $missingLanguages = function ($tip) use ($languages) {
        $missing = [];
        foreach ($languages as $code => $language) {
            foreach ($tip->translatableFields() as $field) {
                if (filled($tip->field($field)) && blank($tip->translations[$code][$field] ?? null)) {
                    $missing[$code] = $language;
                    break;
                }
            }
        }

        return $missing;
    };

    $totals = collect($tabStats);
    $allFields = max(1, $totals->sum('fields'));
    $percent = fn (array $stat, string $code) => $stat['fields'] ? (int) floor($stat['translated'][$code] / $stat['fields'] * 100) : 100;
    $resultColors = ['LOW' => '#1DB815', 'MEDIUM' => '#F58216', 'HIGH' => '#E5121B'];
    $current = $tabStats[$tab];
@endphp

<x-admin.layout title="App Content" subtitle="Everything users read in the app — in English, Filipino and Cebuano.">
    <x-slot:actions>
        @unless ($config['fixed'] ?? false)
            <a href="{{ route('admin.tips.create', ['kind' => $kind, 'audience' => in_array($tab, ['student', 'professional'], true) ? $tab : 'all']) }}" class="admin-btn-primary h-11">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                Add {{ Str::lower($config['label']) }}
            </a>
        @endunless
    </x-slot:actions>

    {{-- Overview --}}
    <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ([
            ['Content items', $totals->sum('items'), 'Across 7 sections', 'albums', 'bg-brand-100 text-brand'],
            ['Shown in the app', $totals->sum('items') - $totals->sum('hidden'), $totals->sum('hidden').' hidden', 'phone-portrait', 'bg-emerald-50 text-emerald-600'],
        ] as [$label, $value, $caption, $icon, $colors])
            <div class="admin-card flex items-center gap-4">
                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full {{ $colors }}"><ion-icon name="{{ $icon }}" class="text-2xl"></ion-icon></span>
                <div><p class="text-2xl font-bold text-navy">{{ $value }}</p><p class="text-sm font-medium text-slate-600">{{ $label }}</p><p class="text-xs text-slate-400">{{ $caption }}</p></div>
            </div>
        @endforeach
        @foreach ($languages as $code => $language)
            @php($done = (int) floor($totals->sum(fn ($stat) => $stat['translated'][$code]) / $allFields * 100))
            <div class="admin-card">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="flex h-12 w-12 items-center justify-center rounded-full bg-sky-50 text-sm font-bold text-sky-700">{{ strtoupper($code) }}</span>
                        <div><p class="text-2xl font-bold text-navy">{{ $done }}%</p><p class="text-sm font-medium text-slate-600">{{ $language }} translated</p></div>
                    </div>
                    @if ($done === 100)
                        <span class="admin-badge bg-emerald-50 text-emerald-700">Complete</span>
                    @endif
                </div>
                <div class="mt-3 h-2 rounded-full bg-slate-100"><div @class(['h-2 rounded-full', 'bg-emerald-500' => $done === 100, 'bg-brand' => $done < 100]) style="width: {{ $done }}%"></div></div>
            </div>
        @endforeach
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-[280px_minmax(0,1fr)]">
        {{-- Section navigation --}}
        <nav class="admin-card self-start p-3" aria-label="Content sections">
            <p class="px-3 pt-2 pb-2 text-[11px] font-semibold tracking-widest text-slate-400 uppercase">Sections</p>
            @foreach ($tabMeta as $key => [$label, , $icon, $colors])
                @php($stat = $tabStats[$key])
                @php($complete = min($percent($stat, 'fil'), $percent($stat, 'ceb')))
                <a href="{{ route('admin.tips.index', ['tab' => $key, 'lang' => $lang === 'en' ? null : $lang]) }}"
                   @class(['group flex items-center gap-3 rounded-xl px-3 py-2.5 transition', 'bg-brand-50 ring-1 ring-brand/20' => $tab === $key, 'hover:bg-slate-50' => $tab !== $key])
                   @if ($tab === $key) aria-current="page" @endif>
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg {{ $colors }}"><ion-icon name="{{ $icon }}" class="text-lg"></ion-icon></span>
                    <span class="min-w-0 flex-1">
                        <span @class(['block truncate text-sm font-semibold', 'text-brand-600' => $tab === $key, 'text-navy' => $tab !== $key])>{{ $label }}</span>
                        <span class="mt-1 flex items-center gap-2">
                            <span class="h-1 flex-1 rounded-full bg-slate-200"><span @class(['block h-1 rounded-full', 'bg-emerald-500' => $complete === 100, 'bg-amber-400' => $complete < 100]) style="width: {{ $complete }}%"></span></span>
                            <span class="text-[10px] text-slate-400">{{ $complete }}%</span>
                        </span>
                    </span>
                    <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-600">{{ $stat['items'] }}</span>
                </a>
            @endforeach
        </nav>

        <div class="min-w-0">
            {{-- Section header --}}
            <div class="admin-card flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl {{ $tabColors }}"><ion-icon name="{{ $tabIcon }}" class="text-2xl"></ion-icon></span>
                    <div>
                        <h2 class="text-lg font-bold tracking-tight text-navy">{{ $tabTitle }}</h2>
                        <p class="text-sm text-slate-500">{{ $tabDescription }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-1 rounded-xl bg-slate-100 p-1" role="group" aria-label="Preview language">
                    <span class="px-2 text-xs font-medium text-slate-500">Preview</span>
                    @foreach (['en' => 'English'] + $languages as $code => $language)
                        <a href="{{ route('admin.tips.index', ['tab' => $tab, 'lang' => $code === 'en' ? null : $code]) }}"
                           @class(['rounded-lg px-3 py-1.5 text-xs font-semibold transition', 'bg-white text-navy shadow-sm' => $lang === $code, 'text-slate-500 hover:text-navy' => $lang !== $code])
                           @if ($lang === $code) aria-current="true" @endif>{{ $language }}</a>
                    @endforeach
                </div>
            </div>

            @if ($lang !== 'en')
                <p class="mt-3 flex items-center gap-2 rounded-xl bg-sky-50 px-4 py-2.5 text-sm text-sky-800">
                    <ion-icon name="language" class="text-lg"></ion-icon>
                    Showing the {{ $languages[$lang] }} text. Lines marked <span class="admin-badge bg-amber-100 text-amber-800">English</span> have no {{ $languages[$lang] }} translation yet.
                </p>
            @endif

            {{-- Items --}}
            <div class="mt-4 space-y-4">
                @forelse ($items as $item)
                    @php([$title, $titleFallback] = $text($item, 'title'))
                    @php([$body, $bodyFallback] = $text($item, 'body'))
                    @php($missing = $missingLanguages($item))
                    <details @class(['admin-card group/item p-0', 'opacity-70' => ! $item->is_active]) @if ($childKinds->isNotEmpty() && $loop->first) open @endif>
                        <summary @class(["flex list-none items-start gap-4 p-5 [&::-webkit-details-marker]:hidden", "cursor-pointer" => $childKinds->isNotEmpty(), "pointer-events-none [&_a]:pointer-events-auto [&_button]:pointer-events-auto" => $childKinds->isEmpty()])>
                            @if ($kind === 'result')
                                <span class="mt-0.5 flex h-11 w-11 shrink-0 items-center justify-center rounded-xl text-white shadow-sm" style="background: {{ $resultColors[$item->key] ?? '#F58216' }}"><ion-icon name="pulse" class="text-xl"></ion-icon></span>
                            @elseif ($kind === 'daily_tip')
                                <span class="mt-0.5 flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-lg font-bold text-amber-600">{{ $loop->iteration }}</span>
                            @else
                                <span class="mt-0.5 flex h-11 w-11 shrink-0 items-center justify-center rounded-xl {{ $tabColors }}"><ion-icon name="{{ $item->icon ?: $tabIcon }}" class="text-xl"></ion-icon></span>
                            @endif
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    @if (filled($title))
                                        <h3 class="text-base font-semibold text-navy">{{ $title }}</h3>
                                    @endif
                                    @if ($titleFallback || (! filled($title) && $bodyFallback))<span class="admin-badge bg-amber-100 text-amber-800">English</span>@endif
                                    @if (! $item->is_active)<span class="admin-badge bg-slate-200 text-slate-600">Hidden</span>@endif
                                    @if ($kind === 'exercise')<span class="admin-badge bg-violet-50 text-violet-700">{{ $item->field('seconds') }}s</span>@endif
                                    @if (in_array($kind, ['daily_tip', 'fact'], true) && $item->audience !== 'all')<span class="admin-badge bg-slate-100 text-slate-600">{{ ucfirst($item->audience) }}s only</span>@endif
                                </div>
                                <p @class(['mt-1 text-sm leading-relaxed', 'text-slate-500' => filled($title), 'text-navy' => ! filled($title)])>{{ $body }}</p>
                                <div class="mt-3 flex flex-wrap items-center gap-2">
                                    @foreach ($languages as $code => $language)
                                        <span @class(['inline-flex items-center gap-1 rounded-md px-2 py-0.5 text-[11px] font-semibold', 'bg-emerald-50 text-emerald-700' => ! isset($missing[$code]), 'bg-amber-50 text-amber-700' => isset($missing[$code])])
                                              title="{{ isset($missing[$code]) ? $language.' translation incomplete' : $language.' translated' }}">
                                            {{ strtoupper($code) }} {{ isset($missing[$code]) ? '•' : '✓' }}
                                        </span>
                                    @endforeach
                                    @foreach ($childKinds as $childKind)
                                        @php($childCount = $item->children->where('kind', $childKind)->count())
                                        <span class="text-xs text-slate-400">· {{ $childCount }} {{ Str::lower(Str::plural($kinds[$childKind]['label'], $childCount)) }}</span>
                                    @endforeach
                                </div>
                            </div>
                            <div class="flex shrink-0 items-center gap-2">
                                <x-admin.tip-actions :tip="$item" :first="$loop->first" :last="$loop->last" />
                                @if ($childKinds->isNotEmpty())
                                    <span class="rounded-lg p-1.5 text-slate-400 transition group-open/item:rotate-180" aria-hidden="true">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
                                    </span>
                                @endif
                            </div>
                        </summary>

                        @if ($childKinds->isNotEmpty())
                            <div class="border-t border-slate-100 bg-slate-50/60 px-5 py-4">
                                @foreach ($childKinds as $childKind)
                                    @php($children = $item->children->where('kind', $childKind)->values())
                                    <div class="mb-4 last:mb-0">
                                        <div class="mb-2 flex items-center justify-between">
                                            <h4 class="text-xs font-semibold tracking-wider text-slate-500 uppercase">{{ Str::plural($kinds[$childKind]['label']) }}</h4>
                                            <a href="{{ route('admin.tips.create', ['kind' => $childKind, 'parent' => $item->id]) }}" class="inline-flex items-center gap-1 rounded-lg px-2 py-1 text-xs font-semibold text-brand transition hover:bg-brand-50">
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                                Add
                                            </a>
                                        </div>
                                        <ul class="grid gap-2 xl:grid-cols-2">
                                            @forelse ($children as $child)
                                                @php([$childTitle, $childFallback] = $text($child, 'title'))
                                                @php([$childBody] = $text($child, 'body'))
                                                @php($childMissing = $missingLanguages($child))
                                                <li @class(['flex items-start gap-3 rounded-xl border border-slate-200 bg-white p-3', 'opacity-60' => ! $child->is_active])>
                                                    <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-50" style="color: {{ $child->color ?: '#F58216' }}">
                                                        <ion-icon name="{{ $child->icon ?: 'ellipse' }}" class="text-base"></ion-icon>
                                                    </span>
                                                    <div class="min-w-0 flex-1">
                                                        <p class="flex flex-wrap items-center gap-1.5 text-sm font-semibold text-navy">
                                                            {{ $childTitle }}
                                                            @if ($childFallback)<span class="admin-badge bg-amber-100 py-0 text-amber-800">English</span>@endif
                                                            @if ($child->field('extra'))<span class="admin-badge bg-slate-100 py-0 text-slate-600">View More</span>@endif
                                                            @if (! $child->is_active)<span class="admin-badge bg-slate-200 py-0 text-slate-600">Hidden</span>@endif
                                                        </p>
                                                        <p class="mt-0.5 text-xs leading-relaxed text-slate-500">{{ $childBody }}</p>
                                                        @if ($childMissing)
                                                            <p class="mt-1 text-[11px] font-medium text-amber-700">Needs {{ implode(' & ', $childMissing) }}</p>
                                                        @endif
                                                    </div>
                                                    <x-admin.tip-actions :tip="$child" :first="$loop->first" :last="$loop->last" compact />
                                                </li>
                                            @empty
                                                <li class="rounded-xl border border-dashed border-slate-300 p-4 text-center text-sm text-slate-500 xl:col-span-2">Nothing here yet.</li>
                                            @endforelse
                                        </ul>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </details>
                @empty
                    <div class="admin-card py-14 text-center">
                        <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-full {{ $tabColors }}"><ion-icon name="{{ $tabIcon }}" class="text-2xl"></ion-icon></span>
                        <p class="mt-3 font-semibold text-navy">No {{ Str::lower(Str::plural($config['label'])) }} yet</p>
                        <p class="text-sm text-slate-500">Add the first one with the button at the top.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-admin.layout>
