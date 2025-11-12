<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateEnrollmentsTable extends Migration
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
            ],
            'course_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'enrollment_date' => [
                'type'    => 'DATETIME',
                'null'    => false,
            ],            'enrollment_status' => [
                'type'       => 'ENUM',
                'constraint' => ['enrolled', 'dropped', 'completed', 'withdrawn'],
                'default'    => 'enrolled',
                'null'       => false,
                'comment'    => 'Current enrollment status',
            ],
            'status_date' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'comment' => 'Date when status was last changed',
            ],            'semester' => [
                'type'       => 'ENUM',
                'constraint' => ['First Semester', 'Second Semester', 'Summer'],
                'null'       => true,
                'comment'    => 'Academic semester for this enrollment (Aug-Dec = First, Jan-Jul = Second)',
            ],
            'semester_duration_weeks' => [
                'type' => 'INT',
                'constraint' => 2,
                'null' => true,
                'default' => 16,
            ],
            'semester_end_date' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'academic_year' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
                'comment'    => 'Academic year (e.g., 2024-2025)',
            ],
            'year_level_at_enrollment' => [
                'type'       => 'ENUM',
                'constraint' => ['1st Year', '2nd Year', '3rd Year', '4th Year', '5th Year', 'Graduate'],
                'null'       => true,
                'comment'    => 'Student year level when they enrolled in this course',
            ],
            'enrollment_type' => [
                'type'       => 'ENUM',
                'constraint' => ['regular', 'irregular', 'retake', 'summer', 'special'],
                'default'    => 'regular',
                'null'       => false,
                'comment'    => 'Type of enrollment',
            ],            
            'payment_status' => [
                'type'       => 'ENUM',
                'constraint' => ['paid', 'partial', 'unpaid', 'scholarship', 'waived'],
                'default'    => 'unpaid',
                'null'       => false,
                'comment'    => 'Payment status for this course',
            ],
            'enrolled_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'ID of admin/teacher who enrolled the student (if not self-enrolled)',
            ],
            'notes' => [
                'type'    => 'TEXT',
                'null'    => true,
                'comment' => 'Additional notes about this enrollment',
            ],
            'created_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
            ],
            'updated_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
            ]
        ]);        $this->forge->addKey('id', true);
        $this->forge->addKey(['user_id', 'course_id']);
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('course_id', 'courses', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('enrolled_by', 'users', 'id', 'SET NULL', 'SET NULL');
        $this->forge->createTable('enrollments');
    }

    public function down()
    {
        $this->forge->dropTable('enrollments');
    }
}