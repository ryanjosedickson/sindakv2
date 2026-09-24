<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddKodeToProvinsiAndKabupatenKotaTable extends Migration
{
    public function up()
    {
        // Kode resmi Kemendagri/BPS — terpisah dari 'id' auto-increment
        // internal, supaya id tetap stabil sebagai primary key/FK meskipun
        // suatu saat kode resminya direvisi pemerintah (jarang terjadi,
        // tapi tetap lebih aman dipisah daripada pakai kode sebagai PK).
        $this->forge->addColumn('provinsi', [
            'kode' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'null'       => true,
                'after'      => 'nama',
            ],
        ]);

        $this->forge->addColumn('kabupaten_kota', [
            'kode' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'null'       => true,
                'after'      => 'nama',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('provinsi', 'kode');
        $this->forge->dropColumn('kabupaten_kota', 'kode');
    }
}
