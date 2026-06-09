<?php

namespace App\Services;

use App\Repositories\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService extends BaseService
{
    protected UserRepositoryInterface $userRepo;

    public function __construct(UserRepositoryInterface $userRepo)
    {
        $this->userRepo = $userRepo;
    }

    public function authenticate(array $credentials, bool $remember = false, string $ip = '', ?string $userAgent = null): User
    {
        $email = $credentials['email'] ?? '';
        $password = $credentials['password'] ?? '';

        $user = $this->userRepo->findByEmail($email);

        if (!$user) {
            $this->userRepo->logFailedLogin($email, $ip, $userAgent);
            throw ValidationException::withMessages([
                'email' => 'The provided credentials do not match our records.',
            ]);
        }

        if ($user->status === 'suspended') {
            $this->userRepo->logFailedLogin($email, $ip, $userAgent);
            throw ValidationException::withMessages([
                'email' => 'Your account is locked due to security limits. Please contact system administrator.',
            ]);
        }

        if (Hash::check($password, $user->password)) {
            if ($user->status !== 'active') {
                $this->userRepo->logFailedLogin($email, $ip, $userAgent);
                throw ValidationException::withMessages([
                    'email' => 'Your account is currently inactive.',
                ]);
            }

            $this->userRepo->resetFailedLogins($user);
            $this->userRepo->updateLoginMetadata($user, $ip);
            $this->userRepo->logLoginHistory($user->id, $ip, $userAgent);

            Auth::login($user, $remember);

            return $user;
        }

        $this->userRepo->incrementFailedLogins($user);
        $this->userRepo->logFailedLogin($email, $ip, $userAgent);

        // Since failed_login_attempts hasn't refreshed on $user object locally, we check incremented count
        if (($user->failed_login_attempts + 1) >= 5) {
            $this->userRepo->lockAccount($user);
            throw ValidationException::withMessages([
                'email' => 'Your account has been locked due to 5 failed login attempts. Please contact Admin to unlock.',
            ]);
        }

        throw ValidationException::withMessages([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }

    public function logout(User $user): void
    {
        $this->userRepo->logLogoutHistory($user->id);
        Auth::logout();
    }

    public function changePassword(User $user, string $currentPassword, string $newPassword): void
    {
        if (!Hash::check($currentPassword, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => 'The provided password does not match your current password.',
            ]);
        }

        $user->update([
            'password' => Hash::make($newPassword),
            'password_changed_at' => now(),
        ]);
    }
}
