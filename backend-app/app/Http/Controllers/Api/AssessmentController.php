<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Question;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssessmentController extends Controller
{
    /**
     * Active questions for the signed-in user's role, in the order the app shows them.
     */
    public function questions(Request $request): JsonResponse
    {
        $questions = Question::forAudience($this->audience($request))->get();
        $translations = $questions->reduce(fn (array $carry, Question $question): array => array_replace_recursive($carry, $question->dictionary()), ['Filipino' => [], 'Cebuano' => []]);

        $questions = $questions->map(fn (Question $question): array => [
            'id' => $question->id,
            'symptom' => $question->symptom,
            'question' => $question->question,
            'image_key' => $question->bundledImage(),
            // Relative, so the app can join it to whichever host it uses to reach the API.
            'image_path' => $question->imageUrl() ? '/storage/'.$question->image : null,
        ]);

        return response()->json(['questions' => $questions, 'max_answer' => Question::MAX_ANSWER, 'translations' => $translations]);
    }

    public function store(Request $request): JsonResponse
    {
        $symptoms = Question::forAudience($this->audience($request))->pluck('symptom')->all();
        if ($symptoms === []) {
            return response()->json(['message' => __('The self-assessment is not available right now.')], 422);
        }

        $data = $request->validate([
            'answers' => ['required', 'array', 'size:'.count($symptoms)],
            'answers.*' => ['required', 'integer', 'between:0,'.Question::MAX_ANSWER],
        ]);

        // Require one answer for each active question, so incomplete or outdated forms cannot be scored.
        $answers = $data['answers'];
        $missing = array_diff($symptoms, array_keys($answers));
        $unknown = array_diff(array_keys($answers), $symptoms);
        if ($missing || $unknown) {
            return response()->json([
                'message' => __('The questions have changed. Please start the self-assessment again.'),
                'errors' => ['answers' => [__('Answer each of the listed questions.')]],
            ], 422);
        }

        // Low up to a third of the maximum score, medium up to two thirds, high above that.
        $score = array_sum($answers);
        $maxScore = count($symptoms) * Question::MAX_ANSWER;
        $risk = $score * 3 <= $maxScore ? 'LOW' : ($score * 3 <= $maxScore * 2 ? 'MEDIUM' : 'HIGH');

        $assessment = DB::transaction(function () use ($request, $answers, $symptoms, $score, $maxScore, $risk) {
            $assessment = $request->user()->assessments()->create([
                'total_score' => $score,
                'max_score' => $maxScore,
                'risk_level' => $risk,
            ]);

            foreach ($symptoms as $name) {
                $assessment->symptoms()->create(['symptom_name' => $name, 'value' => $answers[$name]]);
            }

            return $assessment->load('symptoms');
        });

        return response()->json([
            'assessment' => $assessment,
            'recommendations' => $this->recommendations($risk),
        ], 201);
    }

    public function index(Request $request): JsonResponse
    {
        $assessments = $request->user()->assessments()
            ->with('symptoms')
            ->latest('created_at')
            ->latest('id')
            ->limit(50)
            ->get();

        return response()->json(['assessments' => $assessments]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        // Query through the signed-in user to prevent access to another user's records.
        $assessment = $request->user()->assessments()->with('symptoms')->findOrFail($id);

        return response()->json([
            'assessment' => $assessment,
            'recommendations' => $this->recommendations($assessment->risk_level),
        ]);
    }

    private function audience(Request $request): string
    {
        return $request->user()->role === 'professional' ? 'professional' : 'student';
    }

    private function recommendations(string $risk): array
    {
        return match ($risk) {
            'LOW' => ['Maintain good screen habits', 'Take occasional breaks'],
            'MEDIUM' => ['Follow the 20-20-20 rule', 'Adjust screen brightness', 'Blink more often'],
            default => ['Keep a strict break schedule', 'Reduce screen time', 'Improve your ergonomics', 'Consider consulting an eye specialist'],
        };
    }
}
