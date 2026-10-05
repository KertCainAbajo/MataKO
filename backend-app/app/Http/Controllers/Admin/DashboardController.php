<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminActivity;
use App\Models\Assessment;
use App\Models\Question;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $weekStart = now()->startOfWeek();
        $usersByRole = User::where('is_admin', false)->select('role', DB::raw('count(*) as total'))->groupBy('role')->pluck('total', 'role');
        $riskCounts = Assessment::select('risk_level', DB::raw('count(*) as total'))->groupBy('risk_level')->pluck('total', 'risk_level');

        // Users active in the app today: signed in, or used their app token, since midnight.
        $activeToday = User::where('is_admin', false)
            ->where(fn ($query) => $query
                ->where('last_login_at', '>=', today())
                ->orWhereHas('tokens', fn ($tokens) => $tokens->where('last_used_at', '>=', today())))
            ->count();

        // Assessments per day for the last 14 days, including days with none.
        $since = today()->subDays(13);
        $perDay = Assessment::where('created_at', '>=', $since)
            ->select(DB::raw('date(created_at) as day'), DB::raw('count(*) as total'))
            ->groupBy('day')
            ->pluck('total', 'day');
        $daily = collect(range(0, 13))->map(fn (int $offset): array => [
            'date' => $since->copy()->addDays($offset),
            'total' => (int) ($perDay[$since->copy()->addDays($offset)->toDateString()] ?? 0),
        ]);

        $averageScore = Assessment::whereNotNull('max_score')->where('max_score', '>', 0)
            ->avg(DB::raw('total_score * 100.0 / max_score'));

        return view('admin.dashboard', [
            'daily' => $daily,
            'averageScore' => $averageScore === null ? null : (int) round($averageScore),
            'assessedUsers' => Assessment::distinct('user_id')->count('user_id'),
            'stats' => [
                'users' => $usersByRole->sum(),
                'students' => $usersByRole['student'] ?? 0,
                'professionals' => $usersByRole['professional'] ?? 0,
                'newThisWeek' => User::where('is_admin', false)->where('created_at', '>=', $weekStart)->count(),
                'activeToday' => $activeToday,
                'disabled' => User::whereNotNull('disabled_at')->count(),
                'assessments' => $riskCounts->sum(),
                'assessmentsThisWeek' => Assessment::where('created_at', '>=', $weekStart)->count(),
                'questions' => Question::where('is_active', true)->count(),
            ],
            'riskCounts' => ['LOW' => $riskCounts['LOW'] ?? 0, 'MEDIUM' => $riskCounts['MEDIUM'] ?? 0, 'HIGH' => $riskCounts['HIGH'] ?? 0],
            'recentAssessments' => Assessment::with('user:id,name,role')->latest('created_at')->latest('id')->limit(6)->get(),
            'recentActivity' => AdminActivity::with('admin:id,name')->latest('created_at')->latest('id')->limit(6)->get(),
        ]);
    }
}
