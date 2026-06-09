<?php

namespace App\Http\Controllers;

use App\Models\NotificationTemplate;
use App\Models\NotificationDeliveryLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class NotificationTemplateController extends Controller
{
    /**
     * Display list of notification templates.
     */
    public function index()
    {
        if (Gate::denies('notification.template.manage')) {
            abort(403);
        }

        $templates = NotificationTemplate::orderBy('key', 'asc')->get();

        return view('notifications.templates.index', compact('templates'));
    }

    /**
     * Show form for editing template.
     */
    public function edit($id)
    {
        if (Gate::denies('notification.template.manage')) {
            abort(403);
        }

        $template = NotificationTemplate::findOrFail($id);

        return view('notifications.templates.edit', compact('template'));
    }

    /**
     * Update template details.
     */
    public function update(Request $request, $id)
    {
        if (Gate::denies('notification.template.manage')) {
            abort(403);
        }

        $template = NotificationTemplate::findOrFail($id);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
            'channels' => 'required|array',
            'channels.*' => 'string|in:in_app,email',
            'status' => 'required|in:active,inactive',
        ]);

        $template->update($data);

        return redirect()->route('notification-templates.index')->with('success', 'Notification template updated successfully.');
    }

    /**
     * Display delivery logs.
     */
    public function logs(Request $request)
    {
        if (Gate::denies('notification.manage')) {
            abort(403);
        }

        $logs = NotificationDeliveryLog::with(['notification', 'employee'])
            ->orderBy('created_at', 'desc')
            ->paginate(30);

        return view('notifications.logs.index', compact('logs'));
    }
}
