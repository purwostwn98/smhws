<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddJamMulaiSelesaiToHasilKonseling extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('hasil_konseling', [
            'jam_mulai'  => ['type' => 'TIME', 'null' => true, 'default' => null, 'after' => 'konselor_id'],
            'jam_selesai'=> ['type' => 'TIME', 'null' => true, 'default' => null, 'after' => 'jam_mulai'],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('hasil_konseling', ['jam_mulai', 'jam_selesai']);
    }
}
