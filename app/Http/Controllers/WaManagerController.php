<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventReminder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WaManagerController extends Controller
{
    public function index()
    {
        if (!Auth::check() || !Auth::user()->canManageGlobal()) {
            abort(403, 'Akses ditolak.');
        }

        return view('wa-manager');
    }

    // Mengambil daftar event yang akan datang
    public function getEvents(Request $request)
    {
        if (!Auth::check() || !Auth::user()->canManageGlobal()) {
            return response()->json(['error' => 'Akses ditolak.'], 403);
        }

        $events = Event::with('creator:id,name')
            ->whereDate('date', '>=', now()->toDateString())
            ->orderBy('date', 'asc')
            ->orderBy('start_time', 'asc')
            ->get();

        return response()->json($events);
    }

    // Mengambil detail event termasuk kontak dan reminders
    public function getEventDetails($id)
    {
        if (!Auth::check() || !Auth::user()->canManageGlobal()) {
            return response()->json(['error' => 'Akses ditolak.'], 403);
        }

        $event = Event::with(['whatsappContacts', 'reminders'])->findOrFail($id);
        
        return response()->json($event);
    }

    // Menyimpan pengaturan reminder baru
    public function saveReminders(Request $request, $id)
    {
        if (!Auth::check() || !Auth::user()->canManageGlobal()) {
            return response()->json(['error' => 'Akses ditolak.'], 403);
        }

        $event = Event::findOrFail($id);

        $validated = $request->validate([
            'whatsapp_contact_ids' => 'nullable|array',
            'whatsapp_contact_ids.*' => 'exists:whatsapp_contacts,id',
            'reminders' => 'nullable|array',
            'reminders.*' => 'date_format:Y-m-d\TH:i',
        ]);

        // 1. Sinkronisasi kontak
        if (isset($validated['whatsapp_contact_ids'])) {
            $event->whatsappContacts()->sync($validated['whatsapp_contact_ids']);
        } else {
            $event->whatsappContacts()->detach();
        }

        // 2. Refresh kontak untuk dikirim ke Fonnte
        $contacts = $event->whatsappContacts()->get();
        if ($contacts->isEmpty() && !empty($validated['reminders'])) {
            return response()->json(['error' => 'Anda harus memilih minimal satu kontak penerima.'], 400);
        }

        // 3. Simpan reminders baru
        // Kita hapus yang lama dan buat yang baru agar simpel, kecuali ingin mempertahankan log (tapi untuk prototype ini kita recreate saja)
        $event->reminders()->delete();

        $token = env('FONNTE_TOKEN');
        if (!$token) {
            Log::warning("Fonnte Token tidak ditemukan di .env");
        }

        $phones = $contacts->pluck('phone')->toArray();
        $target = implode(',', $phones);

        $message = "*PENGINGAT KEGIATAN*\n\nYth. Bapak/Ibu,\n\nBerikut adalah pengingat untuk agenda mendatang:\n\n📌 *Agenda:* {$event->title}\n📅 *Tanggal:* " . $event->date->format('d M Y') . "\n⏰ *Waktu:* " . ($event->start_time ? substr($event->start_time, 0, 5) : 'TBA') . " WIB\n📍 *Lokasi:* " . ($event->location ?: 'TBA') . "\n\nMohon kehadiran dan kerja samanya. Terima kasih.\n\n_Sistem Notifikasi Kalender_";

        if (!empty($validated['reminders'])) {
            foreach ($validated['reminders'] as $timeStr) {
                $scheduleTime = \Carbon\Carbon::parse($timeStr);
                
                // Simpan ke DB
                $reminder = $event->reminders()->create([
                    'schedule_time' => $scheduleTime,
                    'is_sent' => false
                ]);

                // Kirim ke Fonnte jika token ada
                if ($token) {
                    try {
                        $response = Http::withHeaders([
                            'Authorization' => $token,
                        ])->post('https://api.fonnte.com/send', [
                            'target'  => $target,
                            'message' => $message,
                            'schedule' => $scheduleTime->timestamp
                        ]);

                        Log::info("Fonnte API Response untuk Event {$event->id} Jadwal {$timeStr}: " . $response->body());
                    } catch (\Exception $e) {
                        Log::error("Fonnte Schedule Error: " . $e->getMessage());
                    }
                }
            }
        }

        return response()->json(['success' => true, 'message' => 'Jadwal pengingat berhasil disimpan.']);
    }
}
