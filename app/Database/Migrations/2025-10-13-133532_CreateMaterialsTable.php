<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMaterialsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField(fields: [
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'course_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'file_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'file_path' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey(key: 'id', primary: true);
        $this->forge->addKey(key: 'course_id');
        $this->forge->addForeignKey(fieldName: 'course_id', tableName: 'courses', tableField: 'id', onUpdate: 'CASCADE', onDelete: 'CASCADE');
        $this->forge->createTable(table: 'materials');
    }

    public function down(): void
    {
        $this->forge->dropTable(tableName: 'materials');
    }
}
