<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTemaKonselingToJanji extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('janji', [
            'tema_konseling' => [
                'type'       => 'ENUM',
                'constraint' => ['akademik', 'keorganisasian', 'pengembangan_diri', 'relasi', 'pribadi', 'keluarga', 'lainnya'],
                'null'       => true,
                'default'    => null,
                'after'      => 'upaya_dilakukan',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('janji', 'tema_konseling');
    }
}
