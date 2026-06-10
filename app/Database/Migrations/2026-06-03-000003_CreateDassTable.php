<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDassTable extends Migration
{
    public function up(): void
    {
        // ── 1. Bank soal DASS-21 (statis, 21 baris) ─────────────────────────
        $this->forge->addField([
            'id' => [
                'type'       => 'TINYINT',
                'constraint' => 3,
                'unsigned'   => true,
                'auto_increment' => true,
            ],
            'nomor' => [
                'type'       => 'TINYINT',
                'constraint' => 2,
                'unsigned'   => true,
                'comment'    => 'Nomor urut 1–21',
            ],
            'pernyataan' => [
                'type' => 'TEXT',
            ],
            'subscale' => [
                'type'       => 'ENUM',
                'constraint' => ['D', 'A', 'S'],
                'comment'    => 'D=Depresi, A=Anxiety, S=Stres',
            ],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('nomor');
        $this->forge->createTable('dass_items', true);

        // ── 2. Hasil asesmen DASS (1 baris per pengisian) ────────────────────
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'constraint'     => 20,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'comment'    => 'Mahasiswa yang mengisi',
            ],
            'konselor_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
                'default'    => null,
                'comment'    => 'Konselor peninjau (nullable jika mandiri)',
            ],
            'tanggal_pengisian' => [
                'type' => 'DATE',
            ],
            'pemeriksa' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
                'default'    => null,
            ],
            'catatan_konselor' => [
                'type'    => 'TEXT',
                'null'    => true,
                'default' => null,
            ],

            // ── Skor Depresi ────────────────────────────────────────────────
            'skor_depresi_raw' => [
                'type'       => 'TINYINT',
                'constraint' => 3,
                'unsigned'   => true,
                'default'    => 0,
                'comment'    => 'Jumlah jawaban 7 item D (0–21)',
            ],
            'skor_depresi' => [
                'type'       => 'TINYINT',
                'constraint' => 3,
                'unsigned'   => true,
                'default'    => 0,
                'comment'    => 'skor_depresi_raw × 2',
            ],
            'kategori_depresi' => [
                'type'       => 'ENUM',
                'constraint' => ['normal', 'ringan', 'sedang', 'berat', 'sangat_berat'],
                'default'    => 'normal',
            ],

            // ── Skor Anxiety ────────────────────────────────────────────────
            'skor_anxiety_raw' => [
                'type'       => 'TINYINT',
                'constraint' => 3,
                'unsigned'   => true,
                'default'    => 0,
            ],
            'skor_anxiety' => [
                'type'       => 'TINYINT',
                'constraint' => 3,
                'unsigned'   => true,
                'default'    => 0,
            ],
            'kategori_anxiety' => [
                'type'       => 'ENUM',
                'constraint' => ['normal', 'ringan', 'sedang', 'berat', 'sangat_berat'],
                'default'    => 'normal',
            ],

            // ── Skor Stres ──────────────────────────────────────────────────
            'skor_stress_raw' => [
                'type'       => 'TINYINT',
                'constraint' => 3,
                'unsigned'   => true,
                'default'    => 0,
            ],
            'skor_stress' => [
                'type'       => 'TINYINT',
                'constraint' => 3,
                'unsigned'   => true,
                'default'    => 0,
            ],
            'kategori_stress' => [
                'type'       => 'ENUM',
                'constraint' => ['normal', 'ringan', 'sedang', 'berat', 'sangat_berat'],
                'default'    => 'normal',
            ],

            // ── Status tinjauan konselor ─────────────────────────────────────
            'is_reviewed' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'unsigned'   => true,
                'default'    => 0,
            ],
            'reviewed_by' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
                'default'    => null,
            ],
            'reviewed_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'default' => null,
            ],

            'created_at' => ['type' => 'DATETIME', 'null' => true, 'default' => null],
            'updated_at' => ['type' => 'DATETIME', 'null' => true, 'default' => null],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true, 'default' => null],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('user_id');
        $this->forge->addKey('tanggal_pengisian');
        $this->forge->addKey(['kategori_depresi', 'kategori_anxiety', 'kategori_stress']);
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('dass_assessments', true);

        // ── 3. Jawaban per item (21 baris per pengisian) ─────────────────────
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'constraint'     => 20,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'assessment_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
            ],
            'item_id' => [
                'type'       => 'TINYINT',
                'constraint' => 3,
                'unsigned'   => true,
                'comment'    => 'FK ke dass_items.id',
            ],
            'nomor_item' => [
                'type'       => 'TINYINT',
                'constraint' => 2,
                'unsigned'   => true,
                'comment'    => '1–21 (redundan untuk kemudahan query)',
            ],
            'subscale' => [
                'type'       => 'ENUM',
                'constraint' => ['D', 'A', 'S'],
            ],
            'jawaban' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'unsigned'   => true,
                'comment'    => '0=Tidak Pernah, 1=Kadang, 2=Cukup Sering, 3=Sering Sekali',
            ],
            'created_at' => ['type' => 'DATETIME', 'null' => true, 'default' => null],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['assessment_id', 'item_id']);
        $this->forge->addKey('subscale');
        $this->forge->addForeignKey('assessment_id', 'dass_assessments', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('item_id', 'dass_items', 'id', 'RESTRICT', 'RESTRICT');
        $this->forge->createTable('dass_jawaban', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('dass_jawaban', true);
        $this->forge->dropTable('dass_assessments', true);
        $this->forge->dropTable('dass_items', true);
    }
}
