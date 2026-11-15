<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDokumenTerbitTable extends Migration
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
            'token' => [
                'type'       => 'CHAR',
                'constraint' => 32,
            ],
            'jenis' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
            ],
            'judul' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'cakupan' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'periode' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'filters' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'file_path' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'file_hash' => [
                'type'       => 'CHAR',
                'constraint' => 64,
            ],
            'file_size' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],
            'penandatangan_nama' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],
            'penandatangan_jabatan' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            // Akun yang mengunduh/menerbitkan PDF (audit), bukan penandatangan
            'diterbitkan_oleh' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
            ],
            'diterbitkan_oleh_nama' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('token');
        $this->forge->addKey('diterbitkan_oleh');

        $this->forge->createTable('dokumen_terbit', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('dokumen_terbit', true);
    }
}
