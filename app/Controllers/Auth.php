<?php

namespace App\Controllers;

use App\Models\LoginAttemptModel;
use App\Models\UserModel;

class Auth extends BaseController
{
    protected UserModel $userModel;
    protected LoginAttemptModel $loginAttemptModel;

    public function __construct()
    {
        $this->userModel         = new UserModel();
        $this->loginAttemptModel = new LoginAttemptModel();
    }

    /**
     * Tampilkan form login.
     */
    public function index()
    {
        // Kalau sudah login, tidak perlu lihat form login lagi.
        if (session()->get('isLoggedIn')) {
            return redirect()->to('/dashboard');
        }

        return view('auth/login');
    }

    /**
     * Proses submit form login.
     */
    public function attemptLogin()
    {
        $rules = [
            'username' => 'required|min_length[3]|max_length[100]',
            'password' => 'required|min_length[6]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $username  = $this->request->getPost('username');
        $password  = $this->request->getPost('password');
        $ipAddress = $this->request->getIPAddress();

        // 1. Cek lockout DULU, sebelum sentuh database user sama sekali.
        if ($this->loginAttemptModel->isLockedOut($username, $ipAddress)) {
            return redirect()->back()->withInput()->with(
                'error',
                'Terlalu banyak percobaan login gagal. Silakan coba lagi dalam 15 menit.'
            );
        }

        // 2. Cari user + role-nya.
        $user = $this->userModel->findByUsernameWithRole($username);

        // 3. Verifikasi. Pesan error SENGAJA digeneralisasi ("username atau
        //    password salah") baik saat user tidak ditemukan maupun saat
        //    password salah — supaya penyerang tidak bisa menebak-nebak
        //    username mana saja yang valid di sistem (user enumeration).
        if (! $user || ! $this->userModel->verifyPassword($password, $user['password_hash'])) {
            $this->loginAttemptModel->record($username, $ipAddress, false);

            return redirect()->back()->withInput()->with('error', 'Username atau password salah.');
        }

        // 4. Login berhasil.
        $this->loginAttemptModel->record($username, $ipAddress, true);

        // Regenerasi session ID saat login — mitigasi session fixation.
        // Parameter true = hapus data session lama sepenuhnya.
        session()->regenerate(true);

        // Regenerasi CSRF token secara eksplisit tepat setelah login.
        // Ini salah satu mitigasi resmi dari CodeIgniter untuk celah
        // CSRF-bypass-via-subdomain (GHSA-5hm8-vh6r-2cjq) — dilakukan
        // manual di sini karena $regenerate di Security.php sengaja
        // di-set false (Opsi B, demi kompatibilitas AJAX berurutan).
        \Config\Services::security()->generateHash();

        session()->set([
            'isLoggedIn'            => true,
            'user_id'               => $user['id'],
            'username'              => $user['username'],
            'full_name'             => $user['full_name'],
            'role'                  => $user['role_name'],   // contoh: 'operator_sekolah'
            'role_label'            => $user['role_label'],  // contoh: 'Operator Sekolah'
            'sekolah_id'            => $user['sekolah_id'],  // null kalau bukan operator sekolah
            'kampus_id'             => $user['kampus_id'],   // null kalau bukan operator kampus
            'is_active'             => (bool) $user['is_active'],
            'must_change_password'  => (bool) $user['must_change_password'],
        ]);

        $this->userModel->update($user['id'], ['last_login_at' => date('Y-m-d H:i:s')]);

        return redirect()->to('/dashboard')->with('success', 'Login berhasil.');
    }

    /**
     * Logout — hancurkan session sepenuhnya.
     */
    public function logout()
    {
        session()->destroy();

        return redirect()->to('/login')->with('success', 'Anda telah logout.');
    }

    /**
     * Tampilkan form ganti password.
     * Diakses baik karena dipaksa (must_change_password aktif) MAUPUN
     * kalau user mau ganti password sendiri secara sukarela nanti.
     */
    public function changePassword()
    {
        return view('auth/change_password');
    }

    /**
     * Proses submit form ganti password.
     */
    public function updatePassword()
    {
        $rules = [
            'current_password'     => 'required',
            'new_password'         => 'required|min_length[8]|differs[current_password]',
            'new_password_confirm' => 'required|matches[new_password]',
        ];

        $messages = [
            'new_password' => [
                'differs' => 'Password baru tidak boleh sama dengan password lama.',
            ],
            'new_password_confirm' => [
                'matches' => 'Konfirmasi password tidak cocok dengan password baru.',
            ],
        ];

        if (! $this->validate($rules, $messages)) {
            return redirect()->back()->with('errors', $this->validator->getErrors());
        }

        $userId  = session()->get('user_id');
        $user    = $this->userModel->find($userId);

        if (! $user || ! $this->userModel->verifyPassword($this->request->getPost('current_password'), $user['password_hash'])) {
            return redirect()->back()->with('error', 'Password lama yang Anda masukkan salah.');
        }

        $newHash = password_hash($this->request->getPost('new_password'), PASSWORD_DEFAULT);

        $this->userModel->update($userId, [
            'password_hash'         => $newHash,
            'must_change_password'  => 0,
        ]);

        session()->set('must_change_password', false);

        return redirect()->to('/dashboard')->with('success', 'Password berhasil diganti.');
    }
}
