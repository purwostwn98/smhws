<?php

namespace App\Controllers;

/**
 * Menu Mental Health Check: memilih jenis assessment.
 * Logika tiap assessment ada di App\Controllers\Skrining\*.
 */
class SkriningController extends BaseController
{
    public function pilih(): string
    {
        return view('skrining/pilih', ['daftarJenis' => config('Assessment')->jenis]);
    }
}
