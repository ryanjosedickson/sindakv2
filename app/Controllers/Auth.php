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

        session()->set([
            'isLoggedIn' => true,
            'user_id'    => $user['id'],
            'username'   => $user['username'],
            'full_name'  => $user['full_name'],
            'role'       => $user['role_name'],   // contoh: 'operator_sekolah'
            'role_label' => $user['role_label'],  // contoh: 'Operator Sekolah'
            'sekolah_id' => $user['sekolah_id'],  // null kalau bukan operator sekolah
            'kampus_id'  => $user['kampus_id'],   // null kalau bukan operator kampus
            'is_active'  => (bool) $user['is_active'],
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
}
