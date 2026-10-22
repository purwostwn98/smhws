<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/** Inventori Masalah Umum (IMU) — asesmen awal pendaftaran konseling, menggantikan DASS-21 pada janji baru. */
class CreateImuTable extends Migration
{
    public function up(): void
    {
        // ── 1. Hasil asesmen per janji ───────────────────────────────────────
        $this->forge->addField([
            'id'                  => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'janji_id'            => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true],
            'user_id'             => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true],
            'skor_total'          => ['type' => 'TINYINT', 'constraint' => 3, 'unsigned' => true, 'comment' => '24–120'],
            'kategori'            => ['type' => 'ENUM', 'constraint' => ['rendah', 'sedang', 'tinggi']],
            'skor_area_emosi'     => ['type' => 'TINYINT', 'constraint' => 2, 'unsigned' => true, 'comment' => 'Butir 1–8 (8–40)'],
            'skor_area_akademik'  => ['type' => 'TINYINT', 'constraint' => 2, 'unsigned' => true, 'comment' => 'Butir 9–16 (8–40)'],
            'skor_area_kesehatan' => ['type' => 'TINYINT', 'constraint' => 2, 'unsigned' => true, 'comment' => 'Butir 17–24 (8–40)'],
            'obat_keterangan'     => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true, 'comment' => 'Isian butir 24'],
            'lainnya_keterangan'  => ['type' => 'TEXT', 'null' => true, 'comment' => 'Butir 25'],
            'lainnya_skor'        => ['type' => 'TINYINT', 'constraint' => 1, 'unsigned' => true, 'null' => true, 'comment' => '1–5, tidak masuk skor total'],
            'is_reviewed'         => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'reviewed_by'         => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'null' => true],
            'reviewed_at'         => ['type' => 'DATETIME', 'null' => true],
            'catatan_konselor'    => ['type' => 'TEXT', 'null' => true],
            'created_at'          => ['type' => 'DATETIME', 'null' => true],
            'updated_at'          => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'          => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('janji_id');
        $this->forge->addKey('user_id');
        $this->forge->addKey('kategori');
        $this->forge->addForeignKey('janji_id', 'janji', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('imu_assessments', true);

        // ── 2. Jawaban per butir (24 baris per pengisian) ────────────────────
        $this->forge->addField([
            'id'            => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'assessment_id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true],
            'nomor_item'    => ['type' => 'TINYINT', 'constraint' => 2, 'unsigned' => true, 'comment' => '1–24'],
            'jawaban'       => ['type' => 'TINYINT', 'constraint' => 1, 'unsigned' => true, 'comment' => '1–5'],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['assessment_id', 'nomor_item']);
        $this->forge->addForeignKey('assessment_id', 'imu_assessments', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('imu_jawaban', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('imu_jawaban', true);
        $this->forge->dropTable('imu_assessments', true);
    }
}
