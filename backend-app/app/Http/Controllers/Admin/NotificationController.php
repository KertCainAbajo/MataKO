<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * The notification bell's two actions. Each admin keeps their own read and cleared state.
 */
class NotificationController extends Controller
{
    public function read(Request $request): RedirectResponse
    {
        $request->user()->forceFill(['notifications_read_at' => now()])->save();

        return back();
    }

    public function clear(Request $request): RedirectResponse
    {
        $request->user()->forceFill(['notifications_read_at' => now(), 'notifications_cleared_at' => now()])->save();

        return back()->with('status', 'Notifications cleared.');
    }
}
