<?php

namespace App\Models;

use CodeIgniter\Model;

class CourseInstructorModel extends Model
{
    protected $table            = 'course_instructors';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'course_offering_id',
        'instructor_id',
        'is_primary',
        'assigned_date'
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
        'course_offering_id' => 'required|integer',
        'instructor_id'      => 'required|integer',
        'is_primary'         => 'permit_empty|in_list[0,1]',
        'assigned_date'      => 'permit_empty|valid_date'
    ];

    protected $validationMessages = [
        'course_offering_id' => [
            'required' => 'Course offering is required'
        ],
        'instructor_id' => [
            'required' => 'Instructor is required'
        ]
    ];

    // Callbacks
    protected $allowCallbacks = true;

    /**
     * Get all instructors for a course offering
     */
    public function getOfferingInstructors($offeringId)
    {
        return $this->select('
                course_instructors.*,
                users.name,
                users.email,
                departments.department_name
            ')
            ->join('users', 'users.id = course_instructors.instructor_id')
            ->join('departments', 'departments.id = users.department_id', 'left')
            ->where('course_instructors.course_offering_id', $offeringId)
            ->orderBy('course_instructors.is_primary', 'DESC')
            ->findAll();
    }

    /**
     * Get primary instructor for an offering
     */
    public function getPrimaryInstructor($offeringId)
    {
        return $this->select('
                course_instructors.*,
                users.name,
                users.email
            ')
            ->join('users', 'users.id = course_instructors.instructor_id')
            ->where('course_instructors.course_offering_id', $offeringId)
            ->where('course_instructors.is_primary', 1)
            ->first();
    }

    /**
     * Get all offerings for an instructor
     */
    public function getInstructorOfferings($instructorId, $termId = null)
    {
        $builder = $this->select('
                course_instructors.*,
                co.section,
                co.status,
                co.current_enrollment,
                co.max_students,
                c.course_code,
                c.title as course_title,
                t.term_name
            ')
            ->join('course_offerings co', 'co.id = course_instructors.course_offering_id')
            ->join('courses c', 'c.id = co.course_id')
            ->join('terms t', 't.id = co.term_id')
            ->where('course_instructors.instructor_id', $instructorId);
        
        if ($termId) {
            $builder->where('co.term_id', $termId);
        }
        
        return $builder->orderBy('course_instructors.is_primary', 'DESC')
                       ->findAll();
    }

    /**
     * Assign instructor to offering
     */
    public function assignInstructor($offeringId, $instructorId, $isPrimary = false)
    {
        // Check if already assigned
        $exists = $this->where('course_offering_id', $offeringId)
                       ->where('instructor_id', $instructorId)
                       ->first();
        
        if ($exists) {
            // Update existing assignment
            return $this->update($exists['id'], [
                'is_primary'    => $isPrimary,
                'assigned_date' => date('Y-m-d')
            ]);
        }
        
        // If setting as primary, remove primary flag from others
        if ($isPrimary) {
            $this->where('course_offering_id', $offeringId)
                 ->set('is_primary', 0)
                 ->update();
        }
        
        return $this->insert([
            'course_offering_id' => $offeringId,
            'instructor_id'      => $instructorId,
            'is_primary'         => $isPrimary,
            'assigned_date'      => date('Y-m-d')
        ]);
    }

    /**
     * Remove instructor from offering
     */
    public function removeInstructor($offeringId, $instructorId)
    {
        return $this->where('course_offering_id', $offeringId)
                    ->where('instructor_id', $instructorId)
                    ->delete();
    }

    /**
     * Set instructor as primary
     */
    public function setPrimary($offeringId, $instructorId)
    {
        $this->db->transStart();
        
        // Remove primary flag from all instructors in this offering
        $this->where('course_offering_id', $offeringId)
             ->set('is_primary', 0)
             ->update();
        
        // Set the specified instructor as primary
        $this->where('course_offering_id', $offeringId)
             ->where('instructor_id', $instructorId)
             ->set('is_primary', 1)
             ->update();
        
        $this->db->transComplete();
        
        return $this->db->transStatus();
    }

    /**
     * Check if instructor is assigned to offering
     */
    public function isAssigned($offeringId, $instructorId)
    {
        return $this->where('course_offering_id', $offeringId)
                    ->where('instructor_id', $instructorId)
                    ->countAllResults() > 0;
    }

    /**
     * Get instructor's workload (count of offerings)
     */
    public function getInstructorWorkload($instructorId, $termId)
    {
        return $this->join('course_offerings co', 'co.id = course_instructors.course_offering_id')
                    ->where('course_instructors.instructor_id', $instructorId)
                    ->where('co.term_id', $termId)
                    ->countAllResults();
    }
}