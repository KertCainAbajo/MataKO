{{-- The MataKo eye logo with the "Admin Console" label, as on the login page. --}}
<div {{ $attributes->merge(['class' => 'flex items-center gap-3']) }}>
    <img src="{{ asset('images/matako-eye.png') }}" alt="" class="h-9 w-auto">
    <div class="leading-none">
        <p class="text-2xl font-extrabold tracking-tight text-navy">MataKo</p>
        <p class="mt-1 text-sm font-medium text-slate-500">Admin Console</p>
    </div>
</div>
