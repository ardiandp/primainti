<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Role::class);

        return view('admin.roles.index', [
            'roles' => Role::with('permissions')->orderBy('name')->paginate(10),
        ]);
    }

    public function create()
    {
        $this->authorize('create', Role::class);

        return view('admin.roles.create', [
            'permissions' => Permission::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Role::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name'],
            'description' => ['nullable', 'string', 'max:255'],
            'permissions' => ['array'],
            'permissions.*' => ['exists:permissions,id'],
        ]);

        $role = Role::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        $role->syncPermissions(Permission::whereIn('id', $validated['permissions'] ?? [])->get());

        AuditLogger::log('role.create', $role, null, [
            'name' => $role->name,
            'description' => $role->description,
            'permissions' => $role->permissions->pluck('name')->all(),
        ]);

        return redirect()->route('admin.roles.index')->with('status', 'Role dibuat.');
    }

    public function edit(Role $role)
    {
        $this->authorize('update', $role);

        return view('admin.roles.edit', [
            'role' => $role->load('permissions'),
            'permissions' => Permission::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Role $role)
    {
        $this->authorize('update', $role);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('roles', 'name')->ignore($role->id)],
            'description' => ['nullable', 'string', 'max:255'],
            'permissions' => ['array'],
            'permissions.*' => ['exists:permissions,id'],
        ]);

        $old = [
            'name' => $role->name,
            'description' => $role->description,
            'permissions' => $role->permissions->pluck('name')->all(),
        ];

        $role->name = $validated['name'];
        $role->description = $validated['description'] ?? null;
        $role->save();

        $role->syncPermissions(Permission::whereIn('id', $validated['permissions'] ?? [])->get());

        AuditLogger::log('role.update', $role, $old, [
            'name' => $role->name,
            'description' => $role->description,
            'permissions' => $role->permissions->pluck('name')->all(),
        ]);

        return redirect()->route('admin.roles.index')->with('status', 'Role diperbarui.');
    }

    public function destroy(Role $role)
    {
        $this->authorize('delete', $role);

        if ($role->name === 'super_admin') {
            return back()->withErrors(['status' => 'Role super_admin tidak bisa dihapus.']);
        }

        if ($role->users()->exists()) {
            return back()->withErrors(['status' => 'Role masih dipakai user. Lepas dulu dari semua user.']);
        }

        $old = ['name' => $role->name];

        $role->permissions()->detach();
        $role->delete();

        AuditLogger::log('role.delete', $role, $old, null);

        return back()->with('status', 'Role dihapus.');
    }
}