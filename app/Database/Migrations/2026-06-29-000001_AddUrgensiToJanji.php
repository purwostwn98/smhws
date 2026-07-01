<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUrgensiToJanji extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('janji', [
            'urgensi' => [
                'type'       => 'ENUM',
                'constraint' => ['biasa', 'cukup_urgen', 'sangat_urgen'],
                'null'       => true,
                'default'    => null,
                'after'      => 'keluhan_utama',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('janji', 'urgensi');
    }
}
