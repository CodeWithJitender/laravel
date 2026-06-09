<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Repositories\UserRepositoryInterface;
use Illuminate\Support\Facades\DB;

class EloquentUserRepository extends BaseRepository implements UserRepositoryInterface
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    public function findByEmail(string $email): ?User
    {
        return $this->model->where('email', $email)->first();
    }

    public function incrementFailedLogins(User $user): void
    {
        $user->increment('failed_login_attempts');
    }

    public function resetFailedLogins(User $user): void
    {
        $user->update([
            'failed_login_attempts' => 0,
            'locked_at' => null,
        ]);
    }

    public function lockAccount(User $user): void
    {
        $user->update([
            'status' => 'suspended',
            'locked_at' => now(),
        ]);
    }

    public function updateLoginMetadata(User $user, string $ip): void
    {
        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => $ip,
        ]);
    }

    public function logLoginHistory(int $userId, string $ip, ?string $userAgent): int
    {
        return DB::table('login_histories')->insertGetId([
            'user_id' => $userId,
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'login_at' => now(),
        ]);
    }

    public function logLogoutHistory(int $userId): void
    {
        $latest = DB::table('login_histories')
            ->where('user_id', $userId)
            ->whereNull('logout_at')
            ->orderBy('id', 'desc')
            ->first();

        if ($latest) {
            DB::table('login_histories')
                ->where('id', $latest->id)
                ->update(['logout_at' => now()]);
        }
    }

    public function logFailedLogin(string $email, string $ip, ?string $userAgent): void
    {
        DB::table('failed_logins')->insert([
            'email' => $email,
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'attempted_at' => now(),
        ]);
    }
}
