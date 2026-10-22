<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddIsPimpinanUnivToUsers extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('users', [
            'is_pimpinan_univ' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'unsigned'   => true,
                'default'    => 0,
                'after'      => 'is_admin_fakultas',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('users', 'is_pimpinan_univ');
    }
}
