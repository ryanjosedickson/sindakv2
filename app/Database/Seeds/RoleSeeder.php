<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run()
    {
        $roles = [
            [
                'name'       => 'super_admin',
                'label'      => 'Super Admin',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name'       => 'operator_pusat',
                'label'      => 'Operator Pusat',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name'       => 'operator_sekolah',
                'label'      => 'Operator Sekolah',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name'       => 'operator_kampus',
                'label'      => 'Operator Kampus',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        // Pakai insertBatch supaya idempotent-friendly: jalankan truncate dulu
        // kalau re-seed, daripada insert duplikat.
        $this->db->table('roles')->insertBatch($roles);
    }
}
