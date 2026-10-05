<x-admin.layout title="Questions" subtitle="The self-assessment questionnaires shown in the app.">
    <x-slot:actions>
        <a href="{{ route('admin.questions.create', ['audience' => $audience]) }}" class="admin-btn-primary">Add question</a>
    </x-slot:actions>

    <div class="mb-5 inline-flex gap-1 rounded-xl bg-slate-200/60 p-1">
        @foreach (['student' => 'Students', 'professional' => 'Professionals'] as $value => $label)
            <a href="{{ route('admin.questions.index', ['audience' => $value]) }}" @class(['admin-tab-active' => $audience === $value, 'admin-tab' => ! ($audience === $value)])>{{ $label }}</a>
        @endforeach
    </div>

    @php($active = $questions->where('is_active', true)->count())
    <p class="mb-4 text-sm text-slate-600">
        {{ $active }} active {{ Str::plural('question', $active) }} · maximum score {{ $active * \App\Models\Question::MAX_ANSWER }}.
        Results are Mild up to a third of the maximum, Moderate up to two thirds, and Severe above that.
        Changes apply the next time someone starts a self-assessment.
    </p>

    <div class="admin-card overflow-x-auto p-0">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr><th class="admin-th w-16">#</th><th class="admin-th w-20">Image</th><th class="admin-th">Question</th><th class="admin-th">Status</th><th class="admin-th text-right">Actions</th></tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($questions as $question)
                    <tr @class(['opacity-60' => ! $question->is_active])>
                        <td class="admin-td font-semibold text-navy">{{ $loop->iteration }}</td>
                        <td class="admin-td">
                            @if ($question->imageUrl())
                                <img src="{{ $question->imageUrl() }}" alt="" class="h-12 w-12 rounded object-contain">
                            @elseif ($question->bundledImage())
                                <img src="{{ asset('images/questions/'.$question->bundledImage().'.png') }}" alt="" class="h-12 w-12 rounded object-contain" title="Built-in mascot">
                            @else
                                <span class="text-xs text-slate-400">None</span>
                            @endif
                        </td>
                        <td class="admin-td">
                            <p class="font-medium text-slate-900">{{ $question->question }}</p>
                            <p class="text-xs text-slate-500">Symptom: {{ $question->symptom }}
                                @if (blank($question->translations['fil']['question'] ?? null) || blank($question->translations['ceb']['question'] ?? null))
                                    <span class="admin-badge ml-1 bg-amber-100 text-amber-800">Needs translation</span>
                                @endif
                            </p>
                        </td>
                        <td class="admin-td">
                            <span @class(['admin-badge', 'bg-green-100 text-green-700' => $question->is_active, 'bg-slate-200 text-slate-600' => ! $question->is_active])>{{ $question->is_active ? 'Shown' : 'Hidden' }}</span>
                        </td>
                        <td class="admin-td">
                            <div class="flex justify-end gap-1">
                                @foreach (['up' => '↑', 'down' => '↓'] as $direction => $arrow)
                                    <form method="POST" action="{{ route('admin.questions.move', $question) }}">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="direction" value="{{ $direction }}">
                                        <button class="admin-btn-secondary px-2.5" aria-label="Move {{ $direction }}" @disabled(($direction === 'up' && $loop->parent->first) || ($direction === 'down' && $loop->parent->last))>{{ $arrow }}</button>
                                    </form>
                                @endforeach
                                <a href="{{ route('admin.questions.edit', $question) }}" class="admin-btn-navy">Edit</a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="admin-td py-8 text-center text-slate-500">No questions yet. The self-assessment is unavailable until you add one.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-admin.layout>
