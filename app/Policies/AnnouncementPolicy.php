<?php

namespace App\Policies;

use App\Models\Announcement;
use App\Models\User;

class AnnouncementPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('announcement.view');
    }

    public function view(User $user, Announcement $announcement): bool
    {
        return $user->hasPermissionTo('announcement.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('announcement.create');
    }

    public function update(User $user, Announcement $announcement): bool
    {
        return $user->hasPermissionTo('announcement.edit') || $announcement->created_by === $user->id;
    }

    public function delete(User $user, Announcement $announcement): bool
    {
        return $user->hasPermissionTo('announcement.delete') || $announcement->created_by === $user->id;
    }

    public function publish(User $user): bool
    {
        return $user->hasPermissionTo('announcement.publish');
    }
}
