<?php

namespace App\Http\Controllers;

use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\DynamicMail;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function showLogin()
    {
        if (Auth::check()) {
            return redirect('/dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $remember = $request->has('remember');
        $ip = $request->ip();
        $userAgent = $request->userAgent();

        $this->authService->authenticate($credentials, $remember, $ip, $userAgent);

        $request->session()->regenerate();

        return redirect()->intended('/dashboard');
    }

    public function logout(Request $request)
    {
        if (Auth::check()) {
            $this->authService->logout(Auth::user());
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    public function showChangePassword()
    {
        return view('auth.change-password');
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required'],
            'new_password' => ['required', 'min:8', 'confirmed'],
        ]);

        $this->authService->changePassword(Auth::user(), $request->current_password, $request->new_password);

        return redirect('/dashboard')->with('success', 'Password updated successfully.');
    }

    public function showForgotPassword()
    {
        if (Auth::check()) {
            return redirect('/dashboard');
        }
        return view('auth.forgot-password');
    }

    public function sendResetOtp(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => 'We could not find a user with that email address.',
            ]);
        }

        // Generate 6-digit OTP
        $otp = rand(100000, 999999);

        // Save to password_reset_tokens table (update or insert)
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            [
                'token' => $otp,
                'created_at' => now(),
            ]
        );

        // Send OTP mail
        $subject = 'Your Password Reset OTP';
        $content = "Hello {$user->name},\n\nYou requested a password reset for your HRMS Enterprise account. Use the following 6-digit OTP code to complete the reset process:\n\nOTP Code: {$otp}\n\nThis OTP is valid for the next 15 minutes. If you did not request this, please ignore this email.";
        
        Mail::to($request->email)->send(new DynamicMail($subject, $content));

        return redirect()->route('password.reset', ['email' => $request->email])
            ->with('status', 'We have sent an OTP code to your registered email address.');
    }

    public function showResetPassword(Request $request)
    {
        if (Auth::check()) {
            return redirect('/dashboard');
        }
        $email = $request->query('email');
        return view('auth.reset-password', compact('email'));
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'otp' => ['required', 'string', 'size:6'],
            'password' => ['required', 'min:8', 'confirmed'],
        ]);

        $record = DB::table('password_reset_tokens')->where('email', $request->email)->first();

        if (!$record || $record->token !== $request->otp) {
            throw ValidationException::withMessages([
                'otp' => 'The provided OTP is invalid.',
            ]);
        }

        // Check expiration (15 minutes)
        if (now()->diffInMinutes($record->created_at) > 15) {
            // Cleanup
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            throw ValidationException::withMessages([
                'otp' => 'This OTP code has expired. Please request a new one.',
            ]);
        }

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            throw ValidationException::withMessages([
                'email' => 'We could not find a user with that email address.',
            ]);
        }

        // Encrypt password and update user
        $user->update([
            'password' => Hash::make($request->password),
            'password_changed_at' => now(),
        ]);

        // Delete the used token
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return redirect()->route('login')->with('success', 'Your password has been reset successfully. You can now login.');
    }
}
