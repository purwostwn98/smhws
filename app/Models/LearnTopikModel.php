<?php

namespace App\Models;

use CodeIgniter\Model;

class LearnTopikModel extends Model
{
    protected $table      = 'learn_topik';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = ['nama', 'slug', 'urutan'];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getAll(): array
    {
        return $this->orderBy('urutan', 'ASC')->orderBy('nama', 'ASC')->findAll();
    }

    /** Topik + jumlah konten (semua status). */
    public function denganJumlah(): array
    {
        return $this->select('learn_topik.*, COUNT(k.id) AS jumlah')
            ->join('learn_konten k', 'k.topik_id = learn_topik.id', 'left')
            ->groupBy('learn_topik.id')
            ->orderBy('urutan', 'ASC')->orderBy('nama', 'ASC')
            ->findAll();
    }
}
