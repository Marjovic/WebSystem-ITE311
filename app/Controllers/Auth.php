<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\StudentModel;
use App\Models\YearLevelModel;
use App\Models\CourseModel;
use App\Models\CourseInstructorModel;
use App\Models\EnrollmentModel;
use App\Models\NotificationModel;
use App\Models\RoleModel;
use App\Models\EmailVerificationModel;
use App\Models\OtpModel;

// Auth Controller class - This handles all user authentication (login, register, logout, dashboard)
// This is the unified controller that manages all user account operations and role-based dashboards
class Auth extends BaseController
{    // These are class properties (variables that belong to this class)
    protected $session;             // This stores user session data (remembers who is logged in)
    protected $validation;          // This checks if form data is correct (validates user input)
    protected $db;                  // This connects to the database (where user data is stored)
      // Models for database operations
    protected $userModel;           // UserModel for all user database operations
    protected $studentModel;        // StudentModel for student-specific data
    protected $yearLevelModel;      // YearLevelModel for year level operations
    protected $courseModel;         // CourseModel for course operations
    protected $courseInstructorModel; // CourseInstructorModel for instructor assignments
    protected $enrollmentModel;     // EnrollmentModel for student enrollments    protected $notificationModel;   // NotificationModel for user notifications
    protected $roleModel;           // RoleModel for role management
    protected $emailVerificationModel; // EmailVerificationModel for email verification
    protected $otpModel;            // OtpModel for OTP/2FA authentication

    // Constructor - This runs automatically when we create Auth object
    // This sets up all the tools we need for authentication to work
    public function __construct()
    {
        // Get session service - this tracks who is logged in across pages
        // Session remembers user information even when they go to different pages
        $this->session = \Config\Services::session();
        
        // Get validation service - this checks if user fills forms correctly
        // Validation makes sure emails are valid, passwords are strong, etc.
        $this->validation = \Config\Services::validation();
        
        // Connect to database - this lets us save and find user accounts
        // Database is where all user information is permanently stored
        $this->db = \Config\Database::connect();          // Initialize all models for database operations
        $this->userModel = new UserModel();
        $this->studentModel = new StudentModel();
        $this->yearLevelModel = new YearLevelModel();
        $this->courseModel = new CourseModel();
        $this->courseInstructorModel = new CourseInstructorModel();
        $this->enrollmentModel = new EnrollmentModel();        $this->notificationModel = new NotificationModel();
        $this->roleModel = new RoleModel();
        $this->emailVerificationModel = new EmailVerificationModel();
        $this->otpModel = new OtpModel();
    }// Register Method - This handles user sign-up (creating new accounts)
    // This function shows the registration form and processes new user accounts
    // Steps: 1) Check if already logged in 2) Validate form data 3) Save to database 4) Redirect
    public function register()
    {        // Step 1: Check if user is already logged in
        // If someone is already logged in, they don't need to register again
        if ($this->session->get('isLoggedIn') === true) {
            // Send them to their role-specific dashboard instead of registration page
            $userRole = $this->session->get('role');
            return redirect()->to(base_url($userRole . '/dashboard'));
        }

        // Step 2: Check if the registration form was submitted
        // This happens when user fills the form and clicks "Register" button
        if ($this->request->getMethod() === 'POST') {            // Step 2a: Set validation rules - these are the requirements for each form field
            // Each rule tells the system what to check for in the user's input
            $rules = [
                'first_name'       => 'required|min_length[2]|max_length[100]|regex_match[/^[a-zA-ZñÑ\s]+$/]',  // First name is required
                'middle_name'      => 'required|min_length[1]|max_length[100]|regex_match[/^[a-zA-ZñÑ\s]+$/]',  // Middle name is required
                'last_name'        => 'required|min_length[2]|max_length[100]|regex_match[/^[a-zA-ZñÑ\s]+$/]',  // Last name is required
                'email'            => 'required|valid_email|is_unique[users.email]|regex_match[/^[a-zA-Z0-9._]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/]',
                'password'         => 'required|min_length[6]',                 // Password must exist and be at least 6 characters
                'password_confirm' => 'required|matches[password]',             // Password confirmation must match the password
                'year_level_id'    => 'required|integer'  // Year level ID is required for students
            ];// Step 2b: Set error messages - these are shown to user if validation fails
            // Each message explains what went wrong in simple language
            $messages = [                
                'first_name' => [
                    'required'     => 'First name is required.',
                    'min_length'   => 'First name must be at least 2 characters long.',
                    'max_length'   => 'First name cannot exceed 100 characters.',
                    'regex_match'  => 'First name can only contain letters (including ñ/Ñ) and spaces.'
                ],
                'middle_name' => [
                    'required'     => 'Middle name is required.',
                    'min_length'   => 'Middle name must be at least 1 character long.',
                    'max_length'   => 'Middle name cannot exceed 100 characters.',
                    'regex_match'  => 'Middle name can only contain letters (including ñ/Ñ) and spaces.'
                ],
                'last_name' => [
                    'required'     => 'Last name is required.',
                    'min_length'   => 'Last name must be at least 2 characters long.',
                    'max_length'   => 'Last name cannot exceed 100 characters.',
                    'regex_match'  => 'Last name can only contain letters (including ñ/Ñ) and spaces.'
                ],
                'email' => [
                    'required'    => 'Email is required.',
                    'valid_email' => 'Please enter a valid email address.',
                    'is_unique'   => 'This email is already registered.',
                    'regex_match'  => 'Invalid email! Email should be like "marjovic_alejado@lms.com".'
                ],                
                'password' => [
                    'required'   => 'Password is required.',
                    'min_length' => 'Password must be at least 6 characters long.'
                ],
                'password_confirm' => [
                    'required' => 'Password confirmation is required.',
                    'matches'  => 'Password confirmation does not match.'
                ],
                'year_level_id' => [
                    'required' => 'Year level is required.',
                    'integer'  => 'Please select a valid year level.'
                ]
            ];            
            // Step 2c: Check if all validation rules pass
            // This tests all the rules against what the user typed in the form
            if ($this->validate(rules: $rules, messages: $messages)) {
                
                // Start database transaction for atomic operation
                $this->db->transStart();
                
                try {
                    // Step 3: Get name fields directly from form
                    $firstName = trim($this->request->getPost(index: 'first_name'));
                    $middleName = trim($this->request->getPost(index: 'middle_name'));
                    $lastName = trim($this->request->getPost(index: 'last_name'));
                    
                    // Step 4: Get student role ID from roles table
                    $studentRole = $this->roleModel->where('role_name', 'Student')->first();
                    if (!$studentRole) {
                        throw new \Exception('Student role not found in database');
                    }
                    
                    // Step 5: Get year level ID from form
                    $yearLevelId = $this->request->getPost(index: 'year_level_id');
                    if (!$yearLevelId) {
                        throw new \Exception('Invalid year level selected');
                    }
                    
                    // Step 6: Prepare user data to save in users table
                    $userData = [
                        'user_code'   => 'STU' . date('Ymd') . rand(1000, 9999), // Auto-generate user code
                        'first_name'  => $firstName,
                        'middle_name' => $middleName,
                        'last_name'   => $lastName,
                        'suffix'      => null,
                        'email'       => $this->request->getPost(index: 'email'),
                        'password'    => $this->request->getPost(index: 'password'), // Will be hashed by UserModel callback
                        'role_id'     => $studentRole['id'],
                        'is_active'   => 1
                    ];

                    // Step 7: Insert user data using UserModel (handles password hashing automatically)
                    $userId = $this->userModel->insert($userData);
                    
                    if (!$userId) {
                        // Get validation errors from UserModel
                        $errors = $this->userModel->errors();
                        $errorMessage = !empty($errors) ? implode(', ', $errors) : 'Failed to create user account';
                        throw new \Exception($errorMessage);
                    }
                    
                    // Step 8: Prepare student data to save in students table
                    $studentData = [
                        'user_id'           => $userId,
                        'student_id_number' => date('Y') . '-' . str_pad($userId, 5, '0', STR_PAD_LEFT), // Auto-generate student ID
                        'year_level_id'     => $yearLevelId,
                        'enrollment_date'   => date('Y-m-d'),
                        'enrollment_status' => 'enrolled',
                        'department_id'     => null, // Will be set later when student selects program
                        'section'           => null, // Will be assigned by admin
                        'guardian_name'     => null, // Can be updated in profile
                        'guardian_contact'  => null, // Can be updated in profile
                        'scholarship_status' => null,
                        'total_units'       => 0
                    ];
                    
                    // Step 9: Insert student data using StudentModel
                    $studentId = $this->studentModel->insert($studentData);
                    
                    if (!$studentId) {
                        // Get validation errors from StudentModel
                        $errors = $this->studentModel->errors();
                        $errorMessage = !empty($errors) ? implode(', ', $errors) : 'Failed to create student record';
                        throw new \Exception($errorMessage);
                    }                    
                    // Step 10: Complete transaction
                    $this->db->transComplete();
                    
                    // Check transaction status
                    if ($this->db->transStatus() === false) {
                        throw new \Exception('Transaction failed during commit');
                    }
                    
                    // Step 11: Create email verification token
                    $verification = $this->emailVerificationModel->createVerification($userId, $userData['email']);
                    
                    if (!$verification) {
                        throw new \Exception('Failed to create email verification token');
                    }
                    
                    // Step 12: Send verification email
                    $verificationLink = base_url('verify-email/' . $verification['verification_token']);
                    $emailSent = $this->sendVerificationEmail(
                        $userData['email'],
                        $firstName . ' ' . $lastName,
                        $verificationLink,
                        $studentData['student_id_number']
                    );
                    
                    if (!$emailSent) {
                        log_message('warning', "Verification email failed to send to: {$userData['email']}");
                    }
                    
                    // Step 13: Log successful registration
                    log_message('info', "New student registered: User ID {$userId}, Student ID {$studentId}, Email: {$userData['email']}");
                    
                    // Step 14: Success - Student account was created successfully
                    $this->session->setFlashdata(data: 'success', value: 'Registration successful! Please check your email to verify your account before logging in.');
                    return redirect()->to(uri: base_url(relativePath: 'login'));
                    
                } catch (\Exception $e) {
                    // Rollback transaction on error
                    $this->db->transRollback();
                    
                    // Log the error with details
                    log_message('error', 'Registration failed: ' . $e->getMessage());
                    log_message('error', 'Stack trace: ' . $e->getTraceAsString());
                    
                    // Show error message to user
                    $this->session->setFlashdata(data: 'error', value: 'Registration failed: ' . $e->getMessage());
                }} else {
                // Validation failed: User input didn't meet the requirements
                // Show all validation error messages to help user fix their input
                $this->session->setFlashdata(data: 'errors', value: $this->validation->getErrors());
            }
        }        // Step 4: Show the registration form page with year levels
        // This runs when user first visits registration page OR if there were errors
        // Get all year levels from database to populate dropdown
        $data['yearLevels'] = $this->yearLevelModel->getAllOrdered();
        return view(name: 'auth/register', data: $data);
    }    // Login Method - This handles user sign-in with 2FA OTP verification
    // This function shows login form and processes user login attempts with OTP
    // Steps: 1) Check if already logged in 2) Validate login form 3) Send OTP 4) Verify OTP 5) Create session
    public function login()
    {        
        // Step 1: Check if user is already logged in
        // If someone is already logged in, send them to their role-specific dashboard
        if ($this->session->get('isLoggedIn') === true) {
            $userRole = $this->session->get('role');
            return redirect()->to(base_url($userRole . '/dashboard'));
        }

        // Step 2: Check if login form was submitted
        // This happens when user enters email/password and clicks "Login" button
        if ($this->request->getMethod() === 'POST') {
            
            // Step 2a: Set validation rules for login form
            // Login form requires email and password
            $rules = [
                'email'    => 'required|valid_email',
                'password' => 'required'
            ];

            // Step 2b: Set error messages for login validation
            $messages = [
                'email' => [
                    'required'    => 'Email is required.',
                    'valid_email' => 'Please enter a valid email address.'
                ],
                'password' => [
                    'required' => 'Password is required.'
                ]
            ];            
            
            // Step 2c: Check if validation passes
            if ($this->validate(rules: $rules, messages: $messages)) {
                // Get the email and password that user typed in the form
                $email = $this->request->getPost(index: 'email');
                $password = $this->request->getPost(index: 'password');                
                
                // Step 3: Use UserModel to verify credentials
                $user = $this->userModel->verifyCredentials($email, $password);

                // Step 3b: Check if user exists and password is correct
                if ($user) {
                    // Step 3c: Check if email is verified
                    if (empty($user['email_verified_at'])) {
                        $this->session->setFlashdata('error', 'Please verify your email address before logging in. Check your inbox for the verification link.');
                        $this->session->setFlashdata('show_resend', true);
                        $this->session->setFlashdata('user_email', $email);
                        return redirect()->to(base_url('login'));
                    }
                    
                    // Step 4: Generate and send OTP for 2FA
                    $otpData = $this->otpModel->createOTP($user['id'], $email, 'login');
                    
                    if ($otpData) {
                        // Send OTP via email
                        $emailSent = $this->sendOTPEmail($email, $user['first_name'] . ' ' . $user['last_name'], $otpData['otp_code']);
                        
                        if ($emailSent) {
                            // Store user data temporarily in session for OTP verification
                            $this->session->setTempdata('otp_user_id', $user['id'], 600); // 10 minutes
                            $this->session->setTempdata('otp_email', $email, 600);
                            $this->session->setTempdata('otp_user_data', $user, 600);
                            
                            $this->session->setFlashdata('success', 'OTP has been sent to your email. Please check your inbox.');
                            return redirect()->to(base_url('verify-otp'));
                        } else {
                            $this->session->setFlashdata('error', 'Failed to send OTP email. Please try again.');
                        }
                    } else {
                        $this->session->setFlashdata('error', 'Please wait before requesting another OTP.');
                    }
                    
                } else {
                    // Step 3d: Login failed - either email doesn't exist or password is wrong
                    $this->session->setFlashdata(data: 'error', value: 'Invalid email or password.');
                }
            } else {
                // Step 2d: Validation failed - email format wrong or missing fields
                $this->session->setFlashdata(data: 'errors', value: $this->validation->getErrors());
            }
        }

        // Step 5: Show the login form page
        return view(name: 'auth/login');
    }

    // Logout Method - This handles user sign-out (ending their session)
    // This function logs user out and sends them back to login page
    // Steps: 1) Destroy session data 2) Show logout message 3) Redirect to login
    public function logout()
    {
        // Step 1: Destroy the current session - forget all user login information
        // This completely logs the user out and clears all their session data
        $this->session->destroy();
        
        // Step 2: Show logout success message to confirm user was logged out
        $this->session->setFlashdata(data: 'success', value: 'You have been logged out successfully.');
        
        // Step 3: Send user back to login page so they can log in again if needed
        return redirect()->to(uri: base_url(relativePath: 'login'));    }
    
    // Dashboard Method - This shows unified dashboard based on user role    // This is the main dashboard that handles all user types in one place    // Only accessible to users who are logged in
    // Now includes Manage Users functionality for Admin users
    public function dashboard()
    {
        // Step 1: Check if user is logged in first
        // If not logged in, they can't access any dashboard
        if ($this->session->get('isLoggedIn') !== true) {
            // Show error message and send to login page
            $this->session->setFlashdata(data: 'error', value: 'Please login to access the dashboard.');
            return redirect()->to(uri: base_url(relativePath: 'login'));
        }

        // Step 2: Get user role from session and current URI
        $userRole = $this->session->get(key: 'role');
        $currentUri = uri_string();
        
        // Step 3: Check if user is accessing the correct role-based dashboard URL
        // If user accesses /dashboard, redirect them to their role-specific URL
        if ($currentUri === 'dashboard') {
            return redirect()->to(base_url($userRole . '/dashboard'));
        }
        
        // Step 4: Validate that user is accessing their own role dashboard
        $expectedUri = $userRole . '/dashboard';
        if ($currentUri !== $expectedUri) {
            $this->session->setFlashdata('error', 'Access denied. You can only access your own dashboard.');
            return redirect()->to(base_url($userRole . '/dashboard'));
        }
        
        // Step 5: Prepare basic user data that all roles need
        $baseData = [
            'user' => [
                'userID' => $this->session->get(key: 'userID'), // User's ID number
                'name'   => $this->session->get(key: 'name'),   // User's full name
                'email'  => $this->session->get(key: 'email'), // User's email address
                'role'   => $this->session->get(key: 'role')   // User's role
            ]
        ];        // Step 6: Get role-specific data and determine view based on user type
        switch ($userRole) {                case 'admin':
                // Admin gets system statistics and user management data using UserModel
                $totalUsers = $this->userModel->countAll();
                
                // Get role IDs for counting
                $adminRole = $this->roleModel->where('role_name', 'Admin')->first();
                $teacherRole = $this->roleModel->where('role_name', 'Teacher')->first();
                $studentRole = $this->roleModel->where('role_name', 'Student')->first();
                
                $totalAdmins = $adminRole ? $this->userModel->where('role_id', $adminRole['id'])->countAllResults(false) : 0;
                $totalTeachers = $teacherRole ? $this->userModel->where('role_id', $teacherRole['id'])->countAllResults(false) : 0;
                $totalStudents = $studentRole ? $this->userModel->where('role_id', $studentRole['id'])->countAllResults(false) : 0;
                  // Get course statistics for admin using CourseModel
                $totalCourses = $this->courseModel->countAll();
                $activeCourses = $this->courseModel->where('is_active', 1)->countAllResults(false);
                $draftCourses = 0; // No draft status in current schema
                $completedCourses = 0; // No completed status in current schema
                
                // Get recent users with more detailed information for activity feed using UserModel
                $recentUsers = $this->userModel
                    ->select('users.id, users.first_name, users.last_name, users.email, users.created_at, users.updated_at, roles.role_name')
                    ->join('roles', 'roles.id = users.role_id', 'left')
                    ->orderBy('users.created_at', 'DESC')
                    ->limit(10)
                    ->findAll();                // Prepare recent activities for display
                $recentActivities = [];
                foreach ($recentUsers as $user) {
                    // Add user registration activity
                    $userName = esc($user['first_name'] . ' ' . $user['last_name']);
                    $roleName = $user['role_name'] ?? 'User';
                    
                    $recentActivities[] = [
                        'type' => 'user_registration',
                        'icon' => '👤',
                        'title' => 'New User Registration',
                        'description' => $userName . ' (' . ucfirst($roleName) . ') joined the system',
                        'time' => $user['created_at'],
                        'user_name' => $userName,
                        'user_role' => $roleName
                    ];
                }
                  // Add admin-managed activities from session to recent activities
                $creationActivities = $this->session->get('creation_activities') ?? [];
                $updateActivities = $this->session->get('update_activities') ?? [];
                $deletionActivities = $this->session->get('deletion_activities') ?? [];
                $assignmentActivities = $this->session->get('assignment_activities') ?? [];
                
                // Merge all admin activities
                $adminActivities = array_merge($creationActivities, $updateActivities, $deletionActivities, $assignmentActivities);
                
                // Add admin activities to recent activities
                foreach ($adminActivities as $adminActivity) {
                    $recentActivities[] = $adminActivity;
                }
                
                // Sort activities by time (most recent first) and limit to 8
                usort($recentActivities, function($a, $b) {
                    return strtotime($b['time']) - strtotime($a['time']);
                });
                $recentActivities = array_slice($recentActivities, 0, 8);                $dashboardData = array_merge($baseData, [
                    'title' => 'Admin Dashboard - MGOD LMS',
                    'totalUsers' => $totalUsers,
                    'totalAdmins' => $totalAdmins,
                    'totalTeachers' => $totalTeachers,
                    'totalStudents' => $totalStudents,
                    'totalCourses' => $totalCourses,
                    'activeCourses' => $activeCourses,
                    'draftCourses' => $draftCourses,
                    'completedCourses' => $completedCourses,
                    'recentUsers' => $recentUsers,
                    'recentActivities' => $recentActivities
                ]);
                return view('auth/dashboard', $dashboardData);            case 'teacher':
                // Teacher gets course and student data using CourseModel
                $teacherID = $this->session->get('userID');
                
                try {                    // Get all courses using CourseModel and filter by teacher manually
                    $allCourses = $this->courseModel
                        ->select('id, title, instructor_ids, is_active')
                        ->findAll();
                    
                    // Count courses where teacher is in instructor_ids
                    $teacherCourses = 0;
                    $activeCourses = 0;
                    
                    foreach ($allCourses as $course) {
                        $instructorIds = json_decode($course['instructor_ids'], true);
                        if (is_array($instructorIds) && in_array($teacherID, $instructorIds)) {
                            $teacherCourses++;
                            if ($course['is_active'] == 1) {
                                $activeCourses++;
                            }
                        }
                    }
                      // Debug log
                    log_message('debug', "Teacher Dashboard - Teacher ID: {$teacherID}, Courses Count: {$teacherCourses}, Active: {$activeCourses}");
                      // Count available courses (active courses where teacher is not assigned)
                    $availableCoursesCount = 0;
                    foreach ($allCourses as $course) {
                        if ($course['is_active'] == 1) {
                            $instructorIds = json_decode($course['instructor_ids'], true);
                            // Course is available if no instructors or teacher not in list
                            if (empty($instructorIds) || !in_array($teacherID, $instructorIds)) {
                                $availableCoursesCount++;
                            }
                        }
                    }
                    
                    // Get course IDs where teacher is assigned
                    $teacherCourseIds = [];
                    foreach ($allCourses as $course) {
                        $instructorIds = json_decode($course['instructor_ids'], true);
                        if (is_array($instructorIds) && in_array($teacherID, $instructorIds)) {
                            $teacherCourseIds[] = $course['id'];
                        }
                    }
                      // Get total students enrolled in teacher's courses using EnrollmentModel
                    $totalStudents = 0;
                    if (!empty($teacherCourseIds)) {
                        $totalStudents = $this->enrollmentModel
                            ->select('COUNT(DISTINCT user_id) as total')
                            ->whereIn('course_id', $teacherCourseIds)
                            ->get()
                            ->getRow()
                            ->total ?? 0;
                    }
                    
                    // Debug log
                    log_message('debug', "Teacher Dashboard - Total Students: {$totalStudents}");
                    log_message('debug', "Teacher Dashboard - Data being passed: Courses={$teacherCourses}, Active={$activeCourses}, Students={$totalStudents}");
                    
                    // Get assignment activities from session for recent activity display
                    $assignmentActivities = $this->session->get('assignment_activities') ?? [];
                    
                    $dashboardData = array_merge($baseData, [
                        'title' => 'Teacher Dashboard - MGOD LMS',
                        'totalCourses' => $teacherCourses,
                        'activeCourses' => $activeCourses,
                        'availableCoursesCount' => $availableCoursesCount,
                        'totalStudents' => $totalStudents,
                        'pendingAssignments' => 0, // Placeholder - implement when assignments table exists
                        'averageGrade' => 0,        // Placeholder - implement when grades table exists
                        'assignment_activities' => $assignmentActivities
                    ]);
                    
                    return view('auth/dashboard', $dashboardData);
                      } catch (\Exception $e) {
                    // If there's a database error, show a simplified teacher dashboard
                    log_message('error', 'Teacher dashboard error: ' . $e->getMessage());
                    
                    $dashboardData = array_merge($baseData, [
                        'title' => 'Teacher Dashboard - MGOD LMS',
                        'totalCourses' => 0,
                        'activeCourses' => 0,
                        'availableCoursesCount' => 0,
                        'totalStudents' => 0,
                        'pendingAssignments' => 0,
                        'averageGrade' => 0,
                        'assignment_activities' => []
                    ]);
                    
                    return view('auth/dashboard', $dashboardData);
                }
                  case 'student':
                // Student gets enrollment and course data using EnrollmentModel and CourseModel
                $userID = $this->session->get('userID');
                
                // Get enrolled courses for this student using EnrollmentModel
                $enrolledCourses = $this->enrollmentModel->getUserEnrollments($userID);
                $enrolledCoursesCount = count($enrolledCourses);
                    // Get available courses that student can enroll in using CourseModel
                $availableCourses = $this->courseModel
                    ->select('courses.*')
                    ->where('courses.is_active', 1)
                    ->orderBy('courses.created_at', 'DESC')
                    ->findAll();
                
                // Get instructor names for each course using UserModel
                foreach ($availableCourses as &$course) {
                    $instructorIds = json_decode($course['instructor_ids'] ?? '[]', true);
                    if (!empty($instructorIds)) {
                        $instructorNames = $this->userModel
                            ->select('name')
                            ->whereIn('id', $instructorIds)
                            ->where('role', 'teacher')
                            ->findAll();
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
                  // Format course data for display
                foreach ($enrolledCourses as &$course) {
                    $course['progress'] = 0; // Default progress is 0% (no progress yet)
                }
                
                foreach ($availableCoursesFiltered as &$course) {
                    $course['start_date_formatted'] = $course['start_date'] ? date('M j, Y', strtotime($course['start_date'])) : 'TBA';
                    $course['end_date_formatted'] = $course['end_date'] ? date('M j, Y', strtotime($course['end_date'])) : 'TBA';
                }
                
                $dashboardData = array_merge($baseData, [
                    'title' => 'Student Dashboard - MGOD LMS',
                    'enrolledCourses' => $enrolledCoursesCount,
                    'enrolledCoursesData' => $enrolledCourses,
                    'availableCoursesData' => array_values($availableCoursesFiltered),
                    'completedAssignments' => 0, // Placeholder - implement when assignments table exists                'pendingAssignments' => 0    // Placeholder - implement when assignments table exists
                ]);                return view('auth/dashboard', $dashboardData);
                
            default:                
                // If role is unknown, show generic dashboard
                return view('auth/dashboard', $baseData);
        }
    }

    /**
     * Send verification email to user
     *
     * @param string $email User's email address
     * @param string $userName User's full name
     * @param string $verificationLink Verification link
     * @param string $studentId Student ID number
     * @return bool True if email sent successfully
     */
    protected function sendVerificationEmail(string $email, string $userName, string $verificationLink, string $studentId): bool
    {
        try {
            $emailService = \Config\Services::email();
            
            // Prepare email data for view
            $emailData = [
                'userName'          => $userName,
                'userEmail'         => $email,
                'verificationLink'  => $verificationLink,
                'studentId'         => $studentId
            ];
            
            // Load HTML email template
            $message = view('emails/verify_email', $emailData);
            
            // Set email parameters
            $emailService->setTo($email);
            $emailService->setSubject('Verify Your Email - MGOD LMS Account Activation');
            $emailService->setMessage($message);
            
            // Send email
            if ($emailService->send()) {
                log_message('info', "Verification email sent successfully to: {$email}");
                return true;
            } else {
                log_message('error', "Failed to send verification email to: {$email}");
                log_message('error', $emailService->printDebugger(['headers']));
                return false;
            }
        } catch (\Exception $e) {
            log_message('error', "Exception sending verification email: " . $e->getMessage());
            return false;
        }
    }    /**
     * Verify Email - This handles email verification via token
     *
     * @param string|null $token Verification token from email link
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    public function verifyEmail(?string $token = null)
    {
        // Check if token is provided
        if (!$token) {
            $this->session->setFlashdata('error', 'Invalid verification link. No token provided.');
            return redirect()->to(base_url('login'));
        }

        // Verify the token using EmailVerificationModel
        $result = $this->emailVerificationModel->verifyToken($token);

        if ($result['success']) {
            // Mark user's email as verified in users table
            $this->userModel->markEmailAsVerified($result['user_id']);

            // Log successful verification
            log_message('info', "Email verified successfully for User ID: {$result['user_id']}, Email: {$result['email']}");

            // Set success message
            $this->session->setFlashdata('success', 'Email verified successfully! You can now log in to your account.');
            
            // Redirect to login page
            return redirect()->to(base_url('login'));
        } else {
            // Verification failed
            log_message('warning', "Email verification failed: {$result['message']}");

            // Set error message
            $this->session->setFlashdata('error', $result['message']);

            // If token expired, offer resend option
            if (isset($result['expired']) && $result['expired']) {
                $this->session->setFlashdata('show_resend', true);
            }

            return redirect()->to(base_url('login'));
        }
    }

    /**
     * Resend Verification Email
     *
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    public function resendVerification()
    {
        // Check if user is logged in (they might be trying to verify after login attempt)
        $email = $this->request->getPost('email');

        if (!$email) {
            $this->session->setFlashdata('error', 'Please provide your email address.');
            return redirect()->to(base_url('login'));
        }

        // Find user by email
        $user = $this->userModel->getUserByEmail($email);

        if (!$user) {
            $this->session->setFlashdata('error', 'No account found with this email address.');
            return redirect()->to(base_url('login'));
        }

        // Check if already verified
        if ($this->userModel->isEmailVerified($user['id'])) {
            $this->session->setFlashdata('info', 'Your email is already verified. You can log in now.');
            return redirect()->to(base_url('login'));
        }

        // Get student data for the email
        $student = $this->studentModel->where('user_id', $user['id'])->first();
        $studentId = $student ? $student['student_id_number'] : 'N/A';

        // Create new verification token
        $verification = $this->emailVerificationModel->resendVerification($user['id'], $email);

        if (!$verification) {
            $this->session->setFlashdata('error', 'Failed to generate verification link. Please try again.');
            return redirect()->to(base_url('login'));
        }

        // Send new verification email
        $verificationLink = base_url('verify-email/' . $verification['verification_token']);
        $userName = trim($user['first_name'] . ' ' . $user['last_name']);
        
        $emailSent = $this->sendVerificationEmail($email, $userName, $verificationLink, $studentId);

        if ($emailSent) {
            $this->session->setFlashdata('success', 'Verification email sent! Please check your inbox.');
        } else {
            $this->session->setFlashdata('error', 'Failed to send verification email. Please contact support.');
        }

        return redirect()->to(base_url('login'));
    }

    /**
     * Send OTP Email - Sends OTP code to user's email for 2FA
     *
     * @param string $email User's email address
     * @param string $userName User's full name
     * @param string $otpCode OTP code
     * @return bool True if email sent successfully
     */
    protected function sendOTPEmail(string $email, string $userName, string $otpCode): bool
    {
        try {
            $emailService = \Config\Services::email();
            
            // Prepare email data for view
            $emailData = [
                'userName' => $userName,
                'otpCode'  => $otpCode,
                'expiryMinutes' => 10
            ];
            
            // Load HTML email template
            $message = view('emails/otp_email', $emailData);
            
            // Set email parameters
            $emailService->setTo($email);
            $emailService->setSubject('Your Login OTP Code - MGOD LMS');
            $emailService->setMessage($message);
            
            // Send email
            if ($emailService->send()) {
                log_message('info', "OTP email sent successfully to: {$email}");
                return true;
            } else {
                log_message('error', "Failed to send OTP email to: {$email}. Error: " . $emailService->printDebugger(['headers']));
                return false;
            }
        } catch (\Exception $e) {
            log_message('error', "Exception sending OTP email: " . $e->getMessage());
            return false;
        }
    }    /**
     * Verify OTP - This handles OTP verification for 2FA login
     *
     * @return \CodeIgniter\HTTP\RedirectResponse|string
     */
    public function verifyOtp()
    {
        // Check if user has a pending OTP session
        $userId = $this->session->getTempdata('otp_user_id');
        $email = $this->session->getTempdata('otp_email');
        $userData = $this->session->getTempdata('otp_user_data');

        if (!$userId || !$email || !$userData) {
            $this->session->setFlashdata('error', 'OTP session expired. Please login again.');
            return redirect()->to(base_url('login'));
        }

        // Handle OTP submission
        if ($this->request->getMethod() === 'POST') {
            $otpCode = $this->request->getPost('otp_code');

            if (!$otpCode) {
                $this->session->setFlashdata('error', 'Please enter the OTP code.');
                return redirect()->to(base_url('verify-otp'));
            }            // Verify OTP
            $result = $this->otpModel->verifyOTP($email, $otpCode, 'login');

            if ($result['success']) {
                // Ensure role_id exists in userData, otherwise fetch fresh user data
                if (!isset($userData['role_id'])) {
                    $freshUser = $this->userModel->find($userData['id']);
                    if ($freshUser) {
                        $userData = $freshUser;
                    }
                }
                
                // Get role name from role_id
                $role = $this->roleModel->find($userData['role_id']);
                $roleName = $role ? strtolower($role['role_name']) : 'student';
                
                // OTP verified - create session and log user in
                $sessionData = [
                    'userID'     => $userData['id'],
                    'name'       => $userData['first_name'] . ' ' . $userData['last_name'],
                    'email'      => $userData['email'],
                    'role'       => $roleName,
                    'isLoggedIn' => true
                ];

                $this->session->set($sessionData);
                
                // Clear temporary OTP session data
                $this->session->removeTempdata('otp_user_id');
                $this->session->removeTempdata('otp_email');
                $this->session->removeTempdata('otp_user_data');

                // Update last login time
                $this->userModel->update($userData['id'], ['last_login' => date('Y-m-d H:i:s')]);

                $this->session->setFlashdata('success', 'Welcome back, ' . $userData['first_name'] . '!');
                return redirect()->to(base_url($roleName . '/dashboard'));
            } else {
                // OTP verification failed
                $this->session->setFlashdata('error', $result['message']);
                return redirect()->to(base_url('verify-otp'));
            }
        }

        // Show OTP verification form
        $data = [
            'email' => $email,
            'userName' => $userData['first_name'] . ' ' . $userData['last_name']
        ];

        return view('auth/verify_otp', $data);
    }

    /**
     * Resend OTP - Resends OTP code to user's email
     *
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    public function resendOtp()
    {
        // Check if user has a pending OTP session
        $userId = $this->session->getTempdata('otp_user_id');
        $email = $this->session->getTempdata('otp_email');
        $userData = $this->session->getTempdata('otp_user_data');

        if (!$userId || !$email || !$userData) {
            $this->session->setFlashdata('error', 'OTP session expired. Please login again.');
            return redirect()->to(base_url('login'));
        }

        // Resend OTP
        $result = $this->otpModel->resendOTP($userId, $email, 'login');

        if ($result && isset($result['otp_code'])) {
            // Send new OTP via email
            $emailSent = $this->sendOTPEmail($email, $userData['first_name'] . ' ' . $userData['last_name'], $result['otp_code']);

            if ($emailSent) {
                // Refresh temp session data
                $this->session->setTempdata('otp_user_id', $userId, 600);
                $this->session->setTempdata('otp_email', $email, 600);
                $this->session->setTempdata('otp_user_data', $userData, 600);

                $this->session->setFlashdata('success', 'A new OTP has been sent to your email.');
            } else {
                $this->session->setFlashdata('error', 'Failed to send OTP email. Please try again.');
            }
        } else {
            $message = isset($result['message']) ? $result['message'] : 'Please wait before requesting another OTP.';
            $this->session->setFlashdata('error', $message);
        }

        return redirect()->to(base_url('verify-otp'));
    }
}