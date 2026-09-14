<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateKampusTable extends Migration
{
    public function up()
    {
        // Versi minimal — cukup untuk keperluan scope Operator Kampus.
        // Kolom lain ditambah nanti saat masuk Tier 2 (modul PTKK/Dosen/Mahasiswa).
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'nama_kampus' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('kampus');
    }

    public function down()
    {
        $this->forge->dropTable('kampus', true);
    }
}
