<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * ForceChangePasswordFilter — memaksa user ke halaman ganti password
 * selama flag must_change_password masih aktif di session-nya.
 *
 * WAJIB dipasang SETELAH 'auth' di route group (butuh session user
 * sudah ada). JANGAN dipasang di route 'change-password' itu sendiri
 * dan 'logout' — supaya user yang kena force ini tetap bisa mencapai
 * halaman ganti password dan tetap bisa logout kalau mau batal.
 */
class ForceChangePasswordFilter implements FilterInterface
{
    /**
     * Path yang DIKECUALIKAN dari pemaksaan ini (relatif, tanpa slash awal).
     * Dicek dengan pencocokan awalan (startsWith), bukan exact match,
     * supaya 'change-password' dan 'change-password/submit' dst. ikut lolos.
     */
    private const EXCLUDED_PATHS = ['change-password', 'logout'];

    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        if (! $session->get('must_change_password')) {
            return; // flag tidak aktif, lanjut seperti biasa
        }

        $path = trim($request->getPath(), '/');

        foreach (self::EXCLUDED_PATHS as $excluded) {
            if (strpos($path, $excluded) === 0) {
                return; // path dikecualikan, jangan di-redirect
            }
        }

        if ($request->isAJAX() || strpos($path, 'api/') === 0) {
            return service('response')
                ->setJSON(['status' => 'error', 'message' => 'Anda wajib mengganti password sebelum melanjutkan.'])
                ->setStatusCode(403);
        }

        return redirect()->to('/change-password')
            ->with('warning', 'Demi keamanan akun, Anda wajib mengganti password sebelum melanjutkan.');
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Tidak ada tindakan setelah response.
    }
}
