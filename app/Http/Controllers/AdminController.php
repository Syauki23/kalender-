<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    private function requireAdmin()
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403, 'Akses ditolak.');
        }
    }

    // Daftar semua user (editor)
    public function users()
    {
        $this->requireAdmin();
        $users = User::where('role', 'editor')->orderBy('name')->get();
        return response()->json($users->map(fn($u) => [
            'id'    => $u->id,
            'name'  => $u->name,
            'email' => $u->email,
            'role'  => $u->role,
        ]));
    }

    // Tambah editor baru
    public function addUser(Request $request)
    {
        $this->requireAdmin();
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role'     => 'editor',
        ]);

        return response()->json(['success' => true, 'user' => [
            'id'    => $user->id,
            'name'  => $user->name,
            'email' => $user->email,
            'role'  => $user->role,
        ]], 201);
    }

    // Hapus editor
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
