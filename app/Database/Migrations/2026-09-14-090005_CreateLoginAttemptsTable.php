<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateLoginAttemptsTable extends Migration
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
                // Disimpan apa adanya (bukan FK ke users), karena percobaan
                // login gagal bisa saja pakai username yang tidak valid/tidak ada.
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'ip_address' => [
                'type'       => 'VARCHAR',
                'constraint' => 45, // cukup untuk IPv6
            ],
            'is_success' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'attempted_at' => [
                'type' => 'DATETIME',
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['username', 'attempted_at']);
        $this->forge->addKey(['ip_address', 'attempted_at']);
        $this->forge->createTable('login_attempts');
    }

    public function down()
    {
        $this->forge->dropTable('login_attempts', true);
    }
}
