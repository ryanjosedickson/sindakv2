<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Class BaseController
 *
 * Parent semua controller SINDAK baru. Berisi helper umum yang akan
 * dipakai berulang-ulang mulai Tier 1 dan seterusnya, terutama untuk
 * enforce scope (Operator Sekolah/Kampus hanya boleh akses datanya
 * sendiri) dan format response standar untuk endpoint API.
 */
abstract class BaseController extends Controller
{
    /**
     * @var CLIRequest|IncomingRequest
     */
    protected $request;

    /**
     * Helper CI4 bawaan yang otomatis dimuat di setiap controller.
     * Tambahkan helper lain di sini kalau perlu (misal 'text', 'array').
     *
     * @var list<string>
     */
    protected $helpers = ['form', 'url'];

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
    }

    // =========================================================
    // HELPER USER & SESSION
    // =========================================================

    /**
     * Ambil data user yang sedang login dari session, dalam bentuk array.
     * Return null kalau belum login (harusnya jarang terjadi di controller
     * yang sudah dilindungi filter 'auth', tapi tetap jaga-jaga).
     */
    protected function currentUser(): ?array
    {
        $session = session();

        if (! $session->get('isLoggedIn')) {
            return null;
        }

        return [
            'id'         => $session->get('user_id'),
            'username'   => $session->get('username'),
            'full_name'  => $session->get('full_name'),
            'role'       => $session->get('role'),
            'role_label' => $session->get('role_label'),
            'sekolah_id' => $session->get('sekolah_id'),
            'kampus_id'  => $session->get('kampus_id'),
        ];
    }

    protected function currentUserId(): ?int
    {
        return session()->get('user_id');
    }

    protected function currentUserRole(): ?string
    {
        return session()->get('role');
    }

    protected function isSuperAdmin(): bool
    {
        return $this->currentUserRole() === 'super_admin';
    }

    protected function isOperatorPusat(): bool
    {
        return $this->currentUserRole() === 'operator_pusat';
    }

    /**
     * Scope entitas milik user yang sedang login.
     *
     * Dipakai untuk membatasi query data di modul Tier 1+ — misal:
     *   $scope = $this->currentUserScope();
     *   if ($scope['type'] === 'sekolah') {
     *       $builder->where('sekolah_id', $scope['id']);
     *   }
     *
     * Return ['type' => 'pusat', 'id' => null] untuk Super Admin dan
     * Operator Pusat — artinya tidak ada batasan scope, akses penuh.
     */
    protected function currentUserScope(): array
    {
        $role = $this->currentUserRole();

        if ($role === 'operator_sekolah') {
            return ['type' => 'sekolah', 'id' => session()->get('sekolah_id')];
        }

        if ($role === 'operator_kampus') {
            return ['type' => 'kampus', 'id' => session()->get('kampus_id')];
        }

        // super_admin & operator_pusat: tidak terikat scope tertentu.
        return ['type' => 'pusat', 'id' => null];
    }

    /**
     * Cek apakah user yang login boleh akses record dengan sekolah_id/
     * kampus_id tertentu, sesuai scope-nya.
     *
     * Dipakai di controller Tier 2 (modul sekolah) & Tier Dikti (modul
     * kampus) sebelum menampilkan/mengubah detail record — INI YANG
     * MENUTUP CELAH IDOR secara terstruktur, bukan hanya mengandalkan
     * data tidak ditampilkan di UI.
     *
     * @param int $targetSekolahId sekolah_id milik record yang sedang diakses
     */
    protected function canAccessSekolah(int $targetSekolahId): bool
    {
        $scope = $this->currentUserScope();

        if ($scope['type'] === 'pusat') {
            return true; // Super Admin / Operator Pusat: akses penuh
        }

        return $scope['type'] === 'sekolah' && (int) $scope['id'] === $targetSekolahId;
    }

    /**
     * Versi kampus dari canAccessSekolah() di atas.
     */
    protected function canAccessKampus(int $targetKampusId): bool
    {
        $scope = $this->currentUserScope();

        if ($scope['type'] === 'pusat') {
            return true;
        }

        return $scope['type'] === 'kampus' && (int) $scope['id'] === $targetKampusId;
    }

    // =========================================================
    // HELPER RESPONSE STANDAR (dipakai terutama untuk endpoint API)
    // =========================================================

    /**
     * Format response JSON standar: {status, message, data}.
     * Dipakai konsisten di semua endpoint API supaya frontend/consumer
     * API tidak perlu urus format yang beda-beda per endpoint.
     */
    protected function jsonResponse(string $status, string $message, $data = null, int $httpCode = 200)
    {
        return $this->response->setStatusCode($httpCode)->setJSON([
            'status'  => $status,
            'message' => $message,
            'data'    => $data,
        ]);
    }

    protected function jsonSuccess(string $message = 'OK', $data = null, int $httpCode = 200)
    {
        return $this->jsonResponse('success', $message, $data, $httpCode);
    }

    protected function jsonError(string $message = 'Terjadi kesalahan.', int $httpCode = 400, $data = null)
    {
        return $this->jsonResponse('error', $message, $data, $httpCode);
    }

    /**
     * Response standar 403 khusus untuk kasus IDOR/scope violation —
     * dipakai bersamaan dengan canAccessSekolah()/canAccessKampus().
     */
    protected function jsonForbidden(string $message = 'Anda tidak memiliki akses ke data ini.')
    {
        return $this->jsonResponse('error', $message, null, 403);
    }
}
