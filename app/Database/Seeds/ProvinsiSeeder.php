<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Data 38 provinsi resmi sesuai kodewilayah.web.id (mengikuti standar BPS).
 * Sudah diverifikasi cocok dengan hasil mapping otomatis dataset ASN
 * Bimas Kristen (138 dari 139 nilai unik di file Excel ke-mapping otomatis
 * ke daftar ini; 1 sisanya - "Siak" - typo data, seharusnya Riau).
 *
 * Data ditulis manual (bukan fetch API) karena provinsi jarang berubah
 * (38 provinsi sudah final sejak pemekaran Papua 2023) — beda dengan
 * kabupaten/kota yang lebih sering berubah, makanya KabupatenKotaSeeder
 * fetch live dari API.
 */
class ProvinsiSeeder extends Seeder
{
    public function run()
    {
        $now = date('Y-m-d H:i:s');

        $provinsiList = [
            ['nama' => 'Aceh', 'kode' => '11'],
            ['nama' => 'Sumatera Utara', 'kode' => '12'],
            ['nama' => 'Sumatera Barat', 'kode' => '13'],
            ['nama' => 'Riau', 'kode' => '14'],
            ['nama' => 'Jambi', 'kode' => '15'],
            ['nama' => 'Sumatera Selatan', 'kode' => '16'],
            ['nama' => 'Bengkulu', 'kode' => '17'],
            ['nama' => 'Lampung', 'kode' => '18'],
            ['nama' => 'Kepulauan Bangka Belitung', 'kode' => '19'],
            ['nama' => 'Kepulauan Riau', 'kode' => '21'],
            ['nama' => 'Daerah Khusus Ibukota Jakarta', 'kode' => '31'],
            ['nama' => 'Jawa Barat', 'kode' => '32'],
            ['nama' => 'Jawa Tengah', 'kode' => '33'],
            ['nama' => 'Daerah Istimewa Yogyakarta', 'kode' => '34'],
            ['nama' => 'Jawa Timur', 'kode' => '35'],
            ['nama' => 'Banten', 'kode' => '36'],
            ['nama' => 'Bali', 'kode' => '51'],
            ['nama' => 'Nusa Tenggara Barat', 'kode' => '52'],
            ['nama' => 'Nusa Tenggara Timur', 'kode' => '53'],
            ['nama' => 'Kalimantan Barat', 'kode' => '61'],
            ['nama' => 'Kalimantan Tengah', 'kode' => '62'],
            ['nama' => 'Kalimantan Selatan', 'kode' => '63'],
            ['nama' => 'Kalimantan Timur', 'kode' => '64'],
            ['nama' => 'Kalimantan Utara', 'kode' => '65'],
            ['nama' => 'Sulawesi Utara', 'kode' => '71'],
            ['nama' => 'Sulawesi Tengah', 'kode' => '72'],
            ['nama' => 'Sulawesi Selatan', 'kode' => '73'],
            ['nama' => 'Sulawesi Tenggara', 'kode' => '74'],
            ['nama' => 'Gorontalo', 'kode' => '75'],
            ['nama' => 'Sulawesi Barat', 'kode' => '76'],
            ['nama' => 'Maluku', 'kode' => '81'],
            ['nama' => 'Maluku Utara', 'kode' => '82'],
            ['nama' => 'Papua', 'kode' => '91'],
            ['nama' => 'Papua Barat', 'kode' => '92'],
            ['nama' => 'Papua Selatan', 'kode' => '93'],
            ['nama' => 'Papua Tengah', 'kode' => '94'],
            ['nama' => 'Papua Pegunungan', 'kode' => '95'],
            ['nama' => 'Papua Barat Daya', 'kode' => '96'],
        ];

        foreach ($provinsiList as $p) {
            $exists = $this->db->table('provinsi')->where('kode', $p['kode'])->countAllResults();

            if ($exists > 0) {
                continue;
            }

            $this->db->table('provinsi')->insert([
                'nama'       => $p['nama'],
                'kode'       => $p['kode'],
                'is_active'  => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
