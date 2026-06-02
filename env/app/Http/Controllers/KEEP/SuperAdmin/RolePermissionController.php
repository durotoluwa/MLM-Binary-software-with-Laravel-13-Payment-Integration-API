<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Auth;

class RolePermissionController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user(); // or Auth::guard('web')->user();
        $roles = Role::all();
        $permissions = Permission::all();
        $selectedRole = null;

        if ($request->has('role_id')) {
            $selectedRole = Role::findOrFail($request->role_id);
        }

        return view('superadmin.role_permissions.index', compact('user','roles', 'permissions', 'selectedRole'));
    }

    public function store(Request $request)
    {
        $role = Role::findOrFail($request->role_id);
        $role->syncPermissions($request->permissions ?? []);

        return redirect()->back()->with('success', 'Permissions updated successfully!');
    }
}
