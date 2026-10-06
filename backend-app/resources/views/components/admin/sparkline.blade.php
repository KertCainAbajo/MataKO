@props(['values', 'color' => '#F58216'])

@php
    // A small trend line over the last 14 days, scaled to the box.
    $width = 96;
    $height = 32;
    $max = max(1, max($values));
    $step = count($values) > 1 ? $width / (count($values) - 1) : $width;
    $points = collect($values)->map(fn ($value, $index) => round($index * $step, 1).','.round($height - 3 - ($value / $max) * ($height - 6), 1))->implode(' ');
    $id = 'spark-'.substr(md5($points.$color), 0, 8);
@endphp

<svg {{ $attributes->merge(['class' => 'h-8 w-24']) }} viewBox="0 0 {{ $width }} {{ $height }}" fill="none" aria-hidden="true">
    <defs>
        <linearGradient id="{{ $id }}" x1="0" y1="0" x2="0" y2="1">
            <stop offset="0%" stop-color="{{ $color }}" stop-opacity="0.25"/>
            <stop offset="100%" stop-color="{{ $color }}" stop-opacity="0"/>
        </linearGradient>
    </defs>
    <polygon points="0,{{ $height }} {{ $points }} {{ $width }},{{ $height }}" fill="url(#{{ $id }})"/>
    <polyline points="{{ $points }}" stroke="{{ $color }}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
