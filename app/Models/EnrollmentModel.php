<?php

namespace App\Models;

use CodeIgniter\Model;

class EnrollmentModel extends Model
{    
    protected $table            = 'enrollments';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    
    protected $allowedFields    = [
        'student_id',
        'course_offering_id',
        'enrollment_date',
        'enrollment_status',
        'enrollment_type',
        'year_level_id',
        'payment_status',
        'enrolled_by',
        'status_changed_at',
        'notes'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'student_id'          => 'required|integer',
        'course_offering_id'  => 'required|integer',
        'enrollment_date'     => 'required|valid_date',
        'enrollment_status'   => 'permit_empty|in_list[pending,enrolled,dropped,withdrawn,completed]',
        'enrollment_type'     => 'permit_empty|in_list[regular,irregular,retake,cross_enroll,special]',
        'year_level_id'       => 'permit_empty|integer',
        'payment_status'      => 'permit_empty|in_list[unpaid,partial,paid,scholarship,waived]',
        'enrolled_by'         => 'permit_empty|integer',
        'status_changed_at'   => 'permit_empty|valid_date',
        'notes'               => 'permit_empty|string'
    ];

    protected $validationMessages = [
        'student_id' => [
            'required' => 'Student ID is required',
            'integer'  => 'Student ID must be a valid number'
        ],
        'course_offering_id' => [
            'required' => 'Course offering is required',
            'integer'  => 'Course offering ID must be a valid number'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = ['updateEnrollmentCount'];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = ['updateEnrollmentCount'];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = ['updateEnrollmentCount'];

    /**
     * Enroll a student in a course offering
     * 
     * @param array $data - Array containing student_id, course_offering_id, etc.
     * @return bool|int - Returns insertion ID on success, false on failure
     */
    public function enrollStudent($data)
    {
        // Validate required fields
        if (!isset($data['student_id']) || !isset($data['course_offering_id'])) {
            log_message('error', 'Missing required fields: student_id or course_offering_id');
            return false;
        }

        // Set enrollment_date to current datetime if not provided
        if (!isset($data['enrollment_date'])) {
            $data['enrollment_date'] = date('Y-m-d H:i:s');
        }

        // Check if student is already enrolled to prevent duplicates
        $existingEnrollment = $this->where('student_id', $data['student_id'])
                                   ->where('course_offering_id', $data['course_offering_id'])
                                   ->first();
        
        if ($existingEnrollment) {
            log_message('warning', "Student {$data['student_id']} already enrolled in offering {$data['course_offering_id']}");
            return false;
        }

        // Validate that student exists in students table
        $db = \Config\Database::connect();
        
        $student = $db->table('students')->where('id', $data['student_id'])->get()->getRowArray();
        if (!$student) {
            log_message('error', "Student ID {$data['student_id']} not found");
            return false;
        }

        // Check if course offering exists
        $offering = $db->table('course_offerings')
            ->where('id', $data['course_offering_id'])
            ->get()
            ->getRowArray();
            
        if (!$offering) {
            log_message('error', "Course offering ID {$data['course_offering_id']} not found");
            return false;
        }        // Check if course offering has available slots
        if (isset($offering['max_students']) && $offering['max_students'] > 0) {
            $currentEnrollment = $offering['current_enrollment'] ?? 0;
            if ($currentEnrollment >= $offering['max_students']) {
                log_message('warning', "Course offering {$data['course_offering_id']} is full");
                return false;
            }
        }

        // Insert the enrollment record
        try {
            $insertData = [
                'student_id'          => (int)$data['student_id'],
                'course_offering_id'  => (int)$data['course_offering_id'],
                'enrollment_date'     => $data['enrollment_date'],
                'enrollment_status'   => $data['enrollment_status'] ?? 'pending',
                'enrollment_type'     => $data['enrollment_type'] ?? 'regular',
                'year_level_id'       => $data['year_level_id'] ?? $student['year_level_id'] ?? null,
                'payment_status'      => $data['payment_status'] ?? 'unpaid',
                'enrolled_by'         => $data['enrolled_by'] ?? null,
                'status_changed_at'   => $data['status_changed_at'] ?? null,
                'notes'               => $data['notes'] ?? null
            ];
            
            $result = $this->insert($insertData);
            
            if ($result) {
                log_message('info', "Student {$data['student_id']} successfully enrolled in offering {$data['course_offering_id']}");
            }
            
            return $result;
            
        } catch (\Exception $e) {
            log_message('error', 'Enrollment insertion failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all enrollments for a student
     * 
     * @param int $student_id - The ID of the student (from students table)
     * @return array - Array of enrollments with course offering details
     */
    public function getStudentEnrollments($student_id)
    {
        // Validate student_id parameter
        if (!is_numeric($student_id) || $student_id <= 0) {
            return [];
        }

        try {
            $db = \Config\Database::connect();
              $enrollments = $db->table('enrollments e')
                ->select('
                    e.*,
                    co.id as offering_id,
                    co.section,
                    co.max_students,
                    co.current_enrollment,
                    co.room,
                    co.start_date,
                    co.end_date,
                    c.id as course_id,
                    c.course_code,
                    c.title as course_title,
                    c.description as course_description,
                    c.credits,
                    c.credits as units,
                    t.term_name,
                    t.start_date as term_start_date,
                    t.end_date as term_end_date,
                    yl.year_level_name,
                    s.student_id_number,
                    u.first_name,
                    u.middle_name,
                    u.last_name,
                    u.suffix,
                    u.email,
                    enrolling_user.first_name as enrolled_by_first_name,
                    enrolling_user.last_name as enrolled_by_last_name
                ')
                ->join('course_offerings co', 'e.course_offering_id = co.id', 'left')
                ->join('courses c', 'co.course_id = c.id', 'left')
                ->join('terms t', 'co.term_id = t.id', 'left')
                ->join('year_levels yl', 'e.year_level_id = yl.id', 'left')
                ->join('students s', 'e.student_id = s.id', 'left')
                ->join('users u', 's.user_id = u.id', 'left')
                ->join('users enrolling_user', 'e.enrolled_by = enrolling_user.id', 'left')
                ->where('e.student_id', $student_id)
                ->orderBy('e.enrollment_date', 'DESC')
                ->get()
                ->getResultArray();

            // Process and format the results
            foreach ($enrollments as &$enrollment) {
                // Get instructor names for this course offering
                $instructors = $db->table('course_instructors ci')
                    ->select('u.first_name, u.middle_name, u.last_name, u.suffix, ci.is_primary')
                    ->join('instructors i', 'ci.instructor_id = i.id')
                    ->join('users u', 'i.user_id = u.id')
                    ->where('ci.course_offering_id', $enrollment['offering_id'])
                    ->orderBy('ci.is_primary', 'DESC')
                    ->get()
                    ->getResultArray();

                if (!empty($instructors)) {
                    $instructorNames = [];
                    foreach ($instructors as $inst) {
                        $name = trim($inst['first_name'] . ' ' . ($inst['middle_name'] ?? '') . ' ' . $inst['last_name']);
                        if (!empty($inst['suffix'])) {
                            $name .= ' ' . $inst['suffix'];
                        }
                        $instructorNames[] = $name . ($inst['is_primary'] ? ' (Primary)' : '');
                    }
                    $enrollment['instructor_names'] = implode(', ', $instructorNames);
                } else {
                    $enrollment['instructor_names'] = 'No instructor assigned';
                }
                
                // Format student name
                $enrollment['student_full_name'] = trim($enrollment['first_name'] . ' ' . 
                    ($enrollment['middle_name'] ?? '') . ' ' . $enrollment['last_name']);
                if (!empty($enrollment['suffix'])) {
                    $enrollment['student_full_name'] .= ' ' . $enrollment['suffix'];
                }
                
                // Format enrolled_by name
                if ($enrollment['enrolled_by']) {
                    $enrollment['enrolled_by_name'] = trim($enrollment['enrolled_by_first_name'] . ' ' . 
                        $enrollment['enrolled_by_last_name']);
                } else {
                    $enrollment['enrolled_by_name'] = 'Self-enrolled';
                }
                
                // Format dates for better display
                $enrollment['enrollment_date_formatted'] = date('M j, Y g:i A', strtotime($enrollment['enrollment_date']));
                $enrollment['start_date_formatted'] = $enrollment['start_date'] ? 
                    date('M j, Y', strtotime($enrollment['start_date'])) : 'TBA';
                $enrollment['end_date_formatted'] = $enrollment['end_date'] ? 
                    date('M j, Y', strtotime($enrollment['end_date'])) : 'TBA';
                $enrollment['status_changed_at_formatted'] = $enrollment['status_changed_at'] ? 
                    date('M j, Y g:i A', strtotime($enrollment['status_changed_at'])) : null;
                
                // Calculate enrollment duration
                if ($enrollment['enrollment_date']) {
                    $enrollmentDate = new \DateTime($enrollment['enrollment_date']);
                    $currentDate = new \DateTime();
                    $interval = $enrollmentDate->diff($currentDate);
                    $enrollment['enrollment_duration_days'] = $interval->days;
                }
                
                // Add course progress status
                $now = date('Y-m-d');
                if ($enrollment['start_date'] && $enrollment['end_date']) {
                    if ($now < $enrollment['start_date']) {
                        $enrollment['course_progress'] = 'not_started';
                    } elseif ($now > $enrollment['end_date']) {
                        $enrollment['course_progress'] = 'ended';
                    } else {
                        $enrollment['course_progress'] = 'in_progress';
                    }
                } else {
                    $enrollment['course_progress'] = 'unknown';
                }
                
                // Add status badge color
                $enrollment['status_badge'] = $this->getStatusBadge($enrollment['enrollment_status']);
                $enrollment['payment_badge'] = $this->getPaymentBadge($enrollment['payment_status']);
            }

            return $enrollments;
            
        } catch (\Exception $e) {
            log_message('error', 'Failed to fetch student enrollments: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get student ID from user ID
     * 
     * @param int $user_id - The user ID
     * @return int|null - The student ID or null if not found
     */
    public function getStudentIdByUserId($user_id)
    {
        $db = \Config\Database::connect();
        $result = $db->table('students')
            ->select('id')
            ->where('user_id', $user_id)
            ->get()
            ->getRowArray();
        
        return $result ? $result['id'] : null;
    }

    /**
     * Get all enrollments for a user (by user_id)
     * Converts user_id to student_id and gets enrollments
     * 
     * @param int $user_id - The user ID
     * @return array
     */
    public function getUserEnrollments($user_id)
    {
        $student_id = $this->getStudentIdByUserId($user_id);
        
        if (!$student_id) {
            log_message('warning', "No student record found for user ID: {$user_id}");
            return [];
        }

        return $this->getStudentEnrollments($student_id);
    }

    /**
     * Check if a student is already enrolled in a course offering
     * 
     * @param int $student_id - The ID of the student
     * @param int $course_offering_id - The ID of the course offering
     * @return bool - True if already enrolled, false otherwise
     */
    public function isAlreadyEnrolled($student_id, $course_offering_id)
    {
        // Validate input parameters
        if (!is_numeric($student_id) || !is_numeric($course_offering_id) || 
            $student_id <= 0 || $course_offering_id <= 0) {
            return false;
        }

        try {
            $enrollment = $this->where('student_id', $student_id)
                               ->where('course_offering_id', $course_offering_id)
                               ->first();

            return $enrollment !== null;
            
        } catch (\Exception $e) {
            log_message('error', 'Failed to check enrollment status: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all students enrolled in a specific course offering
     * 
     * @param int $course_offering_id - The ID of the course offering
     * @return array - Array of student data with user information
     */
    public function getEnrolledStudents($course_offering_id)
    {
        // Validate input parameter
        if (!is_numeric($course_offering_id) || $course_offering_id <= 0) {
            return [];
        }

        try {
            $db = \Config\Database::connect();
            
            $students = $db->table('enrollments e')
                ->select('
                    e.*,
                    s.student_id_number,
                    s.section as student_section,
                    u.id as user_id,
                    u.first_name,
                    u.middle_name,
                    u.last_name,
                    u.suffix,
                    u.email,
                    yl.year_level_name
                ')
                ->join('students s', 'e.student_id = s.id', 'inner')
                ->join('users u', 's.user_id = u.id', 'inner')
                ->join('year_levels yl', 'e.year_level_id = yl.id', 'left')
                ->where('e.course_offering_id', $course_offering_id)
                ->where('e.enrollment_status', 'enrolled')
                ->orderBy('u.last_name', 'ASC')
                ->orderBy('u.first_name', 'ASC')
                ->get()
                ->getResultArray();
            
            // Format student names
            foreach ($students as &$student) {
                $student['full_name'] = trim($student['first_name'] . ' ' . 
                    ($student['middle_name'] ?? '') . ' ' . $student['last_name']);
                if (!empty($student['suffix'])) {
                    $student['full_name'] .= ' ' . $student['suffix'];
                }
            }
            
            return $students;
            
        } catch (\Exception $e) {
            log_message('error', 'Failed to fetch enrolled students: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get Bootstrap badge class for enrollment status
     * 
     * @param string $status - Enrollment status
     * @return string - Bootstrap badge class
     */
    private function getStatusBadge($status)
    {
        $badges = [
            'pending'   => 'bg-warning',
            'enrolled'  => 'bg-success',
            'dropped'   => 'bg-danger',
            'withdrawn' => 'bg-secondary',
            'completed' => 'bg-primary'
        ];
        
        return $badges[$status] ?? 'bg-secondary';
    }

    /**
     * Get Bootstrap badge class for payment status
     * 
     * @param string $status - Payment status
     * @return string - Bootstrap badge class
     */
    private function getPaymentBadge($status)
    {
        $badges = [
            'unpaid'       => 'bg-danger',
            'partial'      => 'bg-warning',
            'paid'         => 'bg-success',
            'scholarship'  => 'bg-info',
            'waived'       => 'bg-secondary'
        ];
        
        return $badges[$status] ?? 'bg-secondary';
    }
    
    /**
     * Update enrollment status
     * 
     * @param int $enrollment_id - ID of the enrollment
     * @param string $status - New status (pending, enrolled, dropped, withdrawn, completed)
     * @param array $additionalData - Additional data to update (notes, etc.)
     * @return bool - Success status
     */
    public function updateEnrollmentStatus($enrollment_id, $status, $additionalData = [])
    {
        try {
            $updateData = [
                'enrollment_status' => $status,
                'status_changed_at' => date('Y-m-d H:i:s')
            ];
            
            // Merge any additional data (like notes)
            $updateData = array_merge($updateData, $additionalData);
            
            return $this->update($enrollment_id, $updateData);
        } catch (\Exception $e) {
            log_message('error', 'Failed to update enrollment status: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get enrollment statistics for a student
     * 
     * @param int $student_id - Student ID
     * @return array - Statistics array
     */
    public function getStudentStatistics($student_id)
    {
        try {
            $enrollments = $this->where('student_id', $student_id)->findAll();
            
            $stats = [
                'total_enrollments' => count($enrollments),
                'pending'           => 0,
                'active_courses'    => 0,
                'completed_courses' => 0,
                'dropped_courses'   => 0,
                'withdrawn_courses' => 0
            ];
            
            foreach ($enrollments as $enrollment) {
                // Count by status
                switch ($enrollment['enrollment_status']) {
                    case 'pending':
                        $stats['pending']++;
                        break;
                    case 'enrolled':
                        $stats['active_courses']++;
                        break;
                    case 'completed':
                        $stats['completed_courses']++;
                        break;
                    case 'dropped':
                        $stats['dropped_courses']++;
                        break;
                    case 'withdrawn':
                        $stats['withdrawn_courses']++;
                        break;
                }
            }
            
            return $stats;
        } catch (\Exception $e) {
            log_message('error', 'Failed to get student statistics: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Update course_offerings.current_enrollment count
     * Triggered after insert, update, or delete
     */
    protected function updateEnrollmentCount(array $data)
    {
        $db = \Config\Database::connect();
        
        // Determine course_offering_id based on operation
        $courseOfferingId = null;
        
        if (isset($data['data']['course_offering_id'])) {
            // After INSERT or UPDATE
            $courseOfferingId = $data['data']['course_offering_id'];
        } elseif (isset($data['id'])) {
            // After DELETE - get from deleted record
            $deleted = $data['data'][0] ?? null;
            if ($deleted && isset($deleted['course_offering_id'])) {
                $courseOfferingId = $deleted['course_offering_id'];
            }
        }
        
        if ($courseOfferingId) {
            // Count all 'enrolled' students for this course offering
            $count = $this->where('course_offering_id', $courseOfferingId)
                         ->where('enrollment_status', 'enrolled')
                         ->countAllResults(false);
            
            // Update the course_offerings table
            $db->table('course_offerings')
               ->where('id', $courseOfferingId)
               ->update(['current_enrollment' => $count]);
        }
        
        return $data;
    }

    /**
     * Manually recalculate enrollment counts for a specific course offering
     * Useful for fixing inconsistencies
     */
    public function recalculateEnrollmentCount($courseOfferingId)
    {
        $count = $this->where('course_offering_id', $courseOfferingId)
                     ->where('enrollment_status', 'enrolled')
                     ->countAllResults();
        
        $db = \Config\Database::connect();
        $db->table('course_offerings')
           ->where('id', $courseOfferingId)
           ->update(['current_enrollment' => $count]);
        
        return $count;
    }

    /**
     * Recalculate enrollment counts for ALL course offerings
     * Run this periodically or after data imports
     */
    public function recalculateAllEnrollmentCounts()
    {
        $db = \Config\Database::connect();
        
        // Get all course offerings
        $offerings = $db->table('course_offerings')->select('id')->get()->getResultArray();
        
        $updated = 0;
        foreach ($offerings as $offering) {
            $this->recalculateEnrollmentCount($offering['id']);
            $updated++;
        }
        
        return $updated;
    }
}
