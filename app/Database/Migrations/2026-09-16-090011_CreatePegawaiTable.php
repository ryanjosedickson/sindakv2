<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePegawaiTable extends Migration
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
            'nip' => [
                // 18 digit, unik di seluruh tabel (gabungan pusat & daerah)
                'type'       => 'VARCHAR',
                'constraint' => 18,
            ],
            'nama_lengkap' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],
            'kategori' => [
                // Pengganti tabel terpisah pegawai_pusat/pegawai_daerah lama
                'type'       => 'ENUM',
                'constraint' => ['pusat', 'daerah'],
            ],
            'agama' => [
                'type'       => 'ENUM',
                'constraint' => ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu'],
                'null'       => true,
            ],
            'jenis_kelamin' => [
                'type'       => 'ENUM',
                'constraint' => ['L', 'P'],
                'null'       => true,
            ],
            'jenjang_pendidikan' => [
                'type'       => 'ENUM',
                'constraint' => ['SD', 'SMP', 'SMA', 'D3', 'S1', 'S2', 'S3'],
                'null'       => true,
            ],
            'status_pegawai' => [
                'type'       => 'ENUM',
                'constraint' => ['PNS', 'PPPK', 'CPNS'],
                'null'       => true,
            ],

            // ── Kolom lookup (FK) — semua nullable dulu, menunggu data referensi ──
            'level_jabatan_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'pangkat_id'       => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'golongan_ruang_id'=> ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'tipe_jabatan_id'  => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'tampil_jabatan_id'=> ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'unit_kerja_id'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'satuan_kerja_id'  => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'satuan_kerja_2_id'=> ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'provinsi_id'      => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'kabupaten_kota_id'=> ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],

            // ── Tanggal ──
            'tmt_cpns'   => ['type' => 'DATE', 'null' => true],
            'tmt_pangkat'=> ['type' => 'DATE', 'null' => true],

            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true], // soft delete — data pegawai jangan dihapus permanen
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('nip');
        $this->forge->addKey('kategori');

        // Foreign key ke semua tabel lookup. Semua pakai 'SET NULL' saat parent
        // dihapus — supaya data pegawai tidak ikut hilang/error hanya karena
        // satu lookup value-nya dihapus/diganti.
        $this->forge->addForeignKey('level_jabatan_id', 'level_jabatan', 'id', 'CASCADE', 'SET NULL');
        $this->forge->addForeignKey('pangkat_id', 'pangkat', 'id', 'CASCADE', 'SET NULL');
        $this->forge->addForeignKey('golongan_ruang_id', 'golongan_ruang', 'id', 'CASCADE', 'SET NULL');
        $this->forge->addForeignKey('tipe_jabatan_id', 'tipe_jabatan', 'id', 'CASCADE', 'SET NULL');
        $this->forge->addForeignKey('tampil_jabatan_id', 'tampil_jabatan', 'id', 'CASCADE', 'SET NULL');
        $this->forge->addForeignKey('unit_kerja_id', 'unit_kerja', 'id', 'CASCADE', 'SET NULL');
        $this->forge->addForeignKey('satuan_kerja_id', 'satuan_kerja', 'id', 'CASCADE', 'SET NULL');
        $this->forge->addForeignKey('satuan_kerja_2_id', 'satuan_kerja_2', 'id', 'CASCADE', 'SET NULL');
        $this->forge->addForeignKey('provinsi_id', 'provinsi', 'id', 'CASCADE', 'SET NULL');
        $this->forge->addForeignKey('kabupaten_kota_id', 'kabupaten_kota', 'id', 'CASCADE', 'SET NULL');

        $this->forge->createTable('pegawai');
    }

    public function down()
    {
        $this->forge->dropTable('pegawai', true);
    }
}
