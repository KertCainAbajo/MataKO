@props(['riskCounts', 'students', 'professionals', 'label' => 'Total'])

@php
    $riskLabels = ['LOW' => ['Mild', '#1DB815'], 'MEDIUM' => ['Moderate', '#F58216'], 'HIGH' => ['Severe', '#E5121B']];
    $riskTotal = array_sum($riskCounts);
    $stops = [];
    $start = 0;
    foreach ($riskLabels as $risk => [, $color]) {
        $end = $riskTotal ? $start + $riskCounts[$risk] / $riskTotal * 100 : $start;
        $stops[] = "{$color} {$start}% {$end}%";
        $start = $end;
    }
    $ring = $riskTotal ? 'conic-gradient('.implode(', ', $stops).')' : 'conic-gradient(#E2E8F0 0 100%)';
@endphp

{{-- Ring chart of Mild / Moderate / Severe results, with user totals. --}}
<div {{ $attributes }}>
        <div class="mt-6 flex items-center gap-6">
            <div class="relative h-36 w-36 shrink-0 rounded-full" style="background: {{ $ring }}">
                <div class="absolute inset-4 flex flex-col items-center justify-center rounded-full bg-white shadow-inner">
                    <span class="text-3xl font-bold text-navy">{{ $riskTotal }}</span>
                    <span class="text-xs text-slate-500">{{ $label }}</span>
                </div>
            </div>
            <ul class="flex-1 space-y-4">
                @foreach ($riskLabels as $risk => [$levelName, $color])
                    <li class="flex items-center justify-between text-sm">
                        <span class="flex items-center gap-2 text-slate-700"><span class="h-3 w-3 rounded-full" style="background: {{ $color }}"></span>{{ $levelName }}</span>
                        <span class="font-semibold text-navy">{{ $riskCounts[$risk] }} <span class="font-normal text-slate-400">· {{ $riskTotal ? round($riskCounts[$risk] / $riskTotal * 100) : 0 }}%</span></span>
                    </li>
                @endforeach
            </ul>
        </div>
        <div class="mt-6 grid grid-cols-2 gap-3">
            @foreach ([[$students, 'Students', 'bg-brand-100 text-brand', 'M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342'],
                       [$professionals, 'Professionals', 'bg-navy/10 text-navy', 'M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 0 0 .75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 0 0-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0 1 12 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 0 1-.673-.38m0 0A2.18 2.18 0 0 1 3 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 0 1 3.413-.387m7.5 0V5.25A2.25 2.25 0 0 0 13.5 3h-3a2.25 2.25 0 0 0-2.25 2.25v.894m7.5 0a48.667 48.667 0 0 0-7.5 0']] as [$count, $label, $classes, $path])
                <div class="flex items-center gap-3 rounded-xl bg-slate-50 p-3">
                    <span class="flex h-9 w-9 items-center justify-center rounded-full {{ $classes }}">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true"><path d="{{ $path }}"/></svg>
                    </span>
                    <div><p class="text-base font-bold leading-tight text-navy">{{ $count }}</p><p class="text-xs text-slate-500">{{ $label }}</p></div>
                </div>
            @endforeach
        </div>
</div>
