<?php

namespace App\Models;

use CodeIgniter\Model;

class HasilKonselingModel extends Model
{
    protected $table         = 'hasil_konseling';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useSoftDeletes = true;

    protected $allowedFields = [
        'janji_id', 'konselor_id', 'jam_mulai', 'jam_selesai',
        'ada_rujukan', 'instansi_rujukan_id', 'instansi_rujukan', 'alasan_rujukan',
        'sesi_lanjutan', 'catatan_sesi',
        'stressor', 'faktor_kerentanan', 'faktor_protektif', 'strategi_koping',
        'diagnosis', 'intervensi', 'rekomendasi', 'prognosis',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    protected $validationRules = [
        'janji_id'    => 'required|is_natural_no_zero',
        'konselor_id' => 'required|is_natural_no_zero',
        'ada_rujukan' => 'required|in_list[0,1]',
    ];

    public function byJanji(int $janjiId): ?array
    {
        return $this->where('janji_id', $janjiId)->first();
    }
}
