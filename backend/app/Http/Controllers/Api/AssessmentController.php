<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssessmentController extends Controller
{
    private const SYMPTOMS = ['Eye pain', 'Dry eyes', 'Blurred vision', 'Headache', 'Eye fatigue'];

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'answers' => ['required', 'array', 'size:5'],
            'answers.*' => ['required', 'integer', 'between:0,3'],
        ]);

        // Require one answer for each known symptom, so incomplete forms cannot be scored.
        $answers = $data['answers'];
        $missing = array_diff(self::SYMPTOMS, array_keys($answers));
        $unknown = array_diff(array_keys($answers), self::SYMPTOMS);
        if ($missing || $unknown) {
            return response()->json([
                'message' => 'Answer each of the five listed symptoms.',
                'errors' => ['answers' => ['Answer each of the five listed symptoms.']],
            ], 422);
        }

        $score = array_sum($answers);
        $risk = $score <= 5 ? 'LOW' : ($score <= 10 ? 'MEDIUM' : 'HIGH');

        $assessment = DB::transaction(function () use ($request, $answers, $score, $risk) {
            $assessment = $request->user()->assessments()->create([
                'total_score' => $score,
                'risk_level' => $risk,
            ]);

            foreach (self::SYMPTOMS as $name) {
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
