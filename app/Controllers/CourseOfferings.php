<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\CourseOfferingModel;
use App\Models\CourseModel;
use App\Models\TermModel;

class CourseOfferings extends BaseController
{
    protected $session;
    protected $validation;
    protected $db;
    protected $offeringModel;
    protected $courseModel;
    protected $termModel;    /**
     * Constructor - Initialize models and dependencies
     */
    public function __construct()
    {
        $this->session = \Config\Services::session();
        $this->validation = \Config\Services::validation();
        $this->db = \Config\Database::connect();
        
        // Initialize models
        $this->offeringModel = new CourseOfferingModel();
        $this->courseModel = new CourseModel();
        $this->termModel = new TermModel();
    }

    public function index()
    {
        return redirect()->to(base_url('admin/manage_offerings'));
    }

    /**
     * Manage Course Offerings Method - Handles all offering management operations
     * Supports create, edit, delete, and display operations
     */
    public function manageOfferings()
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
        $offeringID = $this->request->getGet('id');
        $termID = $this->request->getGet('term_id');

        // Route to appropriate action
        if ($action === 'create' && $this->request->getMethod() === 'POST') {
            return $this->createOffering();
        }

        if ($action === 'edit' && $offeringID) {
            return $this->editOffering($offeringID);
        }

        if ($action === 'delete' && $offeringID) {
            return $this->deleteOffering($offeringID);
        }

        if ($action === 'toggle_status' && $offeringID) {
            return $this->toggleStatus($offeringID);
        }

        // Display offering management interface
        return $this->displayOfferingManagement($termID);
    }    /**
     * Create a new course offering
     */
    private function createOffering()
    {        // Validation rules
        $rules = [
            'course_id'    => 'required|integer',
            'term_id'      => 'required|integer',
            'section'      => 'permit_empty|string|max_length[50]',
            'max_students' => 'required|integer|greater_than[0]',
            'room'         => 'permit_empty|string|max_length[100]',
            'status'       => 'required|in_list[draft,open,closed,cancelled,completed]',
            'start_date'   => 'permit_empty|valid_date|check_future_date[start_date]|check_date_order[end_date]',
            'end_date'     => 'permit_empty|valid_date|check_future_date[end_date]'
        ];

        $messages = [
            'course_id' => [
                'required' => 'Course is required.',
                'integer'  => 'Please select a valid course.'
            ],
            'term_id' => [
                'required' => 'Term is required.',
                'integer'  => 'Please select a valid term.'
            ],
            'max_students' => [
                'required'      => 'Maximum students is required.',
                'integer'       => 'Maximum students must be a number.',
                'greater_than'  => 'Maximum students must be greater than 0.'
            ],
            'status' => [
                'required' => 'Status is required.',
                'in_list'  => 'Invalid status selected.'
            ],
            'start_date' => [
                'check_future_date' => 'Start date must be today or a future date.',
                'check_date_order'  => 'Start date cannot be after end date.'
            ],
            'end_date' => [
                'check_future_date' => 'End date must be today or a future date.'
            ]
        ];

        if (!$this->validate($rules, $messages)) {
            $this->session->setFlashdata('errors', $this->validator->getErrors());
            $this->session->setFlashdata('error', 'Please fix the errors below.');
            return redirect()->to(base_url('admin/manage_offerings?action=create'))->withInput();
        }

        // Check for duplicate offering (same course + term + section)
        $courseId = $this->request->getPost('course_id');
        $termId = $this->request->getPost('term_id');
        $section = $this->request->getPost('section');

        $existing = $this->offeringModel
            ->where('course_id', $courseId)
            ->where('term_id', $termId)
            ->where('section', $section)
            ->first();

        if ($existing) {
            $this->session->setFlashdata('error', 'This course offering already exists for the selected term and section.');
            return redirect()->to(base_url('admin/manage_offerings?action=create&term_id=' . $termId))->withInput();
        }

        // Prepare offering data
        $offeringData = [
            'course_id'          => $courseId,
            'term_id'            => $termId,
            'section'            => $section,
            'max_students'       => $this->request->getPost('max_students'),
            'current_enrollment' => 0,
            'room'               => $this->request->getPost('room'),
            'status'             => $this->request->getPost('status'),
            'start_date'         => $this->request->getPost('start_date') ?: null,
            'end_date'           => $this->request->getPost('end_date') ?: null
        ];

        // Create offering
        if ($this->offeringModel->insert($offeringData)) {
            $this->session->setFlashdata('success', 'Course offering created successfully!');
            return redirect()->to(base_url('admin/manage_offerings?term_id=' . $termId));
        } else {
            $this->session->setFlashdata('errors', $this->offeringModel->errors());
            $this->session->setFlashdata('error', 'Failed to create offering. Please try again.');
            return redirect()->to(base_url('admin/manage_offerings?action=create&term_id=' . $termId))->withInput();
        }
    }

    /**
     * Edit an existing course offering
     */
    private function editOffering($offeringID)
    {
        $offeringToEdit = $this->offeringModel->find($offeringID);

        if (!$offeringToEdit) {
            $this->session->setFlashdata('error', 'Course offering not found.');
            return redirect()->to(base_url('admin/manage_offerings'));
        }        // Handle POST request (update)
        if ($this->request->getMethod() === 'POST') {
            // Validation rules
            $rules = [
                'section'      => 'permit_empty|string|max_length[50]',
                'max_students' => 'required|integer|greater_than[0]',
                'room'         => 'permit_empty|string|max_length[100]',
                'status'       => 'required|in_list[draft,open,closed,cancelled,completed]',
                'start_date'   => 'permit_empty|valid_date|check_future_date[start_date]|check_date_order[end_date]',
                'end_date'     => 'permit_empty|valid_date|check_future_date[end_date]'
            ];

            $messages = [
                'max_students' => [
                    'required'     => 'Maximum students is required.',
                    'integer'      => 'Maximum students must be a number.',
                    'greater_than' => 'Maximum students must be greater than 0.'
                ],
                'status' => [
                    'required' => 'Status is required.',
                    'in_list'  => 'Invalid status selected.'
                ],
                'start_date' => [
                    'check_future_date' => 'Start date must be today or a future date.',
                    'check_date_order'  => 'Start date cannot be after end date.'
                ],
                'end_date' => [
                    'check_future_date' => 'End date must be today or a future date.'
                ]
            ];

            if (!$this->validate($rules, $messages)) {
                $this->session->setFlashdata('errors', $this->validator->getErrors());
                $this->session->setFlashdata('error', 'Please fix the errors below.');
                return redirect()->to(base_url('admin/manage_offerings?action=edit&id=' . $offeringID))->withInput();
            }

            // Prepare update data
            $updateData = [
                'section'      => $this->request->getPost('section'),
                'max_students' => $this->request->getPost('max_students'),
                'room'         => $this->request->getPost('room'),
                'status'       => $this->request->getPost('status'),
                'start_date'   => $this->request->getPost('start_date') ?: null,
                'end_date'     => $this->request->getPost('end_date') ?: null
            ];

            // Update offering
            if ($this->offeringModel->update($offeringID, $updateData)) {
                $this->session->setFlashdata('success', 'Course offering updated successfully!');
                return redirect()->to(base_url('admin/manage_offerings?term_id=' . $offeringToEdit['term_id']));
            } else {
                $this->session->setFlashdata('errors', $this->offeringModel->errors());
                $this->session->setFlashdata('error', 'Failed to update offering. Please try again.');
                return redirect()->to(base_url('admin/manage_offerings?action=edit&id=' . $offeringID))->withInput();
            }
        }        // Get related data
        $course = $this->courseModel->find($offeringToEdit['course_id']);
        $term = $this->termModel->find($offeringToEdit['term_id']);

        // Show edit form
        $data = [
            'user' => [
                'role' => $this->session->get('role')
            ],
            'title' => 'Edit Course Offering - Admin Dashboard',
            'offerings' => [],
            'courses' => $this->courseModel->where('is_active', 1)->orderBy('course_code', 'ASC')->findAll(),
            'terms' => $this->termModel->where('is_active', 1)->orderBy('start_date', 'DESC')->findAll(),
            'editOffering' => $offeringToEdit,
            'course' => $course,
            'term' => $term,
            'showCreateForm' => false,
            'showEditForm' => true,
            'selectedTermId' => $offeringToEdit['term_id'],
            'selectedTerm' => $term,
            'statistics' => $this->getOfferingStatistics()
        ];

        return view('admin/manage_offerings', $data);
    }

    /**
     * Delete a course offering
     */
    private function deleteOffering($offeringID)
    {
        $offeringToDelete = $this->offeringModel->find($offeringID);

        if (!$offeringToDelete) {
            $this->session->setFlashdata('error', 'Course offering not found.');
            return redirect()->to(base_url('admin/manage_offerings'));
        }

        // Check if there are enrollments
        if ($offeringToDelete['current_enrollment'] > 0) {
            $this->session->setFlashdata('error', 'Cannot delete offering with active enrollments. Please remove all enrollments first.');
            return redirect()->to(base_url('admin/manage_offerings?term_id=' . $offeringToDelete['term_id']));
        }

        $termId = $offeringToDelete['term_id'];

        // Delete offering
        if ($this->offeringModel->delete($offeringID)) {
            $this->session->setFlashdata('success', 'Course offering deleted successfully!');
        } else {
            $this->session->setFlashdata('error', 'Failed to delete offering. Please try again.');
        }

        return redirect()->to(base_url('admin/manage_offerings?term_id=' . $termId));
    }

    /**
     * Toggle offering status
     */
    private function toggleStatus($offeringID)
    {
        $offering = $this->offeringModel->find($offeringID);

        if (!$offering) {
            $this->session->setFlashdata('error', 'Course offering not found.');
            return redirect()->to(base_url('admin/manage_offerings'));
        }

        // Cycle through statuses: draft -> open -> closed -> completed
        $statusCycle = [
            'draft' => 'open',
            'open' => 'closed',
            'closed' => 'completed',
            'completed' => 'draft',
            'cancelled' => 'draft'
        ];

        $newStatus = $statusCycle[$offering['status']] ?? 'draft';

        if ($this->offeringModel->update($offeringID, ['status' => $newStatus])) {
            $this->session->setFlashdata('success', "Status changed to '{$newStatus}' successfully!");
        } else {
            $this->session->setFlashdata('error', 'Failed to update status. Please try again.');
        }

        return redirect()->to(base_url('admin/manage_offerings?term_id=' . $offering['term_id']));
    }

    /**
     * Display offering management interface
     */
    private function displayOfferingManagement($termID = null)
    {
        $showCreateForm = $this->request->getGet('action') === 'create';

        // Get offerings by term or all offerings
        if ($termID) {
            $offerings = $this->offeringModel->getOfferingsByTerm($termID);
            $selectedTerm = $this->termModel->find($termID);
        } else {
            // Get all offerings with details
            $offerings = $this->db->table('course_offerings co')
                ->select('co.*, 
                         c.course_code, 
                         c.title as course_title,
                         c.credits,
                         t.term_name,
                         (SELECT COUNT(*) FROM enrollments e WHERE e.course_offering_id = co.id AND e.enrollment_status = "enrolled") as enrolled_count')
                ->join('courses c', 'c.id = co.course_id')
                ->join('terms t', 't.id = co.term_id')
                ->orderBy('t.start_date', 'DESC')
                ->orderBy('c.course_code', 'ASC')
                ->get()
                ->getResultArray();
            $selectedTerm = null;
        }

        $data = [
            'user' => [
                'role' => $this->session->get('role')
            ],
            'title' => 'Manage Course Offerings - Admin Dashboard',
            'offerings' => $offerings,
            'courses' => $this->courseModel->where('is_active', 1)->orderBy('course_code', 'ASC')->findAll(),
            'terms' => $this->termModel->where('is_active', 1)->orderBy('start_date', 'DESC')->findAll(),
            'showCreateForm' => $showCreateForm,
            'showEditForm' => false,
            'selectedTermId' => $termID,
            'selectedTerm' => $selectedTerm,
            'statistics' => $this->getOfferingStatistics()
        ];

        return view('admin/manage_offerings', $data);
    }

    /**
     * Get offering statistics
     */
    private function getOfferingStatistics()
    {
        return [
            'total' => $this->offeringModel->countAll(),
            'draft' => $this->offeringModel->where('status', 'draft')->countAllResults(),
            'open' => $this->offeringModel->where('status', 'open')->countAllResults(),
            'closed' => $this->offeringModel->where('status', 'closed')->countAllResults()
        ];
    }
}
