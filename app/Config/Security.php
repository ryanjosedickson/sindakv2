<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Security extends BaseConfig
{
    /**
     * Session-based CSRF — BUKAN cookie-based. Direkomendasikan resmi oleh
     * CodeIgniter (lihat GHSA-5hm8-vh6r-2cjq) sebagai salah satu mitigasi
     * CSRF bypass lewat serangan subdomain.
     */
    public string $csrfProtection = 'session';

    /**
     * true = tambahkan random mask ke token (mitigasi BREACH/compression
     * side-channel attack). Tidak ada downside signifikan untuk diaktifkan.
     */
    public bool $tokenRandomize = true;

    /**
     * "Opsi B" — token TIDAK diregenerasi di setiap submission.
     *
     * Alasan: SINDAK banyak menggunakan AJAX request BERURUTAN (misal
     * simpan data multi-step, atau beberapa request async hampir
     * bersamaan). Kalau token berubah di setiap request, request kedua
     * yang jalan sebelum halaman ke-refresh akan gagal CSRF check
     * (token sudah tidak valid), padahal user tidak melakukan apa-apa
     * yang salah — ini bikin bug yang membingungkan.
     *
     * Trade-off: token yang sama dipakai sepanjang umur session (atau
     * sampai regenerasi manual, misal saat login — lihat Auth::attemptLogin()
     * yang sudah memanggil regenerasi CSRF eksplisit). Ini masih dianggap
     * aman selama token tetap terikat session per-user dan session
     * sendiri sudah diregenerasi saat login.
     */
    public bool $regenerate = false;

    /**
     * true = kalau CSRF check gagal, user di-redirect balik ke halaman
     * sebelumnya (dengan pesan error), bukan langsung dilempar exception
     * mentah. Lebih ramah untuk form HTML biasa.
     *
     * CATATAN: untuk endpoint API (JWT-based, Tier selanjutnya), CSRF
     * akan di-exclude sama sekali lewat filter — bukan lewat setting ini.
     */
    public bool $redirect = true;

    /**
     * Masa berlaku token dalam detik. Disamakan dengan masa aktif
     * session (7200 detik / 2 jam) di app/Config/Session.php, supaya
     * token tidak kedaluwarsa duluan sebelum session-nya sendiri habis.
     */
    public int $expires = 7200;

    /**
     * SameSite=Lax — standar aman untuk kasus SINDAK (bukan aplikasi
     * yang butuh cross-site request dari domain lain).
     */
    public string $samesite = 'Lax';
}
