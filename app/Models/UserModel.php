<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table         = 'users';
    protected $primaryKey    = 'id';
    protected $useAutoIncrement = true;
    protected $returnType    = 'array';
    protected $useSoftDeletes = true;

    protected $allowedFields = [
        'name',
        'email',
        'password',
        'role',
        'nim_nip',
        'fakultas',
        'prodi',
        'phone',
        'avatar',
        'is_superadmin',
        'is_admin_fakultas',
        'is_active',
        'email_verified_at',
        'last_login_at',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    protected $validationRules = [
        'name'     => 'required|min_length[3]|max_length[150]',
        'email'    => 'required|valid_email|max_length[150]|is_unique[users.email,id,{id}]',
        'password' => 'required|min_length[8]',
        'role'     => 'required|in_list[mahasiswa,konselor]',
    ];

    protected $validationMessages = [
        'email' => [
            'is_unique' => 'Email sudah terdaftar.',
        ],
        'role' => [
            'in_list' => 'Role hanya boleh mahasiswa atau konselor.',
        ],
    ];

    protected $beforeInsert = ['hashPassword'];
    protected $beforeUpdate = ['hashPassword'];

    protected function hashPassword(array $data): array
    {
        if (isset($data['data']['password'])) {
            $data['data']['password'] = password_hash($data['data']['password'], PASSWORD_BCRYPT);
        }
        return $data;
    }

    // -----------------------------------------------------------------------
    // Scopes / helpers
    // -----------------------------------------------------------------------

    public function mahasiswa(): static
    {
        return $this->where('role', 'mahasiswa');
    }

    public function konselor(): static
    {
        return $this->where('role', 'konselor');
    }

    public function active(): static
    {
        return $this->where('is_active', 1);
    }

    public function superadmins(): static
    {
        return $this->where('is_superadmin', 1);
    }

    public function adminFakultas(string $fakultas): static
    {
        return $this->where('is_admin_fakultas', 1)
                    ->where('fakultas', $fakultas);
    }

    public function findByEmail(string $email): array|null
    {
        return $this->where('email', $email)->first();
    }

    public function updateLastLogin(int $id): void
    {
        $this->update($id, ['last_login_at' => date('Y-m-d H:i:s')]);
    }
}
