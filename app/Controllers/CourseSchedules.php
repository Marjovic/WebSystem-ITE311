<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\CourseScheduleModel;
use App\Models\CourseOfferingModel;
use App\Models\CourseModel;
use App\Models\TermModel;

class CourseSchedules extends BaseController
{
    protected $session;
    protected $validation;
    protected $db;
    protected $scheduleModel;
    protected $offeringModel;
    protected $courseModel;
    protected $termModel;

    public function __construct()
    {
        $this->session = \Config\Services::session();
        $this->validation = \Config\Services::validation();
        $this->db = \Config\Database::connect();
        
        $this->scheduleModel = new CourseScheduleModel();
        $this->offeringModel = new CourseOfferingModel();
        $this->courseModel = new CourseModel();
        $this->termModel = new TermModel();
    }

    /**
     * Manage Course Schedules - Main method
     */
    public function manageSchedules()
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
        $scheduleID = $this->request->getGet('id');
        $offeringID = $this->request->getGet('offering_id');

        // Route to appropriate action
        if ($action === 'create' && $this->request->getMethod() === 'POST') {
            return $this->createSchedule();
        }

        if ($action === 'edit' && $scheduleID) {
            return $this->editSchedule($scheduleID);
        }

        if ($action === 'delete' && $scheduleID) {
            return $this->deleteSchedule($scheduleID);
        }

        // Display schedule management interface
        return $this->displayScheduleManagement($offeringID);
    }

    /**
     * Create a new schedule
     */
    private function createSchedule()
    {
        // Validation rules
        $rules = [
            'course_offering_id' => 'required|integer',
            'day_of_week'        => 'required|in_list[Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday]',
            'start_time'         => 'required',
            'end_time'           => 'required',
            'room'               => 'permit_empty|string|max_length[50]'
        ];

        $messages = [
            'course_offering_id' => [
                'required' => 'Course offering is required.',
                'integer'  => 'Please select a valid course offering.'
            ],
            'day_of_week' => [
                'required' => 'Day of week is required.',
                'in_list'  => 'Invalid day selected.'
            ],
            'start_time' => [
                'required' => 'Start time is required.'
            ],
            'end_time' => [
                'required' => 'End time is required.'
            ]
        ];

        if (!$this->validate($rules, $messages)) {
            $this->session->setFlashdata('errors', $this->validator->getErrors());
            $this->session->setFlashdata('error', 'Please fix the errors below.');
            return redirect()->to(base_url('admin/manage_courses_schedule?action=create&offering_id=' . $this->request->getPost('course_offering_id')))->withInput();
        }

        // Validate time order
        $startTime = $this->request->getPost('start_time');
        $endTime = $this->request->getPost('end_time');
        
        if (strtotime($startTime) >= strtotime($endTime)) {
            $this->session->setFlashdata('error', 'End time must be after start time.');
            return redirect()->to(base_url('admin/manage_courses_schedule?action=create&offering_id=' . $this->request->getPost('course_offering_id')))->withInput();
        }

        // Prepare schedule data
        $scheduleData = [
            'course_offering_id' => $this->request->getPost('course_offering_id'),
            'day_of_week'        => $this->request->getPost('day_of_week'),
            'start_time'         => $startTime,
            'end_time'           => $endTime,
            'room'               => $this->request->getPost('room') ?: null
        ];

        // Create schedule
        if ($this->scheduleModel->insert($scheduleData)) {
            $this->session->setFlashdata('success', 'Course schedule created successfully!');
            return redirect()->to(base_url('admin/manage_courses_schedule?offering_id=' . $scheduleData['course_offering_id']));
        } else {
            $this->session->setFlashdata('errors', $this->scheduleModel->errors());
            $this->session->setFlashdata('error', 'Failed to create schedule. Please try again.');
            return redirect()->to(base_url('admin/manage_courses_schedule?action=create&offering_id=' . $this->request->getPost('course_offering_id')))->withInput();
        }
    }

    /**
     * Edit an existing schedule
     */
    private function editSchedule($scheduleID)
    {
        $scheduleToEdit = $this->scheduleModel->find($scheduleID);

        if (!$scheduleToEdit) {
            $this->session->setFlashdata('error', 'Schedule not found.');
            return redirect()->to(base_url('admin/manage_courses_schedule'));
        }

        // Handle POST request (update)
        if ($this->request->getMethod() === 'POST') {
            // Validation rules
            $rules = [
                'day_of_week' => 'required|in_list[Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday]',
                'start_time'  => 'required',
                'end_time'    => 'required',
                'room'        => 'permit_empty|string|max_length[50]'
            ];

            $messages = [
                'day_of_week' => [
                    'required' => 'Day of week is required.',
                    'in_list'  => 'Invalid day selected.'
                ],
                'start_time' => [
                    'required' => 'Start time is required.'
                ],
                'end_time' => [
                    'required' => 'End time is required.'
                ]
            ];

            if (!$this->validate($rules, $messages)) {
                $this->session->setFlashdata('errors', $this->validator->getErrors());
                $this->session->setFlashdata('error', 'Please fix the errors below.');
                return redirect()->to(base_url('admin/manage_courses_schedule?action=edit&id=' . $scheduleID))->withInput();
            }

            // Validate time order
            $startTime = $this->request->getPost('start_time');
            $endTime = $this->request->getPost('end_time');
            
            if (strtotime($startTime) >= strtotime($endTime)) {
                $this->session->setFlashdata('error', 'End time must be after start time.');
                return redirect()->to(base_url('admin/manage_courses_schedule?action=edit&id=' . $scheduleID))->withInput();
            }

            // Prepare update data
            $updateData = [
                'day_of_week' => $this->request->getPost('day_of_week'),
                'start_time'  => $startTime,
                'end_time'    => $endTime,
                'room'        => $this->request->getPost('room') ?: null
            ];

            // Update schedule
            if ($this->scheduleModel->update($scheduleID, $updateData)) {
                $this->session->setFlashdata('success', 'Course schedule updated successfully!');
                return redirect()->to(base_url('admin/manage_courses_schedule?offering_id=' . $scheduleToEdit['course_offering_id']));
            } else {
                $this->session->setFlashdata('errors', $this->scheduleModel->errors());
                $this->session->setFlashdata('error', 'Failed to update schedule. Please try again.');
                return redirect()->to(base_url('admin/manage_courses_schedule?action=edit&id=' . $scheduleID))->withInput();
            }
        }

        // Get offering details
        $offering = $this->db->table('course_offerings co')
            ->select('co.*, c.course_code, c.title, t.term_name')
            ->join('courses c', 'c.id = co.course_id')
            ->join('terms t', 't.id = co.term_id')
            ->where('co.id', $scheduleToEdit['course_offering_id'])
            ->get()
            ->getRowArray();

        // Show edit form
        $data = [
            'user' => [
                'role' => $this->session->get('role')
            ],
            'title' => 'Edit Course Schedule - Admin Dashboard',
            'schedules' => [],
            'offerings' => [$offering],
            'editSchedule' => $scheduleToEdit,
            'selectedOffering' => $offering,
            'showCreateForm' => false,
            'showEditForm' => true
        ];

        return view('admin/manage_courses_schedule', $data);
    }

    /**
     * Delete a schedule
     */
    private function deleteSchedule($scheduleID)
    {
        $scheduleToDelete = $this->scheduleModel->find($scheduleID);

        if (!$scheduleToDelete) {
            $this->session->setFlashdata('error', 'Schedule not found.');
            return redirect()->to(base_url('admin/manage_courses_schedule'));
        }

        $offeringId = $scheduleToDelete['course_offering_id'];

        if ($this->scheduleModel->delete($scheduleID)) {
            $this->session->setFlashdata('success', 'Course schedule deleted successfully!');
        } else {
            $this->session->setFlashdata('error', 'Failed to delete schedule. Please try again.');
        }

        return redirect()->to(base_url('admin/manage_courses_schedule?offering_id=' . $offeringId));
    }

    /**
     * Display schedule management interface
     */
    private function displayScheduleManagement($offeringID = null)
    {
        // Get all course offerings with details
        $offerings = $this->db->table('course_offerings co')
            ->select('co.*, c.course_code, c.title, c.credits, t.term_name')
            ->join('courses c', 'c.id = co.course_id')
            ->join('terms t', 't.id = co.term_id')
            ->orderBy('t.id', 'DESC')
            ->orderBy('c.course_code', 'ASC')
            ->get()
            ->getResultArray();

        // Get schedules
        $schedules = [];
        $selectedOffering = null;
        
        if ($offeringID) {
            $schedules = $this->scheduleModel->getOfferingSchedules($offeringID);
            $selectedOffering = $this->db->table('course_offerings co')
                ->select('co.*, c.course_code, c.title, t.term_name')
                ->join('courses c', 'c.id = co.course_id')
                ->join('terms t', 't.id = co.term_id')
                ->where('co.id', $offeringID)
                ->get()
                ->getRowArray();
        }

        $data = [
            'user' => [
                'userID' => $this->session->get('userID'),
                'name'   => $this->session->get('name'),
                'email'  => $this->session->get('email'),
                'role'   => $this->session->get('role')
            ],
            'title' => 'Manage Course Schedules - Admin Dashboard',
            'offerings' => $offerings,
            'schedules' => $schedules,
            'selectedOffering' => $selectedOffering,
            'selectedOfferingId' => $offeringID,
            'editSchedule' => null,
            'showCreateForm' => $this->request->getGet('action') === 'create',
            'showEditForm' => false
        ];

        return view('admin/manage_courses_schedule', $data);
    }
}
