<?php

namespace App\Models;

use CodeIgniter\Model;

class MaterialModel extends Model
{
    protected $table            = 'materials';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['course_id', 'file_name', 'file_path', 'created_at'];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules = [
        'course_id' => 'required|integer|greater_than[0]',
        'file_name' => 'required|max_length[255]',
        'file_path' => 'required|max_length[255]'
    ];
    
    protected $validationMessages = [
        'course_id' => [
            'required' => 'Course ID is required',
            'integer' => 'Course ID must be an integer',
            'greater_than' => 'Course ID must be greater than 0'
        ],
        'file_name' => [
            'required' => 'File name is required',
            'max_length' => 'File name cannot exceed 255 characters'
        ],
        'file_path' => [
            'required' => 'File path is required',
            'max_length' => 'File path cannot exceed 255 characters'
        ]
    ];
    
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    /**
     * Insert a new material record
     * 
     * @param array $data Material data containing course_id, file_name, file_path
     * @return int|bool Insert ID on success, false on failure
     */
    public function insertMaterial($data)
    {
        try {
            // Ensure created_at is set to current timestamp
            if (!isset($data['created_at'])) {
                $data['created_at'] = date('Y-m-d H:i:s');
            }

            // Insert the material record
            $result = $this->insert($data);
            
            if ($result) {
                return $this->getInsertID();
            }
            
            return false;
        } catch (\Exception $e) {
            log_message('error', 'MaterialModel::insertMaterial() - ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all materials for a specific course
     * 
     * @param int $course_id Course ID
     * @return array Array of materials ordered by creation date (newest first)
     */
    public function getMaterialsByCourse($course_id)
    {
        try {
            return $this->where('course_id', $course_id)
                        ->orderBy('created_at', 'DESC')
                        ->findAll();
        } catch (\Exception $e) {
            log_message('error', 'MaterialModel::getMaterialsByCourse() - ' . $e->getMessage());
            return [];
        }
    }
}
