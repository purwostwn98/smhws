<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RefactorDassJanjiRelation extends Migration
{
    public function up(): void
    {
        // 1. Tambah janji_id ke dass_assessments
        $this->forge->addColumn('dass_assessments', [
            'janji_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,   // nullable agar asesmen mandiri tetap bisa
                'default'    => null,
                'after'      => 'id',
                'comment'    => 'FK ke janji — nullable jika asesmen mandiri',
            ],
        ]);

        $this->db->query('ALTER TABLE `dass_assessments` ADD CONSTRAINT `fk_dass_janji`
            FOREIGN KEY (`janji_id`) REFERENCES `janji`(`id`) ON DELETE SET NULL ON UPDATE CASCADE');

        $this->db->query('ALTER TABLE `dass_assessments` ADD INDEX `idx_dass_janji` (`janji_id`)');

        // 2. Hapus kolom dass_assessment_id dari tabel janji
        //    (FK harus di-drop dulu sebelum kolom dihapus)
        $foreignKeys = $this->db->query(
            "SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = 'janji'
               AND COLUMN_NAME = 'dass_assessment_id'
               AND REFERENCED_TABLE_NAME IS NOT NULL"
        )->getResultArray();

        foreach ($foreignKeys as $fk) {
            $this->db->query("ALTER TABLE `janji` DROP FOREIGN KEY `{$fk['CONSTRAINT_NAME']}`");
        }

        $this->forge->dropColumn('janji', 'dass_assessment_id');
    }

    public function down(): void
    {
        // Kembalikan dass_assessment_id ke janji
        $this->forge->addColumn('janji', [
            'dass_assessment_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
                'default'    => null,
                'after'      => 'konselor_id',
            ],
        ]);

        // Hapus janji_id dari dass_assessments
        $this->db->query("ALTER TABLE `dass_assessments` DROP FOREIGN KEY `fk_dass_janji`");
        $this->db->query("ALTER TABLE `dass_assessments` DROP INDEX `idx_dass_janji`");
        $this->forge->dropColumn('dass_assessments', 'janji_id');
    }
}
