<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface AnnouncementRepositoryInterface extends BaseRepositoryInterface
{
    public function getActiveAnnouncementsForUser(User $user, int $limit = 5);
    
    public function paginateActiveAnnouncementsForUser(User $user, int $perPage = 15): LengthAwarePaginator;
    
    public function markAsReadForUser(User $user, int $announcementId): bool;
}
