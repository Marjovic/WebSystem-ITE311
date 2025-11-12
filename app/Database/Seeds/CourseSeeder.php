<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class CourseSeeder extends Seeder
{
    public function run()
    {
        // Sample course data - Updated for 2025-2026 Academic Year
        $courses = [
            [
                'title' => 'Introduction to Programming',
                'description' => 'Learn the fundamentals of programming with hands-on examples and projects.',
                'course_code' => 'CS101',
                'academic_year' => '2025-2026',
                'category' => 'Computer Science',
                'credits' => 3,
                'duration_weeks' => 22,
                'max_students' => 30,
                'start_date' => '2025-08-20',
                'end_date' => '2026-01-15',
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],            [
                'title' => 'Web Development Basics',
                'description' => 'Master HTML, CSS, and JavaScript to build modern web applications.',
                'course_code' => 'WEB101',
                'academic_year' => '2025-2026',
                'category' => 'Web Development',
                'credits' => 4,
                'duration_weeks' => 22,
                'max_students' => 25,
                'start_date' => '2025-08-25',
                'end_date' => '2026-01-20',
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'title' => 'Database Design',
                'description' => 'Learn relational database concepts, SQL, and database optimization.',
                'course_code' => 'DB201',
                'academic_year' => '2025-2026',
                'category' => 'Database',
                'credits' => 3,
                'duration_weeks' => 22,
                'max_students' => 20,
                'start_date' => '2025-09-01',
                'end_date' => '2026-01-30',
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'title' => 'Data Structures and Algorithms',
                'description' => 'Master fundamental data structures and algorithms for efficient problem solving.',
                'course_code' => 'CS201',
                'academic_year' => '2025-2026',
                'category' => 'Computer Science',
                'credits' => 4,
                'duration_weeks' => 22,
                'max_students' => 35,
                'start_date' => '2026-01-15',
                'end_date' => '2026-06-15',
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'title' => 'Software Engineering Principles',
                'description' => 'Learn software development lifecycle, design patterns, and best practices.',
                'course_code' => 'SE301',
                'academic_year' => '2025-2026',
                'category' => 'Software Engineering',
                'credits' => 3,
                'duration_weeks' => 22,
                'max_students' => 30,
                'start_date' => '2026-01-20',
                'end_date' => '2026-06-20',
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]
        ];

        // Insert courses into database
        foreach ($courses as $course) {
            $this->db->table('courses')->insert($course);
        }

        echo "Courses seeded successfully!\n";

        // Create sample enrollments with new semester structure
        $this->seedEnrollments();
    }

    /**
     * Seed sample enrollments with semester duration and end dates
     */
    private function seedEnrollments()
    {
        // Get some sample users (students) - assuming UserSeeder has run first
        $students = $this->db->table('users')
            ->where('role', 'student')
            ->limit(5)
            ->get()
            ->getResultArray();

        // Get courses
        $courses = $this->db->table('courses')
            ->limit(3)
            ->get()
            ->getResultArray();

        if (empty($students) || empty($courses)) {
            echo "No students or courses found. Skipping enrollment seeding.\n";
            return;
        }

        // Create sample enrollments
        $enrollments = [];
        
        foreach ($students as $index => $student) {
            // Enroll each student in 1-2 courses
            $courseIndex = $index % count($courses);
            $course = $courses[$courseIndex];
            
            // Determine semester based on course start date
            $startDate = strtotime($course['start_date']);
            $month = (int)date('n', $startDate);
            $semester = ($month >= 8 && $month <= 12) ? 'First Semester' : 'Second Semester';
            
            // Calculate enrollment date (a few days after course start)
            $enrollmentDate = date('Y-m-d H:i:s', strtotime($course['start_date'] . ' +3 days'));
            
            // Calculate semester end date (16 weeks from enrollment)
            $semesterEndDateTime = new \DateTime($enrollmentDate);
            $semesterEndDateTime->modify('+16 weeks');
            $semesterEndDate = $semesterEndDateTime->format('Y-m-d H:i:s');
            
            $enrollments[] = [
                'user_id' => $student['id'],
                'course_id' => $course['id'],
                'enrollment_date' => $enrollmentDate,
                'enrollment_status' => 'enrolled',
                'status_date' => $enrollmentDate,
                'semester' => $semester,
                'semester_duration_weeks' => 16,
                'semester_end_date' => $semesterEndDate,
                'academic_year' => $course['academic_year'],
                'year_level_at_enrollment' => $student['year_level'] ?? '1st Year',
                'enrollment_type' => 'regular',
                'payment_status' => ['paid', 'partial', 'unpaid'][array_rand(['paid', 'partial', 'unpaid'])],
                'enrolled_by' => null, // Self-enrolled
                'notes' => 'Sample enrollment created by seeder',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];
        }

        // Insert enrollments
        if (!empty($enrollments)) {
            foreach ($enrollments as $enrollment) {
                $this->db->table('enrollments')->insert($enrollment);
            }
            echo "Sample enrollments seeded successfully! (" . count($enrollments) . " enrollments created)\n";
        }
    }
}
