<x-admin.layout :title="$question->exists ? 'Edit question' : 'Add '.$question->audience.' question'">
    <form method="POST" action="{{ $question->exists ? route('admin.questions.update', $question) : route('admin.questions.store') }}" enctype="multipart/form-data" class="admin-card max-w-2xl space-y-4">
        @csrf
        <x-admin.translate-script />
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
                        <div class="mb-1.5 flex items-center justify-between gap-2">
                            <label for="question_{{ $code }}" class="text-xs font-medium text-slate-500">{{ $language }}</label>
                            <x-admin.translate-button source="question" :target="'question_'.$code" :lang="$code" />
                        </div>
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
        @php
            $currentPicture = old('illustration', $question->exists ? ($question->bundledImage() ?? ($question->image ? 'upload' : 'none')) : null);
        @endphp
        <fieldset class="rounded-lg border border-slate-200 p-4">
            <legend class="px-1 text-sm font-semibold text-slate-700">Picture</legend>
            <p class="text-xs text-slate-500">Shown above the question in the app. As you type the question, the matching mascot is picked for you. Choose another, upload your own, or show no picture.</p>
            <p id="picture-suggestion" class="mt-2 hidden items-center gap-1.5 rounded-lg bg-brand-50 px-3 py-2 text-xs text-navy ring-1 ring-brand-100" role="status" aria-live="polite">
                <ion-icon name="sparkles" class="text-brand"></ion-icon>
                <span>Suggested for this question: <strong data-suggestion-label></strong></span>
            </p>
            @error('illustration')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror

            <div class="mt-3 grid grid-cols-3 gap-2 sm:grid-cols-6" role="radiogroup" aria-label="Picture">
                @foreach (\App\Models\Question::ILLUSTRATIONS as $key => $illustration)
                    <label class="cursor-pointer">
                        <input type="radio" name="illustration" value="{{ $key }}" data-keywords="{{ implode('|', $illustration['keywords']) }}" data-label="{{ $illustration['label'] }}" @checked($currentPicture === $key) class="peer sr-only">
                        <span class="flex h-full flex-col items-center gap-1 rounded-xl border-2 border-slate-100 bg-slate-50 p-1.5 transition hover:border-brand-100 peer-checked:border-brand peer-checked:bg-brand-50 peer-focus-visible:ring-2 peer-focus-visible:ring-brand">
                            <img src="{{ asset('images/questions/'.$key.'.png') }}" alt="" class="h-14 w-14 object-contain" loading="lazy">
                            <span class="text-center text-[11px] leading-tight text-slate-600">{{ $illustration['label'] }}</span>
                        </span>
                    </label>
                @endforeach
                @if ($question->imageUrl())
                    <label class="cursor-pointer">
                        <input type="radio" name="illustration" value="upload" data-label="Your upload" @checked($currentPicture === 'upload') class="peer sr-only">
                        <span class="flex h-full flex-col items-center gap-1 rounded-xl border-2 border-slate-100 bg-slate-50 p-1.5 transition hover:border-brand-100 peer-checked:border-brand peer-checked:bg-brand-50 peer-focus-visible:ring-2 peer-focus-visible:ring-brand">
                            <img src="{{ $question->imageUrl() }}" alt="" class="h-14 w-14 object-contain">
                            <span class="text-center text-[11px] leading-tight text-slate-600">Your upload</span>
                        </span>
                    </label>
                @endif
                <label class="cursor-pointer">
                    <input type="radio" name="illustration" value="none" data-label="No picture" @checked($currentPicture === 'none') class="peer sr-only">
                    <span class="flex h-full flex-col items-center justify-center gap-1 rounded-xl border-2 border-dashed border-slate-200 bg-white p-1.5 transition hover:border-brand-100 peer-checked:border-brand peer-checked:bg-brand-50 peer-focus-visible:ring-2 peer-focus-visible:ring-brand">
                        <ion-icon name="image-outline" class="h-14 text-2xl text-slate-300"></ion-icon>
                        <span class="text-center text-[11px] leading-tight text-slate-600">No picture</span>
                    </span>
                </label>
            </div>

            <div class="mt-4 flex flex-wrap items-center gap-3 rounded-lg bg-slate-50 p-3">
                <img id="upload-preview" src="" alt="" class="hidden h-16 w-16 rounded-lg object-contain ring-1 ring-slate-200">
                <div>
                    <label for="image" class="text-sm font-medium text-slate-700">Or upload your own picture</label>
                    <input id="image" name="image" type="file" accept="image/png,image/jpeg,image/webp" class="mt-1.5 block text-sm text-slate-500 file:mr-3 file:cursor-pointer file:rounded-lg file:border-0 file:bg-navy file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-white hover:file:bg-navy-700">
                    <p class="mt-1 text-xs text-slate-500">PNG, JPG or WebP up to 2 MB. Square images work best. An upload replaces the mascot.</p>
                    @error('image')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>
        </fieldset>

        <script @nonce>
            (() => {
                const choices = [...document.querySelectorAll('input[name="illustration"]')];
                const mascots = choices.filter((choice) => choice.dataset.keywords);
                const fields = ['symptom', 'question'].map((id) => document.getElementById(id));
                const hint = document.getElementById('picture-suggestion');
                const upload = document.getElementById('image');
                const preview = document.getElementById('upload-preview');
                // Once the admin picks a picture themselves, stop changing it for them.
                let pickedByHand = choices.some((choice) => choice.checked);
                choices.forEach((choice) => choice.addEventListener('change', () => {
                    pickedByHand = true;
                    hint.classList.add('hidden');
                    // A chosen mascot replaces a file picked earlier, which would otherwise win on save.
                    upload.value = '';
                    preview.classList.add('hidden');
                }));

                // Same scoring as Question::suggestIllustration(): words in the symptom name count three times.
                const suggest = () => {
                    const [symptom, question] = fields.map((field) => field.value.toLowerCase());
                    let best = null;
                    let bestScore = 0;
                    mascots.forEach((mascot) => {
                        const score = mascot.dataset.keywords.split('|').reduce((total, keyword) => {
                            const pattern = new RegExp('\\b' + keyword.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'));
                            return total + (pattern.test(symptom) ? 3 : 0) + (pattern.test(question) ? 1 : 0);
                        }, 0);
                        if (score > bestScore) [best, bestScore] = [mascot, score];
                    });
                    return best;
                };

                fields.forEach((field) => field.addEventListener('input', () => {
                    if (pickedByHand || upload.files.length) return;
                    const best = suggest();
                    choices.forEach((choice) => { choice.checked = choice === best; });
                    hint.classList.toggle('hidden', !best);
                    hint.classList.toggle('flex', !!best);
                    if (best) hint.querySelector('[data-suggestion-label]').textContent = best.dataset.label;
                }));

                upload.addEventListener('change', () => {
                    const file = upload.files[0];
                    preview.classList.toggle('hidden', !file);
                    if (!file) return;
                    preview.src = URL.createObjectURL(file);
                    choices.forEach((choice) => { choice.checked = false; });
                    hint.classList.add('hidden');
                });
            })();
        </script>
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
