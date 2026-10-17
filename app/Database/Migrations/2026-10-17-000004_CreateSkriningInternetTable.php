<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSkriningInternetTable extends Migration
{
    public function up(): void
    {
        // ── 1. Hasil skrining Pola Penggunaan Internet (anonim, tanpa login) ──
        $this->forge->addField([
            'id'            => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'jenis_kelamin' => ['type' => 'ENUM', 'constraint' => ['laki-laki', 'perempuan']],
            'semester'      => ['type' => 'TINYINT', 'constraint' => 2, 'unsigned' => true],
            'id_lembaga'    => ['type' => 'VARCHAR', 'constraint' => 50, 'comment' => 'Kode prodi (tabel lembaga)'],
            'nama_prodi'    => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'nama_fakultas' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'skor_total'    => ['type' => 'TINYINT', 'constraint' => 3, 'unsigned' => true, 'comment' => '0–90'],
            'kategori'      => ['type' => 'ENUM', 'constraint' => ['normal', 'ringan', 'sedang', 'berat']],
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
            'updated_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('id_lembaga');
        $this->forge->addKey('created_at');
        $this->forge->createTable('skrining_internet', true);

        // ── 2. Jawaban per butir ─────────────────────────────────────────────
        $this->forge->addField([
            'id'          => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'skrining_id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true],
            'nomor_item'  => ['type' => 'TINYINT', 'constraint' => 2, 'unsigned' => true, 'comment' => '1–18'],
            'jawaban'     => ['type' => 'TINYINT', 'constraint' => 1, 'unsigned' => true, 'comment' => '0–5'],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['skrining_id', 'nomor_item']);
        $this->forge->addForeignKey('skrining_id', 'skrining_internet', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('skrining_internet_jawaban', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('skrining_internet_jawaban', true);
        $this->forge->dropTable('skrining_internet', true);
    }
}
