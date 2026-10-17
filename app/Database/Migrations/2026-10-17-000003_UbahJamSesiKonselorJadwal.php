<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Sesi konseling jadi 50 menit: jadwal psikolog yang tersimpan dipindah ke jam sesi baru
 * supaya tetap cocok dengan slot s1–s5 di controller.
 */
class UbahJamSesiKonselorJadwal extends Migration
{
    // jam_mulai lama => [jam_mulai baru, jam_selesai baru]
    private array $lamaKeBaru = [
        '08:00:00' => ['08:00:00', '08:50:00'],
        '09:30:00' => ['09:20:00', '10:10:00'],
        '11:00:00' => ['10:40:00', '11:30:00'],
        '12:30:00' => ['12:30:00', '13:20:00'],
        '14:00:00' => ['13:50:00', '14:40:00'],
    ];

    private array $baruKeLama = [
        '08:00:00' => ['08:00:00', '09:00:00'],
        '09:20:00' => ['09:30:00', '10:30:00'],
        '10:40:00' => ['11:00:00', '12:00:00'],
        '12:30:00' => ['12:30:00', '13:30:00'],
        '13:50:00' => ['14:00:00', '15:00:00'],
    ];

    public function up(): void
    {
        $this->pindahkan($this->lamaKeBaru);
    }

    public function down(): void
    {
        $this->pindahkan($this->baruKeLama);
    }

    private function pindahkan(array $peta): void
    {
        foreach ($peta as $mulaiAsal => [$mulai, $selesai]) {
            $this->db->table('konselor_jadwal')
                ->where('jam_mulai', $mulaiAsal)
                ->update(['jam_mulai' => $mulai, 'jam_selesai' => $selesai]);
        }
    }
}
