<?php

namespace App\Models;

use CodeIgniter\Model;

class InstansiRujukanModel extends Model
{
    protected $table      = 'instansi_rujukan';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = ['nama_instansi', 'singkatan', 'alamat'];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getAll(): array
    {
        return $this->orderBy('nama_instansi', 'ASC')->findAll();
    }
}
