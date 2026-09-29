<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class SimplifyTipeJabatanToEnum extends Migration
{
    public function up()
    {
        // 1. Tambah kolom ENUM baru
        $this->forge->addColumn('pegawai', [
            'tipe_jabatan' => [
                'type'       => 'ENUM',
                'constraint' => ['Jabatan Struktural', 'Jabatan Fungsional Umum', 'Jabatan Fungsional Tertentu'],
                'null'       => true,
                'after'      => 'status_pegawai',
            ],
        ]);

        // 2. Cari nama asli constraint FK dari information_schema — supaya
        // tidak tebak-tebak nama (default CI4 kadang beda tergantung versi).
        $db = $this->db;
        $dbName = $db->getDatabase();
        $fkName = $db->query("
            SELECT CONSTRAINT_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = ?
              AND TABLE_NAME = 'pegawai'
              AND COLUMN_NAME = 'tipe_jabatan_id'
              AND REFERENCED_TABLE_NAME = 'tipe_jabatan'
        ", [$dbName])->getRow();

        if ($fkName) {
            $this->forge->dropForeignKey('pegawai', $fkName->CONSTRAINT_NAME);
        }

        // 3. Drop kolom tipe_jabatan_id (sudah tidak dipakai)
        $this->forge->dropColumn('pegawai', 'tipe_jabatan_id');

        // 4. Drop tabel lookup tipe_jabatan — tidak dipakai modul lain mana pun
        $this->forge->dropTable('tipe_jabatan', true);
    }

    public function down()
    {
        $this->forge->addColumn('pegawai', [
            'tipe_jabatan_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'status_pegawai',
            ],
        ]);
        $this->forge->dropColumn('pegawai', 'tipe_jabatan');
        // Catatan: rollback tidak merekonstruksi ulang tabel tipe_jabatan
        // atau FK constraint-nya — kalau perlu benar-benar mundur total,
        // jalankan ulang migration lama secara manual.
    }
}
