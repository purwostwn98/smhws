<?php

namespace App\Models;

use CodeIgniter\Model;

class DassItemModel extends Model
{
    protected $table      = 'dass_items';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = ['nomor', 'pernyataan', 'subscale'];

    protected $useTimestamps = false;

    public function itemPerSubscale(string $subscale): array
    {
        return $this->where('subscale', $subscale)->orderBy('nomor')->findAll();
    }

    /** Semua item dikelompokkan per subscale. */
    public function groupBySubscale(): array
    {
        $items = $this->orderBy('nomor')->findAll();
        $result = ['D' => [], 'A' => [], 'S' => []];
        foreach ($items as $item) {
            $result[$item['subscale']][] = $item;
        }
        return $result;
    }
}
