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
// CONTOH route khusus Super Admin & Operator Pusat saja
// (dipakai nanti pas bikin modul Kepegawaian/Urusan Agama di Tier 1)
// ============================================
$routes->group('admin', ['filter' => ['auth', 'forcepwd', 'role:super_admin,operator_pusat']], static function ($routes) {
    // isi nanti pas Tier 1
    // Nanti diisi route modul Kepegawaian, Sinode, Yayasan, dst.
    // Contoh: $routes->get('pegawai-pusat', 'Pegpusat::index');
});

// ============================================
// CONTOH route khusus Operator Sekolah
// (dipakai nanti pas bikin modul Guru/Siswa/SPKK di Tier 2)
// ============================================
$routes->group('sekolah', ['filter' => ['auth', 'forcepwd', 'role:super_admin,operator_sekolah']], static function ($routes) {    // Contoh: $routes->get('siswa', 'Siswasdtk::index');
});