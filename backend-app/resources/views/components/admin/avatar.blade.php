@props(['name', 'size' => 'h-9 w-9 text-xs'])

@php
    $initials = collect(preg_split('/\s+/', trim((string) $name)))->filter()->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))->take(2)->implode('') ?: '?';
    // A stable colour per name, so the same person always gets the same avatar.
    $palette = ['bg-sky-100 text-sky-700', 'bg-emerald-100 text-emerald-700', 'bg-violet-100 text-violet-700', 'bg-amber-100 text-amber-700', 'bg-rose-100 text-rose-700', 'bg-brand-100 text-brand-600'];
    $colour = $palette[crc32((string) $name) % count($palette)];
@endphp

<span {{ $attributes->merge(['class' => "inline-flex shrink-0 items-center justify-center rounded-full font-semibold {$size} {$colour}"]) }} aria-hidden="true">{{ $initials }}</span>
