@php
    $languages = \App\Models\Tip::LANGUAGES;
    $maxAnswer = \App\Models\Question::MAX_ANSWER;
    $active = $questions->where('is_active', true);
    $maxScore = $active->count() * $maxAnswer;
    $mildUpTo = intdiv($maxScore, 3);
    $moderateUpTo = intdiv($maxScore * 2, 3);
    $translated = fn ($question, $code) => filled($question->translations[$code]['question'] ?? null);
    $fullyTranslated = $questions->filter(fn ($question) => collect(array_keys($languages))->every(fn ($code) => $translated($question, $code)))->count();
    $totalAnswers = $answers->sum('answers');
    $mostReported = $questions->filter(fn ($question) => isset($answers[$question->symptom]))
        ->sortByDesc(fn ($question) => $answers[$question->symptom]->average)->take(5);
    $image = fn ($question) => $question->imageUrl() ?? ($question->bundledImage() ? asset('images/questions/'.$question->bundledImage().'.png') : null);
    $audiences = [
        'student' => ['Students', 'Studying, classes and schoolwork', 'school', 'bg-brand-100 text-brand'],
        'professional' => ['Professionals', 'Office, remote and screen-based work', 'briefcase', 'bg-navy/10 text-navy'],
    ];
@endphp

<x-admin.layout title="Questions" subtitle="The self-assessment questionnaires shown in the app.">
    <x-slot:actions>
        <a href="{{ route('admin.questions.create', ['audience' => $audience]) }}" class="admin-btn-primary h-11">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Add question
        </a>
    </x-slot:actions>

    {{-- Questionnaire switcher --}}
    <div class="grid gap-5 md:grid-cols-2">
        @foreach ($audiences as $key => [$label, $caption, $icon, $colors])
            <a href="{{ route('admin.questions.index', ['audience' => $key, 'lang' => $lang === 'en' ? null : $lang]) }}"
               @class(['admin-card flex items-center gap-4 transition', 'ring-2 ring-brand' => $audience === $key, 'hover:-translate-y-0.5 hover:shadow-md' => $audience !== $key])
               @if ($audience === $key) aria-current="page" @endif>
                <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl {{ $colors }}"><ion-icon name="{{ $icon }}" class="text-2xl"></ion-icon></span>
                <div class="min-w-0 flex-1">
                    <p class="text-lg font-bold text-navy">{{ $label }} questionnaire</p>
                    <p class="text-sm text-slate-500">{{ $caption }}</p>
                </div>
                <div class="text-right">
                    <p class="text-2xl font-bold text-navy">{{ $counts[$key] ?? 0 }}</p>
                    <p class="text-xs text-slate-500">questions</p>
                </div>
                @if ($audience === $key)
                    <span class="flex h-7 w-7 items-center justify-center rounded-full bg-brand text-white" aria-hidden="true">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                    </span>
                @endif
            </a>
        @endforeach
    </div>

    {{-- Key figures --}}
    <div class="mt-5 grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ([
            [$active->count(), 'Active questions', ($questions->count() - $active->count()).' hidden', 'list', 'bg-brand-100 text-brand'],
            [$maxScore, 'Maximum score', $active->count().' × '.$maxAnswer.' points', 'trophy', 'bg-amber-50 text-amber-600'],
            [$fullyTranslated.'/'.$questions->count(), 'Fully translated', 'Filipino and Cebuano', 'language', 'bg-sky-50 text-sky-600'],
            [number_format($totalAnswers), 'Answers collected', 'From '.$audiences[$audience][0], 'chatbubbles', 'bg-emerald-50 text-emerald-600'],
        ] as [$value, $label, $caption, $icon, $colors])
            <div class="admin-card flex items-center gap-4">
                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full {{ $colors }}"><ion-icon name="{{ $icon }}" class="text-2xl"></ion-icon></span>
                <div><p class="text-2xl font-bold text-navy">{{ $value }}</p><p class="text-sm font-medium text-slate-600">{{ $label }}</p><p class="text-xs text-slate-400">{{ $caption }}</p></div>
            </div>
        @endforeach
    </div>

    <div class="mt-6 grid gap-6 min-[1380px]:grid-cols-3">
        {{-- Question list --}}
        <section class="min-w-0 min-[1380px]:col-span-2">
            <div class="admin-card mb-4 flex flex-wrap items-center justify-between gap-3 py-4">
                <div>
                    <h2 class="admin-card-title">{{ $audiences[$audience][0] }} questions, in the order users see them</h2>
                    <p class="admin-card-subtitle">Changes apply the next time someone starts a self-assessment.</p>
                </div>
                <div class="flex items-center gap-1 rounded-xl bg-slate-100 p-1" role="group" aria-label="Preview language">
                    <span class="px-2 text-xs font-medium text-slate-500">Preview</span>
                    @foreach (['en' => 'English'] + $languages as $code => $language)
                        <a href="{{ route('admin.questions.index', ['audience' => $audience, 'lang' => $code === 'en' ? null : $code]) }}"
                           @class(['rounded-lg px-3 py-1.5 text-xs font-semibold transition', 'bg-white text-navy shadow-sm' => $lang === $code, 'text-slate-500 hover:text-navy' => $lang !== $code])>{{ $language }}</a>
                    @endforeach
                </div>
            </div>

            <ol class="space-y-3">
                @forelse ($questions as $question)
                    @php($stat = $answers[$question->symptom] ?? null)
                    @php($text = $lang === 'en' ? $question->question : ($question->translations[$lang]['question'] ?? null))
                    @php($share = $stat ? $stat->average / $maxAnswer * 100 : 0)
                    <li @class(['admin-card flex items-start gap-4 p-4 transition hover:shadow-md', 'opacity-60' => ! $question->is_active])>
                        <span class="mt-1 flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-navy text-sm font-bold text-white">{{ $loop->iteration }}</span>
                        <div class="flex h-20 w-20 shrink-0 items-center justify-center overflow-hidden rounded-2xl bg-[#F4F4F4]">
                            @if ($image($question))
                                <img src="{{ $image($question) }}" alt="" class="h-full w-full object-contain p-1">
                            @else
                                <ion-icon name="image-outline" class="text-2xl text-slate-300"></ion-icon>
                            @endif
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="flex flex-wrap items-center gap-2">
                                <span class="admin-badge bg-brand-50 text-brand-600">{{ $question->symptom }}</span>
                                @unless ($question->is_active)<span class="admin-badge bg-slate-200 text-slate-600">Hidden</span>@endunless
                                @foreach ($languages as $code => $language)
                                    <span @class(['inline-flex items-center rounded-md px-1.5 py-0.5 text-[11px] font-semibold', 'bg-emerald-50 text-emerald-700' => $translated($question, $code), 'bg-amber-50 text-amber-700' => ! $translated($question, $code)])
                                          title="{{ $language }} {{ $translated($question, $code) ? 'translated' : 'missing' }}">{{ strtoupper($code) }} {{ $translated($question, $code) ? '✓' : '•' }}</span>
                                @endforeach
                            </p>
                            @if (filled($text))
                                <p class="mt-2 text-[15px] leading-snug font-medium text-navy">{{ $text }}</p>
                            @else
                                <p class="mt-2 text-[15px] leading-snug font-medium text-navy">{{ $question->question }}
                                    <span class="admin-badge ml-1 bg-amber-100 text-amber-800">No {{ $languages[$lang] }} yet</span></p>
                            @endif
                            <div class="mt-3 flex items-center gap-3">
                                <div class="h-1.5 flex-1 rounded-full bg-slate-100" title="Average answer">
                                    <div @class(['h-1.5 rounded-full', 'bg-emerald-500' => $share < 34, 'bg-brand' => $share >= 34 && $share < 67, 'bg-red-500' => $share >= 67]) style="width: {{ max($stat ? 3 : 0, $share) }}%"></div>
                                </div>
                                <span class="w-48 shrink-0 text-right text-xs text-slate-500">
                                    @if ($stat)
                                        Avg {{ number_format($stat->average, 1) }} of {{ $maxAnswer }} · {{ $stat->answers }} {{ Str::plural('answer', $stat->answers) }}
                                    @else
                                        No answers yet
                                    @endif
                                </span>
                            </div>
                        </div>
                        <div class="flex shrink-0 items-center gap-1">
                            @foreach (['up' => 'm4.5 15.75 7.5-7.5 7.5 7.5', 'down' => 'm19.5 8.25-7.5 7.5-7.5-7.5'] as $direction => $path)
                                <form method="POST" action="{{ route('admin.questions.move', $question) }}">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="direction" value="{{ $direction }}">
                                    <button class="flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-500 transition hover:border-brand/40 hover:text-brand disabled:pointer-events-none disabled:opacity-30"
                                            aria-label="Move {{ $direction }}" title="Move {{ $direction }}" @disabled(($direction === 'up' && $loop->parent->first) || ($direction === 'down' && $loop->parent->last))>
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $path }}"/></svg>
                                    </button>
                                </form>
                            @endforeach
                            <a href="{{ route('admin.questions.edit', $question) }}" class="flex h-9 items-center gap-1.5 rounded-lg bg-navy px-3 text-sm font-semibold text-white transition hover:bg-navy-700">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125"/></svg>
                                Edit
                            </a>
                        </div>
                    </li>
                @empty
                    <li class="admin-card py-14 text-center">
                        <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-brand-100 text-brand"><ion-icon name="help-circle" class="text-2xl"></ion-icon></span>
                        <p class="mt-3 font-semibold text-navy">No questions yet</p>
                        <p class="text-sm text-slate-500">The self-assessment is unavailable until you add one.</p>
                    </li>
                @endforelse
            </ol>
        </section>

        <div class="grid gap-6 self-start md:max-[1379px]:grid-cols-2">
            {{-- Scoring --}}
            <section class="admin-card">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50 text-amber-600"><ion-icon name="calculator" class="text-xl"></ion-icon></span>
                    <div><h2 class="admin-card-title">How results are scored</h2><p class="admin-card-subtitle">Based on {{ $active->count() }} active questions</p></div>
                </div>
                <ul class="mt-5 space-y-2 text-sm">
                    @foreach ([['Never', 0], ['Occasionally', 1], ['Often or Always', 2]] as [$answer, $points])
                        <li class="flex justify-between rounded-lg bg-slate-50 px-3 py-2"><span class="text-slate-600">{{ $answer }}</span><span class="font-semibold text-navy">{{ $points }} {{ Str::plural('point', $points) }}</span></li>
                    @endforeach
                </ul>
                <div class="mt-5 flex h-3 overflow-hidden rounded-full" aria-hidden="true">
                    <span class="bg-[#1DB815]" style="flex: {{ max(1, $mildUpTo + 1) }}"></span>
                    <span class="bg-brand" style="flex: {{ max(1, $moderateUpTo - $mildUpTo) }}"></span>
                    <span class="bg-[#E5121B]" style="flex: {{ max(1, $maxScore - $moderateUpTo) }}"></span>
                </div>
                <ul class="mt-4 space-y-2 text-sm">
                    @foreach ([['Mild', '#1DB815', "0 – {$mildUpTo}"], ['Moderate', '#F58216', ($mildUpTo + 1).' – '.$moderateUpTo], ['Severe', '#E5121B', ($moderateUpTo + 1).' – '.$maxScore]] as [$level, $color, $range])
                        <li class="flex items-center justify-between">
                            <span class="flex items-center gap-2 text-slate-700"><span class="h-3 w-3 rounded-full" style="background: {{ $color }}"></span>{{ $level }}</span>
                            <span class="font-semibold text-navy">{{ $range }} points</span>
                        </li>
                    @endforeach
                </ul>
            </section>

            {{-- Most reported --}}
            <section class="admin-card">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-red-50 text-red-600"><ion-icon name="pulse" class="text-xl"></ion-icon></span>
                    <div><h2 class="admin-card-title">Most reported symptoms</h2><p class="admin-card-subtitle">Highest average answer from {{ Str::lower($audiences[$audience][0]) }}</p></div>
                </div>
                <ol class="mt-5 space-y-4">
                    @forelse ($mostReported as $question)
                        @php($stat = $answers[$question->symptom])
                        <li>
                            <div class="flex items-center justify-between gap-3 text-sm">
                                <span class="flex min-w-0 items-center gap-2"><span class="text-xs font-bold text-slate-400">{{ $loop->iteration }}</span><span class="truncate font-medium text-navy">{{ $question->symptom }}</span></span>
                                <span class="shrink-0 font-semibold text-navy">{{ number_format($stat->average, 1) }}<span class="font-normal text-slate-400">/{{ $maxAnswer }}</span></span>
                            </div>
                            <div class="mt-1.5 h-2 rounded-full bg-slate-100"><div class="h-2 rounded-full bg-gradient-to-r from-brand to-red-500" style="width: {{ $stat->average / $maxAnswer * 100 }}%"></div></div>
                        </li>
                    @empty
                        <li class="py-4 text-center text-sm text-slate-500">No answers yet.</li>
                    @endforelse
                </ol>
            </section>
        </div>
    </div>
</x-admin.layout>
