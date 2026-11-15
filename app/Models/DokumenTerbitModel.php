<?php

namespace App\Models;

use CodeIgniter\Model;

class DokumenTerbitModel extends Model
{
    protected $table         = 'dokumen_terbit';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'token',
        'jenis',
        'judul',
        'cakupan',
        'periode',
        'filters',
        'file_path',
        'file_hash',
        'file_size',
        'penandatangan_nama',
        'penandatangan_jabatan',
        'diterbitkan_oleh',
        'diterbitkan_oleh_nama',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = '';

    protected array $casts = [
        'filters' => '?json-array',
    ];

    public function findByToken(string $token): ?array
    {
        if (! preg_match('/^[a-f0-9]{32}$/', $token)) {
            return null;
        }

        return $this->where('token', $token)->first();
    }
}
