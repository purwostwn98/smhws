<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class SuperadminFilter implements FilterInterface
{
    /** Path yang juga boleh diakses oleh pimpinan universitas (is_pimpinan_univ), di luar superadmin/admin fakultas. */
    private const PIMPINAN_UNIV_PATHS = [
        'admin/dashboard-univ',
        'admin/dashboard-univ/pdf',
    ];

    public function before(RequestInterface $request, $arguments = null)
    {
        if (! session()->get('is_logged_in')) {
            return redirect()->to(base_url('login'))
                ->with('error', 'Silakan masuk terlebih dahulu.');
        }

        if (session()->get('is_superadmin') || session()->get('is_admin_fakultas')) {
            return;
        }

        $isPimpinanUnivPath = in_array(uri_string(), self::PIMPINAN_UNIV_PATHS, true);
        if ($isPimpinanUnivPath && session()->get('is_pimpinan_univ')) {
            return;
        }

        return redirect()->to(base_url('dashboard'))
            ->with('error', 'Anda tidak memiliki akses ke halaman ini.');
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}
}
