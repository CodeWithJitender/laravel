<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreShiftRequest;
use App\Http\Requests\UpdateShiftRequest;
use App\Services\ShiftService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ShiftController extends Controller
{
    protected $shiftService;

    public function __construct(ShiftService $shiftService)
    {
        $this->shiftService = $shiftService;
    }

    public function index(Request $request)
    {
        if (Gate::denies('shift.view')) {
            abort(403);
        }

        $search = $request->input('search', '');
        $status = $request->input('status', 'all');
        $shifts = $this->shiftService->getPaginated(10, $search, $status);

        if ($request->wantsJson()) {
            return response()->json($shifts);
        }

        return view('organization.shifts.index', compact('shifts', 'search', 'status'));
    }

    public function create()
    {
        if (Gate::denies('shift.create')) {
            abort(403);
        }

        return view('organization.shifts.create');
    }

    public function store(StoreShiftRequest $request)
    {
        $shift = $this->shiftService->createShift($request->validated());

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'data' => $shift], 201);
        }

        return redirect()->route('shifts.index')->with('success', 'Shift created successfully.');
    }

    public function show(Request $request, $id)
    {
        if (Gate::denies('shift.view')) {
            abort(403);
        }

        $shift = $this->shiftService->findById($id);

        if ($request->wantsJson()) {
            return response()->json($shift);
        }

        return view('organization.shifts.show', compact('shift'));
    }

    public function edit($id)
    {
        if (Gate::denies('shift.edit')) {
            abort(403);
        }

        $shift = $this->shiftService->findById($id);

        return view('organization.shifts.edit', compact('shift'));
    }

    public function update(UpdateShiftRequest $request, $id)
    {
        $this->shiftService->updateShift($id, $request->validated());

        if ($request->wantsJson()) {
            $shift = $this->shiftService->findById($id);
            return response()->json(['success' => true, 'data' => $shift]);
        }

        return redirect()->route('shifts.index')->with('success', 'Shift updated successfully.');
    }

    public function destroy(Request $request, $id)
    {
        if (Gate::denies('shift.delete')) {
            abort(403);
        }

        try {
            $this->shiftService->deleteShift($id);

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Shift deleted successfully.']);
            }

            return redirect()->route('shifts.index')->with('success', 'Shift deleted successfully.');
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
            }

            return redirect()->route('shifts.index')->with('error', $e->getMessage());
        }
    }
}
