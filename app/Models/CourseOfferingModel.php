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
    }

    /**
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
     * Get offerings by term with enrollment count
     */
    public function getOfferingsByTerm($termId)
    {
        return $this->select('
                course_offerings.*,
                courses.course_code,
                courses.title as course_title,
                courses.credits,
                departments.department_name,
                (SELECT COUNT(*) FROM enrollments WHERE course_offering_id = course_offerings.id AND enrollment_status = "enrolled") as enrolled_count
            ')
            ->join('courses', 'courses.id = course_offerings.course_id')
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