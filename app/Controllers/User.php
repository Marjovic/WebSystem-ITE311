<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\UserModel;
use App\Models\StudentModel;
use App\Models\InstructorModel;
use App\Models\RoleModel;
use App\Models\YearLevelModel;

class User extends BaseController
{
    protected $session;
    protected $validation;
    protected $db;
    protected $userModel;
    protected $studentModel;
    protected $instructorModel;
    protected $roleModel;
    protected $yearLevelModel;

    /**
     * Constructor - Initialize models and dependencies
     */
    public function __construct()
    {
        $this->session = \Config\Services::session();
        $this->validation = \Config\Services::validation();
        $this->db = \Config\Database::connect();
        
        // Initialize models
        $this->userModel = new UserModel();
        $this->studentModel = new StudentModel();
        $this->instructorModel = new InstructorModel();
        $this->roleModel = new RoleModel();
        $this->yearLevelModel = new YearLevelModel();
    }

    public function index()
    {
        return redirect()->to(base_url('admin/manage_users'));
    }

    /**
     * Manage Users Method - Handles all user management operations
     * Supports create, edit, delete, and display operations
     */
    public function manageUsers()
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
        $userID = $this->request->getGet('id');
        $currentAdminID = $this->session->get('userID');

        // Route to appropriate action
        if ($action === 'create' && $this->request->getMethod() === 'POST') {
            return $this->createUser();
        }

        if ($action === 'edit' && $userID) {
            return $this->editUser($userID, $currentAdminID);
        }

        if ($action === 'delete' && $userID) {
            return $this->deleteUser($userID, $currentAdminID);
        }

        // Display user management interface
        return $this->displayUserManagement($currentAdminID);
    }

    /**
     * Create a new user (Admin/Instructor/Student)
     */
    private function createUser()
    {
        $role = $this->request->getPost('role');

        // Validation rules
        $rules = [
            'first_name' => 'required|min_length[2]|max_length[100]|regex_match[/^[a-zA-ZñÑ\s\-\.]+$/]',
            'last_name'  => 'required|min_length[2]|max_length[100]|regex_match[/^[a-zA-ZñÑ\s\-\.]+$/]',
            'middle_name'=> 'permit_empty|max_length[100]|regex_match[/^[a-zA-ZñÑ\s\-\.]+$/]',
            'suffix'     => 'permit_empty|max_length[10]|regex_match[/^[a-zA-Z\s\.]+$/]',
            'email'      => 'required|valid_email|is_unique[users.email]',
            'password'   => 'required|min_length[6]',
            'role'       => 'required|in_list[admin,instructor,student]'
        ];
        
        // Add role-specific validation
        if ($role === 'student') {
            $rules['year_level_id'] = 'required|integer';
            $rules['section'] = 'permit_empty|max_length[50]';
        } elseif ($role === 'instructor') {
            $rules['department_id'] = 'permit_empty|integer';
            $rules['specialization'] = 'permit_empty|max_length[255]';
        }

        $messages = [
            'first_name' => [
                'required'    => 'First name is required.',
                'min_length'  => 'First name must be at least 2 characters.',
                'regex_match' => 'First name can only contain letters, spaces, hyphens, and periods.'
            ],
            'last_name' => [
                'required'    => 'Last name is required.',
                'min_length'  => 'Last name must be at least 2 characters.',
                'regex_match' => 'Last name can only contain letters, spaces, hyphens, and periods.'
            ],
            'middle_name' => [
                'regex_match' => 'Middle name can only contain letters, spaces, hyphens, and periods.'
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
            'role' => [
                'required' => 'Role is required.',
                'in_list'  => 'Invalid role selected.'
            ]
        ];

        if (!$this->validate($rules, $messages)) {
            $this->session->setFlashdata('errors', $this->validation->getErrors());
            return redirect()->back()->withInput();
        }

        // Start database transaction
        $this->db->transStart();
        
        try {
            // Get role ID
            $roleRecord = $this->roleModel->where('role_name', ucfirst($role))->first();
            if (!$roleRecord) {
                throw new \Exception('Invalid role specified.');
            }

            // Prepare user data
            $userData = [
                'user_code'   => $this->generateUserCode($role),
                'first_name'  => $this->request->getPost('first_name'),
                'middle_name' => $this->request->getPost('middle_name'),
                'last_name'   => $this->request->getPost('last_name'),
                'suffix'      => $this->request->getPost('suffix'),
                'email'       => $this->request->getPost('email'),
                'password'    => $this->request->getPost('password'), // Will be hashed by UserModel
                'role_id'     => $roleRecord['id'],
                'is_active'   => 1,
                'email_verified_at' => date('Y-m-d H:i:s') // Auto-verify for admin-created accounts
            ];

            // Insert user
            $userID = $this->userModel->insert($userData);
            if (!$userID) {
                throw new \Exception('Failed to create user account.');
            }

            // Create role-specific record
            if ($role === 'student') {
                $studentData = [
                    'user_id'          => $userID,
                    'year_level_id'    => $this->request->getPost('year_level_id'),
                    'section'          => $this->request->getPost('section'),
                    'enrollment_date'  => date('Y-m-d'),
                    'enrollment_status'=> 'enrolled'
                ];
                
                if (!$this->studentModel->insert($studentData)) {
                    throw new \Exception('Failed to create student record.');
                }
            } elseif ($role === 'instructor') {
                $instructorData = [
                    'user_id'           => $userID,
                    'department_id'     => $this->request->getPost('department_id'),
                    'specialization'    => $this->request->getPost('specialization'),
                    'hire_date'         => date('Y-m-d'),
                    'employment_status' => 'full_time'
                ];
                
                if (!$this->instructorModel->insert($instructorData)) {
                    throw new \Exception('Failed to create instructor record.');
                }
            }

            // Complete transaction
            $this->db->transComplete();
            
            if ($this->db->transStatus() === false) {
                throw new \Exception('Transaction failed.');
            }

            // Log activity
            $this->logActivity('user_creation', [
                'name' => $userData['first_name'] . ' ' . $userData['last_name'],
                'role' => $role
            ]);

            $this->session->setFlashdata('success', ucfirst($role) . ' created successfully!');
            return redirect()->to(base_url('admin/manage_users'));

        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', 'User creation failed: ' . $e->getMessage());
            $this->session->setFlashdata('error', 'Failed to create user: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    /**
     * Edit an existing user
     */
    private function editUser($userID, $currentAdminID)
    {
        // Find user with role information
        $userToEdit = $this->userModel->find($userID);

        if (!$userToEdit) {
            $this->session->setFlashdata('error', 'User not found.');
            return redirect()->to(base_url('admin/manage_users'));
        }

        if ($userToEdit['id'] == $currentAdminID) {
            $this->session->setFlashdata('error', 'You cannot edit your own account.');
            return redirect()->to(base_url('admin/manage_users'));
        }

        // Handle POST request
        if ($this->request->getMethod() === 'POST') {
            $role = $this->request->getPost('role');

            $rules = [
                'first_name' => 'required|min_length[2]|max_length[100]|regex_match[/^[a-zA-ZñÑ\s\-\.]+$/]',
                'last_name'  => 'required|min_length[2]|max_length[100]|regex_match[/^[a-zA-ZñÑ\s\-\.]+$/]',
                'middle_name'=> 'permit_empty|max_length[100]|regex_match[/^[a-zA-ZñÑ\s\-\.]+$/]',
                'suffix'     => 'permit_empty|max_length[10]|regex_match[/^[a-zA-Z\s\.]+$/]',
                'email'      => "required|valid_email|is_unique[users.email,id,{$userID}]",
                'role'       => 'required|in_list[admin,instructor,student]'
            ];

            if ($this->request->getPost('password')) {
                $rules['password'] = 'min_length[6]';
            }
            
            // Add role-specific validation
            if ($role === 'student') {
                $rules['year_level_id'] = 'required|integer';
            } elseif ($role === 'instructor') {
                $rules['department_id'] = 'permit_empty|integer';
            }

            if (!$this->validate($rules)) {
                $this->session->setFlashdata('errors', $this->validation->getErrors());
                return redirect()->back()->withInput();
            }

            // Start transaction
            $this->db->transStart();
            
            try {
                // Get role ID
                $roleRecord = $this->roleModel->where('role_name', ucfirst($role))->first();
                if (!$roleRecord) {
                    throw new \Exception('Invalid role specified.');
                }

                // Update user data
                $updateData = [
                    'first_name'  => $this->request->getPost('first_name'),
                    'middle_name' => $this->request->getPost('middle_name'),
                    'last_name'   => $this->request->getPost('last_name'),
                    'suffix'      => $this->request->getPost('suffix'),
                    'email'       => $this->request->getPost('email'),
                    'role_id'     => $roleRecord['id']
                ];

                if ($this->request->getPost('password')) {
                    $updateData['password'] = $this->request->getPost('password');
                }

                if (!$this->userModel->update($userID, $updateData)) {
                    throw new \Exception('Failed to update user.');
                }

                // Handle role changes
                $oldRole = $this->getRoleName($userToEdit['role_id']);
                
                // If role changed, delete old role record and create new one
                if ($oldRole !== $role) {
                    if ($oldRole === 'student') {
                        $this->studentModel->where('user_id', $userID)->delete();
                    } elseif ($oldRole === 'instructor') {
                        $this->instructorModel->where('user_id', $userID)->delete();
                    }

                    // Create new role record
                    if ($role === 'student') {
                        $studentData = [
                            'user_id'          => $userID,
                            'year_level_id'    => $this->request->getPost('year_level_id'),
                            'section'          => $this->request->getPost('section'),
                            'enrollment_date'  => date('Y-m-d'),
                            'enrollment_status'=> 'enrolled'
                        ];
                        $this->studentModel->insert($studentData);
                    } elseif ($role === 'instructor') {
                        $instructorData = [
                            'user_id'           => $userID,
                            'department_id'     => $this->request->getPost('department_id'),
                            'specialization'    => $this->request->getPost('specialization'),
                            'hire_date'         => date('Y-m-d'),
                            'employment_status' => 'full_time'
                        ];
                        $this->instructorModel->insert($instructorData);
                    }
                } else {
                    // Update existing role record
                    if ($role === 'student') {
                        $studentRecord = $this->studentModel->where('user_id', $userID)->first();
                        if ($studentRecord) {
                            $this->studentModel->update($studentRecord['id'], [
                                'year_level_id' => $this->request->getPost('year_level_id'),
                                'section'       => $this->request->getPost('section')
                            ]);
                        }
                    } elseif ($role === 'instructor') {
                        $instructorRecord = $this->instructorModel->where('user_id', $userID)->first();
                        if ($instructorRecord) {
                            $this->instructorModel->update($instructorRecord['id'], [
                                'department_id'  => $this->request->getPost('department_id'),
                                'specialization' => $this->request->getPost('specialization')
                            ]);
                        }
                    }
                }

                $this->db->transComplete();
                
                if ($this->db->transStatus() === false) {
                    throw new \Exception('Transaction failed.');
                }

                // Log activity
                $this->logActivity('user_update', [
                    'name' => $updateData['first_name'] . ' ' . $updateData['last_name'],
                    'role' => $role
                ]);

                $this->session->setFlashdata('success', 'User updated successfully!');
                return redirect()->to(base_url('admin/manage_users'));

            } catch (\Exception $e) {
                $this->db->transRollback();
                log_message('error', 'User update failed: ' . $e->getMessage());
                $this->session->setFlashdata('error', 'Failed to update user: ' . $e->getMessage());
                return redirect()->back()->withInput();
            }
        }        // Get year levels for dropdown
        $yearLevels = $this->yearLevelModel->orderBy('year_level_order')->findAll();

        // Get role-specific data
        $roleName = $this->getRoleName($userToEdit['role_id']);
        
        // Add role_name to userToEdit for the view
        $userToEdit['role_name'] = ucfirst($roleName);
        
        $roleSpecificData = null;
        if ($roleName === 'student') {
            $roleSpecificData = $this->studentModel->where('user_id', $userID)->first();
        } elseif ($roleName === 'instructor') {
            $roleSpecificData = $this->instructorModel->where('user_id', $userID)->first();
        }

        // Get all users for display
        $users = $this->getUsersWithRoles();
        
        $data = [
            'user' => [
                'userID' => $this->session->get('userID'),
                'name'   => $this->session->get('name'),
                'email'  => $this->session->get('email'),
                'role'   => $this->session->get('role')
            ],
            'title'             => 'Edit User - Admin Dashboard',
            'users'             => $users,
            'currentAdminID'    => $currentAdminID,
            'editUser'          => $userToEdit,
            'roleSpecificData'  => $roleSpecificData,
            'yearLevels'        => $yearLevels,
            'showCreateForm'    => false,
            'showEditForm'      => true
        ];

        return view('admin/manage_users', $data);
    }

    /**
     * Delete a user (soft delete)
     */
    private function deleteUser($userID, $currentAdminID)
    {
        $userToDelete = $this->userModel->find($userID);

        if (!$userToDelete) {
            $this->session->setFlashdata('error', 'User not found.');
            return redirect()->to(base_url('admin/manage_users'));
        }

        if ($userToDelete['id'] == $currentAdminID) {
            $this->session->setFlashdata('error', 'You cannot delete your own account.');
            return redirect()->to(base_url('admin/manage_users'));
        }

        $this->db->transStart();
        
        try {
            // Get role to delete associated records
            $roleName = $this->getRoleName($userToDelete['role_id']);
            
            // Soft delete role-specific record
            if ($roleName === 'student') {
                $this->studentModel->where('user_id', $userID)->delete();
            } elseif ($roleName === 'instructor') {
                $this->instructorModel->where('user_id', $userID)->delete();
            }

            // Soft delete user
            if (!$this->userModel->delete($userID)) {
                throw new \Exception('Failed to delete user.');
            }

            $this->db->transComplete();
            
            if ($this->db->transStatus() === false) {
                throw new \Exception('Transaction failed.');
            }

            // Log activity
            $this->logActivity('user_deletion', [
                'name' => $userToDelete['first_name'] . ' ' . $userToDelete['last_name'],
                'id'   => $userToDelete['id']
            ]);

            $this->session->setFlashdata('success', 'User deleted successfully! (Soft delete - data preserved)');

        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', 'User deletion failed: ' . $e->getMessage());
            $this->session->setFlashdata('error', 'Failed to delete user: ' . $e->getMessage());
        }

        return redirect()->to(base_url('admin/manage_users'));
    }

    /**
     * Display user management interface
     */
    private function displayUserManagement($currentAdminID)
    {
        // Get all users with role information
        $users = $this->getUsersWithRoles();

        // Get year levels for dropdown
        $yearLevels = $this->yearLevelModel->orderBy('year_level_order')->findAll();        $data = [
            'user' => [
                'userID' => $this->session->get('userID'),
                'name'   => $this->session->get('name'),
                'email'  => $this->session->get('email'),
                'role'   => $this->session->get('role')
            ],
            'title'          => 'Manage Users - Admin Dashboard',
            'users'          => $users,
            'yearLevels'     => $yearLevels,
            'currentAdminID' => $currentAdminID,
            'editUser'       => null,
            'showCreateForm' => $this->request->getGet('action') === 'create',
            'showEditForm'   => false
        ];
          
        return view('admin/manage_users', $data);
    }    /**
     * Get all users with their role names
     */
    private function getUsersWithRoles()
    {
        return $this->userModel
            ->select('users.*, roles.role_name, year_levels.year_level_name, students.section, students.student_id_number')
            ->join('roles', 'roles.id = users.role_id')
            ->join('students', 'students.user_id = users.id', 'left')
            ->join('year_levels', 'year_levels.id = students.year_level_id', 'left')
            ->orderBy('users.created_at', 'DESC')
            ->findAll();
    }

    /**
     * Get role name from role ID
     */
    private function getRoleName($roleId)
    {
        $role = $this->roleModel->find($roleId);
        return $role ? strtolower($role['role_name']) : 'student';
    }

    /**
     * Generate unique user code based on role
     */
    private function generateUserCode($role)
    {
        $prefix = strtoupper(substr($role, 0, 3));
        $year = date('Y');
        $random = rand(1000, 9999);
        return "{$prefix}-{$year}-{$random}";
    }

    /**
     * Log user management activity
     */
    private function logActivity($type, $data)
    {
        $icons = [
            'user_creation' => '➕',
            'user_update'   => '✏️',
            'user_deletion' => '🗑️'
        ];

        $titles = [
            'user_creation' => 'New User Created',
            'user_update'   => 'User Account Updated',
            'user_deletion' => 'User Account Deleted'
        ];

        $activity = [
            'type'        => $type,
            'icon'        => $icons[$type] ?? '📝',
            'title'       => $titles[$type] ?? 'User Activity',
            'description' => $this->getActivityDescription($type, $data),
            'time'        => date('Y-m-d H:i:s'),
            'user_name'   => $data['name'] ?? 'Unknown',
            'user_role'   => $data['role'] ?? 'unknown',
            'created_by'  => $this->session->get('name')
        ];

        $activityKey = $type === 'user_creation' ? 'creation_activities' : 
                      ($type === 'user_update' ? 'update_activities' : 'deletion_activities');

        $activities = $this->session->get($activityKey) ?? [];
        array_unshift($activities, $activity);
        $activities = array_slice($activities, 0, 10);
        $this->session->set($activityKey, $activities);
    }

    /**
     * Get activity description
     */
    private function getActivityDescription($type, $data)
    {
        switch ($type) {
            case 'user_creation':
                return esc($data['name']) . ' (' . ucfirst($data['role']) . ') account was created by admin';
            case 'user_update':
                return esc($data['name']) . ' (' . ucfirst($data['role']) . ') account was updated by admin';
            case 'user_deletion':
                return esc($data['name']) . ' (ID: ' . $data['id'] . ') account was soft deleted from the system';
            default:
                return 'User activity logged';
        }
    }
}
