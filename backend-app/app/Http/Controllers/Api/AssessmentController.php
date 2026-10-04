<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssessmentController extends Controller
{
    // Questionnaires in the order shown in the app; they differ only in the last question.
    // Answers are 0 (Never), 1 (Occasionally), 2 (Often or Always).
    private const STUDENT_SYMPTOMS = [
        'Burning sensation', 'Itchy eyes', 'Foreign body sensation', 'Watery eyes',
        'Excessive blinking', 'Eye redness', 'Eye pain', 'Heavy eyelids',
        'Dry eyes', 'Blurred vision', 'Double vision', 'Difficulty focusing',
        'Light sensitivity', 'Colored halos', 'Worsening vision', 'Worsening vision (Q16)',
    ];

    private const PROFESSIONAL_SYMPTOMS = [
        'Burning sensation', 'Itchy eyes', 'Foreign body sensation', 'Watery eyes',
        'Excessive blinking', 'Eye redness', 'Eye pain', 'Heavy eyelids',
        'Dry eyes', 'Blurred vision', 'Double vision', 'Difficulty focusing',
        'Light sensitivity', 'Colored halos', 'Worsening vision', 'Headache',
    ];

    public function store(Request $request): JsonResponse
    {
        $symptoms = $request->user()->role === 'professional' ? self::PROFESSIONAL_SYMPTOMS : self::STUDENT_SYMPTOMS;
        $maxAnswer = 2;

        $data = $request->validate([
            'answers' => ['required', 'array', 'size:'.count($symptoms)],
            'answers.*' => ['required', 'integer', 'between:0,'.$maxAnswer],
        ]);

        // Require one answer for each known symptom, so incomplete forms cannot be scored.
        $answers = $data['answers'];
        $missing = array_diff($symptoms, array_keys($answers));
        $unknown = array_diff(array_keys($answers), $symptoms);
        if ($missing || $unknown) {
            return response()->json([
                'message' => 'Answer each of the listed questions.',
                'errors' => ['answers' => ['Answer each of the listed questions.']],
            ], 422);
        }

        // Low up to a third of the maximum score, medium up to two thirds, high above that.
        $score = array_sum($answers);
        $maxScore = count($symptoms) * $maxAnswer;
        $risk = $score * 3 <= $maxScore ? 'LOW' : ($score * 3 <= $maxScore * 2 ? 'MEDIUM' : 'HIGH');

        $assessment = DB::transaction(function () use ($request, $answers, $symptoms, $score, $risk) {
            $assessment = $request->user()->assessments()->create([
                'total_score' => $score,
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

    private function recommendations(string $risk): array
    {
        return match ($risk) {
            'LOW' => ['Maintain good screen habits', 'Take occasional breaks'],
            'MEDIUM' => ['Follow the 20-20-20 rule', 'Adjust screen brightness', 'Blink more often'],
            default => ['Keep a strict break schedule', 'Reduce screen time', 'Improve your ergonomics', 'Consider consulting an eye specialist'],
        };
    }
}
