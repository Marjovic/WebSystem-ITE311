<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\EnrollmentModel;

class Course extends BaseController
{
    protected $session;
    protected $enrollmentModel;

    public function __construct()
    {
        // Initialize session service
        $this->session = \Config\Services::session();
        
        // Initialize enrollment model
        $this->enrollmentModel = new EnrollmentModel();
    }    
    /**
     * Handle AJAX enrollment request
     * 
     * @return ResponseInterface JSON response indicating success or failure
     */
    public function enroll()
    {
        // Set content type to JSON for AJAX response
        $this->response->setContentType('application/json');

        // 1. AUTHENTICATION CHECK
        if (!$this->session->get('isLoggedIn')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Unauthorized access. Please login first.',
                'error_code' => 'UNAUTHORIZED'
            ])->setStatusCode(401);
        }

        // 2. ROLE CHECK
        if ($this->session->get('role') !== 'student') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Access denied. Only students can enroll in courses.',
                'error_code' => 'ACCESS_DENIED'
            ])->setStatusCode(403);
        }

        // 3. METHOD CHECK
        if (!$this->request->is('post')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid request method. Only POST requests are allowed.',
                'error_code' => 'INVALID_METHOD'
            ])->setStatusCode(405);
        }

        // 4. AJAX CHECK
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid request type. Only AJAX requests are allowed.',
                'error_code' => 'INVALID_REQUEST_TYPE'
            ])->setStatusCode(400);
        }

        // 5. CSRF VALIDATION
        try {
            // Check if CSRF protection is enabled
            $config = config('Security');
            if ($config->csrfProtection !== false && $config->csrfProtection !== '') {
                
                // Get CSRF token name and hash from config/request
                $tokenName = $config->csrfTokenName ?? 'csrf_token_name';
                $headerName = $config->csrfHeaderName ?? 'X-CSRF-TOKEN';
                
                // Get submitted token from POST data or header
                $submittedToken = $this->request->getPost($tokenName) ?? $this->request->getHeaderLine($headerName);
                
                // Get expected token from session/cookie
                $expectedToken = csrf_hash();
                
                // Validate tokens
                if (empty($submittedToken) || empty($expectedToken)) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Security token is missing. Please refresh the page and try again.',
                        'error_code' => 'CSRF_MISSING',
                        'csrf_hash' => csrf_hash(),
                        'debug' => [
                            'submitted_token_empty' => empty($submittedToken),
                            'expected_token_empty' => empty($expectedToken),
                            'token_name' => $tokenName
                        ]
                    ])->setStatusCode(403);
                }
                
                if (!hash_equals($expectedToken, $submittedToken)) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Security token validation failed. Please refresh the page and try again.',
                        'error_code' => 'CSRF_INVALID',
                        'csrf_hash' => csrf_hash(),
                        'debug' => [
                            'submitted_token' => substr($submittedToken, 0, 10) . '...',
                            'expected_token' => substr($expectedToken, 0, 10) . '...',
                            'tokens_match' => false
                        ]
                    ])->setStatusCode(403);
                }
            }
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'CSRF validation error: ' . $e->getMessage(),
                'error_code' => 'CSRF_EXCEPTION',
                'csrf_hash' => csrf_hash()
            ])->setStatusCode(403);
        }

        // 6. INPUT VALIDATION
        $course_id = $this->request->getPost('course_id');
        
        if (!$course_id || !is_numeric($course_id) || $course_id <= 0) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Valid course ID is required.',
                'error_code' => 'INVALID_COURSE_ID',
                'csrf_hash' => csrf_hash()
            ])->setStatusCode(400);
        }

        // 7. USE SESSION USER ID (NEVER trust client input for user ID)
        $user_id = $this->session->get('userID');

        // Convert to integer for safety
        $course_id = (int)$course_id;

        try {
            // 8. CHECK IF ALREADY ENROLLED
            $alreadyEnrolled = $this->enrollmentModel->isAlreadyEnrolled($user_id, $course_id);
            
            if ($alreadyEnrolled) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'You are already enrolled in this course.',
                    'error_code' => 'ALREADY_ENROLLED',
                    'csrf_hash' => csrf_hash()
                ])->setStatusCode(409);
            }            
            $philippineTimezone = new \DateTimeZone('Asia/Manila');
            $currentDateTime = new \DateTime('now', $philippineTimezone);
            
            $enrollmentData = [
                'user_id' => $user_id,
                'course_id' => $course_id,
                'enrollment_date' => $currentDateTime->format('Y-m-d H:i:s')
            ];            // 10. ATTEMPT TO ENROLL USER
            $enrollmentResult = $this->enrollmentModel->enrollUser($enrollmentData);

            if ($enrollmentResult) {
                // Step 7: Generate notification for student upon successful enrollment
                try {
                    // Get course details for notification message
                    $db = \Config\Database::connect();
                    $course = $db->table('courses')->where('id', $course_id)->get()->getRowArray();
                    $courseName = $course['course_name'] ?? 'the course';
                    
                    // Create notification for the student
                    $notificationModel = new \App\Models\NotificationModel();
                    $notificationModel->insert([
                        'user_id'    => $user_id,
                        'message'    => "You have been successfully enrolled in {$courseName}",
                        'is_read'    => 0,
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                } catch (\Exception $e) {
                    // Log notification error but don't fail the enrollment
                    log_message('error', 'Failed to create enrollment notification: ' . $e->getMessage());
                }
                
                // Success: User enrolled successfully
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Successfully enrolled in the course!',
                    'data' => [
                        'enrollment_id' => $enrollmentResult,
                        'user_id' => $user_id,
                        'course_id' => $course_id,                        
                        'enrollment_date' => $enrollmentData['enrollment_date'],
                        'enrollment_date_formatted' => $currentDateTime->format('M j, Y g:iA')
                    ],
                    'csrf_hash' => csrf_hash() // Provide new token for next request
                ])->setStatusCode(201);
            } else {
                // Enrollment failed for some reason
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to enroll in the course. Please try again later.',
                    'error_code' => 'ENROLLMENT_FAILED',
                    'csrf_hash' => csrf_hash()
                ])->setStatusCode(500);
            }

        } catch (\Exception $e) {
            // Log the error for debugging
            log_message('error', 'Course enrollment error: ' . $e->getMessage());
            
            // Return generic error message to user
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An unexpected error occurred. Please try again later.',
                'error_code' => 'INTERNAL_ERROR',
                'csrf_hash' => csrf_hash()
            ])->setStatusCode(500);
        }
    }

    /**
     * Handle teacher request to remove a student from their course
     * 
     * @return ResponseInterface JSON response indicating success or failure
     */
    public function removeStudent()
    {
        // Set content type to JSON for AJAX response
        $this->response->setContentType('application/json');

        // 1. AUTHENTICATION CHECK
        if (!$this->session->get('isLoggedIn')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Unauthorized access. Please login first.',
                'error_code' => 'UNAUTHORIZED'
            ])->setStatusCode(401);
        }

        // 2. ROLE CHECK - Only teachers can remove students
        if ($this->session->get('role') !== 'teacher') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Access denied. Only teachers can remove students from courses.',
                'error_code' => 'ACCESS_DENIED'
            ])->setStatusCode(403);
        }

        // 3. METHOD CHECK
        if (!$this->request->is('post')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid request method. Only POST requests are allowed.',
                'error_code' => 'INVALID_METHOD'
            ])->setStatusCode(405);
        }

        // 4. CSRF VALIDATION
        try {
            if (!$this->validate(['csrf_test_name' => 'required'])) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'CSRF validation failed.',
                    'error_code' => 'CSRF_FAILED'
                ])->setStatusCode(400);
            }
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'CSRF validation error.',
                'error_code' => 'CSRF_ERROR'
            ])->setStatusCode(400);
        }

        // 5. INPUT VALIDATION
        $student_id = $this->request->getPost('student_id');
        $course_id = $this->request->getPost('course_id');
        
        if (!$student_id || !is_numeric($student_id) || $student_id <= 0) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid student ID provided.',
                'error_code' => 'INVALID_STUDENT_ID',
                'csrf_hash' => csrf_hash()
            ])->setStatusCode(400);
        }

        if (!$course_id || !is_numeric($course_id) || $course_id <= 0) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid course ID provided.',
                'error_code' => 'INVALID_COURSE_ID',
                'csrf_hash' => csrf_hash()
            ])->setStatusCode(400);
        }

        // 6. USE SESSION TEACHER ID
        $teacher_id = $this->session->get('userID');

        // Convert to integers for safety
        $student_id = (int)$student_id;
        $course_id = (int)$course_id;        try {            // 7. VERIFY TEACHER OWNS THE COURSE
            $db = \Config\Database::connect();
            $course = $db->table('courses')
                ->where('id', $course_id)
                ->groupStart()
                    ->where("JSON_CONTAINS(instructor_ids, '\"$teacher_id\"')", null, false)
                    ->orWhere("JSON_CONTAINS(instructor_ids, '$teacher_id')", null, false)
                ->groupEnd()
                ->get()
                ->getRowArray();

            if (!$course) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'You do not have permission to remove students from this course.',
                    'error_code' => 'COURSE_NOT_OWNED',
                    'csrf_hash' => csrf_hash()
                ])->setStatusCode(403);
            }

            // 8. VERIFY STUDENT IS ENROLLED IN THE COURSE
            $enrollment = $db->table('enrollments')
                ->where('user_id', $student_id)
                ->where('course_id', $course_id)
                ->get()
                ->getRowArray();

            if (!$enrollment) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Student is not enrolled in this course.',
                    'error_code' => 'STUDENT_NOT_ENROLLED',
                    'csrf_hash' => csrf_hash()
                ])->setStatusCode(404);
            }

            // 9. GET STUDENT DETAILS FOR LOGGING
            $student = $db->table('users')
                ->select('name, email')
                ->where('id', $student_id)
                ->where('role', 'student')
                ->get()
                ->getRowArray();

            if (!$student) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Student not found or invalid.',
                    'error_code' => 'STUDENT_NOT_FOUND',
                    'csrf_hash' => csrf_hash()
                ])->setStatusCode(404);
            }            // 10. REMOVE STUDENT FROM COURSE
            $removeResult = $db->table('enrollments')
                ->where('user_id', $student_id)
                ->where('course_id', $course_id)
                ->delete();

            if ($removeResult) {
                // Generate notifications for both student and teacher
                try {
                    $courseName = $course['title'] ?? 'a course';
                    $courseCode = $course['course_code'] ?? '';
                    $teacherName = $this->session->get('name');
                    
                    $notificationModel = new \App\Models\NotificationModel();
                    
                    // Notification for the student
                    $notificationModel->insert([
                        'user_id'    => $student_id,
                        'message'    => "You have been removed from {$courseName} ({$courseCode}) by {$teacherName}",
                        'is_read'    => 0,
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                    
                    // Notification for the teacher
                    $notificationModel->insert([
                        'user_id'    => $teacher_id,
                        'message'    => "You removed {$student['name']} from {$courseName} ({$courseCode})",
                        'is_read'    => 0,
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                } catch (\Exception $e) {
                    // Log notification error but don't fail the removal
                    log_message('error', 'Failed to create removal notifications: ' . $e->getMessage());
                }
                
                // Log the removal activity
                log_message('info', 'Teacher ' . $this->session->get('name') . ' (ID: ' . $teacher_id . ') removed student ' . $student['name'] . ' (ID: ' . $student_id . ') from course "' . $course['title'] . '" (ID: ' . $course_id . ')');

                // Success: Student removed successfully
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Student successfully removed from the course.',
                    'data' => [
                        'student_name' => $student['name'],
                        'student_email' => $student['email'],
                        'course_title' => $course['title'],
                        'course_code' => $course['course_code'],
                        'removed_by' => $this->session->get('name'),
                        'removal_date' => date('M j, Y g:iA')
                    ],
                    'csrf_hash' => csrf_hash()
                ])->setStatusCode(200);
            } else {
                // Removal failed for some reason
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to remove student from the course. Please try again later.',
                    'error_code' => 'REMOVAL_FAILED',
                    'csrf_hash' => csrf_hash()
                ])->setStatusCode(500);
            }

        } catch (\Exception $e) {
            // Log the error for debugging
            log_message('error', 'Student removal error: ' . $e->getMessage());
            
            // Return generic error message to user
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An unexpected error occurred. Please try again later.',
                'error_code' => 'INTERNAL_ERROR',
                'csrf_hash' => csrf_hash()
            ])->setStatusCode(500);
        }
    }

    /**
     * Handle teacher request to add a student to their course
     * 
     * @return ResponseInterface JSON response indicating success or failure
     */
    public function addStudent()
    {
        // Set content type to JSON for AJAX response
        $this->response->setContentType('application/json');

        // 1. AUTHENTICATION CHECK
        if (!$this->session->get('isLoggedIn')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Unauthorized access. Please login first.',
                'error_code' => 'UNAUTHORIZED'
            ])->setStatusCode(401);
        }

        // 2. ROLE CHECK - Only teachers can add students
        if ($this->session->get('role') !== 'teacher') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Access denied. Only teachers can add students to courses.',
                'error_code' => 'ACCESS_DENIED'
            ])->setStatusCode(403);
        }

        // 3. METHOD CHECK
        if (!$this->request->is('post')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid request method. Only POST requests are allowed.',
                'error_code' => 'INVALID_METHOD'
            ])->setStatusCode(405);
        }

        // 4. AJAX CHECK
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid request type. Only AJAX requests are allowed.',
                'error_code' => 'INVALID_REQUEST_TYPE'
            ])->setStatusCode(400);
        }

        // 5. INPUT VALIDATION
        $student_id = $this->request->getPost('student_id');
        $course_id = $this->request->getPost('course_id');

        if (!$student_id || !$course_id) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Student ID and Course ID are required.',
                'error_code' => 'MISSING_PARAMETERS',
                'csrf_hash' => csrf_hash()
            ])->setStatusCode(400);
        }

        // 6. GET TEACHER ID FROM SESSION
        $teacher_id = $this->session->get('userID');

        // Convert to integers for safety
        $student_id = (int)$student_id;
        $course_id = (int)$course_id;        try {
            // 7. VERIFY TEACHER OWNS THE COURSE
            $db = \Config\Database::connect();
            $course = $db->table('courses')
                ->where('id', $course_id)
                ->groupStart()
                    ->where("JSON_CONTAINS(instructor_ids, '\"$teacher_id\"')", null, false)
                    ->orWhere("JSON_CONTAINS(instructor_ids, '$teacher_id')", null, false)
                ->groupEnd()
                ->get()
                ->getRowArray();

            if (!$course) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'You do not have permission to add students to this course.',
                    'error_code' => 'COURSE_NOT_OWNED',
                    'csrf_hash' => csrf_hash()
                ])->setStatusCode(403);
            }

            // 8. VERIFY STUDENT EXISTS AND IS A STUDENT
            $student = $db->table('users')
                ->select('name, email')
                ->where('id', $student_id)
                ->where('role', 'student')
                ->get()
                ->getRowArray();

            if (!$student) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Student not found or invalid.',
                    'error_code' => 'STUDENT_NOT_FOUND',
                    'csrf_hash' => csrf_hash()
                ])->setStatusCode(404);
            }

            // 9. CHECK IF STUDENT IS ALREADY ENROLLED IN THE COURSE
            $enrollment = $db->table('enrollments')
                ->where('user_id', $student_id)
                ->where('course_id', $course_id)
                ->get()
                ->getRowArray();

            if ($enrollment) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Student is already enrolled in this course.',
                    'error_code' => 'STUDENT_ALREADY_ENROLLED',
                    'csrf_hash' => csrf_hash()
                ])->setStatusCode(400);
            }

            // 10. CHECK COURSE ENROLLMENT LIMIT
            $enrollmentCount = $db->table('enrollments')
                ->where('course_id', $course_id)
                ->countAllResults();

            if ($course['max_students'] > 0 && $enrollmentCount >= $course['max_students']) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Course has reached maximum enrollment limit.',
                    'error_code' => 'ENROLLMENT_LIMIT_REACHED',
                    'csrf_hash' => csrf_hash()
                ])->setStatusCode(400);
            }

            // 11. ADD STUDENT TO COURSE
            $enrollmentData = [
                'user_id' => $student_id,
                'course_id' => $course_id,
                'enrollment_date' => date('Y-m-d H:i:s')
            ];            $addResult = $db->table('enrollments')->insert($enrollmentData);

            if ($addResult) {
                // Generate notifications for both student and teacher
                try {
                    $courseName = $course['title'] ?? 'a course';
                    $courseCode = $course['course_code'] ?? '';
                    $teacherName = $this->session->get('name');
                    
                    $notificationModel = new \App\Models\NotificationModel();
                    
                    // Notification for the student
                    $notificationModel->insert([
                        'user_id'    => $student_id,
                        'message'    => "You have been added to {$courseName} ({$courseCode}) by {$teacherName}",
                        'is_read'    => 0,
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                    
                    // Notification for the teacher
                    $notificationModel->insert([
                        'user_id'    => $teacher_id,
                        'message'    => "You added {$student['name']} to {$courseName} ({$courseCode})",
                        'is_read'    => 0,
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                } catch (\Exception $e) {
                    // Log notification error but don't fail the enrollment
                    log_message('error', 'Failed to create teacher-add notifications: ' . $e->getMessage());
                }
                
                // Log the addition activity
                log_message('info', 'Teacher ' . $this->session->get('name') . ' (ID: ' . $teacher_id . ') added student ' . $student['name'] . ' (ID: ' . $student_id . ') to course "' . $course['title'] . '" (ID: ' . $course_id . ')');

                // Success: Student added successfully
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Student successfully added to the course.',
                    'data' => [
                        'student_name' => $student['name'],
                        'student_email' => $student['email'],
                        'student_id' => $student_id,
                        'course_title' => $course['title'],
                        'course_code' => $course['course_code'],
                        'enrollment_date' => $enrollmentData['enrollment_date'],
                        'enrollment_date_formatted' => date('M j, Y', strtotime($enrollmentData['enrollment_date'])),
                        'added_by' => $this->session->get('name'),
                        'addition_date' => date('M j, Y g:iA')
                    ],
                    'csrf_hash' => csrf_hash()
                ])->setStatusCode(200);
            } else {
                // Addition failed for some reason
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to add student to the course. Please try again later.',
                    'error_code' => 'ADDITION_FAILED',
                    'csrf_hash' => csrf_hash()
                ])->setStatusCode(500);
            }

        } catch (\Exception $e) {
            // Log the error for debugging
            log_message('error', 'Student addition error: ' . $e->getMessage());
            
            // Return generic error message to user
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An unexpected error occurred. Please try again later.',
                'error_code' => 'INTERNAL_ERROR',
                'csrf_hash' => csrf_hash()
            ])->setStatusCode(500);
        }
    }

    /**
     * Get available students for adding to a course
     * 
     * @return ResponseInterface JSON response with available students
     */
    public function getAvailableStudents()
    {
        // Set content type to JSON for AJAX response
        $this->response->setContentType('application/json');

        // 1. AUTHENTICATION CHECK
        if (!$this->session->get('isLoggedIn')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Unauthorized access. Please login first.',
                'error_code' => 'UNAUTHORIZED'
            ])->setStatusCode(401);
        }

        // 2. ROLE CHECK - Only teachers can access this
        if ($this->session->get('role') !== 'teacher') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Access denied. Only teachers can access this resource.',
                'error_code' => 'ACCESS_DENIED'
            ])->setStatusCode(403);
        }

        // 3. GET COURSE ID
        $course_id = $this->request->getGet('course_id');

        if (!$course_id || !is_numeric($course_id)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Valid course ID is required.',
                'error_code' => 'INVALID_COURSE_ID'
            ])->setStatusCode(400);
        }

        $course_id = (int)$course_id;
        $teacher_id = $this->session->get('userID');        try {
            $db = \Config\Database::connect();
            
            // 4. VERIFY TEACHER OWNS THE COURSE
            $course = $db->table('courses')
                ->where('id', $course_id)
                ->groupStart()
                    ->where("JSON_CONTAINS(instructor_ids, '\"$teacher_id\"')", null, false)
                    ->orWhere("JSON_CONTAINS(instructor_ids, '$teacher_id')", null, false)
                ->groupEnd()
                ->get()
                ->getRowArray();

            if (!$course) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Course not found or access denied.',
                    'error_code' => 'COURSE_NOT_OWNED'
                ])->setStatusCode(403);
            }

            // 5. GET STUDENTS NOT ENROLLED IN THIS COURSE
            $availableStudents = $db->table('users')
                ->select('id, name, email')
                ->where('role', 'student')
                ->where('id NOT IN (SELECT user_id FROM enrollments WHERE course_id = ' . $course_id . ')', null, false)
                ->orderBy('name', 'ASC')
                ->get()
                ->getResultArray();

            return $this->response->setJSON([
                'success' => true,
                'data' => [
                    'students' => $availableStudents,
                    'course_title' => $course['title'],
                    'course_code' => $course['course_code']
                ]
            ])->setStatusCode(200);

        } catch (\Exception $e) {
            // Log the error for debugging
            log_message('error', 'Get available students error: ' . $e->getMessage());
            
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An unexpected error occurred. Please try again later.',
                'error_code' => 'INTERNAL_ERROR'
            ])->setStatusCode(500);
        }
    }
}
