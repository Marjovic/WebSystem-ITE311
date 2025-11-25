<?php

namespace App\Models;

use CodeIgniter\Model;

class DepartmentModel extends Model
{
    protected $table            = 'departments';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'department_code',
        'department_name',
        'description',
        'head_user_id',
        'is_active'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'department_code' => 'required|string|max_length[20]|is_unique[departments.department_code,id,{id}]',
        'department_name' => 'required|string|max_length[150]',
        'description'     => 'permit_empty|string',
        'head_user_id'    => 'permit_empty|integer',
        'is_active'       => 'permit_empty|in_list[0,1]'
    ];

    protected $validationMessages = [
        'department_code' => [
            'required'  => 'Department code is required',
            'is_unique' => 'This department code already exists'
        ],
        'department_name' => [
            'required' => 'Department name is required'
        ]
    ];

    // Callbacks
    protected $allowCallbacks = true;

    /**
     * Get all departments with head information
     */
    public function getDepartmentsWithHead()
    {
        return $this->select('departments.*, users.name as head_name')
                    ->join('users', 'users.id = departments.head_user_id', 'left')
                    ->where('departments.is_active', 1)
                    ->findAll();
    }

    /**
     * Get department by code
     */
    public function getDepartmentByCode($code)
    {
        return $this->where('department_code', $code)->first();
    }

    /**
     * Assign department head
     */
    public function assignHead($departmentId, $userId)
    {
        return $this->update($departmentId, ['head_user_id' => $userId]);
    }

    /**
     * Get all courses in department
     */
    public function getDepartmentCourses($departmentId)
    {
        $db = \Config\Database::connect();
        return $db->table('courses')
                  ->where('department_id', $departmentId)
                  ->where('is_active', 1)
                  ->get()
                  ->getResultArray();
    }
}