<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * KabupatenKotaSeeder — fetch data resmi kabupaten/kota LANGSUNG dari API
 * https://api.kodewilayah.web.id/regencies/{kode_provinsi} untuk setiap
 * provinsi yang sudah ada di tabel 'provinsi' (jalankan ProvinsiSeeder dulu).
 *
 * Kenapa fetch live (bukan data statis seperti ProvinsiSeeder): jumlah
 * kabupaten/kota jauh lebih sering berubah (pemekaran daerah) dibanding
 * provinsi, dan datanya juga jauh lebih banyak (514 entri) — lebih aman
 * ambil langsung dari sumber resmi yang selalu ter-update, daripada
 * di-hardcode dan berisiko basi.
 *
 * PENTING: seeder ini butuh koneksi internet aktif di server saat
 * dijalankan (memanggil API publik). Prosesnya bisa makan waktu 1-2 menit
 * karena ada 38 kali panggilan API (satu per provinsi) dengan jeda supaya
 * tidak membanjiri API publik ini.
 */
class KabupatenKotaSeeder extends Seeder
{
    public function run()
    {
        $now = date('Y-m-d H:i:s');

        $provinsiList = $this->db->table('provinsi')->select('id, kode, nama')->get()->getResultArray();

        if (empty($provinsiList)) {
            echo "Tabel provinsi masih kosong. Jalankan ProvinsiSeeder dulu.\n";
            return;
        }

        foreach ($provinsiList as $provinsi) {
            echo "Mengambil kabupaten/kota untuk: {$provinsi['nama']} (kode {$provinsi['kode']}) ... ";

            $url = "https://api.kodewilayah.web.id/regencies/{$provinsi['kode']}";
            $response = $this->fetchJson($url);

            if ($response === null || empty($response['data'])) {
                echo "GAGAL (cek koneksi internet atau API sedang down)\n";
                continue;
            }

            $inserted = 0;
            foreach ($response['data'] as $regency) {
                // Nama dari API biasanya berupa "KABUPATEN XXX" / "KOTA XXX" —
                // disimpan apa adanya (uppercase) supaya konsisten dengan
                // format resmi BPS, bukan dinormalisasi ke title-case sendiri.
                $exists = $this->db->table('kabupaten_kota')
                    ->where('kode', (string) $regency['code'])
                    ->countAllResults();

                if ($exists > 0) {
                    continue;
                }

                $this->db->table('kabupaten_kota')->insert([
                    'nama'       => $regency['name'],
                    'kode'       => (string) $regency['code'],
                    'parent_id'  => $provinsi['id'],
                    'is_active'  => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $inserted++;
            }

            echo "{$inserted} baru ditambahkan.\n";

            // Jeda singkat supaya tidak membanjiri API publik ini secara beruntun.
            usleep(300000); // 0.3 detik
        }

        echo "\nSelesai.\n";
    }

    /**
     * Ambil & decode JSON dari URL, return null kalau gagal (bukan throw
     * exception) — supaya satu provinsi gagal fetch tidak menghentikan
     * seluruh proses seeding provinsi lainnya.
     */
    private function fetchJson(string $url): ?array
    {
        $context = stream_context_create(['http' => ['timeout' => 15]]);
        $raw = @file_get_contents($url, false, $context);

        if ($raw === false) {
            return null;
        }

        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : null;
    }
}
