<?php

namespace App\Models;

use CodeIgniter\Model;

class SkriningPublikModel extends Model
{
    protected $table      = 'skrining_publik';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'jenis_kelamin', 'semester', 'id_lembaga', 'nama_prodi', 'nama_fakultas',
        'skor_depresi_raw', 'skor_depresi', 'kategori_depresi',
        'skor_anxiety_raw', 'skor_anxiety', 'kategori_anxiety',
        'skor_stress_raw',  'skor_stress',  'kategori_stress',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Hitung skor & kategori DASS-21, simpan skrining beserta jawabannya.
     *
     * @param array $data    jenis_kelamin, semester, id_lembaga, nama_prodi, nama_fakultas
     * @param array $jawaban [item_id => nilai (0-3)]
     * @return array hasil skor yang disimpan (untuk ditampilkan)
     */
    public function simpanSkrining(array $data, array $jawaban): array
    {
        $itemMap = array_column((new DassItemModel())->findAll(), null, 'id');

        $raw = ['D' => 0, 'A' => 0, 'S' => 0];
        foreach ($jawaban as $itemId => $nilai) {
            if (isset($itemMap[$itemId])) {
                $raw[$itemMap[$itemId]['subscale']] += (int) $nilai;
            }
        }

        foreach (['D' => 'depresi', 'A' => 'anxiety', 'S' => 'stress'] as $kode => $nama) {
            $skor = $raw[$kode] * 2; // skor akhir = raw × 2
            $data["skor_{$nama}_raw"] = $raw[$kode];
            $data["skor_{$nama}"]     = $skor;
            $data["kategori_{$nama}"] = DassAssessmentModel::hitungKategori($kode, $skor);
        }

        $db = $this->db;
        $db->transStart();

        $id  = $this->insert($data, true);
        $now = [];
        foreach ($jawaban as $itemId => $nilai) {
            if (! isset($itemMap[$itemId])) continue;
            $now[] = [
                'skrining_id' => $id,
                'item_id'     => (int) $itemId,
                'nomor_item'  => (int) $itemMap[$itemId]['nomor'],
                'subscale'    => $itemMap[$itemId]['subscale'],
                'jawaban'     => (int) $nilai,
            ];
        }
        $db->table('skrining_publik_jawaban')->insertBatch($now);

        $db->transComplete();

        if (! $db->transStatus()) {
            throw new \RuntimeException('Gagal menyimpan hasil skrining.');
        }

        return $data;
    }
}
