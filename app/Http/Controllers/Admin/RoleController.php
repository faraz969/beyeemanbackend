<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    /**
     * Display a listing of roles.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = Role::withCount(['permissions', 'users'])->with('permissions');
        
        // Search filter
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%");
        }
        
        $roles = $query->latest()->paginate(15);
        
        return view('admin.roles.index', [
            'roles' => $roles,
            'filters' => $request->only(['search'])
        ]);
    }

    /**
     * Show the form for creating a new role.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $permissions = Permission::orderBy('name')->get()->groupBy(function($permission) {
            $parts = explode(' ', $permission->name);
            return count($parts) > 1 ? $parts[0] : 'other';
        });
        
        return view('admin.roles.create', [
            'permissions' => $permissions
        ]);
    }

    /**
     * Store a newly created role.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);
        
        $role = Role::create([
            'name' => $validated['name'],
            'guard_name' => 'web'
        ]);
        
        if ($request->has('permissions')) {
            $permissions = Permission::whereIn('id', $validated['permissions'])->get();
            $role->syncPermissions($permissions);
        }
        
        return redirect()->route('admin.roles.index')
            ->with('success', 'Role created successfully.');
    }

    /**
     * Display the specified role.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $role = Role::with(['permissions', 'users'])->findOrFail($id);
        
        return view('admin.roles.show', [
            'role' => $role
        ]);
    }

    /**
     * Show the form for editing the specified role.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $role = Role::with('permissions')->findOrFail($id);
        $permissions = Permission::orderBy('name')->get()->groupBy(function($permission) {
            $parts = explode(' ', $permission->name);
            return count($parts) > 1 ? $parts[0] : 'other';
        });
        
        return view('admin.roles.edit', [
            'role' => $role,
            'permissions' => $permissions
        ]);
    }

    /**
     * Update the specified role.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);
        
        // Prevent editing super-admin and admin names for safety
        if (in_array($role->name, ['super-admin', 'admin']) && $request->name !== $role->name) {
            return redirect()->back()
                ->with('error', 'Cannot change the name of protected roles.');
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $id,
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);
        
        $role->update([
            'name' => $validated['name']
        ]);
        
        if ($request->has('permissions')) {
            $permissions = Permission::whereIn('id', $validated['permissions'])->get();
            $role->syncPermissions($permissions);
        } else {
            $role->permissions()->detach();
        }
        
        return redirect()->route('admin.roles.index')
            ->with('success', 'Role updated successfully.');
    }

    /**
     * Remove the specified role.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $role = Role::findOrFail($id);
        
        // Prevent deleting protected roles
        if (in_array($role->name, ['super-admin', 'admin'])) {
            return redirect()->route('admin.roles.index')
                ->with('error', 'Cannot delete protected roles.');
        }
        
        // Check if role has users
        if ($role->users()->count() > 0) {
            return redirect()->route('admin.roles.index')
                ->with('error', 'Cannot delete role with assigned users. Please reassign users first.');
        }
        
        $role->delete();
        
        return redirect()->route('admin.roles.index')
            ->with('success', 'Role deleted successfully.');
    }
}

