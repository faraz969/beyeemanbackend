<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    /**
     * Display a listing of permissions.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = Permission::withCount(['roles', 'users']);
        
        // Search filter
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%");
        }
        
        $permissions = $query->orderBy('name')->paginate(20);
        
        // Group permissions by prefix for better organization
        $groupedPermissions = $permissions->groupBy(function($permission) {
            $parts = explode(' ', $permission->name);
            return count($parts) > 1 ? $parts[0] : 'other';
        });
        
        return view('admin.permissions.index', [
            'permissions' => $permissions,
            'groupedPermissions' => $groupedPermissions,
            'filters' => $request->only(['search'])
        ]);
    }

    /**
     * Show the form for creating a new permission.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('admin.permissions.create');
    }

    /**
     * Store a newly created permission.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name',
            'guard_name' => 'nullable|string|max:255',
        ]);
        
        Permission::create([
            'name' => $validated['name'],
            'guard_name' => $validated['guard_name'] ?? 'web'
        ]);
        
        return redirect()->route('admin.permissions.index')
            ->with('success', 'Permission created successfully.');
    }

    /**
     * Display the specified permission.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $permission = Permission::with(['roles', 'users'])->findOrFail($id);
        
        return view('admin.permissions.show', [
            'permission' => $permission
        ]);
    }

    /**
     * Show the form for editing the specified permission.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $permission = Permission::findOrFail($id);
        
        return view('admin.permissions.edit', [
            'permission' => $permission
        ]);
    }

    /**
     * Update the specified permission.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $permission = Permission::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name,' . $id,
            'guard_name' => 'nullable|string|max:255',
        ]);
        
        $permission->update($validated);
        
        return redirect()->route('admin.permissions.index')
            ->with('success', 'Permission updated successfully.');
    }

    /**
     * Remove the specified permission.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $permission = Permission::findOrFail($id);
        
        // Check if permission is assigned to roles
        if ($permission->roles()->count() > 0) {
            return redirect()->route('admin.permissions.index')
                ->with('error', 'Cannot delete permission that is assigned to roles. Please remove from roles first.');
        }
        
        $permission->delete();
        
        return redirect()->route('admin.permissions.index')
            ->with('success', 'Permission deleted successfully.');
    }
}

