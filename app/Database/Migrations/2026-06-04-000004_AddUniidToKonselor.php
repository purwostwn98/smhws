<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUniidToKonselor extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('konselor', [
            'uniid' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'default'    => null,
                'after'      => 'nip',
                'comment'    => 'ID pegawai/dosen UMS (diisi jika konselor adalah dosen UMS)',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('konselor', 'uniid');
    }
}
