<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'user_code',
        'email',
        'password',
        'name',
        'first_name',
        'middle_name',
        'last_name',
        'date_of_birth',
        'gender',
        'contact_number',
        'address',
        'role_id',
        'department_id',
        'year_level_id',
        'profile_picture',
        'is_active',
        'last_login',
        'email_verified_at'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules = [
        'user_code'      => 'required|string|max_length[50]|is_unique[users.user_code,id,{id}]',
        'email'          => 'required|valid_email|max_length[150]|is_unique[users.email,id,{id}]',
        'password'       => 'required|min_length[8]',
        'name'           => 'required|string|max_length[255]',
        'first_name'     => 'required|string|max_length[100]',
        'last_name'      => 'required|string|max_length[100]',
        'gender'         => 'permit_empty|in_list[Male,Female,Other]',
        'contact_number' => 'permit_empty|string|max_length[20]',
        'role_id'        => 'required|integer',
        'department_id'  => 'permit_empty|integer',
        'year_level_id'  => 'permit_empty|integer'
    ];

    protected $validationMessages = [
        'user_code' => [
            'required'  => 'User code is required',
            'is_unique' => 'This user code already exists'
        ],
        'email' => [
            'required'    => 'Email is required',
            'valid_email' => 'Please provide a valid email address',
            'is_unique'   => 'This email is already registered'
        ],
        'password' => [
            'required'   => 'Password is required',
            'min_length' => 'Password must be at least 8 characters'
        ]
    ];

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = ['hashPassword'];
    protected $beforeUpdate   = ['hashPassword'];

    /**
     * Hash password before saving
     */
    protected function hashPassword(array $data)
    {
        if (isset($data['data']['password'])) {
            $data['data']['password'] = password_hash($data['data']['password'], PASSWORD_DEFAULT);
        }
        return $data;
    }

    /**
     * Get user with role information
     */
    public function getUserWithRole($userId)
    {
        return $this->select('users.*, roles.role_name')
                    ->join('roles', 'roles.id = users.role_id')
                    ->find($userId);
    }

    /**
     * Get user with all relationships
     */
    public function getUserComplete($userId)
    {
        return $this->select('
                users.*, 
                roles.role_name,
                departments.department_name,
                year_levels.year_level_name
            ')
            ->join('roles', 'roles.id = users.role_id', 'left')
            ->join('departments', 'departments.id = users.department_id', 'left')
            ->join('year_levels', 'year_levels.id = users.year_level_id', 'left')
            ->find($userId);
    }

    /**
     * Get all users by role
     */
    public function getUsersByRole($roleId)
    {
        return $this->where('role_id', $roleId)
                    ->where('is_active', 1)
                    ->findAll();
    }

    /**
     * Get all students
     */
    public function getStudents()
    {
        return $this->select('users.*, year_levels.year_level_name, departments.department_name')
                    ->join('roles', 'roles.id = users.role_id')
                    ->join('year_levels', 'year_levels.id = users.year_level_id', 'left')
                    ->join('departments', 'departments.id = users.department_id', 'left')
                    ->where('roles.role_name', 'Student')
                    ->where('users.is_active', 1)
                    ->findAll();
    }

    /**
     * Get all instructors
     */
    public function getInstructors()
    {
        return $this->select('users.*, departments.department_name')
                    ->join('roles', 'roles.id = users.role_id')
                    ->join('departments', 'departments.id = users.department_id', 'left')
                    ->whereIn('roles.role_name', ['Teacher', 'Instructor'])
                    ->where('users.is_active', 1)
                    ->findAll();
    }

    /**
     * Verify user credentials
     */
    public function verifyCredentials($email, $password)
    {
        $user = $this->where('email', $email)
                     ->where('is_active', 1)
                     ->first();

        if ($user && password_verify($password, $user['password'])) {
            // Update last login
            $this->update($user['id'], ['last_login' => date('Y-m-d H:i:s')]);
            return $user;
        }

        return false;
    }

    /**
     * Get user by email
     */
    public function getUserByEmail($email)
    {
        return $this->where('email', $email)->first();
    }

    /**
     * Get user by user code
     */
    public function getUserByCode($userCode)
    {
        return $this->where('user_code', $userCode)->first();
    }

    /**
     * Update user profile
     */
    public function updateProfile($userId, $data)
    {
        // Remove password from data if empty
        if (isset($data['password']) && empty($data['password'])) {
            unset($data['password']);
        }

        return $this->update($userId, $data);
    }

    /**
     * Check if user has role
     */
    public function hasRole($userId, $roleName)
    {
        $user = $this->select('users.*, roles.role_name')
                     ->join('roles', 'roles.id = users.role_id')
                     ->find($userId);

        return $user && $user['role_name'] === $roleName;
    }

    /**
     * Get students by department and year level
     */
    public function getStudentsByDepartmentAndYear($departmentId, $yearLevelId)
    {
        return $this->select('users.*')
                    ->join('roles', 'roles.id = users.role_id')
                    ->where('roles.role_name', 'Student')
                    ->where('users.department_id', $departmentId)
                    ->where('users.year_level_id', $yearLevelId)
                    ->where('users.is_active', 1)
                    ->findAll();
    }
}
