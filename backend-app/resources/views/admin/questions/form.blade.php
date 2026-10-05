<x-admin.layout :title="$question->exists ? 'Edit question' : 'Add '.$question->audience.' question'">
    <form method="POST" action="{{ $question->exists ? route('admin.questions.update', $question) : route('admin.questions.store') }}" enctype="multipart/form-data" class="admin-card max-w-2xl space-y-4">
        @csrf
        @if ($question->exists)
            @method('PUT')
            <p class="text-sm text-slate-500">{{ ucfirst($question->audience) }} questionnaire</p>
        @else
            <input type="hidden" name="audience" value="{{ $question->audience }}">
        @endif

        <fieldset class="rounded-lg border border-slate-200 p-4">
            <legend class="px-1 text-sm font-semibold text-slate-700">Question</legend>
            <label for="question" class="admin-label text-xs text-slate-500">English</label>
            <textarea id="question" name="question" rows="2" required maxlength="500" class="admin-input">{{ old('question', $question->question) }}</textarea>
            <div class="mt-3 grid gap-3 sm:grid-cols-2">
                @foreach (\App\Models\Tip::LANGUAGES as $code => $language)
                    <div>
                        <label for="question_{{ $code }}" class="admin-label text-xs text-slate-500">{{ $language }}</label>
                        <textarea id="question_{{ $code }}" name="translations[{{ $code }}][question]" rows="2" maxlength="500" class="admin-input">{{ old("translations.{$code}.question", $question->translations[$code]['question'] ?? '') }}</textarea>
                    </div>
                @endforeach
            </div>
            <p class="mt-2 text-xs text-slate-500">If a translation is left empty, users of that language see the English question.</p>
        </fieldset>
        <div>
            <label for="symptom" class="admin-label">Symptom name</label>
            <input id="symptom" name="symptom" value="{{ old('symptom', $question->symptom) }}" required maxlength="60" class="admin-input">
            <p class="mt-1 text-xs text-slate-500">A short label such as "Dry eyes". Saved with each answer and shown in results, so each question in a questionnaire needs its own.</p>
        </div>
        <div>
            <label for="image" class="admin-label">Illustration</label>
            @if ($question->imageUrl())
                <img src="{{ $question->imageUrl() }}" alt="" class="mb-2 h-24 rounded object-contain">
            @elseif ($question->bundledImage())
                <img src="{{ asset('images/questions/'.$question->bundledImage().'.png') }}" alt="" class="mb-1 h-24 rounded object-contain">
                <p class="mb-2 text-xs text-slate-500">Built-in mascot. Upload a file to replace it.</p>
            @endif
            <input id="image" name="image" type="file" accept="image/png,image/jpeg,image/webp" class="block text-sm">
            <p class="mt-1 text-xs text-slate-500">PNG, JPG or WebP up to 2 MB. Square images work best.</p>
            @if ($question->exists && $question->image)
                <label class="mt-2 flex items-center gap-2 text-sm text-slate-600"><input type="checkbox" name="remove_image" value="1" class="rounded border-slate-300"> Remove the illustration</label>
            @endif
        </div>
        <label class="flex items-center gap-2 text-sm">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $question->is_active)) class="rounded border-slate-300 text-brand">
            Show this question in the app
        </label>
        <div class="flex gap-2">
            <button class="admin-btn-primary">{{ $question->exists ? 'Save question' : 'Add question' }}</button>
            <a href="{{ route('admin.questions.index', ['audience' => $question->audience]) }}" class="admin-btn-secondary">Cancel</a>
        </div>
    </form>

    @if ($question->exists)
        <details class="admin-card mt-6 max-w-2xl border-red-200">
            <summary class="cursor-pointer text-sm font-semibold text-red-700">Delete this question</summary>
            <p class="mt-2 text-sm text-slate-600">Past results keep their answers. To take a question out only for now, untick "Show this question in the app" instead.</p>
            <form method="POST" action="{{ route('admin.questions.destroy', $question) }}" class="mt-3">
                @csrf @method('DELETE')
                <button class="admin-btn-danger">Delete question</button>
            </form>
        </details>
    @endif
</x-admin.layout>
