@php
    use App\Models\Tip;

    $config = Tip::KINDS[$kind];
    $childKinds = collect(Tip::KINDS)->filter(fn ($child) => ($child['parent'] ?? null) === $kind)->keys();
    $descriptions = [
        'daily_tip' => 'One tip is shown each day on Home and Tips, rotating through the list.',
        'fact' => 'Shown under "Did You Know?" on the Tips page.',
        'topics' => 'The topics on the Eye Care tab. Each has its own page with symptoms and relief tips.',
        'exercises' => 'The exercises on the Eye Care tab. The five built-in exercises keep their special screens; new ones use a simple timer screen.',
        'results' => 'What users see after a self-assessment, for each strain level. The three levels are fixed; their text and care tips can be changed.',
    ];
@endphp

<x-admin.layout title="App Content" subtitle="Tips, eye health topics, exercises and result screens — in three languages.">
    <x-slot:actions>
        @unless ($config['fixed'] ?? false)
            <a href="{{ route('admin.tips.create', ['kind' => $kind, 'audience' => in_array($tab, ['student', 'professional'], true) ? $tab : 'all']) }}" class="admin-btn-primary">Add {{ Str::lower($config['label']) }}</a>
        @endunless
    </x-slot:actions>

    <div class="mb-5 inline-flex flex-wrap gap-1 rounded-xl bg-slate-200/60 p-1">
        @foreach ($tabs as $value => $label)
            <a href="{{ route('admin.tips.index', ['tab' => $value]) }}" @class(['admin-tab-active' => $tab === $value, 'admin-tab' => ! ($tab === $value)])>{{ $label }}</a>
        @endforeach
    </div>

    <p class="mb-4 text-sm text-slate-600">
        {{ $descriptions[$tab] ?? "The expandable sections on the {$tab} Tips page, in this order." }}
        Every text has Filipino and Cebuano versions; a <span class="admin-badge bg-amber-100 text-amber-800">Needs translation</span> label means users of that language see English.
        Icon names come from <a href="https://ionic.io/ionicons" target="_blank" rel="noopener" class="text-brand underline">Ionicons</a>, for example <code>eye</code>, <code>moon</code> or <code>water</code>.
    </p>

    <div class="space-y-4">
        @forelse ($items as $item)
            <section @class(['admin-card', 'opacity-60' => ! $item->is_active])>
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="min-w-0">
                        <h2 class="admin-card-title">
                            {{ $item->title ?: Str::limit($item->body, 90) }}
                            @unless ($item->is_active)<span class="admin-badge ml-1 bg-slate-200 text-slate-600">Hidden</span>@endunless
                            <x-admin.translation-status :tip="$item" />
                        </h2>
                        @if ($item->title)
                            <p class="text-sm text-slate-500">{{ $item->body }}</p>
                        @endif
                        @if ($kind === 'exercise')
                            <p class="mt-1 text-xs text-slate-500">{{ $item->field('seconds') }} seconds · {{ in_array($item->key, ['palming', 'blinking', 'focus', 'rule', 'rolling'], true) ? 'Built-in screen' : 'Simple timer screen' }}</p>
                        @elseif (in_array($kind, ['daily_tip', 'fact'], true))
                            <p class="mt-1 text-xs text-slate-500">Shown to: {{ $item->audience === 'all' ? 'everyone' : $item->audience.'s' }}</p>
                        @endif
                    </div>
                    <x-admin.tip-actions :tip="$item" :first="$loop->first" :last="$loop->last" />
                </div>

                @foreach ($childKinds as $childKind)
                    @php($children = $item->children->where('kind', $childKind)->values())
                    <div class="mt-4">
                        @if ($childKinds->count() > 1)
                            <h3 class="mb-2 text-xs font-semibold tracking-wide text-slate-500 uppercase">{{ Str::plural(Tip::KINDS[$childKind]['label']) }}</h3>
                        @endif
                        <ul class="space-y-2 border-l-2 border-slate-100 pl-4">
                            @foreach ($children as $child)
                                <li @class(['flex flex-wrap items-start justify-between gap-3 rounded-lg bg-slate-50 p-3', 'opacity-60' => ! $child->is_active])>
                                    <div class="min-w-0">
                                        <p class="text-sm font-medium text-slate-800">
                                            @if ($child->color)<span class="mr-1 inline-block h-2.5 w-2.5 rounded-full" style="background: {{ $child->color }}"></span>@endif
                                            {{ $child->title }}
                                            @if ($child->field('extra'))<span class="admin-badge ml-1 bg-slate-200 text-slate-600">After View More</span>@endif
                                            <x-admin.translation-status :tip="$child" />
                                        </p>
                                        <p class="text-xs text-slate-500">{{ $child->body }}</p>
                                    </div>
                                    <x-admin.tip-actions :tip="$child" :first="$loop->first" :last="$loop->last" />
                                </li>
                            @endforeach
                        </ul>
                        <a href="{{ route('admin.tips.create', ['kind' => $childKind, 'parent' => $item->id]) }}" class="mt-2 inline-block text-sm font-medium text-brand hover:underline">+ Add {{ Str::lower(Tip::KINDS[$childKind]['label']) }}</a>
                    </div>
                @endforeach
            </section>
        @empty
            <p class="admin-card text-center text-sm text-slate-500">Nothing here yet.</p>
        @endforelse
    </div>
</x-admin.layout>
