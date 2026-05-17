<?php

namespace App\Controllers\Counselor;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class Dashboard extends BaseController
{
    public function index()
    {
        return view('counselor/dashboard', [
            'title' => 'Dashboard',
        ]);
    }
}
