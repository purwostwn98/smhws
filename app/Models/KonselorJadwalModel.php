<?php

namespace App\Models;

use CodeIgniter\Model;

class KonselorJadwalModel extends Model
{
    protected $table      = 'konselor_jadwal';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'konselor_id',
        'hari',
        'jam_mulai',
        'jam_selesai',
        'kuota',
        'is_active',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /** Semua jadwal aktif seorang konselor, diurutkan per hari & jam. */
    public function jadwalKonselor(int $konselorId): array
    {
        $urutanHari = ['senin' => 1, 'selasa' => 2, 'rabu' => 3, 'kamis' => 4, 'jumat' => 5, 'sabtu' => 6];

        $rows = $this->where('konselor_id', $konselorId)
                     ->where('is_active', 1)
                     ->orderBy('hari')
                     ->orderBy('jam_mulai')
                     ->findAll();

        usort($rows, fn($a, $b) =>
            ($urutanHari[$a['hari']] ?? 9) <=> ($urutanHari[$b['hari']] ?? 9)
        );

        return $rows;
    }

    /** Jadwal tersedia pada hari tertentu (untuk semua konselor aktif). */
    public function jadwalHari(string $hari): array
    {
        return $this->select('konselor_jadwal.*, konselor.user_id, users.name')
                    ->join('konselor', 'konselor.id = konselor_jadwal.konselor_id')
                    ->join('users', 'users.id = konselor.user_id')
                    ->where('konselor_jadwal.hari', $hari)
                    ->where('konselor_jadwal.is_active', 1)
                    ->where('konselor.is_available', 1)
                    ->orderBy('jam_mulai')
                    ->findAll();
    }

    /** Simpan banyak jadwal sekaligus (replace lama). */
    public function syncJadwal(int $konselorId, array $jadwalList): void
    {
        $this->where('konselor_id', $konselorId)->delete();

        if (empty($jadwalList)) {
            return;
        }

        $now  = date('Y-m-d H:i:s');
        $rows = array_map(fn($j) => array_merge($j, [
            'konselor_id' => $konselorId,
            'created_at'  => $now,
            'updated_at'  => $now,
        ]), $jadwalList);

        $this->insertBatch($rows);
    }
}
