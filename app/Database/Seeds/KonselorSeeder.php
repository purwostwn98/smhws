<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class KonselorSeeder extends Seeder
{
    public function run(): void
    {
        $now = date('Y-m-d H:i:s');

        // Ambil user_id konselor dari tabel users
        $userSiti   = $this->db->table('users')->where('email', 's.rahayu@smhws.ums.ac.id')->get()->getRowArray();
        $userAhmad  = $this->db->table('users')->where('email', 'a.hidayat@smhws.ums.ac.id')->get()->getRowArray();

        if (! $userSiti || ! $userAhmad) {
            echo "Jalankan UserSeeder terlebih dahulu.\n";
            return;
        }

        // ── Profil Konselor ─────────────────────────────────────────────────
        $konselor = [
            [
                'user_id'            => $userSiti['id'],
                'nip'                => '197801012005012001',
                'gelar_depan'        => 'Dr.',
                'gelar_belakang'     => 'M.Psi., Psikolog',
                'spesialisasi'       => json_encode(['Depresi', 'Kecemasan', 'Stres Akademik', 'Trauma']),
                'bio'                => 'Psikolog klinis berpengalaman dengan fokus pada kesehatan mental mahasiswa. Telah menangani lebih dari 500 kasus konseling selama 10 tahun berkarir di UMS.',
                'foto'               => null,
                'no_str'             => 'STR.1234/PSI/2020',
                'tahun_pengalaman'   => 10,
                'max_pasien_per_hari'=> 8,
                'is_available'       => 1,
                'rating'             => '4.90',
                'total_sesi'         => 520,
                'created_at'         => $now,
                'updated_at'         => $now,
            ],
            [
                'user_id'            => $userAhmad['id'],
                'nip'                => '198505152010011003',
                'gelar_depan'        => null,
                'gelar_belakang'     => 'S.Psi., M.Psi.',
                'spesialisasi'       => json_encode(['Hubungan Interpersonal', 'Manajemen Emosi', 'Pengembangan Diri']),
                'bio'                => 'Konselor psikologi lulusan UGM dengan spesialisasi hubungan interpersonal dan pengembangan diri pada mahasiswa. Aktif sebagai fasilitator workshop psikoeduasi.',
                'foto'               => null,
                'no_str'             => 'STR.5678/PSI/2021',
                'tahun_pengalaman'   => 6,
                'max_pasien_per_hari'=> 6,
                'is_available'       => 1,
                'rating'             => '4.75',
                'total_sesi'         => 210,
                'created_at'         => $now,
                'updated_at'         => $now,
            ],
        ];

        $this->db->table('konselor')->insertBatch($konselor);

        // ── Jadwal Ketersediaan ─────────────────────────────────────────────
        $konselorSiti  = $this->db->table('konselor')->where('user_id', $userSiti['id'])->get()->getRowArray();
        $konselorAhmad = $this->db->table('konselor')->where('user_id', $userAhmad['id'])->get()->getRowArray();

        $jadwal = [
            // Dr. Siti: Senin–Jumat, 2 shift
            ['konselor_id' => $konselorSiti['id'],  'hari' => 'senin',  'jam_mulai' => '08:00', 'jam_selesai' => '12:00', 'kuota' => 4, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['konselor_id' => $konselorSiti['id'],  'hari' => 'senin',  'jam_mulai' => '13:00', 'jam_selesai' => '16:00', 'kuota' => 3, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['konselor_id' => $konselorSiti['id'],  'hari' => 'selasa', 'jam_mulai' => '08:00', 'jam_selesai' => '12:00', 'kuota' => 4, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['konselor_id' => $konselorSiti['id'],  'hari' => 'rabu',   'jam_mulai' => '08:00', 'jam_selesai' => '12:00', 'kuota' => 4, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['konselor_id' => $konselorSiti['id'],  'hari' => 'kamis',  'jam_mulai' => '13:00', 'jam_selesai' => '16:00', 'kuota' => 3, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['konselor_id' => $konselorSiti['id'],  'hari' => 'jumat',  'jam_mulai' => '08:00', 'jam_selesai' => '11:00', 'kuota' => 3, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],

            // Ahmad: Senin, Rabu, Jumat
            ['konselor_id' => $konselorAhmad['id'], 'hari' => 'senin',  'jam_mulai' => '09:00', 'jam_selesai' => '12:00', 'kuota' => 3, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['konselor_id' => $konselorAhmad['id'], 'hari' => 'rabu',   'jam_mulai' => '09:00', 'jam_selesai' => '12:00', 'kuota' => 3, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['konselor_id' => $konselorAhmad['id'], 'hari' => 'rabu',   'jam_mulai' => '13:00', 'jam_selesai' => '15:00', 'kuota' => 2, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['konselor_id' => $konselorAhmad['id'], 'hari' => 'jumat',  'jam_mulai' => '09:00', 'jam_selesai' => '12:00', 'kuota' => 3, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
        ];

        $this->db->table('konselor_jadwal')->insertBatch($jadwal);

        echo "KonselorSeeder: 2 konselor + 10 jadwal berhasil disimpan.\n";
    }
}
