<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePertanyaanPsikologTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'      => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'user_id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'comment' => 'Mahasiswa penanya'],

            // Identitas (snapshot saat bertanya)
            'nama'          => ['type' => 'VARCHAR', 'constraint' => 255],
            'prodi'         => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'fakultas'      => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'jenis_kelamin' => ['type' => 'ENUM', 'constraint' => ['laki-laki', 'perempuan']],
            'semester'      => ['type' => 'TINYINT', 'constraint' => 2, 'unsigned' => true],

            // Pertanyaan
            'topik_id'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true, 'comment' => 'learn_topik'],
            'judul'      => ['type' => 'VARCHAR', 'constraint' => 255, 'comment' => 'Inti pertanyaan'],
            'pertanyaan' => ['type' => 'TEXT'],
            'izin_publikasi' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0, 'comment' => 'Boleh ditampilkan anonim di SMHWS Learn'],

            // Alur
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['menunggu', 'ditugaskan', 'dijawab', 'ditolak'],
                'default'    => 'menunggu',
            ],
            'alasan_ditolak'  => ['type' => 'TEXT', 'null' => true],
            'konselor_id'     => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'null' => true],
            'ditugaskan_oleh' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'null' => true],
            'ditugaskan_at'   => ['type' => 'DATETIME', 'null' => true],

            // Jawaban
            'jawaban'    => ['type' => 'TEXT', 'null' => true],
            'dijawab_at' => ['type' => 'DATETIME', 'null' => true],
            'dibaca_mahasiswa_at' => ['type' => 'DATETIME', 'null' => true],

            // Publikasi ke SMHWS Learn
            'learn_konten_id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'null' => true],

            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('status');
        $this->forge->addKey('user_id');
        $this->forge->addKey('konselor_id');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('konselor_id', 'konselor', 'id', 'CASCADE', 'SET NULL');
        $this->forge->addForeignKey('topik_id', 'learn_topik', 'id', 'CASCADE', 'SET NULL');
        $this->forge->addForeignKey('learn_konten_id', 'learn_konten', 'id', 'CASCADE', 'SET NULL');
        $this->forge->createTable('pertanyaan_psikolog', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('pertanyaan_psikolog', true);
    }
}
