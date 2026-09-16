<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true; // pakai kolom deleted_at

    protected $allowedFields = [
        'username',
        'email',
        'password_hash',
        'must_change_password',
        'full_name',
        'role_id',
        'sekolah_id',
        'kampus_id',
        'is_active',
        'last_login_at',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    /**
     * Cari user berdasarkan username, sekalian join nama role-nya.
     * Dipakai saat proses login.
     */
    public function findByUsernameWithRole(string $username): ?array
    {
        $user = $this->select('users.*, roles.name as role_name, roles.label as role_label')
            ->join('roles', 'roles.id = users.role_id')
            ->where('users.username', $username)
            ->where('users.is_active', 1)
            ->first();

        return $user ?: null;
    }

    /**
     * Verifikasi password mentah terhadap hash yang tersimpan.
     * Dipisah jadi method sendiri supaya logic verifikasi terpusat
     * di satu tempat, bukan tersebar di tiap controller.
     */
    public function verifyPassword(string $rawPassword, string $storedHash): bool
    {
        return password_verify($rawPassword, $storedHash);
    }
}
