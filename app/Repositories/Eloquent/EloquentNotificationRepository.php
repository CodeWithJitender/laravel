<?php

namespace App\Repositories\Eloquent;

use App\Models\Notification;
use App\Models\NotificationRecipient;
use App\Models\User;
use App\Repositories\NotificationRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentNotificationRepository extends BaseRepository implements NotificationRepositoryInterface
{
    public function __construct(Notification $model)
    {
        parent::__construct($model);
    }

    public function getUnreadNotificationsForUser(User $user, int $limit = 5)
    {
        return NotificationRecipient::with('notification.creator')
            ->where('employee_id', $user->id)
            ->where('status', '!=', 'read')
            ->where('status', '!=', 'archived')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function paginateNotificationsForUser(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return NotificationRecipient::with('notification.creator')
            ->where('employee_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function markAllAsReadForUser(User $user): int
    {
        return NotificationRecipient::where('employee_id', $user->id)
            ->where('status', '!=', 'read')
            ->update([
                'status' => 'read',
                'read_at' => now(),
            ]);
    }

    public function markAsReadForUser(User $user, int $notificationId): bool
    {
        return NotificationRecipient::where('employee_id', $user->id)
            ->where('notification_id', $notificationId)
            ->update([
                'status' => 'read',
                'read_at' => now(),
            ]) > 0;
    }

    public function archiveNotificationForUser(User $user, int $notificationId): bool
    {
        return NotificationRecipient::where('employee_id', $user->id)
            ->where('notification_id', $notificationId)
            ->update([
                'status' => 'archived',
            ]) > 0;
    }
}
