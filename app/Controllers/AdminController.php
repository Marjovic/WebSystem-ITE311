<?php

namespace App\Controllers;

use App\Controllers\BaseController;

// AdminController class - This handles all admin-related functions
// Admin is the highest level user who can manage the whole system
class AdminController extends BaseController
{
    // These are class properties (variables that belong to this class)
    protected $session; // This stores user session data (login info)
    protected $db;      // This connects to the database

    // Constructor - This runs automatically when we create AdminController object
    public function __construct()
    {
        // Get the session service - this tracks if user is logged in
        $this->session = \Config\Services::session();
        
        // Connect to database - this lets us read/write data
        $this->db = \Config\Database::connect();
    }

    // Dashboard function - This shows the main admin page
    public function dashboard()
    {
        // First, we check if user is allowed to see admin page
        // We check two things: 1) Is user logged in? 2) Is user an admin?
        if (!$this->isLoggedIn() || $this->session->get('role') !== 'admin') {
            // If user is not admin, show error message
            $this->session->setFlashdata('error', 'Access denied. Admin privileges required.');
            
            // Send user back to login page
            return redirect()->to(uri: base_url(relativePath: 'login'));
        }

        // If user is admin, get data to show on dashboard
        $data = $this->getDashboardData();
        
        // Show the admin dashboard page with the data
        return view(name: 'admin/dashboard', data: $data);
    }

    // Private function - This gets data to show on admin dashboard
    private function getDashboardData(): array
    {
        // Count how many total users are in the system
        $totalUsers = $this->db->table(tableName: 'users')->countAll();
        
        // Count users by their roles (admin, teacher, student)
        // Count how many admins exist
        $totalAdmins = $this->db->table(tableName: 'users')->where(key: 'role', value: 'admin')->countAllResults();
        
        // Count how many teachers exist
        $totalTeachers = $this->db->table(tableName: 'users')->where(key: 'role', value: 'teacher')->countAllResults();
        
        // Count how many students exist
        $totalStudents = $this->db->table(tableName: 'users')->where(key: 'role', value: 'student')->countAllResults();
        
        // Get the 5 newest users (most recently registered)
        // Order by created_at DESC means newest first, limit 5 means only 5 users
        $recentUsers = $this->db->table(tableName: 'users')->orderBy(orderBy: 'created_at', direction: 'DESC')->limit(value: 5)->get()->getResultArray();

        // Return all data as an array to send to the view
        return [
            // Current logged in user information
            'user' => [
                'userID' => $this->session->get(key: 'userID'), // User's ID number
                'name'   => $this->session->get(key: 'name'),   // User's full name
                'email'  => $this->session->get(key: 'email'), // User's email address
                'role'   => $this->session->get(key: 'role')   // User's role (admin)
            ],
            'title' => 'Admin Dashboard - MGOD LMS',  // Page title
            'totalUsers' => $totalUsers,              // Total number of users
            'totalAdmins' => $totalAdmins,            // Number of admin users
            'totalTeachers' => $totalTeachers,        // Number of teacher users
            'totalStudents' => $totalStudents,        // Number of student users
            'recentUsers' => $recentUsers             // List of 5 newest users
        ];
    }

    // Private function - This checks if user is logged in
    private function isLoggedIn(): bool
    {
        // Check if session has 'isLoggedIn' set to true
        // This returns true if logged in, false if not logged in
        return $this->session->get(key: 'isLoggedIn') === true;
    }
}
