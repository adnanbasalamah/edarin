<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');

$routes->post('api/auth/login', 'Auth::login');
$routes->post('api/auth/refresh', 'Auth::refresh', ['filter' => 'jwt-auth']);
