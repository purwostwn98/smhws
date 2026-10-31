<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Catatan baris yang dibuat oleh impor data lama, agar satu batch impor dapat dibatalkan.
 */
class CreateImporDataLamaTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'         => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'batch'      => ['type' => 'VARCHAR', 'constraint' => 50],
            'tabel'      => ['type' => 'VARCHAR', 'constraint' => 50, 'comment' => 'users / mahasiswa / konselor / janji / hasil_konseling'],
            'ref_id'     => ['type' => 'VARCHAR', 'constraint' => 50, 'comment' => 'Primary key baris (nim untuk mahasiswa)'],
            'keterangan' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey(['batch', 'tabel']);
        $this->forge->createTable('impor_data_lama', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('impor_data_lama', true);
    }
}
