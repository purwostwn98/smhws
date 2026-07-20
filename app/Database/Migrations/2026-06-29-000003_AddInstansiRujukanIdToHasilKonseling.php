<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddInstansiRujukanIdToHasilKonseling extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('hasil_konseling', [
            'instansi_rujukan_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'default'    => null,
                'after'      => 'ada_rujukan',
            ],
        ]);

        $this->db->query('ALTER TABLE hasil_konseling
            ADD CONSTRAINT fk_hasil_instansi_rujukan
            FOREIGN KEY (instansi_rujukan_id)
            REFERENCES instansi_rujukan(id)
            ON DELETE SET NULL ON UPDATE CASCADE');
    }

    public function down(): void
    {
        $this->db->query('ALTER TABLE hasil_konseling DROP FOREIGN KEY fk_hasil_instansi_rujukan');
        $this->forge->dropColumn('hasil_konseling', 'instansi_rujukan_id');
    }
}
