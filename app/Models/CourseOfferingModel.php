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
    protected $allowedFields    = [
        'course_id',
        'term_id',
        'section',
        'max_students',
        'current_enrollment',
        'status',
        'start_date',
        'end_date'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'course_id'     => 'required|integer',
        'term_id'       => 'required|integer',
        'section'       => 'permit_empty|string|max_length[20]',
        'max_students'  => 'required|integer',
        'status'        => 'required|in_list[draft,open,closed,cancelled,completed]',
        'start_date'    => 'permit_empty|valid_date',
        'end_date'      => 'permit_empty|valid_date'
    ];

    protected $validationMessages = [
        'course_id' => [
            'required' => 'Course is required'
        ],
        'term_id' => [
            'required' => 'Term is required'
        ]
    ];

    // Callbacks
    protected $allowCallbacks = true;

    /**
     * Get offering with complete details
     */
    public function getOfferingWithDetails($offeringId)
    {
        return $this->select('
                course_offerings.*,
                c.course_code,
                c.title as course_title,
                c.credits,
                t.term_name,
                GROUP_CONCAT(DISTINCT u.name SEPARATOR ", ") as instructors,
                GROUP_CONCAT(
                    DISTINCT CONCAT(
                        cs.day_of_week, " ", 
                        TIME_FORMAT(cs.start_time, "%h:%i %p"), "-",
                        TIME_FORMAT(cs.end_time, "%h:%i %p")
                    ) 
                    ORDER BY 
                        FIELD(cs.day_of_week, "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday")
                    SEPARATOR ", "
                ) as schedule,
                GROUP_CONCAT(DISTINCT cs.room SEPARATOR ", ") as rooms
            ')
            ->join('courses c', 'c.id = course_offerings.course_id')
            ->join('terms t', 't.id = course_offerings.term_id')
            ->join('course_instructors ci', 'ci.course_offering_id = course_offerings.id', 'left')
            ->join('users u', 'u.id = ci.instructor_id', 'left')
            ->join('course_schedules cs', 'cs.course_offering_id = course_offerings.id', 'left')
            ->groupBy('course_offerings.id')
            ->find($offeringId);
    }

    /**
     * Get all offerings for a term with details
     */
    public function getOfferingsByTerm($termId)
    {
        return $this->select('
                course_offerings.*,
                c.course_code,
                c.title as course_title,
                c.credits,
                GROUP_CONCAT(DISTINCT u.name SEPARATOR ", ") as instructors
            ')
            ->join('courses c', 'c.id = course_offerings.course_id')
            ->join('course_instructors ci', 'ci.course_offering_id = course_offerings.id', 'left')
            ->join('users u', 'u.id = ci.instructor_id', 'left')
            ->where('course_offerings.term_id', $termId)
            ->groupBy('course_offerings.id')
            ->findAll();
    }

    /**
     * Get available offerings (open and not full)
     */
    public function getAvailableOfferings($termId)
    {
        return $this->select('
                course_offerings.*,
                c.course_code,
                c.title as course_title,
                GROUP_CONCAT(DISTINCT u.name SEPARATOR ", ") as instructors
            ')
            ->join('courses c', 'c.id = course_offerings.course_id')
            ->join('course_instructors ci', 'ci.course_offering_id = course_offerings.id', 'left')
            ->join('users u', 'u.id = ci.instructor_id', 'left')
            ->where('course_offerings.term_id', $termId)
            ->where('course_offerings.status', 'open')
            ->where('course_offerings.current_enrollment <', 'course_offerings.max_students', false)
            ->groupBy('course_offerings.id')
            ->findAll();
    }

    /**
     * Get offerings taught by an instructor
     */
    public function getOfferingsByInstructor($instructorId, $termId = null)
    {
        $builder = $this->select('
                course_offerings.*,
                c.course_code,
                c.title as course_title,
                t.term_name
            ')
            ->join('courses c', 'c.id = course_offerings.course_id')
            ->join('terms t', 't.id = course_offerings.term_id')
            ->join('course_instructors ci', 'ci.course_offering_id = course_offerings.id')
            ->where('ci.instructor_id', $instructorId);
        
        if ($termId) {
            $builder->where('course_offerings.term_id', $termId);
        }
        
        return $builder->findAll();
    }

    /**
     * Get student's enrolled offerings for a term
     */
    public function getStudentOfferings($studentId, $termId)
    {
        $db = \Config\Database::connect();
        
        return $db->table('course_offerings co')
                  ->select('
                      co.*,
                      c.course_code,
                      c.title as course_title,
                      c.credits,
                      e.enrollment_status,
                      e.final_grade,
                      GROUP_CONCAT(DISTINCT u.name SEPARATOR ", ") as instructors
                  ')
                  ->join('courses c', 'c.id = co.course_id')
                  ->join('enrollments e', 'e.course_offering_id = co.id')
                  ->join('course_instructors ci', 'ci.course_offering_id = co.id', 'left')
                  ->join('users u', 'u.id = ci.instructor_id', 'left')
                  ->where('e.student_id', $studentId)
                  ->where('co.term_id', $termId)
                  ->groupBy('co.id')
                  ->get()
                  ->getResultArray();
    }

    /**
     * Check if offering is full
     */
    public function isFull($offeringId)
    {
        $offering = $this->find($offeringId);
        return $offering && $offering['current_enrollment'] >= $offering['max_students'];
    }

    /**
     * Get enrollment statistics
     */
    public function getEnrollmentStats($offeringId)
    {
        $offering = $this->find($offeringId);
        
        if (!$offering) {
            return null;
        }
        
        $available = $offering['max_students'] - $offering['current_enrollment'];
        $percentage = ($offering['current_enrollment'] / $offering['max_students']) * 100;
        
        return [
            'max_students'        => $offering['max_students'],
            'current_enrollment'  => $offering['current_enrollment'],
            'available_slots'     => $available,
            'enrollment_percentage' => round($percentage, 2),
            'is_full'             => $available <= 0
        ];
    }

    /**
     * Update offering status
     */
    public function updateStatus($offeringId, $status)
    {
        return $this->update($offeringId, ['status' => $status]);
    }

    /**
     * Close enrollment (set status to closed)
     */
    public function closeEnrollment($offeringId)
    {
        return $this->updateStatus($offeringId, 'closed');
    }

    /**
     * Open enrollment (set status to open)
     */
    public function openEnrollment($offeringId)
    {
        return $this->updateStatus($offeringId, 'open');
    }
}