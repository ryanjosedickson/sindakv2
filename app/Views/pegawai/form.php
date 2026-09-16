<?php
    // $pegawai === null berarti mode TAMBAH, kalau ada isinya berarti mode EDIT
    $isEdit = $pegawai !== null;
    $formAction = $isEdit
        ? site_url('admin/pegawai/' . $pegawai['id'] . '/edit')
        : site_url('admin/pegawai/tambah');

    /**
     * Helper kecil ambil value: dari old() dulu (kalau validasi gagal &
     * form di-render ulang), baru dari data pegawai (mode edit), baru
     * default kosong.
     */
    if (! function_exists('fieldValue')) {
        function fieldValue(string $key, ?array $pegawai)
        {
            $old = old($key);
            if ($old !== null) {
                return $old;
            }
            return $pegawai[$key] ?? '';
        }
    }
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= $isEdit ? 'Edit' : 'Tambah' ?> Pegawai — SINDAK</title>
</head>
<body>
    <h1><?= $isEdit ? 'Edit Pegawai' : 'Tambah Pegawai' ?></h1>
    <p><a href="<?= site_url('admin/pegawai') ?>">&larr; Kembali ke Daftar Pegawai</a></p>

    <?php if (session()->getFlashdata('errors')): ?>
        <ul style="color:red;">
            <?php foreach (session()->getFlashdata('errors') as $error): ?>
                <li><?= esc($error) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <form action="<?= $formAction ?>" method="post">
        <?= csrf_field() ?>

        <label for="nip">NIP (18 digit)</label><br>
        <input type="text" id="nip" name="nip" maxlength="18"
               value="<?= esc(fieldValue('nip', $pegawai)) ?>" required><br><br>

        <label for="nama_lengkap">Nama Lengkap</label><br>
        <input type="text" id="nama_lengkap" name="nama_lengkap"
               value="<?= esc(fieldValue('nama_lengkap', $pegawai)) ?>" required><br><br>

        <label for="kategori">Kategori</label><br>
        <select id="kategori" name="kategori" required>
            <option value="">-- Pilih --</option>
            <?php foreach (['pusat' => 'Pegawai Pusat', 'daerah' => 'Pegawai Daerah'] as $val => $label): ?>
                <option value="<?= $val ?>" <?= fieldValue('kategori', $pegawai) === $val ? 'selected' : '' ?>>
                    <?= $label ?>
                </option>
            <?php endforeach; ?>
        </select><br><br>

        <label for="status_pegawai">Status Pegawai</label><br>
        <select id="status_pegawai" name="status_pegawai">
            <option value="">-- Pilih --</option>
            <?php foreach (['PNS', 'PPPK', 'CPNS'] as $val): ?>
                <option value="<?= $val ?>" <?= fieldValue('status_pegawai', $pegawai) === $val ? 'selected' : '' ?>>
                    <?= $val ?>
                </option>
            <?php endforeach; ?>
        </select><br><br>

        <label for="jenis_kelamin">Jenis Kelamin</label><br>
        <select id="jenis_kelamin" name="jenis_kelamin">
            <option value="">-- Pilih --</option>
            <option value="L" <?= fieldValue('jenis_kelamin', $pegawai) === 'L' ? 'selected' : '' ?>>Laki-laki</option>
            <option value="P" <?= fieldValue('jenis_kelamin', $pegawai) === 'P' ? 'selected' : '' ?>>Perempuan</option>
        </select><br><br>

        <label for="agama">Agama</label><br>
        <select id="agama" name="agama">
            <option value="">-- Pilih --</option>
            <?php foreach (['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu'] as $val): ?>
                <option value="<?= $val ?>" <?= fieldValue('agama', $pegawai) === $val ? 'selected' : '' ?>><?= $val ?></option>
            <?php endforeach; ?>
        </select><br><br>

        <label for="jenjang_pendidikan">Jenjang Pendidikan</label><br>
        <select id="jenjang_pendidikan" name="jenjang_pendidikan">
            <option value="">-- Pilih --</option>
            <?php foreach (['SD', 'SMP', 'SMA', 'D3', 'S1', 'S2', 'S3'] as $val): ?>
                <option value="<?= $val ?>" <?= fieldValue('jenjang_pendidikan', $pegawai) === $val ? 'selected' : '' ?>><?= $val ?></option>
            <?php endforeach; ?>
        </select><br><br>

        <?php
            // Dropdown lookup — pola sama untuk semua, jadi di-loop
            // supaya tidak menulis <select> yang sama 10x manual.
            $lookupFields = [
                'level_jabatan_id'   => ['label' => 'Level Jabatan',  'list' => $lookups['level_jabatan']],
                'pangkat_id'         => ['label' => 'Pangkat',        'list' => $lookups['pangkat']],
                'golongan_ruang_id'  => ['label' => 'Golongan/Ruang', 'list' => $lookups['golongan_ruang']],
                'tipe_jabatan_id'    => ['label' => 'Tipe Jabatan',   'list' => $lookups['tipe_jabatan']],
                'tampil_jabatan_id'  => ['label' => 'Tampil Jabatan', 'list' => $lookups['tampil_jabatan']],
                'unit_kerja_id'      => ['label' => 'Unit Kerja',     'list' => $lookups['unit_kerja']],
                'satuan_kerja_id'    => ['label' => 'Satuan Kerja',   'list' => $lookups['satuan_kerja']],
                'satuan_kerja_2_id'  => ['label' => 'Satuan Kerja 2', 'list' => $lookups['satuan_kerja_2']],
                'provinsi_id'        => ['label' => 'Provinsi',       'list' => $lookups['provinsi']],
                'kabupaten_kota_id'  => ['label' => 'Kabupaten/Kota', 'list' => $lookups['kabupaten_kota']],
            ];
        ?>

        <?php foreach ($lookupFields as $fieldName => $info): ?>
            <label for="<?= $fieldName ?>"><?= $info['label'] ?></label><br>
            <select id="<?= $fieldName ?>" name="<?= $fieldName ?>">
                <option value="">-- Pilih --</option>
                <?php if (empty($info['list'])): ?>
                    <option value="" disabled>(Data belum tersedia — menunggu referensi)</option>
                <?php else: ?>
                    <?php foreach ($info['list'] as $item): ?>
                        <option value="<?= $item['id'] ?>"
                            <?= (string) fieldValue($fieldName, $pegawai) === (string) $item['id'] ? 'selected' : '' ?>>
                            <?= esc($item['nama']) ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select><br><br>
        <?php endforeach; ?>

        <label for="tmt_cpns">TMT CPNS</label><br>
        <input type="date" id="tmt_cpns" name="tmt_cpns"
               value="<?= esc(fieldValue('tmt_cpns', $pegawai)) ?>"><br><br>

        <label for="tmt_pangkat">TMT Pangkat</label><br>
        <input type="date" id="tmt_pangkat" name="tmt_pangkat"
               value="<?= esc(fieldValue('tmt_pangkat', $pegawai)) ?>"><br><br>

        <button type="submit"><?= $isEdit ? 'Simpan Perubahan' : 'Simpan Pegawai' ?></button>
    </form>
</body>
</html>
