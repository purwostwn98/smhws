<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Pilihan "E. Status Penanganan Klien Saat Ini" menjadi:
 * Selesai; Konseling Lanjutan; Rujukan; Terminasi atas kesepakatan bersama; Lainnya.
 * "Follow-up" diganti nama (termasuk data lama), "Monitoring" dinonaktifkan tetapi
 * data lamanya tetap tersimpan sebagai riwayat.
 */
class UbahPilihanStatusPenanganan extends Migration
{
    private const URUTAN_BARU = [
        'Selesai'                            => 1,
        'Konseling Lanjutan'                 => 2,
        'Rujukan'                            => 3,
        'Terminasi atas kesepakatan bersama' => 4,
        'Lainnya'                            => 5,
        'Monitoring'                         => 9,
    ];

    private const URUTAN_LAMA = [
        'Selesai'                            => 1,
        'Monitoring'                         => 2,
        'Follow-up'                          => 3,
        'Rujukan'                            => 4,
        'Terminasi atas kesepakatan bersama' => 5,
        'Lainnya'                            => 6,
    ];

    public function up(): void
    {
        $this->gantiLabel('Follow-up', 'Konseling Lanjutan');
        $this->items()->where('item_label', 'Monitoring')->update(['is_active' => 0]);
        $this->aturUrutan(self::URUTAN_BARU);
        $this->gantiStatusTersimpan('Follow-up', 'Konseling Lanjutan');
    }

    public function down(): void
    {
        $this->gantiLabel('Konseling Lanjutan', 'Follow-up');
        $this->items()->where('item_label', 'Monitoring')->update(['is_active' => 1]);
        $this->aturUrutan(self::URUTAN_LAMA);
        $this->gantiStatusTersimpan('Konseling Lanjutan', 'Follow-up');
    }

    private function items(): \CodeIgniter\Database\BaseBuilder
    {
        return $this->db->table('checklist_items')->where('subsection_key', 'status');
    }

    private function gantiLabel(string $dari, string $ke): void
    {
        $this->items()->where('item_label', $dari)->update(['item_label' => $ke]);
    }

    private function aturUrutan(array $urutan): void
    {
        foreach ($urutan as $label => $no) {
            $this->items()->where('item_label', $label)->update(['item_urutan' => $no]);
        }
    }

    private function gantiStatusTersimpan(string $dari, string $ke): void
    {
        $this->db->query(
            "UPDATE hasil_konseling
                SET rekomendasi = JSON_SET(rekomendasi, '$.status', ?)
              WHERE JSON_UNQUOTE(JSON_EXTRACT(rekomendasi, '$.status')) = ?",
            [$ke, $dari]
        );
    }
}
