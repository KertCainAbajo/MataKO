@props(['source', 'target', 'lang'])

{{-- Fills one translation box from its English text (see the translate-script component). --}}
<button type="button" data-translate-source="{{ $source }}" data-translate-target="{{ $target }}" data-translate-lang="{{ $lang }}"
        class="inline-flex items-center gap-1 rounded-md px-1.5 py-0.5 text-[11px] font-semibold text-brand transition hover:bg-brand-50 disabled:opacity-50"
        title="Translate the English text into this language">
    <ion-icon name="language-outline" class="text-sm" data-translate-icon></ion-icon>
    <span data-translate-label>Translate</span>
</button>
