<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class InstansiRujukanSeeder extends Seeder
{
    public function run(): void
    {
        $now  = date('Y-m-d H:i:s');
        $data = [
            [
                'nama_instansi' => 'Biro Konsultasi dan Pemeriksaan Psikologis UMS',
                'singkatan'     => 'BKPP UMS',
                'alamat'        => 'Jl. A. Yani, Pabelan, Kartasura, Sukoharjo, Jawa Tengah 57169, Indonesia',
                'created_at'    => $now,
                'updated_at'    => $now,
            ],
            [
                'nama_instansi' => 'Muhammadiyah Medical Center UMS',
                'singkatan'     => 'MMC UMS',
                'alamat'        => 'Jl. Garuda Mas No.6, Mendungan, Pabelan, Kec. Kartasura, Kabupaten Sukoharjo, Jawa Tengah 57169',
                'created_at'    => $now,
                'updated_at'    => $now,
            ],
            [
                'nama_instansi' => 'Konseling Kesehatan Mental - Fakultas Kedokteran UMS',
                'singkatan'     => 'Psikiatri FK UMS',
                'alamat'        => 'Kampus IV UMS Gonilan Kartasura, Gonilan, Sukoharjo, Kabupaten Sukoharjo, Jawa Tengah 57169',
                'created_at'    => $now,
                'updated_at'    => $now,
            ],
        ];

        $this->db->table('instansi_rujukan')->insertBatch($data);
    }
}
