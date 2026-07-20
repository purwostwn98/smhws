<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

// Auth Routes
$routes->get('/login', 'Auth::index', ['as' => 'login']);
$routes->post('/login', 'Auth::login');
$routes->get('/logout', 'Auth::logout', ['as' => 'logout']);

// Admin Routes
$routes->group('admin', ['filter' => 'role:admin'], function ($routes) {
    $routes->get('dashboard', 'Admin\Dashboard::index', ['as' => 'admin.dashboard']);
});

// Counselor Routes
$routes->group('counselor', ['filter' => 'role:counselor'], function ($routes) {
    $routes->get('dashboard', 'Counselor\Dashboard::index', ['as' => 'counselor.dashboard']);
});

// Leader Routes
$routes->group('leader', ['filter' => 'role:leader'], function ($routes) {
    $routes->get('dashboard', 'Leader\Dashboard::index', ['as' => 'leader.dashboard']);
});

// Student Routes
$routes->group('student', ['filter' => 'role:student'], function ($routes) {
    $routes->get('dashboard', 'Student\Dashboard::index', ['as' => 'student.dashboard']);
});

$routes->set404Override(function () {
    return view('error_404');
});

$routes->get('get-session', function () {
    var_dump(session()->get());
    die();
});
// Auth
$routes->get('login',  'AuthController::loginPage');
$routes->post('login', 'AuthController::login');
$routes->get('logout', 'AuthController::logout');

// Dashboard
$routes->get('dashboard',            'DashboardController::index');
$routes->get('dashboard/mahasiswa',  'DashboardController::mahasiswaDashboard');

// Janji
$routes->get('janji',                'JanjiController::index');
$routes->get('janji/buat',           'JanjiController::buat');
$routes->post('janji/simpan',        'JanjiController::simpan');
$routes->get('janji/sukses/(:num)',  'JanjiController::sukses/$1');
$routes->get('janji/(:num)',         'JanjiController::detail/$1');
$routes->post('janji/hapus/(:num)', 'JanjiController::hapus/$1');

// Admin
$routes->get('admin/dashboard',              'AdminController::dashboard');
$routes->get('admin/janji',                  'AdminController::janjiList');
$routes->get('admin/janji/(:num)',           'AdminController::janjiDetail/$1');
$routes->post('admin/janji/proses/(:num)',   'AdminController::prosesJanji/$1');
$routes->post('admin/janji/batal/(:num)',    'AdminController::batalJanji/$1');
$routes->post('admin/janji/mulai/(:num)',    'AdminController::mulaiJanji/$1');

// Mahasiswa: konfirmasi kehadiran & pembatalan
$routes->post('janji/konfirmasi/(:num)',     'JanjiController::konfirmasiKehadiran/$1');
$routes->post('janji/batal/(:num)',          'JanjiController::batalKonseling/$1');
$routes->get('janji/konselor-jadwal',        'JanjiController::konselorJadwal');

// Konselor
$routes->get('konselor/dashboard',           'KonselorController::dashboard');
$routes->get('konselor/janji',               'KonselorController::janjiList');
$routes->get('konselor/janji/(:num)',        'KonselorController::janjiDetail/$1');
$routes->post('konselor/janji/hasil/(:num)', 'KonselorController::isiHasil/$1');
$routes->post('konselor/janji/mulai/(:num)', 'KonselorController::mulaiSesi/$1');

// Admin: Kelola Konselor
$routes->get('admin/konselor',                  'AdminController::konselorList');
$routes->get('admin/konselor/buat',             'AdminController::konselorBuat');
$routes->post('admin/konselor/simpan',          'AdminController::konselorSimpan');
$routes->get('admin/konselor/edit/(:num)',       'AdminController::konselorEdit/$1');
$routes->post('admin/konselor/update/(:num)',    'AdminController::konselorUpdate/$1');
$routes->post('admin/konselor/hapus/(:num)',     'AdminController::konselorHapus/$1');
$routes->post('admin/konselor/toggle/(:num)',    'AdminController::konselorToggle/$1');

// Feedback
$routes->get('feedback/(:num)',              'FeedbackController::buat/$1');
$routes->post('feedback/simpan/(:num)',      'FeedbackController::simpan/$1');
