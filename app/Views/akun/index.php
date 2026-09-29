<?= $this->include('layouts/header') ?>

<div class="welcome-block">
    <h1>Akun Saya</h1>
    <p>Nama: <?= esc(session()->get('full_name') ?? '-') ?></p>
    <p>Username: <?= esc(session()->get('username') ?? '-') ?></p>
    <p>Peran: <?= esc(session()->get('role_label') ?? '-') ?></p>
</div>

<p><a href="<?= site_url('change-password') ?>">Ganti Password</a></p>

<?= $this->include('layouts/footer') ?>
