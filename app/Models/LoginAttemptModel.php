<?php

namespace App\Models;

use CodeIgniter\Model;

class LoginAttemptModel extends Model
{
    protected $table         = 'login_attempts';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false; // pakai kolom attempted_at manual, bukan created_at/updated_at

    protected $allowedFields = [
        'username',
        'ip_address',
        'is_success',
        'attempted_at',
    ];

    /** Batas percobaan gagal sebelum dikunci sementara. */
    private const MAX_ATTEMPTS = 5;

    /** Jendela waktu (menit) untuk menghitung percobaan gagal. */
    private const WINDOW_MINUTES = 5;

    public function record(string $username, string $ipAddress, bool $isSuccess): void
    {
        $this->insert([
            'username'     => $username,
            'ip_address'   => $ipAddress,
            'is_success'   => $isSuccess ? 1 : 0,
            'attempted_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Cek apakah username ATAU IP ini sedang kena lockout sementara,
     * berdasarkan jumlah percobaan gagal dalam WINDOW_MINUTES terakhir.
     *
     * Dicek dari dua sisi (username & IP) supaya penyerang tidak bisa
     * menghindari lockout hanya dengan ganti-ganti username dari IP yang
     * sama, atau sebaliknya mencoba banyak username dari IP yang sama.
     */
    public function isLockedOut(string $username, string $ipAddress): bool
    {
        $since = date('Y-m-d H:i:s', strtotime('-' . self::WINDOW_MINUTES . ' minutes'));

        $failedByUsername = $this->where('username', $username)
            ->where('is_success', 0)
            ->where('attempted_at >=', $since)
            ->countAllResults();

        $failedByIp = $this->where('ip_address', $ipAddress)
            ->where('is_success', 0)
            ->where('attempted_at >=', $since)
            ->countAllResults();

        return $failedByUsername >= self::MAX_ATTEMPTS || $failedByIp >= self::MAX_ATTEMPTS;
    }
}
