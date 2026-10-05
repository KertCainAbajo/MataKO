@php
    use App\Models\Tip;

    $config = Tip::KINDS[$tip->kind];
    $tab = $config['tab'] === 'audience' ? ($tip->audience ?: 'student') : $config['tab'];
    $inputName = fn (string $code, string $field) => "translations[{$code}][{$field}]";
@endphp

<x-admin.layout :title="($tip->exists ? 'Edit ' : 'Add ').Str::lower($config['label'])">
    <form method="POST" action="{{ $tip->exists ? route('admin.tips.update', $tip) : route('admin.tips.store') }}" class="admin-card max-w-3xl space-y-5">
        @csrf
        @if ($tip->exists)
            @method('PUT')
        @else
            <input type="hidden" name="kind" value="{{ $tip->kind }}">
        @endif

        @if (isset($config['parent']))
            <div>
                <label for="parent_id" class="admin-label">{{ Tip::KINDS[$config['parent']]['label'] }}</label>
                <select id="parent_id" name="parent_id" class="admin-input">
                    @foreach ($parents as $parent)
                        <option value="{{ $parent->id }}" @selected((int) old('parent_id', $tip->parent_id) === $parent->id)>@if ($parent->audience !== 'all'){{ ucfirst($parent->audience) }} · @endif{{ $parent->title }}</option>
                    @endforeach
                </select>
            </div>
        @elseif ($tip->kind === 'category' || in_array($tip->kind, ['daily_tip', 'fact'], true))
            <div>
                <label for="audience" class="admin-label">Shown to</label>
                <select id="audience" name="audience" class="admin-input">
                    @foreach ($tip->kind === 'category' ? ['student', 'professional'] : Tip::AUDIENCES as $audience)
                        <option value="{{ $audience }}" @selected(old('audience', $tip->audience) === $audience)>{{ $audience === 'all' ? 'Everyone' : ucfirst($audience).'s' }}</option>
                    @endforeach
                </select>
            </div>
        @endif

        @foreach ($config['fields'] as $name => [$label, $type])
            @php($value = old($name, $tip->field($name)))
            @if (in_array($type, ['text', 'textarea'], true))
                <fieldset class="rounded-lg border border-slate-200 p-4">
                    <legend class="px-1 text-sm font-semibold text-slate-700">{{ $label }}</legend>
                    <div class="grid gap-3">
                        <div>
                            <label for="{{ $name }}" class="admin-label text-xs text-slate-500">English</label>
                            @if ($type === 'textarea')
                                <textarea id="{{ $name }}" name="{{ $name }}" rows="2" class="admin-input" @required(in_array($name, ['title', 'body'], true))>{{ $value }}</textarea>
                            @else
                                <input id="{{ $name }}" name="{{ $name }}" value="{{ $value }}" class="admin-input" @required(in_array($name, ['title', 'body'], true))>
                            @endif
                        </div>
                        <div class="grid gap-3 sm:grid-cols-2">
                            @foreach (Tip::LANGUAGES as $code => $language)
                                @php($translated = old("translations.{$code}.{$name}", $tip->translations[$code][$name] ?? ''))
                                <div>
                                    <label for="{{ "{$name}_{$code}" }}" class="admin-label text-xs text-slate-500">{{ $language }}</label>
                                    @if ($type === 'textarea')
                                        <textarea id="{{ "{$name}_{$code}" }}" name="{{ $inputName($code, $name) }}" rows="2" class="admin-input">{{ $translated }}</textarea>
                                    @else
                                        <input id="{{ "{$name}_{$code}" }}" name="{{ $inputName($code, $name) }}" value="{{ $translated }}" class="admin-input">
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </fieldset>
            @elseif ($type === 'icon')
                <div>
                    <label for="{{ $name }}" class="admin-label">{{ $label }}</label>
                    <input id="{{ $name }}" name="{{ $name }}" value="{{ $value }}" maxlength="40" placeholder="e.g. eye" class="admin-input max-w-xs">
                    <p class="mt-1 text-xs text-slate-500">Any <a href="https://ionic.io/ionicons" target="_blank" rel="noopener" class="text-brand underline">Ionicons</a> name.</p>
                </div>
            @elseif ($type === 'color')
                <div>
                    <label for="{{ $name }}" class="admin-label">{{ $label }}</label>
                    <input id="{{ $name }}" name="{{ $name }}" type="color" value="{{ $value ?: '#F58216' }}" class="h-10 w-20 rounded border border-slate-300">
                </div>
            @elseif ($type === 'number')
                <div>
                    <label for="{{ $name }}" class="admin-label">{{ $label }}</label>
                    <input id="{{ $name }}" name="{{ $name }}" type="number" min="5" max="3600" value="{{ $value ?: 30 }}" required class="admin-input max-w-[10rem]">
                </div>
            @elseif ($type === 'checkbox')
                <label class="flex items-center gap-2 text-sm">
                    <input type="hidden" name="{{ $name }}" value="0">
                    <input type="checkbox" name="{{ $name }}" value="1" @checked($value) class="rounded border-slate-300 text-brand">
                    {{ $label }}
                </label>
            @endif
        @endforeach

        <p class="text-xs text-slate-500">When you change the English text, update the Filipino and Cebuano text too. If a translation is left empty, users of that language see the English text.</p>

        <label class="flex items-center gap-2 text-sm">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $tip->is_active)) class="rounded border-slate-300 text-brand">
            Show in the app
        </label>

        <div class="flex gap-2">
            <button class="admin-btn-primary">Save</button>
            <a href="{{ route('admin.tips.index', ['tab' => $tab]) }}" class="admin-btn-secondary">Cancel</a>
        </div>
    </form>

    @if ($tip->exists && ! ($config['fixed'] ?? false))
        <details class="admin-card mt-6 max-w-3xl border-red-200">
            <summary class="cursor-pointer text-sm font-semibold text-red-700">Delete</summary>
            <p class="mt-2 text-sm text-slate-600">{{ $tip->children()->exists() ? 'This also deletes everything inside it.' : 'This cannot be undone.' }} To take it out only for now, untick "Show in the app" instead.</p>
            <form method="POST" action="{{ route('admin.tips.destroy', $tip) }}" class="mt-3">
                @csrf @method('DELETE')
                <button class="admin-btn-danger">Delete</button>
            </form>
        </details>
    @endif
</x-admin.layout>
