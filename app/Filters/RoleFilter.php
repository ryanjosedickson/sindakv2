<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * RoleFilter — membatasi akses route berdasarkan role tertentu.
 *
 * WAJIB dipasang SETELAH AuthFilter di route group (urutan filter
 * penting), karena filter ini asumsikan user sudah pasti login.
 *
 * Cara pakai di Routes.php:
 *   $routes->group('admin', ['filter' => ['auth', 'role:super_admin,operator_pusat']], ...);
 *
 * Catatan: filter ini BARU memeriksa role, BELUM memeriksa scope data
 * (misal operator sekolah A tidak boleh akses data sekolah B). Cek
 * scope tetap harus dilakukan di controller/model masing-masing modul
 * data, karena scope check butuh tahu ID record yang diakses — itu
 * baru relevan mulai Tier 1 saat modul data mulai dibangun.
 */
class RoleFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        // Kalau tidak ada argument role yang diminta, filter ini tidak
        // melakukan apa-apa (harusnya tidak pernah terjadi kalau dipasang
        // benar di Routes.php, tapi jaga-jaga daripada block semua).
        if (empty($arguments)) {
            return;
        }

        $userRole = $session->get('role');

        if (! in_array($userRole, $arguments, true)) {
            if ($request->isAJAX() || strpos($request->getPath(), 'api/') === 0) {
                return service('response')
                    ->setJSON(['status' => 'error', 'message' => 'Anda tidak memiliki akses ke resource ini.'])
                    ->setStatusCode(403);
            }

            return redirect()->to('/beranda')->with('error', 'Anda tidak memiliki akses ke halaman tersebut.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Tidak ada tindakan setelah response.
    }
}
