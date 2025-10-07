<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\EnrollmentModel;

class Course extends BaseController
{
    protected $session;
    protected $enrollmentModel;

    public function __construct()
    {
        // Initialize session service
        $this->session = \Config\Services::session();
        
        // Initialize enrollment model
        $this->enrollmentModel = new EnrollmentModel();
    }    
    /**
     * Handle AJAX enrollment request
     * 
     * @return ResponseInterface JSON response indicating success or failure
     */
    public function enroll()
    {
        // Set content type to JSON for AJAX response
        $this->response->setContentType('application/json');

        // 1. AUTHENTICATION CHECK
        if (!$this->session->get('isLoggedIn')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Unauthorized access. Please login first.',
                'error_code' => 'UNAUTHORIZED'
            ])->setStatusCode(401);
        }

        // 2. ROLE CHECK
        if ($this->session->get('role') !== 'student') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Access denied. Only students can enroll in courses.',
                'error_code' => 'ACCESS_DENIED'
            ])->setStatusCode(403);
        }

        // 3. METHOD CHECK
        if (!$this->request->is('post')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid request method. Only POST requests are allowed.',
                'error_code' => 'INVALID_METHOD'
            ])->setStatusCode(405);
        }

        // 4. AJAX CHECK
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid request type. Only AJAX requests are allowed.',
                'error_code' => 'INVALID_REQUEST_TYPE'
            ])->setStatusCode(400);
        }

        // 5. CSRF VALIDATION
        try {
            // Check if CSRF protection is enabled
            $config = config('Security');
            if ($config->csrfProtection !== false && $config->csrfProtection !== '') {
                
                // Get CSRF token name and hash from config/request
                $tokenName = $config->csrfTokenName ?? 'csrf_token_name';
                $headerName = $config->csrfHeaderName ?? 'X-CSRF-TOKEN';
                
                // Get submitted token from POST data or header
                $submittedToken = $this->request->getPost($tokenName) ?? $this->request->getHeaderLine($headerName);
                
                // Get expected token from session/cookie
                $expectedToken = csrf_hash();
                
                // Validate tokens
                if (empty($submittedToken) || empty($expectedToken)) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Security token is missing. Please refresh the page and try again.',
                        'error_code' => 'CSRF_MISSING',
                        'csrf_hash' => csrf_hash(),
                        'debug' => [
                            'submitted_token_empty' => empty($submittedToken),
                            'expected_token_empty' => empty($expectedToken),
                            'token_name' => $tokenName
                        ]
                    ])->setStatusCode(403);
                }
                
                if (!hash_equals($expectedToken, $submittedToken)) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Security token validation failed. Please refresh the page and try again.',
                        'error_code' => 'CSRF_INVALID',
                        'csrf_hash' => csrf_hash(),
                        'debug' => [
                            'submitted_token' => substr($submittedToken, 0, 10) . '...',
                            'expected_token' => substr($expectedToken, 0, 10) . '...',
                            'tokens_match' => false
                        ]
                    ])->setStatusCode(403);
                }
            }
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'CSRF validation error: ' . $e->getMessage(),
                'error_code' => 'CSRF_EXCEPTION',
                'csrf_hash' => csrf_hash()
            ])->setStatusCode(403);
        }

        // 6. INPUT VALIDATION
        $course_id = $this->request->getPost('course_id');
        
        if (!$course_id || !is_numeric($course_id) || $course_id <= 0) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Valid course ID is required.',
                'error_code' => 'INVALID_COURSE_ID',
                'csrf_hash' => csrf_hash()
            ])->setStatusCode(400);
        }

        // 7. USE SESSION USER ID (NEVER trust client input for user ID)
        $user_id = $this->session->get('userID');

        // Convert to integer for safety
        $course_id = (int)$course_id;

        try {
            // 8. CHECK IF ALREADY ENROLLED
            $alreadyEnrolled = $this->enrollmentModel->isAlreadyEnrolled($user_id, $course_id);
            
            if ($alreadyEnrolled) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'You are already enrolled in this course.',
                    'error_code' => 'ALREADY_ENROLLED',
                    'csrf_hash' => csrf_hash()
                ])->setStatusCode(409);
            }            
            $philippineTimezone = new \DateTimeZone('Asia/Manila');
            $currentDateTime = new \DateTime('now', $philippineTimezone);
            
            $enrollmentData = [
                'user_id' => $user_id,
                'course_id' => $course_id,
                'enrollment_date' => $currentDateTime->format('Y-m-d H:i:s')
            ];

            // 10. ATTEMPT TO ENROLL USER
            $enrollmentResult = $this->enrollmentModel->enrollUser($enrollmentData);

            if ($enrollmentResult) {
                // Success: User enrolled successfully
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Successfully enrolled in the course!',
                    'data' => [
                        'enrollment_id' => $enrollmentResult,
                        'user_id' => $user_id,
                        'course_id' => $course_id,                        'enrollment_date' => $enrollmentData['enrollment_date'],
                        'enrollment_date_formatted' => $currentDateTime->format('M j, Y g:iA')
                    ],
                    'csrf_hash' => csrf_hash() // Provide new token for next request
                ])->setStatusCode(201);
            } else {
                // Enrollment failed for some reason
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to enroll in the course. Please try again later.',
                    'error_code' => 'ENROLLMENT_FAILED',
                    'csrf_hash' => csrf_hash()
                ])->setStatusCode(500);
            }

        } catch (\Exception $e) {
            // Log the error for debugging
            log_message('error', 'Course enrollment error: ' . $e->getMessage());
            
            // Return generic error message to user
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An unexpected error occurred. Please try again later.',
                'error_code' => 'INTERNAL_ERROR',
                'csrf_hash' => csrf_hash()
            ])->setStatusCode(500);
        }
    }
}
