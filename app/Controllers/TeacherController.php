<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class TeacherController extends BaseController
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
        // Check if user is logged in and has teacher role
        if (!$this->isLoggedIn() || $this->session->get('role') !== 'teacher') {
            $this->session->setFlashdata('error', 'Access denied. Teacher privileges required.');
            return redirect()->to(base_url('login'));
        }

        // Get dashboard data
        $data = $this->getDashboardData();
        
        return view('teacher/teacher', $data);
    }

    private function getDashboardData()
    {
        // For now, we'll use mock data since we don't have courses table yet
        $totalCourses = 5; // Mock data
        $totalStudents = 25; // Mock data
        
        return [
            'user' => [
                'userID' => $this->session->get('userID'),
                'name'   => $this->session->get('name'),
                'email'  => $this->session->get('email'),
                'role'   => $this->session->get('role')
            ],
            'title' => 'Teacher Dashboard - MGOD LMS',
            'totalCourses' => $totalCourses,
            'totalStudents' => $totalStudents
        ];
    }

    private function isLoggedIn(): bool
    {
        return $this->session->get('isLoggedIn') === true;
    }
}
