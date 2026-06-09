<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function index()
    {
        $roles = Role::all();
        $permissions = Permission::all()->groupBy(function ($perm) {
            return explode('.', $perm->name)[0] ?? 'Other';
        });

        return view('roles.matrix', compact('roles', 'permissions'));
    }

    public function syncMatrix(Request $request)
    {
        $request->validate([
            'matrix' => ['array'],
        ]);

        $matrix = $request->input('matrix', []);
        $roles = Role::all();

        foreach ($roles as $role) {
            if ($role->name === 'Admin') {
                $role->syncPermissions(Permission::all());
                continue;
            }

            $rolePerms = $matrix[$role->id] ?? [];
            $role->syncPermissions($rolePerms);
        }

        return redirect()->back()->with('success', 'Permission matrix updated successfully.');
    }
}
