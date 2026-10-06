@props(['id', 'name'])

{{-- A password field with a lock icon and a show/hide button (wired up by the page's script). --}}
<div class="relative">
    <ion-icon name="lock-closed-outline" class="pointer-events-none absolute top-1/2 left-3.5 -translate-y-1/2 text-lg text-slate-400"></ion-icon>
    <input id="{{ $id }}" name="{{ $name }}" type="password" required {{ $attributes->merge(['class' => 'admin-input pr-11 pl-11']) }}>
    <button type="button" class="absolute top-1/2 right-2 flex -translate-y-1/2 rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-navy" data-toggle="{{ $id }}" aria-label="Show password">
        <ion-icon name="eye-outline" class="text-lg"></ion-icon>
    </button>
</div>
