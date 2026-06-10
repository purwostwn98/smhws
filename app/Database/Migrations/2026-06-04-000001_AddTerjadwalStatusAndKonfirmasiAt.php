<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTerjadwalStatusAndKonfirmasiAt extends Migration
{
    public function up(): void
    {
        // Tambah 'terjadwal' ke ENUM status janji
        $this->db->query("
            ALTER TABLE `janji`
            MODIFY COLUMN `status` ENUM('menunggu','dikonfirmasi','terjadwal','berlangsung','selesai','dibatalkan')
            NOT NULL DEFAULT 'menunggu'
        ");

        // Tambah kolom konfirmasi mahasiswa
        $this->forge->addColumn('janji', [
            'mahasiswa_konfirmasi_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'default' => null,
                'after'   => 'catatan_admin',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('janji', 'mahasiswa_konfirmasi_at');

        $this->db->query("
            ALTER TABLE `janji`
            MODIFY COLUMN `status` ENUM('menunggu','dikonfirmasi','berlangsung','selesai','dibatalkan')
            NOT NULL DEFAULT 'menunggu'
        ");
    }
}
