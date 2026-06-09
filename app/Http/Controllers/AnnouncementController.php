<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAnnouncementRequest;
use App\Models\Announcement;
use App\Models\AnnouncementCategory;
use App\Models\Department;
use App\Models\Location;
use App\Services\AnnouncementService;
use App\Repositories\AnnouncementRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AnnouncementController extends Controller
{
    protected $announcementService;
    protected $announcementRepo;

    public function __construct(
        AnnouncementService $announcementService,
        AnnouncementRepositoryInterface $announcementRepo
    ) {
        $this->announcementService = $announcementService;
        $this->announcementRepo = $announcementRepo;
    }

    /**
     * Display a listing of announcements.
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        if ($user->hasAnyRole(['Admin', 'Manager'])) {
            // Admin and Manager can see administrative index of all announcements
            $announcements = Announcement::with(['category', 'creator'])
                ->orderBy('created_at', 'desc')
                ->paginate(15);
        } else {
            // Employees see only active announcements resolved for them
            $announcements = $this->announcementRepo->paginateActiveAnnouncementsForUser($user, 15);
        }

        if ($request->wantsJson()) {
            return response()->json($announcements);
        }

        $categories = AnnouncementCategory::where('status', 'active')->get();
        $departments = Department::where('status', 'active')->get();
        $locations = Location::where('status', 'active')->get();

        return view('announcements.index', compact('announcements', 'categories', 'departments', 'locations'));
    }

    /**
     * Store a newly created announcement.
     */
    public function store(StoreAnnouncementRequest $request)
    {
        Gate::authorize('create', Announcement::class);

        $creator = auth()->user();
        $this->announcementService->createAnnouncement($request->validated(), $creator);

        return redirect()->back()->with('success', 'Announcement created successfully.');
    }

    /**
     * Update the specified announcement.
     */
    public function update(StoreAnnouncementRequest $request, $id)
    {
        $announcement = Announcement::findOrFail($id);
        Gate::authorize('update', $announcement);

        $this->announcementService->updateAnnouncement($id, $request->validated());

        return redirect()->back()->with('success', 'Announcement updated successfully.');
    }

    /**
     * Remove the specified announcement.
     */
    public function destroy($id)
    {
        $announcement = Announcement::findOrFail($id);
        Gate::authorize('delete', $announcement);

        $announcement->delete();

        return redirect()->back()->with('success', 'Announcement deleted.');
    }

    /**
     * Publish draft/scheduled announcement.
     */
    public function publish($id)
    {
        $announcement = Announcement::findOrFail($id);
        Gate::authorize('update', $announcement);

        $this->announcementService->updateAnnouncement($id, [
            'status' => 'published',
            'publish_at' => now()->toDateTimeString(),
        ]);

        return redirect()->back()->with('success', 'Announcement published successfully.');
    }

    /**
     * Mark announcement as read for user.
     */
    public function markRead(Request $request, $id)
    {
        $user = auth()->user();
        $this->announcementRepo->markAsReadForUser($user, $id);

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back();
    }

    /**
     * Store a newly created announcement category.
     */
    public function storeCategory(Request $request)
    {
        Gate::authorize('create', Announcement::class);

        $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:announcement_categories,name'],
            'color' => ['required', 'string', 'max:7', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
        ]);

        AnnouncementCategory::create([
            'name' => $request->name,
            'color' => $request->color,
            'status' => 'active',
        ]);

        return redirect()->back()->with('success', 'Announcement category created successfully.');
    }
}
