<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateJanjiTable extends Migration
{
    public function up(): void
    {
        // ── Tabel pendaftaran/janji konseling ────────────────────────────────
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],

            // Relasi
            'user_id'            => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true],
            'konselor_id'        => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'null' => true, 'default' => null],
            'dass_assessment_id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'null' => true, 'default' => null],

            // Identitas tambahan (belum ada di tabel users)
            'jenis_kelamin' => ['type' => 'ENUM', 'constraint' => ['laki-laki', 'perempuan']],
            'usia'          => ['type' => 'TINYINT', 'constraint' => 3, 'unsigned' => true],
            'agama'         => ['type' => 'VARCHAR', 'constraint' => 50],
            'semester'      => ['type' => 'TINYINT', 'constraint' => 2, 'unsigned' => true],
            'dosen_pa'      => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true, 'default' => null, 'comment' => 'Dosen Pembimbing Akademik'],
            'domisili'      => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => true, 'default' => null],
            'status_pernikahan' => [
                'type'       => 'ENUM',
                'constraint' => ['belum_menikah', 'menikah', 'cerai'],
                'default'    => 'belum_menikah',
            ],

            // Riwayat & preferensi
            'pernah_konseling_smhws' => ['type' => 'TINYINT', 'constraint' => 1, 'unsigned' => true, 'default' => 0],
            'metode' => [
                'type'       => 'ENUM',
                'constraint' => ['offline', 'online', 'hybrid'],
                'default'    => 'offline',
            ],
            // JSON: [{"hari":"senin","waktu":"08:00-10:00"}, ...]
            'jadwal_pilihan'  => ['type' => 'JSON', 'null' => true, 'default' => null],
            // JSON: [konselor_id, ...]
            'konselor_pilihan'=> ['type' => 'JSON', 'null' => true, 'default' => null],

            // Keluhan
            'tema_konseling' => [
                'type'       => 'ENUM',
                'constraint' => ['akademik', 'keorganisasian', 'pengembangan_diri', 'relasi', 'pribadi', 'keluarga', 'lainnya'],
                'null'       => true,
                'default'    => null,
            ],
            'keluhan_utama'  => ['type' => 'TEXT'],
            'mulai_keluhan'  => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true, 'default' => null, 'comment' => 'Sejak kapan keluhan dirasakan'],
            'upaya_dilakukan'=> ['type' => 'TEXT', 'null' => true, 'default' => null],

            // Status janji (diisi admin/konselor)
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['menunggu', 'dikonfirmasi', 'berlangsung', 'selesai', 'dibatalkan'],
                'default'    => 'menunggu',
            ],
            'tanggal_konseling' => ['type' => 'DATE', 'null' => true, 'default' => null],
            'jam_konseling'     => ['type' => 'TIME', 'null' => true, 'default' => null],
            'lokasi_link'       => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true, 'default' => null, 'comment' => 'Ruangan / link meeting'],
            'catatan_admin'     => ['type' => 'TEXT', 'null' => true, 'default' => null],

            'created_at' => ['type' => 'DATETIME', 'null' => true, 'default' => null],
            'updated_at' => ['type' => 'DATETIME', 'null' => true, 'default' => null],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true, 'default' => null],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('user_id');
        $this->forge->addKey('status');
        $this->forge->addKey('tanggal_konseling');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('janji', true);

        // ── Tabel safety screening ───────────────────────────────────────────
        $this->forge->addField([
            'id'       => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'janji_id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true],

            // Q1: Dalam 3 bulan terakhir, pernah melukai diri sendiri?
            'pernah_selfharm' => [
                'type'       => 'ENUM',
                'constraint' => ['tidak', 'ya', 'tidak_mau_menjawab'],
                'default'    => 'tidak',
            ],
            // Q2: Saat ini merasa aman dengan diri sendiri?
            'merasa_aman' => [
                'type'       => 'ENUM',
                'constraint' => ['ya', 'tidak', 'tidak_mau_menjawab'],
                'default'    => 'ya',
            ],
            // Q3: Dalam 1 bulan terakhir, pernah punya pikiran mengakhiri hidup?
            'pikiran_mengakhiri_hidup' => [
                'type'       => 'ENUM',
                'constraint' => ['tidak', 'ya', 'tidak_mau_menjawab'],
                'default'    => 'tidak',
            ],
            // Q4: Apakah pikiran tersebut mengganggu / sulit dikendalikan?
            'pikiran_mengganggu' => [
                'type'       => 'ENUM',
                'constraint' => ['tidak', 'ya', 'tidak_berlaku'],
                'default'    => 'tidak_berlaku',
            ],
            // Keterangan tambahan riwayat self-harm (teks bebas)
            'riwayat_selfharm_keterangan' => ['type' => 'TEXT', 'null' => true, 'default' => null],

            'created_at' => ['type' => 'DATETIME', 'null' => true, 'default' => null],
            'updated_at' => ['type' => 'DATETIME', 'null' => true, 'default' => null],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('janji_id');
        $this->forge->addForeignKey('janji_id', 'janji', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('janji_safety_screening', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('janji_safety_screening', true);
        $this->forge->dropTable('janji', true);
    }
}
