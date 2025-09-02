<?php

namespace App\Controllers;

/**
 * Auth Controller - Handles user authentication
 * This controller manages user registration, login, logout, and dashboard access
 * It uses simple English comments to explain each part
 */
class Auth extends BaseController
{
    // These are tools we need for the controller to work
    protected $session;    // Saves user information when logged in
    protected $validation; // Checks if form data is correct
    protected $db;         // Connects to the database

    /**
     * Constructor - This runs first when controller starts
     * Sets up the three main tools we need
     */
    public function __construct()
    {
        // Get session service - this remembers user login information
        $this->session = \Config\Services::session();
        
        // Get validation service - this checks if user input is correct
        $this->validation = \Config\Services::validation();
        
        // Connect to database - this lets us save and find user data
        $this->db = \Config\Database::connect();
    }

    /**
     * Register Method - Handles user sign-up
     * Shows sign-up form and processes new user registration
     * Step 1: Check if user is already logged in
     * Step 2: If form is submitted, validate the data
     * Step 3: If valid, save user to database
     * Step 4: Redirect to login page
     */
    public function register()
    {
        // Step 1: If user is already logged in, send them to dashboard
        // No need to register if already have account
        if ($this->isLoggedIn()) {
            return redirect()->to(base_url('dashboard'));
        }

        // Step 2: Check if the form was submitted (user clicked submit button)
        if ($this->request->getMethod() === 'POST') {
            
            // Step 2a: Set validation rules - what we check for each field
            $rules = [
                'name'             => 'required|min_length[3]|max_length[100]',  // Name must be 3-100 characters
                'email'            => 'required|valid_email|is_unique[users.email]', // Email must be valid and not used before
                'password'         => 'required|min_length[6]',                 // Password must be at least 6 characters
                'password_confirm' => 'required|matches[password]'              // Password confirmation must match password
            ];

            // Step 2b: Set error messages - what to show if validation fails
            $messages = [
                'name' => [
                    'required'   => 'Name is required.',                        // Show if name is empty
                    'min_length' => 'Name must be at least 3 characters long.', // Show if name too short
                    'max_length' => 'Name cannot exceed 100 characters.'        // Show if name too long
                ],
                'email' => [
                    'required'    => 'Email is required.',                      // Show if email is empty
                    'valid_email' => 'Please enter a valid email address.',     // Show if email format wrong
                    'is_unique'   => 'This email is already registered.'        // Show if email already exists
                ],
                'password' => [
                    'required'   => 'Password is required.',                    // Show if password is empty
                    'min_length' => 'Password must be at least 6 characters long.' // Show if password too short
                ],
                'password_confirm' => [
                    'required' => 'Password confirmation is required.',         // Show if confirmation empty
                    'matches'  => 'Password confirmation does not match.'       // Show if passwords don't match
                ]
            ];

            // Step 2c: Check if all validation rules pass
            if ($this->validate($rules, $messages)) {
                
                // Step 3a: Make password safe using hash function
                // This scrambles the password so hackers can't read it
                $hashedPassword = password_hash($this->request->getPost('password'), PASSWORD_DEFAULT);

                // Step 3b: Prepare user data to save in database
                // Get form data and organize it for database
                $userData = [
                    'name'       => $this->request->getPost('name'),      // Get name from form
                    'email'      => $this->request->getPost('email'),     // Get email from form
                    'password'   => $hashedPassword,                      // Use hashed password (safe version)
                    'role'       => 'user',                               // Set default role as regular user
                    'created_at' => date('Y-m-d H:i:s'),                  // Set creation time to now
                    'updated_at' => date('Y-m-d H:i:s')                   // Set update time to now
                ];

                // Step 3c: Save user data to the users table in database
                $builder = $this->db->table('users');
                
                // Step 3d: Try to insert data and check if successful
                if ($builder->insert($userData)) {
                    // Success: Show success message and go to login page
                    $this->session->setFlashdata('success', 'Registration successful! Please login with your credentials.');
                    return redirect()->to(base_url('login'));
                } else {
                    // Failed: Show error message
                    $this->session->setFlashdata('error', 'Registration failed. Please try again.');
                }
            } else {
                // Validation failed: Show all error messages
                $this->session->setFlashdata('errors', $this->validation->getErrors());
            }
        }

        // Step 4: Show the registration form page
        return view('auth/register');
    }

    /**
     * Login Method - Handles user sign-in
     * Shows login form and processes user authentication
     * Step 1: Check if user is already logged in
     * Step 2: If form submitted, validate email and password
     * Step 3: Check if user exists and password is correct
     * Step 4: Create user session and redirect to dashboard
     */
    public function login()
    {
        // Step 1: If user is already logged in, send them to dashboard
        // No need to login again if already logged in
        if ($this->isLoggedIn()) {
            return redirect()->to(base_url('dashboard'));
        }

        // Step 2: Check if login form was submitted
        if ($this->request->getMethod() === 'POST') {
            
            // Step 2a: Set validation rules for login form
            $rules = [
                'email'    => 'required|valid_email',  // Email must be provided and valid format
                'password' => 'required'               // Password must be provided
            ];

            // Step 2b: Set error messages for login validation
            $messages = [
                'email' => [
                    'required'    => 'Email is required.',                  // Show if email is empty
                    'valid_email' => 'Please enter a valid email address.'  // Show if email format wrong
                ],
                'password' => [
                    'required' => 'Password is required.'                   // Show if password is empty
                ]
            ];

            // Step 2c: Check if validation passes
            if ($this->validate($rules, $messages)) {
                // Get email and password from form
                $email = $this->request->getPost('email');
                $password = $this->request->getPost('password');

                // Step 3a: Look for user in database using email
                $builder = $this->db->table('users');
                $user = $builder->where('email', $email)  // Find user with this email
                               ->get()                    // Execute the search
                               ->getRowArray();           // Get result as array

                // Step 3b: Check if user exists and password is correct
                if ($user && password_verify($password, $user['password'])) {
                    
                    // Step 4a: Login successful - create user session
                    // Session stores user information while they browse the site
                    $sessionData = [
                        'userID'     => $user['id'],      // Store user ID
                        'name'       => $user['name'],    // Store user name
                        'email'      => $user['email'],   // Store user email
                        'role'       => $user['role'],    // Store user role (admin or user)
                        'isLoggedIn' => true              // Mark as logged in
                    ];

                    // Save session data
                    $this->session->set($sessionData);
                    
                    // Step 4b: Show welcome message and go to dashboard
                    $this->session->setFlashdata('success', 'Welcome back, ' . $user['name'] . '!');
                    return redirect()->to(base_url('dashboard'));
                    
                } else {
                    // Step 3c: Login failed - wrong email or password
                    $this->session->setFlashdata('error', 'Invalid email or password.');
                }
            } else {
                // Step 2d: Validation failed - show error messages
                $this->session->setFlashdata('errors', $this->validation->getErrors());
            }
        }

        // Step 5: Show the login form page
        return view('auth/login');
    }

    /**
     * Logout Method - Handles user sign-out
     * Destroys user session and redirects to login page
     * Step 1: Delete all user session data
     * Step 2: Show logout message
     * Step 3: Redirect to login page
     */
    public function logout()
    {
        // Step 1: Destroy the current session - forget all user information
        // This logs the user out completely
        $this->session->destroy();
        
        // Step 2: Show logout success message
        $this->session->setFlashdata('success', 'You have been logged out successfully.');
        
        // Step 3: Send user back to login page
        return redirect()->to(base_url('login'));
    }

    /**
     * Dashboard Method - Shows user dashboard (main page after login)
     * Only accessible to logged-in users
     * Step 1: Check if user is logged in
     * Step 2: Get user information from session
     * Step 3: Show dashboard page with user data
     */
    public function dashboard()
    {
        // Step 1: Check if user is logged in at the start
        // If not logged in, send them to login page
        if (!$this->isLoggedIn()) {
            $this->session->setFlashdata('error', 'Please login to access the dashboard.');
            return redirect()->to(base_url('login'));
        }

        // Step 2: Get current user data from session
        // This gets the information we saved when user logged in
        $userData = [
            'userID' => $this->session->get('userID'),  // Get user ID
            'name'   => $this->session->get('name'),    // Get user name
            'email'  => $this->session->get('email'),   // Get user email
            'role'   => $this->session->get('role')     // Get user role
        ];
        
        // Step 3: Prepare data for dashboard page
        $data = [
            'user' => $userData,                    // Send user data to page
            'title' => 'Dashboard - MGOD LMS'      // Set page title
        ];

        // Show dashboard page with user information
        return view('auth/dashboard', $data);
    }

    /**
     * Helper Method: Check if user is logged in
     * Returns true if user is logged in, false if not
     * This is used by other methods to check login status
     */
    private function isLoggedIn(): bool
    {
        // Check if 'isLoggedIn' is set to true in session
        // If yes, user is logged in
        // If no or not set, user is not logged in
        return $this->session->get('isLoggedIn') === true;
    }

    /**
     * Helper Method: Get current user data from session
     * Returns array with user information
     * This can be used by other parts of the application
     */
    public function getCurrentUser(): array
    {
        // Return all user information stored in session
        return [
            'userID' => $this->session->get('userID'),  // User ID number
            'name'   => $this->session->get('name'),    // User full name
            'email'  => $this->session->get('email'),   // User email address
            'role'   => $this->session->get('role')     // User role (admin or user)
        ];
    }
}