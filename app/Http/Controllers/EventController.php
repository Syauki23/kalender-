<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EventController extends Controller
{
    // Ambil semua event (untuk FullCalendar via JSON)
    public function index()
    {
        $events = Event::with('creator:id,name')->get()->map(function ($event) {
            return [
                'id'          => $event->id,
                'title'       => $event->title,
                'start'       => $event->date->format('Y-m-d') . ($event->start_time ? 'T' . $event->start_time : ''),
                'end'         => $event->date->format('Y-m-d') . ($event->end_time ? 'T' . $event->end_time : ''),
                'color'       => $this->resolveColor($event->color),
                'extendedProps' => [
                    'description' => $event->description,
                    'location'    => $event->location,
                    'color_label' => $event->color,
                    'start_time'  => $event->start_time,
                    'end_time'    => $event->end_time,
                    'creator'     => $event->creator?->name,
                    'created_by'  => $event->created_by,
                ],
            ];
        });

        return response()->json($events);
    }

    // Simpan event baru
    public function store(Request $request)
    {
        $this->requireAuth();

        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'date'        => 'required|date',
            'start_time'  => 'nullable|regex:/^[0-9]{2}:[0-9]{2}(:[0-9]{2})?$/',
            'end_time'    => 'nullable|regex:/^[0-9]{2}:[0-9]{2}(:[0-9]{2})?$/',
            'location'    => 'nullable|string|max:255',
            'color'       => 'required|in:blue,green,orange,red,yellow',
        ]);

        $event = Event::create([
            ...$validated,
            'created_by' => Auth::id(),
        ]);

        return response()->json(['success' => true, 'event' => $event], 201);
    }

    // Detail satu event
    public function show(Event $event)
    {
        return response()->json($event->load('creator:id,name'));
    }

    // Update event
    public function update(Request $request, Event $event)
    {
        $this->requireAuth();

        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'date'        => 'required|date',
            'start_time'  => 'nullable|regex:/^[0-9]{2}:[0-9]{2}(:[0-9]{2})?$/',
            'end_time'    => 'nullable|regex:/^[0-9]{2}:[0-9]{2}(:[0-9]{2})?$/',
            'location'    => 'nullable|string|max:255',
            'color'       => 'required|in:blue,green,orange,red,yellow',
        ]);

        $event->update($validated);

        return response()->json(['success' => true, 'event' => $event]);
    }

    // Hapus event (hanya Admin)
    public function destroy(Event $event)
    {
        $user = Auth::user();

        if (!$user || !$user->isAdmin()) {
            return response()->json(['error' => 'Tidak diizinkan.'], 403);
        }

        $event->delete();
        return response()->json(['success' => true]);
    }

    // Helper resolve warna HEX dari label
    private function resolveColor(string $color): string
    {
        return match ($color) {
            'green'  => '#10b981',
            'orange' => '#f59e0b',
            'red'    => '#ef4444',
            'yellow' => '#eab308',
            default  => '#3b82f6', // blue
        };
    }

    private function requireAuth()
    {
        if (!Auth::check()) {
            abort(401, 'Unauthorized');
        }
    }
}
