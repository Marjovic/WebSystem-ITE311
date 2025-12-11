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

                // Set validation rules with current course ID for update
                $this->courseModel->setValidationRules([
                    'course_code'   => "required|string|max_length[20]|is_unique[courses.course_code,id,{$courseID}]",
                    'title'         => 'required|string|max_length[255]',
                    'description'   => 'permit_empty|string',
                    'credits'       => 'required|integer',
                    'lecture_hours' => 'permit_empty|decimal',
                    'lab_hours'     => 'permit_empty|decimal',
                    'department_id' => 'permit_empty|integer',
                    'category_id'   => 'permit_empty|integer',
                    'year_level_id' => 'permit_empty|integer',
                    'is_active'     => 'permit_empty|in_list[0,1]'
                ]);

                // Use CourseModel to update with validation
                if ($this->courseModel->update($courseID, $updateData)) {
                    $this->session->setFlashdata('success', 'Course updated successfully!');
                    return redirect()->to(base_url('admin/manage_courses'));
                } else {
                    $this->session->setFlashdata('errors', $this->courseModel->errors());
                    $this->session->setFlashdata('error', 'Failed to update course. Please check the errors below.');
                    return redirect()->to(base_url('admin/manage_courses?action=edit&id=' . $courseID))->withInput();
                }
            }// Show edit form
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

    /**
     * Enroll student in a course offering (AJAX endpoint)
     */
    public function enroll()
    {
        // Check if request is AJAX
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid request method'
            ]);
        }
        
        // Check if user is logged in and is a student
        if ($this->session->get('isLoggedIn') !== true || $this->session->get('role') !== 'student') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'You must be logged in as a student to enroll in courses.',
                'error_code' => 'NOT_AUTHORIZED'
            ]);
        }
        
        $userID = $this->session->get('userID');
        
        // Get student record
        $studentModel = new \App\Models\StudentModel();
        $student = $studentModel->where('user_id', $userID)->first();
        
        if (!$student) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Student record not found. Please contact administrator.',
                'error_code' => 'STUDENT_NOT_FOUND'
            ]);
        }
        
        $studentId = $student['id'];
        $courseOfferingId = $this->request->getPost('course_id'); // Note: This is actually course_offering_id from the view
        
        // Validate course offering ID
        if (!$courseOfferingId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Course offering ID is required.',
                'error_code' => 'MISSING_COURSE_ID'
            ]);
        }
        
        // Check if course offering exists and is open
        $courseOffering = $this->courseOfferingModel->find($courseOfferingId);
        
        if (!$courseOffering) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Course offering not found.',
                'error_code' => 'OFFERING_NOT_FOUND'
            ]);
        }
        
        if ($courseOffering['status'] !== 'open') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'This course offering is not currently accepting enrollments.',
                'error_code' => 'OFFERING_CLOSED'
            ]);
        }
        
        // Check if student is already enrolled
        $existingEnrollment = $this->enrollmentModel
            ->where('student_id', $studentId)
            ->where('course_offering_id', $courseOfferingId)
            ->first();
        
        if ($existingEnrollment) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'You are already enrolled in this course offering.',
                'error_code' => 'ALREADY_ENROLLED'
            ]);
        }
        
        // Check if course offering has available slots
        if (!$this->courseOfferingModel->hasAvailableSlots($courseOfferingId)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'This course offering is full. No available slots.',
                'error_code' => 'OFFERING_FULL'
            ]);
        }
        
        // Create enrollment
        $enrollmentData = [
            'student_id' => $studentId,
            'course_offering_id' => $courseOfferingId,
            'enrollment_date' => date('Y-m-d'),
            'enrollment_status' => 'enrolled',
            'enrollment_type' => 'regular',
            'year_level_id' => $student['year_level_id'],
            'payment_status' => 'unpaid',
            'enrolled_by' => $userID
        ];
        
        $db = \Config\Database::connect();
        $db->transStart();
        
        try {
            // Insert enrollment
            if (!$this->enrollmentModel->insert($enrollmentData)) {
                throw new \Exception('Failed to create enrollment record');
            }
            
            // Increment enrollment count in course offering
            if (!$this->courseOfferingModel->incrementEnrollment($courseOfferingId)) {
                throw new \Exception('Failed to update enrollment count');
            }
            
            $db->transComplete();
            
            if ($db->transStatus() === false) {
                throw new \Exception('Transaction failed');
            }
            
            // Get course details for response
            $offeringDetails = $this->courseOfferingModel->getOfferingWithDetails($courseOfferingId);
            
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Successfully enrolled in ' . $offeringDetails['course_code'] . ' - ' . $offeringDetails['course_title'],
                'data' => [
                    'course_code' => $offeringDetails['course_code'],
                    'course_title' => $offeringDetails['course_title'],
                    'section' => $offeringDetails['section'],
                    'term' => $offeringDetails['term_name']
                ],
                'csrf_hash' => csrf_hash() // Return new CSRF token
            ]);
            
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Enrollment error: ' . $e->getMessage());
            
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred while processing your enrollment. Please try again.',
                'error_code' => 'ENROLLMENT_ERROR'
            ]);
        }
    }

}
