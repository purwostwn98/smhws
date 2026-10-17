<?php

namespace App\Controllers;

use App\Models\KonselorModel;

class Home extends BaseController
{
    public function index(): string
    {
        return view('home/maintenance');
    }

    /** Halaman landing (home/index) — sementara diakses lewat /landing selama beranda masih maintenance. */
    public function landing(): string
    {
        $timPsikolog = (new KonselorModel())->withUser()
                                            ->available()
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

            $result[] = [
                'nama' => rtrim(pathinfo($file, PATHINFO_FILENAME), '.'),
                'url'  => base_url(rtrim($urlPrefix, '/') . '/' . rawurlencode(basename($file))) . '?v=' . filemtime($file),
            ];
        }

        return $result;
    }
}
