<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUsersTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'username' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
            ],
            'password_hash' => [
                // Hasil password_hash(), bukan password mentah/MD5
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'full_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],
            'role_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'sekolah_id' => [
                // Diisi HANYA jika role = operator_sekolah. Null untuk role lain.
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'kampus_id' => [
                // Diisi HANYA jika role = operator_kampus. Null untuk role lain.
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'last_login_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'deleted_at' => [
                // Soft delete — jangan hapus akun secara permanen,
                // supaya jejak audit (siapa pernah jadi operator apa) tetap ada
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('username');
        $this->forge->addKey('role_id');
        $this->forge->addKey('sekolah_id');
        $this->forge->addKey('kampus_id');
        $this->forge->addForeignKey('role_id', 'roles', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('sekolah_id', 'sekolah', 'id', 'CASCADE', 'SET NULL');
        $this->forge->addForeignKey('kampus_id', 'kampus', 'id', 'CASCADE', 'SET NULL');
        $this->forge->createTable('users');
    }

    public function down()
    {
        $this->forge->dropTable('users', true);
    }
}
