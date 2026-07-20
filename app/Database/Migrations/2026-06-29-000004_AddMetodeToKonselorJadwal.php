<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMetodeToKonselorJadwal extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('konselor_jadwal', [
            'metode' => [
                'type'       => 'ENUM',
                'constraint' => ['online', 'offline', 'keduanya'],
                'null'       => true,
                'default'    => null,
                'after'      => 'kuota',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('konselor_jadwal', 'metode');
    }
}
