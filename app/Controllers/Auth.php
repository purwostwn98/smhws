<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class Auth extends BaseController
{
    private $users;

    public function __construct()
    {
        // Mock data users
        $this->users = [
            [
                'id'       => '1',
                'username' => 'admin',
                'name'     => 'Admin SMHWS UMS',
                'email'    => 'admin@ums.ac.id',
                'password' => password_hash('password123', PASSWORD_DEFAULT),
                'role'     => 'admin'
            ],
            [
                'id'       => '2',
                'username' => 'student',
                'name'     => 'Mahasiswa UMS',
                'email'    => 'student@ums.ac.id',
                'password' => password_hash('password123', PASSWORD_DEFAULT),
                'role'     => 'student'
            ],
            [
                'id'       => '3',
                'username' => 'counselor',
                'name'     => 'Konselor SMHWS UMS',
                'email'    => 'counselor@ums.ac.id',
                'password' => password_hash('password123', PASSWORD_DEFAULT),
                'role'     => 'counselor'
            ],
            [
                'id'       => '4',
                'username' => 'leader',
                'name'     => 'Pimpinan SMHWS UMS',
                'email'    => 'leader@ums.ac.id',
                'password' => password_hash('password123', PASSWORD_DEFAULT),
                'role'     => 'leader'
            ],
        ];
    }

    public function index()
    {
        // if (session()->get('userdata') && session()->get('userdata')['is_logged_in']) {
        //     return $this->redirectByRole(session()->get('userdata')['role']);
        // }
        return view('auth/login');
    }

    public function login()
    {
        $login = $this->request->getPost('email-username');
        $password = $this->request->getPost('password');

        $user = null;
        foreach ($this->users as $u) {
            if ($u['email'] === $login || $u['username'] === $login) {
                $user = $u;
                break;
            }
        }

        if (!$user) {
            return redirect()->back()->withInput()->with('error', 'Mohon maaf pengguna tidak ditemukan');
        }

        if (!password_verify($password, $user['password'])) {
            return redirect()->back()->withInput()->with('error', 'Kata sandi yang Anda masukkan salah');
        }

        $userdata = [
            'id'           => $user['id'],
            'username'     => $user['username'],
            'name'         => $user['name'],
            'email'        => $user['email'],
            'role'         => $user['role'],
            'is_logged_in' => true,
        ];

        session()->set('userdata', $userdata);

        switch ($user['role']) {
            case 'admin':
                return redirect()->to(route_to('admin.dashboard'));
            case 'student':
                return redirect()->to(route_to('student.dashboard'));
            case 'counselor':
                return redirect()->to(route_to('counselor.dashboard'));
            case 'leader':
                return redirect()->to(route_to('leader.dashboard'));
            default:
                return redirect()->to(route_to('login'))->with('error', 'Role pengguna tidak dikenali');
        }
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to(route_to('login'))->with('success', 'Anda telah berhasil logout');
    }
}
