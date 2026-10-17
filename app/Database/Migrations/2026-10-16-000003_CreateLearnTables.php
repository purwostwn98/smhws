<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateLearnTables extends Migration
{
    public function up(): void
    {
        // ── 1. Topik konten ──────────────────────────────────────────────────
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'nama'       => ['type' => 'VARCHAR', 'constraint' => 100],
            'slug'       => ['type' => 'VARCHAR', 'constraint' => 120],
            'urutan'     => ['type' => 'SMALLINT', 'constraint' => 5, 'unsigned' => true, 'default' => 0],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('slug');
        $this->forge->createTable('learn_topik', true);

        // ── 2. Konten ────────────────────────────────────────────────────────
        $this->forge->addField([
            'id'    => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'tipe'  => ['type' => 'ENUM', 'constraint' => ['artikel', 'video', 'poster', 'media']],
            'judul' => ['type' => 'VARCHAR', 'constraint' => 255],
            'slug'  => ['type' => 'VARCHAR', 'constraint' => 280],
            'ringkasan' => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'topik_id'  => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'thumbnail' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'isi'       => ['type' => 'LONGTEXT', 'null' => true, 'comment' => 'HTML artikel (tersanitasi) / keterangan'],
            'youtube_id'     => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'file'           => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true, 'comment' => 'Poster: gambar; Media: PDF'],
            'link_eksternal' => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'penulis'        => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true, 'comment' => 'Penulis / sumber'],
            'status'         => ['type' => 'ENUM', 'constraint' => ['draft', 'terbit'], 'default' => 'draft'],
            'diterbitkan_at' => ['type' => 'DATETIME', 'null' => true],
            'dilihat'        => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'default' => 0],
            'created_at'     => ['type' => 'DATETIME', 'null' => true],
            'updated_at'     => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('slug');
        $this->forge->addKey(['status', 'tipe']);
        $this->forge->addKey('topik_id');
        $this->forge->addForeignKey('topik_id', 'learn_topik', 'id', 'CASCADE', 'SET NULL');
        $this->forge->createTable('learn_konten', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('learn_konten', true);
        $this->forge->dropTable('learn_topik', true);
    }
}
