<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMaterialsTable extends Migration
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

        // Set primary key
        $this->forge->addKey('id', true);
        
        // Add index for course_id for better query performance
        $this->forge->addKey('course_id');
        
        // Add foreign key constraint
        $this->forge->addForeignKey('course_id', 'courses', 'id', 'CASCADE', 'CASCADE');
        
        // Create the table
        $this->forge->createTable('materials');
    }

    public function down()
    {
        $this->forge->dropTable('materials');
    }
}
