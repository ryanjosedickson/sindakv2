<?php
use App\Models\PegawaiModel;

$isPusat = $kategori === 'pusat';
$KOSONG  = PegawaiModel::FILTER_KOSONG;

/** Render satu <option>, otomatis "selected" kalau cocok dengan filter aktif. */
$opt = static function (string $value, string $label, string $current): string {
    $sel = ($value === $current) ? ' selected' : '';
    return '<option value="' . esc($value) . '"' . $sel . '>' . esc($label) . '</option>';
};

/** <select> filter untuk daftar nilai tetap (ENUM). $items: [nilai => label] atau [nilai, nilai, ...] */
$selectEnum = static function (string $name, array $items) use ($filters, $opt, $KOSONG): string {
    $cur = $filters[$name] ?? '';
    $html = '<select name="' . $name . '" id="f_' . $name . '" class="js-filter" form="filterForm">';
    $html .= $opt('', '— Semua —', $cur) . $opt($KOSONG, '(Kosong)', $cur);
    foreach ($items as $k => $v) {
        $value = is_int($k) ? (string) $v : (string) $k;
        $html .= $opt($value, (string) $v, $cur);
    }
    return $html . '</select>';
};

/** <select> filter untuk lookup table: $rows = [['id'=>..,'nama'=>..], ...] */
$selectLookup = static function (string $name, array $rows) use ($filters, $opt, $KOSONG): string {
    $cur = $filters[$name] ?? '';
    $html = '<select name="' . $name . '" id="f_' . $name . '" class="js-filter" form="filterForm">';
    $html .= $opt('', '— Semua —', $cur) . $opt($KOSONG, '(Kosong)', $cur);
    foreach ($rows as $r) {
        $html .= $opt((string) $r['id'], $r['nama'], $cur);
    }
    return $html . '</select>';
};

/** <select> filter bertingkat: isi opsinya diisi JavaScript sesuai pilihan induknya. */
$selectCascade = static function (string $name) use ($filters, $opt, $KOSONG): string {
    $cur = $filters[$name] ?? '';
    return '<select name="' . $name . '" id="f_' . $name . '" class="js-filter" form="filterForm" data-selected="' . esc($cur) . '">'
        . $opt('', '— Semua —', $cur) . $opt($KOSONG, '(Kosong)', $cur)
        . '</select>';
};

/** <select> filter tahun untuk kolom tanggal. */
$selectYear = static function (string $name, array $years) use ($filters, $opt, $KOSONG): string {
    $cur = $filters[$name] ?? '';
    $html = '<select name="' . $name . '" id="f_' . $name . '" class="js-filter" form="filterForm">';
    $html .= $opt('', '— Semua —', $cur) . $opt($KOSONG, '(Kosong)', $cur);
    foreach ($years as $y) {
        $html .= $opt((string) $y, (string) $y, $cur);
    }
    return $html . '</select>';
};

$fmtDate = static fn (?string $d): string => $d ? date('d-m-Y', strtotime($d)) : '-';
$pageUrl = static fn (int $p): string => site_url('admin/pegawai') . '?' . http_build_query(array_merge($baseQuery, ['page' => $p]));

$adaFilter = ! empty($filters);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Pegawai — SINDAK</title>
    <style>
        .tabel-wrap { overflow-x: auto; }
        th { text-align: center; vertical-align: middle; }
        .baris-filter th { background: #f4f4f4; padding: 4px; }
        .baris-filter select { width: 100%; min-width: 110px; max-width: 200px; }
        .atas-tabel { display: flex; justify-content: space-between; align-items: center; margin: 12px 0; }
        .pager a, .pager span { display: inline-block; padding: 3px 8px; border: 1px solid #ccc; margin-right: 2px; text-decoration: none; }
        .pager .aktif { font-weight: bold; background: #ddd; }
    </style>
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

    <!-- Tab kategori: pindah tab = mulai dari nol (filter tidak dibawa) -->
    <p>
        <a href="<?= site_url('admin/pegawai?kategori=pusat') ?>"
           style="<?= $isPusat ? 'font-weight:bold;' : '' ?>">Pegawai Pusat</a>
        &nbsp;|&nbsp;
        <a href="<?= site_url('admin/pegawai?kategori=daerah') ?>"
           style="<?= ! $isPusat ? 'font-weight:bold;' : '' ?>">Pegawai Daerah</a>
    </p>

    <p><a href="<?= site_url('admin/pegawai/tambah') ?>">+ Tambah Pegawai</a></p>

    <!-- Form filter: semua <select> di bawah terhubung ke sini lewat atribut form="filterForm" -->
    <form id="filterForm" action="<?= site_url('admin/pegawai') ?>" method="get">
        <input type="hidden" name="kategori" value="<?= esc($kategori) ?>">
    </form>

    <div class="atas-tabel">
        <div>
            Menampilkan <strong><?= $firstItem ?>–<?= $lastItem ?></strong> dari <strong><?= $total ?></strong> data
            <?php if ($adaFilter): ?>
                (terfilter) &nbsp;
                <a href="<?= site_url('admin/pegawai?kategori=' . $kategori . '&per_page=' . $perPage) ?>">Reset filter</a>
            <?php endif; ?>
        </div>
        <div>
            Menampilkan :
            <select name="per_page" class="js-filter" form="filterForm">
                <?php foreach ($perPageOptions as $n): ?>
                    <option value="<?= $n ?>" <?= $n === $perPage ? 'selected' : '' ?>><?= $n ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="tabel-wrap">
    <table border="1" cellpadding="6" cellspacing="0">
        <thead>
            <tr>
                <th>No</th>
                <th>NIP</th>
                <th>Nama Lengkap</th>
                <th>Agama</th>
                <th>Jenis Kelamin</th>
                <th>Jenjang Pendidikan</th>
                <th>Level Jabatan</th>
                <th>Pangkat</th>
                <th>Gol/Ruang</th>
                <th>TMT CPNS</th>
                <th>TMT Pangkat</th>
                <th>Tipe Jabatan</th>
                <th>Tampil Jabatan</th>
                <th>Unit Kerja</th>
                <th>Satuan Kerja</th>
                <th>Satuan Kerja 2</th>
                <th>Provinsi</th>
                <th>Kab/Kota</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
            <!-- Baris filter: satu dropdown per kolom (kecuali No, NIP, Nama, Aksi,
                 dan Unit Kerja pegawai pusat karena nilainya selalu sama) -->
            <tr class="baris-filter">
                <th></th>
                <th></th>
                <th></th>
                <th><?= $selectEnum('agama', PegawaiModel::AGAMA) ?></th>
                <th><?= $selectEnum('jenis_kelamin', PegawaiModel::JENIS_KELAMIN) ?></th>
                <th><?= $selectEnum('jenjang_pendidikan', PegawaiModel::JENJANG_PENDIDIKAN) ?></th>
                <th><?= $selectLookup('level_jabatan_id', $dropdowns['level_jabatan']) ?></th>
                <th><?= $selectLookup('pangkat_id', $dropdowns['pangkat']) ?></th>
                <th><?= $selectLookup('golongan_ruang_id', $dropdowns['golongan_ruang']) ?></th>
                <th><?= $selectYear('tmt_cpns', $dropdowns['tahun_tmt_cpns']) ?></th>
                <th><?= $selectYear('tmt_pangkat', $dropdowns['tahun_tmt_pangkat']) ?></th>
                <th><?= $selectEnum('tipe_jabatan', PegawaiModel::TIPE_JABATAN) ?></th>
                <th><?= $selectLookup('tampil_jabatan_id', $dropdowns['tampil_jabatan']) ?></th>
                <th><?= $isPusat ? '' : $selectLookup('unit_kerja_id', $dropdowns['unit_kerja']) ?></th>
                <th><?= $selectCascade('satuan_kerja_id') ?></th>
                <th><?= $selectCascade('satuan_kerja_2_id') ?></th>
                <th><?= $selectLookup('provinsi_id', $dropdowns['provinsi']) ?></th>
                <th><?= $selectCascade('kabupaten_kota_id') ?></th>
                <th><?= $selectEnum('status_pegawai', PegawaiModel::STATUS_PEGAWAI) ?></th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($daftarPegawai)): ?>
                <tr>
                    <td colspan="20">Tidak ada data yang cocok<?= $adaFilter ? ' dengan filter yang dipilih' : '' ?>.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($daftarPegawai as $i => $p): ?>
                    <tr>
                        <td><?= $firstItem + $i ?></td>
                        <td><?= esc($p['nip'] ?? '-') ?></td>
                        <td><?= esc($p['nama_lengkap']) ?></td>
                        <td><?= esc($p['agama'] ?? '-') ?></td>
                        <td><?= esc(PegawaiModel::JENIS_KELAMIN[$p['jenis_kelamin'] ?? ''] ?? '-') ?></td>
                        <td><?= esc($p['jenjang_pendidikan'] ?? '-') ?></td>
                        <td><?= esc($p['level_jabatan_nama'] ?? '-') ?></td>
                        <td><?= esc($p['pangkat_nama'] ?? '-') ?></td>
                        <td><?= esc($p['golongan_ruang_nama'] ?? '-') ?></td>
                        <td><?= esc($fmtDate($p['tmt_cpns'] ?? null)) ?></td>
                        <td><?= esc($fmtDate($p['tmt_pangkat'] ?? null)) ?></td>
                        <td><?= esc($p['tipe_jabatan'] ?? '-') ?></td>
                        <td><?= esc($p['tampil_jabatan_nama'] ?? '-') ?></td>
                        <td><?= esc($p['unit_kerja_nama'] ?? '-') ?></td>
                        <td><?= esc($p['satuan_kerja_nama'] ?? '-') ?></td>
                        <td><?= esc($p['satuan_kerja_2_nama'] ?? '-') ?></td>
                        <td><?= esc($p['provinsi_nama'] ?? '-') ?></td>
                        <td><?= esc($p['kabupaten_kota_nama'] ?? '-') ?></td>
                        <td><?= esc($p['status_pegawai'] ?? '-') ?></td>
                        <td>
                            <a href="<?= site_url('admin/pegawai/' . $p['id'] . '/edit') ?>">Edit</a>
                            &nbsp;
                            <form action="<?= site_url('admin/pegawai/' . $p['id'] . '/hapus') ?>"
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
    </div>

    <?php if ($pageCount > 1): ?>
        <div class="pager" style="margin-top:12px;">
            <?php if ($currentPage > 1): ?>
                <a href="<?= $pageUrl(1) ?>">&laquo; Pertama</a>
                <a href="<?= $pageUrl($currentPage - 1) ?>">&lsaquo; Sebelumnya</a>
            <?php endif; ?>

            <?php for ($n = max(1, $currentPage - 2); $n <= min($pageCount, $currentPage + 2); $n++): ?>
                <?php if ($n === $currentPage): ?>
                    <span class="aktif"><?= $n ?></span>
                <?php else: ?>
                    <a href="<?= $pageUrl($n) ?>"><?= $n ?></a>
                <?php endif; ?>
            <?php endfor; ?>

            <?php if ($currentPage < $pageCount): ?>
                <a href="<?= $pageUrl($currentPage + 1) ?>">Berikutnya &rsaquo;</a>
                <a href="<?= $pageUrl($pageCount) ?>">Terakhir &raquo;</a>
            <?php endif; ?>
            &nbsp; Halaman <?= $currentPage ?> dari <?= $pageCount ?>
        </div>
    <?php endif; ?>

    <script>
        const cascade = <?= json_encode($cascadeData, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
        const isPusat = <?= $isPusat ? 'true' : 'false' ?>;
        const form = document.getElementById('filterForm');

        const nilai = (id) => { const el = document.getElementById(id); return el ? el.value : ''; };
        const angka = (v) => (/^\d+$/.test(v) ? v : null);

        // Isi opsi dropdown bertingkat (anak) sesuai pilihan induknya.
        function isiDropdown(id, daftar) {
            const sel = document.getElementById(id);
            if (!sel) return;
            const terpilih = sel.dataset.selected || '';
            daftar.forEach((item) => {
                const o = document.createElement('option');
                o.value = item.id;
                o.textContent = item.nama;
                sel.appendChild(o);
            });
            sel.value = terpilih;
            if (sel.selectedIndex === -1) sel.value = '';
        }

        // Satuan Kerja: pegawai pusat -> otomatis di bawah Ditjen; daerah -> ikut Unit Kerja terpilih.
        const unitInduk = isPusat ? String(cascade.pusatUnitId) : angka(nilai('f_unit_kerja_id'));
        isiDropdown('f_satuan_kerja_id',
            unitInduk === null ? [] : cascade.satuanKerja.filter((x) => String(x.parent_id) === unitInduk));

        // Satuan Kerja 2: ikut Satuan Kerja terpilih.
        const skInduk = angka(nilai('f_satuan_kerja_id'));
        isiDropdown('f_satuan_kerja_2_id',
            skInduk === null ? [] : cascade.satuanKerja2.filter((x) => String(x.parent_id) === skInduk));

        // Kab/Kota: ikut Provinsi terpilih.
        const provInduk = angka(nilai('f_provinsi_id'));
        isiDropdown('f_kabupaten_kota_id',
            provInduk === null ? [] : cascade.kabKota.filter((x) => String(x.parent_id) === provInduk));

        // Kalau induk berubah, pilihan anaknya dikosongkan (sudah tidak relevan).
        const anak = {
            f_unit_kerja_id: ['f_satuan_kerja_id', 'f_satuan_kerja_2_id'],
            f_satuan_kerja_id: ['f_satuan_kerja_2_id'],
            f_provinsi_id: ['f_kabupaten_kota_id'],
        };

        // Setiap dropdown berubah -> langsung terapkan filter (kembali ke halaman 1).
        document.querySelectorAll('.js-filter').forEach((sel) => {
            sel.addEventListener('change', () => {
                (anak[sel.id] || []).forEach((cid) => {
                    const c = document.getElementById(cid);
                    if (c) c.value = '';
                });
                form.submit();
            });
        });
    </script>
</body>
</html>
