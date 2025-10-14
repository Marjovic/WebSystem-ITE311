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
$routes->get(from: '/dashboard', to: 'Auth::dashboard');
$routes->post(from: '/dashboard', to: 'Auth::dashboard');

// Role-based unified dashboard routes
$routes->get(from: '/admin/dashboard', to: 'Auth::dashboard');
$routes->get(from: '/teacher/dashboard', to: 'Auth::dashboard');
$routes->get(from: '/student/dashboard', to: 'Auth::dashboard');

// Admin management routes
$routes->get(from: '/admin/manage_users', to: 'Auth::manageUsers');
$routes->post(from: '/admin/manage_users', to: 'Auth::manageUsers');
$routes->get(from: '/admin/manage_courses', to: 'Auth::manageCourses');
$routes->post(from: '/admin/manage_courses', to: 'Auth::manageCourses');

// Course enrollment routes
$routes->post(from: '/course/enroll', to: 'Course::enroll');

// Teacher course management routes
$routes->get(from: '/teacher/courses', to: 'Auth::teacherCourses');
$routes->post(from: '/teacher/courses', to: 'Auth::teacherCourses');

// Student course management routes
$routes->get(from: '/student/courses', to: 'Auth::studentCourses');

// Material management routes
$routes->get('/admin/course/(:num)/upload', 'Material::upload/$1');
$routes->post('/admin/course/(:num)/upload', 'Material::upload/$1');
$routes->get('/teacher/course/(:num)/upload', 'Material::upload/$1');
$routes->post('/teacher/course/(:num)/upload', 'Material::upload/$1');

// Legacy material upload route (for backward compatibility)
$routes->get('/material/upload/(:num)', 'Material::upload/$1');
$routes->post('/material/upload/(:num)', 'Material::upload/$1');

$routes->get(from: '/material/delete/(:num)', to: 'Material::delete/$1');
$routes->get(from: '/material/download/(:num)', to: 'Material::download/$1');
