<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * LookupModel — model generik untuk SEMUA tabel referensi/lookup
 * (level_jabatan, pangkat, golongan_ruang, tipe_jabatan, tampil_jabatan,
 * unit_kerja, satuan_kerja, satuan_kerja_2, provinsi, kabupaten_kota).
 *
 * Dipakai dengan cara di-instantiate langsung sambil kasih nama tabel,
 * BUKAN dengan bikin class baru per tabel:
 *
 *   $model = new LookupModel('unit_kerja');
 *   $unitKerjaList = $model->findAll();
 *
 *   $model = new LookupModel('satuan_kerja');
 *   $satkerDiBawahUnit = $model->where('parent_id', $unitKerjaId)->findAll();
 *
 * Kolom 'parent_id' cuma benar-benar dipakai di tabel yang hierarkis
 * (satuan_kerja, satuan_kerja_2, kabupaten_kota). Untuk tabel lookup
 * biasa (level_jabatan, dst.) kolom ini tidak ada di tabelnya — tidak
 * masalah, karena kita tidak pernah mengirim 'parent_id' saat insert/update
 * ke tabel yang memang tidak punya kolom itu.
 */
class LookupModel extends Model
{
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';

    protected $allowedFields = ['nama', 'parent_id', 'is_active'];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function __construct(string $table)
    {
        $this->table = $table;
        parent::__construct();
    }

    /**
     * Ambil daftar anak (child) dari satu parent tertentu.
     * Contoh: (new LookupModel('kabupaten_kota'))->findByParent($provinsiId)
     */
    public function findByParent(int $parentId): array
    {
        return $this->where('parent_id', $parentId)
            ->where('is_active', 1)
            ->orderBy('nama', 'ASC')
            ->findAll();
    }

    /**
     * Ambil semua yang aktif, urut abjad — dipakai untuk tabel lookup
     * yang TIDAK hierarkis (level_jabatan, pangkat, dst.)
     */
    public function findAllActive(): array
    {
        return $this->where('is_active', 1)
            ->orderBy('nama', 'ASC')
            ->findAll();
    }
}
