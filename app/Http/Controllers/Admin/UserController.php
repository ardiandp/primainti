<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', User::class);

        return view('admin.users.index', [
            'users' => User::with('roles')->orderBy('name')->paginate(10),
        ]);
    }

    public function create()
    {
        $this->authorize('create', User::class);

        return view('admin.users.create', [
            'roles' => Role::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', User::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'roles' => ['array'],
            'roles.*' => ['exists:roles,id'],
        ]);

        $roles = Role::whereIn('id', $validated['roles'] ?? [])->get();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'is_active' => true,
        ]);

        $user->syncRoles($roles);

        AuditLogger::log('user.create', $user, null, [
            'name' => $user->name,
            'email' => $user->email,
            'roles' => $user->roleNames()->all(),
        ]);

        return redirect()->route('admin.users.index')->with('status', 'User dibuat.');
    }

    public function edit(User $user)
    {
        $this->authorize('update', $user);

        return view('admin.users.edit', [
            'user' => $user->load('roles'),
            'roles' => Role::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'roles' => ['array'],
            'roles.*' => ['exists:roles,id'],
        ]);

        $old = [
            'name' => $user->name,
            'email' => $user->email,
            'roles' => $user->roleNames()->all(),
        ];

        $user->name = $validated['name'];
        $user->email = $validated['email'];

        if (! empty($validated['password'])) {
            $user->password = $validated['password'];
        }

        $user->save();

        $user->syncRoles(Role::whereIn('id', $validated['roles'] ?? [])->get());

        AuditLogger::log('user.update', $user, $old, [
            'name' => $user->name,
            'email' => $user->email,
            'roles' => $user->roleNames()->all(),
            'password_changed' => ! empty($validated['password']),
        ]);

        return redirect()->route('admin.users.index')->with('status', 'User diperbarui.');
    }

    public function deactivate(User $user)
    {
        $this->authorize('deactivate', $user);

        if ($user->is(Auth::user())) {
            return back()->withErrors(['status' => 'Tidak bisa menonaktifkan akun sendiri.']);
        }

        $user->is_active = false;
        $user->save();

        AuditLogger::log('user.deactivate', $user, ['is_active' => true], ['is_active' => false]);

        return back()->with('status', 'User dinonaktifkan.');
    }

    public function activate(User $user)
    {
        $this->authorize('activate', $user);

        $user->is_active = true;
        $user->save();

        AuditLogger::log('user.activate', $user, ['is_active' => false], ['is_active' => true]);

        return back()->with('status', 'User diaktifkan.');
    }

    public function resetAccess(User $user)
    {
        $this->authorize('resetAccess', $user);

        if ($user->is(Auth::user())) {
            return back()->withErrors(['status' => 'Tidak bisa mereset akses akun sendiri.']);
        }

        $old = ['roles' => $user->roleNames()->all()];

        $user->syncRoles([]);

        AuditLogger::log('user.reset_access', $user, $old, ['roles' => []]);

        return back()->with('status', 'Akses user direset (tanpa role).');
    }
}