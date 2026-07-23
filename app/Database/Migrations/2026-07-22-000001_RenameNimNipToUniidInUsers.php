<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RenameNimNipToUniidInUsers extends Migration
{
    public function up(): void
    {
        $this->db->query(
            'ALTER TABLE users CHANGE COLUMN nim_nip uniid VARCHAR(30) NULL DEFAULT NULL'
        );
    }

    public function down(): void
    {
        $this->db->query(
            'ALTER TABLE users CHANGE COLUMN uniid nim_nip VARCHAR(30) NULL DEFAULT NULL'
        );
    }
}
