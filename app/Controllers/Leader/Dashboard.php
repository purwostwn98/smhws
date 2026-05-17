<?php

namespace App\Controllers\Leader;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class Dashboard extends BaseController
{
    public function index()
    {
        return view('leader/dashboard', [
            'title' => 'Dashboard',
        ]);
    }
}
