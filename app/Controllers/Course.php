<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\EnrollmentModel;
use App\Models\CourseModel;
use App\Models\CourseOfferingModel;
use App\Models\CourseInstructorModel;
use App\Models\CourseScheduleModel;
use App\Models\CoursePrerequisiteModel;
use App\Models\YearLevelModel;
use App\Models\SemesterModel;
use App\Models\TermModel;
use App\Models\AcademicYearModel;
use App\Models\DepartmentModel;
use App\Models\CategoryModel;
use App\Models\NotificationModel;
use App\Models\UserModel;

class Course extends BaseController
{
    protected $session;
    protected $validation;
    protected $db;
    
    // Models
    protected $enrollmentModel;
    protected $courseModel;
    protected $courseOfferingModel;
    protected $courseInstructorModel;
    protected $courseScheduleModel;
    protected $coursePrerequisiteModel;
    protected $yearLevelModel;
    protected $semesterModel;
    protected $termModel;
    protected $academicYearModel;
    protected $departmentModel;
    protected $categoryModel;
    protected $notificationModel;
    protected $userModel;

    public function __construct()
    {
        // Initialize session service
        $this->session = \Config\Services::session();
        
        // Initialize validation service
        $this->validation = \Config\Services::validation();
        
        // Initialize database connection
        $this->db = \Config\Database::connect();
        
        // Initialize all models
        $this->enrollmentModel = new EnrollmentModel();
        $this->courseModel = new CourseModel();
        $this->courseOfferingModel = new CourseOfferingModel();
        $this->courseInstructorModel = new CourseInstructorModel();
        $this->courseScheduleModel = new CourseScheduleModel();
        $this->coursePrerequisiteModel = new CoursePrerequisiteModel();
        $this->yearLevelModel = new YearLevelModel();
        $this->semesterModel = new SemesterModel();
        $this->termModel = new TermModel();
        $this->academicYearModel = new AcademicYearModel();
        $this->departmentModel = new DepartmentModel();
        $this->categoryModel = new CategoryModel();
        $this->notificationModel = new NotificationModel();
        $this->userModel = new UserModel();
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
                ])->setStatusCode(409);            }            
            $philippineTimezone = new \DateTimeZone('Asia/Manila');
            $currentDateTime = new \DateTime('now', $philippineTimezone);
            
            // Get course details for semester and academic year
            $db = \Config\Database::connect();
            $course = $db->table('courses')->where('id', $course_id)->get()->getRowArray();
            
            // Determine semester based on current date
            // First semester: August - December, Second semester: January - July
            $currentMonth = (int)date('n');
            $semester = ($currentMonth >= 8 && $currentMonth <= 12) ? 'First Semester' : 'Second Semester';
            
            // Calculate semester end date (16 weeks from enrollment)
            $semesterEndDate = clone $currentDateTime;
            $semesterEndDate->modify('+16 weeks'); // Add 16 weeks (112 days)
            
            // Calculate semester duration in weeks
            $semesterDuration = 16; // Standard semester duration
            
            $enrollmentData = [
                'user_id' => $user_id,
                'course_id' => $course_id,
                'enrollment_date' => $currentDateTime->format('Y-m-d H:i:s'),
                'semester' => $semester,
                'semester_duration_weeks' => $semesterDuration,
                'semester_end_date' => $semesterEndDate->format('Y-m-d H:i:s'),
                'academic_year' => $course['academic_year'] ?? null
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
        $course_id = (int)$course_id;        
        try {            
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
            }            // 8. VERIFY STUDENT EXISTS AND IS A STUDENT
            $student = $db->table('users')
                ->select('name, email, year_level')
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
            }            // 11. GET STUDENT'S YEAR LEVEL
            $studentYearLevel = $student['year_level'] ?? null;
            
            // 11a. DETERMINE SEMESTER BASED ON CURRENT DATE
            // First semester: August - December, Second semester: January - July
            $currentMonth = (int)date('n');
            $semester = ($currentMonth >= 8 && $currentMonth <= 12) ? 'First Semester' : 'Second Semester';
            
            // 11b. CALCULATE SEMESTER END DATE (16 weeks from enrollment)
            $enrollmentDateTime = new \DateTime('now', new \DateTimeZone('Asia/Manila'));
            $semesterEndDate = clone $enrollmentDateTime;
            $semesterEndDate->modify('+16 weeks'); // Add 16 weeks (112 days)
            
            // 11c. CALCULATE SEMESTER DURATION IN WEEKS
            $semesterDuration = 16; // Standard semester duration
            
            // 12. ADD STUDENT TO COURSE
            $enrollmentData = [
                'user_id' => $student_id,
                'course_id' => $course_id,
                'enrollment_date' => $enrollmentDateTime->format('Y-m-d H:i:s'),
                'enrollment_status' => 'enrolled',
                'year_level_at_enrollment' => $studentYearLevel,
                'academic_year' => $course['academic_year'] ?? null,
                'semester' => $semester,
                'semester_duration_weeks' => $semesterDuration,
                'semester_end_date' => $semesterEndDate->format('Y-m-d H:i:s'),
                'enrollment_type' => 'regular',
                'payment_status' => 'unpaid',
                'enrolled_by' => $teacher_id
            ];
            
            $addResult = $db->table('enrollments')->insert($enrollmentData);

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
                log_message('info', 'Teacher ' . $this->session->get('name') . ' (ID: ' . $teacher_id . ') added student ' . $student['name'] . ' (ID: ' . $student_id . ') to course "' . $course['title'] . '" (ID: ' . $course_id . ')');                // Success: Student added successfully
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
                        'year_level_at_enrollment' => $studentYearLevel,
                        'semester' => $semester,
                        'semester_duration_weeks' => $semesterDuration,
                        'semester_end_date' => $enrollmentData['semester_end_date'],
                        'semester_end_date_formatted' => $semesterEndDate->format('M j, Y'),
                        'academic_year' => $course['academic_year'] ?? null,
                        'enrollment_type' => 'regular',
                        'enrollment_status' => 'enrolled',
                        'payment_status' => 'unpaid',
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
            }            // 5. GET STUDENTS NOT ENROLLED IN THIS COURSE
            $availableStudents = $db->table('users')
                ->select('id, name, email, year_level')
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

    // Manage Courses Method - Consolidated method for all course management operations  
    // This handles create, edit, delete, and display operations in one place to avoid spaghetti code
    public function manageCourses()
    {
        // Security check - only admins can access this page
        if ($this->session->get('isLoggedIn') !== true) {
            $this->session->setFlashdata('error', 'Please login to access this page.');
            return redirect()->to(base_url('login'));        
        }
        
        if ($this->session->get('role') !== 'admin') {
            $this->session->setFlashdata('error', 'Access denied. You do not have permission to access this page.');
            $userRole = $this->session->get('role');
            return redirect()->to(base_url($userRole . '/dashboard'));
        }

        $action = $this->request->getGet('action');
        $courseID = $this->request->getGet('id');
        
        // ===== CREATE COURSE =====
        if ($action === 'create' && $this->request->getMethod() === 'POST') {            $rules = [
                'title' => 'required|min_length[3]|max_length[200]|regex_match[/^[a-zA-Z\s\-\.]+$/]',
                'course_code' => 'required|min_length[3]|max_length[20]|regex_match[/^[A-Z]+\-?[0-9]+$/]|is_unique[courses.course_code]',
                'academic_year' => 'permit_empty|max_length[20]|regex_match[/^[0-9]{4}\-[0-9]{4}$/]',
                'instructor_ids' => 'permit_empty',
                'category' => 'permit_empty|max_length[100]|regex_match[/^[a-zA-Z\s\-\.]+$/]',
                'credits' => 'permit_empty|integer|greater_than[0]|less_than[10]',
                'duration_weeks' => 'permit_empty|integer|greater_than[0]|less_than[100]',
                'max_students' => 'permit_empty|integer|greater_than[0]|less_than[1000]',
                'start_date' => 'permit_empty|valid_date[Y-m-d]',
                'end_date' => 'permit_empty|valid_date[Y-m-d]',
                'status' => 'required|in_list[draft,active,completed,cancelled]',
                'description' => 'permit_empty|max_length[1000]|regex_match[/^[a-zA-Z0-9\s\.\,\:\;\!\?\n\r•\-]+$/]'
            ];

            $messages = [
                'title' => [
                    'required' => 'Course title is required.',
                    'min_length' => 'Course title must be at least 3 characters long.',
                    'max_length' => 'Course title cannot exceed 200 characters.',
                    'regex_match' => 'Course title can only contain letters, spaces, hyphens, and periods.'
                ],
                'course_code' => [
                    'required' => 'Course code is required.',
                    'min_length' => 'Course code must be at least 3 characters long.',
                    'max_length' => 'Course code cannot exceed 20 characters.',
                    'regex_match' => 'Course code must start with letters followed by numbers (e.g., CS101, MATH201).',
                    'is_unique' => 'This course code is already in use.'
                ],
                'academic_year' => [
                    'max_length' => 'Academic year cannot exceed 20 characters.',
                    'regex_match' => 'Academic year must be in format YYYY-YYYY (e.g., 2024-2025, 2025-2026).'
                ],
                'instructor_id' => [
                    'integer' => 'Invalid instructor selected.',
                    'greater_than' => 'Please select a valid instructor if choosing one.'
                ],
                'category' => [
                    'max_length' => 'Category cannot exceed 100 characters.',
                    'regex_match' => 'Category can only contain letters, spaces, hyphens, and periods.'
                ],
                'credits' => [
                    'integer' => 'Credits must be a valid number.',
                    'greater_than' => 'Credits must be greater than 0.',
                    'less_than' => 'Credits cannot exceed 9.'
                ],
                'duration_weeks' => [
                    'integer' => 'Duration must be a valid number.',
                    'greater_than' => 'Duration must be greater than 0 weeks.',
                    'less_than' => 'Duration cannot exceed 99 weeks.'
                ],
                'max_students' => [
                    'integer' => 'Max students must be a valid number.',
                    'greater_than' => 'Max students must be greater than 0.',
                    'less_than' => 'Max students cannot exceed 999.'
                ],
                'start_date' => [
                    'valid_date' => 'Please enter a valid start date.'
                ],                'end_date' => [
                    'valid_date' => 'Please enter a valid end date.'
                ],
                'status' => [
                    'required' => 'Course status is required.',
                    'in_list' => 'Invalid course status selected.'
                ],
                'description' => [
                    'max_length' => 'Description cannot exceed 1000 characters.',
                    'regex_match' => 'Description can only contain letters, numbers, spaces, hyphens, and basic punctuation (periods, commas, colons, semicolons, exclamation marks, question marks, and bullet points •).'
                ]
            ];
              if ($this->validate($rules, $messages)) {
                // Additional validation: Check if dates align with academic year
                $academicYear = $this->request->getPost('academic_year');
                $startDate = $this->request->getPost('start_date');
                $endDate = $this->request->getPost('end_date');
                
                if ($academicYear && ($startDate || $endDate)) {
                    $academicYearError = $this->validateAcademicYearDates($academicYear, $startDate, $endDate);
                    if ($academicYearError) {
                        $this->session->setFlashdata('error', $academicYearError);
                        $this->session->setFlashdata('errors', ['academic_year' => $academicYearError]);
                        return redirect()->to(base_url('admin/manage_courses?action=create'))->withInput();
                    }
                }
                
                $instructorIds = $this->request->getPost('instructor_ids');
                $finalInstructorIds = [];
                
                if ($instructorIds && is_array($instructorIds)) {
                    foreach ($instructorIds as $instructorId) {
                        if (is_numeric($instructorId) && $instructorId > 0) {
                            $finalInstructorIds[] = (int)$instructorId;
                        }
                    }
                }                $courseData = [
                    'title' => $this->request->getPost('title'),
                    'course_code' => $this->request->getPost('course_code'),
                    'academic_year' => $academicYear ?: null,
                    'instructor_ids' => json_encode($finalInstructorIds),
                    'category' => $this->request->getPost('category') ?: null,
                    'credits' => $this->request->getPost('credits') ?: 3,
                    'duration_weeks' => $this->request->getPost('duration_weeks') ?: 16,
                    'max_students' => $this->request->getPost('max_students') ?: 30,
                    'start_date' => $startDate ?: null,
                    'end_date' => $endDate ?: null,
                    'status' => $this->request->getPost('status'),
                    'description' => $this->request->getPost('description'),
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];                $coursesBuilder = $this->db->table('courses');
                if ($coursesBuilder->insert($courseData)) {
                    $creationActivity = [
                        'type' => 'course_creation',
                        'icon' => '📚',
                        'title' => 'New Course Created',
                        'description' => 'Course "' . esc($courseData['title']) . '" (' . esc($courseData['course_code']) . ') was created by admin',
                        'time' => date('Y-m-d H:i:s'),
                        'course_title' => esc($courseData['title']),
                        'course_code' => esc($courseData['course_code']),
                        'created_by' => $this->session->get('name')
                    ];

                    $creationActivities = $this->session->get('creation_activities') ?? [];
                    array_unshift($creationActivities, $creationActivity);
                    $creationActivities = array_slice($creationActivities, 0, 10);
                    $this->session->set('creation_activities', $creationActivities);

                    // Generate notifications for assigned teachers
                    if (!empty($finalInstructorIds)) {
                        try {
                            $courseName = $courseData['title'];
                            $courseCode = $courseData['course_code'];
                            $adminName = $this->session->get('name');
                            $adminId = $this->session->get('userID');
                            
                            $notificationModel = new \App\Models\NotificationModel();
                            
                            // Get teacher details for notifications
                            $teacherBuilder = $this->db->table('users');
                            $teachers = $teacherBuilder
                                ->select('id, name')
                                ->whereIn('id', $finalInstructorIds)
                                ->where('role', 'teacher')
                                ->get()
                                ->getResultArray();
                            
                            foreach ($teachers as $teacher) {
                                // Notification for each teacher
                                $notificationModel->insert([
                                    'user_id'    => $teacher['id'],
                                    'message'    => "You have been assigned to teach {$courseName} ({$courseCode}) by Admin {$adminName}",
                                    'is_read'    => 0,
                                    'created_at' => date('Y-m-d H:i:s')
                                ]);
                                
                                // Notification for admin (one per teacher assigned)
                                $notificationModel->insert([
                                    'user_id'    => $adminId,
                                    'message'    => "You assigned {$teacher['name']} to teach {$courseName} ({$courseCode})",
                                    'is_read'    => 0,
                                    'created_at' => date('Y-m-d H:i:s')
                                ]);
                            }
                        } catch (\Exception $e) {
                            log_message('error', 'Failed to create course assignment notifications: ' . $e->getMessage());
                        }
                    }

                    $this->session->setFlashdata('success', 'Course created successfully!');
                    return redirect()->to(base_url('admin/manage_courses'));
                } else {
                    $this->session->setFlashdata('error', 'Failed to create course. Please try again.');
                }
            } else {
                $this->session->setFlashdata('errors', $this->validation->getErrors());
            }
        }
        
        // ===== EDIT COURSE =====
        if ($action === 'edit' && $courseID) {
            $coursesBuilder = $this->db->table('courses');
            $courseToEdit = $coursesBuilder->where('id', $courseID)->get()->getRowArray();

            if (!$courseToEdit) {
                $this->session->setFlashdata('error', 'Course not found.');
                return redirect()->to(base_url('admin/manage_courses'));
            }
              if ($this->request->getMethod() === 'POST') {
                $rules = [
                    'title' => 'required|min_length[3]|max_length[200]|regex_match[/^[a-zA-Z\s\-\.]+$/]',
                    'course_code' => "required|min_length[3]|max_length[20]|regex_match[/^[A-Z]+\-?[0-9]+$/]|is_unique[courses.course_code,id,{$courseID}]",
                    'academic_year' => 'permit_empty|max_length[20]|regex_match[/^[0-9]{4}\-[0-9]{4}$/]',
                    'instructor_ids' => 'permit_empty',
                    'category' => 'permit_empty|max_length[100]|regex_match[/^[a-zA-Z\s\-\.]+$/]',                    'credits' => 'permit_empty|integer|greater_than[0]|less_than[10]',
                    'duration_weeks' => 'permit_empty|integer|greater_than[0]|less_than[100]',
                    'max_students' => 'permit_empty|integer|greater_than[0]|less_than[1000]',
                    'start_date' => 'permit_empty|valid_date[Y-m-d]',
                    'end_date' => 'permit_empty|valid_date[Y-m-d]',
                    'status' => 'required|in_list[draft,active,completed,cancelled]',
                    'description' => 'permit_empty|max_length[1000]|regex_match[/^[a-zA-Z0-9\s\.\,\:\;\!\?\n\r•\-]+$/]'
                ];

                $messages = [
                    'title' => [
                        'required' => 'Course title is required.',
                        'min_length' => 'Course title must be at least 3 characters long.',
                        'max_length' => 'Course title cannot exceed 200 characters.',
                        'regex_match' => 'Course title can only contain letters, spaces, hyphens, and periods.'
                    ],
                    'course_code' => [
                        'required' => 'Course code is required.',
                        'min_length' => 'Course code must be at least 3 characters long.',
                        'max_length' => 'Course code cannot exceed 20 characters.',
                        'regex_match' => 'Course code must start with letters followed by numbers (e.g., CS101, MATH201).',
                        'is_unique' => 'This course code is already in use.'
                    ],
                    'academic_year' => [
                        'max_length' => 'Academic year cannot exceed 20 characters.',
                        'regex_match' => 'Academic year must be in format YYYY-YYYY (e.g., 2024-2025, 2025-2026).'
                    ],
                    'instructor_id' => [
                        'integer' => 'Invalid instructor selected.',
                        'greater_than' => 'Please select a valid instructor if choosing one.'
                    ],
                    'category' => [
                        'max_length' => 'Category cannot exceed 100 characters.',
                        'regex_match' => 'Category can only contain letters, spaces, hyphens, and periods.'
                    ],                    'status' => [
                        'required' => 'Course status is required.',
                        'in_list' => 'Invalid course status selected.'
                    ],
                    'description' => [
                        'max_length' => 'Description cannot exceed 1000 characters.',
                        'regex_match' => 'Description can only contain letters, numbers, spaces, hyphens, and basic punctuation (periods, commas, colons, semicolons, exclamation marks, question marks, and bullet points •).'
                    ]                ];
                
                if ($this->validate($rules, $messages)) {
                    // Additional validation: Check if dates align with academic year
                    $academicYear = $this->request->getPost('academic_year');
                    $startDate = $this->request->getPost('start_date');
                    $endDate = $this->request->getPost('end_date');
                    
                    if ($academicYear && ($startDate || $endDate)) {
                        $academicYearError = $this->validateAcademicYearDates($academicYear, $startDate, $endDate);
                        if ($academicYearError) {
                            $this->session->setFlashdata('error', $academicYearError);
                            $this->session->setFlashdata('errors', ['academic_year' => $academicYearError]);
                            return redirect()->to(base_url('admin/manage_courses?action=edit&id=' . $courseID))->withInput();
                        }
                    }
                    
                    $instructorIds = $this->request->getPost('instructor_ids');
                    $finalInstructorIds = [];
                    
                    if ($instructorIds && is_array($instructorIds)) {
                        foreach ($instructorIds as $instructorId) {
                            if (is_numeric($instructorId) && $instructorId > 0) {
                                $finalInstructorIds[] = (int)$instructorId;
                            }
                        }
                    }                    $updateData = [
                        'title' => $this->request->getPost('title'),
                        'course_code' => $this->request->getPost('course_code'),
                        'academic_year' => $academicYear ?: null,
                        'instructor_ids' => json_encode($finalInstructorIds),
                        'category' => $this->request->getPost('category') ?: null,
                        'credits' => $this->request->getPost('credits') ?: 3,
                        'duration_weeks' => $this->request->getPost('duration_weeks') ?: 16,
                        'max_students' => $this->request->getPost('max_students') ?: 30,
                        'start_date' => $startDate ?: null,
                        'end_date' => $endDate ?: null,
                        'status' => $this->request->getPost('status'),
                        'description' => $this->request->getPost('description'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ];                    if ($coursesBuilder->where('id', $courseID)->update($updateData)) {
                        // Detect instructor changes and send notifications
                        $originalInstructorIds = json_decode($courseToEdit['instructor_ids'] ?? '[]', true);
                        if (!is_array($originalInstructorIds)) {
                            $originalInstructorIds = [];
                        }
                        
                        // Find added and removed instructors
                        $addedInstructorIds = array_diff($finalInstructorIds, $originalInstructorIds);
                        $removedInstructorIds = array_diff($originalInstructorIds, $finalInstructorIds);
                        
                        // Send notifications for instructor changes
                        if (!empty($addedInstructorIds) || !empty($removedInstructorIds)) {
                            try {
                                $courseName = $updateData['title'];
                                $courseCode = $updateData['course_code'];
                                $adminName = $this->session->get('name');
                                $adminId = $this->session->get('userID');
                                
                                $notificationModel = new \App\Models\NotificationModel();
                                
                                // Handle added instructors
                                if (!empty($addedInstructorIds)) {
                                    $teacherBuilder = $this->db->table('users');
                                    $addedTeachers = $teacherBuilder
                                        ->select('id, name')
                                        ->whereIn('id', $addedInstructorIds)
                                        ->where('role', 'teacher')
                                        ->get()
                                        ->getResultArray();
                                    
                                    foreach ($addedTeachers as $teacher) {
                                        // Notification for teacher
                                        $notificationModel->insert([
                                            'user_id'    => $teacher['id'],
                                            'message'    => "You have been assigned to teach {$courseName} ({$courseCode}) by Admin {$adminName}",
                                            'is_read'    => 0,
                                            'created_at' => date('Y-m-d H:i:s')
                                        ]);
                                        
                                        // Notification for admin
                                        $notificationModel->insert([
                                            'user_id'    => $adminId,
                                            'message'    => "You assigned {$teacher['name']} to teach {$courseName} ({$courseCode})",
                                            'is_read'    => 0,
                                            'created_at' => date('Y-m-d H:i:s')
                                        ]);
                                    }
                                }
                                
                                // Handle removed instructors
                                if (!empty($removedInstructorIds)) {
                                    $teacherBuilder = $this->db->table('users');
                                    $removedTeachers = $teacherBuilder
                                        ->select('id, name')
                                        ->whereIn('id', $removedInstructorIds)
                                        ->where('role', 'teacher')
                                        ->get()
                                        ->getResultArray();
                                    
                                    foreach ($removedTeachers as $teacher) {
                                        // Notification for teacher
                                        $notificationModel->insert([
                                            'user_id'    => $teacher['id'],
                                            'message'    => "You have been removed from teaching {$courseName} ({$courseCode}) by Admin {$adminName}",
                                            'is_read'    => 0,
                                            'created_at' => date('Y-m-d H:i:s')
                                        ]);
                                        
                                        // Notification for admin
                                        $notificationModel->insert([
                                            'user_id'    => $adminId,
                                            'message'    => "You removed {$teacher['name']} from teaching {$courseName} ({$courseCode})",
                                            'is_read'    => 0,
                                            'created_at' => date('Y-m-d H:i:s')
                                        ]);
                                    }
                                }
                            } catch (\Exception $e) {
                                log_message('error', 'Failed to create instructor change notifications: ' . $e->getMessage());
                            }
                        }
                        
                        $updateActivity = [
                            'type' => 'course_update',
                            'icon' => '✏️',
                            'title' => 'Course Updated',
                            'description' => 'Course "' . esc($updateData['title']) . '" (' . esc($updateData['course_code']) . ') was updated by admin',
                            'time' => date('Y-m-d H:i:s'),
                            'course_title' => esc($updateData['title']),
                            'course_code' => esc($updateData['course_code']),
                            'updated_by' => $this->session->get('name')
                        ];

                        $updateActivities = $this->session->get('update_activities') ?? [];
                        array_unshift($updateActivities, $updateActivity);
                        $updateActivities = array_slice($updateActivities, 0, 10);
                        $this->session->set('update_activities', $updateActivities);

                        $this->session->setFlashdata('success', 'Course updated successfully!');
                        return redirect()->to(base_url('admin/manage_courses'));
                    } else {
                        $this->session->setFlashdata('error', 'Failed to update course. Please try again.');
                    }
                } else {
                    $this->session->setFlashdata('errors', $this->validation->getErrors());
                }
            }
            
            $courses = $coursesBuilder
                ->select('courses.*')
                ->orderBy('courses.created_at', 'DESC')
                ->get()
                ->getResultArray();

            foreach ($courses as &$course) {
                $instructorIds = json_decode($course['instructor_ids'] ?? '[]', true);
                if (!empty($instructorIds)) {
                    $instructorNames = $this->db->table('users')
                        ->select('name')
                        ->whereIn('id', $instructorIds)
                        ->where('role', 'teacher')
                        ->get()
                        ->getResultArray();
                    $course['instructor_name'] = implode(', ', array_column($instructorNames, 'name'));
                } else {
                    $course['instructor_name'] = 'No instructor assigned';
                }
            }

            $teachersBuilder = $this->db->table('users');
            $teachers = $teachersBuilder->where('role', 'teacher')->orderBy('name', 'ASC')->get()->getResultArray();

            $data = [
                'user' => [
                    'userID' => $this->session->get('userID'),
                    'name'   => $this->session->get('name'),
                    'email'  => $this->session->get('email'),
                    'role'   => $this->session->get('role')
                ],
                'title' => 'Edit Course - Admin Dashboard',
                'courses' => $courses,
                'teachers' => $teachers,
                'editCourse' => $courseToEdit,
                'showCreateForm' => false,
                'showEditForm' => true
            ];
            return view('admin/manage_courses', $data);
        }
        
        // ===== DELETE COURSE =====
        if ($action === 'delete' && $courseID) {
            $coursesBuilder = $this->db->table('courses');
            $courseToDelete = $coursesBuilder->where('id', $courseID)->get()->getRowArray();

            if (!$courseToDelete) {
                $this->session->setFlashdata('error', 'Course not found.');
                return redirect()->to(base_url('admin/manage_courses'));
            }

            $deletionActivity = [
                'type' => 'course_deletion',
                'icon' => '🗑️',
                'title' => 'Course Deleted',
                'description' => 'Course "' . esc($courseToDelete['title']) . '" (' . esc($courseToDelete['course_code']) . ') was removed from the system',
                'time' => date('Y-m-d H:i:s'),
                'course_title' => esc($courseToDelete['title']),
                'course_code' => esc($courseToDelete['course_code']),
                'deleted_by' => $this->session->get('name')
            ];

            $deletionActivities = $this->session->get('deletion_activities') ?? [];
            array_unshift($deletionActivities, $deletionActivity);
            $deletionActivities = array_slice($deletionActivities, 0, 10);
            $this->session->set('deletion_activities', $deletionActivities);

            $deleteBuilder = $this->db->table('courses');
            if ($deleteBuilder->where('id', $courseID)->delete()) {
                $this->session->setFlashdata('success', 'Course deleted successfully!');
            } else {
                $this->session->setFlashdata('error', 'Failed to delete course. Please try again.');
            }

            return redirect()->to(base_url('admin/manage_courses'));
        }
          
        // ===== SHOW COURSE MANAGEMENT INTERFACE =====
        $coursesBuilder = $this->db->table('courses');
        $courses = $coursesBuilder
            ->select('courses.*')
            ->orderBy('courses.created_at', 'DESC')
            ->get()
            ->getResultArray();

        foreach ($courses as &$course) {
            $instructorIds = json_decode($course['instructor_ids'] ?? '[]', true);
            if (!empty($instructorIds)) {
                $instructorNames = $this->db->table('users')
                    ->select('name')
                    ->whereIn('id', $instructorIds)
                    ->where('role', 'teacher')
                    ->get()
                    ->getResultArray();
                $course['instructor_name'] = implode(', ', array_column($instructorNames, 'name'));
            } else {
                $course['instructor_name'] = 'No instructor assigned';
            }
        }

        $teachersBuilder = $this->db->table('users');
        $teachers = $teachersBuilder->where('role', 'teacher')->orderBy('name', 'ASC')->get()->getResultArray();

        $data = [
            'user' => [
                'userID' => $this->session->get('userID'),
                'name'   => $this->session->get('name'),
                'email'  => $this->session->get('email'),
                'role'   => $this->session->get('role')
            ],
            'title' => 'Manage Courses - Admin Dashboard',
            'courses' => $courses,
            'teachers' => $teachers,
            'editCourse' => null,
            'showCreateForm' => $this->request->getGet('create') === 'true' || ($action === 'create' && $this->request->getMethod() !== 'POST'),
            'showEditForm' => false
        ];
        
        return view('admin/manage_courses', $data);
    }

    // =====================================================
    // TEACHER COURSE MANAGEMENT METHODS  
    // =====================================================
    
    /**
     * Teacher Courses Management - Show courses assigned to teacher and available courses
     */
    public function teacherCourses()
    {
        // Security check - only teachers can access this page
        if ($this->session->get('isLoggedIn') !== true) {
            $this->session->setFlashdata('error', 'Please login to access this page.');
            return redirect()->to(base_url('login'));
        }
        
        if ($this->session->get('role') !== 'teacher') {
            $this->session->setFlashdata('error', 'Access denied. Only teachers can access this page.');
            $userRole = $this->session->get('role');
            return redirect()->to(base_url($userRole . '/dashboard'));        }

        $teacherID = $this->session->get('userID');
        
        // Handle course assignment request
        if ($this->request->getMethod() === 'POST' && $this->request->getPost('action') === 'assign_course') {
            return $this->handleCourseAssignmentRequest($teacherID);
        }
        
        // Handle course unassignment request
        if ($this->request->getMethod() === 'POST' && $this->request->getPost('action') === 'unassign_course') {
            return $this->handleCourseUnassignmentRequest($teacherID);
        }        // Get courses assigned to this teacher using JSON_CONTAINS
        // Use both string and integer format to ensure compatibility
        $assignedCoursesBuilder = $this->db->table('courses');
        $assignedCourses = $assignedCoursesBuilder
            ->select('courses.*, 
                      COUNT(enrollments.id) as enrolled_students')
            ->join('enrollments', 'courses.id = enrollments.course_id', 'left')
            ->groupStart()
                ->where("JSON_CONTAINS(courses.instructor_ids, '\"$teacherID\"')", null, false)
                ->orWhere("JSON_CONTAINS(courses.instructor_ids, '$teacherID')", null, false)
            ->groupEnd()
            ->groupBy('courses.id')
            ->orderBy('courses.created_at', 'DESC')            ->get()
            ->getResultArray();        // Get detailed student information for each course with enhanced enrollment data
        foreach ($assignedCourses as &$course) {
            $studentsBuilder = $this->db->table('enrollments');                $course['students'] = $studentsBuilder
                ->select('users.id as user_id, users.name, users.email, 
                         enrollments.enrollment_date, enrollments.enrollment_status,
                         enrollments.year_level_at_enrollment, enrollments.enrollment_type,
                         enrollments.payment_status, enrollments.semester, enrollments.academic_year,
                         enrollments.semester_duration_weeks, enrollments.semester_end_date')
                ->join('users', 'enrollments.user_id = users.id')
                ->where('enrollments.course_id', $course['id'])
                ->where('users.role', 'student')
                ->orderBy('users.name', 'ASC')
                ->get()
                ->getResultArray();
                
            // Get co-instructor information (other instructors assigned to this course)
            $instructorIds = json_decode($course['instructor_ids'] ?? '[]', true);
            if (is_array($instructorIds) && count($instructorIds) > 1) {
                // Get other instructor names (exclude current teacher)
                $otherInstructorIds = array_filter($instructorIds, function($id) use ($teacherID) {
                    return $id != $teacherID;
                });
                
                if (!empty($otherInstructorIds)) {
                    $coInstructorsBuilder = $this->db->table('users');
                    $course['co_instructors'] = $coInstructorsBuilder
                        ->select('id, name, email')
                        ->whereIn('id', $otherInstructorIds)
                        ->where('role', 'teacher')
                        ->orderBy('name', 'ASC')
                        ->get()
                        ->getResultArray();
                } else {
                    $course['co_instructors'] = [];
                }
            } else {
                $course['co_instructors'] = [];
            }
        }        // Get available courses (active courses without instructors or teacher not already assigned)
        $availableCoursesBuilder = $this->db->table('courses');
        $availableCourses = $availableCoursesBuilder
            ->select('courses.*, COUNT(enrollments.id) as enrolled_students')
            ->join('enrollments', 'courses.id = enrollments.course_id', 'left')
            ->where('courses.status', 'active')
            ->groupStart()
                ->where('courses.instructor_ids IS NULL')
                ->orWhere('courses.instructor_ids', '[]')
                ->orGroupStart()
                    ->where("NOT JSON_CONTAINS(courses.instructor_ids, '\"$teacherID\"')", null, false)
                    ->where("NOT JSON_CONTAINS(courses.instructor_ids, '$teacherID')", null, false)
                ->groupEnd()
            ->groupEnd()
            ->groupBy('courses.id')
            ->orderBy('courses.created_at', 'DESC')
            ->get()
            ->getResultArray();// Format dates for available courses
        foreach ($availableCourses as &$course) {
            if (isset($course['start_date']) && $course['start_date']) {
                $course['start_date_formatted'] = date('M j, Y', strtotime($course['start_date']));
            } else {
                $course['start_date_formatted'] = 'TBA';
            }
            
            if (isset($course['end_date']) && $course['end_date']) {
                $course['end_date_formatted'] = date('M j, Y', strtotime($course['end_date']));
            } else {
                $course['end_date_formatted'] = 'TBA';
            }
            
            // Get existing instructor information for available courses
            $instructorIds = json_decode($course['instructor_ids'] ?? '[]', true);
            if (is_array($instructorIds) && !empty($instructorIds)) {
                $existingInstructorsBuilder = $this->db->table('users');
                $course['existing_instructors'] = $existingInstructorsBuilder
                    ->select('id, name, email')
                    ->whereIn('id', $instructorIds)
                    ->where('role', 'teacher')
                    ->orderBy('name', 'ASC')
                    ->get()
                    ->getResultArray();
            } else {
                $course['existing_instructors'] = [];
            }
        }

        $data = [
            'user' => [
                'userID' => $this->session->get('userID'),
                'name'   => $this->session->get('name'),
                'email'  => $this->session->get('email'),
                'role'   => $this->session->get('role')
            ],
            'title' => 'My Courses - Teacher Dashboard',
            'assignedCourses' => $assignedCourses,
            'availableCourses' => $availableCourses
        ];
        
        return view('teacher/courses', $data);
    }
    
    public function studentCourses()
    {
        // Security check - only students can access this page
        if ($this->session->get('isLoggedIn') !== true) {
            $this->session->setFlashdata('error', 'Please login to access this page.');
            return redirect()->to(base_url('login'));
        }
        
        if ($this->session->get('role') !== 'student') {
            $this->session->setFlashdata('error', 'Access denied. Only students can access this page.');
            $userRole = $this->session->get('role');
            return redirect()->to(base_url($userRole . '/dashboard'));
        }

        $studentID = $this->session->get('userID');
          // Initialize enrollment model to fetch student enrollment data
        $enrollmentModel = new \App\Models\EnrollmentModel();
        
        // Initialize material model to fetch course materials
        $materialModel = new \App\Models\MaterialModel();
        
        // Get enrolled courses for this student with detailed information
        $enrolledCourses = $enrollmentModel->getUserEnrollments($studentID);
        
        // Get materials for each enrolled course
        foreach ($enrolledCourses as &$course) {
            $course['materials'] = $materialModel->getMaterialsByCourse($course['course_id']);
        }
          // Get available courses that student can still enroll in
        $coursesBuilder = $this->db->table('courses');
        $availableCourses = $coursesBuilder
            ->select('courses.*')
            ->where('courses.status', 'active')
            ->orderBy('courses.created_at', 'DESC')
            ->get()
            ->getResultArray();
        
        // Get instructor names for each course
        foreach ($availableCourses as &$course) {
            $instructorIds = json_decode($course['instructor_ids'] ?? '[]', true);
            if (!empty($instructorIds)) {
                $instructorNames = $this->db->table('users')
                    ->select('name')
                    ->whereIn('id', $instructorIds)
                    ->where('role', 'teacher')
                    ->get()
                    ->getResultArray();
                $course['instructor_name'] = implode(', ', array_column($instructorNames, 'name'));
            } else {
                $course['instructor_name'] = 'No instructor assigned';
            }
        }
        
        // Filter out courses the student is already enrolled in
        $enrolledCourseIds = array_column($enrolledCourses, 'course_id');
        $availableCoursesFiltered = array_filter($availableCourses, function($course) use ($enrolledCourseIds) {
            return !in_array($course['id'], $enrolledCourseIds);
        });
          // Add progress data to enrolled courses (placeholder - can be enhanced with actual progress tracking)
        foreach ($enrolledCourses as &$course) {
            $course['progress'] = 0; // Default progress is 0% (no progress yet)
            $course['status_badge'] = $this->getCourseStatusBadge($course['course_status']);
            
            // Format dates for better display
            if (isset($course['start_date']) && $course['start_date']) {
                $course['start_date_formatted'] = date('M j, Y', strtotime($course['start_date']));
            } else {
                $course['start_date_formatted'] = 'TBA';
            }
            
            if (isset($course['end_date']) && $course['end_date']) {
                $course['end_date_formatted'] = date('M j, Y', strtotime($course['end_date']));
            } else {
                $course['end_date_formatted'] = 'TBA';
            }
        }
        
        // Format available courses dates
        foreach ($availableCoursesFiltered as &$course) {
            if (isset($course['start_date']) && $course['start_date']) {
                $course['start_date_formatted'] = date('M j, Y', strtotime($course['start_date']));
            } else {
                $course['start_date_formatted'] = 'TBA';
            }
            
            if (isset($course['end_date']) && $course['end_date']) {
                $course['end_date_formatted'] = date('M j, Y', strtotime($course['end_date']));
            } else {
                $course['end_date_formatted'] = 'TBA';
            }
        }

        $data = [
            'user' => [
                'userID' => $this->session->get('userID'),
                'name'   => $this->session->get('name'),
                'email'  => $this->session->get('email'),
                'role'   => $this->session->get('role')
            ],
            'title' => 'My Courses - Student Dashboard',
            'enrolledCourses' => $enrolledCourses,
            'availableCourses' => array_values($availableCoursesFiltered),
            'totalEnrolled' => count($enrolledCourses),
            'totalAvailable' => count($availableCoursesFiltered)
        ];
        
        return view('student/courses', $data);
    }
    
    /**
     * Helper method to get course status badge HTML
     */
    private function getCourseStatusBadge($status)
    {
        switch (strtolower($status)) {
            case 'active':
                return '<span class="badge bg-success">Active</span>';
            case 'draft':
                return '<span class="badge bg-warning">Draft</span>';
            case 'completed':
                return '<span class="badge bg-info">Completed</span>';
            case 'archived':
                return '<span class="badge bg-secondary">Archived</span>';
            default:
                return '<span class="badge bg-light text-dark">Unknown</span>';
        }
    }

    /**
     * Handle teacher request to be assigned to a course
     */
    private function handleCourseAssignmentRequest($teacherID)
    {
        $courseID = $this->request->getPost('course_id');
        
        if (!$courseID || !is_numeric($courseID)) {
            $this->session->setFlashdata('error', 'Invalid course ID.');
            return redirect()->to(base_url('teacher/courses'));
        }        // Validate that the course exists and teacher is not already assigned
        $courseBuilder = $this->db->table('courses');
        $course = $courseBuilder
            ->where('id', $courseID)
            ->where('status', 'active')
            ->where("(instructor_ids IS NULL OR instructor_ids = '[]' OR (NOT JSON_CONTAINS(instructor_ids, '\"$teacherID\"') AND NOT JSON_CONTAINS(instructor_ids, '$teacherID')))", null, false)
            ->get()
            ->getRowArray();
            
        if (!$course) {
            $this->session->setFlashdata('error', 'Course not found, not available, or you are already assigned to it.');
            return redirect()->to(base_url('teacher/courses'));
        }
        
        // Get current instructor IDs and add this teacher
        $currentInstructorIds = json_decode($course['instructor_ids'] ?? '[]', true);
        if (!is_array($currentInstructorIds)) {
            $currentInstructorIds = [];
        }
        $currentInstructorIds[] = $teacherID;
        
        // Assign the teacher to the course
        $updateData = [
            'instructor_ids' => json_encode(array_unique($currentInstructorIds)),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        if ($courseBuilder->where('id', $courseID)->update($updateData)) {
            // Record course assignment activity
            $assignmentActivity = [
                'type' => 'course_assignment',
                'icon' => '👨‍🏫',
                'title' => 'Course Assignment',
                'description' => 'Teacher ' . esc($this->session->get('name')) . ' was assigned to teach "' . esc($course['title']) . '" (' . esc($course['course_code']) . ')',
                'time' => date('Y-m-d H:i:s'),
                'course_title' => esc($course['title']),
                'course_code' => esc($course['course_code']),
                'teacher_name' => esc($this->session->get('name')),
                'assigned_by' => 'Self-Assignment'
            ];

            // Get existing assignment activities from session (if any)
            $assignmentActivities = $this->session->get('assignment_activities') ?? [];
            
            // Add new assignment activity to the beginning of the array
            array_unshift($assignmentActivities, $assignmentActivity);
            
            // Keep only the last 10 assignment activities to prevent session bloat
            $assignmentActivities = array_slice($assignmentActivities, 0, 10);
            
            // Store updated assignment activities in session
            $this->session->set('assignment_activities', $assignmentActivities);

            $this->session->setFlashdata('success', 'You have been successfully assigned to teach "' . esc($course['title']) . '"!');
        } else {
            $this->session->setFlashdata('error', 'Failed to assign course. Please try again.');
        }
          return redirect()->to(base_url('teacher/courses'));
    }

    /**
     * Handle teacher request to unassign themselves from a course
     */
    private function handleCourseUnassignmentRequest($teacherID)
    {
        $courseID = $this->request->getPost('course_id');
        
        if (!$courseID || !is_numeric($courseID)) {
            $this->session->setFlashdata('error', 'Invalid course ID.');
            return redirect()->to(base_url('teacher/courses'));
        }        // Validate that the course exists and teacher is assigned to it
        $courseBuilder = $this->db->table('courses');
        $course = $courseBuilder
            ->where('id', $courseID)
            ->groupStart()
                ->where("JSON_CONTAINS(instructor_ids, '\"$teacherID\"')", null, false)
                ->orWhere("JSON_CONTAINS(instructor_ids, '$teacherID')", null, false)
            ->groupEnd()
            ->get()
            ->getRowArray();
            
        if (!$course) {
            $this->session->setFlashdata('error', 'Course not found or you are not assigned to it.');
            return redirect()->to(base_url('teacher/courses'));
        }
        
        // Check if course has enrolled students - warn but still allow unassignment
        $enrollmentCount = $this->db->table('enrollments')
            ->where('course_id', $courseID)
            ->countAllResults();
        
        // Remove teacher from instructor IDs
        $currentInstructorIds = json_decode($course['instructor_ids'] ?? '[]', true);
        if (!is_array($currentInstructorIds)) {
            $currentInstructorIds = [];
        }
        $currentInstructorIds = array_values(array_filter($currentInstructorIds, function($id) use ($teacherID) {
            return $id != $teacherID;
        }));
        
        // Unassign the teacher from the course
        $updateData = [
            'instructor_ids' => json_encode($currentInstructorIds),
            'updated_at' => date('Y-m-d H:i:s')
        ];
          if ($courseBuilder->where('id', $courseID)->update($updateData)) {
            // Record course unassignment activity
            $unassignmentActivity = [
                'type' => 'course_unassignment',
                'icon' => '🔄',
                'title' => 'Course Unassignment',
                'description' => 'Teacher ' . esc($this->session->get('name')) . ' unassigned themselves from "' . esc($course['title']) . '" (' . esc($course['course_code']) . ')',
                'time' => date('Y-m-d H:i:s'),
                'course_title' => esc($course['title']),
                'course_code' => esc($course['course_code']),
                'teacher_name' => esc($this->session->get('name')),
                'student_count' => $enrollmentCount,
                'unassigned_by' => 'Self-Unassignment'
            ];

            // Get existing assignment activities from session (if any)
            $assignmentActivities = $this->session->get('assignment_activities') ?? [];
            
            // Add new unassignment activity to the beginning of the array
            array_unshift($assignmentActivities, $unassignmentActivity);
            
            // Keep only the last 10 assignment activities to prevent session bloat
            $assignmentActivities = array_slice($assignmentActivities, 0, 10);
            
            // Store updated assignment activities in session
            $this->session->set('assignment_activities', $assignmentActivities);

            // Generate notification for admin when teacher self-unassigns
            try {
                $courseName = $course['title'] ?? 'a course';
                $courseCode = $course['course_code'] ?? '';
                $teacherName = $this->session->get('name');
                
                $notificationModel = new \App\Models\NotificationModel();
                
                // Get all admin users
                $adminBuilder = $this->db->table('users');
                $admins = $adminBuilder
                    ->select('id, name')
                    ->where('role', 'admin')
                    ->get()
                    ->getResultArray();
                
                // Send notification to each admin
                foreach ($admins as $admin) {
                    $notificationModel->insert([
                        'user_id'    => $admin['id'],
                        'message'    => "Teacher {$teacherName} has unassigned themselves from {$courseName} ({$courseCode})",
                        'is_read'    => 0,
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                }
            } catch (\Exception $e) {
                log_message('error', 'Failed to create teacher self-unassignment notification: ' . $e->getMessage());
            }

            // Show appropriate success message
            if ($enrollmentCount > 0) {
                $this->session->setFlashdata('success', 'You have been unassigned from "' . esc($course['title']) . '"! Note: ' . $enrollmentCount . ' student(s) are still enrolled in this course.');
            } else {
                $this->session->setFlashdata('success', 'You have been successfully unassigned from "' . esc($course['title']) . '"!');
            }
        } else {
            $this->session->setFlashdata('error', 'Failed to unassign course. Please try again.');
        }
        
        return redirect()->to(base_url('teacher/courses'));
    }
      /**
     * Validate that course dates fall within the specified academic year
     * Enforces: 
     * - Academic year must be current year or future (e.g., 2025-2026 onward)
     * - Start date must be today or later
     * - End date must be no more than 220 class days from start date
     * 
     * @param string $academicYear Academic year in YYYY-YYYY format
     * @param string|null $startDate Course start date
     * @param string|null $endDate Course end date
     * @return string|null Error message if validation fails, null if valid
     */
    private function validateAcademicYearDates($academicYear, $startDate, $endDate)
    {
        $today = date('Y-m-d');
        $currentYear = (int)date('Y');
        
        // Parse academic year (e.g., "2025-2026")
        if (!preg_match('/^([0-9]{4})\-([0-9]{4})$/', $academicYear, $matches)) {
            return null; // Invalid academic year format, skip validation
        }
        
        $startYear = (int)$matches[1];
        $endYear = (int)$matches[2];
        
        // VALIDATION 1: Academic year must be current year or future
        if ($startYear < $currentYear) {
            return "Academic year cannot be in the past. Please use {$currentYear}-" . ($currentYear + 1) . " or later.";
        }
        
        // Academic year must be consecutive (e.g., 2025-2026, not 2025-2027)
        if ($endYear != $startYear + 1) {
            return "Academic year must be consecutive years (e.g., {$startYear}-" . ($startYear + 1) . ").";
        }
        
        // VALIDATION 2: Start date must be today or later
        if ($startDate) {
            if ($startDate < $today) {
                return "Start date cannot be in the past. Please select today (" . date('M j, Y') . ") or a future date.";
            }
            
            // Start date must fall within the specified academic year
            $academicYearStart = $startYear . '-08-01'; // August 1 of start year
            $academicYearEnd = $endYear . '-06-30'; // June 30 of end year
            
            if ($startDate < $academicYearStart || $startDate > $academicYearEnd) {
                return "Start date must fall within academic year {$academicYear} (August 1, {$startYear} - June 30, {$endYear}).";
            }
        }
        
        // VALIDATION 3: End date validation with 220 class days maximum
        if ($startDate && $endDate) {
            $startTimestamp = strtotime($startDate);
            $endTimestamp = strtotime($endDate);
            
            // End date must be after start date
            if ($endTimestamp <= $startTimestamp) {
                return "End date must be after start date.";
            }
            
            // Calculate the maximum allowed end date (220 class days from start)
            // Class days = weekdays only, approximately 220 class days = ~44 weeks
            $maxClassDays = 220;
            $classDay = 0;
            $currentDate = $startTimestamp;
            
            while ($classDay < $maxClassDays) {
                $currentDate = strtotime('+1 day', $currentDate);
                $dayOfWeek = date('N', $currentDate); // 1 (Monday) through 7 (Sunday)
                
                // Count only weekdays (Monday-Friday)
                if ($dayOfWeek <= 5) {
                    $classDay++;
                }
            }
            
            if ($endTimestamp > $currentDate) {
                $maxEndDate = date('M j, Y', $currentDate);
                $daysDiff = round(($endTimestamp - $startTimestamp) / (60 * 60 * 24));
                return "End date exceeds the maximum of 220 class days from start date. Maximum allowed end date is {$maxEndDate}. Your selected range spans {$daysDiff} calendar days.";
            }
            
            // End date must also fall within the academic year
            $academicYearEnd = strtotime($endYear . '-06-30');
            if ($endTimestamp > $academicYearEnd) {
                return "End date must fall within academic year {$academicYear} (before June 30, {$endYear}).";
            }
        }
        
        return null;
    }    /**
     * Helper method to get year level ID from year level name
     * Uses YearLevelModel to fetch from database
     * 
     * @param string $yearLevelName Year level name (e.g., "1st Year", "2nd Year")
     * @return int|null Year level ID or null if not found
     */
    public function getYearLevelId($yearLevelName)
    {
        try {
            // Query the database using YearLevelModel
            $yearLevel = $this->yearLevelModel
                ->where('year_level_name', $yearLevelName)
                ->first();
            
            return $yearLevel ? $yearLevel['id'] : null;
        } catch (\Exception $e) {
            log_message('error', 'Failed to get year level ID: ' . $e->getMessage());
            
            // Fallback to hardcoded map if database query fails
            $yearLevelMap = [
                '1st Year' => 1,
                '2nd Year' => 2,
                '3rd Year' => 3,
                '4th Year' => 4
            ];
            
            return $yearLevelMap[$yearLevelName] ?? null;
        }
    }

}
