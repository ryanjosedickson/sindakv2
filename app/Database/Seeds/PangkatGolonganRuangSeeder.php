<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * PangkatGolonganRuangSeeder — mengisi tabel 'pangkat' (13 nilai) dan
 * 'golongan_ruang' (34 nilai: 17 PNS + 17 PPPK) dari data resmi.
 *
 * MENGGANTIKAN bagian Pangkat & Golongan Ruang di PegawaiLookupSeeder
 * yang lama (yang datanya dari screenshot, lebih kasar/tidak lengkap).
 * Kalau PegawaiLookupSeeder lama SUDAH sempat dijalankan sebelumnya,
 * jalankan dulu:
 *   TRUNCATE TABLE pangkat;
 *   TRUNCATE TABLE golongan_ruang;
 * sebelum menjalankan seeder ini, supaya tidak ada data lama yang
 * tercampur/dobel. Kalau tabelnya memang masih kosong, langsung jalankan
 * saja tanpa perlu truncate.
 */
class PangkatGolonganRuangSeeder extends Seeder
{
    public function run()
    {
        $now = date('Y-m-d H:i:s');

        // ── Pangkat (13 nilai resmi) ──
        // Catatan: 2 nilai di file sumber ("Pengatur Muda Tk.l" dan
        // "Pengatur Tk.l") memakai huruf 'l' kecil, bukan 'I' — sudah
        // saya normalisasi ke format "Tk. I" (kapital, dengan spasi)
        // supaya konsisten dengan 11 nilai lainnya yang sudah memakai
        // format itu. Kalau ternyata ejaan asli memang sengaja begitu,
        // kabari saya untuk dikembalikan.
        $pangkatList = [
            'Pembina Utama', 'Pembina Utama Madya', 'Pembina Utama Muda',
            'Pembina', 'Pembina Tk. I',
            'Penata Muda', 'Penata Muda Tk. I', 'Penata', 'Penata Tk. I',
            'Pengatur Muda', 'Pengatur Muda Tk. I', 'Pengatur', 'Pengatur Tk. I',
        ];
        foreach ($pangkatList as $nama) {
            $this->insertIfNotExists('pangkat', ['nama' => $nama], $now);
        }
        echo "Pangkat: " . count($pangkatList) . " diproses.\n";

        // ── Golongan Ruang PNS (17 nilai, dengan Jenis Golongan berpasangan) ──
        $golonganPns = [
            ['I/a', 'Juru Muda'],
            ['I/b', 'Juru Muda Tingkat I'],
            ['I/c', 'Juru'],
            ['I/d', 'Juru Tingkat I'],
            ['II/a', 'Pengatur Muda'],
            ['II/b', 'Pengatur Muda Tingkat I'],
            ['II/c', 'Pengatur'],
            ['II/d', 'Pengatur Tingkat I'],
            ['III/a', 'Penata Muda'],
            ['III/b', 'Penata Muda Tingkat I'],
            ['III/c', 'Penata'],
            ['III/d', 'Penata Tingkat I'],
            ['IV/a', 'Pembina'],
            ['IV/b', 'Pembina Tingkat I'],
            ['IV/c', 'Pembina Utama Muda'],
            ['IV/d', 'Pembina Utama Madya'],
            ['IV/e', 'Pembina Utama'],
        ];
        foreach ($golonganPns as [$kode, $jenis]) {
            $this->insertIfNotExists('golongan_ruang', [
                'nama'            => $kode,
                'jenis_golongan'  => $jenis,
                'tipe_asn'        => 'PNS',
            ], $now);
        }

        // ── Golongan Ruang PPPK (17 nilai, angka romawi polos, tanpa Jenis Golongan) ──
        $golonganPppk = ['I','II','III','IV','V','VI','VII','VIII','IX','X','XI','XII','XIII','XIV','XV','XVI','XVII'];
        foreach ($golonganPppk as $kode) {
            $this->insertIfNotExists('golongan_ruang', [
                'nama'            => $kode,
                'jenis_golongan'  => null,
                'tipe_asn'        => 'PPPK',
            ], $now);
        }
        echo "Golongan Ruang: " . (count($golonganPns) + count($golonganPppk)) . " diproses.\n";
    }

    private function insertIfNotExists(string $table, array $fields, string $now): void
    {
        $query = $this->db->table($table);
        foreach ($fields as $key => $value) {
            if ($value === null) {
                $query->where($key, null);
            } else {
                $query->where($key, $value);
            }
        }
        if ($query->countAllResults() > 0) {
            return;
        }

        $this->db->table($table)->insert(array_merge($fields, [
            'is_active'  => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]));
    }
}
