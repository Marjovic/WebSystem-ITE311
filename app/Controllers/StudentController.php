<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class StudentController extends BaseController
{
    protected $session;
    protected $db;

    public function __construct()
    {
        $this->session = \Config\Services::session();
        $this->db = \Config\Database::connect();
    }

    public function index()
    {
        //
    }

    public function dashboard()
    {
        // Check if user is logged in and has student role
        if (!$this->isLoggedIn() || $this->session->get('role') !== 'student') {
            $this->session->setFlashdata('error', 'Access denied. Student privileges required.');
            return redirect()->to(base_url('login'));
        }

        // Get dashboard data
        $data = $this->getDashboardData();
        
        return view('student/dashboard', $data);
    }

    private function getDashboardData()
    {
        // For now, we'll use mock data since we don't have courses table yet
        $enrolledCourses = 3; // Mock data
        $completedAssignments = 8; // Mock data
        $pendingAssignments = 2; // Mock data
        
        return [
            'user' => [
                'userID' => $this->session->get('userID'),
                'name'   => $this->session->get('name'),
                'email'  => $this->session->get('email'),
                'role'   => $this->session->get('role')
            ],
            'title' => 'Student Dashboard - MGOD LMS',
            'enrolledCourses' => $enrolledCourses,
            'completedAssignments' => $completedAssignments,
            'pendingAssignments' => $pendingAssignments
        ];
    }

    private function isLoggedIn(): bool
    {
        return $this->session->get('isLoggedIn') === true;
    }
}
