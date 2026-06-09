<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLocationRequest;
use App\Http\Requests\UpdateLocationRequest;
use App\Services\LocationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class LocationController extends Controller
{
    protected $locationService;

    public function __construct(LocationService $locationService)
    {
        $this->locationService = $locationService;
    }

    public function index(Request $request)
    {
        if (Gate::denies('location.view')) {
            abort(403);
        }

        $search = $request->input('search', '');
        $status = $request->input('status', 'all');
        $locations = $this->locationService->getPaginated(10, $search, $status);

        if ($request->wantsJson()) {
            return response()->json($locations);
        }

        return view('organization.locations.index', compact('locations', 'search', 'status'));
    }

    public function create()
    {
        if (Gate::denies('location.create')) {
            abort(403);
        }

        return view('organization.locations.create');
    }

    public function store(StoreLocationRequest $request)
    {
        $location = $this->locationService->createLocation($request->validated());

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'data' => $location], 201);
        }

        return redirect()->route('locations.index')->with('success', 'Location created successfully.');
    }

    public function show(Request $request, $id)
    {
        if (Gate::denies('location.view')) {
            abort(403);
        }

        $location = $this->locationService->findById($id);

        if ($request->wantsJson()) {
            return response()->json($location);
        }

        return view('organization.locations.show', compact('location'));
    }

    public function edit($id)
    {
        if (Gate::denies('location.edit')) {
            abort(403);
        }

        $location = $this->locationService->findById($id);

        return view('organization.locations.edit', compact('location'));
    }

    public function update(UpdateLocationRequest $request, $id)
    {
        $this->locationService->updateLocation($id, $request->validated());

        if ($request->wantsJson()) {
            $location = $this->locationService->findById($id);
            return response()->json(['success' => true, 'data' => $location]);
        }

        return redirect()->route('locations.index')->with('success', 'Location updated successfully.');
    }

    public function destroy(Request $request, $id)
    {
        if (Gate::denies('location.delete')) {
            abort(403);
        }

        try {
            $this->locationService->deleteLocation($id);

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Location deleted successfully.']);
            }

            return redirect()->route('locations.index')->with('success', 'Location deleted successfully.');
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
            }

            return redirect()->route('locations.index')->with('error', $e->getMessage());
        }
    }
}
