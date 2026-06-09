<?php

namespace App\Repositories;

use App\Models\User;

interface UserRepositoryInterface extends BaseRepositoryInterface
{
    public function findByEmail(string $email): ?User;

    public function incrementFailedLogins(User $user): void;

    public function resetFailedLogins(User $user): void;

    public function lockAccount(User $user): void;

    public function updateLoginMetadata(User $user, string $ip): void;

    public function logLoginHistory(int $userId, string $ip, ?string $userAgent): int;

    public function logLogoutHistory(int $userId): void;

    public function logFailedLogin(string $email, string $ip, ?string $userAgent): void;
}
