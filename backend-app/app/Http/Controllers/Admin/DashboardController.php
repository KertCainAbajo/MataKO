<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminActivity;
use App\Models\Assessment;
use App\Models\Question;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $since = today()->subDays(13);
        $weekStart = today()->subDays(6);
        $lastWeekStart = today()->subDays(13);
        $appUsers = fn () => User::where('is_admin', false);

        $usersByRole = $appUsers()->select('role', DB::raw('count(*) as total'))->groupBy('role')->pluck('total', 'role');
        $riskCounts = Assessment::select('risk_level', DB::raw('count(*) as total'))->groupBy('risk_level')->pluck('total', 'risk_level');

        // Daily series for the last 14 days, used by the chart and the sparklines.
        $assessmentsPerDay = $this->perDay(Assessment::query(), $since);
        $studentsPerDay = $this->perDay($appUsers()->where('role', 'student'), $since);
        $professionalsPerDay = $this->perDay($appUsers()->where('role', 'professional'), $since);
        $scorePerDay = Assessment::where('created_at', '>=', $since)->where('max_score', '>', 0)
            ->select(DB::raw('date(created_at) as day'), DB::raw('avg(total_score * 100.0 / max_score) as score'))
            ->groupBy('day')->pluck('score', 'day');
        $days = collect(range(0, 13))->map(fn (int $offset): Carbon => $since->copy()->addDays($offset));
        $series = fn (Collection $values): array => $days->map(fn (Carbon $day): float => (float) ($values[$day->toDateString()] ?? 0))->all();

        $averageBetween = fn (Carbon $from, ?Carbon $to = null) => Assessment::where('max_score', '>', 0)
            ->where('created_at', '>=', $from)->when($to, fn ($query) => $query->where('created_at', '<', $to))
            ->avg(DB::raw('total_score * 100.0 / max_score'));
        $averageScore = Assessment::where('max_score', '>', 0)->avg(DB::raw('total_score * 100.0 / max_score'));
        $averageThisWeek = $averageBetween($weekStart);
        $averageLastWeek = $averageBetween($lastWeekStart, $weekStart);

        // Users active in the app today: signed in, or used their app token, since midnight.
        $activeToday = $appUsers()
            ->where(fn ($query) => $query
                ->where('last_login_at', '>=', today())
                ->orWhereHas('tokens', fn ($tokens) => $tokens->where('last_used_at', '>=', today())))
            ->count();

        return view('admin.dashboard', [
            'stats' => [
                'users' => $usersByRole->sum(),
                'students' => $usersByRole['student'] ?? 0,
                'professionals' => $usersByRole['professional'] ?? 0,
                'newStudents' => $appUsers()->where('role', 'student')->where('created_at', '>=', $weekStart)->count(),
                'newProfessionals' => $appUsers()->where('role', 'professional')->where('created_at', '>=', $weekStart)->count(),
                'activeToday' => $activeToday,
                'disabled' => User::whereNotNull('disabled_at')->count(),
                'assessments' => $riskCounts->sum(),
                'assessmentsThisWeek' => Assessment::where('created_at', '>=', $weekStart)->count(),
                'questions' => Question::where('is_active', true)->count(),
                'averageScore' => $averageScore === null ? null : (int) round($averageScore),
                'averageChange' => $averageThisWeek === null || $averageLastWeek === null ? null : (int) round($averageThisWeek - $averageLastWeek),
            ],
            'riskCounts' => ['LOW' => $riskCounts['LOW'] ?? 0, 'MEDIUM' => $riskCounts['MEDIUM'] ?? 0, 'HIGH' => $riskCounts['HIGH'] ?? 0],
            'days' => $days,
            'daily' => $series($assessmentsPerDay),
            'sparklines' => [
                'students' => $series($studentsPerDay),
                'professionals' => $series($professionalsPerDay),
                'assessments' => $series($assessmentsPerDay),
                'score' => $series($scorePerDay),
            ],
            'recentAssessments' => Assessment::with('user:id,name,role')->latest('created_at')->latest('id')->limit(5)->get(),
            'recentActivity' => AdminActivity::with('admin:id,name')->latest('created_at')->latest('id')->limit(5)->get(),
        ]);
    }

    /**
     * Rows created per day since a date, keyed by "Y-m-d".
     *
     * @param  Builder<Model>  $query
     * @return Collection<string, int>
     */
    private function perDay($query, Carbon $since): Collection
    {
        return $query->where('created_at', '>=', $since)
            ->select(DB::raw('date(created_at) as day'), DB::raw('count(*) as total'))
            ->groupBy('day')
            ->pluck('total', 'day');
    }
}
