<?php

namespace App\Controllers;

use App\Models\KonselorModel;

class Home extends BaseController
{
    public function index(): string
    {
        $konselorModel = new KonselorModel();
        $timPsikolog   = $konselorModel->withUser()
                                       ->where('konselor.is_available', 1)
                                       ->orderBy('konselor.rating', 'DESC')
                                       ->findAll();

        return view('home/index', [
            'timPsikolog' => $timPsikolog,
            'ketua'       => $this->scanFotoFolder(FCPATH . 'myimg/ketua/', 'myimg/ketua/'),
            'staff'       => $this->scanFotoFolder(FCPATH . 'myimg/staff/', 'myimg/staff/'),
        ]);
    }

    private function scanFotoFolder(string $dir, string $urlPrefix): array
    {
        if (! is_dir($dir)) return [];

        $result = [];
        $exts   = ['jpg', 'jpeg', 'png', 'webp'];

        foreach (glob($dir . '*') as $file) {
            if (! is_file($file)) continue;
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (! in_array($ext, $exts, true)) continue;

            $filename = pathinfo($file, PATHINFO_FILENAME);
            // Hapus trailing dot jika ada (misal "Dr. Usmi., M.Si." → tetap)
            $nama = rtrim($filename, '.');

            $result[] = [
                'nama' => $nama,
                'url'  => base_url(rtrim($urlPrefix, '/') . '/' . rawurlencode(basename($file))),
            ];
        }

        return $result;
    }
}
