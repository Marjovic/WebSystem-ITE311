<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class CourseSeeder extends Seeder
{
    public function run()
    {        // Sample course data
        $courses = [
            [
                'title' => 'Introduction to Programming',
                'description' => 'Learn the fundamentals of programming with hands-on examples and projects.',
                'course_code' => 'CS101',
                'instructor_ids' => json_encode([2]), // JSON array with single instructor
                'category' => 'Computer Science',
                'credits' => 3,
                'duration_weeks' => 16,
                'max_students' => 30,
                'start_date' => '2024-01-15',
                'end_date' => '2024-05-15',
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'title' => 'Web Development Basics',
                'description' => 'Master HTML, CSS, and JavaScript to build modern web applications.',
                'course_code' => 'WEB101',
                'instructor_ids' => json_encode([2, 3]), // JSON array with multiple instructors
                'category' => 'Web Development',
                'credits' => 4,
                'duration_weeks' => 12,
                'max_students' => 25,
                'start_date' => '2024-02-01',
                'end_date' => '2024-04-30',
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'title' => 'Database Design',
                'description' => 'Learn relational database concepts, SQL, and database optimization.',
                'course_code' => 'DB201',
                'instructor_ids' => json_encode([2]), // JSON array with single instructor
                'category' => 'Database',
                'credits' => 3,
                'duration_weeks' => 14,
                'max_students' => 20,
                'start_date' => '2024-03-01',
                'end_date' => '2024-06-15',
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]
        ];

        // Insert courses into database
        foreach ($courses as $course) {
            $this->db->table('courses')->insert($course);
        }

    }
}
