<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EventController extends Controller
{
    // Ambil semua event (untuk FullCalendar via JSON)
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Event::with('creator:id,name');

        // Visibility Logic
        if (!$user) {
            // Guest hanya bisa lihat event publik (department_id IS NULL)
            $query->whereNull('department_id');
        } elseif (!$user->canManageGlobal()) {
            // Role 'user' (Akun Dept) hanya bisa lihat event publik ATAU event milik departemennya
            $query->where(function ($q) use ($user) {
                $q->whereNull('department_id')
                    ->orWhere('department_id', $user->department_id);
            });
        }
        // Admin & Editor (canManageGlobal) bisa lihat semuanya

        $events = $query->get()->map(function ($event) {
            return [
                'id' => $event->id,
                'title' => $event->title,
                'start' => $event->date->format('Y-m-d') . ($event->start_time ? 'T' . $event->start_time : ''),
                'end' => $event->date->format('Y-m-d') . ($event->end_time ? 'T' . $event->end_time : ''),
                'color' => $this->resolveColor($event->color),
                'extendedProps' => [
                    'description' => $event->description,
                    'location' => $event->location,
                    'color_label' => $event->color,
                    'start_time' => $event->start_time,
                    'end_time' => $event->end_time,
                    'creator' => $event->creator?->name,
                    'created_by' => $event->created_by,
                    'department_id' => $event->department_id,
                    'wa_schedule_time' => $event->wa_schedule_time ? $event->wa_schedule_time->format('Y-m-d\TH:i') : null,
                ],
            ];
        });

        return response()->json($events);
    }

    // Simpan event baru
    public function store(Request $request)
    {
        $this->requireAuth();
        $user = Auth::user();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'date' => 'required|date',
            'start_time' => 'nullable|regex:/^[0-9]{2}:[0-9]{2}(:[0-9]{2})?$/',
            'end_time' => 'nullable|regex:/^[0-9]{2}:[0-9]{2}(:[0-9]{2})?$/',
            'location' => 'nullable|string|max:255',
            'color' => 'required|in:blue,green,orange,red,yellow',
            'is_private' => 'nullable|boolean',
            'department_id' => 'nullable|exists:departments,id',
            'wa_schedule_time' => 'nullable|date_format:Y-m-d\TH:i',
            'whatsapp_contact_ids' => 'nullable|array',
            'whatsapp_contact_ids.*' => 'exists:whatsapp_contacts,id',
        ]);

        $isPrivate = $request->input('is_private') == 1 || $request->input('is_private') === true;
        
        $deptId = null;
        if ($isPrivate) {
            if ($user->canManageGlobal() && $request->has('department_id')) {
                $deptId = $request->input('department_id');
            } else {
                $deptId = $user->department_id;
            }
        }

        $event = Event::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'date' => $validated['date'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'location' => $validated['location'],
            'color' => $validated['color'],
            'department_id' => $deptId,
            'created_by' => Auth::id(),
            'wa_schedule_time' => $request->input('wa_schedule_time'),
        ]);

        if ($request->has('whatsapp_contact_ids')) {
            $event->whatsappContacts()->sync($request->input('whatsapp_contact_ids'));
        }

        $this->scheduleFonnteNotification($event);

        return response()->json(['success' => true, 'event' => $event], 201);
    }

    // Detail satu event
    public function show(Event $event)
    {
        return response()->json($event->load(['creator:id,name', 'whatsappContacts']));
    }

    // Update event
    public function update(Request $request, Event $event)
    {
        $this->requireAuth();
        $user = Auth::user();

        if (!$user->canManageGlobal() && $event->created_by !== $user->id) {
            return response()->json(['error' => 'Tidak diizinkan.'], 403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'date' => 'required|date',
            'start_time' => 'nullable|regex:/^[0-9]{2}:[0-9]{2}(:[0-9]{2})?$/',
            'end_time' => 'nullable|regex:/^[0-9]{2}:[0-9]{2}(:[0-9]{2})?$/',
            'location' => 'nullable|string|max:255',
            'color' => 'required|in:blue,green,orange,red,yellow',
            'is_private' => 'nullable|boolean',
            'department_id' => 'nullable|exists:departments,id',
            'wa_schedule_time' => 'nullable|date_format:Y-m-d\TH:i',
            'whatsapp_contact_ids' => 'nullable|array',
            'whatsapp_contact_ids.*' => 'exists:whatsapp_contacts,id',
        ]);

        $isPrivate = $request->input('is_private') == 1 || $request->input('is_private') === true;
        
        $deptId = null;
        if ($isPrivate) {
            if ($user->canManageGlobal() && $request->has('department_id')) {
                $deptId = $request->input('department_id');
            } else {
                $deptId = $user->department_id;
            }
        }

        $event->update([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'date' => $validated['date'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'location' => $validated['location'],
            'color' => $validated['color'],
            'department_id' => $deptId,
            'wa_schedule_time' => $request->input('wa_schedule_time'),
        ]);

        if ($request->has('whatsapp_contact_ids')) {
            $event->whatsappContacts()->sync($request->input('whatsapp_contact_ids'));
        }

        $this->scheduleFonnteNotification($event);

        return response()->json(['success' => true, 'event' => $event]);
    }

    // Hapus event
    public function destroy(Event $event)
    {
        $this->requireAuth();
        $user = Auth::user();

        if (!$user->canManageGlobal() && $event->created_by !== $user->id) {
            return response()->json(['error' => 'Tidak diizinkan.'], 403);
        }

        $event->delete();
        return response()->json(['success' => true]);
    }

    // Helper resolve warna HEX dari label
    private function resolveColor(string $color): string
    {
        return match ($color) {
            'green' => '#10b981',
            'orange' => '#f59e0b',
            'red' => '#ef4444',
            'yellow' => '#eab308',
            default => '#3b82f6', // blue
        };
    }

    private function requireAuth()
    {
        if (!Auth::check()) {
            abort(401, 'Unauthorized');
        }
    }

    private function scheduleFonnteNotification(Event $event)
    {
        if (!$event->wa_schedule_time) return;

        $token = env('FONNTE_TOKEN');
        if (!$token) {
            \Illuminate\Support\Facades\Log::warning("Fonnte Token tidak ditemukan di .env");
            return;
        }

        $deptId = $event->department_id ?: $event->creator->department_id;
        
        // Ambil kontak yang dipilih secara spesifik untuk event ini
        $contacts = $event->whatsappContacts;

        // Jika tidak ada kontak yang dipilih secara spesifik, kembalikan (fallback ke departemen dihapus sesuai permintaan user untuk "memilih siapa saja")
        if ($contacts->isEmpty()) {
            \Illuminate\Support\Facades\Log::info("Event ID {$event->id} tidak memiliki kontak WhatsApp terpilih.");
            return;
        }

        $phones = $contacts->pluck('phone')->toArray();
        $target = implode(',', $phones);

        $message = "Halo, Pengingat Acara:\n\n*{$event->title}*\nTanggal: " . $event->date->format('d M Y') . "\nWaktu: " . ($event->start_time ? substr($event->start_time, 0, 5) : 'TBA') . "\nLokasi: " . ($event->location ?: 'TBA') . "\n\nTerima kasih.";

        $timestamp = $event->wa_schedule_time->timestamp;

        try {
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'Authorization' => $token,
            ])->post('https://api.fonnte.com/send', [
                'target'  => $target,
                'message' => $message,
                'schedule' => $timestamp
            ]);

            \Illuminate\Support\Facades\Log::info("Fonnte API Response untuk Event {$event->id}: " . $response->body());
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Fonnte Schedule Error: " . $e->getMessage());
        }
    }
}
