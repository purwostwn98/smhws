<?php

namespace App\Models;

use CodeIgniter\Model;

class ChecklistSectionModel extends Model
{
    protected $table      = 'checklist_sections';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = ['section_key', 'huruf', 'label', 'icon', 'color', 'urutan', 'is_active'];

    public function getActive(): array
    {
        return $this->where('is_active', 1)->orderBy('urutan', 'ASC')->findAll();
    }

    public function getAll(): array
    {
        return $this->orderBy('urutan', 'ASC')->findAll();
    }

    public function byKey(string $key): ?array
    {
        return $this->where('section_key', $key)->first();
    }
}
