<?php

namespace App\Controllers;

// Auth Controller class - This handles all user authentication (login, register, logout, dashboard)
// This is the unified controller that manages all user account operations and role-based dashboards
class Auth extends BaseController
{
    // These are class properties (variables that belong to this class)
    protected $session;    // This stores user session data (remembers who is logged in)
    protected $validation; // This checks if form data is correct (validates user input)
    protected $db;         // This connects to the database (where user data is stored)

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
        $this->db = \Config\Database::connect();
    }

    // Register Method - This handles user sign-up (creating new accounts)
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
                'name'             => 'required|min_length[3]|max_length[100]|regex_match[/^[a-zA-ZñÑ\s]+$/]',  // Name must exist, be 3-100 characters, and only contain letters (including ñ/Ñ) and spaces
                'email'            => 'required|valid_email|is_unique[users.email]|regex_match[/^[a-zA-Z0-9._]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/]',
                'password'         => 'required|min_length[6]',                 // Password must exist and be at least 6 characters
                'password_confirm' => 'required|matches[password]',             // Password confirmation must match the password
                'year_level'       => 'required|in_list[1st Year,2nd Year,3rd Year,4th Year]'  // Year level is required for students
            ];// Step 2b: Set error messages - these are shown to user if validation fails
            // Each message explains what went wrong in simple language
            $messages = [                
                'name' => [
                    'required'     => 'Name is required.',                        // Show if user didn't enter name
                    'min_length'   => 'Name must be at least 3 characters long.', // Show if name is too short
                    'max_length'   => 'Name cannot exceed 100 characters.',       // Show if name is too long
                    'regex_match'  => 'Name can only contain letters (including ñ/Ñ) and spaces.' // Show if name contains numbers or symbols
                ],
                'email' => [
                    'required'    => 'Email is required.',                      // Show if user didn't enter email
                    'valid_email' => 'Please enter a valid email address.',     // Show if email format is wrong (no @ sign, etc.)
                    'is_unique'   => 'This email is already registered.',
                    'regex_match'  => 'Invalid email! Email should be like "marjovic_alejado@lms.com".'
                ],                'password' => [
                    'required'   => 'Password is required.',                    // Show if user didn't enter password
                    'min_length' => 'Password must be at least 6 characters long.' // Show if password is too short (less than 6 characters)
                ],
                'password_confirm' => [
                    'required' => 'Password confirmation is required.',         // Show if user didn't confirm password
                    'matches'  => 'Password confirmation does not match.'       // Show if password and confirmation are different
                ],
                'year_level' => [
                    'required' => 'Year level is required.',                    // Show if user didn't select year level
                    'in_list'  => 'Please select a valid year level.'           // Show if invalid year level selected
                ]
            ];

            // Step 2c: Check if all validation rules pass
            // This tests all the rules against what the user typed in the form
            if ($this->validate(rules: $rules, messages: $messages)) {
                
                // Step 3a: Make password secure using hash function
                // Hashing scrambles the password so hackers can't read it even if they steal the database
                // We never store plain text passwords - always hashed for security
                $hashedPassword = password_hash(password: $this->request->getPost(index: 'password'), algo: PASSWORD_DEFAULT);
                  // Step 3b: Prepare user data to save in database
                // Get all form data and organize it into an array for database storage
                $userData = [
                    'name'       => $this->request->getPost(index: 'name'),      // Get user's full name from form
                    'email'      => $this->request->getPost(index: 'email'),     // Get user's email address from form
                    'password'   => $hashedPassword,                      // Use the hashed (secure) version of password
                    'role'       => 'student',                            // Set default role as student (new users start as students)
                    'year_level' => $this->request->getPost(index: 'year_level'), // Get student's year level from form
                    'created_at' => date(format: 'Y-m-d H:i:s'),          // Record when account was created (current date/time)
                    'updated_at' => date(format: 'Y-m-d H:i:s')           // Record when account was last updated (same as created for new accounts)
                ];

                // Step 3c: Save user data to the users table in database
                // Get database table builder for 'users' table
                $builder = $this->db->table(tableName: 'users');
                
                // Step 3d: Try to insert the new user data and check if it worked
                if ($builder->insert(set: $userData)) {
                    // Success: User account was created successfully
                    // Show success message and send user to login page to sign in
                    $this->session->setFlashdata(data: 'success', value: 'Registration successful! Please login with your credentials.');
                    return redirect()->to(uri: base_url(relativePath: 'login'));
                } else {
                    // Failed: Something went wrong saving to database
                    // Show error message and let user try again
                    $this->session->setFlashdata(data: 'error', value: 'Registration failed. Please try again.');
                }
            } else {
                // Validation failed: User input didn't meet the requirements
                // Show all validation error messages to help user fix their input                $this->session->setFlashdata(data: 'errors', value: $this->validation->getErrors());
            }
        }

        // Step 4: Show the registration form page
        // This runs when user first visits registration page OR if there were errors
        return view(name: 'auth/register');
    }

    // Login Method - This handles user sign-in with unified dashboard redirect
    // This function shows login form and processes user login attempts
    // Steps: 1) Check if already logged in 2) Validate login form 3) Check password 4) Create session 5) Redirect to dashboard
    public function login()
    {        // Step 1: Check if user is already logged in
        // If someone is already logged in, send them to their role-specific dashboard
        if ($this->session->get('isLoggedIn') === true) {
            $userRole = $this->session->get('role');
            return redirect()->to(base_url($userRole . '/dashboard'));
        }

        // Step 2: Check if login form was submitted
        // This happens when user enters email/password and clicks "Login" button
        if ($this->request->getMethod() === 'POST') {
            
            // Step 2a: Set validation rules for login form
            // Login form is simpler than registration - only need email and password
            $rules = [
                'email'    => 'required|valid_email',  // Email must be provided and in correct format
                'password' => 'required'               // Password must be provided (we'll check if it's correct later)
            ];

            // Step 2b: Set error messages for login validation
            // These messages help user understand what went wrong
            $messages = [
                'email' => [
                    'required'    => 'Email is required.',                   // Show if user didn't enter email
                    'valid_email' => 'Please enter a valid email address.'   // Show if email format is wrong
                ],
                'password' => [
                    'required' => 'Password is required.'                    // Show if user didn't enter password
                ]
            ];

            // Step 2c: Check if validation passes
            // Make sure user provided valid email and some password
            if ($this->validate(rules: $rules, messages: $messages)) {
                // Get the email and password that user typed in the form
                $email = $this->request->getPost(index: 'email');
                $password = $this->request->getPost(index: 'password');

                // Step 3a: Look for user in database using email address
                // Search the users table to find someone with this email
                $builder = $this->db->table(tableName: 'users');
                $user = $builder->where(key: 'email', value: $email)->get()->getRowArray();

                // Step 3b: Check if user exists and password is correct
                // First check if we found a user, then verify the password matches
                if ($user && password_verify(password: $password, hash: $user['password'])) {
                    
                    // Step 4a: Login successful - create user session
                    // Session data remembers who is logged in across different pages
                    $sessionData = [
                        'userID'     => $user['id'],       // Store user's unique ID number
                        'name'       => $user['name'],     // Store user's full name
                        'email'      => $user['email'],    // Store user's email address
                        'role'       => $user['role'],     // Store user's role (admin, teacher, or student)
                        'isLoggedIn' => true               // Mark that user is successfully logged in
                    ];

                    // Save all session data - this remembers the user across pages
                    $this->session->set(data: $sessionData);                    // Step 4b: Show welcome message and redirect user to their role-specific dashboard
                    $this->session->setFlashdata(data: 'success', value: 'Welcome back, ' . $user['name'] . '!');
                    return redirect()->to(base_url($user['role'] . '/dashboard'));
                    
                } else {
                    // Step 3c: Login failed - either email doesn't exist or password is wrong
                    // Don't tell user which one is wrong (security - don't help hackers)
                    $this->session->setFlashdata(data: 'error', value: 'Invalid email or password.');
                }
            } else {
                // Step 2d: Validation failed - email format wrong or missing fields
                // Show all validation error messages to help user fix their input
                $this->session->setFlashdata(data: 'errors', value: $this->validation->getErrors());
            }
        }

        // Step 5: Show the login form page
        // This runs when user first visits login page OR if login failed
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
        ];

        // Step 6: Get role-specific data and determine view based on user type
        switch ($userRole) {              case 'admin':
                // Admin gets system statistics and user management data
                $totalUsers = $this->db->table('users')->countAll();
                $totalAdmins = $this->db->table('users')->where('role', 'admin')->countAllResults();
                $totalTeachers = $this->db->table('users')->where('role', 'teacher')->countAllResults();
                $totalStudents = $this->db->table('users')->where('role', 'student')->countAllResults();
                
                // Get course statistics for admin
                $totalCourses = $this->db->table('courses')->countAll();
                $activeCourses = $this->db->table('courses')->where('status', 'active')->countAllResults();
                $draftCourses = $this->db->table('courses')->where('status', 'draft')->countAllResults();
                $completedCourses = $this->db->table('courses')->where('status', 'completed')->countAllResults();
                
                // Get recent users with more detailed information for activity feed
                $recentUsers = $this->db->table('users')
                    ->select('id, name, email, role, created_at, updated_at')
                    ->orderBy('created_at', 'DESC')
                    ->limit(10)
                    ->get()
                    ->getResultArray();

                // Prepare recent activities for display
                $recentActivities = [];
                foreach ($recentUsers as $user) {
                    // Add user registration activity
                    $recentActivities[] = [
                        'type' => 'user_registration',
                        'icon' => '👤',
                        'title' => 'New User Registration',
                        'description' => esc($user['name']) . ' (' . ucfirst($user['role']) . ') joined the system',
                        'time' => $user['created_at'],
                        'user_name' => esc($user['name']),
                        'user_role' => $user['role']
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
                // Teacher gets course and student data 
                $teacherID = $this->session->get('userID');                try {                    // Get all courses and filter by teacher manually (compatible with all MySQL versions)
                    $allCourses = $this->db->table('courses')
                        ->select('id, title, instructor_ids, status')
                        ->get()
                        ->getResultArray();
                    
                    // Count courses where teacher is in instructor_ids
                    $teacherCourses = 0;
                    $activeCourses = 0;
                    
                    foreach ($allCourses as $course) {
                        $instructorIds = json_decode($course['instructor_ids'], true);
                        if (is_array($instructorIds) && in_array($teacherID, $instructorIds)) {
                            $teacherCourses++;
                            if ($course['status'] === 'active') {
                                $activeCourses++;
                            }
                        }
                    }
                      // Debug log
                    log_message('debug', "Teacher Dashboard - Teacher ID: {$teacherID}, Courses Count: {$teacherCourses}, Active: {$activeCourses}");
                    
                    // Count available courses (active courses where teacher is not assigned)
                    $availableCoursesCount = 0;
                    foreach ($allCourses as $course) {
                        if ($course['status'] === 'active') {
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
                    
                    // Get total students enrolled in teacher's courses
                    $totalStudents = 0;
                    if (!empty($teacherCourseIds)) {
                        $totalStudents = $this->db->table('enrollments')
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
                // Student gets enrollment and course data 
                $userID = $this->session->get('userID');
                
                // Initialize enrollment model to fetch student data
                $enrollmentModel = new \App\Models\EnrollmentModel();
                
                // Get enrolled courses for this student
                $enrolledCourses = $enrollmentModel->getUserEnrollments($userID);
                $enrolledCoursesCount = count($enrolledCourses);
                  // Get available courses that student can enroll in
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
                    'completedAssignments' => 0, // Placeholder - implement when assignments table exists
                    'pendingAssignments' => 0    // Placeholder - implement when assignments table exists
                ]);
                return view('auth/dashboard', $dashboardData);
                
            default:                
                // If role is unknown, show generic dashboard
                return view('auth/dashboard', $baseData);
        }
    }
      // Manage Users Method - Consolidated method for all user management operations
    // This handles create, edit, delete, and display operations in one place to avoid spaghetti code
    public function manageUsers()
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
        $userID = $this->request->getGet('id');
        $currentAdminID = $this->session->get('userID');
          // ===== CREATE USER =====
        if ($action === 'create' && $this->request->getMethod() === 'POST') {
            $rules = [
                'name'     => 'required|min_length[3]|max_length[100]|regex_match[/^[a-zA-ZñÑ\s]+$/]',
                'email'    => 'required|valid_email|is_unique[users.email]|regex_match[/^[a-zA-Z0-9._]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/]',
                'password' => 'required|min_length[6]',
                'role'     => 'required|in_list[admin,teacher,student]'
            ];
            
            // Add year_level validation if role is student
            $role = $this->request->getPost('role');
            if ($role === 'student') {
                $rules['year_level'] = 'required|in_list[1st Year,2nd Year,3rd Year,4th Year]';
            }

            $messages = [
                'name' => [
                    'required'    => 'Name is required.',
                    'min_length'  => 'Name must be at least 3 characters long.',
                    'max_length'  => 'Name cannot exceed 100 characters.',
                    'regex_match' => 'Name can only contain letters (including ñ/Ñ) and spaces.'
                ],
                'email' => [
                    'required'    => 'Email is required.',
                    'valid_email' => 'Please enter a valid email address.',
                    'is_unique'   => 'This email is already registered.',
                    'regex_match'  => 'Invalid email! Email should be like "marjovic_alejado@lms.com"'
                ],
                'password' => [
                    'required'   => 'Password is required.',
                    'min_length' => 'Password must be at least 6 characters long.'
                ],
                'role' => [
                    'required' => 'Role is required.',
                    'in_list'  => 'Invalid role selected.'
                ],
                'year_level' => [
                    'required' => 'Year level is required for students.',
                    'in_list'  => 'Please select a valid year level.'
                ]
            ];

            if ($this->validate($rules, $messages)) {
                $userData = [
                    'name'       => $this->request->getPost('name'),
                    'email'      => $this->request->getPost('email'),
                    'password'   => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
                    'role'       => $this->request->getPost('role'),
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                
                // Add year_level only if role is student
                if ($role === 'student') {
                    $userData['year_level'] = $this->request->getPost('year_level');
                }

                $builder = $this->db->table('users');
                if ($builder->insert($userData)) {
                    $creationActivity = [
                        'type' => 'user_creation',
                        'icon' => '➕',
                        'title' => 'New User Created',
                        'description' => esc($userData['name']) . ' (' . ucfirst($userData['role']) . ') account was created by admin',
                        'time' => date('Y-m-d H:i:s'),
                        'user_name' => esc($userData['name']),
                        'user_role' => $userData['role'],
                        'created_by' => $this->session->get('name')
                    ];

                    $creationActivities = $this->session->get('creation_activities') ?? [];
                    array_unshift($creationActivities, $creationActivity);
                    $creationActivities = array_slice($creationActivities, 0, 10);
                    $this->session->set('creation_activities', $creationActivities);

                    $this->session->setFlashdata('success', 'User created successfully!');
                    return redirect()->to(base_url('admin/manage_users'));
                } else {
                    $this->session->setFlashdata('error', 'Failed to create user. Please try again.');
                }
            } else {
                $this->session->setFlashdata('errors', $this->validation->getErrors());
            }
        }
        
        // ===== EDIT USER =====
        if ($action === 'edit' && $userID) {
            $builder = $this->db->table('users');
            $userToEdit = $builder->where('id', $userID)->get()->getRowArray();

            if (!$userToEdit) {
                $this->session->setFlashdata('error', 'User not found.');
                return redirect()->to(base_url('admin/manage_users'));
            }

            if ($userToEdit['id'] == $currentAdminID) {
                $this->session->setFlashdata('error', 'You cannot edit your own account.');
                return redirect()->to(base_url('admin/manage_users'));
            }            if ($this->request->getMethod() === 'POST') {
                $rules = [
                    'name' => 'required|min_length[3]|max_length[100]|regex_match[/^[a-zA-ZñÑ\s]+$/]',
                    'email' => "required|valid_email|is_unique[users.email,id,{$userID}]|regex_match[/^[a-zA-Z0-9._]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/]",
                    'role' => 'required|in_list[admin,teacher,student]'
                ];

                if ($this->request->getPost('password')) {
                    $rules['password'] = 'min_length[6]';
                }
                
                // Add year_level validation if role is student
                $role = $this->request->getPost('role');
                if ($role === 'student') {
                    $rules['year_level'] = 'required|in_list[1st Year,2nd Year,3rd Year,4th Year]';
                }   

                $messages = [
                    'name' => [
                        'required'    => 'Name is required.',
                        'min_length'  => 'Name must be at least 3 characters long.',
                        'max_length'  => 'Name cannot exceed 100 characters.',
                        'regex_match' => 'Name can only contain letters (including ñ/Ñ) and spaces.'
                    ],
                    'email' => [
                        'required'    => 'Email is required.',
                        'valid_email' => 'Please enter a valid email address.',
                        'is_unique'   => 'This email is already registered.',
                        'regex_match'  => 'Invalid email! Email should be like "marjovic_alejado@lms.com"'
                    ],
                    'role' => [
                        'required' => 'Role is required.',
                        'in_list'  => 'Invalid role selected.'
                    ],
                    'password' => [
                        'min_length' => 'Password must be at least 6 characters long.'
                    ],
                    'year_level' => [
                        'required' => 'Year level is required for students.',
                        'in_list'  => 'Please select a valid year level.'
                    ]
                ];

                if ($this->validate($rules, $messages)) {
                    $updateData = [
                        'name'       => $this->request->getPost('name'),
                        'email'      => $this->request->getPost('email'),
                        'role'       => $this->request->getPost('role'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ];

                    if ($this->request->getPost('password')) {
                        $updateData['password'] = password_hash($this->request->getPost('password'), PASSWORD_DEFAULT);
                    }
                    
                    // Add or update year_level if role is student
                    if ($role === 'student') {
                        $updateData['year_level'] = $this->request->getPost('year_level');
                    } else {
                        // Clear year_level if role changed from student to admin/teacher
                        $updateData['year_level'] = null;
                    }

                    if ($builder->where('id', $userID)->update($updateData)) {
                        $updateActivity = [
                            'type' => 'user_update',
                            'icon' => '✏️',
                            'title' => 'User Account Updated',
                            'description' => esc($updateData['name']) . ' (' . ucfirst($updateData['role']) . ') account was updated by admin',
                            'time' => date('Y-m-d H:i:s'),
                            'user_name' => esc($updateData['name']),
                            'user_role' => $updateData['role'],
                            'updated_by' => $this->session->get('name')
                        ];

                        $updateActivities = $this->session->get('update_activities') ?? [];
                        array_unshift($updateActivities, $updateActivity);
                        $updateActivities = array_slice($updateActivities, 0, 10);
                        $this->session->set('update_activities', $updateActivities);

                        $this->session->setFlashdata('success', 'User updated successfully!');
                        return redirect()->to(base_url('admin/manage_users'));
                    } else {
                        $this->session->setFlashdata('error', 'Failed to update user. Please try again.');
                    }
                } else {
                    $this->session->setFlashdata('errors', $this->validation->getErrors());
                }
            }

            $users = $builder->orderBy('created_at', 'DESC')->get()->getResultArray();
            $data = [
                'user' => [
                    'userID' => $this->session->get('userID'),
                    'name'   => $this->session->get('name'),
                    'email'  => $this->session->get('email'),
                    'role'   => $this->session->get('role')
                ],
                'title' => 'Edit User - Admin Dashboard',
                'users' => $users,
                'currentAdminID' => $currentAdminID,
                'editUser' => $userToEdit,
                'showCreateForm' => false,
                'showEditForm' => true
            ];
            return view('admin/manage_users', $data);
        }
        
        // ===== DELETE USER =====
        if ($action === 'delete' && $userID) {
            $builder = $this->db->table('users');
            $userToDelete = $builder->where('id', $userID)->get()->getRowArray();

            if (!$userToDelete) {
                $this->session->setFlashdata('error', 'User not found.');
                return redirect()->to(base_url('admin/manage_users'));
            }

            if ($userToDelete['id'] == $currentAdminID) {
                $this->session->setFlashdata('error', 'You cannot delete your own account.');
                return redirect()->to(base_url('admin/manage_users'));
            }

            $deletionActivity = [
                'type' => 'user_deletion',
                'icon' => '🗑️',
                'title' => 'User Account Deleted',
                'description' => esc($userToDelete['name']) . ' (' . ucfirst($userToDelete['role']) . ') account was removed from the system',
                'time' => date('Y-m-d H:i:s'),
                'user_name' => esc($userToDelete['name']),
                'user_role' => $userToDelete['role'],
                'deleted_by' => $this->session->get('name')
            ];

            $deletionActivities = $this->session->get('deletion_activities') ?? [];
            array_unshift($deletionActivities, $deletionActivity);
            $deletionActivities = array_slice($deletionActivities, 0, 10);
            $this->session->set('deletion_activities', $deletionActivities);

            $deleteBuilder = $this->db->table('users');
            if ($deleteBuilder->where('id', $userID)->delete()) {
                $this->session->setFlashdata('success', 'User deleted successfully!');
            } else {
                $this->session->setFlashdata('error', 'Failed to delete user. Please try again.');
            }

            return redirect()->to(base_url('admin/manage_users'));
        }
        
        // ===== SHOW USER MANAGEMENT INTERFACE =====
        $builder = $this->db->table('users');
        $users = $builder->orderBy('created_at', 'DESC')->get()->getResultArray();

        $data = [
            'user' => [
                'userID' => $this->session->get('userID'),
                'name'   => $this->session->get('name'),
                'email'  => $this->session->get('email'),
                'role'   => $this->session->get('role')
            ],
            'title' => 'Manage Users - Admin Dashboard',
            'users' => $users,
            'currentAdminID' => $currentAdminID,
            'editUser' => null,
            'showCreateForm' => $this->request->getGet('create') === 'true' || ($action === 'create' && $this->request->getMethod() !== 'POST'),
            'showEditForm' => false
        ];
          
        return view('admin/manage_users', $data);
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
    }
}