<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDetailToGolonganRuangTable extends Migration
{
    public function up()
    {
        // Tabel golongan_ruang masih kosong (belum pernah di-seed), jadi
        // aman ditambah kolom tanpa risiko kehilangan data.
        $this->forge->addColumn('golongan_ruang', [
            'jenis_golongan' => [
                // Nama Pangkat yang berpasangan dgn Golongan/Ruang ini
                // (khusus PNS — NULL untuk PPPK karena PPPK tidak
                // memakai sistem penamaan Pangkat).
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'after'      => 'nama',
            ],
            'tipe_asn' => [
                // PNS pakai skema I/a s.d. IV/e (17 kombinasi + nama Pangkat).
                // PPPK pakai skema I s.d. XVII (17 nilai, angka romawi polos,
                // tanpa nama Pangkat).
                'type'       => 'ENUM',
                'constraint' => ['PNS', 'PPPK'],
                'null'       => true,
                'after'      => 'jenis_golongan',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('golongan_ruang', 'jenis_golongan');
        $this->forge->dropColumn('golongan_ruang', 'tipe_asn');
    }
}
