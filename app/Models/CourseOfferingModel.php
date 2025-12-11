<?php

namespace App\Models;

use CodeIgniter\Model;

class CourseOfferingModel extends Model
{
    protected $table            = 'course_offerings';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    
    protected $allowedFields = [
        'course_id',
        'term_id',
        'section',
        'max_students',
        'current_enrollment',
        'room',
        'status',
        'start_date',
        'end_date'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'course_id'    => 'required|integer',
        'term_id'      => 'required|integer',
        'max_students' => 'required|integer|greater_than[0]',
        'status'       => 'required|in_list[draft,open,closed,cancelled,completed]',
    ];

    /**
     * Get offering with full details
     */
    public function getOfferingWithDetails($offeringId)
    {
        return $this->select('
                course_offerings.*,
                courses.course_code,
                courses.title as course_title,
                courses.credits,
                courses.description as course_description,
                terms.term_name,
                academic_years.year_name as academic_year,
                semesters.semester_name,
                departments.department_name
            ')
            ->join('courses', 'courses.id = course_offerings.course_id')
            ->join('terms', 'terms.id = course_offerings.term_id')
            ->join('academic_years', 'academic_years.id = terms.academic_year_id')
            ->join('semesters', 'semesters.id = terms.semester_id')
            ->join('departments', 'departments.id = courses.department_id', 'left')
            ->where('course_offerings.id', $offeringId)
            ->first();
    }    /**
     * Get available offerings for enrollment
     */
    public function getAvailableOfferings($termId)
    {
        return $this->select('
                course_offerings.*,
                courses.course_code,
                courses.title as course_title,
                courses.credits,
                courses.description,
                departments.department_name,
                (course_offerings.max_students - course_offerings.current_enrollment) as available_slots
            ')
            ->join('courses', 'courses.id = course_offerings.course_id')
            ->join('departments', 'departments.id = courses.department_id', 'left')
            ->where('course_offerings.term_id', $termId)
            ->where('course_offerings.status', 'open')
            ->having('available_slots >', 0)
            ->orderBy('courses.course_code', 'ASC')
            ->findAll();
    }

    /**
     * Get available offerings for a specific student based on their program, department, and year level
     * This filters courses to show only relevant offerings for the student
     * 
     * @param int $termId The current term ID
     * @param int $studentId The student's ID
     * @param array $enrolledOfferingIds Array of course offering IDs student is already enrolled in
     * @return array Available course offerings filtered for the student
     */
    public function getAvailableOfferingsForStudent($termId, $studentId, $enrolledOfferingIds = [])
    {
        $db = \Config\Database::connect();
        
        // Get student details first
        $student = $db->table('students s')
            ->select('s.*, p.program_name, d.department_name, yl.year_level_name, yl.year_level_order')
            ->join('programs p', 'p.id = s.program_id', 'left')
            ->join('departments d', 'd.id = s.department_id', 'left')
            ->join('year_levels yl', 'yl.id = s.year_level_id', 'left')
            ->where('s.id', $studentId)
            ->get()
            ->getRow();
        
        if (!$student) {
            return [];
        }
          // Build query for available offerings
        $builder = $db->table('course_offerings co')
            ->select('
                co.id,
                co.section,
                co.max_students,
                co.current_enrollment,
                co.room,
                co.status,
                co.start_date,
                co.end_date,
                c.course_code,
                c.title as course_title,
                c.credits,
                c.description,
                c.year_level_id,
                c.department_id,
                d.department_name,
                yl.year_level_name,
                yl.year_level_order,
                t.term_name,
                s.semester_name,
                ay.year_name as academic_year,
                (co.max_students - co.current_enrollment) as available_slots,
                cat.category_name
            ')
            ->join('courses c', 'c.id = co.course_id')
            ->join('departments d', 'd.id = c.department_id', 'left')
            ->join('year_levels yl', 'yl.id = c.year_level_id', 'left')
            ->join('categories cat', 'cat.id = c.category_id', 'left')
            ->join('terms t', 't.id = co.term_id')
            ->join('semesters s', 's.id = t.semester_id', 'left')
            ->join('academic_years ay', 'ay.id = t.academic_year_id', 'left')
            ->where('co.term_id', $termId)
            ->where('co.status', 'open')
            ->where('c.is_active', 1)
            ->having('available_slots >', 0);
        
        // Filter by student's department (show courses from student's department)
        if (!empty($student->department_id)) {
            $builder->groupStart()
                ->where('c.department_id', $student->department_id)
                ->orWhere('c.department_id IS NULL')
                ->groupEnd();
        }
        
        // Filter by year level - show courses for current year level or below
        // (Students can take lower level courses but not higher level courses)
        if (!empty($student->year_level_id)) {
            $builder->groupStart()
                ->where('yl.year_level_order <=', $student->year_level_order)
                ->orWhere('c.year_level_id IS NULL')
                ->groupEnd();
        }
        
        // Exclude courses student is already enrolled in
        if (!empty($enrolledOfferingIds)) {
            $builder->whereNotIn('co.id', $enrolledOfferingIds);
        }
        
        $offerings = $builder->orderBy('yl.year_level_order', 'ASC')
            ->orderBy('c.course_code', 'ASC')
            ->get()
            ->getResultArray();
        
        // Get instructors for each offering
        foreach ($offerings as &$offering) {
            $instructors = $db->table('course_instructors ci')
                ->select('u.first_name, u.middle_name, u.last_name, i.employee_id')
                ->join('instructors i', 'i.id = ci.instructor_id')
                ->join('users u', 'u.id = i.user_id')
                ->where('ci.course_offering_id', $offering['id'])
                ->where('ci.is_primary', 1)
                ->get()
                ->getResultArray();
            
            if (!empty($instructors)) {
                $instructorNames = array_map(function($instructor) {
                    return trim($instructor['first_name'] . ' ' . $instructor['last_name']);
                }, $instructors);
                $offering['instructor_name'] = implode(', ', $instructorNames);
                $offering['instructor_names'] = $instructorNames;
            } else {
                $offering['instructor_name'] = 'TBA';
                $offering['instructor_names'] = [];
            }
            
            // Get schedule information
            $schedules = $db->table('course_schedules')
                ->where('course_offering_id', $offering['id'])
                ->orderBy('day_of_week', 'ASC')
                ->get()
                ->getResultArray();
            
            $offering['schedules'] = $schedules;
            $offering['has_schedule'] = !empty($schedules);
            
            // Format schedule for display
            if (!empty($schedules)) {
                $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                $scheduleText = [];
                foreach ($schedules as $schedule) {
                    $day = $days[$schedule['day_of_week']] ?? 'Unknown';
                    $time = date('g:i A', strtotime($schedule['start_time'])) . ' - ' . date('g:i A', strtotime($schedule['end_time']));
                    $scheduleText[] = $day . ' ' . $time;
                }
                $offering['schedule_display'] = implode(', ', $scheduleText);
            } else {
                $offering['schedule_display'] = 'TBA';
            }
            
            // Check prerequisites
            $prerequisites = $db->table('course_prerequisites cp')
                ->select('c.course_code, c.title')
                ->join('courses c', 'c.id = cp.prerequisite_course_id')
                ->where('cp.course_id', $offering['id'])
                ->get()
                ->getResultArray();
            
            $offering['prerequisites'] = $prerequisites;
            $offering['has_prerequisites'] = !empty($prerequisites);
            
            // Format dates
            $offering['start_date_formatted'] = !empty($offering['start_date']) ? date('M j, Y', strtotime($offering['start_date'])) : 'TBA';
            $offering['end_date_formatted'] = !empty($offering['end_date']) ? date('M j, Y', strtotime($offering['end_date'])) : 'TBA';
        }
        
        return $offerings;
    }
    
    /**
     * Check if student meets prerequisites for a course offering
     * 
     * @param int $courseOfferingId Course offering ID
     * @param int $studentId Student ID
     * @return array ['meets_prerequisites' => bool, 'missing_prerequisites' => array]
     */
    public function checkPrerequisites($courseOfferingId, $studentId)
    {
        $db = \Config\Database::connect();
        
        // Get course ID from offering
        $offering = $this->find($courseOfferingId);
        if (!$offering) {
            return ['meets_prerequisites' => false, 'missing_prerequisites' => []];
        }
        
        // Get all prerequisites for this course
        $prerequisites = $db->table('course_prerequisites cp')
            ->select('cp.prerequisite_course_id, c.course_code, c.title')
            ->join('courses c', 'c.id = cp.prerequisite_course_id')
            ->where('cp.course_id', $offering['course_id'])
            ->get()
            ->getResultArray();
        
        if (empty($prerequisites)) {
            return ['meets_prerequisites' => true, 'missing_prerequisites' => []];
        }
        
        // Get student's completed courses (passed courses with grade)
        $completedCourses = $db->table('enrollments e')
            ->select('co.course_id')
            ->join('course_offerings co', 'co.id = e.course_offering_id')
            ->where('e.student_id', $studentId)
            ->where('e.enrollment_status', 'completed')
            ->whereIn('e.final_grade', ['1.0', '1.25', '1.5', '1.75', '2.0', '2.25', '2.5', '2.75', '3.0']) // Passing grades
            ->get()
            ->getResultArray();
        
        $completedCourseIds = array_column($completedCourses, 'course_id');
        
        // Check which prerequisites are missing
        $missingPrerequisites = [];
        foreach ($prerequisites as $prereq) {
            if (!in_array($prereq['prerequisite_course_id'], $completedCourseIds)) {
                $missingPrerequisites[] = [
                    'course_code' => $prereq['course_code'],
                    'title' => $prereq['title']
                ];
            }
        }
        
        return [
            'meets_prerequisites' => empty($missingPrerequisites),
            'missing_prerequisites' => $missingPrerequisites
        ];
    }/**
     * Get offerings by term with enrollment count
     */
    public function getOfferingsByTerm($termId)
    {
        return $this->select('
                course_offerings.*,
                courses.course_code,
                courses.title as course_title,
                courses.credits,
                terms.term_name,
                departments.department_name,
                (SELECT COUNT(*) FROM enrollments WHERE course_offering_id = course_offerings.id AND enrollment_status = "enrolled") as enrolled_count
            ')
            ->join('courses', 'courses.id = course_offerings.course_id')
            ->join('terms', 'terms.id = course_offerings.term_id')
            ->join('departments', 'departments.id = courses.department_id', 'left')
            ->where('course_offerings.term_id', $termId)
            ->orderBy('courses.course_code', 'ASC')
            ->findAll();
    }

    /**
     * Check if offering has available slots
     */
    public function hasAvailableSlots($offeringId)
    {
        $offering = $this->find($offeringId);
        if (!$offering) {
            return false;
        }
        return $offering['current_enrollment'] < $offering['max_students'];
    }

    /**
     * Increment enrollment count
     */
    public function incrementEnrollment($offeringId)
    {
        return $this->set('current_enrollment', 'current_enrollment + 1', false)
                    ->where('id', $offeringId)
                    ->update();
    }

    /**
     * Decrement enrollment count
     */
    public function decrementEnrollment($offeringId)
    {
        return $this->set('current_enrollment', 'current_enrollment - 1', false)
                    ->where('id', $offeringId)
                    ->where('current_enrollment >', 0)
                    ->update();
    }

    /**
     * Get instructor(s) for an offering
     */
    public function getOfferingInstructors($offeringId)
    {
        $db = \Config\Database::connect();
        return $db->table('course_instructors')
            ->select('
                course_instructors.*,
                instructors.employee_id,
                users.first_name,
                users.middle_name,
                users.last_name,
                users.email
            ')
            ->join('instructors', 'instructors.id = course_instructors.instructor_id')
            ->join('users', 'users.id = instructors.user_id')
            ->where('course_instructors.course_offering_id', $offeringId)
            ->get()
            ->getResultArray();
    }

    /**
     * Get schedules for an offering
     */
    public function getOfferingSchedules($offeringId)
    {
        $db = \Config\Database::connect();
        return $db->table('course_schedules')
            ->where('course_offering_id', $offeringId)
            ->orderBy('day_of_week', 'ASC')
            ->get()
            ->getResultArray();
    }
}