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