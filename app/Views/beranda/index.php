<?= $this->include('layouts/header') ?>

<div class="welcome-block">
    <h1>Selamat datang, <?= esc(session()->get('full_name') ?? '') ?></h1>
    <p><?= esc(session()->get('role_label') ?? '') ?></p>
</div>

<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-card__label">Pegawai Pusat</div>
        <div class="stat-card__value"><?= number_format($totalPusat, 0, ',', '.') ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-card__label">Pegawai Daerah</div>
        <div class="stat-card__value"><?= number_format($totalDaerah, 0, ',', '.') ?></div>
    </div>
</div>

<div class="quick-links">
    <a href="<?= site_url('admin/pegawai?kategori=pusat') ?>">Lihat Data Pegawai Pusat &rarr;</a>
    <a href="<?= site_url('admin/pegawai?kategori=daerah') ?>">Lihat Data Pegawai Daerah &rarr;</a>
</div>

<?= $this->include('layouts/footer') ?>
