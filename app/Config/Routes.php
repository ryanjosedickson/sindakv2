<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');

// ============================================
// AUTH ROUTES
// ============================================

$routes->get('login', 'Auth::index');
$routes->post('login', 'Auth::attemptLogin');
$routes->get('logout', 'Auth::logout', ['filter' => 'auth']);

// ============================================
// DASHBOARD
// ============================================

$routes->get('dashboard', 'Beranda::index', ['filter' => 'auth']);

// ============================================
// CONTOH route khusus Super Admin & Operator Pusat saja
// (dipakai nanti pas bikin modul Kepegawaian/Urusan Agama di Tier 1)
// ============================================
$routes->group('admin', ['filter' => ['auth', 'role:super_admin,operator_pusat']], static function ($routes) {
    // Nanti diisi route modul Kepegawaian, Sinode, Yayasan, dst.
    // Contoh: $routes->get('pegawai-pusat', 'Pegpusat::index');
});

// ============================================
// CONTOH route khusus Operator Sekolah
// (dipakai nanti pas bikin modul Guru/Siswa/SPKK di Tier 2)
// ============================================
$routes->group('sekolah', ['filter' => ['auth', 'role:super_admin,operator_sekolah']], static function ($routes) {
    // Contoh: $routes->get('siswa', 'Siswasdtk::index');
});