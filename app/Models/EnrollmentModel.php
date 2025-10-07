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
    protected $allowedFields    = ['user_id', 'course_id', 'enrollment_date'];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = false;
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
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

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
        
        // Check if user exists
        $userExists = $db->table('users')->where('id', $data['user_id'])->countAllResults() > 0;
        if (!$userExists) {
            return false;
        }

        // Check if course exists
        $courseExists = $db->table('courses')->where('id', $data['course_id'])->countAllResults() > 0;
        if (!$courseExists) {
            return false;
        }

        // Insert the enrollment record
        try {
            $insertData = [
                'user_id' => (int)$data['user_id'],
                'course_id' => (int)$data['course_id'],
                'enrollment_date' => $data['enrollment_date']
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
                    c.title as course_title,
                    c.description as course_description,
                    c.course_code,
                    c.category,
                    c.credits,
                    c.duration_weeks,
                    c.start_date,
                    c.end_date,
                    c.status as course_status,
                    c.instructor_id,
                    u.name as instructor_name,
                    u.email as instructor_email
                ')
                ->join('courses c', 'e.course_id = c.id', 'left')
                ->join('users u', 'c.instructor_id = u.id', 'left')
                ->where('e.user_id', $user_id)
                ->orderBy('e.enrollment_date', 'DESC')
                ->get()
                ->getResultArray();

            // Process and format the results
            foreach ($enrollments as &$enrollment) {
                // Format dates for better display
                $enrollment['enrollment_date_formatted'] = date('M j, Y', strtotime($enrollment['enrollment_date']));
                $enrollment['start_date_formatted'] = $enrollment['start_date'] ? date('M j, Y', strtotime($enrollment['start_date'])) : 'TBA';
                $enrollment['end_date_formatted'] = $enrollment['end_date'] ? date('M j, Y', strtotime($enrollment['end_date'])) : 'TBA';
                
                // Calculate enrollment duration
                $enrollmentDate = new \DateTime($enrollment['enrollment_date']);
                $currentDate = new \DateTime();
                $interval = $enrollmentDate->diff($currentDate);
                $enrollment['enrollment_duration_days'] = $interval->days;
                
                // Add enrollment status based on course dates
                $now = date('Y-m-d');
                if ($enrollment['start_date'] && $enrollment['end_date']) {
                    if ($now < $enrollment['start_date']) {
                        $enrollment['enrollment_status'] = 'upcoming';
                    } elseif ($now > $enrollment['end_date']) {
                        $enrollment['enrollment_status'] = 'completed';
                    } else {
                        $enrollment['enrollment_status'] = 'active';
                    }
                } else {
                    $enrollment['enrollment_status'] = 'active';
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
}
