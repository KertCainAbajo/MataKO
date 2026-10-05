<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminActivity;
use App\Models\Question;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class QuestionController extends Controller
{
    public function index(Request $request): View
    {
        $audience = in_array($request->query('audience'), Question::AUDIENCES, true) ? $request->query('audience') : 'student';

        return view('admin.questions.index', [
            'audience' => $audience,
            'questions' => Question::where('audience', $audience)->orderBy('position')->orderBy('id')->get(),
        ]);
    }

    public function create(Request $request): View
    {
        $audience = in_array($request->query('audience'), Question::AUDIENCES, true) ? $request->query('audience') : 'student';

        return view('admin.questions.form', ['question' => new Question(['audience' => $audience, 'is_active' => true])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['position'] = (int) Question::where('audience', $data['audience'])->max('position') + 1;
        $data['image'] = $request->file('image')?->store('questions', 'public');

        $question = Question::create($data);
        AdminActivity::record('question.created', "Added {$question->audience} question \"{$question->symptom}\"");

        return redirect()->route('admin.questions.index', ['audience' => $question->audience])->with('status', 'Question added. The app will use it for the next self-assessment.');
    }

    public function edit(Question $question): View
    {
        return view('admin.questions.form', compact('question'));
    }

    public function update(Request $request, Question $question): RedirectResponse
    {
        $data = $this->validated($request, $question);

        if ($request->hasFile('image') || $request->boolean('remove_image')) {
            $this->deleteUploadedImage($question);
            $data['image'] = $request->file('image')?->store('questions', 'public');
        }

        $question->update($data);
        AdminActivity::record('question.updated', "Updated {$question->audience} question \"{$question->symptom}\"");

        return redirect()->route('admin.questions.index', ['audience' => $question->audience])->with('status', 'Question saved.');
    }

    public function destroy(Question $question): RedirectResponse
    {
        $this->deleteUploadedImage($question);
        $question->delete();
        AdminActivity::record('question.deleted', "Deleted {$question->audience} question \"{$question->symptom}\"");

        return redirect()->route('admin.questions.index', ['audience' => $question->audience])->with('status', 'Question deleted. Past results keep their answers.');
    }

    /**
     * Swap a question with its neighbour above or below.
     */
    public function move(Request $request, Question $question): RedirectResponse
    {
        $direction = $request->validate(['direction' => ['required', 'in:up,down']])['direction'];
        $siblings = Question::where('audience', $question->audience)->orderBy('position')->orderBy('id')->get()->values();
        $index = $siblings->search(fn (Question $item): bool => $item->is($question));
        $swapWith = $siblings->get($direction === 'up' ? $index - 1 : $index + 1);

        if ($swapWith) {
            // Renumber the whole list so duplicate or missing positions are corrected as a side effect.
            $ordered = $siblings->all();
            [$ordered[$index], $ordered[$siblings->search($swapWith)]] = [$swapWith, $question];
            foreach ($ordered as $position => $item) {
                $item->update(['position' => $position + 1]);
            }
            AdminActivity::record('question.moved', "Moved {$question->audience} question \"{$question->symptom}\" {$direction}");
        }

        return back();
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?Question $question = null): array
    {
        $audience = $question?->audience ?? $request->input('audience');

        $data = $request->validate([
            'audience' => [$question ? 'prohibited' : 'required', Rule::in(Question::AUDIENCES)],
            'symptom' => ['required', 'string', 'max:60', Rule::unique('questions')->where('audience', $audience)->ignore($question?->id)],
            'question' => ['required', 'string', 'max:500'],
            'image' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'is_active' => ['boolean'],
            'translations.fil.question' => ['nullable', 'string', 'max:500'],
            'translations.ceb.question' => ['nullable', 'string', 'max:500'],
        ], [], ['translations.fil.question' => 'Filipino question', 'translations.ceb.question' => 'Cebuano question']);
        $data['is_active'] = $request->boolean('is_active');
        $data['translations'] = array_filter([
            'fil' => array_filter(['question' => trim((string) ($data['translations']['fil']['question'] ?? ''))]),
            'ceb' => array_filter(['question' => trim((string) ($data['translations']['ceb']['question'] ?? ''))]),
        ]);
        unset($data['image']);

        return $data;
    }

    private function deleteUploadedImage(Question $question): void
    {
        if ($question->image && ! $question->bundledImage()) {
            Storage::disk('public')->delete($question->image);
        }
    }
}
