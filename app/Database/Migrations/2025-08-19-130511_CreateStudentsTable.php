<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateStudentsTable extends Migration
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
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
                'comment'    => 'References users table',
            ],
            'student_id_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
                'comment'    => 'Unique student ID number (e.g., 2025-00001)',
            ],
            'department_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'Student department/program',
            ],
            'year_level_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'Current year level (1st, 2nd, 3rd, 4th)',
            ],
            'section' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'comment'    => 'Student section (e.g., Section A, Section B)',
            ],
            'enrollment_date' => [
                'type'    => 'DATE',
                'null'    => true,
                'comment' => 'Date when student enrolled',
            ],
            'enrollment_status' => [
                'type'       => 'ENUM',
                'constraint' => ['enrolled', 'graduated', 'dropped', 'on_leave', 'suspended'],
                'default'    => 'enrolled',
                'comment'    => 'Current enrollment status',
            ],
            'guardian_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'comment'    => 'Parent/Guardian full name',
            ],
            'guardian_contact' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
                'comment'    => 'Parent/Guardian contact number',
            ],
            'scholarship_status' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'comment'    => 'Scholarship type if applicable',
            ],
            'total_units' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
                'default'    => 0,
                'comment'    => 'Total units completed',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        // Primary key
        $this->forge->addKey('id', true);
        
        // Unique keys
        $this->forge->addUniqueKey('user_id');
        $this->forge->addUniqueKey('student_id_number');
        
        // Foreign keys
        $this->forge->addKey('department_id');
        $this->forge->addKey('year_level_id');
        $this->forge->addKey('deleted_at');
        
        // Add foreign key constraints
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('department_id', 'departments', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('year_level_id', 'year_levels', 'id', 'SET NULL', 'CASCADE');
        
        $this->forge->createTable('students');
    }

    public function down()
    {
        $this->forge->dropTable('students');
    }
}
