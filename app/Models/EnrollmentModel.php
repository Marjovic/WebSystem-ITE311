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
        'user_id', 
        'course_id', 
        'enrollment_date',
        'enrollment_status',
        'status_date',
        'semester',
        'semester_duration_weeks',
        'semester_end_date',
        'academic_year',
        'year_level_at_enrollment',
        'enrollment_type',
        'payment_status',
        'enrolled_by',
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
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
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
     * Insert a new enrollment record
     * 
     * @param array $data - Array containing user_id, course_id, and enrollment_date
     * @return bool|int - Returns insertion ID on success, false on failure
     */
    public function enrollUser($data)
    {
        // Validate required fields
        if (!isset($data['user_id']) || !isset($data['course_id'])) {
            return false;
        }

        // Set enrollment_date to current datetime if not provided
        if (!isset($data['enrollment_date'])) {
            $data['enrollment_date'] = date('Y-m-d H:i:s');
        }

        // Check if user is already enrolled to prevent duplicates
        $existingEnrollment = $this->where('user_id', $data['user_id'])
                                   ->where('course_id', $data['course_id'])
                                   ->first();
        
        if ($existingEnrollment) {
            return false; // User already enrolled
        }

        // Validate that user_id and course_id exist in their respective tables
        $db = \Config\Database::connect();
        
        // Check if user exists and get their current year level
        $user = $db->table('users')->where('id', $data['user_id'])->get()->getRowArray();
        if (!$user) {
            return false;
        }

        // Check if course exists and get course details
        $course = $db->table('courses')->where('id', $data['course_id'])->get()->getRowArray();
        if (!$course) {
            return false;
        }        
        // Insert the enrollment record
        try {
            $insertData = [
                'user_id' => (int)$data['user_id'],
                'course_id' => (int)$data['course_id'],
                'enrollment_date' => $data['enrollment_date'],
                'enrollment_status' => $data['enrollment_status'] ?? 'enrolled',
                'year_level_at_enrollment' => $data['year_level_at_enrollment'] ?? $user['year_level'] ?? null,
                'semester' => $data['semester'] ?? null,
                'semester_duration_weeks' => $data['semester_duration_weeks'] ?? null,
                'semester_end_date' => $data['semester_end_date'] ?? null,
                'academic_year' => $data['academic_year'] ?? $course['academic_year'] ?? null,
                'enrollment_type' => $data['enrollment_type'] ?? 'regular',
                'payment_status' => $data['payment_status'] ?? 'unpaid',
                'enrolled_by' => $data['enrolled_by'] ?? null,
                'notes' => $data['notes'] ?? null
            ];
            
            return $this->insert($insertData);
        } catch (\Exception $e) {
            log_message('error', 'Enrollment insertion failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Fetch all courses a user is enrolled in
     * 
     * @param int $user_id - The ID of the user
     * @return array - Array of courses with enrollment details
     */
    public function getUserEnrollments($user_id)
    {
        // Validate user_id parameter
        if (!is_numeric($user_id) || $user_id <= 0) {
            return [];
        }

        // Join enrollments with courses and users tables to get complete information
        $db = \Config\Database::connect();
        $builder = $db->table('enrollments e');
        
        try {              
            $enrollments = $builder
                ->select('
                    e.id as enrollment_id,
                    e.user_id,
                    e.course_id,
                    e.enrollment_date,
                    e.enrollment_status,
                    e.status_date,
                    e.semester,
                    e.academic_year,
                    e.year_level_at_enrollment,
                    e.enrollment_type,
                    e.payment_status,
                    e.semester_duration_weeks,
                    e.semester_end_date,
                    e.notes,
                    c.title as course_title,
                    c.description as course_description,
                    c.course_code,
                    c.category,
                    c.credits,
                    c.duration_weeks,
                    c.start_date,
                    c.end_date,
                    c.status as course_status,
                    c.instructor_ids
                ')
                ->join('courses c', 'e.course_id = c.id', 'left')
                ->where('e.user_id', $user_id)
                ->orderBy('e.enrollment_date', 'DESC')
                ->get()
                ->getResultArray();

            // Process and format the results
            foreach ($enrollments as &$enrollment) {
                // Get instructor names for multiple instructors
                $instructorIds = json_decode($enrollment['instructor_ids'] ?? '[]', true);
                if (!empty($instructorIds)) {
                    $instructorData = $db->table('users')
                        ->select('name, email')
                        ->whereIn('id', $instructorIds)
                        ->where('role', 'teacher')
                        ->get()
                        ->getResultArray();
                    $enrollment['instructor_name'] = implode(', ', array_column($instructorData, 'name'));
                    $enrollment['instructor_email'] = implode(', ', array_column($instructorData, 'email'));
                } else {
                    $enrollment['instructor_name'] = 'No instructor assigned';
                    $enrollment['instructor_email'] = '';
                }
                  
                // Format dates for better display
                $enrollment['enrollment_date_formatted'] = date('M j, Y', strtotime($enrollment['enrollment_date']));
                $enrollment['start_date_formatted'] = $enrollment['start_date'] ? date('M j, Y', strtotime($enrollment['start_date'])) : 'TBA';
                $enrollment['end_date_formatted'] = $enrollment['end_date'] ? date('M j, Y', strtotime($enrollment['end_date'])) : 'TBA';
                $enrollment['semester_end_date_formatted'] = $enrollment['semester_end_date'] ? date('M j, Y', strtotime($enrollment['semester_end_date'])) : null;
                
                // Calculate enrollment duration
                $enrollmentDate = new \DateTime($enrollment['enrollment_date']);
                $currentDate = new \DateTime();
                $interval = $enrollmentDate->diff($currentDate);
                $enrollment['enrollment_duration_days'] = $interval->days;                
                // Add course progress status (different from enrollment_status)
                $now = date('Y-m-d');
                if ($enrollment['start_date'] && $enrollment['end_date']) {
                    if ($now < $enrollment['start_date']) {
                        $enrollment['course_progress'] = 'upcoming';
                    } elseif ($now > $enrollment['end_date']) {
                        $enrollment['course_progress'] = 'finished';
                    } else {
                        $enrollment['course_progress'] = 'ongoing';
                    }
                } else {
                    $enrollment['course_progress'] = 'ongoing';
                }
                
                // Add status badge color
                $enrollment['status_badge'] = $this->getStatusBadge($enrollment['enrollment_status']);
                
                // Format status date
                if ($enrollment['status_date']) {
                    $enrollment['status_date_formatted'] = date('M j, Y', strtotime($enrollment['status_date']));
                } else {
                    $enrollment['status_date_formatted'] = 'N/A';
                }
            }

            return $enrollments;
            
        } catch (\Exception $e) {
            log_message('error', 'Failed to fetch user enrollments: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Check if a user is already enrolled in a specific course to prevent duplicates
     * 
     * @param int $user_id - The ID of the user
     * @param int $course_id - The ID of the course
     * @return bool - True if already enrolled, false otherwise
     */
    public function isAlreadyEnrolled($user_id, $course_id)
    {
        // Validate input parameters
        if (!is_numeric($user_id) || !is_numeric($course_id) || $user_id <= 0 || $course_id <= 0) {
            return false;
        }

        try {
            // Check if enrollment record exists
            $enrollment = $this->where('user_id', $user_id)
                               ->where('course_id', $course_id)
                               ->first();

            // If enrollment exists, also verify that both user and course still exist
            if ($enrollment) {
                $db = \Config\Database::connect();
                
                // Verify user still exists
                $userExists = $db->table('users')
                                 ->where('id', $user_id)
                                 ->countAllResults() > 0;
                
                // Verify course still exists
                $courseExists = $db->table('courses')
                                   ->where('id', $course_id)
                                   ->countAllResults() > 0;
                
                // Return true only if enrollment exists AND both user and course exist
                return $userExists && $courseExists;
            }

            return false;
            
        } catch (\Exception $e) {
            log_message('error', 'Failed to check enrollment status: ' . $e->getMessage());
            return false; // Return false on error to be safe
        }
    }

    /**
     * Get all students enrolled in a specific course
     * Used for sending notifications when materials are uploaded
     * 
     * @param int $course_id - The ID of the course
     * @return array - Array of student data (id, name, email)
     */
    public function getEnrolledStudents($course_id)
    {
        // Validate input parameter
        if (!is_numeric($course_id) || $course_id <= 0) {
            return [];
        }

        try {
            $db = \Config\Database::connect();
            $builder = $db->table('enrollments e');
            
            $students = $builder
                ->select('u.id, u.name, u.email')
                ->join('users u', 'e.user_id = u.id', 'inner')
                ->where('e.course_id', $course_id)
                ->where('u.role', 'student')
                ->orderBy('u.name', 'ASC')
                ->get()
                ->getResultArray();
            
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
            'enrolled' => 'bg-success',
            'dropped' => 'bg-warning',
            'completed' => 'bg-primary',
            'withdrawn' => 'bg-secondary'
        ];
        
        return $badges[$status] ?? 'bg-secondary';
    }    
    
    /**
     * Update enrollment status (for dropping, completing courses, etc.)
     * 
     * @param int $enrollment_id - ID of the enrollment
     * @param string $status - New status (enrolled, dropped, completed, withdrawn)
     * @param array $additionalData - Additional data to update (notes, etc.)
     * @return bool - Success status
     */
    public function updateEnrollmentStatus($enrollment_id, $status, $additionalData = [])
    {
        try {
            $updateData = [
                'enrollment_status' => $status,
                'status_date' => date('Y-m-d H:i:s')
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
     * @param int $user_id - Student ID
     * @return array - Statistics array
     */
    public function getStudentStatistics($user_id)
    {
        try {
            $enrollments = $this->where('user_id', $user_id)->findAll();
            
            $stats = [
                'total_enrollments' => count($enrollments),
                'active_courses' => 0,
                'completed_courses' => 0,
                'dropped_courses' => 0,
                'withdrawn_courses' => 0
            ];
            
            foreach ($enrollments as $enrollment) {
                // Count by status
                switch ($enrollment['enrollment_status']) {
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
                         ->countAllResults(false); // false = don't reset query
            
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
