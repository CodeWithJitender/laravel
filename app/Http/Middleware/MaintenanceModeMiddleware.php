<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Support\Facades\Settings;
use Symfony\Component\HttpFoundation\Response;

class MaintenanceModeMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $status = Settings::get('system.system_status', 'online');

        if ($status === 'maintenance') {
            $user = $request->user();
            
            // Allow Admins to bypass maintenance mode to turn it back online
            if (!$user || !$user->hasRole('Admin')) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'The system is currently undergoing scheduled maintenance. Please check back later.'
                    ], 503);
                }

                abort(503, 'System is in maintenance mode.');
            }
        }

        return $next($request);
    }
}
