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
    {
        // Step 1: Check if user is already logged in
        // If someone is already logged in, they don't need to register again
        if ($this->session->get('isLoggedIn') === true) {
            // Send them to their unified dashboard instead of registration page
            return redirect()->to(uri: base_url(relativePath: 'dashboard'));
        }

        // Step 2: Check if the registration form was submitted
        // This happens when user fills the form and clicks "Register" button
        if ($this->request->getMethod() === 'POST') {            // Step 2a: Set validation rules - these are the requirements for each form field
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
    {
        // Step 1: Check if user is already logged in
        // If someone is already logged in, send them to unified dashboard
        if ($this->session->get('isLoggedIn') === true) {
            return redirect()->to(uri: base_url(relativePath: 'dashboard'));
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
                    $this->session->set(data: $sessionData);
                    
                    // Step 4b: Show welcome message and redirect user to unified dashboard
                    // All users go to the same dashboard route - role is handled inside dashboard method
                    $this->session->setFlashdata(data: 'success', value: 'Welcome back, ' . $user['name'] . '!');
                    return redirect()->to(uri: base_url(relativePath: 'dashboard'));
                    
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
        return redirect()->to(uri: base_url(relativePath: 'login'));
    }    // Dashboard Method - This shows unified dashboard based on user role
    // This is the main dashboard that handles all user types in one place
    // Only accessible to users who are logged in
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

        // Step 2: Get user role from session to determine what data to fetch
        // Each role needs different data and different dashboard view
        $userRole = $this->session->get(key: 'role');
          // Step 3: Check if admin is accessing manage users functionality
        // This allows admin to manage users directly from dashboard
        if ($userRole === 'admin') {
            $action = $this->request->getGet('action');
            $userID = $this->request->getGet('id');
            
            // Handle user management actions for admin
            if ($action) {
                switch ($action) {
                    case 'manageUsers':
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
                        return view('auth/manage_users', $data);

                    case 'createUser':
                        // Handle user creation
                        if ($this->request->getMethod() === 'POST') {                            // Validation rules for creating new user
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
                                    $this->session->setFlashdata('success', 'User created successfully!');
                                    return redirect()->to(base_url('dashboard?action=manageUsers'));
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
                            'showEditForm' => false
                        ];
                        return view('auth/manage_users', $data);

                    case 'editUser':
                        // Handle user editing
                        if (!$userID) {
                            $this->session->setFlashdata('error', 'User ID is required.');
                            return redirect()->to(base_url('dashboard?action=manageUsers'));
                        }

                        // Get user to edit
                        $builder = $this->db->table('users');
                        $userToEdit = $builder->where('id', $userID)->get()->getRowArray();

                        if (!$userToEdit) {
                            $this->session->setFlashdata('error', 'User not found.');
                            return redirect()->to(base_url('dashboard?action=manageUsers'));
                        }

                        // Check restrictions: Admin cannot edit self or other admins
                        $currentAdminID = $this->session->get('userID');
                        if ($userToEdit['role'] === 'admin' || $userToEdit['id'] == $currentAdminID) {
                            $this->session->setFlashdata('error', 'You cannot edit admin accounts or your own account.');
                            return redirect()->to(base_url('dashboard?action=manageUsers'));
                        }

                        if ($this->request->getMethod() === 'POST') {                            // Validation rules for editing user
                            $rules = [
                                'name' => 'required|min_length[3]|max_length[100]|regex_match[/^[a-zA-ZñÑ\s]+$/]',
                                'email' => "required|valid_email|is_unique[users.email,id,{$userID}]",
                                'role' => 'required|in_list[teacher,student]'
                            ];

                            // Only validate password if provided
                            if ($this->request->getPost('password')) {
                                $rules['password'] = 'min_length[6]';
                            }

                            $messages = [                                'name' => [
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
                                    $this->session->setFlashdata('success', 'User updated successfully!');
                                    return redirect()->to(base_url('dashboard?action=manageUsers'));
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
                            'currentAdminID' => $currentAdminID,
                            'editUser' => $userToEdit,
                            'showCreateForm' => false,
                            'showEditForm' => true
                        ];
                        return view('auth/manage_users', $data);

                    case 'deleteUser':
                        // Handle user deletion
                        if (!$userID) {
                            $this->session->setFlashdata('error', 'User ID is required.');
                            return redirect()->to(base_url('dashboard?action=manageUsers'));
                        }

                        // Get user to delete
                        $builder = $this->db->table('users');
                        $userToDelete = $builder->where('id', $userID)->get()->getRowArray();

                        if (!$userToDelete) {
                            $this->session->setFlashdata('error', 'User not found.');
                            return redirect()->to(base_url('dashboard?action=manageUsers'));
                        }

                        // Check restrictions: Admin cannot delete self or other admins
                        $currentAdminID = $this->session->get('userID');
                        if ($userToDelete['role'] === 'admin' || $userToDelete['id'] == $currentAdminID) {
                            $this->session->setFlashdata('error', 'You cannot delete admin accounts or your own account.');
                            return redirect()->to(base_url('dashboard?action=manageUsers'));
                        }

                        // Delete user
                        if ($builder->where('id', $userID)->delete()) {
                            $this->session->setFlashdata('success', 'User deleted successfully!');
                        } else {
                            $this->session->setFlashdata('error', 'Failed to delete user. Please try again.');
                        }

                        return redirect()->to(base_url('dashboard?action=manageUsers'));
                }
            }
        }
        
        // Step 4: Prepare basic user data that all roles need
        $baseData = [
            'user' => [
                'userID' => $this->session->get(key: 'userID'), // User's ID number
                'name'   => $this->session->get(key: 'name'),   // User's full name
                'email'  => $this->session->get(key: 'email'), // User's email address
                'role'   => $this->session->get(key: 'role')   // User's role
            ]
        ];

        // Step 5: Get role-specific data and determine view based on user type
        switch ($userRole) {
            case 'admin':
                // Admin gets system statistics and user management data
                $totalUsers = $this->db->table('users')->countAll();
                $totalAdmins = $this->db->table('users')->where('role', 'admin')->countAllResults();
                $totalTeachers = $this->db->table('users')->where('role', 'teacher')->countAllResults();
                $totalStudents = $this->db->table('users')->where('role', 'student')->countAllResults();
                $recentUsers = $this->db->table('users')->orderBy('created_at', 'DESC')->limit(5)->get()->getResultArray();

                $dashboardData = array_merge($baseData, [
                    'title' => 'Admin Dashboard - MGOD LMS',
                    'totalUsers' => $totalUsers,
                    'totalAdmins' => $totalAdmins,
                    'totalTeachers' => $totalTeachers,
                    'totalStudents' => $totalStudents,
                    'recentUsers' => $recentUsers
                ]);
                return view('auth/dashboard', $dashboardData);
                
            case 'teacher':
                // Teacher gets course and student data 
                $dashboardData = array_merge($baseData, [
                    'title' => 'Teacher Dashboard - MGOD LMS',
                    'totalCourses' => 0,    // Replace with actual course count from database
                    'totalStudents' => 0    // Replace with actual student count from database
                ]);
                
                return view('auth/dashboard', $dashboardData);
                
            case 'student':
                // Student gets enrollment and assignment data 
                $dashboardData = array_merge($baseData, [
                    'title' => 'Student Dashboard - MGOD LMS',
                    'enrolledCourses' => 0,      // Replace with actual enrolled courses count
                    'completedAssignments' => 0, // Replace with actual completed assignments count
                    'pendingAssignments' => 0    // Replace with actual pending assignments count
                ]);
                return view('auth/dashboard', $dashboardData);
                
            default:                
                // If role is unknown, show generic dashboard
                return view('auth/dashboard', $baseData);
        }
    }
}