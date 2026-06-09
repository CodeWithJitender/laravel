<?php

namespace App\Services;

use App\Models\Announcement;
use App\Models\AnnouncementCategory;
use App\Models\AnnouncementRecipient;
use App\Models\User;
use App\Services\NotificationService;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class AnnouncementService extends BaseService
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Create a new announcement.
     */
    public function createAnnouncement(array $data, User $creator)
    {
        return $this->transaction(function () use ($data, $creator) {
            $publishAt = isset($data['publish_at']) ? Carbon::parse($data['publish_at']) : Carbon::now();
            $expireAt = isset($data['expire_at']) && !empty($data['expire_at']) ? Carbon::parse($data['expire_at']) : null;

            $status = $data['status'] ?? 'draft';
            if ($status === 'published' && $publishAt->isFuture()) {
                $status = 'scheduled';
            }

            // Audience values resolve
            $audienceValues = $data['audience_values'] ?? [];

            $announcement = Announcement::create([
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'content' => $data['content'],
                'category_id' => $data['category_id'],
                'audience_type' => $data['audience_type'] ?? 'all',
                'audience_values' => $audienceValues,
                'status' => $status,
                'publish_at' => $publishAt,
                'expire_at' => $expireAt,
                'created_by' => $creator->id,
            ]);

            // If immediate publish, notify target users
            if ($status === 'published') {
                $this->notifyAudience($announcement);
            }

            return $announcement;
        });
    }

    /**
     * Update an announcement.
     */
    public function updateAnnouncement(int $id, array $data)
    {
        return $this->transaction(function () use ($id, $data) {
            $announcement = Announcement::findOrFail($id);

            $publishAt = isset($data['publish_at']) ? Carbon::parse($data['publish_at']) : $announcement->publish_at;
            $expireAt = isset($data['expire_at']) ? (!empty($data['expire_at']) ? Carbon::parse($data['expire_at']) : null) : $announcement->expire_at;

            $status = $data['status'] ?? $announcement->status;
            if ($status === 'published' && $publishAt->isFuture()) {
                $status = 'scheduled';
            }

            $oldStatus = $announcement->status;

            $announcement->update([
                'title' => $data['title'] ?? $announcement->title,
                'description' => $data['description'] ?? $announcement->description,
                'content' => $data['content'] ?? $announcement->content,
                'category_id' => $data['category_id'] ?? $announcement->category_id,
                'audience_type' => $data['audience_type'] ?? $announcement->audience_type,
                'audience_values' => $data['audience_values'] ?? $announcement->audience_values,
                'status' => $status,
                'publish_at' => $publishAt,
                'expire_at' => $expireAt,
            ]);

            // Notify if transitioning to published
            if ($status === 'published' && $oldStatus !== 'published') {
                $this->notifyAudience($announcement);
            }

            return $announcement;
        });
    }

    /**
     * Publish scheduled announcements (called from scheduler or controller).
     */
    public function publishScheduled()
    {
        return $this->transaction(function () {
            $announcements = Announcement::where('status', 'scheduled')
                ->where('publish_at', '<=', now())
                ->get();

            $count = 0;
            foreach ($announcements as $announcement) {
                $announcement->update(['status' => 'published']);
                $this->notifyAudience($announcement);
                $count++;
            }

            return $count;
        });
    }

    /**
     * Read an announcement.
     */
    public function markAsRead(User $user, int $announcementId)
    {
        return AnnouncementRecipient::updateOrCreate(
            [
                'announcement_id' => $announcementId,
                'employee_id' => $user->id,
            ],
            [
                'read_at' => now(),
            ]
        );
    }

    /**
     * Send in-app and email notifications to the targeted audience.
     */
    public function notifyAudience(Announcement $announcement)
    {
        $payload = [
            'title' => 'New Corporate Announcement',
            'subject' => $announcement->title,
            'message' => $announcement->description ?? Str::words(strip_tags($announcement->content), 20),
            'type' => 'announcement',
            'priority' => 'medium',
            'channels' => ['in_app', 'email'],
        ];

        $this->notificationService->sendCustom(
            $payload,
            $announcement->audience_type,
            $announcement->audience_values,
            $announcement->creator
        );
    }
}
