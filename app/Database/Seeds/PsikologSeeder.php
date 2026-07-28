<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PsikologSeeder extends Seeder
{
    public function run(): void
    {
        $now = date('Y-m-d H:i:s');

        $psikolog = [
            [
                'email'           => 'm.rahma@smhws.ums.ac.id',
                'name'            => 'Marida Rahma Salimah',
                'nim_nip'         => null,
                'uniid'           => null,
                'gelar_depan'     => null,
                'gelar_belakang'  => 'S.Psi., M.Psi., Psikolog',
                'spesialisasi'    => ['Klinis Dewasa', 'Masalah Emosi', 'Hubungan Interpersonal', 'Intervensi Krisis Psikologis', 'Penyesuaian Diri'],
                'bio'             => 'Menghadirkan ruang yang aman dan nyaman untuk bercerita, memahami diri, dan bertumbuh.',
                'no_str'          => '20210780-2023-02-1971',
                'tahun_pengalaman'=> 5,
            ],
            [
                'email'           => 's.wahyu@smhws.ums.ac.id',
                'name'            => 'Septian Wahyu Rahmanto',
                'nim_nip'         => '2199',
                'uniid'           => 'swr620',
                'gelar_depan'     => null,
                'gelar_belakang'  => 'S.Psi., M.Psi., Psikolog',
                'spesialisasi'    => ['Trauma', 'Kecemasan', 'Depresi', 'Adiksi', 'Pengembangan Diri', 'Pra-Nikah', 'Relasi Pasangan', 'Pengasuhan'],
                'bio'             => null,
                'no_str'          => '20200289-2025-194-0065',
                'tahun_pengalaman'=> 4,
            ],
            [
                'email'           => 'i.jeihan@smhws.ums.ac.id',
                'name'            => 'Inezzakya Jeihan',
                'nim_nip'         => null,
                'uniid'           => null,
                'gelar_depan'     => null,
                'gelar_belakang'  => 'S.Psi., M.Psi., Psikolog',
                'spesialisasi'    => ['Klinis Remaja', 'Klinis Dewasa'],
                'bio'             => null,
                'no_str'          => '20241706-2025-01-0772',
                'tahun_pengalaman'=> 1,
            ],
            [
                'email'           => 'm.shobabiya@smhws.ums.ac.id',
                'name'            => 'Mahasri Shobabiya',
                'nim_nip'         => '600.2436',
                'uniid'           => null,
                'gelar_depan'     => null,
                'gelar_belakang'  => 'S.Pd.I., S.Psi., M.Psi., Psikolog',
                'spesialisasi'    => ['Kesehatan Mental', 'Relasi Interpersonal', 'Permasalahan Akademik', 'Pengembangan Diri'],
                'bio'             => null,
                'no_str'          => '20120165-2023-02-1121',
                'tahun_pengalaman'=> 7,
            ],
            [
                'email'           => 'partini@smhws.ums.ac.id',
                'name'            => 'Partini',
                'nim_nip'         => '594',
                'uniid'           => 'par289',
                'gelar_depan'     => 'Dra.',
                'gelar_belakang'  => 'M.Si., Psikolog',
                'spesialisasi'    => ['Promotif', 'Preventif', 'Kuratif', 'Berbasis Religius-Spiritual'],
                'bio'             => 'Setiap diri buat ruang dan waktu untuk bertumbuh. Setiap relasi — baik secara individual, kelompok, maupun lingkungan eksternal — adalah mitra untuk bertumbuh mencapai kondisi damai, nyaman, dan tenang berbasis religius-spiritual.',
                'no_str'          => '0451-12-1-2',
                'tahun_pengalaman'=> 33,
            ],
            [
                'email'           => 'd.setyaningrum@smhws.ums.ac.id',
                'name'            => 'Dewi Setyaningrum',
                'nim_nip'         => '500.2334',
                'uniid'           => 'ds672',
                'gelar_depan'     => null,
                'gelar_belakang'  => 'S.Psi., M.Psi., Psikolog',
                'spesialisasi'    => ['Klinis Remaja', 'Klinis Dewasa', 'Kecemasan', 'Depresi', 'Mental Health Issues'],
                'bio'             => 'Percaya bahwa setiap manusia memiliki nilai, kekuatan, dan harapan dalam dirinya. Setiap proses kehidupan, seberat apa pun, menyimpan kesempatan untuk bertumbuh, menguatkan hati, dan menemukan makna menuju versi diri yang lebih utuh.',
                'no_str'          => '20201075-2023-02-1874',
                'tahun_pengalaman'=> 6,
            ],
            [
                'email'           => 'b.suseno@smhws.ums.ac.id',
                'name'            => 'Bayu Suseno',
                'nim_nip'         => '2233',
                'uniid'           => 'bs324',
                'gelar_depan'     => null,
                'gelar_belakang'  => 'S.Psi., M.Psi., Psikolog',
                'spesialisasi'    => ['Depresi', 'Kecemasan', 'Kecanduan'],
                'bio'             => 'Belajar menerima yang datang, berserah pada yang tak dapat dikendalikan, dan percaya bahwa setiap kesulitan adalah bagian yang tak terpisahkan dari perjalanan menuju kedewasaan.',
                'no_str'          => '20230240-2025-02-1226',
                'tahun_pengalaman'=> 4,
            ],
        ];

        $inserted = 0;
        foreach ($psikolog as $p) {
            // Skip jika email sudah ada
            if ($this->db->table('users')->where('email', $p['email'])->countAllResults() > 0) {
                echo "  Skip (sudah ada): {$p['name']}\n";
                continue;
            }

            // Insert ke users
            $this->db->table('users')->insert([
                'name'              => $p['name'],
                'email'             => $p['email'],
                'password'          => password_hash('Konselor@2026', PASSWORD_BCRYPT),
                'role'              => 'konselor',
                'uniid'             => $p['uniid'],
                'is_superadmin'     => 0,
                'is_admin_fakultas' => 0,
                'is_active'         => 1,
                'email_verified_at' => $now,
                'created_at'        => $now,
                'updated_at'        => $now,
            ]);
            $userId = $this->db->insertID();

            // Insert ke konselor
            $this->db->table('konselor')->insert([
                'user_id'             => $userId,
                'nip'                 => $p['nim_nip'],
                'uniid'               => $p['uniid'],
                'gelar_depan'         => $p['gelar_depan'],
                'gelar_belakang'      => $p['gelar_belakang'],
                'spesialisasi'        => json_encode($p['spesialisasi'], JSON_UNESCAPED_UNICODE),
                'bio'                 => $p['bio'],
                'no_str'              => $p['no_str'],
                'tahun_pengalaman'    => $p['tahun_pengalaman'],
                'max_pasien_per_hari' => 8,
                'is_available'        => 1,
                'rating'              => '0.00',
                'total_sesi'          => 0,
                'created_at'          => $now,
                'updated_at'          => $now,
            ]);

            echo "  Inserted: {$p['name']}\n";
            $inserted++;
        }

        echo "PsikologSeeder: {$inserted} psikolog berhasil ditambahkan.\n";
    }
}
