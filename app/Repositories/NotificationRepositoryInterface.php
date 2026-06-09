<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface NotificationRepositoryInterface extends BaseRepositoryInterface
{
    public function getUnreadNotificationsForUser(User $user, int $limit = 5);
    
    public function paginateNotificationsForUser(User $user, int $perPage = 15): LengthAwarePaginator;
    
    public function markAllAsReadForUser(User $user): int;
    
    public function markAsReadForUser(User $user, int $notificationId): bool;
    
    public function archiveNotificationForUser(User $user, int $notificationId): bool;
}
