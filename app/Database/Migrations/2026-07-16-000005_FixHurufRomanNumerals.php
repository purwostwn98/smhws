<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class FixHurufRomanNumerals extends Migration
{
    public function up(): void
    {
        $updates = [
            'diagnosis'   => 'VI',
            'intervensi'  => 'VII',
            'rekomendasi' => 'VIII',
            'prognosis'   => 'IX',
        ];
        foreach ($updates as $key => $huruf) {
            $this->db->table('checklist_sections')
                ->where('section_key', $key)
                ->update(['huruf' => $huruf]);
        }
    }

    public function down(): void
    {
        // intentionally empty
    }
}
