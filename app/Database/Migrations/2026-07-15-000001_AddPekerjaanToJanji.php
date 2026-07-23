<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPekerjaanToJanji extends Migration
{
    public function up(): void
    {
        $this->db->query("ALTER TABLE janji ADD COLUMN pekerjaan VARCHAR(150) NULL DEFAULT NULL AFTER domisili");
    }

    public function down(): void
    {
        $this->db->query("ALTER TABLE janji DROP COLUMN pekerjaan");
    }
}
