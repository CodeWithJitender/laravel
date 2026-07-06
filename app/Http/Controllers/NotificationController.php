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
        $filter = $request->get('filter', 'all');

        // Fetch unread notifications count (always needed for badge)
        $unreadCount = NotificationRecipient::where('employee_id', $user->id)
            ->where('status', '!=', 'read')
            ->where('status', '!=', 'archived')
            ->count();

        // Fetch read count for the tab badge
        $readCount = NotificationRecipient::where('employee_id', $user->id)
            ->where('status', 'read')
            ->count();

        if ($request->wantsJson()) {
            // Bell dropdown: only show unread notifications
            $unreadNotifications = $this->notificationRepo->getUnreadNotificationsForUser($user, 5);
            return response()->json([
                'success' => true,
                'notifications' => ['data' => $unreadNotifications],
                'unread_count' => $unreadCount,
            ]);
        }

        // Filter notifications based on selected tab
        $query = NotificationRecipient::where('employee_id', $user->id)
            ->with('notification.creator')
            ->orderBy('created_at', 'desc');

        if ($filter === 'unread') {
            $query->where('status', '!=', 'read')->where('status', '!=', 'archived');
        } elseif ($filter === 'read') {
            $query->where('status', 'read');
        }
        // 'all' shows everything (no extra filter)

        $recipients = $query->paginate(15);

        return view('notifications.index', compact('recipients', 'unreadCount', 'readCount', 'filter'));
    }

    /**
     * Mark an individual notification as read.
     */
    public function read(Request $request, $id)
    {
        $user = auth()->user();
        $success = $this->notificationRepo->markAsReadForUser($user, $id);

        // Fetch the action_url for redirect
        $notification = \App\Models\Notification::find($id);
        $actionUrl = $notification ? $notification->action_url : null;

        if ($request->wantsJson()) {
            return response()->json([
                'success' => $success,
                'action_url' => $actionUrl,
            ]);
        }

        if ($actionUrl) {
            return redirect($actionUrl);
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
