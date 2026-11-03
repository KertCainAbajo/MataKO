<?php

namespace App\View\Composers;

use App\Models\Assessment;
use App\Models\Question;
use App\Models\SecurityEvent;
use App\Models\User;
use Illuminate\Support\Carbon;
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

        // The bell shows the last 24 hours, minus anything this admin cleared; "unread" is newer than their last "mark all as read".
        $admin = auth()->user();
        $from = $admin?->notifications_cleared_at?->greaterThan($since) ? $admin->notifications_cleared_at : $since;

        $signups = User::where('is_admin', false)->where('created_at', '>', $from)->latest()->limit(5)->get(['id', 'name', 'role', 'created_at'])
            ->map(fn (User $user): array => ['type' => 'signup', 'name' => $user->name, 'text' => 'Signed up as a '.$user->role, 'time' => $user->created_at, 'url' => route('admin.users.show', $user)]);
        $assessments = Assessment::with('user:id,name')->where('created_at', '>', $from)->latest('created_at')->limit(5)->get()
            ->map(fn (Assessment $assessment): array => ['type' => 'assessment', 'name' => $assessment->user?->name ?? 'Deleted user', 'text' => 'Completed an assessment', 'time' => $assessment->created_at, 'url' => route('admin.assessments.show', $assessment)]);

        // Many failed sign-ins in the last hour usually means someone is guessing passwords.
        $failed = SecurityEvent::whereIn('type', ['login.failed', 'admin.login.failed', 'admin.two_factor.failed'])->where('created_at', '>=', now()->subHour());
        $failedLastHour = (clone $failed)->count();
        $latestFailure = $failedLastHour ? Carbon::parse((clone $failed)->max('created_at')) : null;
        $alerts = collect($failedLastHour >= self::FAILED_SIGN_IN_ALERT && $latestFailure->greaterThan($from) ? [[
            'type' => 'security',
            'name' => 'Security alert',
            'text' => "{$failedLastHour} failed sign-ins in the last hour",
            'time' => $latestFailure,
            'url' => route('admin.security'),
        ]] : []);

        $readAt = $admin?->notifications_read_at;
        $notifications = $alerts->concat($signups->concat($assessments)->sortByDesc('time'))->take(8)
            ->map(fn (array $item): array => $item + ['unread' => $readAt === null || $item['time']->greaterThan($readAt)])
            ->values();

        $view->with([
            'notifications' => $notifications,
            'unreadNotifications' => $notifications->where('unread', true)->count(),
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
