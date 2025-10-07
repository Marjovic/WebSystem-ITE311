<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSubmissionsTable extends Migration
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
            'quiz_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'user_id' => [  // Changed from student_id to user_id for consistency
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'answer' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'is_correct' => [
                'type'       => 'BOOLEAN',
                'null'       => true,
            ],
            'points_earned' => [
                'type'       => 'INT',
                'constraint' => 3,
                'default'    => 0,
            ],
            'attempt_number' => [
                'type'       => 'INT',
                'constraint' => 2,
                'default'    => 1,
            ],
            'time_taken_minutes' => [
                'type'       => 'INT',
                'constraint' => 4,
                'null'       => true,
            ],
            'submitted_at' => [
                'type'    => 'DATETIME',
                'null'    => false,
            ],
            'graded_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
            ],
            'graded_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'feedback' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
            ],
            'updated_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey(['quiz_id', 'user_id']);  // Changed from student_id to user_id
        $this->forge->addKey('user_id');  // Changed from student_id to user_id
        $this->forge->addForeignKey('quiz_id', 'quizzes', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');  // Changed from student_id to user_id
        $this->forge->addForeignKey('graded_by', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('submissions');
    }

    public function down()
    {
        $this->forge->dropTable('submissions');
    }
}