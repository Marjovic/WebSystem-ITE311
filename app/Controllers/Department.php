<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\DepartmentModel;
use App\Models\InstructorModel;
use App\Models\UserModel;

class Department extends BaseController
{
    protected $session;
    protected $validation;
    protected $db;
    protected $departmentModel;
    protected $instructorModel;
    protected $userModel;

    /**
     * Constructor - Initialize models and dependencies
     */
    public function __construct()
    {
        $this->session = \Config\Services::session();
        $this->validation = \Config\Services::validation();
        $this->db = \Config\Database::connect();
        
        // Initialize models
        $this->departmentModel = new DepartmentModel();
        $this->instructorModel = new InstructorModel();
        $this->userModel = new UserModel();
    }

    public function index()
    {
        return redirect()->to(base_url('admin/manage_departments'));
    }

    /**
     * Manage Departments Method - Handles all department management operations
     * Supports create, edit, delete, and display operations
     */
    public function manageDepartments()
    {
        // Security check - only admins can access
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
        $departmentID = $this->request->getGet('id');

        // Route to appropriate action
        if ($action === 'create' && $this->request->getMethod() === 'POST') {
            return $this->createDepartment();
        }

        if ($action === 'edit' && $departmentID) {
            return $this->editDepartment($departmentID);
        }

        if ($action === 'delete' && $departmentID) {
            return $this->deleteDepartment($departmentID);
        }

        if ($action === 'toggle_status' && $departmentID) {
            return $this->toggleStatus($departmentID);
        }

        // Display department management interface
        return $this->displayDepartmentManagement();
    }

    /**
     * Create a new department
     */
    private function createDepartment()
    {
        // Validation rules
        $rules = [
            'department_code' => 'required|min_length[2]|max_length[20]|is_unique[departments.department_code]|regex_match[/^[A-Z0-9\-]+$/]',
            'department_name' => 'required|min_length[3]|max_length[150]',
            'description'     => 'permit_empty|max_length[500]',
            'head_user_id'    => 'permit_empty|integer'
        ];

        $messages = [
            'department_code' => [
                'required'    => 'Department code is required.',
                'min_length'  => 'Department code must be at least 2 characters.',
                'max_length'  => 'Department code must not exceed 20 characters.',
                'is_unique'   => 'This department code already exists.',
                'regex_match' => 'Department code must contain only uppercase letters, numbers, and hyphens.'
            ],
            'department_name' => [
                'required'   => 'Department name is required.',
                'min_length' => 'Department name must be at least 3 characters.',
                'max_length' => 'Department name must not exceed 150 characters.'
            ],
            'description' => [
                'max_length' => 'Description must not exceed 500 characters.'
            ]
        ];

        if (!$this->validate($rules, $messages)) {
            $this->session->setFlashdata('errors', $this->validation->getErrors());
            return redirect()->back()->withInput();
        }

        // Start database transaction
        $this->db->transStart();
        
        try {
            // Prepare department data
            $departmentData = [
                'department_code' => strtoupper($this->request->getPost('department_code')),
                'department_name' => $this->request->getPost('department_name'),
                'description'     => $this->request->getPost('description'),
                'head_user_id'    => $this->request->getPost('head_user_id') ?: null,
                'is_active'       => 1
            ];

            // Insert department
            if (!$this->departmentModel->insert($departmentData)) {
                throw new \Exception('Failed to create department.');
            }

            // Complete transaction
            $this->db->transComplete();
            
            if ($this->db->transStatus() === false) {
                throw new \Exception('Transaction failed.');
            }

            $this->session->setFlashdata('success', 'Department "' . esc($departmentData['department_name']) . '" created successfully!');
            return redirect()->to(base_url('admin/manage_departments'));

        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', 'Department creation failed: ' . $e->getMessage());
            $this->session->setFlashdata('error', 'Failed to create department: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    /**
     * Edit an existing department
     */
    private function editDepartment($departmentID)
    {
        // Find department
        $departmentToEdit = $this->departmentModel->find($departmentID);

        if (!$departmentToEdit) {
            $this->session->setFlashdata('error', 'Department not found.');
            return redirect()->to(base_url('admin/manage_departments'));
        }

        // Handle POST request
        if ($this->request->getMethod() === 'POST') {
            $rules = [
                'department_code' => "required|min_length[2]|max_length[20]|is_unique[departments.department_code,id,{$departmentID}]|regex_match[/^[A-Z0-9\-]+$/]",
                'department_name' => 'required|min_length[3]|max_length[150]',
                'description'     => 'permit_empty|max_length[500]',
                'head_user_id'    => 'permit_empty|integer'
            ];

            $messages = [
                'department_code' => [
                    'required'    => 'Department code is required.',
                    'is_unique'   => 'This department code already exists.',
                    'regex_match' => 'Department code must contain only uppercase letters, numbers, and hyphens.'
                ],
                'department_name' => [
                    'required' => 'Department name is required.'
                ]
            ];

            if (!$this->validate($rules, $messages)) {
                $this->session->setFlashdata('errors', $this->validation->getErrors());
                return redirect()->back()->withInput();
            }

            // Start transaction
            $this->db->transStart();
            
            try {
                // Update department data
                $updateData = [
                    'department_code' => strtoupper($this->request->getPost('department_code')),
                    'department_name' => $this->request->getPost('department_name'),
                    'description'     => $this->request->getPost('description'),
                    'head_user_id'    => $this->request->getPost('head_user_id') ?: null
                ];

                if (!$this->departmentModel->update($departmentID, $updateData)) {
                    throw new \Exception('Failed to update department.');
                }

                $this->db->transComplete();
                
                if ($this->db->transStatus() === false) {
                    throw new \Exception('Transaction failed.');
                }

                $this->session->setFlashdata('success', 'Department updated successfully!');
                return redirect()->to(base_url('admin/manage_departments'));

            } catch (\Exception $e) {
                $this->db->transRollback();
                log_message('error', 'Department update failed: ' . $e->getMessage());
                $this->session->setFlashdata('error', 'Failed to update department: ' . $e->getMessage());
                return redirect()->back()->withInput();
            }
        }

        // Get all departments and instructors for display
        $departments = $this->departmentModel->getDepartmentsWithHead();
        $instructors = $this->instructorModel->getInstructorsWithUser();
        
        $data = [
            'user' => [
                'userID' => $this->session->get('userID'),
                'name'   => $this->session->get('name'),
                'email'  => $this->session->get('email'),
                'role'   => $this->session->get('role')
            ],
            'title'             => 'Edit Department - Admin Dashboard',
            'departments'       => $departments,
            'instructors'       => $instructors,
            'editDepartment'    => $departmentToEdit,
            'showCreateForm'    => false,
            'showEditForm'      => true
        ];

        return view('admin/manage_departments', $data);
    }

    /**
     * Delete a department
     */
    private function deleteDepartment($departmentID)
    {
        $departmentToDelete = $this->departmentModel->find($departmentID);

        if (!$departmentToDelete) {
            $this->session->setFlashdata('error', 'Department not found.');
            return redirect()->to(base_url('admin/manage_departments'));
        }

        // Check if department has students, instructors, or courses
        if ($this->departmentModel->hasStudents($departmentID)) {
            $this->session->setFlashdata('error', 'Cannot delete department. It has enrolled students.');
            return redirect()->to(base_url('admin/manage_departments'));
        }

        if ($this->departmentModel->hasInstructors($departmentID)) {
            $this->session->setFlashdata('error', 'Cannot delete department. It has assigned instructors.');
            return redirect()->to(base_url('admin/manage_departments'));
        }

        if ($this->departmentModel->hasCourses($departmentID)) {
            $this->session->setFlashdata('error', 'Cannot delete department. It has associated courses.');
            return redirect()->to(base_url('admin/manage_departments'));
        }

        $this->db->transStart();
        
        try {
            if (!$this->departmentModel->delete($departmentID)) {
                throw new \Exception('Failed to delete department.');
            }

            $this->db->transComplete();
            
            if ($this->db->transStatus() === false) {
                throw new \Exception('Transaction failed.');
            }

            $this->session->setFlashdata('success', 'Department "' . esc($departmentToDelete['department_name']) . '" deleted successfully!');

        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', 'Department deletion failed: ' . $e->getMessage());
            $this->session->setFlashdata('error', 'Failed to delete department: ' . $e->getMessage());
        }

        return redirect()->to(base_url('admin/manage_departments'));
    }

    /**
     * Toggle department active status
     */
    private function toggleStatus($departmentID)
    {
        $department = $this->departmentModel->find($departmentID);

        if (!$department) {
            $this->session->setFlashdata('error', 'Department not found.');
            return redirect()->to(base_url('admin/manage_departments'));
        }

        try {
            $newStatus = $department['is_active'] ? 0 : 1;
            $this->departmentModel->update($departmentID, ['is_active' => $newStatus]);

            $statusText = $newStatus ? 'activated' : 'deactivated';
            $this->session->setFlashdata('success', 'Department "' . esc($department['department_name']) . '" ' . $statusText . ' successfully!');

        } catch (\Exception $e) {
            log_message('error', 'Department status toggle failed: ' . $e->getMessage());
            $this->session->setFlashdata('error', 'Failed to update department status.');
        }

        return redirect()->to(base_url('admin/manage_departments'));
    }

    /**
     * Display department management interface
     */
    private function displayDepartmentManagement()
    {
        // Get all departments with head information
        $departments = $this->departmentModel->getDepartmentsWithHead();

        // Get all instructors for dropdown
        $instructors = $this->instructorModel->getInstructorsWithUser();

        $data = [
            'user' => [
                'userID' => $this->session->get('userID'),
                'name'   => $this->session->get('name'),
                'email'  => $this->session->get('email'),
                'role'   => $this->session->get('role')
            ],
            'title'          => 'Manage Departments - Admin Dashboard',
            'departments'    => $departments,
            'instructors'    => $instructors,
            'editDepartment' => null,
            'showCreateForm' => $this->request->getGet('action') === 'create',
            'showEditForm'   => false
        ];
          
        return view('admin/manage_departments', $data);
    }

    /**
     * Search departments - AJAX endpoint
     * Accepts GET or POST requests with search term
     * Searches department_code, department_name, and description
     */
    public function search()
    {
        // Check if user is logged in and is admin
        if ($this->session->get('isLoggedIn') !== true || $this->session->get('role') !== 'admin') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Unauthorized access'
            ]);
        }

        // Get search term from GET or POST
        $searchTerm = $this->request->getGet('search') ?? $this->request->getPost('search');

        if (empty($searchTerm)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Search term is required'
            ]);
        }

        try {
            // Search departments using Query Builder with LIKE queries
            $results = $this->departmentModel->select('
                    departments.*,
                    CONCAT(users.first_name, " ", users.last_name) as head_name,
                    users.email as head_email
                ')
                ->join('instructors', 'instructors.id = departments.head_instructor_id', 'left')
                ->join('users', 'users.id = instructors.user_id', 'left')
                ->groupStart()
                    ->like('departments.department_code', $searchTerm)
                    ->orLike('departments.department_name', $searchTerm)
                    ->orLike('departments.description', $searchTerm)
                    ->orLike('users.first_name', $searchTerm)
                    ->orLike('users.last_name', $searchTerm)
                ->groupEnd()
                ->orderBy('departments.department_name', 'ASC')
                ->findAll();

            return $this->response->setJSON([
                'success' => true,
                'count' => count($results),
                'data' => $results,
                'search_term' => $searchTerm
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Department search error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred while searching departments'
            ]);
        }
    }
}
