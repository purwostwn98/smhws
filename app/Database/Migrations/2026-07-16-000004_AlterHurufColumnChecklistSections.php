<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterHurufColumnChecklistSections extends Migration
{
    public function up(): void
    {
        $this->forge->modifyColumn('checklist_sections', [
            'huruf' => ['name' => 'huruf', 'type' => 'VARCHAR', 'constraint' => 4],
        ]);
    }

    public function down(): void
    {
        $this->forge->modifyColumn('checklist_sections', [
            'huruf' => ['name' => 'huruf', 'type' => 'CHAR', 'constraint' => 1],
        ]);
    }
}
