<?php

namespace App\Controllers;

use App\Models\JanjiModel;
use App\Models\KonselorModel;
use App\Models\UserModel;

class DashboardController extends BaseController
{
    public function index(): string|\CodeIgniter\HTTP\RedirectResponse
    {
        if (! session()->get('is_logged_in')) {
            return redirect()->to(base_url('login'))->with('error', 'Silakan masuk terlebih dahulu.');
        }

        $role         = session()->get('user_role');
        $isSuperadmin = session()->get('is_superadmin');
        $isAdminFak   = session()->get('is_admin_fakultas');

        if ($isSuperadmin || $isAdminFak) {
            return redirect()->to(base_url('admin/dashboard'));
        }

        if ($role === 'konselor') {
            return redirect()->to(base_url('konselor/dashboard'));
        }

        return $this->mahasiswaDashboard();
    }

    public function mahasiswaDashboard(): string|\CodeIgniter\HTTP\RedirectResponse
    {
        if (! session()->get('is_logged_in')) {
            return redirect()->to(base_url('login'))->with('error', 'Silakan masuk terlebih dahulu.');
        }

        $userId    = session()->get('user_id');
        $userModel = new UserModel();
        $janjiModel = new JanjiModel();

        $user        = $userModel->find($userId);
        $semuaJanji  = $janjiModel->byUser($userId)->findAll();

        $stats = [
            'janji_aktif'    => 0,
            'sesi_selesai'   => 0,
            'janji_menunggu' => 0,
            'total_sesi'     => count($semuaJanji),
        ];
        foreach ($semuaJanji as $j) {
            if (in_array($j['status'], ['dikonfirmasi', 'berlangsung'])) $stats['janji_aktif']++;
            if ($j['status'] === 'selesai')   $stats['sesi_selesai']++;
            if ($j['status'] === 'menunggu')  $stats['janji_menunggu']++;
        }

        $janji_mendatang = $janjiModel->mendatang($userId);

        // Bangun lookup konselor untuk janji mendatang
        $konselorMap = [];
        if (! empty($janji_mendatang)) {
            $konselorIds = array_filter(array_unique(array_column($janji_mendatang, 'konselor_id')));
            if (! empty($konselorIds)) {
                $konselorModel = new KonselorModel();
                $rows = $konselorModel
                    ->select('konselor.id, users.name, konselor.gelar_depan, konselor.gelar_belakang')
                    ->join('users', 'users.id = konselor.user_id')
                    ->whereIn('konselor.id', $konselorIds)
                    ->findAll();
                foreach ($rows as $k) {
                    $konselorMap[$k['id']] = KonselorModel::namaLengkap($k);
                }
            }
        }

        return view('dashboard/mahasiswa', [
            'user'            => $user,
            'stats'           => $stats,
            'janji_mendatang' => $janji_mendatang,
            'konselorMap'     => $konselorMap,
        ]);
    }
}
