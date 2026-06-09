<?php

namespace App\Policies;

use App\Models\Notification;
use App\Models\User;

class NotificationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('notification.view');
    }

    public function manage(User $user): bool
    {
        return $user->hasPermissionTo('notification.manage');
    }
}
