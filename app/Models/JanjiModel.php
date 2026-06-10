<?php

namespace App\Models;

use CodeIgniter\Model;

class JanjiModel extends Model
{
    protected $table         = 'janji';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useSoftDeletes = true;

    protected $allowedFields = [
        'user_id', 'konselor_id',
        'jenis_kelamin', 'usia', 'agama', 'semester',
        'dosen_pa', 'domisili', 'status_pernikahan',
        'pernah_konseling_smhws', 'metode',
        'jadwal_pilihan', 'konselor_pilihan',
        'tema_konseling', 'keluhan_utama', 'mulai_keluhan', 'upaya_dilakukan',
        'status', 'tanggal_konseling', 'jam_konseling',
        'lokasi_link', 'catatan_admin',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    protected $beforeInsert = ['encodeJsonFields'];
    protected $beforeUpdate = ['encodeJsonFields'];
    protected $afterFind    = ['decodeJsonFields'];

    protected function encodeJsonFields(array $data): array
    {
        $fields = ['jadwal_pilihan', 'konselor_pilihan'];
        foreach ($fields as $f) {
            if (isset($data['data'][$f]) && is_array($data['data'][$f])) {
                $data['data'][$f] = json_encode($data['data'][$f]);
            }
        }
        return $data;
    }

    protected function decodeJsonFields(array $data): array
    {
        if (empty($data['data'])) return $data;

        $fields = ['jadwal_pilihan', 'konselor_pilihan'];

        $decode = static function (array $row) use ($fields): array {
            foreach ($fields as $f) {
                if (isset($row[$f]) && is_string($row[$f])) {
                    $decoded = json_decode($row[$f], true);
                    // Jika masih string (double-encoded), decode sekali lagi
                    if (is_string($decoded)) {
                        $decoded = json_decode($decoded, true);
                    }
                    $row[$f] = is_array($decoded) ? $decoded : [];
                }
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

    protected $validationRules = [
        'jenis_kelamin'  => 'required|in_list[laki-laki,perempuan]',
        'usia'           => 'required|is_natural|greater_than[14]|less_than[100]',
        'agama'          => 'required|min_length[3]',
        'semester'       => 'required|is_natural|greater_than[0]|less_than[20]',
        'metode'         => 'required|in_list[offline,online,hybrid]',
        'tema_konseling' => 'required|in_list[akademik,keorganisasian,pengembangan_diri,relasi,pribadi,keluarga,lainnya]',
        'keluhan_utama'  => 'required|min_length[20]',
    ];

    // -----------------------------------------------------------------------

    public function byUser(int $userId): static
    {
        return $this->where('user_id', $userId)->orderBy('created_at', 'DESC');
    }

    public function mendatang(int $userId): array
    {
        return $this->where('user_id', $userId)
                    ->whereIn('status', ['menunggu', 'dikonfirmasi', 'berlangsung'])
                    ->orderBy('tanggal_konseling', 'ASC')
                    ->findAll();
    }

    public function withDetail(): static
    {
        return $this->select('janji.*, users.name, users.nim_nip, users.email, users.phone, users.fakultas, users.prodi')
                    ->join('users', 'users.id = janji.user_id');
    }

    public function ubahStatus(int $id, string $status): void
    {
        $this->update($id, ['status' => $status]);
    }
}
