<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRolesTable extends Migration
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
            'name' => [
                // Nilai baku: super_admin, operator_pusat, operator_sekolah, operator_kampus
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'label' => [
                // Nama tampilan, misal "Operator Sekolah"
                'type'       => 'VARCHAR',
                'constraint' => 100,
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
        $this->forge->addUniqueKey('name');
        $this->forge->createTable('roles');
    }

    public function down()
    {
        $this->forge->dropTable('roles', true);
    }
}
