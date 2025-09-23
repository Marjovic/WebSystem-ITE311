<?php

namespace App\Controllers;

// Auth Controller class - This handles user authentication (login, register, logout)
// This is the main controller that manages user accounts and login system
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
        if ($this->isLoggedIn()) {
            // Send them to their dashboard instead of registration page
            return redirect()->to(uri: base_url(relativePath: 'dashboard'));
        }

        // Step 2: Check if the registration form was submitted        // This happens when user fills the form and clicks "Register" button
        if ($this->request->getMethod() === 'POST') {
            
            // Step 2a: Set validation rules - these are the requirements for each form field
            // Each rule tells the system what to check for in the user's input
            $rules = [
                'name'             => 'required|min_length[3]|max_length[100]',  // Name must exist and be 3-100 characters
                'email'            => 'required|valid_email|is_unique[users.email]', // Email must be valid format and not already used
                'password'         => 'required|min_length[6]',                 // Password must exist and be at least 6 characters
                'password_confirm' => 'required|matches[password]'              // Password confirmation must match the password
            ];

            // Step 2b: Set error messages - these are shown to user if validation fails
            // Each message explains what went wrong in simple language
            $messages = [
                'name' => [
                    'required'   => 'Name is required.',                        // Show if user didn't enter name
                    'min_length' => 'Name must be at least 3 characters long.', // Show if name is too short
                    'max_length' => 'Name cannot exceed 100 characters.'        // Show if name is too long
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
                    'required' => 'Password confirmation is required.',         // Show if user didn't confirm password                    'matches'  => 'Password confirmation does not match.'       // Show if password and confirmation are different
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
                // Show all validation error messages to help user fix their input
                $this->session->setFlashdata(data: 'errors', value: $this->validation->getErrors());
            }
        }

        // Step 4: Show the registration form page        
        // This runs when user first visits registration page OR if there were errors
        return view(name: 'auth/register');
    }

    // Login Method - This handles user sign-in with role-based redirection
    // This function shows login form and processes user login attempts
    // Steps: 1) Check if already logged in 2) Validate login form 3) Check password 4) Create session 5) Redirect by role
    public function login()
    {
        // Step 1: Check if user is already logged in
        // If someone is already logged in, send them to their appropriate dashboard
        if ($this->isLoggedIn()) {
            return $this->redirectByRole();
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
                    ];                    // Save all session data - this remembers the user across pages
                    $this->session->set(data: $sessionData);
                    
                    // Step 4b: Show welcome message and redirect user to their dashboard
                    // Different roles go to different dashboards (admin, teacher, student)
                    $this->session->setFlashdata(data: 'success', value: 'Welcome back, ' . $user['name'] . '!');
                    return $this->redirectByRole();
                    
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

        // Step 5: Show the login form page        // This runs when user first visits login page OR if login failed
        return view(name: 'auth/login');
    }

    // Helper method to redirect users based on their role
    // Different user types go to different dashboards after login
    // Admin goes to admin dashboard, teacher goes to teacher dashboard, student goes to student dashboard
    private function redirectByRole()
    {
        // Get the role of currently logged in user from session
        $role = $this->session->get(key: 'role');
        
        // Check user's role and send them to appropriate dashboard
        switch ($role) {
            case 'admin':
                // Admin users go to admin dashboard (can manage whole system)
                return redirect()->to(uri: base_url(relativePath: 'admin/dashboard'));
            case 'teacher':
                // Teacher users go to teacher dashboard (can manage courses and students)
                return redirect()->to(uri: base_url(relativePath: 'teacher/dashboard'));
            case 'student':
                // Student users go to student dashboard (can view courses and assignments)                
                return redirect()->to(uri: base_url(relativePath: 'student/dashboard'));
        }
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
    }

    // Dashboard Method - This shows user dashboard (main page after login)
    // This is a general dashboard that redirects to role-specific dashboards
    // Only accessible to users who are logged in
    public function dashboard()
    {
        // Check if user is logged in first
        // If not logged in, they can't access any dashboard
        if (!$this->isLoggedIn()) {
            // Show error message and send to login page
            $this->session->setFlashdata(data: 'error', value: 'Please login to access the dashboard.');
            return redirect()->to(uri: base_url(relativePath: 'login'));
        }

        // If user is logged in, redirect them to their role-specific dashboard       
        // This uses their role (admin, teacher, student) to send them to the right place
        return $this->redirectByRole();
    }

    // Helper Method: Check if user is logged in
    // This is a private function used by other methods to verify login status
    // Returns true if user is logged in, false if user is not logged in
    private function isLoggedIn(): bool
    {
        // Check if session has 'isLoggedIn' set to true
        // Session remembers if user successfully logged in earlier
        // If 'isLoggedIn' is true, user is logged in        // If 'isLoggedIn' is false or doesn't exist, user is not logged in
        return $this->session->get(key: 'isLoggedIn') === true;
    }

    // Helper Method: Get current user data from session
    // This function returns information about the currently logged in user
    // Other parts of the system can use this to get user details
    public function getCurrentUser(): array
    {
        // Return all user information stored in session as an array
        // This includes user ID, name, email, and role
        return [
            'userID' => $this->session->get(key: 'userID'),  // User's unique ID number from database
            'name'   => $this->session->get(key: 'name'),    // User's full name (first and last name)
            'email'  => $this->session->get(key: 'email'),   // User's email address (used for login)
            'role'   => $this->session->get(key: 'role')     // User's role (admin, teacher, or student)
        ];
    }
}