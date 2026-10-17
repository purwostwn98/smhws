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
        $timPsikolog = (new KonselorModel())->withUser()->available()->findAll();

        return view('home/index', ['timPsikolog' => $timPsikolog]);
    }
}
