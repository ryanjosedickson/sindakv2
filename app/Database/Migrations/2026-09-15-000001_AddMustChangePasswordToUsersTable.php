<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMustChangePasswordToUsersTable extends Migration
{
    public function up()
    {
        $this->forge->addColumn('users', [
            'must_change_password' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1, // default 1 (wajib ganti) untuk SEMUA user baru
                'after'      => 'password_hash',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('users', 'must_change_password');
    }
}
