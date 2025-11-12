<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSemesterDurationToEnrollments extends Migration
{
    public function up()
    {
        // Add new fields to enrollments table
        $fields = [
            'semester_duration_weeks' => [
                'type' => 'INT',
                'constraint' => 2,
                'null' => true,
                'default' => 16,
                'after' => 'semester',
                'comment' => 'Duration of semester in weeks (default: 16 weeks)'
            ],
            'semester_end_date' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'semester_duration_weeks',
                'comment' => 'Calculated end date of semester (enrollment_date + 16 weeks)'
            ],
        ];

        $this->forge->addColumn('enrollments', $fields);
    }

    public function down()
    {
        // Remove the added columns
        $this->forge->dropColumn('enrollments', ['semester_duration_weeks', 'semester_end_date']);
    }
}
