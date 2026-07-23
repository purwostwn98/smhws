<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class JabatanSeeder extends Seeder
{
    public function run(): void
    {
        helper('sihrd');

        $result = sihrd_get_jabatan();

        if (empty($result['rows'])) {
            echo 'Tidak ada data jabatan dari API.' . PHP_EOL;
            return;
        }

        $now  = date('Y-m-d H:i:s');
        $rows = [];

        foreach ($result['rows'] as $row) {
            $rows[] = [
                'kode_jabatan'    => $row['kode_jabatan']    ?? null,
                'nama'            => $row['nama']            ?? null,
                'unit'            => $row['unit']            ?? null,
                'kode_lembaga'    => $row['kode_lembaga']    ?? null,
                'singkatan_lmbg'  => $row['singkatan_lmbg']  ?? null,
                'jenis_lembaga_id'=> $row['jenis_lembaga_id'] ?? null,
                'eselon'          => $row['eselon']          ?? null,
                'sks'             => $row['sks']             ?? null,
                'penjabat'        => $row['penjabat']        ?? null,
                'uniid_penjabat'  => isset($row['uniid_penjabat'])
                    ? strtolower($row['uniid_penjabat'])
                    : null,
                'ext'             => $row['ext']             ?? 0,
                'synced_at'       => $now,
            ];
        }

        // Truncate dulu agar data selalu fresh dari API
        $this->db->table('jabatans')->truncate();

        // Insert batch per 200 agar tidak timeout
        foreach (array_chunk($rows, 200) as $chunk) {
            $this->db->table('jabatans')->insertBatch($chunk);
        }

        echo 'Jabatan berhasil di-sync: ' . count($rows) . ' data.' . PHP_EOL;
    }
}
