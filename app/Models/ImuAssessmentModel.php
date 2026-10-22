<?php

namespace App\Models;

use App\Libraries\InventoriMasalahUmum as IMU;
use CodeIgniter\Model;

/** Asesmen awal Inventori Masalah Umum (tabel imu_assessments + imu_jawaban). */
class ImuAssessmentModel extends Model
{
    protected $table          = 'imu_assessments';
    protected $primaryKey     = 'id';
    protected $returnType     = 'array';
    protected $useSoftDeletes = true;

    protected $allowedFields = [
        'janji_id', 'user_id', 'skor_total', 'kategori',
        'skor_area_emosi', 'skor_area_akademik', 'skor_area_kesehatan',
        'obat_keterangan', 'lainnya_keterangan', 'lainnya_skor',
        'is_reviewed', 'reviewed_by', 'reviewed_at', 'catatan_konselor',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    /**
     * Hitung skor total, kategori & skor area, lalu simpan asesmen beserta jawabannya.
     * Dipanggil di dalam transaksi pemanggil (JanjiController::simpan).
     *
     * @param array $data    janji_id, user_id, obat_keterangan, lainnya_keterangan, lainnya_skor
     * @param array $jawaban [nomor_item => nilai (1-5)]
     */
    public function simpanAsesmen(array $data, array $jawaban): int
    {
        $skor = array_sum(array_map('intval', $jawaban));
        $data['skor_total'] = $skor;
        $data['kategori']   = IMU::kategori($skor);
        foreach (IMU::skorArea($jawaban) as $kode => $skorArea) {
            $data['skor_area_' . $kode] = $skorArea;
        }

        $id   = $this->insert($data, true);
        $rows = [];
        foreach ($jawaban as $nomor => $nilai) {
            $rows[] = ['assessment_id' => $id, 'nomor_item' => (int) $nomor, 'jawaban' => (int) $nilai];
        }
        $this->db->table('imu_jawaban')->insertBatch($rows);

        return $id;
    }

    /** Jawaban suatu asesmen: [nomor_item => nilai]. */
    public function jawaban(int $assessmentId): array
    {
        $rows = $this->db->table('imu_jawaban')
            ->select('nomor_item, jawaban')
            ->where('assessment_id', $assessmentId)
            ->orderBy('nomor_item')
            ->get()->getResultArray();

        return array_column($rows, 'jawaban', 'nomor_item');
    }

    /** Asesmen IMU suatu janji beserta jawabannya (null bila janji lama tanpa IMU). */
    public function byJanji(int $janjiId): ?array
    {
        $row = $this->where('janji_id', $janjiId)->first();
        if (! $row) return null;

        $row['jawaban'] = $this->jawaban((int) $row['id']);
        return $row;
    }
}
