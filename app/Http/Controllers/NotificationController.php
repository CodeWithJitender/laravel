<?php

namespace App\Http\Controllers;

use App\Repositories\NotificationRepositoryInterface;
use App\Models\NotificationRecipient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class NotificationController extends Controller
{
    protected $notificationRepo;

    public function __construct(NotificationRepositoryInterface $notificationRepo)
    {
        $this->notificationRepo = $notificationRepo;
    }

    /**
     * Display a listing of notifications.
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // Paginate user notifications
        $recipients = $this->notificationRepo->paginateNotificationsForUser($user, 15);

        // Fetch unread notifications count
        $unreadCount = NotificationRecipient::where('employee_id', $user->id)
            ->where('status', '!=', 'read')
            ->where('status', '!=', 'archived')
            ->count();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'notifications' => $recipients,
                'unread_count' => $unreadCount,
            ]);
        }

        return view('notifications.index', compact('recipients', 'unreadCount'));
    }

    /**
     * Mark an individual notification as read.
     */
    public function read(Request $request, $id)
    {
        $user = auth()->user();
        $success = $this->notificationRepo->markAsReadForUser($user, $id);

        if ($request->wantsJson()) {
            return response()->json(['success' => $success]);
        }

        return redirect()->back()->with('success', 'Notification marked as read.');
    }

    /**
     * Mark all notifications as read.
     */
    public function readAll(Request $request)
    {
        $user = auth()->user();
        $count = $this->notificationRepo->markAllAsReadForUser($user);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'marked_count' => $count]);
        }

        return redirect()->back()->with('success', 'All notifications marked as read.');
    }

    /**
     * Archive a specific notification.
     */
    public function archive(Request $request, $id)
    {
        $user = auth()->user();
        $success = $this->notificationRepo->archiveNotificationForUser($user, $id);

        if ($request->wantsJson()) {
            return response()->json(['success' => $success]);
        }

        return redirect()->back()->with('success', 'Notification archived.');
    }

    /**
     * Remove the specified notification from user inbox.
     */
    public function destroy(Request $request, $id)
    {
        $user = auth()->user();
        
        $recipient = NotificationRecipient::where('employee_id', $user->id)
            ->where('notification_id', $id)
            ->firstOrFail();

        $recipient->delete();

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back()->with('success', 'Notification removed.');
    }
}
