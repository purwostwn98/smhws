<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddChecklistToHasilKonseling extends Migration
{
    public function up(): void
    {
        $this->db->query("ALTER TABLE hasil_konseling
            ADD COLUMN stressor         JSON NULL DEFAULT NULL AFTER catatan_sesi,
            ADD COLUMN faktor_kerentanan JSON NULL DEFAULT NULL AFTER stressor,
            ADD COLUMN faktor_protektif  JSON NULL DEFAULT NULL AFTER faktor_kerentanan,
            ADD COLUMN strategi_koping   JSON NULL DEFAULT NULL AFTER faktor_protektif
        ");
    }

    public function down(): void
    {
        $this->db->query("ALTER TABLE hasil_konseling
            DROP COLUMN stressor,
            DROP COLUMN faktor_kerentanan,
            DROP COLUMN faktor_protektif,
            DROP COLUMN strategi_koping
        ");
    }
}
