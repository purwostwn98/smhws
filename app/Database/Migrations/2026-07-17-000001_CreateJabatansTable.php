<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateJabatansTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'kode_jabatan' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => false,
            ],
            'nama' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'unit' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'default'    => null,
            ],
            'kode_lembaga' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
                'default'    => null,
            ],
            'singkatan_lmbg' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'default'    => null,
            ],
            'jenis_lembaga_id' => [
                'type'       => 'TINYINT',
                'constraint' => 4,
                'null'       => true,
                'default'    => null,
            ],
            'eselon' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
                'default'    => null,
            ],
            'sks' => [
                'type'       => 'TINYINT',
                'constraint' => 4,
                'unsigned'   => true,
                'null'       => true,
                'default'    => null,
            ],
            'penjabat' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'default'    => null,
            ],
            'uniid_penjabat' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
                'default'    => null,
            ],
            'ext' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'unsigned'   => true,
                'null'       => false,
                'default'    => 0,
            ],
            'synced_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('kode_jabatan', true);
        $this->forge->addKey('kode_lembaga');
        $this->forge->addKey('uniid_penjabat');
        $this->forge->createTable('jabatans');
    }

    public function down(): void
    {
        $this->forge->dropTable('jabatans', true);
    }
}
