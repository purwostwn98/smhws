<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateChecklistTables extends Migration
{
    public function up(): void
    {
        // Tabel section (B, C, D, E)
        $this->forge->addField([
            'id'          => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'section_key' => ['type' => 'VARCHAR', 'constraint' => 30],
            'huruf'       => ['type' => 'CHAR', 'constraint' => 1],
            'label'       => ['type' => 'VARCHAR', 'constraint' => 150],
            'icon'        => ['type' => 'VARCHAR', 'constraint' => 50],
            'color'       => ['type' => 'VARCHAR', 'constraint' => 20],
            'urutan'      => ['type' => 'INT', 'default' => 0],
            'is_active'   => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('section_key');
        $this->forge->createTable('checklist_sections', true);

        // Tabel item checklist
        $this->forge->addField([
            'id'                => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'section_id'        => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true],
            'subsection_key'    => ['type' => 'VARCHAR', 'constraint' => 50],
            'subsection_label'  => ['type' => 'VARCHAR', 'constraint' => 150],
            'subsection_urutan' => ['type' => 'INT', 'default' => 0],
            'item_label'        => ['type' => 'TEXT'],
            'input_type'        => ['type' => 'ENUM', 'constraint' => ['checkbox', 'radio'], 'default' => 'checkbox'],
            'item_urutan'       => ['type' => 'INT', 'default' => 0],
            'is_active'         => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at'        => ['type' => 'DATETIME', 'null' => true, 'default' => null],
            'updated_at'        => ['type' => 'DATETIME', 'null' => true, 'default' => null],
            'deleted_at'        => ['type' => 'DATETIME', 'null' => true, 'default' => null],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('section_id', 'checklist_sections', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('checklist_items', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('checklist_items', true);
        $this->forge->dropTable('checklist_sections', true);
    }
}
