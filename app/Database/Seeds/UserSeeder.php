<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $now = date('Y-m-d H:i:s');

        $users = [
            // Superadmin
            [
                'name'              => 'Super Admin SMHWS',
                'email'             => 'admin@smhws.ums.ac.id',
                'password'          => password_hash('Admin@smhws2026', PASSWORD_BCRYPT),
                'role'              => 'konselor',
                'nim_nip'           => null,
                'fakultas'          => null,
                'prodi'             => null,
                'phone'             => '081234567890',
                'is_superadmin'     => 1,
                'is_admin_fakultas' => 0,
                'is_active'         => 1,
                'email_verified_at' => $now,
                'created_at'        => $now,
                'updated_at'        => $now,
            ],
            // Konselor contoh
            [
                'name'              => 'Dr. Siti Rahayu, M.Psi.',
                'email'             => 's.rahayu@smhws.ums.ac.id',
                'password'          => password_hash('Konselor@2026', PASSWORD_BCRYPT),
                'role'              => 'konselor',
                'nim_nip'           => '197801012005012001',
                'fakultas'          => null,
                'prodi'             => null,
                'phone'             => '081298765432',
                'is_superadmin'     => 0,
                'is_admin_fakultas' => 0,
                'is_active'         => 1,
                'email_verified_at' => $now,
                'created_at'        => $now,
                'updated_at'        => $now,
            ],
            // Admin Fakultas contoh
            [
                'name'              => 'Ahmad Hidayat, S.Psi.',
                'email'             => 'a.hidayat@smhws.ums.ac.id',
                'password'          => password_hash('Konselor@2026', PASSWORD_BCRYPT),
                'role'              => 'konselor',
                'nim_nip'           => '198505152010011003',
                'fakultas'          => 'Fakultas Psikologi',
                'prodi'             => null,
                'phone'             => '082112345678',
                'is_superadmin'     => 0,
                'is_admin_fakultas' => 0,
                'is_active'         => 1,
                'email_verified_at' => $now,
                'created_at'        => $now,
                'updated_at'        => $now,
            ],
            // Mahasiswa contoh
            [
                'name'              => 'Alika Bernanda Irfan',
                'email'             => 'd600200065@student.ums.ac.id',
                'password'          => password_hash('d600200065', PASSWORD_BCRYPT),
                'role'              => 'mahasiswa',
                'nim_nip'           => 'D600200065',
                'fakultas'          => 'Fakultas Teknik',
                'prodi'             => 'Program Studi Teknik Industri',
                'phone'             => '085647053296',
                'is_superadmin'     => 0,
                'is_admin_fakultas' => 0,
                'is_active'         => 1,
                'email_verified_at' => $now,
                'created_at'        => $now,
                'updated_at'        => $now,
            ],
            [
                'name'              => 'Budi Santoso',
                'email'             => 'budi.santoso@student.ums.ac.id',
                'password'          => password_hash('Mahasiswa@2026', PASSWORD_BCRYPT),
                'role'              => 'mahasiswa',
                'nim_nip'           => 'B100220001',
                'fakultas'          => 'Fakultas Ekonomi dan Bisnis',
                'prodi'             => 'Manajemen',
                'phone'             => '089876543210',
                'is_superadmin'     => 0,
                'is_admin_fakultas' => 0,
                'is_active'         => 1,
                'email_verified_at' => $now,
                'created_at'        => $now,
                'updated_at'        => $now,
            ],
        ];

        $this->db->table('users')->insertBatch($users);
    }
}
