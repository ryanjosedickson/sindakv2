<?php

namespace App\Models;

use CodeIgniter\Model;

class PegawaiModel extends Model
{
    protected $table            = 'pegawai';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;

    protected $allowedFields = [
        'nip',
        'nama_lengkap',
        'kategori',
        'agama',
        'jenis_kelamin',
        'jenjang_pendidikan',
        'status_pegawai',
        'level_jabatan_id',
        'pangkat_id',
        'golongan_ruang_id',
        'tipe_jabatan',
        'tampil_jabatan_id',
        'unit_kerja_id',
        'satuan_kerja_id',
        'satuan_kerja_2_id',
        'provinsi_id',
        'kabupaten_kota_id',
        'tmt_cpns',
        'tmt_pangkat',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validasi dasar di level model — lapisan pertahanan kedua selain
    // validasi di controller (defense in depth).
    protected $validationRules = [
        // Rule untuk field 'id' WAJIB didaftarkan (sejak CI4 4.3.5) supaya
        // placeholder {id} di rule 'nip' di bawah bisa tergantikan dengan
        // benar saat update — kalau tidak, is_unique akan selalu anggap
        // tidak ada pengecualian, sehingga data yang di-edit dianggap
        // "duplikat" terhadap dirinya sendiri.
        'id'           => 'permit_empty|is_natural_no_zero',
        // 'permit_empty' — NIP boleh kosong (untuk data hasil bulk import
        // yang belum ada NIP-nya), tapi KALAU diisi, tetap harus 18 digit,
        // angka semua, dan unik. Ini beda dengan 'required' yang akan
        // menolak form kalau kosong sama sekali.
        'nip'          => 'permit_empty|exact_length[18]|numeric|is_unique[pegawai.nip,id,{id}]',
        'nama_lengkap' => 'required|max_length[150]',
        'kategori'     => 'required|in_list[pusat,daerah]',
    ];

    protected $validationMessages = [
        'nip' => [
            'exact_length' => 'NIP harus tepat 18 digit.',
            'numeric'      => 'NIP hanya boleh berisi angka.',
            'is_unique'    => 'NIP ini sudah terdaftar di sistem.',
        ],
    ];

    // =========================================================
    // FILTER LIST PEGAWAI
    // =========================================================

    /** Unit Kerja tunggal untuk semua pegawai pusat (tidak perlu filter). */
    public const UNIT_KERJA_PUSAT = 'Direktorat Jenderal Bimbingan Masyarakat Kristen';

    /** Nilai khusus di dropdown filter: cari baris yang kolomnya masih kosong. */
    public const FILTER_KOSONG = '__kosong__';

    public const AGAMA = ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu'];
    public const JENIS_KELAMIN = ['L' => 'Laki-laki', 'P' => 'Perempuan'];
    public const JENJANG_PENDIDIKAN = [
        'SD', 'SLTP/SMP SEDERAJAT', 'SLTA/SMA SEDERAJAT',
        'D II', 'Diploma III/Sarjana Muda', 'Diploma IV',
        'S-1/Sarjana', 'S-2/Magister', 'S-3/Doktor',
    ];
    public const TIPE_JABATAN = ['Jabatan Struktural', 'Jabatan Fungsional Umum', 'Jabatan Fungsional Tertentu'];
    public const STATUS_PEGAWAI = ['PNS', 'PPPK', 'CPNS'];

    /** Filter berbasis nilai ENUM tetap: kolom => daftar nilai yang valid. */
    private function enumFilterOptions(): array
    {
        return [
            'agama'              => self::AGAMA,
            'jenis_kelamin'      => array_keys(self::JENIS_KELAMIN),
            'jenjang_pendidikan' => self::JENJANG_PENDIDIKAN,
            'tipe_jabatan'       => self::TIPE_JABATAN,
            'status_pegawai'     => self::STATUS_PEGAWAI,
        ];
    }

    /** Filter berbasis ID lookup table. */
    public const ID_FILTERS = [
        'level_jabatan_id', 'pangkat_id', 'golongan_ruang_id', 'tampil_jabatan_id',
        'unit_kerja_id', 'satuan_kerja_id', 'satuan_kerja_2_id',
        'provinsi_id', 'kabupaten_kota_id',
    ];

    /** Filter berbasis tahun dari kolom tanggal. */
    public const YEAR_FILTERS = ['tmt_cpns', 'tmt_pangkat'];

    /** Semua nama parameter filter yang dikenali (untuk dibaca dari URL). */
    public static function filterKeys(): array
    {
        return array_merge(
            ['agama', 'jenis_kelamin', 'jenjang_pendidikan', 'tipe_jabatan', 'status_pegawai'],
            self::ID_FILTERS,
            self::YEAR_FILTERS
        );
    }

    /**
     * Siapkan query list pegawai untuk satu kategori + filter, lengkap dengan
     * nama dari tabel lookup. Mengembalikan $this supaya bisa langsung
     * disambung ->paginate($perPage).
     *
     * Semua nama kolom berasal dari whitelist di atas, dan semua nilai
     * divalidasi (angka di-cast int, ENUM dicocokkan ke daftar valid) —
     * nilai dari URL tidak pernah masuk ke SQL mentah begitu saja.
     * Filter dengan nilai tidak valid diabaikan diam-diam.
     */
    public function listQuery(string $kategori, array $filters = []): self
    {
        $this->select('
                pegawai.*,
                level_jabatan.nama   AS level_jabatan_nama,
                pangkat.nama         AS pangkat_nama,
                golongan_ruang.nama  AS golongan_ruang_nama,
                tampil_jabatan.nama  AS tampil_jabatan_nama,
                unit_kerja.nama      AS unit_kerja_nama,
                satuan_kerja.nama    AS satuan_kerja_nama,
                satuan_kerja_2.nama  AS satuan_kerja_2_nama,
                provinsi.nama        AS provinsi_nama,
                kabupaten_kota.nama  AS kabupaten_kota_nama
            ')
            ->join('level_jabatan', 'level_jabatan.id = pegawai.level_jabatan_id', 'left')
            ->join('pangkat', 'pangkat.id = pegawai.pangkat_id', 'left')
            ->join('golongan_ruang', 'golongan_ruang.id = pegawai.golongan_ruang_id', 'left')
            ->join('tampil_jabatan', 'tampil_jabatan.id = pegawai.tampil_jabatan_id', 'left')
            ->join('unit_kerja', 'unit_kerja.id = pegawai.unit_kerja_id', 'left')
            ->join('satuan_kerja', 'satuan_kerja.id = pegawai.satuan_kerja_id', 'left')
            ->join('satuan_kerja_2', 'satuan_kerja_2.id = pegawai.satuan_kerja_2_id', 'left')
            ->join('provinsi', 'provinsi.id = pegawai.provinsi_id', 'left')
            ->join('kabupaten_kota', 'kabupaten_kota.id = pegawai.kabupaten_kota_id', 'left')
            ->where('pegawai.kategori', $kategori)
            ->orderBy('pegawai.id', 'ASC'); // urutan stabil supaya pagination konsisten

        // ENUM
        foreach ($this->enumFilterOptions() as $col => $validValues) {
            $val = $filters[$col] ?? null;
            if ($val === null) {
                continue;
            }
            if ($val === self::FILTER_KOSONG) {
                $this->where("pegawai.{$col} IS NULL", null, false);
            } elseif (in_array($val, $validValues, true)) {
                $this->where("pegawai.{$col}", $val);
            }
        }

        // ID lookup
        foreach (self::ID_FILTERS as $col) {
            $val = $filters[$col] ?? null;
            if ($val === null) {
                continue;
            }
            if ($val === self::FILTER_KOSONG) {
                $this->where("pegawai.{$col} IS NULL", null, false);
            } elseif (ctype_digit((string) $val)) {
                $this->where("pegawai.{$col}", (int) $val);
            }
        }

        // Tahun TMT
        foreach (self::YEAR_FILTERS as $col) {
            $val = $filters[$col] ?? null;
            if ($val === null) {
                continue;
            }
            if ($val === self::FILTER_KOSONG) {
                $this->where("pegawai.{$col} IS NULL", null, false);
            } elseif (preg_match('/^\d{4}$/', (string) $val)) {
                $this->where("YEAR(pegawai.{$col}) = " . (int) $val, null, false);
            }
        }

        return $this;
    }

    /**
     * Daftar tahun yang benar-benar ada di kolom tanggal (untuk dropdown
     * filter TMT), urut dari terbaru.
     */
    public function tmtYears(string $kolom, string $kategori): array
    {
        if (! in_array($kolom, self::YEAR_FILTERS, true)) {
            return [];
        }

        $rows = $this->db->table('pegawai')
            ->select("DISTINCT YEAR({$kolom}) AS tahun", false)
            ->where('kategori', $kategori)
            ->where('deleted_at IS NULL', null, false)
            ->where("{$kolom} IS NOT NULL", null, false)
            ->orderBy('tahun', 'DESC')
            ->get()->getResultArray();

        return array_column($rows, 'tahun');
    }
}
