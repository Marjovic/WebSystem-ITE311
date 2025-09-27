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
