<?php

namespace App\Controllers;

use App\Models\LookupModel;
use App\Models\PegawaiModel;

class Pegawai extends BaseController
{
    protected PegawaiModel $pegawaiModel;

    public function __construct()
    {
        $this->pegawaiModel = new PegawaiModel();
    }

    /** Pilihan jumlah data per halaman (mirip phpMyAdmin). */
    private const PER_PAGE_OPTIONS = [10, 25, 50, 100, 250, 500];
    private const DEFAULT_PER_PAGE = 25;

    /**
     * List pegawai: tab kategori (pusat/daerah), filter per kolom, pilihan
     * jumlah data per halaman, dan pagination — semuanya lewat query string
     * (?kategori=daerah&provinsi_id=12&per_page=50&page=2), jadi hasil
     * filter bisa di-bookmark/dibagikan lewat URL.
     */
    public function index()
    {
        $kategori = $this->request->getGet('kategori') ?? 'pusat';
        if (! in_array($kategori, ['pusat', 'daerah'], true)) {
            $kategori = 'pusat';
        }

        $perPage = (int) $this->request->getGet('per_page');
        if (! in_array($perPage, self::PER_PAGE_OPTIONS, true)) {
            $perPage = self::DEFAULT_PER_PAGE;
        }

        // Ambil hanya parameter filter yang dikenali & tidak kosong.
        $filters = [];
        foreach (PegawaiModel::filterKeys() as $key) {
            $val = $this->request->getGet($key);
            if (is_string($val) && $val !== '') {
                $filters[$key] = $val;
            }
        }
        // Unit Kerja untuk pegawai pusat selalu sama (Ditjen), tidak difilter.
        if ($kategori === 'pusat') {
            unset($filters['unit_kerja_id']);
        }

        $daftarPegawai = $this->pegawaiModel->listQuery($kategori, $filters)->paginate($perPage);
        $pager         = $this->pegawaiModel->pager;

        // Data untuk dropdown filter -------------------------------------
        $unitKerjaModel = new LookupModel('unit_kerja');
        $pusatUnit = $unitKerjaModel->where('nama', PegawaiModel::UNIT_KERJA_PUSAT)->first();

        $unitKerjaList = array_values(array_filter(
            $unitKerjaModel->findAllActive(),
            static fn ($u) => $u['nama'] !== PegawaiModel::UNIT_KERJA_PUSAT
        ));

        $cascade = static fn (string $tabel) => (new LookupModel($tabel))
            ->select('id, nama, parent_id')
            ->where('is_active', 1)
            ->orderBy('nama', 'ASC')
            ->findAll();

        // Query string dasar untuk link pagination (semua kecuali 'page').
        $baseQuery = array_merge(['kategori' => $kategori, 'per_page' => $perPage], $filters);

        $total = $pager->getTotal();

        return view('pegawai/index', [
            'kategori'      => $kategori,
            'daftarPegawai' => $daftarPegawai,
            'filters'       => $filters,
            'perPage'       => $perPage,
            'perPageOptions'=> self::PER_PAGE_OPTIONS,
            'currentPage'   => $pager->getCurrentPage(),
            'pageCount'     => $pager->getPageCount(),
            'total'         => $total,
            'firstItem'     => $total === 0 ? 0 : (($pager->getCurrentPage() - 1) * $perPage) + 1,
            'lastItem'      => min($total, $pager->getCurrentPage() * $perPage),
            'baseQuery'     => $baseQuery,
            'dropdowns'     => [
                'level_jabatan'  => (new LookupModel('level_jabatan'))->findAllActive(),
                'pangkat'        => (new LookupModel('pangkat'))->findAllActive(),
                'golongan_ruang' => (new LookupModel('golongan_ruang'))->findAllActive(),
                'tampil_jabatan' => (new LookupModel('tampil_jabatan'))->findAllActive(),
                'unit_kerja'     => $unitKerjaList,
                'provinsi'       => (new LookupModel('provinsi'))->findAllActive(),
                'tahun_tmt_cpns'    => $this->pegawaiModel->tmtYears('tmt_cpns', $kategori),
                'tahun_tmt_pangkat' => $this->pegawaiModel->tmtYears('tmt_pangkat', $kategori),
            ],
            // Data bertingkat: diisi ke dropdown anak lewat JavaScript
            'cascadeData'   => [
                'satuanKerja'  => $cascade('satuan_kerja'),
                'satuanKerja2' => $cascade('satuan_kerja_2'),
                'kabKota'      => $cascade('kabupaten_kota'),
                'pusatUnitId'  => $pusatUnit['id'] ?? null,
            ],
        ]);
    }

    /**
     * Form tambah pegawai baru.
     */
    public function create()
    {
        return view('pegawai/form', [
            'pegawai' => null, // null = mode tambah, bukan edit
            'lookups' => $this->getLookupLists(),
        ]);
    }

    /**
     * Proses simpan pegawai baru.
     */
    public function store()
    {
        $data = $this->extractPegawaiInput();

        // insert() otomatis menjalankan $validationRules yang ada di
        // PegawaiModel (termasuk cek NIP 18 digit & unik).
        if (! $this->pegawaiModel->insert($data)) {
            return redirect()->back()->withInput()->with('errors', $this->pegawaiModel->errors());
        }

        return redirect()->to('/admin/pegawai?kategori=' . $data['kategori'])
            ->with('success', 'Data pegawai berhasil ditambahkan.');
    }

    /**
     * Form edit pegawai.
     */
    public function edit(int $id)
    {
        $pegawai = $this->pegawaiModel->find($id);

        if (! $pegawai) {
            return redirect()->to('/admin/pegawai')->with('error', 'Data pegawai tidak ditemukan.');
        }

        return view('pegawai/form', [
            'pegawai' => $pegawai,
            'lookups' => $this->getLookupLists(),
        ]);
    }

    /**
     * Proses update pegawai.
     */
    public function update(int $id)
    {
        $pegawai = $this->pegawaiModel->find($id);

        if (! $pegawai) {
            return redirect()->to('/admin/pegawai')->with('error', 'Data pegawai tidak ditemukan.');
        }

        $data = $this->extractPegawaiInput();

        // Sertakan 'id' eksplisit di $data — CI4 (sejak 4.3.5) TIDAK lagi
        // otomatis mengisi placeholder {id} di validationRules hanya dari
        // parameter $id di update(). Field 'id' harus benar-benar ada di
        // array $data, dan rule untuk 'id' harus didaftarkan juga di
        // PegawaiModel::$validationRules (sudah ditambahkan di sana).
        $data['id'] = $id;

        if (! $this->pegawaiModel->update($id, $data)) {
            return redirect()->back()->withInput()->with('errors', $this->pegawaiModel->errors());
        }

        return redirect()->to('/admin/pegawai?kategori=' . $data['kategori'])
            ->with('success', 'Data pegawai berhasil diperbarui.');
    }

    /**
     * Soft delete pegawai.
     */
    public function delete(int $id)
    {
        $pegawai = $this->pegawaiModel->find($id);

        if (! $pegawai) {
            return redirect()->to('/admin/pegawai')->with('error', 'Data pegawai tidak ditemukan.');
        }

        $this->pegawaiModel->delete($id); // soft delete (deleted_at diisi, bukan DELETE permanen)

        return redirect()->to('/admin/pegawai?kategori=' . $pegawai['kategori'])
            ->with('success', 'Data pegawai berhasil dihapus.');
    }

    // =========================================================
    // HELPER PRIVATE
    // =========================================================

    /**
     * Ambil input form jadi array siap simpan. Dipusatkan di sini supaya
     * store() dan update() tidak duplikasi kode.
     */
    private function extractPegawaiInput(): array
    {
        return [
            // ?: null penting — kalau NIP dikosongkan di form, harus benar-benar
            // tersimpan sebagai NULL (bukan string kosong ''), supaya tidak
            // bentrok dengan unique constraint saat ada banyak pegawai yang
            // sama-sama belum diisi NIP-nya (data hasil bulk import).
            'nip'                => $this->request->getPost('nip') ?: null,
            'nama_lengkap'       => $this->request->getPost('nama_lengkap'),
            'kategori'           => $this->request->getPost('kategori'),
            'agama'              => $this->request->getPost('agama') ?: null,
            'jenis_kelamin'      => $this->request->getPost('jenis_kelamin') ?: null,
            'jenjang_pendidikan' => $this->request->getPost('jenjang_pendidikan') ?: null,
            'status_pegawai'     => $this->request->getPost('status_pegawai') ?: null,
            'level_jabatan_id'   => $this->request->getPost('level_jabatan_id') ?: null,
            'pangkat_id'         => $this->request->getPost('pangkat_id') ?: null,
            'golongan_ruang_id'  => $this->request->getPost('golongan_ruang_id') ?: null,
            'tipe_jabatan'       => $this->request->getPost('tipe_jabatan') ?: null,
            'tampil_jabatan_id'  => $this->request->getPost('tampil_jabatan_id') ?: null,
            'unit_kerja_id'      => $this->request->getPost('unit_kerja_id') ?: null,
            'satuan_kerja_id'    => $this->request->getPost('satuan_kerja_id') ?: null,
            'satuan_kerja_2_id'  => $this->request->getPost('satuan_kerja_2_id') ?: null,
            'provinsi_id'        => $this->request->getPost('provinsi_id') ?: null,
            'kabupaten_kota_id'  => $this->request->getPost('kabupaten_kota_id') ?: null,
            'tmt_cpns'           => $this->request->getPost('tmt_cpns') ?: null,
            'tmt_pangkat'        => $this->request->getPost('tmt_pangkat') ?: null,
        ];
    }

    /**
     * Ambil semua daftar lookup sekaligus untuk dropdown form.
     * Dipusatkan supaya create() dan edit() tidak duplikasi kode.
     *
     * CATATAN: selama lookup table masih kosong (menunggu data dari
     * senior kamu), dropdown-nya otomatis kosong juga — ini normal,
     * bukan bug. Tinggal isi tabel lookup-nya nanti, dropdown langsung
     * kebaca tanpa perlu ubah kode form sama sekali.
     */
    private function getLookupLists(): array
    {
        return [
            'level_jabatan'   => (new LookupModel('level_jabatan'))->findAllActive(),
            'pangkat'         => (new LookupModel('pangkat'))->findAllActive(),
            'golongan_ruang'  => (new LookupModel('golongan_ruang'))->findAllActive(),
            // 'tipe_jabatan' dihapus dari sini — sekarang kolom ENUM langsung
            // di tabel pegawai (3 nilai tetap), bukan lookup table lagi.
            'tampil_jabatan'  => (new LookupModel('tampil_jabatan'))->findAllActive(),
            'unit_kerja'      => (new LookupModel('unit_kerja'))->findAllActive(),
            'satuan_kerja'    => (new LookupModel('satuan_kerja'))->findAllActive(),
            'satuan_kerja_2'  => (new LookupModel('satuan_kerja_2'))->findAllActive(),
            'provinsi'        => (new LookupModel('provinsi'))->findAllActive(),
            'kabupaten_kota'  => (new LookupModel('kabupaten_kota'))->findAllActive(),
        ];
    }
}
