<?php

namespace App\View\Composers;

use App\Models\Assessment;
use App\Models\Question;
use App\Models\SecurityEvent;
use App\Models\User;
use Illuminate\View\View;

class AdminLayoutComposer
{
    /**
     * Shared data for every admin page: the notification bell and the sidebar's counts and "Today" card.
     */
    /** Failed sign-ins in one hour that raise an alert in the notification bell. */
    public const FAILED_SIGN_IN_ALERT = 10;

    public function compose(View $view): void
    {
        $since = now()->subDay();
        $today = now(config('app.display_timezone'))->startOfDay()->utc();

        $signups = User::where('is_admin', false)->where('created_at', '>=', $since)->latest()->limit(5)->get(['id', 'name', 'role', 'created_at'])
            ->map(fn (User $user): array => ['name' => $user->name, 'text' => 'Signed up as a '.$user->role, 'time' => $user->created_at, 'url' => route('admin.users.show', $user)]);
        $assessments = Assessment::with('user:id,name')->where('created_at', '>=', $since)->latest('created_at')->limit(5)->get()
            ->map(fn (Assessment $assessment): array => ['name' => $assessment->user?->name ?? 'Deleted user', 'text' => 'Completed an assessment', 'time' => $assessment->created_at, 'url' => route('admin.assessments.show', $assessment)]);

        // Many failed sign-ins in the last hour usually means someone is guessing passwords.
        $failedLastHour = SecurityEvent::whereIn('type', ['login.failed', 'admin.login.failed', 'admin.two_factor.failed'])->where('created_at', '>=', now()->subHour())->count();
        $alerts = collect($failedLastHour >= self::FAILED_SIGN_IN_ALERT ? [[
            'name' => 'Security alert',
            'text' => "{$failedLastHour} failed sign-ins in the last hour",
            'time' => now(),
            'url' => route('admin.security'),
        ]] : []);

        $view->with([
            'notifications' => $alerts->concat($signups->concat($assessments)->sortByDesc('time'))->take(6)->values(),
            'sidebar' => [
                'results' => Assessment::count(),
                'users' => User::where('is_admin', false)->count(),
                'questions' => Question::where('is_active', true)->count(),
                'resultsToday' => Assessment::where('created_at', '>=', $today)->count(),
                'usersToday' => User::where('is_admin', false)->where('created_at', '>=', $today)->count(),
                'activeToday' => User::where('is_admin', false)->where('last_login_at', '>=', $today)->count(),
                // Serious security events in the last 24 hours: failed admin sign-ins, lockouts, wrong 2FA codes.
                'security' => SecurityEvent::whereIn('type', SecurityEvent::serious())->where('created_at', '>=', $since)->count(),
            ],
        ]);
    }
}
