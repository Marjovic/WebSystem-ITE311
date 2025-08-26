<?php

namespace App\Controllers;

class Auth extends BaseController
{
    protected $session;
    protected $validation;
    protected $db;

    public function __construct()
    {
        $this->session = \Config\Services::session();
        $this->validation = \Config\Services::validation();
        $this->db = \Config\Database::connect();
    }

    public function register()
    {
        // If user is already logged in, redirect to dashboard
        if ($this->isLoggedIn()) {
            return redirect()->to(base_url('dashboard')); // FIXED: Changed from 'auth/dashboard'
        }

        // Check if the form was submitted (POST request)
        if ($this->request->getMethod() === 'POST') {
            
            // Set validation rules for name, email, password, and password_confirm fields
            $rules = [
                'name'             => 'required|min_length[3]|max_length[100]',
                'email'            => 'required|valid_email|is_unique[users.email]',
                'password'         => 'required|min_length[6]',
                'password_confirm' => 'required|matches[password]'
            ];

            $messages = [
                'name' => [
                    'required'   => 'Name is required.',
                    'min_length' => 'Name must be at least 3 characters long.',
                    'max_length' => 'Name cannot exceed 100 characters.'
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
                'password_confirm' => [
                    'required' => 'Password confirmation is required.',
                    'matches'  => 'Password confirmation does not match.'
                ]
            ];

            // If validation passes
            if ($this->validate($rules, $messages)) {
                
                // Hash the password using password_hash() function
                $hashedPassword = password_hash($this->request->getPost('password'), PASSWORD_DEFAULT);

                // Prepare user data (name, email, hashed_password, role) to match your table structure
                $userData = [
                    'name'       => $this->request->getPost('name'),
                    'email'      => $this->request->getPost('email'),
                    'password'   => $hashedPassword,
                    'role'       => 'user', // Default role (admin or user based on your ENUM)
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];

                // Save the user data to the users table
                $builder = $this->db->table('users');
                
                if ($builder->insert($userData)) {
                    // On success, set a flash message and redirect to the login page
                    $this->session->setFlashdata('success', 'Registration successful! Please login with your credentials.');
                    return redirect()->to(base_url('login')); // FIXED: Changed from 'auth/login'
                } else {
                    $this->session->setFlashdata('error', 'Registration failed. Please try again.');
                }
            } else {
                // Validation failed
                $this->session->setFlashdata('errors', $this->validation->getErrors());
            }
        }

        // Load the registration view
        return view('auth/register');
    }

    public function login()
    {
        // If user is already logged in, redirect to dashboard
        if ($this->isLoggedIn()) {
            return redirect()->to(base_url('dashboard')); // FIXED: Changed from 'auth/dashboard'
        }

        // Check for a POST request
        if ($this->request->getMethod() === 'POST') {
            
            // Set validation rules for email and password
            $rules = [
                'email'    => 'required|valid_email',
                'password' => 'required'
            ];

            $messages = [
                'email' => [
                    'required'    => 'Email is required.',
                    'valid_email' => 'Please enter a valid email address.'
                ],
                'password' => [
                    'required' => 'Password is required.'
                ]
            ];

            // If validation passes
            if ($this->validate($rules, $messages)) {
                $email = $this->request->getPost('email');
                $password = $this->request->getPost('password');

                // Check the database for a user using the provided email
                $builder = $this->db->table('users');
                $user = $builder->where('email', $email)
                               ->get()
                               ->getRowArray();

                // If user exists, verify the submitted password against the stored hash
                if ($user && password_verify($password, $user['password'])) {
                    
                    // If credentials are correct, create a user session
                    // Store: userID, name, email, role (matching your table structure)
                    $sessionData = [
                        'userID'     => $user['id'],
                        'name'       => $user['name'],
                        'email'      => $user['email'],
                        'role'       => $user['role'],
                        'isLoggedIn' => true
                    ];

                    $this->session->set($sessionData);
                    
                    // Set a welcome flash message and redirect to the dashboard
                    $this->session->setFlashdata('success', 'Welcome back, ' . $user['name'] . '!');
                    return redirect()->to(base_url('dashboard')); // FIXED: Changed from 'auth/dashboard'
                    
                } else {
                    // Invalid credentials
                    $this->session->setFlashdata('error', 'Invalid email or password.');
                }
            } else {
                // Validation failed
                $this->session->setFlashdata('errors', $this->validation->getErrors());
            }
        }

        // Load the login view
        return view('auth/login');
    }

    public function logout()
    {
        // Destroy the current session using session()->destroy()
        $this->session->destroy();
        
        // Set logout message and redirect the user to the login page
        $this->session->setFlashdata('success', 'You have been logged out successfully.');
        return redirect()->to(base_url('login')); // FIXED: Changed from 'auth/login'
    }

    public function dashboard()
    {
        // Check if a user is logged in at the start of the method
        // If not, redirect them to the login page
        if (!$this->isLoggedIn()) {
            $this->session->setFlashdata('error', 'Please login to access the dashboard.');
            return redirect()->to(base_url('login')); // FIXED: Changed from 'auth/login'
        }

        // Get current user data from session (matching your table structure)
        $userData = [
            'userID' => $this->session->get('userID'),
            'name'   => $this->session->get('name'),
            'email'  => $this->session->get('email'),
            'role'   => $this->session->get('role')
        ];
        
        $data = [
            'user' => $userData,
            'title' => 'Dashboard - MGOD LMS'
        ];

        return view('auth/dashboard', $data);
    }

    /**
     * Helper method: Check if user is logged in
     */
    private function isLoggedIn(): bool
    {
        return $this->session->get('isLoggedIn') === true;
    }

    /**
     * Helper method: Get current user data from session
     */
    public function getCurrentUser(): array
    {
        return [
            'userID' => $this->session->get('userID'),
            'name'   => $this->session->get('name'),
            'email'  => $this->session->get('email'),
            'role'   => $this->session->get('role')
        ];
    }
}