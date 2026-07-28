<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddKepuasanToFeedbackKonseling extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('feedback_konseling', [
            'kepuasan' => [
                'type'    => 'TEXT',
                'null'    => true,
                'default' => null,
                'after'   => 'rating',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('feedback_konseling', 'kepuasan');
    }
}
