<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateKabupatenKotaTable extends Migration
{
    public function up()
    {
        // parent_id merujuk ke tabel 'provinsi' — konvensi nama generik
        // 'parent_id' (bukan 'provinsi_id') sengaja dipakai supaya
        // LookupModel bisa dipakai ulang untuk semua tabel hierarkis
        // tanpa perlu tahu nama kolom FK yang berbeda-beda per tabel.
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'nama' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],
            'parent_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true, // nullable dulu, sampai data referensi dari senior lengkap
            ],
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('parent_id');
        $this->forge->addForeignKey('parent_id', 'provinsi', 'id', 'CASCADE', 'SET NULL');
        $this->forge->createTable('kabupaten_kota');
    }

    public function down()
    {
        $this->forge->dropTable('kabupaten_kota', true);
    }
}
