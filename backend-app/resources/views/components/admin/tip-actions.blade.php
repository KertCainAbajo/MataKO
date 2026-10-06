@props(['tip', 'first' => false, 'last' => false, 'compact' => false])

{{-- Move up / move down / edit buttons for a piece of app content. --}}
<div class="flex shrink-0 items-center gap-1">
    @foreach (['up' => 'm4.5 15.75 7.5-7.5 7.5 7.5', 'down' => 'm19.5 8.25-7.5 7.5-7.5-7.5'] as $direction => $path)
        <form method="POST" action="{{ route('admin.tips.move', $tip) }}">
            @csrf @method('PATCH')
            <input type="hidden" name="direction" value="{{ $direction }}">
            <button @class(['flex items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-500 transition hover:border-brand/40 hover:text-brand disabled:pointer-events-none disabled:opacity-30', 'h-7 w-7' => $compact, 'h-9 w-9' => ! $compact])
                    aria-label="Move {{ $direction }}" title="Move {{ $direction }}" @disabled(($direction === 'up' && $first) || ($direction === 'down' && $last))>
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $path }}"/></svg>
            </button>
        </form>
    @endforeach
    <a href="{{ route('admin.tips.edit', $tip) }}" @class(['flex items-center justify-center gap-1.5 rounded-lg bg-navy font-semibold text-white transition hover:bg-navy-700', 'h-7 w-7' => $compact, 'h-9 px-3 text-sm' => ! $compact]) title="Edit" aria-label="Edit">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125"/></svg>
        @unless ($compact)<span>Edit</span>@endunless
    </a>
</div>
