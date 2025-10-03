<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    /**
     * Display a listing of roles.
     */
    public function index()
    {
        $roles = Role::withCount('users')->latest()->paginate(20);
        return view('admin.roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new role.
     */
    public function create()
    {
        return view('admin.roles.create');
    }

    /**
     * Store a newly created role in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        Role::create($validated);

        return redirect()->route('admin.roles.index')
                        ->with('success', 'Role created successfully.');
    }

    /**
     * Display the specified role.
     */
    public function show(Role $role)
    {
        $role->load(['users' => function ($query) {
            $query->latest()->limit(10);
        }]);
        
        return view('admin.roles.show', compact('role'));
    }

    /**
     * Show the form for editing the specified role.
     */
    public function edit(Role $role)
    {
        return view('admin.roles.edit', compact('role'));
    }

    /**
     * Update the specified role in storage.
     */
    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('roles')->ignore($role->id)],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        $role->update($validated);

        return redirect()->route('admin.roles.index')
                        ->with('success', 'Role updated successfully.');
    }

    /**
     * Remove the specified role from storage.
     */
    public function destroy(Role $role)
    {
        // Prevent deletion of core roles
        $coreRoles = ['admin', 'editor', 'teacher', 'customer'];
        if (in_array($role->slug, $coreRoles)) {
            return redirect()->route('admin.roles.index')
                            ->with('error', 'Cannot delete core system roles.');
        }

        // Check if role has users assigned
        if ($role->users()->exists()) {
            return redirect()->route('admin.roles.index')
                            ->with('error', 'Cannot delete role that has users assigned to it.');
        }

        $role->delete();

        return redirect()->route('admin.roles.index')
                        ->with('success', 'Role deleted successfully.');
    }
}
