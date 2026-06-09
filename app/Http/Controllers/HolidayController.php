<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreHolidayRequest;
use App\Models\Holiday;
use App\Models\HolidayType;
use App\Models\Location;
use App\Services\HolidayService;
use App\Repositories\HolidayRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class HolidayController extends Controller
{
    protected $holidayService;
    protected $holidayRepo;

    public function __construct(
        HolidayService $holidayService,
        HolidayRepositoryInterface $holidayRepo
    ) {
        $this->holidayService = $holidayService;
        $this->holidayRepo = $holidayRepo;
    }

    /**
     * Display listing of holidays.
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // Check view permission via policy
        if (Gate::denies('viewAny', Holiday::class)) {
            abort(403);
        }

        $filters = $request->only(['year', 'location_id', 'holiday_type_id', 'status']);
        
        // Default to current year if no year filter
        if (empty($filters['year'])) {
            $filters['year'] = date('Y');
        }

        $holidays = $this->holidayRepo->paginateHolidays($filters, 15);

        if ($request->wantsJson()) {
            return response()->json($holidays);
        }

        $holidayTypes = HolidayType::where('status', 'active')->get();
        $locations = Location::where('status', 'active')->get();
        $yearExpression = \Illuminate\Support\Facades\DB::getDriverName() === 'sqlite' 
            ? "strftime('%Y', holiday_date) as year" 
            : "YEAR(holiday_date) as year";

        $years = Holiday::selectRaw($yearExpression)
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->toArray();

        if (empty($years)) {
            $years = [date('Y')];
        }

        return view('holidays.index', compact('holidays', 'holidayTypes', 'locations', 'years', 'filters'));
    }

    /**
     * Store new holiday.
     */
    public function store(StoreHolidayRequest $request)
    {
        if (Gate::denies('create', Holiday::class)) {
            abort(403);
        }

        $actor = auth()->user();
        $this->holidayService->createHoliday($request->validated(), $actor);

        return redirect()->route('holidays.index')->with('success', 'Holiday created successfully.');
    }

    /**
     * Update specified holiday.
     */
    public function update(StoreHolidayRequest $request, $id)
    {
        $holiday = Holiday::findOrFail($id);
        
        if (Gate::denies('update', $holiday)) {
            abort(403);
        }

        $this->holidayService->updateHoliday($id, $request->validated());

        return redirect()->route('holidays.index')->with('success', 'Holiday updated successfully.');
    }

    /**
     * Delete specified holiday.
     */
    public function destroy($id)
    {
        $holiday = Holiday::findOrFail($id);

        if (Gate::denies('delete', $holiday)) {
            abort(403);
        }

        $this->holidayService->deleteHoliday($id);

        return redirect()->route('holidays.index')->with('success', 'Holiday deleted.');
    }

    /**
     * Publish specified holiday.
     */
    public function publish($id)
    {
        $holiday = Holiday::findOrFail($id);

        if (Gate::denies('publish', $holiday)) {
            abort(403);
        }

        $this->holidayService->publishHoliday($id);

        return redirect()->route('holidays.index')->with('success', 'Holiday published successfully.');
    }

    /**
     * Display Holiday Calendar View.
     */
    public function calendar(Request $request)
    {
        if (Gate::denies('viewAny', Holiday::class)) {
            abort(403);
        }

        $user = auth()->user();
        $year = $request->input('year', date('Y'));
        $locationId = $request->input('location_id');

        // Fetch location holidays
        $query = Holiday::with('holidayType')
            ->where('status', 'published')
            ->whereYear('holiday_date', $year);

        if ($locationId) {
            $query->where(function ($q) use ($locationId) {
                $q->whereHas('locations', function ($l) use ($locationId) {
                    $l->where('locations.id', $locationId);
                })->orWhereDoesntHave('locations');
            });
        }

        $holidays = $query->orderBy('holiday_date', 'asc')->get();

        $locations = Location::where('status', 'active')->get();

        return view('holidays.calendar', compact('holidays', 'locations', 'year', 'locationId'));
    }

    /**
     * Display Holiday Reports dashboard.
     */
    public function reports(Request $request)
    {
        if (Gate::denies('viewAny', Holiday::class)) {
            abort(403);
        }

        $year = $request->input('year', date('Y'));

        // General stats
        $totalHolidays = Holiday::where('status', 'published')->whereYear('holiday_date', $year)->count();
        $nationalCount = Holiday::where('status', 'published')
            ->whereYear('holiday_date', $year)
            ->whereHas('holidayType', function ($q) {
                $q->where('code', 'national');
            })
            ->count();

        // Fetch all published holidays for the year
        $holidays = Holiday::with(['holidayType', 'locations'])
            ->where('status', 'published')
            ->whereYear('holiday_date', $year)
            ->orderBy('holiday_date', 'asc')
            ->get();

        return view('holidays.reports', compact('holidays', 'totalHolidays', 'nationalCount', 'year'));
    }
}
