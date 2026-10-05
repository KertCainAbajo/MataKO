@props(['tip', 'first' => false, 'last' => false])

<div class="flex shrink-0 gap-1">
    @foreach (['up' => '↑', 'down' => '↓'] as $direction => $arrow)
        <form method="POST" action="{{ route('admin.tips.move', $tip) }}">
            @csrf @method('PATCH')
            <input type="hidden" name="direction" value="{{ $direction }}">
            <button class="admin-btn-secondary px-2.5 py-1" aria-label="Move {{ $direction }}" @disabled(($direction === 'up' && $first) || ($direction === 'down' && $last))>{{ $arrow }}</button>
        </form>
    @endforeach
    <a href="{{ route('admin.tips.edit', $tip) }}" class="admin-btn-navy py-1">Edit</a>
</div>
