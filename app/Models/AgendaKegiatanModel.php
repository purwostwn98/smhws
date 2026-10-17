<?php

namespace App\Models;

use CodeIgniter\Model;

class AgendaKegiatanModel extends Model
{
    protected $table      = 'agenda_kegiatan';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'jenis', 'nama_kegiatan', 'deskripsi', 'tema', 'narasumber', 'poster',
        'tanggal_mulai', 'tanggal_selesai', 'lokasi',
        'biaya_tipe', 'biaya_nominal', 'kontak',
        'link_pendaftaran', 'batas_pendaftaran', 'status',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public const JENIS = [
        'psychoeducation' => 'Psychoeducation',
        'webinar'         => 'Webinar',
        'workshop'        => 'Workshop',
    ];

    /** Semua agenda untuk admin: terbaru di atas. */
    public function getAll(): array
    {
        return $this->orderBy('tanggal_mulai', 'DESC')->findAll();
    }

    /** Kegiatan yang belum berakhir, terdekat dulu. */
    public function akanDatang(?string $jenis = null): array
    {
        return $this->filterJenis($jenis)
            ->where('COALESCE(tanggal_selesai, tanggal_mulai) >=', date('Y-m-d H:i:s'))
            ->orderBy('tanggal_mulai', 'ASC')
            ->findAll();
    }

    /** Kegiatan yang sudah berakhir, terbaru dulu. */
    public function telahSelesai(?string $jenis = null): array
    {
        return $this->filterJenis($jenis)
            ->where('COALESCE(tanggal_selesai, tanggal_mulai) <', date('Y-m-d H:i:s'))
            ->orderBy('tanggal_mulai', 'DESC')
            ->findAll();
    }

    private function filterJenis(?string $jenis): static
    {
        if ($jenis !== null && isset(self::JENIS[$jenis])) {
            $this->where('jenis', $jenis);
        }
        return $this;
    }

    /** Kegiatan sudah berakhir? */
    public static function sudahSelesai(array $row): bool
    {
        $akhir = $row['tanggal_selesai'] ?: $row['tanggal_mulai'];
        return strtotime($akhir) < time();
    }

    /**
     * Status pendaftaran sebenarnya: ditutup jika diatur ditutup, batas pendaftaran lewat,
     * atau kegiatan sudah berakhir.
     */
    public static function statusEfektif(array $row): string
    {
        if ($row['status'] === 'ditutup') return 'ditutup';
        if (! empty($row['batas_pendaftaran']) && strtotime($row['batas_pendaftaran']) < time()) return 'ditutup';
        if (self::sudahSelesai($row)) return 'ditutup';
        return 'dibuka';
    }
}
