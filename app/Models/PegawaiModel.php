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
        'tipe_jabatan_id',
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
        'nip'          => 'required|exact_length[18]|numeric|is_unique[pegawai.nip,id,{id}]',
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

    /**
     * Ambil daftar pegawai berdasarkan kategori (pusat/daerah), lengkap
     * dengan nama-nama dari tabel lookup (bukan cuma ID mentah) — dipakai
     * untuk tampilan list, supaya tidak perlu N+1 query di view.
     */
    public function findByKategori(string $kategori): array
    {
        return $this->select('
                pegawai.*,
                level_jabatan.nama as level_jabatan_nama,
                pangkat.nama as pangkat_nama,
                golongan_ruang.nama as golongan_ruang_nama,
                unit_kerja.nama as unit_kerja_nama,
                provinsi.nama as provinsi_nama,
                kabupaten_kota.nama as kabupaten_kota_nama
            ')
            ->join('level_jabatan', 'level_jabatan.id = pegawai.level_jabatan_id', 'left')
            ->join('pangkat', 'pangkat.id = pegawai.pangkat_id', 'left')
            ->join('golongan_ruang', 'golongan_ruang.id = pegawai.golongan_ruang_id', 'left')
            ->join('unit_kerja', 'unit_kerja.id = pegawai.unit_kerja_id', 'left')
            ->join('provinsi', 'provinsi.id = pegawai.provinsi_id', 'left')
            ->join('kabupaten_kota', 'kabupaten_kota.id = pegawai.kabupaten_kota_id', 'left')
            ->where('pegawai.kategori', $kategori)
            ->findAll();
    }
}
