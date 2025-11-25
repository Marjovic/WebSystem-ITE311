<?php

namespace App\Models;

use CodeIgniter\Model;

class GradeComponentModel extends Model
{
    protected $table            = 'grade_components';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'course_offering_id',
        'grading_period_id',
        'assignment_type_id',
        'component_name',
        'weight_percentage',
        'max_score',
        'description',
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
        'course_offering_id' => 'required|integer',
        'grading_period_id'  => 'permit_empty|integer',
        'assignment_type_id' => 'permit_empty|integer',
        'component_name'     => 'required|string|max_length[100]',
        'weight_percentage'  => 'required|decimal|greater_than[0]|less_than_equal_to[100]',
        'max_score'          => 'permit_empty|decimal',
        'description'        => 'permit_empty|string',
        'is_active'          => 'permit_empty|in_list[0,1]'
    ];

    protected $validationMessages = [
        'component_name' => [
            'required' => 'Component name is required'
        ],
        'weight_percentage' => [
            'required'             => 'Weight percentage is required',
            'greater_than'         => 'Weight must be greater than 0',
            'less_than_equal_to'   => 'Weight cannot exceed 100%'
        ]
    ];

    // Callbacks
    protected $allowCallbacks = true;

    /**
     * Get all grade components for a course offering
     */
    public function getOfferingComponents($offeringId)
    {
        return $this->select('
                grade_components.*,
                grading_periods.period_name,
                assignment_types.type_name
            ')
            ->join('grading_periods', 'grading_periods.id = grade_components.grading_period_id', 'left')
            ->join('assignment_types', 'assignment_types.id = grade_components.assignment_type_id', 'left')
            ->where('grade_components.course_offering_id', $offeringId)
            ->where('grade_components.is_active', 1)
            ->orderBy('grading_periods.period_order', 'ASC')
            ->orderBy('grade_components.component_name', 'ASC')
            ->findAll();
    }

    /**
     * Get components by grading period
     */
    public function getComponentsByPeriod($offeringId, $periodId)
    {
        return $this->where('course_offering_id', $offeringId)
                    ->where('grading_period_id', $periodId)
                    ->where('is_active', 1)
                    ->orderBy('component_name', 'ASC')
                    ->findAll();
    }

    /**
     * Validate total weight percentage for an offering
     */
    public function validateTotalWeight($offeringId, $periodId = null)
    {
        $builder = $this->where('course_offering_id', $offeringId)
                        ->where('is_active', 1);
        
        if ($periodId) {
            $builder->where('grading_period_id', $periodId);
        }
        
        $components = $builder->findAll();
        $totalWeight = array_sum(array_column($components, 'weight_percentage'));
        
        return [
            'valid'        => abs($totalWeight - 100) < 0.01,
            'total_weight' => $totalWeight,
            'difference'   => 100 - $totalWeight
        ];
    }

    /**
     * Get component with assignment type details
     */
    public function getComponentWithDetails($componentId)
    {
        return $this->select('
                grade_components.*,
                grading_periods.period_name,
                grading_periods.weight_percentage as period_weight,
                assignment_types.type_name,
                assignment_types.type_code
            ')
            ->join('grading_periods', 'grading_periods.id = grade_components.grading_period_id', 'left')
            ->join('assignment_types', 'assignment_types.id = grade_components.assignment_type_id', 'left')
            ->find($componentId);
    }

    /**
     * Calculate student's grade for a component
     */
    public function calculateComponentGrade($componentId, $studentId)
    {
        $db = \Config\Database::connect();
        
        $component = $this->find($componentId);
        if (!$component) {
            return null;
        }
        
        // Get all assignments for this component
        $assignments = $db->table('assignments')
                         ->where('grade_component_id', $componentId)
                         ->where('is_active', 1)
                         ->get()
                         ->getResultArray();
        
        if (empty($assignments)) {
            return [
                'component_grade' => 0,
                'weighted_grade'  => 0,
                'assignments_completed' => 0,
                'total_assignments' => 0
            ];
        }
        
        $totalScore = 0;
        $totalMaxScore = 0;
        $completedCount = 0;
        
        foreach ($assignments as $assignment) {
            // Get student's submission
            $submission = $db->table('submissions')
                            ->where('assignment_id', $assignment['id'])
                            ->where('student_id', $studentId)
                            ->where('score IS NOT NULL')
                            ->get()
                            ->getRow();
            
            if ($submission) {
                $totalScore += $submission->score;
                $totalMaxScore += $assignment['max_score'];
                $completedCount++;
            }
        }
        
        $componentGrade = $totalMaxScore > 0 ? ($totalScore / $totalMaxScore) * 100 : 0;
        $weightedGrade = $componentGrade * ($component['weight_percentage'] / 100);
        
        return [
            'component_grade'       => round($componentGrade, 2),
            'weighted_grade'        => round($weightedGrade, 2),
            'assignments_completed' => $completedCount,
            'total_assignments'     => count($assignments),
            'total_score'           => $totalScore,
            'total_max_score'       => $totalMaxScore
        ];
    }

    /**
     * Get grading breakdown for a course offering
     */
    public function getGradingBreakdown($offeringId)
    {
        return $this->select('
                gp.period_name,
                gp.weight_percentage as period_weight,
                gc.component_name,
                gc.weight_percentage as component_weight,
                at.type_name
            ')
            ->from('grade_components gc')
            ->join('grading_periods gp', 'gp.id = gc.grading_period_id', 'left')
            ->join('assignment_types at', 'at.id = gc.assignment_type_id', 'left')
            ->where('gc.course_offering_id', $offeringId)
            ->where('gc.is_active', 1)
            ->orderBy('gp.period_order', 'ASC')
            ->orderBy('gc.component_name', 'ASC')
            ->get()
            ->getResultArray();
    }
}