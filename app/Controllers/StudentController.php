<?php

namespace App\Controllers;

use App\Controllers\BaseController;

// StudentController class - This handles all student-related functions
// Student is a user who can enroll in courses and submit assignments
class StudentController extends BaseController
{
    // These are class properties (variables that belong to this class)
    protected $session; // This stores user session data (login info)
    protected $db;      // This connects to the database

    // Constructor - This runs automatically when we create StudentController object
    public function __construct()
    {
        // Get the session service - this tracks if user is logged in
        $this->session = \Config\Services::session();
        
        // Connect to database - this lets us read/write data
        $this->db = \Config\Database::connect();
    }

    // Dashboard function - This shows the main student page
    public function dashboard()
    {
        // First, we check if user is allowed to see student page
        // We check two things: 1) Is user logged in? 2) Is user a student?
        if (!$this->isLoggedIn() || $this->session->get('role') !== 'student') {
            // If user is not student, show error message
            $this->session->setFlashdata('error', 'Access denied. Student privileges required.');
            
            // Send user back to login page
            return redirect()->to(uri: base_url(relativePath: 'login'));
        }

        // If user is student, get data to show on dashboard
        $data = $this->getDashboardData();
        
        // Show the student dashboard page with the data
        return view(name: 'student/dashboard', data: $data);
    }

    // Private function - This gets data to show on student dashboard
    private function getDashboardData()
    {
        // These are temporary hard-coded numbers
        // In real system, these would come from database
        $enrolledCourses = 0;      // Number of courses student is enrolled in
        $completedAssignments = 0; // Number of assignments student has completed
        $pendingAssignments = 0;   // Number of assignments student still needs to do
        
        // Return all data as an array to send to the view
        return [
            // Current logged in user information
            'user' => [
                'userID' => $this->session->get(key: 'userID'), // User's ID number
                'name'   => $this->session->get(key: 'name'),   // User's full name
                'email'  => $this->session->get(key: 'email'), // User's email address
                'role'   => $this->session->get(key: 'role')   // User's role (student)
            ],
            'title' => 'Student Dashboard - MGOD LMS',         // Page title
            'enrolledCourses' => $enrolledCourses,             // Courses student is taking
            'completedAssignments' => $completedAssignments,   // Finished assignments
            'pendingAssignments' => $pendingAssignments        // Assignments still to do
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
