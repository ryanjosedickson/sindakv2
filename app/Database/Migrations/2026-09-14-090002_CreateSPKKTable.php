<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSekolahTable extends Migration
{
    public function up()
    {
        // Versi minimal — cukup untuk keperluan scope Operator Sekolah.
        // Kolom lain (alamat, kontak, akreditasi, dst.) ditambah nanti saat
        // masuk Tier 2 (modul SPKK/Guru/Siswa).
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'nama_sekolah' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],
            'jenjang' => [
                // SDTK / SMPTK / SMTK / SMAK
                'type' => 'ENUM',
                'constraint' => ['SDTK', 'SMPTK', 'SMTK', 'SMAK'],
            ],
            'npsn' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
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
        $this->forge->addKey('jenjang');
        $this->forge->createTable('sekolah');
    }

    public function down()
    {
        $this->forge->dropTable('sekolah', true);
    }
}
