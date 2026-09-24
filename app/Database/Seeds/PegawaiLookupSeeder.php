<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PegawaiLookupSeeder extends Seeder
{
    public function run()
    {
        $now = date('Y-m-d H:i:s');

        // ── Pangkat (dari gambar 2 & 3 — "(Blanks)" dikecualikan, itu
        //    artifact tabel pivot Excel, bukan data pangkat sungguhan) ──
        $pangkat = [
            'Pembina', 'Pembina Tk. I', 'Pembina Utama', 'Pembina Utama Madya', 'Pembina Utama Muda',
            'Penata', 'Penata Muda', 'Penata Muda Tk. I', 'Penata Tk. I',
            'Pengatur', 'Pengatur Muda', 'Pengatur Muda Tk.I', 'Pengatur Tk.I',
        ];
        $this->seedNamaOnly('pangkat', $pangkat, $now);

        // ── Golongan/Ruang (dari gambar 4-6 — dimasukkan APA ADANYA sesuai
        //    keputusan, termasuk nilai non-standar 'IX', 'V', 'VII', 'X',
        //    'XII', 'LAIN-LAIN' yang kemungkinan berasal dari data historis/
        //    kesalahan input di sistem lama. "(Blanks)" tetap dikecualikan
        //    karena itu bukan nilai golongan, cuma baris kosong di Excel) ──
        $golonganRuang = [
            'I',
            'II/a', 'II/b', 'II/c', 'II/d',
            'III', 'III/a', 'III/b', 'III/c', 'III/d',
            'IV/a', 'IV/b', 'IV/c', 'IV/d', 'IV/e',
            'V', 'VII', 'IX', 'X', 'XII',
            'LAIN-LAIN',
        ];
        $this->seedNamaOnly('golongan_ruang', $golonganRuang, $now);

        // ── Tipe Jabatan (3 nilai tetap sesuai instruksi) ──
        $tipeJabatan = [
            'Jabatan Struktural',
            'Jabatan Fungsional Umum',
            'Jabatan Fungsional Tertentu',
        ];
        $this->seedNamaOnly('tipe_jabatan', $tipeJabatan, $now);
    }

    /**
     * Helper: insert daftar nama ke tabel lookup sederhana (id, nama,
     * is_active, timestamps), skip kalau nama itu sudah ada supaya seeder
     * aman dijalankan berulang kali (idempotent-friendly).
     */
    private function seedNamaOnly(string $table, array $namaList, string $now): void
    {
        foreach ($namaList as $nama) {
            $exists = $this->db->table($table)->where('nama', $nama)->countAllResults();

            if ($exists > 0) {
                continue; // sudah ada, jangan dobel
            }

            $this->db->table($table)->insert([
                'nama'       => $nama,
                'is_active'  => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
