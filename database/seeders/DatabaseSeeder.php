<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Event;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ─── Buat Admin ────────────────────────────────────────────────────────
        $admin = User::create([
            'name'     => 'Administrator',
            'email'    => 'admin@kalender.com',
            'password' => Hash::make('admin123'),
            'role'     => 'admin',
        ]);

        // ─── Buat Editor ───────────────────────────────────────────────────────
        $editor = User::create([
            'name'     => 'Budi Editor',
            'email'    => 'editor@kalender.com',
            'password' => Hash::make('editor123'),
            'role'     => 'editor',
        ]);

        // ─── Buat Sample Events ────────────────────────────────────────────────
        $sampleEvents = [
            [
                'title'       => 'Rapat Tim Bulanan',
                'description' => 'Rapat evaluasi dan perencanaan tim divisi IT.',
                'date'        => now()->startOfMonth()->addDays(2)->format('Y-m-d'),
                'start_time'  => '09:00',
                'end_time'    => '11:00',
                'location'    => 'Ruang Rapat A',
                'color'       => 'blue',
                'created_by'  => $admin->id,
            ],
            [
                'title'       => 'Workshop Desain UI/UX',
                'description' => 'Workshop peningkatan skill desain antarmuka.',
                'date'        => now()->startOfMonth()->addDays(5)->format('Y-m-d'),
                'start_time'  => '13:00',
                'end_time'    => '17:00',
                'location'    => 'Aula Utama',
                'color'       => 'green',
                'created_by'  => $editor->id,
            ],
            [
                'title'       => 'Deadline Laporan Q1',
                'description' => 'Pengumpulan laporan keuangan kuartal pertama.',
                'date'        => now()->startOfMonth()->addDays(9)->format('Y-m-d'),
                'start_time'  => null,
                'end_time'    => null,
                'location'    => 'Online / Email',
                'color'       => 'red',
                'created_by'  => $admin->id,
            ],
            [
                'title'       => 'Presentasi Produk Baru',
                'description' => 'Demo peluncuran fitur terbaru kepada klien.',
                'date'        => now()->startOfMonth()->addDays(14)->format('Y-m-d'),
                'start_time'  => '10:00',
                'end_time'    => '12:00',
                'location'    => 'Zoom Meeting',
                'color'       => 'orange',
                'created_by'  => $editor->id,
            ],
            [
                'title'       => 'Pelatihan Keamanan Siber',
                'description' => 'Training cyber security untuk seluruh karyawan.',
                'date'        => now()->startOfMonth()->addDays(18)->format('Y-m-d'),
                'start_time'  => '08:00',
                'end_time'    => '16:00',
                'location'    => 'Ruang Training B',
                'color'       => 'blue',
                'created_by'  => $admin->id,
            ],
            [
                'title'       => 'Team Building Outing',
                'description' => 'Kegiatan team building outdoor seluruh divisi.',
                'date'        => now()->startOfMonth()->addDays(22)->format('Y-m-d'),
                'start_time'  => '07:00',
                'end_time'    => '18:00',
                'location'    => 'Puncak, Bogor',
                'color'       => 'green',
                'created_by'  => $admin->id,
            ],
        ];

        foreach ($sampleEvents as $event) {
            Event::create($event);
        }
    }
}
