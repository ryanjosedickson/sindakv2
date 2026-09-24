<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class MakeNipNullableInPegawaiTable extends Migration
{
    public function up()
    {
        // NIP boleh kosong sementara — dataset cutoff hasil import massal
        // tidak menyertakan NIP sama sekali, akan diisi manual satu-satu
        // belakangan lewat form edit. Unique constraint tetap aman karena
        // MySQL mengizinkan banyak baris NULL pada kolom UNIQUE (NULL tidak
        // dianggap "sama" satu sama lain).
        $this->forge->modifyColumn('pegawai', [
            'nip' => [
                'name'       => 'nip',
                'type'       => 'VARCHAR',
                'constraint' => 18,
                'null'       => true,
            ],
        ]);
    }

    public function down()
    {
        $this->forge->modifyColumn('pegawai', [
            'nip' => [
                'name'       => 'nip',
                'type'       => 'VARCHAR',
                'constraint' => 18,
                'null'       => false,
            ],
        ]);
    }
}
