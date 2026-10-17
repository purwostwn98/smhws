<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSkriningPublikTable extends Migration
{
    private const KATEGORI = ['normal', 'ringan', 'sedang', 'berat', 'sangat_berat'];

    public function up(): void
    {
        // ── 1. Hasil skrining publik (anonim, tanpa login) ───────────────────
        $fields = [
            'id' => [
                'type'           => 'BIGINT',
                'constraint'     => 20,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'jenis_kelamin' => [
                'type'       => 'ENUM',
                'constraint' => ['laki-laki', 'perempuan'],
            ],
            'semester' => [
                'type'       => 'TINYINT',
                'constraint' => 2,
                'unsigned'   => true,
            ],
            'id_lembaga' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'comment'    => 'Kode prodi (tabel lembaga)',
            ],
            'nama_prodi' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'nama_fakultas' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
        ];

        foreach (['depresi', 'anxiety', 'stress'] as $sub) {
            $fields["skor_{$sub}_raw"] = ['type' => 'TINYINT', 'constraint' => 3, 'unsigned' => true, 'default' => 0];
            $fields["skor_{$sub}"]     = ['type' => 'TINYINT', 'constraint' => 3, 'unsigned' => true, 'default' => 0];
            $fields["kategori_{$sub}"] = ['type' => 'ENUM', 'constraint' => self::KATEGORI, 'default' => 'normal'];
        }

        $fields['created_at'] = ['type' => 'DATETIME', 'null' => true];
        $fields['updated_at'] = ['type' => 'DATETIME', 'null' => true];

        $this->forge->addField($fields);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('id_lembaga');
        $this->forge->addKey('created_at');
        $this->forge->createTable('skrining_publik', true);

        // ── 2. Jawaban per item ──────────────────────────────────────────────
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'constraint'     => 20,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'skrining_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
            ],
            'item_id' => [
                'type'       => 'TINYINT',
                'constraint' => 3,
                'unsigned'   => true,
            ],
            'nomor_item' => [
                'type'       => 'TINYINT',
                'constraint' => 2,
                'unsigned'   => true,
            ],
            'subscale' => [
                'type'       => 'ENUM',
                'constraint' => ['D', 'A', 'S'],
            ],
            'jawaban' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'unsigned'   => true,
                'comment'    => '0-3',
            ],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['skrining_id', 'item_id']);
        $this->forge->addForeignKey('skrining_id', 'skrining_publik', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('item_id', 'dass_items', 'id', 'RESTRICT', 'RESTRICT');
        $this->forge->createTable('skrining_publik_jawaban', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('skrining_publik_jawaban', true);
        $this->forge->dropTable('skrining_publik', true);
    }
}
