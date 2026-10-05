@props(['risk', 'score' => null, 'max' => null])

@php
    [$label, $classes, $dot] = match ($risk) {
        'HIGH' => ['Severe', 'bg-red-50 text-red-700', 'bg-red-500'],
        'MEDIUM' => ['Moderate', 'bg-orange-50 text-orange-700', 'bg-orange-500'],
        default => ['Mild', 'bg-emerald-50 text-emerald-700', 'bg-emerald-500'],
    };
@endphp

<span {{ $attributes->merge(['class' => "admin-badge {$classes}"]) }}>
    <span class="h-1.5 w-1.5 rounded-full {{ $dot }}"></span>{{ $label }}@if ($score !== null) · {{ $score }}{{ $max ? "/{$max}" : '' }}@endif
</span>
