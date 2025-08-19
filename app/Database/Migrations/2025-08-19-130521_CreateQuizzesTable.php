<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateQuizzesTable extends Migration
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
            'lesson_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'course_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'title' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'question' => [
                'type' => 'TEXT',
            ],
            'question_type' => [
                'type'       => 'ENUM',
                'constraint' => ['multiple_choice', 'true_false', 'short_answer', 'essay'],
                'default'    => 'multiple_choice',
            ],
            'options' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'correct_answer' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'points' => [
                'type'       => 'INT',
                'constraint' => 3,
                'default'    => 1,
            ],
            'time_limit_minutes' => [
                'type'       => 'INT',
                'constraint' => 4,
                'null'       => true,
            ],
            'attempts_allowed' => [
                'type'       => 'INT',
                'constraint' => 2,
                'default'    => 1,
            ],
            'is_published' => [
                'type'       => 'BOOLEAN',
                'default'    => false,
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
        $this->forge->addKey('lesson_id');
        $this->forge->addKey('course_id');
        $this->forge->addForeignKey('lesson_id', 'lessons', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('course_id', 'courses', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('quizzes');
    }

    public function down()
    {
        $this->forge->dropTable('quizzes');
    }
}