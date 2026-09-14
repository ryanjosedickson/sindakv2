<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * AuthFilter — memastikan request datang dari user yang sudah login.
 *
 * Ini yang menutup temuan audit utama: "tidak ada route-level auth
 * sama sekali". Filter ini dipasang di app/Config/Filters.php pada
 * SETIAP route group yang butuh login, bukan dicek manual satu-satu
 * di dalam controller seperti kode lama.
 */
class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        if (! $session->get('isLoggedIn')) {
            // Bedakan response untuk request API (JSON) vs request web (redirect).
            // Endpoint API ditandai lewat prefix route 'api/' — akan kita
            // tegaskan lagi saat desain RoleFilter & Routes.php.
            if ($request->isAJAX() || strpos($request->getPath(), 'api/') === 0) {
                return service('response')
                    ->setJSON(['status' => 'error', 'message' => 'Unauthorized. Silakan login terlebih dahulu.'])
                    ->setStatusCode(401);
            }

            return redirect()->to('/login')->with('error', 'Silakan login terlebih dahulu.');
        }

        // Guard tambahan: kalau akun ternyata sudah dinonaktifkan Super Admin
        // SETELAH user login (misal akun operator di-nonaktifkan di tengah sesi),
        // paksa logout supaya sesi lama tidak terus jalan.
        if ($session->get('is_active') === false) {
            $session->destroy();

            return redirect()->to('/login')->with('error', 'Akun Anda telah dinonaktifkan. Hubungi Super Admin.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Tidak ada tindakan setelah response — auth check cukup di 'before'.
    }
}
