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
        if ($this->request->getMethod() === 'POST') {
            // Step 2a: Set validation rules - these are the requirements for each form field
            // Each rule tells the system what to check for in the user's input
            $rules = [
                'name'             => 'required|min_length[3]|max_length[100]|regex_match[/^[a-zA-ZñÑ\s]+$/]',  // Name must exist, be 3-100 characters, and only contain letters (including ñ/Ñ) and spaces
                'email'            => 'required|valid_email|is_unique[users.email]', // Email must be valid format and not already used
                'password'         => 'required|min_length[6]',                 // Password must exist and be at least 6 characters
                'password_confirm' => 'required|matches[password]'              // Password confirmation must match the password
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
                    'is_unique'   => 'This email is already registered.'        // Show if someone already uses this email
                ],
                'password' => [
                    'required'   => 'Password is required.',                    // Show if user didn't enter password
                    'min_length' => 'Password must be at least 6 characters long.' // Show if password is too short (less than 6 characters)
                ],
                'password_confirm' => [
                    'required' => 'Password confirmation is required.',         // Show if user didn't confirm password
                    'matches'  => 'Password confirmation does not match.'       // Show if password and confirmation are different
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
                return view('auth/dashboard', $dashboardData);
                  case 'teacher':                // Teacher gets course and student data 
                $teacherID = $this->session->get('userID');
                
                // Get courses assigned to this teacher
                $teacherCourses = $this->db->table('courses')->where('instructor_id', $teacherID)->countAllResults();
                $activeCourses = $this->db->table('courses')->where('instructor_id', $teacherID)->where('status', 'active')->countAllResults();
                
                // Get available courses (active courses without an instructor)
                $availableCoursesCount = $this->db->table('courses')
                    ->where('status', 'active')
                    ->where('(instructor_id IS NULL OR instructor_id = 0)')
                    ->countAllResults();
                
                // Get total students enrolled in teacher's courses
                $totalStudentsQuery = $this->db->query("
                    SELECT COUNT(DISTINCT e.user_id) as total_students
                    FROM enrollments e 
                    INNER JOIN courses c ON e.course_id = c.id 
                    WHERE c.instructor_id = ?
                ", [$teacherID]);
                $totalStudents = $totalStudentsQuery->getRow()->total_students ?? 0;
                
                $dashboardData = array_merge($baseData, [
                    'title' => 'Teacher Dashboard - MGOD LMS',
                    'totalCourses' => $teacherCourses,
                    'activeCourses' => $activeCourses,
                    'availableCoursesCount' => $availableCoursesCount,
                    'totalStudents' => $totalStudents
                ]);
                
                return view('auth/dashboard', $dashboardData);
                
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
                    ->select('courses.*, users.name as instructor_name')
                    ->join('users', 'courses.instructor_id = users.id', 'left')
                    ->where('courses.status', 'active')
                    ->orderBy('courses.created_at', 'DESC')
                    ->get()
                    ->getResultArray();
                
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
      // Manage Users Method - Standalone method for /admin/manage_users route
    // This handles all user management operations for admin users
    public function manageUsers()
    {
        // Security check - only admins can access this page
        if ($this->session->get('isLoggedIn') !== true) {
            $this->session->setFlashdata('error', 'Please login to access this page.');
            return redirect()->to(base_url('login'));
        }        if ($this->session->get('role') !== 'admin') {
            $this->session->setFlashdata('error', 'Access denied. You do not have permission to access this page.');
            $userRole = $this->session->get('role');
            return redirect()->to(base_url($userRole . '/dashboard'));
        }

        $action = $this->request->getGet('action');
        $userID = $this->request->getGet('id');
        
        // Handle user management actions
        if ($action) {
            switch ($action) {
                case 'create':
                    return $this->handleCreateUser();
                    
                case 'edit':
                    return $this->handleEditUser($userID);
                    
                case 'delete':
                    return $this->handleDeleteUser($userID);
            }
        }
        
        // Show user management interface
        $builder = $this->db->table('users');
        $users = $builder->orderBy('created_at', 'DESC')->get()->getResultArray();
        $currentAdminID = $this->session->get('userID');

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
            'showCreateForm' => $this->request->getGet('create') === 'true',
            'showEditForm' => false
        ];
          return view('admin/manage_users', $data);
    }

    // Manage Courses Method - Standalone method for /admin/manage_courses route  
    // This handles all course management operations for admin users
    public function manageCourses()
    {
        // Security check - only admins can access this page
        if ($this->session->get('isLoggedIn') !== true) {
            $this->session->setFlashdata('error', 'Please login to access this page.');
            return redirect()->to(base_url('login'));        }
        
        if ($this->session->get('role') !== 'admin') {
            $this->session->setFlashdata('error', 'Access denied. You do not have permission to access this page.');
            $userRole = $this->session->get('role');
            return redirect()->to(base_url($userRole . '/dashboard'));
        }

        $action = $this->request->getGet('action');
        $courseID = $this->request->getGet('id');
        
        // Handle course management actions
        if ($action) {
            switch ($action) {
                case 'create':
                    return $this->handleCreateCourse();
                    
                case 'edit':
                    return $this->handleEditCourse($courseID);
                    
                case 'delete':
                    return $this->handleDeleteCourse($courseID);
            }
        }
        
        // Show course management interface
        $coursesBuilder = $this->db->table('courses');
        $courses = $coursesBuilder
            ->select('courses.*, users.name as instructor_name')
            ->join('users', 'courses.instructor_id = users.id', 'left')
            ->orderBy('courses.created_at', 'DESC')
            ->get()
            ->getResultArray();

        // Get all teachers for dropdown
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
            'showCreateForm' => $this->request->getGet('create') === 'true',
            'showEditForm' => false        ];
        
        return view('admin/manage_courses', $data);
    }

    private function handleCreateUser()
    {
        if ($this->request->getMethod() === 'POST') {
            // Validation rules for creating new user
            $rules = [
                'name'     => 'required|min_length[3]|max_length[100]|regex_match[/^[a-zA-ZñÑ\s]+$/]',
                'email'    => 'required|valid_email|is_unique[users.email]',
                'password' => 'required|min_length[6]',
                'role'     => 'required|in_list[admin,teacher,student]'
            ];

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
                    'is_unique'   => 'This email is already registered.'
                ],
                'password' => [
                    'required'   => 'Password is required.',
                    'min_length' => 'Password must be at least 6 characters long.'
                ],
                'role' => [
                    'required' => 'Role is required.',
                    'in_list'  => 'Invalid role selected.'
                ]
            ];

            if ($this->validate($rules, $messages)) {
                // Create new user
                $userData = [
                    'name'       => $this->request->getPost('name'),
                    'email'      => $this->request->getPost('email'),
                    'password'   => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
                    'role'       => $this->request->getPost('role'),
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];

                $builder = $this->db->table('users');
                if ($builder->insert($userData)) {
                    // Record user creation activity
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

                    // Get existing creation activities from session (if any)
                    $creationActivities = $this->session->get('creation_activities') ?? [];
                    
                    // Add new creation activity to the beginning of the array
                    array_unshift($creationActivities, $creationActivity);
                    
                    // Keep only the last 10 creation activities to prevent session bloat
                    $creationActivities = array_slice($creationActivities, 0, 10);
                    
                    // Store updated creation activities in session
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

        // Show manage users view with create form
        $builder = $this->db->table('users');
        $users = $builder->orderBy('created_at', 'DESC')->get()->getResultArray();
        $currentAdminID = $this->session->get('userID');

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
            'showCreateForm' => true,
            'showEditForm' => false        ];
        return view('admin/manage_users', $data);
    }

    private function handleEditUser($userID)
    {
        if (!$userID) {
            $this->session->setFlashdata('error', 'User ID is required.');
            return redirect()->to(base_url('admin/manage_users'));
        }

        // Get user to edit
        $builder = $this->db->table('users');
        $userToEdit = $builder->where('id', $userID)->get()->getRowArray();

        if (!$userToEdit) {
            $this->session->setFlashdata('error', 'User not found.');
            return redirect()->to(base_url('admin/manage_users'));
        }

        // Check restrictions: Admin cannot edit self or other admins
        $currentAdminID = $this->session->get('userID');
        if ($userToEdit['role'] === 'admin' || $userToEdit['id'] == $currentAdminID) {
            $this->session->setFlashdata('error', 'You cannot edit admin accounts or your own account.');
            return redirect()->to(base_url('admin/manage_users'));
        }

        if ($this->request->getMethod() === 'POST') {
            // Validation rules for editing user
            $rules = [
                'name' => 'required|min_length[3]|max_length[100]|regex_match[/^[a-zA-ZñÑ\s]+$/]',
                'email' => "required|valid_email|is_unique[users.email,id,{$userID}]",
                'role' => 'required|in_list[teacher,student]'
            ];

            // Only validate password if provided
            if ($this->request->getPost('password')) {
                $rules['password'] = 'min_length[6]';
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
                    'is_unique'   => 'This email is already registered.'
                ],
                'role' => [
                    'required' => 'Role is required.',
                    'in_list'  => 'Invalid role selected.'
                ],
                'password' => [
                    'min_length' => 'Password must be at least 6 characters long.'
                ]
            ];

            if ($this->validate($rules, $messages)) {
                // Prepare update data
                $updateData = [
                    'name'       => $this->request->getPost('name'),
                    'email'      => $this->request->getPost('email'),
                    'role'       => $this->request->getPost('role'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];

                // Update password only if provided
                if ($this->request->getPost('password')) {
                    $updateData['password'] = password_hash($this->request->getPost('password'), PASSWORD_DEFAULT);
                }

                if ($builder->where('id', $userID)->update($updateData)) {
                    // Record user update activity
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

                    // Get existing update activities from session (if any)
                    $updateActivities = $this->session->get('update_activities') ?? [];
                    
                    // Add new update activity to the beginning of the array
                    array_unshift($updateActivities, $updateActivity);
                    
                    // Keep only the last 10 update activities to prevent session bloat
                    $updateActivities = array_slice($updateActivities, 0, 10);
                    
                    // Store updated update activities in session
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

        // Show edit form
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
            'currentAdminID' => $this->session->get('userID'),
            'editUser' => $userToEdit,
            'showCreateForm' => false,
            'showEditForm' => true
        ];        return view('admin/manage_users', $data);
    }

    private function handleDeleteUser($userID)
    {
        if (!$userID) {
            $this->session->setFlashdata('error', 'User ID is required.');
            return redirect()->to(base_url('admin/manage_users'));
        }

        // Get user to delete
        $builder = $this->db->table('users');
        $userToDelete = $builder->where('id', $userID)->get()->getRowArray();

        if (!$userToDelete) {
            $this->session->setFlashdata('error', 'User not found.');
            return redirect()->to(base_url('admin/manage_users'));
        }

        // Check restrictions: Admin cannot delete self or other admins
        $currentAdminID = $this->session->get('userID');
        if ($userToDelete['role'] === 'admin' || $userToDelete['id'] == $currentAdminID) {
            $this->session->setFlashdata('error', 'You cannot delete admin accounts or your own account.');
            return redirect()->to(base_url('admin/manage_users'));
        }

        // Store deletion activity before deleting user
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

        // Get existing deletion activities from session (if any)
        $deletionActivities = $this->session->get('deletion_activities') ?? [];
        
        // Add new deletion activity to the beginning of the array
        array_unshift($deletionActivities, $deletionActivity);
        
        // Keep only the last 10 deletion activities to prevent session bloat
        $deletionActivities = array_slice($deletionActivities, 0, 10);
        
        // Store updated deletion activities in session
        $this->session->set('deletion_activities', $deletionActivities);

        // Delete user
        if ($builder->where('id', $userID)->delete()) {
            $this->session->setFlashdata('success', 'User deleted successfully!');
        } else {
            $this->session->setFlashdata('error', 'Failed to delete user. Please try again.');
        }

        return redirect()->to(base_url('admin/manage_users'));
    }

    // =====================================================
    // HELPER METHODS FOR COURSE MANAGEMENT  
    // =====================================================
    
    private function handleCreateCourse()
    {
        if ($this->request->getMethod() === 'POST') {            // Validation rules for creating new course
            $rules = [
                'title' => 'required|min_length[3]|max_length[200]|regex_match[/^[a-zA-Z\s\-\.]+$/]',
                'course_code' => 'required|min_length[3]|max_length[20]|regex_match[/^[A-Z]+\-?[0-9]+$/]|is_unique[courses.course_code]',
                'instructor_id' => 'permit_empty|integer|greater_than[0]',
                'category' => 'permit_empty|max_length[100]|regex_match[/^[a-zA-Z\s\-\.]+$/]',
                'credits' => 'permit_empty|integer|greater_than[0]|less_than[10]',
                'duration_weeks' => 'permit_empty|integer|greater_than[0]|less_than[100]',
                'max_students' => 'permit_empty|integer|greater_than[0]|less_than[1000]',
                'start_date' => 'permit_empty|valid_date[Y-m-d]',
                'end_date' => 'permit_empty|valid_date[Y-m-d]',
                'status' => 'required|in_list[draft,active,completed,cancelled]',
                'description' => 'permit_empty|regex_match[/^[a-zA-Z0-9\s\.\,\:\;\!\?\n\r•\-]+$/]'
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
                ],                'instructor_id' => [
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
                ],
                'end_date' => [
                    'valid_date' => 'Please enter a valid end date.'
                ],
                'status' => [
                    'required' => 'Course status is required.',
                    'in_list' => 'Invalid course status selected.'
                ],
                'description' => [
                    'regex_match' => 'Description can only contain letters, numbers, spaces, hyphens, and basic punctuation (periods, commas, colons, semicolons, exclamation marks, question marks, and bullet points •).'
                ]
            ];

            if ($this->validate($rules, $messages)) {                // Create new course
                $courseData = [
                    'title' => $this->request->getPost('title'),
                    'course_code' => $this->request->getPost('course_code'),
                    'instructor_id' => $this->request->getPost('instructor_id') ?: null,
                    'category' => $this->request->getPost('category') ?: null,
                    'credits' => $this->request->getPost('credits') ?: 3,
                    'duration_weeks' => $this->request->getPost('duration_weeks') ?: 16,
                    'max_students' => $this->request->getPost('max_students') ?: 30,
                    'start_date' => $this->request->getPost('start_date') ?: null,
                    'end_date' => $this->request->getPost('end_date') ?: null,
                    'status' => $this->request->getPost('status'),
                    'description' => $this->request->getPost('description'),
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];

                $coursesBuilder = $this->db->table('courses');
                if ($coursesBuilder->insert($courseData)) {
                    // Record course creation activity
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

                    // Get existing creation activities from session (if any)
                    $creationActivities = $this->session->get('creation_activities') ?? [];
                    
                    // Add new creation activity to the beginning of the array
                    array_unshift($creationActivities, $creationActivity);
                    
                    // Keep only the last 10 creation activities to prevent session bloat
                    $creationActivities = array_slice($creationActivities, 0, 10);
                    
                    // Store updated creation activities in session
                    $this->session->set('creation_activities', $creationActivities);

                    $this->session->setFlashdata('success', 'Course created successfully!');
                    return redirect()->to(base_url('admin/manage_courses'));
                } else {
                    $this->session->setFlashdata('error', 'Failed to create course. Please try again.');
                }
            } else {
                $this->session->setFlashdata('errors', $this->validation->getErrors());
            }
        }

        // Show manage courses view with create form
        $coursesBuilder = $this->db->table('courses');
        $courses = $coursesBuilder
            ->select('courses.*, users.name as instructor_name')
            ->join('users', 'courses.instructor_id = users.id', 'left')
            ->orderBy('courses.created_at', 'DESC')
            ->get()
            ->getResultArray();

        // Get all teachers for dropdown
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
            'showCreateForm' => true,
            'showEditForm' => false
        ];        return view('admin/manage_courses', $data);
    }

    private function handleEditCourse($courseID)
    {
        if (!$courseID) {
            $this->session->setFlashdata('error', 'Course ID is required.');
            return redirect()->to(base_url('admin/manage_courses'));
        }

        // Get course to edit
        $coursesBuilder = $this->db->table('courses');
        $courseToEdit = $coursesBuilder->where('id', $courseID)->get()->getRowArray();

        if (!$courseToEdit) {
            $this->session->setFlashdata('error', 'Course not found.');
            return redirect()->to(base_url('admin/manage_courses'));
        }        if ($this->request->getMethod() === 'POST') {
            // Validation rules for editing course
            $rules = [
                'title' => 'required|min_length[3]|max_length[200]|regex_match[/^[a-zA-Z\s\-\.]+$/]',
                'course_code' => "required|min_length[3]|max_length[20]|regex_match[/^[A-Z]+\-?[0-9]+$/]|is_unique[courses.course_code,id,{$courseID}]",
                'instructor_id' => 'permit_empty|integer|greater_than[0]',
                'category' => 'permit_empty|max_length[100]|regex_match[/^[a-zA-Z\s\-\.]+$/]',
                'credits' => 'permit_empty|integer|greater_than[0]|less_than[10]',
                'duration_weeks' => 'permit_empty|integer|greater_than[0]|less_than[100]',
                'max_students' => 'permit_empty|integer|greater_than[0]|less_than[1000]',
                'start_date' => 'permit_empty|valid_date[Y-m-d]',
                'end_date' => 'permit_empty|valid_date[Y-m-d]',
                'status' => 'required|in_list[draft,active,completed,cancelled]',
                'description' => 'permit_empty|regex_match[/^[a-zA-Z0-9\s\.\,\:\;\!\?\n\r•\-]+$/]'
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
                ],                'instructor_id' => [
                    'integer' => 'Invalid instructor selected.',
                    'greater_than' => 'Please select a valid instructor if choosing one.'
                ],
                'category' => [
                    'max_length' => 'Category cannot exceed 100 characters.',
                    'regex_match' => 'Category can only contain letters, spaces, hyphens, and periods.'
                ],
                'status' => [
                    'required' => 'Course status is required.',
                    'in_list' => 'Invalid course status selected.'
                ],
                'description' => [
                    'regex_match' => 'Description can only contain letters, numbers, spaces, hyphens, and basic punctuation (periods, commas, colons, semicolons, exclamation marks, question marks, and bullet points •).'
                ]
            ];

            if ($this->validate($rules, $messages)) {                // Update course data
                $updateData = [
                    'title' => $this->request->getPost('title'),
                    'course_code' => $this->request->getPost('course_code'),
                    'instructor_id' => $this->request->getPost('instructor_id') ?: null,
                    'category' => $this->request->getPost('category') ?: null,
                    'credits' => $this->request->getPost('credits') ?: 3,
                    'duration_weeks' => $this->request->getPost('duration_weeks') ?: 16,
                    'max_students' => $this->request->getPost('max_students') ?: 30,
                    'start_date' => $this->request->getPost('start_date') ?: null,
                    'end_date' => $this->request->getPost('end_date') ?: null,
                    'status' => $this->request->getPost('status'),
                    'description' => $this->request->getPost('description'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];

                if ($coursesBuilder->where('id', $courseID)->update($updateData)) {
                    // Record course update activity
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

                    // Get existing update activities from session (if any)
                    $updateActivities = $this->session->get('update_activities') ?? [];
                    
                    // Add new update activity to the beginning of the array
                    array_unshift($updateActivities, $updateActivity);
                    
                    // Keep only the last 10 update activities to prevent session bloat
                    $updateActivities = array_slice($updateActivities, 0, 10);
                    
                    // Store updated update activities in session
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

        // Show edit form
        $courses = $coursesBuilder
            ->select('courses.*, users.name as instructor_name')
            ->join('users', 'courses.instructor_id = users.id', 'left')
            ->orderBy('courses.created_at', 'DESC')
            ->get()
            ->getResultArray();

        // Get all teachers for dropdown
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
            'showEditForm' => true        ];
        return view('admin/manage_courses', $data);
    }

    private function handleDeleteCourse($courseID)
    {
        if (!$courseID) {
            $this->session->setFlashdata('error', 'Course ID is required.');
            return redirect()->to(base_url('admin/manage_courses'));
        }

        // Get course to delete
        $coursesBuilder = $this->db->table('courses');
        $courseToDelete = $coursesBuilder->where('id', $courseID)->get()->getRowArray();

        if (!$courseToDelete) {
            $this->session->setFlashdata('error', 'Course not found.');
            return redirect()->to(base_url('admin/manage_courses'));
        }

        // Store deletion activity before deleting course
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

        // Get existing deletion activities from session (if any)
        $deletionActivities = $this->session->get('deletion_activities') ?? [];
        
        // Add new deletion activity to the beginning of the array
        array_unshift($deletionActivities, $deletionActivity);
        
        // Keep only the last 10 deletion activities to prevent session bloat
        $deletionActivities = array_slice($deletionActivities, 0, 10);
        
        // Store updated deletion activities in session
        $this->session->set('deletion_activities', $deletionActivities);

        // Delete course
        if ($coursesBuilder->where('id', $courseID)->delete()) {
            $this->session->setFlashdata('success', 'Course deleted successfully!');
        } else {
            $this->session->setFlashdata('error', 'Failed to delete course. Please try again.');
        }

        return redirect()->to(base_url('admin/manage_courses'));
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
            return redirect()->to(base_url($userRole . '/dashboard'));
        }        $teacherID = $this->session->get('userID');
          // Handle course assignment request
        if ($this->request->getMethod() === 'POST' && $this->request->getPost('action') === 'assign_course') {
            return $this->handleCourseAssignmentRequest($teacherID);
        }
        
        // Handle course unassignment request
        if ($this->request->getMethod() === 'POST' && $this->request->getPost('action') === 'unassign_course') {
            return $this->handleCourseUnassignmentRequest($teacherID);
        }
        
        // Get courses assigned to this teacher
        $assignedCoursesBuilder = $this->db->table('courses');
        $assignedCourses = $assignedCoursesBuilder
            ->select('courses.*, 
                      COUNT(enrollments.id) as enrolled_students,
                      GROUP_CONCAT(CONCAT(users.name, " (", users.email, ")") SEPARATOR ", ") as student_list')
            ->join('enrollments', 'courses.id = enrollments.course_id', 'left')
            ->join('users', 'enrollments.user_id = users.id AND users.role = "student"', 'left')
            ->where('courses.instructor_id', $teacherID)
            ->groupBy('courses.id')
            ->orderBy('courses.created_at', 'DESC')
            ->get()
            ->getResultArray();

        // Get available courses (active courses without an instructor)
        $availableCoursesBuilder = $this->db->table('courses');
        $availableCourses = $availableCoursesBuilder
            ->select('courses.*, COUNT(enrollments.id) as enrolled_students')
            ->join('enrollments', 'courses.id = enrollments.course_id', 'left')
            ->where('courses.status', 'active')
            ->where('(courses.instructor_id IS NULL OR courses.instructor_id = 0)')
            ->groupBy('courses.id')
            ->orderBy('courses.created_at', 'DESC')
            ->get()
            ->getResultArray();

        // Format dates for available courses
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
            ->select('courses.*, users.name as instructor_name')
            ->join('users', 'courses.instructor_id = users.id', 'left')
            ->where('courses.status', 'active')
            ->orderBy('courses.created_at', 'DESC')
            ->get()
            ->getResultArray();
        
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
        }
        
        // Validate that the course exists and is available for assignment
        $courseBuilder = $this->db->table('courses');
        $course = $courseBuilder
            ->where('id', $courseID)
            ->where('status', 'active')
            ->where('(instructor_id IS NULL OR instructor_id = 0)')
            ->get()
            ->getRowArray();
            
        if (!$course) {
            $this->session->setFlashdata('error', 'Course not found or not available for assignment.');
            return redirect()->to(base_url('teacher/courses'));
        }
        
        // Assign the teacher to the course
        $updateData = [
            'instructor_id' => $teacherID,
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
        }
        
        // Validate that the course exists and is assigned to this teacher
        $courseBuilder = $this->db->table('courses');
        $course = $courseBuilder
            ->where('id', $courseID)
            ->where('instructor_id', $teacherID)
            ->get()
            ->getRowArray();
            
        if (!$course) {
            $this->session->setFlashdata('error', 'Course not found or not assigned to you.');
            return redirect()->to(base_url('teacher/courses'));
        }
        
        // Check if course has enrolled students - warn but still allow unassignment
        $enrollmentCount = $this->db->table('enrollments')
            ->where('course_id', $courseID)
            ->countAllResults();
        
        // Unassign the teacher from the course (set instructor_id to null)
        $updateData = [
            'instructor_id' => null,
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
}