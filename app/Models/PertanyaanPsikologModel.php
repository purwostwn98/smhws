<?php

namespace App\Models;

use CodeIgniter\Model;

class PertanyaanPsikologModel extends Model
{
    protected $table      = 'pertanyaan_psikolog';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'user_id', 'nama', 'prodi', 'fakultas', 'jenis_kelamin', 'semester',
        'topik_id', 'judul', 'pertanyaan', 'izin_publikasi',
        'status', 'alasan_ditolak', 'konselor_id', 'ditugaskan_oleh', 'ditugaskan_at',
        'jawaban', 'dijawab_at', 'dibaca_mahasiswa_at', 'learn_konten_id',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public const STATUS = [
        'menunggu'   => ['Menunggu',   'bg-label-warning'],
        'ditugaskan' => ['Diproses',   'bg-label-info'],
        'dijawab'    => ['Dijawab',    'bg-label-success'],
        'ditolak'    => ['Ditolak',    'bg-label-secondary'],
    ];

    /** Batas pertanyaan aktif (belum dijawab) per mahasiswa. */
    public const MAKS_AKTIF = 3;

    /** Pilih kolom pertanyaan + nama psikolog, topik, dan status Learn. */
    public function lengkap(): static
    {
        return $this->select('pertanyaan_psikolog.*,
                k.gelar_depan, k.gelar_belakang, k.foto AS konselor_foto, ku.name AS konselor_nama_user,
                t.nama AS topik_nama, lk.status AS learn_status, lk.slug AS learn_slug')
            ->join('konselor k', 'k.id = pertanyaan_psikolog.konselor_id', 'left')
            ->join('users ku', 'ku.id = k.user_id', 'left')
            ->join('learn_topik t', 't.id = pertanyaan_psikolog.topik_id', 'left')
            ->join('learn_konten lk', 'lk.id = pertanyaan_psikolog.learn_konten_id', 'left');
    }

    public function milikUser(int $userId): static
    {
        return $this->lengkap()->where('pertanyaan_psikolog.user_id', $userId);
    }

    public function untukKonselor(int $konselorId): static
    {
        return $this->lengkap()->where('pertanyaan_psikolog.konselor_id', $konselorId);
    }

    /** [status => jumlah], opsional dibatasi per konselor. */
    public function hitungPerStatus(?int $konselorId = null): array
    {
        $b = $this->db->table($this->table)->select('status, COUNT(*) AS n')->groupBy('status');
        if ($konselorId) $b->where('konselor_id', $konselorId);

        $hasil = array_fill_keys(array_keys(self::STATUS), 0);
        foreach ($b->get()->getResultArray() as $r) {
            $hasil[$r['status']] = (int) $r['n'];
        }
        return $hasil;
    }

    public function jumlahAktifUser(int $userId): int
    {
        return $this->where('user_id', $userId)->whereIn('status', ['menunggu', 'ditugaskan'])->countAllResults();
    }

    /** Jawaban yang belum dibuka mahasiswa (untuk kartu dashboard). */
    public function jawabanBaru(int $userId): array
    {
        return $this->where('user_id', $userId)
            ->where('status', 'dijawab')
            ->where('dibaca_mahasiswa_at', null)
            ->orderBy('dijawab_at', 'DESC')
            ->findAll();
    }

    /** Nama lengkap psikolog dari baris hasil lengkap(). */
    public static function namaPsikolog(array $row): string
    {
        if (empty($row['konselor_nama_user'])) return '';
        return KonselorModel::namaLengkap([
            'gelar_depan'    => $row['gelar_depan'] ?? null,
            'gelar_belakang' => $row['gelar_belakang'] ?? null,
            'name'           => $row['konselor_nama_user'],
        ]);
    }

    public static function badge(string $status): string
    {
        [$label, $kelas] = self::STATUS[$status] ?? [$status, 'bg-label-secondary'];
        return '<span class="badge ' . $kelas . '">' . esc($label) . '</span>';
    }
}
