<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    private function requireAdmin()
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403, 'Akses ditolak.');
        }
    }

    private function requireGlobal()
    {
        if (!Auth::check() || !Auth::user()->canManageGlobal()) {
            abort(403, 'Akses ditolak.');
        }
    }

    // ─── DEPARTMENTS ─────────────────────────────────────────────────────────
    
    public function departments()
    {
        $this->requireGlobal();
        return response()->json(Department::orderBy('name')->get());
    }

    public function addDepartment(Request $request)
    {
        $this->requireGlobal();
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:departments,name',
        ]);

        $dept = Department::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
        ]);

        return response()->json(['success' => true, 'department' => $dept], 201);
    }

    public function removeDepartment(Department $department)
    {
        $this->requireGlobal();
        if ($department->users()->count() > 0) {
            return response()->json(['error' => 'Tidak bisa menghapus: Masih ada akun yang terdaftar di departemen ini.'], 422);
        }
        $department->delete();
        return response()->json(['success' => true]);
    }

    public function updateDepartment(Request $request, Department $department)
    {
        $this->requireGlobal();
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:departments,name,' . $department->id,
        ]);

        $department->update([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
        ]);

        return response()->json(['success' => true]);
    }

    // ─── USERS ───────────────────────────────────────────────────────────────

    public function users()
    {
        $this->requireAdmin();
        $users = User::with('department')->whereIn('role', ['editor', 'user'])->orderBy('name')->get();
        return response()->json($users->map(fn($u) => [
            'id'    => $u->id,
            'name'  => $u->name,
            'username' => $u->username,
            'email' => $u->email,
            'role'  => $u->role,
            'department' => $u->department ? ['id' => $u->department->id, 'name' => $u->department->name] : null,
        ]));
    }

    public function addUser(Request $request)
    {
        $this->requireAdmin();
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'username'      => 'required|string|max:255|unique:users,username',
            'email'         => 'required|email|unique:users,email',
            'password'      => 'required|string',
            'role'          => 'required|in:editor,user',
            'department_id' => 'required|exists:departments,id',
        ]);

        $user = User::create([
            'name'          => $validated['name'],
            'username'      => $validated['username'],
            'email'         => $validated['email'],
            'password'      => Hash::make($validated['password']),
            'role'          => $validated['role'],
            'department_id' => $validated['department_id'],
        ]);

        return response()->json(['success' => true, 'user' => $user], 201);
    }

    public function updateUser(Request $request, User $user)
    {
        $this->requireAdmin();
        if ($user->isAdmin()) {
            return response()->json(['error' => 'Tidak bisa mengedit admin.'], 403);
        }

        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'username'      => 'required|string|max:255|unique:users,username,' . $user->id,
            'email'         => 'required|email|unique:users,email,' . $user->id,
            'password'      => 'nullable|string',
            'role'          => 'required|in:editor,user',
            'department_id' => 'required|exists:departments,id',
        ]);

        $data = [
            'name'          => $validated['name'],
            'username'      => $validated['username'],
            'email'         => $validated['email'],
            'role'          => $validated['role'],
            'department_id' => $validated['department_id'],
        ];

        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $user->update($data);

        return response()->json(['success' => true]);
    }

    public function removeUser(User $user)
    {
        $this->requireAdmin();
        if ($user->isAdmin()) {
            return response()->json(['error' => 'Tidak bisa menghapus admin.'], 403);
        }
        $user->delete();
        return response()->json(['success' => true]);
    }
}
