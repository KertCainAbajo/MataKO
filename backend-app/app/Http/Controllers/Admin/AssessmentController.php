<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminActivity;
use App\Models\Assessment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AssessmentController extends Controller
{
    public function index(Request $request): View
    {
        return view('admin.assessments.index', [
            'assessments' => $this->filtered($request)->with('user:id,name,email,role')->paginate(25)->withQueryString(),
            'filters' => $request->only(['risk', 'role', 'search']),
        ]);
    }

    public function show(Assessment $assessment): View
    {
        $assessment->load(['user', 'symptoms' => fn ($query) => $query->orderBy('id')]);

        return view('admin.assessments.show', compact('assessment'));
    }

    public function destroy(Assessment $assessment): RedirectResponse
    {
        $assessment->delete();
        AdminActivity::record('assessment.deleted', "Deleted assessment #{$assessment->id}");

        return redirect()->route('admin.assessments.index')->with('status', 'Assessment deleted.');
    }

    /**
     * Download the filtered results as a spreadsheet-friendly CSV file.
     */
    public function export(Request $request): StreamedResponse
    {
        // chunkById pages through ids in its own order, so drop the newest-first ordering.
        $query = $this->filtered($request)->reorder()->with(['user:id,name,email,role', 'symptoms']);
        AdminActivity::record('assessment.exported', 'Exported assessment results to CSV');

        return response()->streamDownload(function () use ($query): void {
            $output = fopen('php://output', 'w');
            fputcsv($output, ['ID', 'Date', 'User', 'Email', 'User type', 'Score', 'Max score', 'Result', 'Answers']);
            $query->chunkById(200, function ($assessments) use ($output): void {
                foreach ($assessments as $assessment) {
                    fputcsv($output, [
                        $assessment->id,
                        $assessment->created_at?->toDateTimeString(),
                        $assessment->user?->name,
                        $assessment->user?->email,
                        $assessment->user?->role,
                        $assessment->total_score,
                        $assessment->max_score,
                        $assessment->risk_level,
                        $assessment->symptoms->map(fn ($symptom): string => "{$symptom->symptom_name}: {$symptom->value}")->implode('; '),
                    ]);
                }
            });
            fclose($output);
        }, 'matako-assessments-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv']);
    }

    /**
     * @return Builder<Assessment>
     */
    private function filtered(Request $request): Builder
    {
        $search = trim((string) $request->query('search', ''));

        return Assessment::query()
            ->when(in_array($request->query('risk'), ['LOW', 'MEDIUM', 'HIGH'], true), fn ($query) => $query->where('risk_level', $request->query('risk')))
            ->when(in_array($request->query('role'), ['student', 'professional'], true), fn ($query) => $query->whereHas('user', fn ($user) => $user->where('role', $request->query('role'))))
            ->when($search !== '', fn ($query) => $query->whereHas('user', fn ($user) => $user->where(fn ($match) => $match->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"))))
            ->latest('created_at')
            ->latest('id');
    }
}
