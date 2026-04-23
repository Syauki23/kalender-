<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\WhatsappContact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WhatsappContactController extends Controller
{
    private function requireGlobal()
    {
        if (!Auth::check() || !Auth::user()->canManageGlobal()) {
            abort(403, 'Akses ditolak.');
        }
    }

    public function index()
    {
        $this->requireGlobal();
        $departments = Department::orderBy('name')->get();
        return view('whatsapp-contacts.index', compact('departments'));
    }

    public function getContacts(Department $department)
    {
        $this->requireGlobal();
        return response()->json($department->whatsappContacts);
    }

    public function store(Request $request)
    {
        $this->requireGlobal();
        $validated = $request->validate([
            'department_id' => 'required|exists:departments,id',
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
        ]);

        $contact = WhatsappContact::create($validated);

        return response()->json(['success' => true, 'contact' => $contact]);
    }

    public function update(Request $request, WhatsappContact $contact)
    {
        $this->requireGlobal();
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
        ]);

        $contact->update($validated);

        return response()->json(['success' => true]);
    }

    public function destroy(WhatsappContact $contact)
    {
        $this->requireGlobal();
        $contact->delete();

        return response()->json(['success' => true]);
    }

    // Untuk pilihan di modal event
    public function getAllContacts(Request $request)
    {
        if (!Auth::check()) return response()->json([], 401);
        
        $user = Auth::user();
        $query = WhatsappContact::query();

        // Jika bukan admin/editor, hanya lihat kontak departemen sendiri
        if (!$user->canManageGlobal()) {
            $query->where('department_id', $user->department_id);
        } else {
            // Admin/Editor bisa filter lewat ?department_id=...
            if ($request->has('department_id')) {
                $query->where('department_id', $request->department_id);
            }
        }

        return response()->json($query->orderBy('name')->get());
    }
}
