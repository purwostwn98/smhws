<?php

namespace App\Controllers;

use App\Models\UserModel;

class AuthController extends BaseController
{
    public function loginPage(): string|\CodeIgniter\HTTP\RedirectResponse
    {
        if (session()->get('is_logged_in')) {
            return redirect()->to($this->redirectAfterLogin());
        }

        $redirect = $this->request->getGet('redirect') ?? '';
        return view('auth/login', ['redirect' => $redirect]);
    }

    public function login(): \CodeIgniter\HTTP\RedirectResponse
    {
        $rules = [
            'email'    => 'required|valid_email',
            'password' => 'required|min_length[6]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $model = new UserModel();
        $user  = $model->findByEmail($this->request->getPost('email'));

        if (! $user || ! password_verify($this->request->getPost('password'), $user['password'])) {
            return redirect()->back()->withInput()
                ->with('error', 'Email atau password salah. Silakan coba lagi.');
        }

        if (! $user['is_active']) {
            return redirect()->back()->withInput()
                ->with('error', 'Akun Anda tidak aktif. Hubungi admin SMHWS.');
        }

        session()->set([
            'user_id'           => $user['id'],
            'user_name'         => $user['name'],
            'user_email'        => $user['email'],
            'user_role'         => $user['role'],
            'is_superadmin'     => (bool) $user['is_superadmin'],
            'is_admin_fakultas' => (bool) $user['is_admin_fakultas'],
            'is_logged_in'      => true,
        ]);

        $model->updateLastLogin($user['id']);

        $redirect = $this->request->getPost('redirect');
        if ($redirect === 'janji') {
            return redirect()->to(base_url('/#konsultasi'));
        }

        return redirect()->to($this->redirectAfterLogin($user));
    }

    public function logout(): \CodeIgniter\HTTP\RedirectResponse
    {
        session()->destroy();
        return redirect()->to(base_url('login'))
            ->with('success', 'Anda telah berhasil keluar.');
    }

    private function redirectAfterLogin(array $user): string
    {
        if ($user['is_superadmin'] || $user['is_admin_fakultas']) {
            return base_url('admin/dashboard');
        }
        return base_url('dashboard');
    }
}
