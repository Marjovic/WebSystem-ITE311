<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\CourseInstructorModel;
use App\Models\CourseOfferingModel;
use App\Models\InstructorModel;
use App\Models\TermModel;

class CourseInstructors extends BaseController
{
    protected $session;
    protected $validation;
    protected $db;
    protected $courseInstructorModel;
    protected $offeringModel;
    protected $instructorModel;
    protected $termModel;

    public function __construct()
    {
        $this->session = \Config\Services::session();
        $this->validation = \Config\Services::validation();
        $this->db = \Config\Database::connect();
        
        $this->courseInstructorModel = new CourseInstructorModel();
        $this->offeringModel = new CourseOfferingModel();
        $this->instructorModel = new InstructorModel();
        $this->termModel = new TermModel();
    }

    /**
     * Manage Course Instructors - Main method
     */
    public function manageInstructors()
    {
        // Security check
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
        $assignmentID = $this->request->getGet('id');
        $offeringID = $this->request->getGet('offering_id');

        // Route to appropriate action
        if ($action === 'assign' && $this->request->getMethod() === 'POST') {
            return $this->assignInstructor();
        }

        if ($action === 'remove' && $assignmentID) {
            return $this->removeInstructor($assignmentID);
        }

        if ($action === 'set_primary' && $assignmentID) {
            return $this->setPrimaryInstructor($assignmentID);
        }

        // Display instructor management interface
        return $this->displayInstructorManagement($offeringID);
    }

    /**
     * Assign instructor to course offering
     */
    private function assignInstructor()
    {
        // Validation rules
        $rules = [
            'course_offering_id' => 'required|integer',
            'instructor_id'      => 'required|integer',
            'is_primary'         => 'permit_empty|in_list[0,1]'
        ];

        $messages = [
            'course_offering_id' => [
                'required' => 'Course offering is required.',
                'integer'  => 'Please select a valid course offering.'
            ],
            'instructor_id' => [
                'required' => 'Instructor is required.',
                'integer'  => 'Please select a valid instructor.'
            ]
        ];

        if (!$this->validate($rules, $messages)) {
            $this->session->setFlashdata('errors', $this->validator->getErrors());
            $this->session->setFlashdata('error', 'Please fix the errors below.');
            return redirect()->to(base_url('admin/manage_course_instructors?action=assign&offering_id=' . $this->request->getPost('course_offering_id')))->withInput();
        }

        $offeringId = $this->request->getPost('course_offering_id');
        $instructorId = $this->request->getPost('instructor_id');
        $isPrimary = $this->request->getPost('is_primary') == '1';

        // Check if already assigned
        if ($this->courseInstructorModel->isAssigned($offeringId, $instructorId)) {
            $this->session->setFlashdata('error', 'This instructor is already assigned to this course offering.');
            return redirect()->to(base_url('admin/manage_course_instructors?offering_id=' . $offeringId));
        }

        // Assign instructor
        if ($this->courseInstructorModel->assignInstructor($offeringId, $instructorId, $isPrimary)) {
            $instructorName = $this->courseInstructorModel->getInstructorName($instructorId);
            $this->session->setFlashdata('success', "Instructor {$instructorName} assigned successfully!");
            return redirect()->to(base_url('admin/manage_course_instructors?offering_id=' . $offeringId));
        } else {
            $this->session->setFlashdata('error', 'Failed to assign instructor. Please try again.');
            return redirect()->to(base_url('admin/manage_course_instructors?action=assign&offering_id=' . $offeringId))->withInput();
        }
    }

    /**
     * Remove instructor from course offering
     */
    private function removeInstructor($assignmentID)
    {
        $assignment = $this->courseInstructorModel->find($assignmentID);

        if (!$assignment) {
            $this->session->setFlashdata('error', 'Instructor assignment not found.');
            return redirect()->to(base_url('admin/manage_course_instructors'));
        }

        $offeringId = $assignment['course_offering_id'];
        $instructorName = $this->courseInstructorModel->getInstructorName($assignment['instructor_id']);

        if ($this->courseInstructorModel->delete($assignmentID)) {
            $this->session->setFlashdata('success', "Instructor {$instructorName} removed successfully!");
        } else {
            $this->session->setFlashdata('error', 'Failed to remove instructor. Please try again.');
        }

        return redirect()->to(base_url('admin/manage_course_instructors?offering_id=' . $offeringId));
    }

    /**
     * Set instructor as primary
     */
    private function setPrimaryInstructor($assignmentID)
    {
        $assignment = $this->courseInstructorModel->find($assignmentID);

        if (!$assignment) {
            $this->session->setFlashdata('error', 'Instructor assignment not found.');
            return redirect()->to(base_url('admin/manage_course_instructors'));
        }

        $offeringId = $assignment['course_offering_id'];
        $instructorId = $assignment['instructor_id'];

        if ($this->courseInstructorModel->setPrimary($offeringId, $instructorId)) {
            $instructorName = $this->courseInstructorModel->getInstructorName($instructorId);
            $this->session->setFlashdata('success', "{$instructorName} set as primary instructor!");
        } else {
            $this->session->setFlashdata('error', 'Failed to set primary instructor. Please try again.');
        }

        return redirect()->to(base_url('admin/manage_course_instructors?offering_id=' . $offeringId));
    }

    /**
     * Display instructor management interface
     */
    private function displayInstructorManagement($offeringID = null)
    {
        // Get all course offerings with details
        $offerings = $this->db->table('course_offerings co')
            ->select('co.*, c.course_code, c.title, t.term_name, t.id as term_id')
            ->join('courses c', 'c.id = co.course_id')
            ->join('terms t', 't.id = co.term_id')
            ->orderBy('t.id', 'DESC')
            ->orderBy('c.course_code', 'ASC')
            ->get()
            ->getResultArray();

        // Get all active instructors
        $instructors = $this->instructorModel->getInstructorsByStatus('active');

        // Get assignments and offering details if offering is selected
        $assignments = [];
        $selectedOffering = null;
        $availableInstructors = $instructors;
        
        if ($offeringID) {
            $assignments = $this->courseInstructorModel->getOfferingInstructors($offeringID);
            
            $selectedOffering = $this->db->table('course_offerings co')
                ->select('co.*, c.course_code, c.title, c.credits, t.term_name')
                ->join('courses c', 'c.id = co.course_id')
                ->join('terms t', 't.id = co.term_id')
                ->where('co.id', $offeringID)
                ->get()
                ->getRowArray();

            // Filter out already assigned instructors
            $assignedIds = array_column($assignments, 'instructor_id');
            $availableInstructors = array_filter($instructors, function($instructor) use ($assignedIds) {
                return !in_array($instructor['id'], $assignedIds);
            });
        }

        $data = [
            'user' => [
                'userID' => $this->session->get('userID'),
                'name'   => $this->session->get('name'),
                'email'  => $this->session->get('email'),
                'role'   => $this->session->get('role')
            ],
            'title' => 'Manage Course Instructors - Admin Dashboard',
            'offerings' => $offerings,
            'instructors' => $instructors,
            'availableInstructors' => $availableInstructors,
            'assignments' => $assignments,
            'selectedOffering' => $selectedOffering,
            'selectedOfferingId' => $offeringID,
            'showAssignForm' => $this->request->getGet('action') === 'assign'
        ];

        return view('admin/manage_course_instructors', $data);
    }
}
