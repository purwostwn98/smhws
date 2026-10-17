<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAgendaKegiatanTable extends Migration
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
            'jenis' => [
                'type'       => 'ENUM',
                'constraint' => ['psychoeducation', 'webinar', 'workshop'],
            ],
            'nama_kegiatan' => ['type' => 'VARCHAR', 'constraint' => 255],
            'deskripsi'     => ['type' => 'TEXT', 'null' => true],
            'tema'          => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'narasumber'    => ['type' => 'TEXT', 'null' => true, 'comment' => 'Satu narasumber per baris'],
            'poster'        => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'tanggal_mulai'   => ['type' => 'DATETIME'],
            'tanggal_selesai' => ['type' => 'DATETIME', 'null' => true],
            'lokasi'        => ['type' => 'VARCHAR', 'constraint' => 255],
            'biaya_tipe' => [
                'type'       => 'ENUM',
                'constraint' => ['gratis', 'berbayar'],
                'default'    => 'gratis',
            ],
            'biaya_nominal' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'kontak'        => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'link_pendaftaran'  => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'batas_pendaftaran' => ['type' => 'DATETIME', 'null' => true],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['dibuka', 'ditutup'],
                'default'    => 'dibuka',
            ],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('tanggal_mulai');
        $this->forge->addKey('jenis');
        $this->forge->createTable('agenda_kegiatan', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('agenda_kegiatan', true);
    }
}
