<?php

namespace App\Models;

use CodeIgniter\Model;

class ChecklistItemModel extends Model
{
    protected $table      = 'checklist_items';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $useTimestamps  = true;

    protected $allowedFields = [
        'section_id', 'subsection_key', 'subsection_label', 'subsection_urutan',
        'item_label', 'input_type', 'item_urutan', 'is_active',
    ];

    /** Ambil semua item aktif untuk satu section, dikelompokkan per subsection */
    public function groupedBySubsection(int $sectionId): array
    {
        $rows = $this->where('section_id', $sectionId)
                     ->where('is_active', 1)
                     ->orderBy('subsection_urutan', 'ASC')
                     ->orderBy('item_urutan', 'ASC')
                     ->findAll();

        $groups = [];
        foreach ($rows as $row) {
            $key = $row['subsection_key'];
            if (! isset($groups[$key])) {
                $groups[$key] = [
                    'subsection_key'    => $key,
                    'subsection_label'  => $row['subsection_label'],
                    'subsection_urutan' => (int) $row['subsection_urutan'],
                    'input_type'        => $row['input_type'],
                    'items'             => [],
                ];
            }
            $groups[$key]['items'][] = $row;
        }
        return $groups;
    }

    /** Ambil semua item (aktif+nonaktif) per section untuk admin, grouped by subsection */
    public function adminGrouped(int $sectionId): array
    {
        $rows = $this->where('section_id', $sectionId)
                     ->orderBy('subsection_urutan', 'ASC')
                     ->orderBy('item_urutan', 'ASC')
                     ->findAll();

        $groups = [];
        foreach ($rows as $row) {
            $key = $row['subsection_key'];
            if (! isset($groups[$key])) {
                $groups[$key] = [
                    'subsection_key'    => $key,
                    'subsection_label'  => $row['subsection_label'],
                    'subsection_urutan' => (int) $row['subsection_urutan'],
                    'input_type'        => $row['input_type'],
                    'items'             => [],
                ];
            }
            $groups[$key]['items'][] = $row;
        }
        return $groups;
    }

    /** Ambil semua checklist aktif, terstruktur per section_key → subsection → items */
    public function allForForm(): array
    {
        $db       = \Config\Database::connect();
        $secModel = new ChecklistSectionModel();
        $sections = $secModel->getActive();

        $result = [];
        foreach ($sections as $sec) {
            $result[$sec['section_key']] = [
                'section'    => $sec,
                'subsections'=> $this->groupedBySubsection((int) $sec['id']),
            ];
        }
        return $result;
    }

    public function getSubsections(int $sectionId): array
    {
        return $this->db->table('checklist_items')
            ->select('subsection_key, subsection_label, subsection_urutan, input_type')
            ->where('section_id', $sectionId)
            ->where('deleted_at IS NULL')
            ->groupBy('subsection_key')
            ->orderBy('subsection_urutan', 'ASC')
            ->get()->getResultArray();
    }
}
