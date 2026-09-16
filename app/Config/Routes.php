<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Auth::index');

// ============================================
// AUTH ROUTES
// ============================================
$routes->get('login', 'Auth::index');
$routes->post('login', 'Auth::attemptLogin');
$routes->get('logout', 'Auth::logout', ['filter' => 'auth']);

// ============================================
// GANTI PASSWORD
// ============================================

$routes->get('change-password', 'Auth::changePassword', ['filter' => 'auth']);
$routes->post('change-password', 'Auth::updatePassword', ['filter' => 'auth']);

// ============================================
// DASHBOARD
// ============================================

$routes->get('dashboard', 'Beranda::index', ['filter' => ['auth', 'forcepwd']]);

// ============================================
// Route Super Admin & Operator Pusat
// ============================================
$routes->group('admin', ['filter' => ['auth', 'forcepwd', 'role:super_admin,operator_pusat']], static function ($routes) {
    // ============================================
    // MODUL PEGAWAI
    // ============================================
    $routes->get('pegawai', 'Pegawai::index');
    $routes->get('pegawai/tambah', 'Pegawai::create');
    $routes->post('pegawai/tambah', 'Pegawai::store');
    $routes->get('pegawai/(:num)/edit', 'Pegawai::edit/$1');
    $routes->post('pegawai/(:num)/edit', 'Pegawai::update/$1');
    $routes->post('pegawai/(:num)/hapus', 'Pegawai::delete/$1');
});


// ============================================
// CONTOH route khusus Operator Sekolah
// (dipakai nanti pas bikin modul Guru/Siswa/SPKK di Tier 2)
// ============================================
$routes->group('sekolah', ['filter' => ['auth', 'forcepwd', 'role:super_admin,operator_sekolah']], static function ($routes) {    
    // Contoh: $routes->get('siswa', 'Siswasdtk::index');
});