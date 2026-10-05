@props(['tip'])

@php
    $missing = [];
    foreach (\App\Models\Tip::LANGUAGES as $code => $language) {
        foreach ($tip->translatableFields() as $field) {
            if (filled($tip->field($field)) && blank($tip->translations[$code][$field] ?? null)) {
                $missing[] = $language;
                break;
            }
        }
    }
@endphp

@if ($missing)
    <span class="admin-badge ml-1 bg-amber-100 text-amber-800" title="Missing: {{ implode(', ', $missing) }}">Needs translation</span>
@endif
