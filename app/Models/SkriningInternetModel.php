<?php

namespace App\Models;

use App\Libraries\PolaInternet;
use CodeIgniter\Model;

/** Hasil skrining Pola Penggunaan Internet (tabel skrining_internet + skrining_internet_jawaban). */
class SkriningInternetModel extends Model
{
    protected $table      = 'skrining_internet';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'jenis_kelamin', 'semester', 'id_lembaga', 'nama_prodi', 'nama_fakultas',
        'skor_total', 'kategori',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Hitung skor total & kategori, simpan skrining beserta jawabannya.
     *
     * @param array $data    jenis_kelamin, semester, id_lembaga, nama_prodi, nama_fakultas
     * @param array $jawaban [nomor_item => nilai (0-5)]
     * @return array hasil yang disimpan (untuk ditampilkan)
     */
    public function simpanSkrining(array $data, array $jawaban): array
    {
        $skor = array_sum(array_map('intval', $jawaban));
        $data['skor_total'] = $skor;
        $data['kategori']   = PolaInternet::kategori($skor);

        $db = $this->db;
        $db->transStart();

        $id   = $this->insert($data, true);
        $rows = [];
        foreach ($jawaban as $nomor => $nilai) {
            $rows[] = ['skrining_id' => $id, 'nomor_item' => (int) $nomor, 'jawaban' => (int) $nilai];
        }
        $db->table('skrining_internet_jawaban')->insertBatch($rows);

        $db->transComplete();

        if (! $db->transStatus()) {
            throw new \RuntimeException('Gagal menyimpan hasil skrining.');
        }

        return $data;
    }
}
