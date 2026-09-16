<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Pegawai — SINDAK</title>
</head>
<body>
    <h1>Data Pegawai</h1>
    <p><a href="<?= site_url('dashboard') ?>">&larr; Kembali ke Dashboard</a></p>

    <?php if (session()->getFlashdata('success')): ?>
        <p style="color:green;"><?= esc(session()->getFlashdata('success')) ?></p>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <p style="color:red;"><?= esc(session()->getFlashdata('error')) ?></p>
    <?php endif; ?>

    <!-- Tab filter kategori -->
    <p>
        <a href="<?= site_url('admin/pegawai?kategori=pusat') ?>"
           style="<?= $kategori === 'pusat' ? 'font-weight:bold;' : '' ?>">Pegawai Pusat</a>
        &nbsp;|&nbsp;
        <a href="<?= site_url('admin/pegawai?kategori=daerah') ?>"
           style="<?= $kategori === 'daerah' ? 'font-weight:bold;' : '' ?>">Pegawai Daerah</a>
    </p>

    <p><a href="<?= site_url('admin/pegawai/tambah') ?>">+ Tambah Pegawai</a></p>

    <table border="1" cellpadding="6" cellspacing="0">
        <thead>
            <tr>
                <th>NIP</th>
                <th>Nama Lengkap</th>
                <th>Status</th>
                <th>Level Jabatan</th>
                <th>Pangkat</th>
                <th>Gol/Ruang</th>
                <th>Unit Kerja</th>
                <th>Provinsi</th>
                <th>Kab/Kota</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($daftarPegawai)): ?>
                <tr>
                    <td colspan="10">Belum ada data pegawai <?= esc($kategori) ?>.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($daftarPegawai as $pegawai): ?>
                    <tr>
                        <td><?= esc($pegawai['nip']) ?></td>
                        <td><?= esc($pegawai['nama_lengkap']) ?></td>
                        <td><?= esc($pegawai['status_pegawai'] ?? '-') ?></td>
                        <td><?= esc($pegawai['level_jabatan_nama'] ?? '-') ?></td>
                        <td><?= esc($pegawai['pangkat_nama'] ?? '-') ?></td>
                        <td><?= esc($pegawai['golongan_ruang_nama'] ?? '-') ?></td>
                        <td><?= esc($pegawai['unit_kerja_nama'] ?? '-') ?></td>
                        <td><?= esc($pegawai['provinsi_nama'] ?? '-') ?></td>
                        <td><?= esc($pegawai['kabupaten_kota_nama'] ?? '-') ?></td>
                        <td>
                            <a href="<?= site_url('admin/pegawai/' . $pegawai['id'] . '/edit') ?>">Edit</a>
                            &nbsp;
                            <form action="<?= site_url('admin/pegawai/' . $pegawai['id'] . '/hapus') ?>"
                                  method="post" style="display:inline;"
                                  onsubmit="return confirm('Yakin hapus data pegawai ini?');">
                                <?= csrf_field() ?>
                                <button type="submit">Hapus</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
