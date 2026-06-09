<?php

namespace App\Http\Controllers;

use App\Models\Designation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class OrgStructureController extends Controller
{
    public function index(Request $request)
    {
        if (Gate::denies('org_structure.view')) {
            abort(403);
        }

        $designations = Designation::with(['hierarchy.parentDesignation', 'employeeDetails.user'])
            ->where('status', 'active')
            ->orderBy('level')
            ->get();

        // Roots are designations without a parent
        $roots = $designations->filter(function ($desg) {
            return !$desg->hierarchy || !$desg->hierarchy->parent_designation_id;
        });

        $tree = $this->buildTree($roots, $designations);

        if ($request->wantsJson()) {
            return response()->json($tree);
        }

        return view('organization.structure.index', compact('tree'));
    }

    private function buildTree($nodes, $allNodes)
    {
        $tree = [];

        foreach ($nodes as $node) {
            $childrenNodes = $allNodes->filter(function ($item) use ($node) {
                return $item->hierarchy && $item->hierarchy->parent_designation_id == $node->id;
            });

            $tree[] = [
                'id' => $node->id,
                'designation_name' => $node->designation_name,
                'designation_code' => $node->designation_code,
                'level' => $node->level,
                'employees_count' => $node->employeeDetails->count(),
                'employees' => $node->employeeDetails->map(function ($detail) {
                    return $detail->user ? $detail->user->name : 'Unknown';
                })->toArray(),
                'children' => $this->buildTree($childrenNodes, $allNodes)
            ];
        }

        return $tree;
    }
}
