<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateOfficeTimingRequest;
use App\Services\OfficeTimingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class OfficeTimingController extends Controller
{
    protected $officeTimingService;

    public function __construct(OfficeTimingService $officeTimingService)
    {
        $this->officeTimingService = $officeTimingService;
    }

    public function show(Request $request)
    {
        if (Gate::denies('office_timing.manage')) {
            abort(403);
        }

        $timing = $this->officeTimingService->getDefault();

        if ($request->wantsJson()) {
            return response()->json($timing);
        }

        return view('organization.office_timings.edit', compact('timing'));
    }

    public function edit()
    {
        if (Gate::denies('office_timing.manage')) {
            abort(403);
        }

        $timing = $this->officeTimingService->getDefault();

        return view('organization.office_timings.edit', compact('timing'));
    }

    public function update(UpdateOfficeTimingRequest $request)
    {
        $timing = $this->officeTimingService->updateDefault($request->validated());

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'data' => $timing]);
        }

        return redirect()->route('office-timings.show')->with('success', 'Office timings and rules updated successfully.');
    }
}
