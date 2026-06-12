<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDesignationRequest;
use App\Http\Requests\UpdateDesignationRequest;
use App\Services\DesignationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class DesignationController extends Controller
{
    protected $designationService;

    public function __construct(DesignationService $designationService)
    {
        $this->designationService = $designationService;
    }

    public function index(Request $request)
    {
        if (Gate::denies('designation.view')) {
            abort(403);
        }

        $search = $request->input('search', '');
        $status = $request->input('status', 'all');
        $designations = $this->designationService->getPaginated(10, $search, $status);

        if ($request->wantsJson()) {
            return response()->json($designations);
        }

        return view('organization.designations.index', compact('designations', 'search', 'status'));
    }

    public function create()
    {
        if (Gate::denies('designation.create')) {
            abort(403);
        }

        $parentDesignations = $this->designationService->getAllActive();

        return view('organization.designations.create', compact('parentDesignations'));
    }

    public function store(StoreDesignationRequest $request)
    {
        $designation = $this->designationService->createDesignation($request->validated());

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'data' => $designation], 201);
        }

        return redirect()->route('designations.index')->with('success', 'Designation created successfully.');
    }

    public function show(Request $request, $id)
    {
        if (Gate::denies('designation.view')) {
            abort(403);
        }

        $designation = $this->designationService->findById($id);

        if ($request->wantsJson()) {
            return response()->json($designation);
        }

        return view('organization.designations.show', compact('designation'));
    }

    public function edit($id)
    {
        if (Gate::denies('designation.edit')) {
            abort(403);
        }

        $designation = $this->designationService->findById($id);
        $parentDesignations = $this->designationService->getAllActive()->where('id', '!=', $id);

        return view('organization.designations.edit', compact('designation', 'parentDesignations'));
    }

    public function update(UpdateDesignationRequest $request, $id)
    {
        $this->designationService->updateDesignation($id, $request->validated());

        if ($request->wantsJson()) {
            $designation = $this->designationService->findById($id);
            return response()->json(['success' => true, 'data' => $designation]);
        }

        return redirect()->route('designations.index')->with('success', 'Designation updated successfully.');
    }

    public function destroy(Request $request, $id)
    {
        if (Gate::denies('designation.delete')) {
            abort(403);
        }

        try {
            $this->designationService->deleteDesignation($id);

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Designation deleted successfully.']);
            }

            return redirect()->route('designations.index')->with('success', 'Designation deleted successfully.');
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
            }

            return redirect()->route('designations.index')->with('error', $e->getMessage());
        }
    }

    public function bulkDestroy(Request $request)
    {
        if (Gate::denies('designation.delete')) {
            abort(403);
        }

        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return response()->json(['success' => false, 'message' => 'No designation selected.'], 400);
        }

        $deletedCount = 0;
        $skippedCount = 0;
        $errors = [];

        foreach ($ids as $id) {
            try {
                $this->designationService->deleteDesignation($id);
                $deletedCount++;
            } catch (\Exception $e) {
                $skippedCount++;
                $errors[] = $e->getMessage();
            }
        }

        if ($deletedCount === 0) {
            return response()->json(['success' => false, 'message' => implode("\n", array_unique($errors))], 400);
        }

        $message = "Successfully deleted {$deletedCount} designation(s).";
        if ($skippedCount > 0) {
            $message .= " {$skippedCount} designation(s) were skipped because they have active employees assigned.";
        }

        return response()->json(['success' => true, 'message' => $message]);
    }

    public function bulkUpdate(Request $request)
    {
        if (Gate::denies('designation.edit')) {
            abort(403);
        }

        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return response()->json(['success' => false, 'message' => 'No designation selected.'], 400);
        }

        try {
            $status = $request->input('status');
            if ($status) {
                \App\Models\Designation::whereIn('id', $ids)->update(['status' => $status]);
            }
            return response()->json(['success' => true, 'message' => 'Selected designations updated successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }
}
