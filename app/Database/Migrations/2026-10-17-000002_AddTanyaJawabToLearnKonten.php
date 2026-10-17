<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTanyaJawabToLearnKonten extends Migration
{
    public function up(): void
    {
        $this->forge->modifyColumn('learn_konten', [
            'tipe' => ['type' => 'ENUM', 'constraint' => ['artikel', 'video', 'poster', 'media', 'tanya_jawab']],
        ]);
    }

    public function down(): void
    {
        // Konten tanya_jawab dijadikan artikel agar tidak gagal saat ENUM dipersempit
        $this->db->table('learn_konten')->where('tipe', 'tanya_jawab')->update(['tipe' => 'artikel']);
        $this->forge->modifyColumn('learn_konten', [
            'tipe' => ['type' => 'ENUM', 'constraint' => ['artikel', 'video', 'poster', 'media']],
        ]);
    }
}
