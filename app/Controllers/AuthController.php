<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Libraries\Lib_Cas;

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

    public function loginWithCas(): \CodeIgniter\HTTP\RedirectResponse
    {
        helper(['star', 'sihrd']);

        // Start CI4's session BEFORE phpCAS initialises.
        // phpCAS::client() checks session_id() === "" and skips session_start()
        // when a session is already open — so phpCAS reuses CI4's session instead
        // of opening its own. Without this, phpCAS starts a new session FIRST and
        // CI4's subsequent configure() call then fails with
        // "ini_set(): Session ini settings cannot be changed when a session is active".
        session()->start();

        // Simpan tujuan sebelum diarahkan ke SSO (parameter ?redirect hilang setelah kembali dari CAS)
        $this->simpanTujuan($this->request->getGet('redirect'));

        $cas = new Lib_Cas;
        $cas->forceAuth();
        $casUser = $cas->user();

        $uniid        = $casUser->userlogin;
        $isMahasiswa  = strlen($uniid) === 10;

        if ($isMahasiswa) {
            $profil   = star_get_profil_mahasiswa($uniid) ?? [];
            $nimNip   = $uniid;
            $role     = 'mahasiswa';
            $name     = $profil['Nama']     ?? $uniid;
            $email    = strtolower($uniid) . '@student.ums.ac.id';
            $fakultas = $profil['Fakultas'] ?? null;
            $prodi    = $profil['Prodi']    ?? null;
        } else {
            $profil   = sihrd_get_detail_dosen($uniid) ?? [];
            $rows     = $profil['rows'] ?? [];
            $nimNip   = strtolower($uniid);
            $role     = 'dosen';
            $name     = $rows['namalengkap'] ?? $uniid;
            $email    = $nimNip . '@ums.ac.id';
            $fakultas = $rows['homebase']    ?? null;
            $prodi    = null;
        }

        $model    = new UserModel();
        $existing = $model->where('uniid', $nimNip)->first();

        if (! $existing) {
            $insertId = $model->skipValidation(true)->insert([
                'name'      => $name,
                'email'     => $email,
                'password'  => bin2hex("smhws@2026"),
                'role'      => $role,
                'uniid'   => $nimNip,
                'fakultas'  => $fakultas,
                'prodi'     => $prodi,
                'is_active' => 1,
            ]);
            $existing = $model->find($insertId);
        }

        $model->updateLastLogin($existing['id']);

        session()->set([
            'user_id'           => $existing['id'],
            'user_name'         => $existing['name'],
            'user_email'        => $existing['email'],
            'user_role'         => $existing['role'],
            'user_uniid'      => $existing['uniid'],
            'is_superadmin'     => (bool) $existing['is_superadmin'],
            'is_admin_fakultas' => (bool) $existing['is_admin_fakultas'],
            'is_logged_in'      => true,
            'auth_via_cas'      => true,
        ]);

        return redirect()->to($this->ambilTujuan($existing) ?? $this->redirectAfterLogin($existing));
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
            'user_uniid'      => $user['uniid'] ?? '',
            'is_superadmin'     => (bool) $user['is_superadmin'],
            'is_admin_fakultas' => (bool) $user['is_admin_fakultas'],
            'is_logged_in'      => true,
        ]);

        $model->updateLastLogin($user['id']);

        $this->simpanTujuan($this->request->getPost('redirect'));

        return redirect()->to($this->ambilTujuan($user) ?? $this->redirectAfterLogin($user));
    }

    public function logout(): \CodeIgniter\HTTP\RedirectResponse
    {
        $isCasUser = (bool) session()->get('auth_via_cas');

        session()->destroy();

        if ($isCasUser) {
            $service = urlencode(base_url('login'));
            return redirect()->to('https://auth.ums.ac.id/cas/logout?service=' . $service);
        }

        return redirect()->to(base_url('login'))
            ->with('success', 'Anda telah berhasil keluar.');
    }

    /** Kode ?redirect yang diizinkan → path internal tujuan setelah login. */
    private const TUJUAN_LOGIN = [
        'janji' => 'janji/buat',
        'tanya' => 'tanya/buat',
    ];

    /** Simpan tujuan dari kode ?redirect (hanya kode di whitelist, mencegah open redirect). */
    private function simpanTujuan(?string $kode): void
    {
        if ($kode && isset(self::TUJUAN_LOGIN[$kode])) {
            session()->set('redirect_after_login', self::TUJUAN_LOGIN[$kode]);
        }
    }

    /**
     * Ambil & hapus tujuan setelah login. Diisi oleh simpanTujuan() atau oleh filter
     * (halaman terproteksi yang dibuka sebelum login). Hanya path internal relatif,
     * dan hanya untuk mahasiswa (semua tujuan yang disimpan adalah halaman mahasiswa).
     */
    private function ambilTujuan(array $user): ?string
    {
        $path = session()->get('redirect_after_login');
        session()->remove('redirect_after_login');

        $isAdmin = ! empty($user['is_superadmin']) || ! empty($user['is_admin_fakultas']);
        if ($isAdmin || ($user['role'] ?? null) !== 'mahasiswa') {
            return null;
        }

        if (! is_string($path) || ! preg_match('#^[A-Za-z0-9/_\-]+(\?[A-Za-z0-9=&_\-%]*)?$#', $path) || str_starts_with($path, '/')) {
            return null;
        }
        return base_url($path);
    }

    private function redirectAfterLogin(array $user = null): string
    {
        if ($user === null) {
            $user = [
                'is_superadmin'     => (bool) session()->get('is_superadmin'),
                'is_admin_fakultas' => (bool) session()->get('is_admin_fakultas'),
            ];
        }

        if (! empty($user['is_superadmin']) || ! empty($user['is_admin_fakultas'])) {
            return base_url('admin/dashboard');
        }

        $role = $user['role'] ?? session()->get('user_role');
        if ($role === 'dosen') {
            return base_url('dosen/dashboard');
        }

        return base_url('dashboard');
    }
}
