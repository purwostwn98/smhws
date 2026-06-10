<?php

namespace App\Models;

use CodeIgniter\Model;

class DassJawabanModel extends Model
{
    protected $table      = 'dass_jawaban';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'assessment_id',
        'item_id',
        'nomor_item',
        'subscale',
        'jawaban',
        'created_at',
    ];

    // Timestamp diisi manual di rows — nonaktifkan agar insertBatch tidak konflik
    protected $useTimestamps = false;

    // -----------------------------------------------------------------------

    /**
     * Simpan 21 jawaban sekaligus.
     *
     * @param int   $assessmentId
     * @param array $jawaban   [item_id => nilai (0-3)]
     * @param array $itemMap   [item_id => ['subscale'=>..., 'nomor'=>...]]
     */
    public function simpanJawaban(int $assessmentId, array $jawaban, array $itemMap): void
    {
        $now  = date('Y-m-d H:i:s');
        $rows = [];

        foreach ($jawaban as $itemId => $nilai) {
            $item = $itemMap[$itemId] ?? null;
            if (! $item) continue;

            $rows[] = [
                'assessment_id' => $assessmentId,
                'item_id'       => (int) $itemId,
                'nomor_item'    => (int) $item['nomor'],
                'subscale'      => $item['subscale'],
                'jawaban'       => (int) $nilai,
                'created_at'    => $now,
            ];
        }

        if (! empty($rows)) {
            $this->insertBatch($rows);
        }
    }

    /** Ambil semua jawaban suatu asesmen, join dengan teks pernyataan. */
    public function jawabanLengkap(int $assessmentId): array
    {
        return $this->select('dass_jawaban.*, dass_items.nomor, dass_items.pernyataan, dass_items.subscale')
                    ->join('dass_items', 'dass_items.id = dass_jawaban.item_id')
                    ->where('assessment_id', $assessmentId)
                    ->orderBy('dass_items.nomor')
                    ->findAll();
    }
}
