<?php

namespace App\Listeners;

use App\Models\UserLoginHistory;
use App\Models\ActivityLog;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Request;

class AuthEventsListener
{
    /**
     * Handle user login events.
     */
    public function onUserLogin(Login $event)
    {
        $user = $event->user;
        $ip = Request::ip();
        $userAgent = Request::header('User-Agent', '');

        UserLoginHistory::create([
            'user_id' => $user->id,
            'status' => 'success',
            'ip_address' => $ip,
            'user_agent' => substr($userAgent, 0, 255),
        ]);

        ActivityLog::create([
            'user_id' => $user->id,
            'activity' => 'Login',
            'module' => 'Authentication',
            'description' => "User logged in successfully from IP: {$ip}",
            'ip_address' => $ip,
        ]);
    }

    /**
     * Handle user logout events.
     */
    public function onUserLogout(Logout $event)
    {
        $user = $event->user;
        if (!$user) {
            return;
        }

        $ip = Request::ip();

        ActivityLog::create([
            'user_id' => $user->id,
            'activity' => 'Logout',
            'module' => 'Authentication',
            'description' => "User logged out.",
            'ip_address' => $ip,
        ]);
    }

    /**
     * Handle failed login events.
     */
    public function onUserLoginFailed(Failed $event)
    {
        $ip = Request::ip();
        $userAgent = Request::header('User-Agent', '');
        $credentials = $event->credentials;
        $email = $credentials['email'] ?? 'unknown';

        $user = $event->user; // Might be null if user doesn't exist

        UserLoginHistory::create([
            'user_id' => $user ? $user->id : null,
            'status' => 'failed',
            'ip_address' => $ip,
            'user_agent' => substr($userAgent, 0, 255),
        ]);

        ActivityLog::create([
            'user_id' => $user ? $user->id : null,
            'activity' => 'Failed Login',
            'module' => 'Authentication',
            'description' => "Failed login attempt for email/username: {$email} from IP: {$ip}",
            'ip_address' => $ip,
        ]);
    }

    /**
     * Register the listeners for the subscriber.
     */
    public function subscribe($events)
    {
        $events->listen(
            Login::class,
            [AuthEventsListener::class, 'onUserLogin']
        );

        $events->listen(
            Logout::class,
            [AuthEventsListener::class, 'onUserLogout']
        );

        $events->listen(
            Failed::class,
            [AuthEventsListener::class, 'onUserLoginFailed']
        );
    }
}
