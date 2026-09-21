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

    /**
     * List pegawai, bisa difilter kategori (pusat/daerah) lewat query
     * string ?kategori=pusat — default tampilkan pusat.
     */
    public function index()
    {
        $kategori = $this->request->getGet('kategori') ?? 'pusat';

        if (! in_array($kategori, ['pusat', 'daerah'], true)) {
            $kategori = 'pusat';
        }

        return view('pegawai/index', [
            'kategori'     => $kategori,
            'daftarPegawai'=> $this->pegawaiModel->findByKategori($kategori),
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
            'nip'                => $this->request->getPost('nip'),
            'nama_lengkap'       => $this->request->getPost('nama_lengkap'),
            'kategori'           => $this->request->getPost('kategori'),
            'agama'              => $this->request->getPost('agama') ?: null,
            'jenis_kelamin'      => $this->request->getPost('jenis_kelamin') ?: null,
            'jenjang_pendidikan' => $this->request->getPost('jenjang_pendidikan') ?: null,
            'status_pegawai'     => $this->request->getPost('status_pegawai') ?: null,
            'level_jabatan_id'   => $this->request->getPost('level_jabatan_id') ?: null,
            'pangkat_id'         => $this->request->getPost('pangkat_id') ?: null,
            'golongan_ruang_id'  => $this->request->getPost('golongan_ruang_id') ?: null,
            'tipe_jabatan_id'    => $this->request->getPost('tipe_jabatan_id') ?: null,
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
            'tipe_jabatan'    => (new LookupModel('tipe_jabatan'))->findAllActive(),
            'tampil_jabatan'  => (new LookupModel('tampil_jabatan'))->findAllActive(),
            'unit_kerja'      => (new LookupModel('unit_kerja'))->findAllActive(),
            'satuan_kerja'    => (new LookupModel('satuan_kerja'))->findAllActive(),
            'satuan_kerja_2'  => (new LookupModel('satuan_kerja_2'))->findAllActive(),
            'provinsi'        => (new LookupModel('provinsi'))->findAllActive(),
            'kabupaten_kota'  => (new LookupModel('kabupaten_kota'))->findAllActive(),
        ];
    }
}
