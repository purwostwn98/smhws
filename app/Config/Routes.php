<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('landing', 'Home::landing');

// SMHWS Learn (publik)
$routes->get('learn',             'LearnController::index');
$routes->get('learn/(:segment)',  'LearnController::detail/$1');

// Agenda Psychoeducation, Webinar & Workshop (publik)
$routes->get('psikoedukasi',          'AgendaController::index');
$routes->get('psikoedukasi/(:num)',   'AgendaController::detail/$1');

// Skrining DASS-21 publik (tanpa login)
$routes->get('skrining', 'SkriningController::pilih'); // pilih jenis assessment
$routes->group('skrining', ['namespace' => 'App\Controllers\Skrining'], static function ($routes) {
    // DASS-21
    $routes->get('dass',           'DassController::index');
    $routes->post('dass/submit',   'DassController::submit');
    $routes->get('dass/hasil',     'DassController::hasil');
    $routes->get('dass/hasil/pdf', 'DassController::pdf');

    // Pola Penggunaan Internet
    $routes->get('internet',           'InternetController::index');
    $routes->post('internet/submit',   'InternetController::submit');
    $routes->get('internet/hasil',     'InternetController::hasil');
    $routes->get('internet/hasil/pdf', 'InternetController::pdf');
});

// Auth
$routes->get('login',  'AuthController::loginPage');
$routes->post('login', 'AuthController::login');
$routes->get('logout', 'AuthController::logout');

// Auth CAS
$routes->get('cas', 'AuthController::loginWithCas');

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
$routes->get('admin/dashboard-univ',         'AdminController::dashboardUniv');
$routes->get('admin/dashboard-univ/pdf',     'AdminController::dashboardUnivPdf');
$routes->get('admin/stressor-detail',        'AdminController::stressorDetail');
$routes->get('admin/mahasiswa',              'AdminController::mahasiswaList');
$routes->get('admin/rekap-konseling',        'AdminController::rekapKonseling');
$routes->get('admin/rekap-konseling/export', 'AdminController::rekapKonselingExport');
$routes->get('admin/kalender',               'AdminController::kalender');
$routes->get('admin/kalender/events',        'AdminController::kalenderEvents');
$routes->get('admin/janji',                  'AdminController::janjiList');
$routes->get('admin/janji/(:num)',           'AdminController::janjiDetail/$1');
$routes->get('admin/janji/(:num)/pdf',           'AdminController::exportPdf/$1');
$routes->get('admin/janji/(:num)/surat-rujukan', 'AdminController::suratRujukan/$1');
$routes->post('admin/janji/proses/(:num)',   'AdminController::prosesJanji/$1');
$routes->post('admin/janji/batal/(:num)',    'AdminController::batalJanji/$1');
$routes->post('admin/janji/mulai/(:num)',                  'AdminController::mulaiJanji/$1');
$routes->post('admin/janji/konfirmasi-kehadiran/(:num)',   'AdminController::konfirmasiKehadiran/$1');

// Mahasiswa: konfirmasi kehadiran & pembatalan
$routes->post('janji/konfirmasi/(:num)',     'JanjiController::konfirmasiKehadiran/$1');
$routes->post('janji/batal/(:num)',          'JanjiController::batalKonseling/$1');
$routes->get('janji/konselor-jadwal',        'JanjiController::konselorJadwal');
$routes->get('janji/slot-booked',           'JanjiController::slotBooked');
$routes->get('janji/ketersediaan',          'JanjiController::ketersediaan');

// Dosen
$routes->get('dosen/dashboard',                    'DosenController::dashboard');
$routes->get('dosen/dashboard-prodi',              'DosenController::dashboardProdi');
$routes->get('dosen/dashboard-prodi/pdf',          'DosenController::dashboardProdiPdf');
$routes->get('dosen/stressor-detail',              'DosenController::stressorDetail');
$routes->get('dosen/dashboard-fakultas',           'DosenController::dashboardFakultas');
$routes->get('dosen/dashboard-fakultas/pdf',       'DosenController::dashboardFakultasPdf');
$routes->get('dosen/stressor-detail-fakultas',     'DosenController::stressorDetailFakultas');

// Ask Our Psychologist — mahasiswa (filter mahasiswa)
$routes->get('tanya',              'TanyaController::index');
$routes->get('tanya/buat',         'TanyaController::buat');
$routes->post('tanya/simpan',      'TanyaController::simpan');
$routes->get('tanya/(:num)',       'TanyaController::detail/$1');

// Konselor
$routes->get('konselor/tanya',              'KonselorTanyaController::index');
$routes->get('konselor/tanya/(:num)',       'KonselorTanyaController::detail/$1');
$routes->post('konselor/tanya/jawab/(:num)', 'KonselorTanyaController::jawab/$1');
$routes->get('konselor/dashboard',                'KonselorController::dashboard');
$routes->get('konselor/profil',                   'KonselorController::profil');
$routes->post('konselor/profil/update',           'KonselorController::profilUpdate');
$routes->get('konselor/janji',                    'KonselorController::janjiList');
$routes->get('konselor/janji/(:num)',             'KonselorController::janjiDetail/$1');
$routes->post('konselor/janji/hasil/(:num)',      'KonselorController::isiHasil/$1');
$routes->post('konselor/janji/edit-hasil/(:num)', 'KonselorController::editHasil/$1');
$routes->get('konselor/janji/(:num)/pdf',           'KonselorController::exportPdf/$1');
$routes->get('konselor/janji/(:num)/surat-rujukan', 'KonselorController::suratRujukan/$1');
$routes->post('konselor/janji/mulai/(:num)',       'KonselorController::mulaiSesi/$1');
$routes->post('konselor/janji/tidak-hadir/(:num)', 'KonselorController::tidakHadir/$1');

// Admin: Kelola Konselor
$routes->get('admin/konselor',                  'AdminController::konselorList');
$routes->get('admin/konselor/buat',             'AdminController::konselorBuat');
$routes->post('admin/konselor/simpan',          'AdminController::konselorSimpan');
$routes->get('admin/konselor/edit/(:num)',       'AdminController::konselorEdit/$1');
$routes->post('admin/konselor/update/(:num)',    'AdminController::konselorUpdate/$1');
$routes->post('admin/konselor/hapus/(:num)',     'AdminController::konselorHapus/$1');
$routes->post('admin/konselor/toggle/(:num)',    'AdminController::konselorToggle/$1');

// Admin: Rekap Skrining (Mental Health Check)
$routes->get('admin/rekap-skrining',              'RekapSkriningController::index');
$routes->get('admin/rekap-skrining/export',       'RekapSkriningController::export');
$routes->get('admin/rekap-skrining/(:num)',       'RekapSkriningController::detail/$1');
$routes->get('admin/rekap-skrining/internet',          'RekapInternetController::index');
$routes->get('admin/rekap-skrining/internet/export',   'RekapInternetController::export');
$routes->get('admin/rekap-skrining/internet/(:num)',   'RekapInternetController::detail/$1');

// Admin: Ask Our Psychologist
$routes->get('admin/tanya',                    'TanyaAdminController::index');
$routes->get('admin/tanya/(:num)',             'TanyaAdminController::detail/$1');
$routes->post('admin/tanya/tugaskan/(:num)',   'TanyaAdminController::tugaskan/$1');
$routes->post('admin/tanya/tolak/(:num)',      'TanyaAdminController::tolak/$1');
$routes->post('admin/tanya/terbitkan/(:num)',  'TanyaAdminController::terbitkan/$1');

// Admin: SMHWS Learn
$routes->get('admin/learn',                      'LearnAdminController::index');
$routes->get('admin/learn/buat',                 'LearnAdminController::buat');
$routes->post('admin/learn/simpan',              'LearnAdminController::simpan');
$routes->get('admin/learn/edit/(:num)',          'LearnAdminController::edit/$1');
$routes->post('admin/learn/update/(:num)',       'LearnAdminController::update/$1');
$routes->post('admin/learn/hapus/(:num)',        'LearnAdminController::hapus/$1');
$routes->post('admin/learn/toggle/(:num)',       'LearnAdminController::toggle/$1');
$routes->get('admin/learn/topik',                'LearnAdminController::topik');
$routes->post('admin/learn/topik/simpan',        'LearnAdminController::topikSimpan');
$routes->post('admin/learn/topik/update/(:num)', 'LearnAdminController::topikUpdate/$1');
$routes->post('admin/learn/topik/hapus/(:num)',  'LearnAdminController::topikHapus/$1');

// Admin: Agenda Kegiatan (Psychoeducation, Webinar & Workshop)
$routes->get('admin/agenda-kegiatan',                'AgendaAdminController::index');
$routes->get('admin/agenda-kegiatan/buat',           'AgendaAdminController::buat');
$routes->post('admin/agenda-kegiatan/simpan',        'AgendaAdminController::simpan');
$routes->get('admin/agenda-kegiatan/edit/(:num)',    'AgendaAdminController::edit/$1');
$routes->post('admin/agenda-kegiatan/update/(:num)', 'AgendaAdminController::update/$1');
$routes->post('admin/agenda-kegiatan/hapus/(:num)',  'AgendaAdminController::hapus/$1');
$routes->post('admin/agenda-kegiatan/toggle/(:num)', 'AgendaAdminController::toggle/$1');

// Admin: Instansi Rujukan
$routes->get('admin/instansi-rujukan',                'AdminController::instansiList');
$routes->get('admin/instansi-rujukan/buat',           'AdminController::instansiBuat');
$routes->post('admin/instansi-rujukan/simpan',        'AdminController::instansiSimpan');
$routes->get('admin/instansi-rujukan/edit/(:num)',    'AdminController::instansiEdit/$1');
$routes->post('admin/instansi-rujukan/update/(:num)', 'AdminController::instansiUpdate/$1');
$routes->post('admin/instansi-rujukan/hapus/(:num)',  'AdminController::instansiHapus/$1');

// Admin: Checklist Konseling
$routes->get('admin/checklist',                          'ChecklistAdminController::index');
$routes->get('admin/checklist/item/buat',                'ChecklistAdminController::itemBuat');
$routes->post('admin/checklist/item/simpan',             'ChecklistAdminController::itemSimpan');
$routes->get('admin/checklist/item/edit/(:num)',         'ChecklistAdminController::itemEdit/$1');
$routes->post('admin/checklist/item/update/(:num)',      'ChecklistAdminController::itemUpdate/$1');
$routes->post('admin/checklist/item/hapus/(:num)',       'ChecklistAdminController::itemHapus/$1');
$routes->post('admin/checklist/item/toggle/(:num)',      'ChecklistAdminController::itemToggle/$1');
$routes->get('admin/checklist/subsections',              'ChecklistAdminController::subsectionsAjax');
$routes->get('admin/checklist/(:segment)',               'ChecklistAdminController::sectionItems/$1');

// Feedback
$routes->get('feedback/(:num)',              'FeedbackController::buat/$1');
$routes->post('feedback/simpan/(:num)',      'FeedbackController::simpan/$1');
