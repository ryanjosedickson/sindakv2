<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Ganti Password — SINDAK</title>
</head>
<body>
    <h1>Ganti Password</h1>

    <?php if (session()->get('must_change_password')): ?>
        <p style="color:orange;">
            <strong>Demi keamanan akun Anda, password harus diganti sebelum melanjutkan.</strong>
        </p>
    <?php endif; ?>

    <?php if (session()->getFlashdata('warning')): ?>
        <p style="color:orange;"><?= esc(session()->getFlashdata('warning')) ?></p>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <p style="color:red;"><?= esc(session()->getFlashdata('error')) ?></p>
    <?php endif; ?>

    <?php if (session()->getFlashdata('success')): ?>
        <p style="color:green;"><?= esc(session()->getFlashdata('success')) ?></p>
    <?php endif; ?>

    <?php if (session()->getFlashdata('errors')): ?>
        <ul style="color:red;">
            <?php foreach (session()->getFlashdata('errors') as $error): ?>
                <li><?= esc($error) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <form action="<?= site_url('change-password') ?>" method="post">
        <?= csrf_field() ?>

        <label for="current_password">Password Lama</label><br>
        <input type="password" id="current_password" name="current_password" required><br><br>

        <label for="new_password">Password Baru (min. 8 karakter)</label><br>
        <input type="password" id="new_password" name="new_password" required><br><br>

        <label for="new_password_confirm">Konfirmasi Password Baru</label><br>
        <input type="password" id="new_password_confirm" name="new_password_confirm" required><br><br>

        <button type="submit">Simpan Password Baru</button>

        <?php if (! session()->get('must_change_password')): ?>
            <a href="<?= site_url('dashboard') ?>">Batal</a>
        <?php endif; ?>
    </form>
</body>
</html>
