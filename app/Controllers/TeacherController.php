<?php

namespace App\Controllers;

use App\Controllers\BaseController;

// TeacherController class - This handles all teacher-related functions
// Teacher is a user who can create courses and manage students
class TeacherController extends BaseController
{
    // These are class properties (variables that belong to this class)
    protected $session; // This stores user session data (login info)
    protected $db;      // This connects to the database

    // Constructor - This runs automatically when we create TeacherController object
    public function __construct()
    {
        // Get the session service - this tracks if user is logged in
        $this->session = \Config\Services::session();
        
        // Connect to database - this lets us read/write data
        $this->db = \Config\Database::connect();
    }

    // Dashboard function - This shows the main teacher page
    public function dashboard()
    {
        // First, we check if user is allowed to see teacher page
        // We check two things: 1) Is user logged in? 2) Is user a teacher?
        if (!$this->isLoggedIn() || $this->session->get(key: 'role') !== 'teacher') {
            // If user is not teacher, show error message
            $this->session->setFlashdata(data: 'error', value: 'Access denied. Teacher privileges required.');
            
            // Send user back to login page
            return redirect()->to(uri: base_url(relativePath: 'login'));
        }

        // If user is teacher, get data to show on dashboard
        $data = $this->getDashboardData();
        
        // Show the teacher dashboard page with the data
        return view(name: 'teacher/dashboard', data: $data);
    }    // Private function - This gets data to show on teacher dashboard
    private function getDashboardData(): array
    {
        // These are temporary hard-coded numbers
        // In real system, these would come from database
        $totalCourses = 0;  // Number of courses this teacher has created
        $totalStudents = 0; // Number of students enrolled in teacher's courses
        
        // Return all data as an array to send to the view
        return [
            // Current logged in user information
            'user' => [
                'userID' => $this->session->get(key: 'userID'), // User's ID number
                'name'   => $this->session->get(key: 'name'),   // User's full name
                'email'  => $this->session->get(key: 'email'), // User's email address
                'role'   => $this->session->get(key: 'role')   // User's role (teacher)
            ],
            'title' => 'Teacher Dashboard - MGOD LMS',  // Page title
            'totalCourses' => $totalCourses,           // Number of courses teacher has
            'totalStudents' => $totalStudents          // Number of students in teacher's courses
        ];
    }

    // Private function - This checks if user is logged in
    private function isLoggedIn(): bool
    {
        // Check if session has 'isLoggedIn' set to true
        // This returns true if logged in, false if not logged in
        return $this->session->get('isLoggedIn') === true;
    }
}
