<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateJenjangPendidikanEnumPegawaiTable extends Migration
{
    public function up()
    {
        $this->forge->modifyColumn('pegawai', [
            'jenjang_pendidikan' => [
                'name'       => 'jenjang_pendidikan',
                'type'       => 'ENUM',
                'constraint' => [
                    'SD',
                    'SLTP/SMP SEDERAJAT',
                    'SLTA/SMA SEDERAJAT',
                    'D II',
                    'Diploma III/Sarjana Muda',
                    'Diploma IV',
                    'S-1/Sarjana',
                    'S-2/Magister',
                    'S-3/Doktor',
                ],
                'null' => true,
            ],
        ]);
    }

    public function down()
    {
        // Rollback ke enum lama (sederhana) — kalau memang perlu mundur.
        $this->forge->modifyColumn('pegawai', [
            'jenjang_pendidikan' => [
                'name'       => 'jenjang_pendidikan',
                'type'       => 'ENUM',
                'constraint' => ['SD', 'SMP', 'SMA', 'D3', 'S1', 'S2', 'S3'],
                'null'       => true,
            ],
        ]);
    }
}
