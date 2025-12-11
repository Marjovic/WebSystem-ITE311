<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get(from: '/', to: 'Home::index');
$routes->get(from: '/about', to: 'Home::about');
$routes->get(from: '/contact', to: 'Home::contact');

$routes->get(from: '/register', to: 'Auth::register');
$routes->post(from: '/register', to: 'Auth::register');
$routes->get(from: '/login', to: 'Auth::login');
$routes->post(from: '/login', to: 'Auth::login');
$routes->get(from: '/logout', to: 'Auth::logout');

// Email verification routes
$routes->get('/verify-email/(:any)', 'Auth::verifyEmail/$1');
$routes->post('/resend-verification', 'Auth::resendVerification');

// OTP verification routes (2FA)
$routes->get('/verify-otp', 'Auth::verifyOtp');
$routes->post('/verify-otp', 'Auth::verifyOtp');
$routes->post('/resend-otp', 'Auth::resendOtp');

$routes->get(from: '/dashboard', to: 'Auth::dashboard');
$routes->post(from: '/dashboard', to: 'Auth::dashboard');

// Role-based unified dashboard routes
$routes->get(from: '/admin/dashboard', to: 'Auth::dashboard');
$routes->get(from: '/teacher/dashboard', to: 'Auth::dashboard');
$routes->get(from: '/student/dashboard', to: 'Auth::dashboard');

// Admin management routes
$routes->get(from: '/admin/manage_users', to: 'User::manageUsers');
$routes->post(from: '/admin/manage_users', to: 'User::manageUsers');
$routes->get(from: '/admin/manage_departments', to: 'Department::manageDepartments');
$routes->post(from: '/admin/manage_departments', to: 'Department::manageDepartments');
$routes->get(from: '/admin/manage_assignment_types', to: 'AssignmentType::manageAssignmentTypes');
$routes->post(from: '/admin/manage_assignment_types', to: 'AssignmentType::manageAssignmentTypes');
$routes->get(from: '/admin/manage_grading_periods', to: 'GradingPeriod::manageGradingPeriods');
$routes->post(from: '/admin/manage_grading_periods', to: 'GradingPeriod::manageGradingPeriods');
$routes->get(from: '/admin/manage_grade_components', to: 'GradeComponent::manageGradeComponents');
$routes->post(from: '/admin/manage_grade_components', to: 'GradeComponent::manageGradeComponents');
$routes->get(from: '/admin/manage_terms', to: 'Term::manageTerms');
$routes->post(from: '/admin/manage_terms', to: 'Term::manageTerms');
$routes->get(from: '/admin/manage_courses', to: 'Course::manageCourses');
$routes->post(from: '/admin/manage_courses', to: 'Course::manageCourses');
$routes->get(from: '/admin/manage_prerequisites', to: 'CoursePrerequisite::managePrerequisites');
$routes->post(from: '/admin/manage_prerequisites', to: 'CoursePrerequisite::managePrerequisites');
$routes->get(from: '/admin/manage_offerings', to: 'CourseOfferings::manageOfferings');
$routes->post(from: '/admin/manage_offerings', to: 'CourseOfferings::manageOfferings');
$routes->get(from: '/admin/manage_courses_schedule', to: 'CourseSchedules::manageSchedules');
$routes->post(from: '/admin/manage_courses_schedule', to: 'CourseSchedules::manageSchedules');
$routes->get(from: '/admin/manage_course_instructors', to: 'CourseInstructors::manageInstructors');
$routes->post(from: '/admin/manage_course_instructors', to: 'CourseInstructors::manageInstructors');
$routes->get(from: '/admin/manage_programs', to: 'Program::managePrograms');
$routes->post(from: '/admin/manage_programs', to: 'Program::managePrograms');
$routes->get(from: '/admin/manage_curriculum', to: 'Program::manageCurriculum');
$routes->post(from: '/admin/manage_curriculum', to: 'Program::manageCurriculum');
$routes->get(from: '/admin/manage_enrollments', to: 'Enrollment::manageEnrollments');
$routes->post(from: '/admin/manage_enrollments', to: 'Enrollment::manageEnrollments');

// Course enrollment routes
$routes->post(from: '/course/enroll', to: 'Course::enroll');

// Teacher course management routes
$routes->get(from: '/teacher/courses', to: 'CourseInstructors::teacherCourses');
$routes->post(from: '/teacher/courses', to: 'CourseInstructors::teacherCourses');
$routes->get(from: '/teacher/enroll_student', to: 'Enrollment::teacherEnrollStudent');
$routes->post(from: '/teacher/enroll_student', to: 'Enrollment::teacherEnrollStudent');
$routes->get(from: '/teacher/enrolled_students', to: 'Enrollment::teacherEnrolledStudents');

// AJAX endpoint for teacher bulk enrollment
$routes->post('/teacher/ajax_enroll_students', 'Enrollment::ajaxEnrollStudents');


// Student course management routes
$routes->get(from: '/student/courses', to: 'Auth::studentCourses');

// Material management routes
$routes->get('/admin/course/(:num)/upload', 'Material::upload/$1');
$routes->post('/admin/course/(:num)/upload', 'Material::upload/$1');
$routes->get('/teacher/course/(:num)/upload', 'Material::upload/$1');
$routes->post('/teacher/course/(:num)/upload', 'Material::upload/$1');

// Teacher student management routes  
$routes->post('/teacher/course/remove_student', 'Course::removeStudent');
$routes->post('/teacher/course/add_student', 'Course::addStudent');
$routes->get('/teacher/course/get_available_students', 'Course::getAvailableStudents');

// Legacy material upload route 
$routes->get('/material/upload/(:num)', 'Material::upload/$1');
$routes->post('/material/upload/(:num)', 'Material::upload/$1');
$routes->get(from: '/material/delete/(:num)', to: 'Material::delete/$1');
$routes->get(from: '/material/download/(:num)', to: 'Material::download/$1');

// Material download routes (with enrollment check)
$routes->get('/material/download/(:num)', 'Material::download/$1');
$routes->get('/material/view/(:num)', 'Material::view/$1');

// Notification API routes
$routes->get('/notifications', 'Notifications::get');
$routes->post('/notifications/mark_read/(:num)', 'Notifications::mark_as_read/$1');
$routes->post('/notifications/hide/(:num)', 'Notifications::hide/$1');

// API routes for dynamic data
$routes->get('/api/programs/by-department/(:num)', 'User::getProgramsByDepartment/$1');
