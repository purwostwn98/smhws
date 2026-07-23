<?php

namespace App\Models;

use CodeIgniter\Model;

class KonselorModel extends Model
{
    protected $table         = 'konselor';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useSoftDeletes = true;

    protected $allowedFields = [
        'user_id',
        'nip',
        'uniid',
        'gelar_depan',
        'gelar_belakang',
        'spesialisasi',
        'bio',
        'foto',
        'no_str',
        'tahun_pengalaman',
        'max_pasien_per_hari',
        'is_available',
        'rating',
        'total_sesi',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Decode spesialisasi JSON manual — hindari JsonCast TypeError di CI4 4.7
    protected $afterFind = ['decodeSpesialisasi'];

    protected function decodeSpesialisasi(array $data): array
    {
        if (empty($data['data'])) return $data;

        $decode = static function (array $row): array {
            if (isset($row['spesialisasi']) && is_string($row['spesialisasi'])) {
                $decoded = json_decode($row['spesialisasi'], true);
                $row['spesialisasi'] = is_array($decoded) ? $decoded : [];
            }
            return $row;
        };

        if ($data['singleton']) {
            $data['data'] = $decode($data['data']);
        } else {
            $data['data'] = array_map($decode, $data['data']);
        }

        return $data;
    }

    // -----------------------------------------------------------------------
    // Scopes
    // -----------------------------------------------------------------------

    public function available(): static
    {
        return $this->where('konselor.is_available', 1);
    }

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    /** Ambil profil konselor beserta data user (nama, email, foto user). */
    public function withUser(): static
    {
        return $this
            ->select('
                konselor.id, konselor.user_id, konselor.nip, konselor.uniid,
                konselor.gelar_depan, konselor.gelar_belakang, konselor.spesialisasi,
                konselor.bio, konselor.foto, konselor.no_str, konselor.tahun_pengalaman,
                konselor.max_pasien_per_hari, konselor.is_available,
                konselor.created_at, konselor.updated_at, konselor.deleted_at,
                users.name, users.email, users.phone, users.fakultas,
                (SELECT COUNT(*)
                 FROM janji
                 WHERE janji.konselor_id = konselor.id
                   AND janji.status = "selesai"
                   AND janji.deleted_at IS NULL) AS total_sesi,
                (SELECT ROUND(AVG(fk.rating), 2)
                 FROM feedback_konseling fk
                 JOIN janji j ON j.id = fk.janji_id
                 WHERE j.konselor_id = konselor.id) AS rating
            ')
            ->join('users', 'users.id = konselor.user_id');
    }

    /** Nama lengkap dengan gelar: "Dr. Siti Rahayu, M.Psi." */
    public static function namaLengkap(array $konselor): string
    {
        $gelarDepan = $konselor['gelar_depan'] ? $konselor['gelar_depan'] . ' ' : '';
        $gelarBelakang = $konselor['gelar_belakang'] ? ', ' . $konselor['gelar_belakang'] : '';
        return trim($gelarDepan . ($konselor['name'] ?? '') . $gelarBelakang);
    }

    /** Cari profil konselor by user_id. */
    public function findByUserId(int $userId): array|null
    {
        return $this->where('user_id', $userId)->first();
    }

    /** Cari konselor dengan jadwal pada hari tertentu. */
    public function withJadwal(string $hari): static
    {
        return $this->select('konselor.*, konselor_jadwal.jam_mulai, konselor_jadwal.jam_selesai, konselor_jadwal.kuota')
                    ->join('konselor_jadwal', 'konselor_jadwal.konselor_id = konselor.id')
                    ->where('konselor_jadwal.hari', $hari)
                    ->where('konselor_jadwal.is_active', 1);
    }

    /** Update rating rata-rata konselor. */
    public function updateRating(int $konselorId, float $rating): void
    {
        $this->update($konselorId, ['rating' => round($rating, 2)]);
    }

    /** Tambah counter total sesi. */
    public function incrementSesi(int $konselorId): void
    {
        $this->set('total_sesi', 'total_sesi + 1', false)
             ->where('id', $konselorId)
             ->update();
    }
}
