<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateHasilKonselingTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'constraint'     => 20,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'janji_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
            ],
            'konselor_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
            ],
            'ada_rujukan' => [
                'type'    => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
            ],
            'instansi_rujukan' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'default'    => null,
            ],
            'alasan_rujukan' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'sesi_lanjutan' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'catatan_sesi' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => ['type' => 'DATETIME', 'null' => true, 'default' => null],
            'updated_at' => ['type' => 'DATETIME', 'null' => true, 'default' => null],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true, 'default' => null],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('janji_id');
        $this->forge->addForeignKey('janji_id',    'janji',    'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('konselor_id', 'konselor', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('hasil_konseling', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('hasil_konseling', true);
    }
}
