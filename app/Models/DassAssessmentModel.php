<?php

namespace App\Models;

use CodeIgniter\Model;

class DassAssessmentModel extends Model
{
    protected $table         = 'dass_assessments';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useSoftDeletes = true;

    protected $allowedFields = [
        'janji_id', 'user_id', 'konselor_id', 'tanggal_pengisian', 'pemeriksa', 'catatan_konselor',
        'skor_depresi_raw', 'skor_depresi', 'kategori_depresi',
        'skor_anxiety_raw', 'skor_anxiety', 'kategori_anxiety',
        'skor_stress_raw',  'skor_stress',  'kategori_stress',
        'is_reviewed', 'reviewed_by', 'reviewed_at',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // -----------------------------------------------------------------------
    // Tabel ambang batas kategori (Sumber: DASS-21 manual)
    // -----------------------------------------------------------------------

    private const THRESHOLD = [
        'depresi' => [
            'normal'      => [0,   9],
            'ringan'      => [10, 13],
            'sedang'      => [14, 20],
            'berat'       => [21, 27],
            'sangat_berat'=> [28, PHP_INT_MAX],
        ],
        'anxiety' => [
            'normal'      => [0,   7],
            'ringan'      => [8,   9],
            'sedang'      => [10, 14],
            'berat'       => [15, 19],
            'sangat_berat'=> [20, PHP_INT_MAX],
        ],
        'stress' => [
            'normal'      => [0,  14],
            'ringan'      => [15, 18],
            'sedang'      => [19, 25],
            'berat'       => [26, 33],
            'sangat_berat'=> [34, PHP_INT_MAX],
        ],
    ];

    // -----------------------------------------------------------------------
    // Hitung kategori dari skor akhir (sudah dikali 2)
    // -----------------------------------------------------------------------

    public static function hitungKategori(string $subscale, int $skor): string
    {
        $key = match($subscale) {
            'D' => 'depresi',
            'A' => 'anxiety',
            'S' => 'stress',
            default => throw new \InvalidArgumentException("Subscale tidak valid: $subscale"),
        };

        foreach (self::THRESHOLD[$key] as $kategori => [$min, $max]) {
            if ($skor >= $min && $skor <= $max) {
                return $kategori;
            }
        }

        return 'sangat_berat';
    }

    // -----------------------------------------------------------------------
    // Simpan asesmen lengkap dari array jawaban [item_id => jawaban (0-3)]
    // -----------------------------------------------------------------------

    public function simpanAsesmenLengkap(array $data, array $jawaban): int
    {
        // Hitung skor per subscale
        $itemModel = new DassItemModel();
        $items     = $itemModel->findAll();
        $itemMap   = array_column($items, null, 'id'); // id => row

        $raw = ['D' => 0, 'A' => 0, 'S' => 0];
        foreach ($jawaban as $itemId => $nilai) {
            $subscale = $itemMap[$itemId]['subscale'] ?? null;
            if ($subscale) {
                $raw[$subscale] += (int) $nilai;
            }
        }

        // Skor akhir = raw × 2
        $data['skor_depresi_raw'] = $raw['D'];
        $data['skor_depresi']     = $raw['D'] * 2;
        $data['kategori_depresi'] = self::hitungKategori('D', $raw['D'] * 2);

        $data['skor_anxiety_raw'] = $raw['A'];
        $data['skor_anxiety']     = $raw['A'] * 2;
        $data['kategori_anxiety'] = self::hitungKategori('A', $raw['A'] * 2);

        $data['skor_stress_raw']  = $raw['S'];
        $data['skor_stress']      = $raw['S'] * 2;
        $data['kategori_stress']  = self::hitungKategori('S', $raw['S'] * 2);

        $assessmentId = $this->insert($data, true);

        // Simpan jawaban per item
        $jawabanModel = new DassJawabanModel();
        $jawabanModel->simpanJawaban($assessmentId, $jawaban, $itemMap);

        return $assessmentId;
    }

    // -----------------------------------------------------------------------
    // Scopes / helpers
    // -----------------------------------------------------------------------

    public function byUser(int $userId): static
    {
        return $this->where('user_id', $userId)->orderBy('tanggal_pengisian', 'DESC');
    }

    public function belumDitinjau(): static
    {
        return $this->where('is_reviewed', 0);
    }

    public function withUser(): static
    {
        return $this->select('dass_assessments.*, users.name, users.uniid, users.fakultas, users.prodi')
                    ->join('users', 'users.id = dass_assessments.user_id');
    }

    /** Tandai asesmen sudah ditinjau konselor. */
    public function tandaiDitinjau(int $id, int $reviewerId, string $catatan = ''): void
    {
        $this->update($id, [
            'is_reviewed'      => 1,
            'reviewed_by'      => $reviewerId,
            'reviewed_at'      => date('Y-m-d H:i:s'),
            'catatan_konselor' => $catatan,
        ]);
    }

    /** Statistik ringkasan per mahasiswa. */
    public function ringkasanUser(int $userId): array
    {
        $rows = $this->where('user_id', $userId)
                     ->orderBy('tanggal_pengisian', 'DESC')
                     ->findAll();

        if (empty($rows)) {
            return [];
        }

        $last = $rows[0];
        return [
            'total_pengisian'   => count($rows),
            'terakhir'          => $last,
            'kategori_depresi'  => $last['kategori_depresi'],
            'kategori_anxiety'  => $last['kategori_anxiety'],
            'kategori_stress'   => $last['kategori_stress'],
            'perlu_perhatian'   => in_array('berat', [$last['kategori_depresi'], $last['kategori_anxiety'], $last['kategori_stress']])
                                || in_array('sangat_berat', [$last['kategori_depresi'], $last['kategori_anxiety'], $last['kategori_stress']]),
        ];
    }

    // -----------------------------------------------------------------------
    // Label tampilan
    // -----------------------------------------------------------------------

    public static function labelKategori(string $kategori): string
    {
        return match($kategori) {
            'normal'       => 'Normal',
            'ringan'       => 'Ringan',
            'sedang'       => 'Sedang',
            'berat'        => 'Berat',
            'sangat_berat' => 'Sangat Berat',
            default        => $kategori,
        };
    }

    public static function badgeKelas(string $kategori): string
    {
        return match($kategori) {
            'normal'       => 'bg-label-success',
            'ringan'       => 'bg-label-info',
            'sedang'       => 'bg-label-warning',
            'berat'        => 'bg-label-danger',
            'sangat_berat' => 'bg-danger text-white',
            default        => 'bg-label-secondary',
        };
    }
}
