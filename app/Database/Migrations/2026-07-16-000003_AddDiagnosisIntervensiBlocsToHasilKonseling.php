<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDiagnosisIntervensiBlocsToHasilKonseling extends Migration
{
    public function up(): void
    {
        $fields = [
            'diagnosis'   => ['type' => 'JSON', 'null' => true, 'after' => 'strategi_koping'],
            'intervensi'  => ['type' => 'JSON', 'null' => true, 'after' => 'diagnosis'],
            'rekomendasi' => ['type' => 'JSON', 'null' => true, 'after' => 'intervensi'],
            'prognosis'   => ['type' => 'JSON', 'null' => true, 'after' => 'rekomendasi'],
        ];
        $this->forge->addColumn('hasil_konseling', $fields);
    }

    public function down(): void
    {
        $this->forge->dropColumn('hasil_konseling', ['diagnosis', 'intervensi', 'rekomendasi', 'prognosis']);
    }
}
