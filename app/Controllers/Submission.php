<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\AssignmentModel;
use App\Models\SubmissionModel;
use App\Models\EnrollmentModel;
use App\Models\NotificationModel;
use App\Models\CourseInstructorModel;
use App\Models\StudentModel;
use App\Models\InstructorModel;
use CodeIgniter\HTTP\ResponseInterface;

class Submission extends BaseController
{
    protected $assignmentModel;
    protected $submissionModel;
    protected $enrollmentModel;
    protected $notificationModel;
    protected $courseInstructorModel;
    protected $studentModel;
    protected $instructorModel;
    
    public function __construct()
    {
        $this->assignmentModel = new AssignmentModel();
        $this->submissionModel = new SubmissionModel();
        $this->enrollmentModel = new EnrollmentModel();
        $this->notificationModel = new NotificationModel();
        $this->courseInstructorModel = new CourseInstructorModel();
        $this->studentModel = new StudentModel();
        $this->instructorModel = new InstructorModel();
    }

    public function studentAssignments()
    {
        if (!$this->session->get('isLoggedIn') || $this->session->get('role') !== 'student') {
            return redirect()->to(base_url('login'))->with('error', 'Unauthorized access');
        }

        // Get student record from user_id
        $student = $this->studentModel->getStudentByUserId($this->session->get('userID'));
        if (!$student) {
            return redirect()->to(base_url('login'))->with('error', 'Student record not found');
        }
        $studentId = $student['id'];

        $db = \Config\Database::connect();
        $assignments = $db->table('assignments a')
            ->select('a.*, c.course_code, c.title as course_title, co.section, 
                      at.type_name, gp.period_name,
                      s.id as submission_id, s.status as submission_status, 
                      s.score, s.submitted_at, s.is_late,
                      e.id as enrollment_id')
            ->join('course_offerings co', 'co.id = a.course_offering_id')
            ->join('courses c', 'c.id = co.course_id')
            ->join('enrollments e', 'e.course_offering_id = co.id')
            ->join('assignment_types at', 'at.id = a.assignment_type_id', 'left')
            ->join('grading_periods gp', 'gp.id = a.grading_period_id', 'left')
            ->join('submissions s', 's.assignment_id = a.id AND s.enrollment_id = e.id', 'left')
            ->where('e.student_id', $studentId)
            ->where('e.enrollment_status', 'enrolled')
            ->where('a.is_active', 1)
            ->where('a.is_published', 1)
            ->orderBy('a.due_date', 'ASC')
            ->get()
            ->getResultArray();

        $upcoming = [];
        $overdue = [];
        $submitted = [];
        $graded = [];

        $now = date('Y-m-d H:i:s');

        foreach ($assignments as $assignment) {
            if ($assignment['submission_status'] === 'graded') {
                $graded[] = $assignment;
            } elseif ($assignment['submission_status'] === 'submitted') {
                $submitted[] = $assignment;
            } elseif ($assignment['due_date'] < $now) {
                $overdue[] = $assignment;
            } else {
                $upcoming[] = $assignment;
            }
        }

        $data = [
            'title' => 'My Assignments',
            'upcoming' => $upcoming,
            'overdue' => $overdue,
            'submitted' => $submitted,
            'graded' => $graded
        ];

        return view('student/assignments', $data);
    }

    public function viewAssignment($assignmentId)
    {
        if (!$this->session->get('isLoggedIn') || $this->session->get('role') !== 'student') {
            return redirect()->to(base_url('login'))->with('error', 'Unauthorized access');
        }

        // Get student record
        $student = $this->studentModel->getStudentByUserId($this->session->get('userID'));
        if (!$student) {
            return redirect()->to(base_url('login'))->with('error', 'Student record not found');
        }
        $studentId = $student['id'];

        // Get assignment with details
        $assignment = $this->assignmentModel->getAssignmentWithDetails($assignmentId);
        if (!$assignment) {
            return redirect()->to(base_url('student/assignments'))->with('error', 'Assignment not found');
        }

        // Check if student is enrolled in the course
        $db = \Config\Database::connect();
        $enrollment = $db->table('enrollments')
            ->where('student_id', $studentId)
            ->where('course_offering_id', $assignment['course_offering_id'])
            ->where('enrollment_status', 'enrolled')
            ->get()
            ->getRowArray();

        if (!$enrollment) {
            return redirect()->to(base_url('student/assignments'))->with('error', 'You are not enrolled in this course');
        }

        // Get existing submission if any
        $submission = $db->table('submissions')
            ->where('assignment_id', $assignmentId)
            ->where('enrollment_id', $enrollment['id'])
            ->get()
            ->getRowArray();

        // Check if assignment is available for submission
        $now = date('Y-m-d H:i:s');
        $isAvailable = true;
        $availabilityMessage = '';

        if ($assignment['available_from'] && $now < $assignment['available_from']) {
            $isAvailable = false;
            $availabilityMessage = 'This assignment will be available on ' . date('M j, Y g:i A', strtotime($assignment['available_from']));
        }

        if ($assignment['available_until'] && $now > $assignment['available_until']) {
            $isAvailable = false;
            $availabilityMessage = 'Submission window closed on ' . date('M j, Y g:i A', strtotime($assignment['available_until']));
        }

        $data = [
            'title' => 'Assignment - ' . $assignment['title'],
            'assignment' => $assignment,
            'submission' => $submission,
            'enrollment' => $enrollment,
            'isAvailable' => $isAvailable,
            'availabilityMessage' => $availabilityMessage,
            'isOverdue' => $now > $assignment['due_date'],
            'canSubmitLate' => $assignment['allow_late_submission'] && $now > $assignment['due_date']
        ];

        return view('student/assignment_detail', $data);
    }

    public function downloadAttachment($assignmentId)
    {
        if (!$this->session->get('isLoggedIn') || $this->session->get('role') !== 'student') {
            return redirect()->to(base_url('login'))->with('error', 'Unauthorized access');
        }

        // Get student record
        $student = $this->studentModel->getStudentByUserId($this->session->get('userID'));
        if (!$student) {
            return redirect()->to(base_url('login'))->with('error', 'Student record not found');
        }
        $studentId = $student['id'];

        // Get assignment with details
        $assignment = $this->assignmentModel->find($assignmentId);
        if (!$assignment || !$assignment['attachment_path']) {
            return redirect()->back()->with('error', 'Attachment not found');
        }

        // Check if student is enrolled in the course
        $db = \Config\Database::connect();
        $enrollment = $db->table('enrollments')
            ->where('student_id', $studentId)
            ->where('course_offering_id', $assignment['course_offering_id'])
            ->where('enrollment_status', 'enrolled')
            ->get()
            ->getRowArray();

        if (!$enrollment) {
            return redirect()->back()->with('error', 'You are not enrolled in this course');
        }

        $filePath = WRITEPATH . 'uploads/' . $assignment['attachment_path'];
        
        if (!file_exists($filePath)) {
            return redirect()->back()->with('error', 'File not found');
        }

        // Set appropriate content type based on file extension
        $fileExtension = pathinfo($filePath, PATHINFO_EXTENSION);
        switch(strtolower($fileExtension)) {
            case 'pdf':
                $contentType = 'application/pdf';
                break;
            case 'doc':
                $contentType = 'application/msword';
                break;
            case 'docx':
                $contentType = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
                break;
            default:
                $contentType = 'application/octet-stream';
        }

        return $this->response
            ->setHeader('Content-Type', $contentType)
            ->setHeader('Content-Disposition', 'inline; filename="' . basename($filePath) . '"')
            ->download($filePath, null);
    }

    public function submit()
    {
        if (!$this->session->get('isLoggedIn') || $this->session->get('role') !== 'student') {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized'])->setStatusCode(403);
        }

        // Get student record from user_id
        $student = $this->studentModel->getStudentByUserId($this->session->get('userID'));
        if (!$student) {
            return $this->response->setJSON(['success' => false, 'message' => 'Student record not found'])->setStatusCode(404);
        }
        $studentId = $student['id'];
        
        $assignmentId = $this->request->getPost('assignment_id');
        $enrollmentId = $this->request->getPost('enrollment_id');
        $submissionText = $this->request->getPost('submission_text');

        $assignment = $this->assignmentModel->find($assignmentId);
        if (!$assignment) {
            return $this->response->setJSON(['success' => false, 'message' => 'Assignment not found'])->setStatusCode(404);
        }

        $enrollment = $this->enrollmentModel->find($enrollmentId);
        if (!$enrollment || $enrollment['student_id'] != $studentId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid enrollment'])->setStatusCode(403);
        }

        $submissionType = $assignment['submission_type'] ?? 'both';
        $file = $this->request->getFile('submission_file');
        $hasFile = $file && $file->isValid() && !$file->hasMoved();

        if ($submissionType === 'text' && empty($submissionText)) {
            return $this->response->setJSON([
                'success' => false, 
                'message' => 'Text submission is required for this assignment'
            ])->setStatusCode(400);
        }

        if ($submissionType === 'file' && !$hasFile) {
            return $this->response->setJSON([
                'success' => false, 
                'message' => 'File upload is required for this assignment'
            ])->setStatusCode(400);
        }

        if ($submissionType === 'both' && empty($submissionText) && !$hasFile) {
            return $this->response->setJSON([
                'success' => false, 
                'message' => 'Please provide either a text submission or upload a file'
            ])->setStatusCode(400);
        }

        $filePath = null;
        
        if ($hasFile) {
            $allowedExtensions = ['pdf', 'doc', 'docx'];
            $fileExtension = $file->getExtension();
            
            if (!in_array(strtolower($fileExtension), $allowedExtensions)) {
                return $this->response->setJSON([
                    'success' => false, 
                    'message' => 'Only PDF and Word documents are allowed'
                ])->setStatusCode(400);
            }

            $maxSize = 10 * 1024 * 1024;
            if ($file->getSize() > $maxSize) {
                return $this->response->setJSON([
                    'success' => false, 
                    'message' => 'File size must not exceed 10MB'
                ])->setStatusCode(400);
            }

            $newName = 'submission_' . $assignmentId . '_' . $enrollmentId . '_' . time() . '.' . $fileExtension;
            $uploadPath = WRITEPATH . 'uploads/submissions/';
            
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0777, true);
            }

            if ($file->move($uploadPath, $newName)) {
                $filePath = 'submissions/' . $newName;
            } else {
                return $this->response->setJSON([
                    'success' => false, 
                    'message' => 'Failed to upload file'
                ])->setStatusCode(500);
            }
        }

        if (!$submissionText && !$filePath) {
            return $this->response->setJSON([
                'success' => false, 
                'message' => 'Please provide either text submission or upload a file'
            ])->setStatusCode(400);
        }

        $now = date('Y-m-d H:i:s');
        $isLate = ($now > $assignment['due_date']) ? 1 : 0;

        if ($isLate && !$assignment['allow_late_submission']) {
            return $this->response->setJSON([
                'success' => false, 
                'message' => 'Late submissions are not allowed for this assignment'
            ])->setStatusCode(400);
        }

        $db = \Config\Database::connect();
        $existingSubmission = $db->table('submissions')
            ->where('assignment_id', $assignmentId)
            ->where('enrollment_id', $enrollmentId)
            ->get()
            ->getRowArray();

        $submissionData = [
            'enrollment_id' => $enrollmentId,
            'assignment_id' => $assignmentId,
            'submission_text' => $submissionText,
            'file_path' => $filePath,
            'submitted_at' => $now,
            'is_late' => $isLate,
            'status' => 'submitted'
        ];

        if ($existingSubmission) {
            if ($existingSubmission['status'] === 'graded') {
                return $this->response->setJSON([
                    'success' => false, 
                    'message' => 'Cannot resubmit a graded assignment'
                ])->setStatusCode(400);
            }

            if ($this->submissionModel->update($existingSubmission['id'], $submissionData)) {
                $this->notifyTeacherNewSubmission($assignmentId, $assignment['course_offering_id'], $studentId);
                return $this->response->setJSON(['success' => true, 'message' => 'Assignment resubmitted successfully']);
            }
        } else {
            if ($this->submissionModel->insert($submissionData)) {
                $this->notifyTeacherNewSubmission($assignmentId, $assignment['course_offering_id'], $studentId);
                return $this->response->setJSON(['success' => true, 'message' => 'Assignment submitted successfully']);
            }
        }

        return $this->response->setJSON(['success' => false, 'message' => 'Failed to submit assignment'])->setStatusCode(500);
    }

    public function downloadSubmission($submissionId)
    {
        if (!$this->session->get('isLoggedIn')) {
            return redirect()->to(base_url('login'))->with('error', 'Unauthorized access');
        }

        $userRole = $this->session->get('role');
        $userId = $this->session->get('userID');

        $submission = $this->submissionModel->getSubmissionWithDetails($submissionId);
        if (!$submission) {
            return redirect()->back()->with('error', 'Submission not found');
        }

        $db = \Config\Database::connect();
        $enrollment = $db->table('enrollments')->where('id', $submission['enrollment_id'])->get()->getRowArray();

        if ($userRole === 'student' && $enrollment['student_id'] != $userId) {
            return redirect()->back()->with('error', 'Unauthorized access');
        }

        if ($userRole === 'teacher') {
            // Get instructor record from user_id
            $instructor = $this->instructorModel->getInstructorByUserId($userId);
            if (!$instructor) {
                return redirect()->back()->with('error', 'Instructor record not found');
            }
            
            $assignment = $this->assignmentModel->find($submission['assignment_id']);
            $isInstructor = $this->courseInstructorModel
                ->where('course_offering_id', $assignment['course_offering_id'])
                ->where('instructor_id', $instructor['id'])
                ->first();

            if (!$isInstructor) {
                return redirect()->back()->with('error', 'Unauthorized access');
            }
        }

        if (!$submission['file_path']) {
            return redirect()->back()->with('error', 'No file attached to this submission');
        }

        $filePath = WRITEPATH . 'uploads/' . $submission['file_path'];
        
        if (!file_exists($filePath)) {
            return redirect()->back()->with('error', 'File not found');
        }

        return $this->response->download($filePath, null)->setFileName(basename($filePath));
    }

    private function notifyTeacherNewSubmission($assignmentId, $courseOfferingId, $studentId)
    {
        $assignment = $this->assignmentModel->getAssignmentWithDetails($assignmentId);
        
        $db = \Config\Database::connect();
        $student = $db->table('users u')
            ->select("CONCAT(u.first_name, ' ', COALESCE(u.middle_name, ''), ' ', u.last_name) as full_name")
            ->where('u.id', $studentId)
            ->get()
            ->getRowArray();
        
        $instructors = $this->courseInstructorModel
            ->where('course_offering_id', $courseOfferingId)
            ->findAll();

        foreach ($instructors as $instructor) {
            $this->notificationModel->insert([
                'user_id' => $instructor['instructor_id'],
                'message' => "New submission from {$student['full_name']} for assignment '{$assignment['title']}' in {$assignment['course_code']}",
                'is_read' => 0,
                'is_hidden' => 0
            ]);
        }
    }
}
