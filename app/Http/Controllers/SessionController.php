<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class SessionController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id();
        $currentSessionId = $request->session()->getId();

        $sessions = DB::table('sessions')
            ->where('user_id', $userId)
            ->get()
            ->map(function ($session) use ($currentSessionId) {
                $userAgent = $session->user_agent;
                $browser = 'Unknown Browser';
                $platform = 'Unknown Platform';

                if (preg_match('/MSIE/i', $userAgent) && !preg_match('/Opera/i', $userAgent)) {
                    $browser = 'Internet Explorer';
                } elseif (preg_match('/Firefox/i', $userAgent)) {
                    $browser = 'Mozilla Firefox';
                } elseif (preg_match('/Chrome/i', $userAgent)) {
                    $browser = 'Google Chrome';
                } elseif (preg_match('/Safari/i', $userAgent)) {
                    $browser = 'Apple Safari';
                } elseif (preg_match('/Opera/i', $userAgent)) {
                    $browser = 'Opera';
                } elseif (preg_match('/Netscape/i', $userAgent)) {
                    $browser = 'Netscape';
                }

                if (preg_match('/windows|win32/i', $userAgent)) {
                    $platform = 'Windows';
                } elseif (preg_match('/macintosh|mac os x/i', $userAgent)) {
                    $platform = 'Mac OS';
                } elseif (preg_match('/linux/i', $userAgent)) {
                    $platform = 'Linux';
                } elseif (preg_match('/iphone|ipad/i', $userAgent)) {
                    $platform = 'iOS';
                } elseif (preg_match('/android/i', $userAgent)) {
                    $platform = 'Android';
                }

                return [
                    'id' => $session->id,
                    'ip_address' => $session->ip_address,
                    'browser' => $browser,
                    'platform' => $platform,
                    'last_active' => date('Y-m-d H:i:s', $session->last_activity),
                    'is_current' => $session->id === $currentSessionId,
                ];
            });

        return view('auth.sessions', compact('sessions'));
    }

    public function destroy(Request $request, string $id)
    {
        $userId = Auth::id();
        $currentSessionId = $request->session()->getId();

        if ($id === $currentSessionId) {
            return back()->withErrors(['session' => 'You cannot terminate your current active session here. Use Log Out instead.']);
        }

        DB::table('sessions')
            ->where('user_id', $userId)
            ->where('id', $id)
            ->delete();

        return back()->with('success', 'Device session terminated successfully.');
    }

    public function clearAll(Request $request)
    {
        $userId = Auth::id();
        $currentSessionId = $request->session()->getId();

        DB::table('sessions')
            ->where('user_id', $userId)
            ->where('id', '!=', $currentSessionId)
            ->delete();

        return back()->with('success', 'All other device sessions terminated successfully.');
    }
}
