<?php

namespace App\Repositories\Eloquent;

use App\Models\Announcement;
use App\Models\AnnouncementRecipient;
use App\Models\User;
use App\Repositories\AnnouncementRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentAnnouncementRepository extends BaseRepository implements AnnouncementRepositoryInterface
{
    public function __construct(Announcement $model)
    {
        parent::__construct($model);
    }

    public function getActiveAnnouncementsForUser(User $user, int $limit = 5)
    {
        $detail = $user->employeeDetail;
        $departmentId = $detail?->department_id;
        $locationId = $detail?->location_id;
        $roles = $user->roles->pluck('name')->toArray();

        return Announcement::with(['category', 'creator'])
            ->where('status', 'published')
            ->where('publish_at', '<=', now())
            ->where(function ($q) {
                $q->whereNull('expire_at')
                  ->orWhere('expire_at', '>=', now());
            })
            ->where(function ($q) use ($user, $departmentId, $locationId, $roles) {
                $q->where('audience_type', 'all')
                  ->orWhere(function ($sub) use ($departmentId) {
                      $sub->where('audience_type', 'department')
                          ->whereJsonContains('audience_values', $departmentId);
                  })
                  ->orWhere(function ($sub) use ($locationId) {
                      $sub->where('audience_type', 'location')
                          ->whereJsonContains('audience_values', $locationId);
                  })
                  ->orWhere(function ($sub) use ($roles) {
                      $sub->where('audience_type', 'role')
                          ->where(function ($roleQuery) use ($roles) {
                              foreach ($roles as $role) {
                                  $roleQuery->orWhereJsonContains('audience_values', $role);
                              }
                          });
                  })
                  ->orWhere(function ($sub) use ($user) {
                      $sub->where('audience_type', 'individual')
                          ->whereJsonContains('audience_values', $user->id);
                  });
            })
            ->orderBy('publish_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function paginateActiveAnnouncementsForUser(User $user, int $perPage = 15): LengthAwarePaginator
    {
        $detail = $user->employeeDetail;
        $departmentId = $detail?->department_id;
        $locationId = $detail?->location_id;
        $roles = $user->roles->pluck('name')->toArray();

        return Announcement::with(['category', 'creator', 'recipients' => function ($q) use ($user) {
                $q->where('employee_id', $user->id);
            }])
            ->where('status', 'published')
            ->where('publish_at', '<=', now())
            ->where(function ($q) {
                $q->whereNull('expire_at')
                  ->orWhere('expire_at', '>=', now());
            })
            ->where(function ($q) use ($user, $departmentId, $locationId, $roles) {
                $q->where('audience_type', 'all')
                  ->orWhere(function ($sub) use ($departmentId) {
                      $sub->where('audience_type', 'department')
                          ->whereJsonContains('audience_values', $departmentId);
                  })
                  ->orWhere(function ($sub) use ($locationId) {
                      $sub->where('audience_type', 'location')
                          ->whereJsonContains('audience_values', $locationId);
                  })
                  ->orWhere(function ($sub) use ($roles) {
                      $sub->where('audience_type', 'role')
                          ->where(function ($roleQuery) use ($roles) {
                              foreach ($roles as $role) {
                                  $roleQuery->orWhereJsonContains('audience_values', $role);
                              }
                          });
                  })
                  ->orWhere(function ($sub) use ($user) {
                      $sub->where('audience_type', 'individual')
                          ->whereJsonContains('audience_values', $user->id);
                  });
            })
            ->orderBy('publish_at', 'desc')
            ->paginate($perPage);
    }

    public function markAsReadForUser(User $user, int $announcementId): bool
    {
        return AnnouncementRecipient::updateOrCreate(
            ['announcement_id' => $announcementId, 'employee_id' => $user->id],
            ['read_at' => now()]
        )->wasChanged() || true;
    }
}
