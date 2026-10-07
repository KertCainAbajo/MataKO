<?php

namespace App\Http\Middleware;

use App\Models\SecurityEvent;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Signs an admin out after 30 minutes without activity, so an unattended computer does not stay
 * open on the admin dashboard.
 */
class AdminIdleTimeout
{
    public const IDLE_MINUTES = 30;

    public function handle(Request $request, Closure $next): Response
    {
        $session = $request->session();
        $lastActivity = $session->get('admin_last_activity');

        if ($lastActivity && now()->timestamp - $lastActivity > self::IDLE_MINUTES * 60) {
            $admin = $request->user();
            // Also clears the "Keep me signed in" cookie, so the next visit asks for the password again.
            Auth::guard('web')->logout();
            $session->invalidate();
            $session->regenerateToken();
            SecurityEvent::record('admin.session.expired', user: $admin);

            return redirect()->route('admin.login')->with('status', 'You were signed out after '.self::IDLE_MINUTES.' minutes of inactivity. Please sign in again.');
        }

        $session->put('admin_last_activity', now()->timestamp);

        return $next($request);
    }
}
