<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * ImportPegawaiSeeder — import massal data pegawai dari CSV hasil export
 * Excel data ASN Bimas Kristen.
 *
 * CARA PAKAI:
 *   1. Buka file Excel ASLI (yang MASIH ada kolom "Nama Lengkap") di Excel.
 *   2. File > Save As > CSV UTF-8 (Comma delimited).
 *   3. Taruh file CSV itu di: writable/imports/pegawai_import.csv
 *      (buat folder 'imports' dulu kalau belum ada)
 *   4. Jalankan: php spark db:seed ImportPegawaiSeeder
 *
 * Kolom CSV dibaca berdasarkan NAMA HEADER (bukan urutan posisi), jadi
 * urutan kolom di file asli tidak masalah, asal nama header-nya cocok
 * dengan yang di $requiredColumns di bawah.
 *
 * NIP sengaja TIDAK diisi (NULL) — akan diisi manual satu per satu lewat
 * form edit setelah data ini masuk, sesuai kesepakatan sebelumnya.
 *
 * Kolom "Keterangan" sengaja DIABAIKAN (tidak disimpan) sesuai keputusan
 * sebelumnya — isinya cuma penamaan ulang dari Jabatan/Satker/Unit Kerja.
 */
class ImportPegawaiSeeder extends Seeder
{
    private const CSV_PATH = WRITEPATH . 'imports/pegawai_import.csv';

    /** Alias untuk normalisasi teks Provinsi yang typo/singkatan di data sumber. */
    private const PROVINSI_ALIASES = [
        'DKI JAKARTA'                => 'Daerah Khusus Ibukota Jakarta',
        'DKI. JAKARTA'               => 'Daerah Khusus Ibukota Jakarta',
        'DIY'                        => 'Daerah Istimewa Yogyakarta',
        'D.I. YOGYAKARTA'            => 'Daerah Istimewa Yogyakarta',
        'D.I.YOGYAKARTA'             => 'Daerah Istimewa Yogyakarta',
        'DI YOGYAKARTA'              => 'Daerah Istimewa Yogyakarta',
        'NTB'                        => 'Nusa Tenggara Barat',
        'NTT'                        => 'Nusa Tenggara Timur',
        'BANGKA BELITUNG'            => 'Kepulauan Bangka Belitung',
        'JABAR'                      => 'Jawa Barat',
        'SULUT'                      => 'Sulawesi Utara',
        'SULTRA'                     => 'Sulawesi Tenggara',
        'SUMSEL'                     => 'Sumatera Selatan',
        'SUMUT'                      => 'Sumatera Utara',
        'SUMATRA UTARA'              => 'Sumatera Utara',
        'KALIMATAN TENGAH'           => 'Kalimantan Tengah',
        'MALIKU'                     => 'Maluku',
        'NUSA TENGGARA TIMUR (NTT)'  => 'Nusa Tenggara Timur',
        'NUSA TENGGGARA TIMUR'       => 'Nusa Tenggara Timur',
        'NUSATENGGARA TIMUR'         => 'Nusa Tenggara Timur',
        'NUSA TEGGARA TIMUR'         => 'Nusa Tenggara Timur',
        'NUSA TENGGARA RIMUR'        => 'Nusa Tenggara Timur',
        'PAPUA TENGA'                => 'Papua Tengah',
        'PAPUA PEGUNUNGGAN'          => 'Papua Pegunungan',
        'PAPAU BARAT'                => 'Papua Barat',
        'SULAWASI BARAT'             => 'Sulawesi Barat',
        'SULAWESI SELATANESI'        => 'Sulawesi Selatan',
        'SIAK'                       => 'Riau', // keputusan: Siak (nama Kabupaten) -> Provinsi Riau
    ];

    /** Alias untuk nama Kabupaten/Kota yang berubah resmi, pakai nama ibukota,
     *  atau singkatan — ditemukan dari analisis laporan unresolved. */
    private const KABKOTA_ALIASES = [
        'TOBA SAMOSIR'              => 'TOBA', // ganti nama resmi 2020
        'MALUKU TENGGARA BARAT'     => 'KEPULAUAN TANIMBAR', // ganti nama resmi 2008
        'TTS'                       => 'TIMOR TENGAH SELATAN',
        'MINSEL'                    => 'MINAHASA SELATAN',
        'KUALA KAPUAS'              => 'KAPUAS', // nama ibukota, bukan nama kabupaten
        'TANJUNG SELOR'             => 'BULUNGAN', // nama ibukota
        'AMURANG'                   => 'MINAHASA SELATAN', // nama ibukota
        'TIMIKA'                    => 'MIMIKA', // nama ibukota
        'MIMIKA BARU'               => 'MIMIKA', // nama kecamatan, bukan kabupaten
        'JAYAPURA KOTA'             => 'KOTA JAYAPURA', // urutan kata terbalik
        'DELIYAI'                   => 'DEIYAI', // typo/ejaan alternatif
    ];

    /** Cache lookup supaya tidak query berulang-ulang untuk nilai yang sama. */
    private array $cache = [];

    private array $unresolvedKabKota = [];
    private int $totalRows = 0;
    private int $inserted = 0;
    private int $skipped = 0;

    public function run()
    {
        if (! file_exists(self::CSV_PATH)) {
            echo "File CSV tidak ditemukan di: " . self::CSV_PATH . "\n";
            echo "Pastikan sudah ditaruh di writable/imports/pegawai_import.csv\n";
            return;
        }

        $handle = fopen(self::CSV_PATH, 'r');
        $header = fgetcsv($handle);

        if ($header === false) {
            echo "File CSV kosong atau gagal dibaca.\n";
            return;
        }

        // Bersihkan BOM (byte-order-mark) yang sering nempel di CSV hasil
        // export Excel, supaya nama kolom pertama tidak salah baca.
        $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);
        $header = array_map('trim', $header);
        $colIndex = array_flip($header);

        $requiredColumns = [
            'Nama Lengkap', 'Agama', 'Jenis Kelamin', 'Jenjang Pendidikan',
            'Level Jabatan', 'Pangkat', 'Gol Ruang', 'Tmt Cpns', 'Tmt Pangkat',
            'Tipe Jabatan', 'Tampil Jabatan', 'Satker 1', 'Satker 3', 'Satker 4',
            'Provinsi', 'Kab Kota', 'Status Pegawai',
        ];

        foreach ($requiredColumns as $col) {
            if (! isset($colIndex[$col])) {
                echo "Kolom wajib '{$col}' tidak ditemukan di header CSV. Import dibatalkan.\n";
                fclose($handle);
                return;
            }
        }

        $batch = [];
        $now = date('Y-m-d H:i:s');

        while (($row = fgetcsv($handle)) !== false) {
            $this->totalRows++;
            $get = fn (string $col) => trim($row[$colIndex[$col]] ?? '');

            $satker4 = $get('Satker 4');
            $satker3 = $get('Satker 3');
            $satker1 = $get('Satker 1');

            $unitKerjaId = $this->findIdByName('unit_kerja', $satker4);
            $satuanKerjaId = $satker3 !== '' && $unitKerjaId
                ? $this->findIdByNameAndParent('satuan_kerja', $satker3, $unitKerjaId)
                : null;
            $satuanKerja2Id = $satker1 !== '' && $satuanKerjaId
                ? $this->findIdByNameAndParent('satuan_kerja_2', $satker1, $satuanKerjaId)
                : null;

            $provinsiRaw = $get('Provinsi');
            $provinsiNama = $this->normalizeProvinsi($provinsiRaw);
            $provinsiId = $provinsiNama ? $this->findIdByName('provinsi', $provinsiNama) : null;

            $kabKotaRaw = $get('Kab Kota');
            $kabupatenKotaId = ($kabKotaRaw !== '')
                ? $this->resolveKabupatenKota($kabKotaRaw, $provinsiId)
                : null;

            if ($kabKotaRaw !== '' && $kabupatenKotaId === null) {
                $this->unresolvedKabKota[] = "{$kabKotaRaw} (baris " . ($this->totalRows + 1) . ", provinsi: {$provinsiNama})";
            }

            $namaLengkap = $get('Nama Lengkap');
            if ($namaLengkap === '') {
                $this->skipped++;
                continue; // nama lengkap wajib ada, skip baris tanpa nama
            }

            $batch[] = [
                'nip'                => null,
                'nama_lengkap'       => $namaLengkap,
                'kategori'           => $satker4 === 'Direktorat Jenderal Bimbingan Masyarakat Kristen' ? 'pusat' : 'daerah',
                'agama'              => $get('Agama') ?: null,
                'jenis_kelamin'      => $this->mapJenisKelamin($get('Jenis Kelamin')),
                'jenjang_pendidikan' => $get('Jenjang Pendidikan') ?: null,
                'status_pegawai'     => $get('Status Pegawai') ?: null,
                'level_jabatan_id'   => $this->findIdByName('level_jabatan', $get('Level Jabatan')),
                'pangkat_id'         => $this->findIdByName('pangkat', $get('Pangkat')),
                'golongan_ruang_id'  => $this->findIdByName('golongan_ruang', $get('Gol Ruang')),
                'tipe_jabatan_id'    => $this->findIdByName('tipe_jabatan', $get('Tipe Jabatan')),
                'tampil_jabatan_id'  => $this->findIdByName('tampil_jabatan', $get('Tampil Jabatan')),
                'unit_kerja_id'      => $unitKerjaId,
                'satuan_kerja_id'    => $satuanKerjaId,
                'satuan_kerja_2_id'  => $satuanKerja2Id,
                'provinsi_id'        => $provinsiId,
                'kabupaten_kota_id'  => $kabupatenKotaId,
                'tmt_cpns'           => $this->convertDate($get('Tmt Cpns')),
                'tmt_pangkat'        => $this->convertDate($get('Tmt Pangkat')),
                'created_at'         => $now,
                'updated_at'         => $now,
            ];

            // Insert per batch 200 baris — lebih cepat daripada insert satu-satu,
            // dan tidak terlalu besar sampai bikin query timeout.
            if (count($batch) >= 200) {
                $this->db->table('pegawai')->insertBatch($batch);
                $this->inserted += count($batch);
                echo "  ... {$this->inserted} baris ter-insert\n";
                $batch = [];
            }
        }

        if (! empty($batch)) {
            $this->db->table('pegawai')->insertBatch($batch);
            $this->inserted += count($batch);
        }

        fclose($handle);

        echo "\n=== SELESAI ===\n";
        echo "Total baris di CSV: {$this->totalRows}\n";
        echo "Berhasil di-insert: {$this->inserted}\n";
        echo "Dilewati (nama lengkap kosong): {$this->skipped}\n";
        echo "Kab/Kota TIDAK ke-resolve otomatis: " . count($this->unresolvedKabKota) . "\n";

        if (! empty($this->unresolvedKabKota)) {
            $reportPath = WRITEPATH . 'imports/unresolved_kabkota_report.txt';
            file_put_contents($reportPath, implode("\n", $this->unresolvedKabKota));
            echo "Detail disimpan di: {$reportPath}\n";
            echo "(Baris-baris ini TETAP ter-import, cuma kabupaten_kota_id-nya NULL — perlu dilengkapi manual)\n";
        }
    }

    // =========================================================
    // HELPER
    // =========================================================

    private function mapJenisKelamin(string $raw): ?string
    {
        $t = strtolower($raw);
        if ($t === 'laki-laki') return 'L';
        if ($t === 'perempuan') return 'P';
        return null;
    }

    private function convertDate(string $raw): ?string
    {
        if ($raw === '') {
            return null;
        }
        $date = \DateTime::createFromFormat('d-m-Y', $raw);
        return $date ? $date->format('Y-m-d') : null;
    }

    private function normalizeProvinsi(string $raw): ?string
    {
        if ($raw === '') {
            return null;
        }
        $t = strtoupper(trim($raw));
        $t = preg_replace('/^PROVINSI\s*/', '', $t);
        $t = preg_replace('/\s+/', ' ', $t);
        $t = rtrim($t, '.');

        if ($t === '' || $t === 'PROVINSI') {
            return null;
        }

        if (isset(self::PROVINSI_ALIASES[$t])) {
            return self::PROVINSI_ALIASES[$t];
        }

        // Cocokkan case-insensitive langsung ke tabel provinsi
        $cacheKey = 'provinsi_raw_' . $t;
        if (! isset($this->cache[$cacheKey])) {
            $row = $this->db->table('provinsi')
                ->select('nama')
                ->where('UPPER(nama)', $t)
                ->get()->getRowArray();
            $this->cache[$cacheKey] = $row['nama'] ?? $raw; // fallback ke raw kalau tak ketemu (biar findIdByName yang gagal & ke-log)
        }

        return $this->cache[$cacheKey];
    }

    /**
     * Normalisasi teks Kab/Kota mentah: buang bagian "/Ibukota", buang isi
     * dalam kurung, cek alias, lalu strip prefix Kabupaten/Kota (termasuk
     * "Kota Administrasi" untuk kasus DKI Jakarta).
     */
    private function cleanKabKotaText(string $raw): string
    {
        $t = strtoupper(trim($raw));

        // "Rote Ndao / Ba'a" -> "ROTE NDAO" (ambil bagian sebelum slash)
        if (strpos($t, '/') !== false) {
            $t = trim(explode('/', $t)[0]);
        }

        // "Kab. Deiyai (Deliyai)" -> "KAB. DEIYAI" ; "KEDIRI (KAB)" -> "KEDIRI"
        $t = trim(preg_replace('/\(.*?\)/', '', $t));

        // Cek alias SEBELUM strip prefix (beberapa alias sudah tanpa prefix)
        if (isset(self::KABKOTA_ALIASES[$t])) {
            $t = self::KABKOTA_ALIASES[$t];
        }

        // Strip prefix "Kabupaten"/"Kab."/"Kota Administrasi"/"Kota" dengan
        // nol-atau-lebih spasi sesudahnya (menangani "Kab.Sorong" tanpa spasi)
        $t = preg_replace('/^(KABUPATEN|KAB\.?|KOTA\s*(ADMINISTRASI|ADM\.?)?)\s*/', '', $t);
        $t = trim(preg_replace('/\s+/', ' ', $t));

        // Cek alias LAGI setelah strip prefix (untuk alias yang aslinya
        // sudah tanpa prefix, misal "TOBA SAMOSIR" -> "TOBA")
        return self::KABKOTA_ALIASES[$t] ?? $t;
    }

    /**
     * Resolve Kabupaten/Kota. Strategi berlapis:
     * 1. Exact/fuzzy match dalam SCOPE provinsi yang sudah resolve
     * 2. Kalau gagal & provinsi termasuk "keluarga Papua" (yang kena
     *    pemekaran 2022), perluas pencarian ke SEMUA provinsi yang
     *    namanya mengandung "Papua" — karena data sumber sering masih
     *    pakai batas provinsi lama sebelum pemekaran
     * 3. Kalau provinsi tidak diketahui (kosong di data sumber), coba
     *    cari ke SELURUH kabupaten/kota secara nasional (tanpa scope)
     */
    private function resolveKabupatenKota(string $raw, ?int $provinsiId): ?int
    {
        $cleaned = $this->cleanKabKotaText($raw);
        $cacheKey = 'kabkota_' . ($provinsiId ?? 'null') . '_' . $cleaned;
        if (array_key_exists($cacheKey, $this->cache)) {
            return $this->cache[$cacheKey];
        }

        $result = null;

        if ($provinsiId !== null) {
            $result = $this->searchKabKotaInProvinces($cleaned, [$provinsiId]);

            if ($result === null) {
                $provinsiNama = $this->db->table('provinsi')->select('nama')
                    ->where('id', $provinsiId)->get()->getRowArray()['nama'] ?? '';

                if (stripos($provinsiNama, 'Papua') !== false) {
                    $papuaProvinceIds = array_column(
                        $this->db->table('provinsi')->select('id')
                            ->like('nama', 'Papua')->get()->getResultArray(),
                        'id'
                    );
                    $result = $this->searchKabKotaInProvinces($cleaned, $papuaProvinceIds);
                }
            }
        } else {
            // Provinsi tidak diketahui dari data sumber — cari nasional
            $allProvinceIds = array_column(
                $this->db->table('provinsi')->select('id')->get()->getResultArray(),
                'id'
            );
            $result = $this->searchKabKotaInProvinces($cleaned, $allProvinceIds, exactOnly: true);
        }

        return $this->cache[$cacheKey] = $result;
    }

    /**
     * Cari nama yang sudah dibersihkan di antara kabupaten/kota milik
     * daftar provinsi tertentu. exactOnly=true dipakai untuk pencarian
     * nasional tanpa scope provinsi — di situ fuzzy match terlalu
     * berisiko salah tempel (candidate pool terlalu besar & beragam).
     */
    private function searchKabKotaInProvinces(string $cleaned, array $provinsiIds, bool $exactOnly = false): ?int
    {
        if (empty($provinsiIds)) {
            return null;
        }

        $candidates = $this->db->table('kabupaten_kota')
            ->select('id, nama')
            ->whereIn('parent_id', $provinsiIds)
            ->get()->getResultArray();

        $bestId = null;
        $bestScore = PHP_INT_MAX;

        foreach ($candidates as $c) {
            $candidateClean = $this->cleanKabKotaText($c['nama']);

            if ($candidateClean === $cleaned) {
                return (int) $c['id']; // exact match, langsung return
            }

            if (! $exactOnly) {
                $distance = levenshtein($cleaned, $candidateClean);
                if ($distance < $bestScore) {
                    $bestScore = $distance;
                    $bestId = (int) $c['id'];
                }
            }
        }

        // Ambang toleransi typo — dinaikkan sedikit dari versi awal (3 -> 4)
        // karena pool kandidat sudah dipersempit per-provinsi, jadi risiko
        // salah tempel akibat threshold lebih longgar tetap kecil.
        return (! $exactOnly && $bestScore <= 4) ? $bestId : null;
    }

    private function findIdByName(string $table, string $nama): ?int
    {
        if ($nama === '') {
            return null;
        }
        $cacheKey = "{$table}_{$nama}";
        if (array_key_exists($cacheKey, $this->cache)) {
            return $this->cache[$cacheKey];
        }
        $row = $this->db->table($table)->select('id')->where('nama', $nama)->get()->getRowArray();
        return $this->cache[$cacheKey] = ($row['id'] ?? null);
    }

    private function findIdByNameAndParent(string $table, string $nama, int $parentId): ?int
    {
        if ($nama === '') {
            return null;
        }
        $cacheKey = "{$table}_{$nama}_{$parentId}";
        if (array_key_exists($cacheKey, $this->cache)) {
            return $this->cache[$cacheKey];
        }
        $row = $this->db->table($table)->select('id')
            ->where('nama', $nama)->where('parent_id', $parentId)
            ->get()->getRowArray();
        return $this->cache[$cacheKey] = ($row['id'] ?? null);
    }
}
