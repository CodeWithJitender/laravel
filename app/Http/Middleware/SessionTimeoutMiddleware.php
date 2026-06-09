<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Support\Facades\Settings;
use App\Models\ActivityLog;
use Symfony\Component\HttpFoundation\Response;

class SessionTimeoutMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            $timeoutMinutes = (int) Settings::get('security.session_timeout_minutes', 120);
            
            $lastActivity = session('last_activity_timestamp');
            $currentTime = time();

            if ($lastActivity && ($currentTime - $lastActivity) > ($timeoutMinutes * 60)) {
                
                ActivityLog::create([
                    'user_id' => $user->id,
                    'activity' => 'Session Timeout',
                    'module' => 'Authentication',
                    'description' => "User session timed out after {$timeoutMinutes} minutes of inactivity.",
                    'ip_address' => $request->ip(),
                ]);

                Auth::logout();
                session()->invalidate();
                session()->regenerateToken();

                if ($request->expectsJson()) {
                    return response()->json(['message' => 'Your session has timed out due to inactivity.'], 401);
                }

                return redirect()->route('login')->with('message', 'Your session has timed out.');
            }

            session(['last_activity_timestamp' => $currentTime]);
        }

        return $next($request);
    }
}
