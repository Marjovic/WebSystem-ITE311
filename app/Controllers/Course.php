<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\EnrollmentModel;
use App\Models\CourseModel;
use App\Models\CourseOfferingModel;
use App\Models\CourseInstructorModel;
use App\Models\CourseScheduleModel;
use App\Models\CoursePrerequisiteModel;
use App\Models\YearLevelModel;
use App\Models\SemesterModel;
use App\Models\TermModel;
use App\Models\AcademicYearModel;
use App\Models\DepartmentModel;
use App\Models\CategoryModel;
use App\Models\NotificationModel;
use App\Models\UserModel;
use App\Models\RoleModel;

class Course extends BaseController
{
    protected $session;
    protected $validation;
    protected $db;
    
    // Models
    protected $enrollmentModel;
    protected $courseModel;
    protected $courseOfferingModel;
    protected $courseInstructorModel;
    protected $courseScheduleModel;
    protected $coursePrerequisiteModel;
    protected $yearLevelModel;
    protected $semesterModel;
    protected $termModel;
    protected $academicYearModel;
    protected $departmentModel;
    protected $categoryModel;
    protected $notificationModel;
    protected $userModel;
    protected $roleModel;

    public function __construct()
    {
        // Initialize session service
        $this->session = \Config\Services::session();
        
        // Initialize validation service
        $this->validation = \Config\Services::validation();
        
        // Initialize database connection
        $this->db = \Config\Database::connect();
        
        // Initialize all models
        $this->enrollmentModel = new EnrollmentModel();
        $this->courseModel = new CourseModel();
        $this->courseOfferingModel = new CourseOfferingModel();
        $this->courseInstructorModel = new CourseInstructorModel();
        $this->courseScheduleModel = new CourseScheduleModel();
        $this->coursePrerequisiteModel = new CoursePrerequisiteModel();
        $this->yearLevelModel = new YearLevelModel();
        $this->semesterModel = new SemesterModel();
        $this->termModel = new TermModel();
        $this->academicYearModel = new AcademicYearModel();        $this->departmentModel = new DepartmentModel();
        $this->categoryModel = new CategoryModel();
        $this->notificationModel = new NotificationModel();
        $this->userModel = new UserModel();
        $this->roleModel = new RoleModel();
    }

    // Manage Courses Method - Consolidated method for all course management operations  
    public function manageCourses()
    {
        // Security check - only admins can access this page
        if ($this->session->get('isLoggedIn') !== true) {
            $this->session->setFlashdata('error', 'Please login to access this page.');
            return redirect()->to(base_url('login'));        
        }
        
        if ($this->session->get('role') !== 'admin') {
            $this->session->setFlashdata('error', 'Access denied. You do not have permission to access this page.');
            $userRole = $this->session->get('role');
            return redirect()->to(base_url($userRole . '/dashboard'));
        }

        $action = $this->request->getGet('action');
        $courseID = $this->request->getGet('id');
          // ===== CREATE COURSE =====
        if ($action === 'create' && $this->request->getMethod() === 'POST') {
            // Build course data array
            $courseData = [
                'course_code' => $this->request->getPost('course_code'),
                'title' => $this->request->getPost('title'),
                'description' => $this->request->getPost('description'),
                'credits' => $this->request->getPost('credits') ?: 3,
                'lecture_hours' => $this->request->getPost('lecture_hours'),
                'lab_hours' => $this->request->getPost('lab_hours'),
                'department_id' => $this->request->getPost('department_id') ?: null,
                'category_id' => $this->request->getPost('category_id') ?: null,
                'year_level_id' => $this->request->getPost('year_level_id') ?: null,
                'is_active' => $this->request->getPost('is_active', FILTER_VALIDATE_BOOLEAN) ? 1 : 0
            ];

            // Use CourseModel to insert with validation
            if ($this->courseModel->insert($courseData)) {
                $this->session->setFlashdata('success', 'Course created successfully!');
                return redirect()->to(base_url('admin/manage_courses'));
            } else {
                $this->session->setFlashdata('errors', $this->courseModel->errors());
                $this->session->setFlashdata('error', 'Failed to create course. Please check the errors below.');
                return redirect()->to(base_url('admin/manage_courses?action=create'))->withInput();
            }
        }
        
        // ===== EDIT COURSE =====
        if ($action === 'edit' && $courseID) {
            // Get the course to edit
            $courseToEdit = $this->courseModel->find($courseID);

            if (!$courseToEdit) {
                $this->session->setFlashdata('error', 'Course not found.');
                return redirect()->to(base_url('admin/manage_courses'));
            }            // Handle POST request (update)
            if ($this->request->getMethod() === 'POST') {
                // Build update data array
                $updateData = [
                    'course_code' => $this->request->getPost('course_code'),
                    'title' => $this->request->getPost('title'),
                    'description' => $this->request->getPost('description'),
                    'credits' => $this->request->getPost('credits') ?: 3,
                    'lecture_hours' => $this->request->getPost('lecture_hours'),
                    'lab_hours' => $this->request->getPost('lab_hours'),
                    'department_id' => $this->request->getPost('department_id') ?: null,
                    'category_id' => $this->request->getPost('category_id') ?: null,
                    'year_level_id' => $this->request->getPost('year_level_id') ?: null,
                    'is_active' => $this->request->getPost('is_active', FILTER_VALIDATE_BOOLEAN) ? 1 : 0
                ];

                // Use CourseModel to update with validation
                if ($this->courseModel->update($courseID, $updateData)) {
                    $this->session->setFlashdata('success', 'Course updated successfully!');
                    return redirect()->to(base_url('admin/manage_courses'));
                } else {
                    $this->session->setFlashdata('errors', $this->courseModel->errors());
                    $this->session->setFlashdata('error', 'Failed to update course. Please check the errors below.');
                    return redirect()->to(base_url('admin/manage_courses?action=edit&id=' . $courseID))->withInput();
                }
            }            // Show edit form
            $data = [
                'user' => [
                    'role'   => $this->session->get('role')
                ],
                'title' => 'Edit Course - Admin Dashboard',
                'courses' => [],
                'departments' => $this->departmentModel->findAll(),
                'categories' => $this->categoryModel->findAll(),
                'yearLevels' => $this->yearLevelModel->findAll(),
                'editCourse' => $courseToEdit,
                'showCreateForm' => false,
                'showEditForm' => true
            ];
            return view('admin/manage_courses', $data);
        }
        
        // ===== DELETE COURSE =====
        if ($action === 'delete' && $courseID) {
            $courseToDelete = $this->courseModel->find($courseID);

            if (!$courseToDelete) {
                $this->session->setFlashdata('error', 'Course not found.');
                return redirect()->to(base_url('admin/manage_courses'));
            }

            if ($this->courseModel->delete($courseID)) {
                $this->session->setFlashdata('success', 'Course deleted successfully!');
            } else {
                $this->session->setFlashdata('error', 'Failed to delete course. Please try again.');
            }

            return redirect()->to(base_url('admin/manage_courses'));
        }
        
        // ===== SHOW COURSE MANAGEMENT INTERFACE =====
        // Get all courses with details
        $courses = $this->courseModel->getAllCoursesWithDetails();        $data = [
            'user' => [
                'userID' => $this->session->get('userID'),
                'name'   => $this->session->get('name'),
                'email'  => $this->session->get('email'),
                'role'   => $this->session->get('role')
            ],
            'title' => 'Manage Courses - Admin Dashboard',
            'courses' => $courses,
            'departments' => $this->departmentModel->findAll(),
            'categories' => $this->categoryModel->findAll(),
            'yearLevels' => $this->yearLevelModel->findAll(),
            'editCourse' => null,
            'showCreateForm' => $this->request->getGet('action') === 'create',
            'showEditForm' => false
        ];
        
        return view('admin/manage_courses', $data);
    }

}
