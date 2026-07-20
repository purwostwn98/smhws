<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateKonselorTable extends Migration
{
    public function up(): void
    {
        // ── Tabel profil konselor (1-to-1 dengan users) ─────────────────────
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'constraint'     => 20,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'     => 'BIGINT',
                'constraint' => 20,
                'unsigned' => true,
            ],
            'nip' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => true,
                'default'    => null,
            ],
            'gelar_depan' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'default'    => null,
                'comment'    => 'Contoh: Dr., Prof.',
            ],
            'gelar_belakang' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'default'    => null,
                'comment'    => 'Contoh: M.Psi., Psikolog',
            ],
            // JSON array: ["Depresi","Kecemasan","Akademik"]
            'spesialisasi' => [
                'type' => 'JSON',
                'null' => true,
                'default' => null,
            ],
            'bio' => [
                'type' => 'TEXT',
                'null' => true,
                'default' => null,
            ],
            'foto' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'default'    => null,
            ],
            'no_str' => [
                'type'       => 'VARCHAR',
                'constraint' => 60,
                'null'       => true,
                'default'    => null,
                'comment'    => 'Nomor Surat Tanda Registrasi',
            ],
            'tahun_pengalaman' => [
                'type'       => 'TINYINT',
                'constraint' => 3,
                'unsigned'   => true,
                'null'       => true,
                'default'    => null,
            ],
            'max_pasien_per_hari' => [
                'type'       => 'TINYINT',
                'constraint' => 3,
                'unsigned'   => true,
                'default'    => 8,
            ],
            'is_available' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'unsigned'   => true,
                'default'    => 1,
                'comment'    => '1=tersedia, 0=tidak tersedia',
            ],
            'rating' => [
                'type'       => 'DECIMAL',
                'constraint' => '3,2',
                'default'    => '0.00',
            ],
            'total_sesi' => [
                'type'     => 'INT',
                'unsigned' => true,
                'default'  => 0,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'default' => null,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'default' => null,
            ],
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'default' => null,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('user_id');
        $this->forge->addKey('is_available');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');

        $this->forge->createTable('konselor', true);

        // ── Tabel jadwal ketersediaan konselor ───────────────────────────────
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'constraint'     => 20,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'konselor_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
            ],
            'hari' => [
                'type'       => 'ENUM',
                'constraint' => ['senin','selasa','rabu','kamis','jumat','sabtu'],
            ],
            'jam_mulai' => [
                'type' => 'TIME',
            ],
            'jam_selesai' => [
                'type' => 'TIME',
            ],
            'kuota' => [
                'type'       => 'TINYINT',
                'constraint' => 3,
                'unsigned'   => true,
                'default'    => 4,
                'comment'    => 'Jumlah slot janji per sesi',
            ],
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'unsigned'   => true,
                'default'    => 1,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'default' => null,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'default' => null,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey(['konselor_id', 'hari']);
        $this->forge->addForeignKey('konselor_id', 'konselor', 'id', 'CASCADE', 'CASCADE');

        $this->forge->createTable('konselor_jadwal', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('konselor_jadwal', true);
        $this->forge->dropTable('konselor', true);
    }
}
