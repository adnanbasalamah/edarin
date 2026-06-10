<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');

$routes->post('api/auth/login', 'Auth::login');
$routes->post('api/auth/refresh', 'Auth::refresh', ['filter' => 'jwt-auth']);

$routes->group('api', ['filter' => 'jwt-auth'], static function ($routes) {
    $routes->group('products', static function ($routes) {
        $routes->get('/', 'Products::index');
        $routes->get('(:num)', 'Products::show/$1');
        $routes->post('/', 'Products::create', ['filter' => 'role-check:admin']);
        $routes->put('(:num)', 'Products::update/$1', ['filter' => 'role-check:admin']);
        $routes->delete('(:num)', 'Products::delete/$1', ['filter' => 'role-check:admin']);
    });

    $routes->group('stores', static function ($routes) {
        $routes->get('/', 'Stores::index');
        $routes->get('(:num)', 'Stores::show/$1');
        $routes->post('/', 'Stores::create');
        $routes->put('(:num)', 'Stores::update/$1');
        $routes->delete('(:num)', 'Stores::delete/$1', ['filter' => 'role-check:admin']);
    });

    $routes->group('distributors', static function ($routes) {
        $routes->get('/', 'Distributors::index', ['filter' => 'role-check:admin']);
        $routes->get('(:num)', 'Distributors::show/$1', ['filter' => 'role-check:admin']);
        $routes->post('/', 'Distributors::create', ['filter' => 'role-check:admin']);
        $routes->put('(:num)', 'Distributors::update/$1', ['filter' => 'role-check:admin']);
        $routes->delete('(:num)', 'Distributors::delete/$1', ['filter' => 'role-check:admin']);
    });

    $routes->group('sales', static function ($routes) {
        $routes->get('/', 'Sales::index');
        $routes->get('summary', 'Sales::summary');
        $routes->post('/', 'Sales::create');
    });

    $routes->group('reports', static function ($routes) {
        $routes->get('/', 'Reports::index');
        $routes->get('download', 'Reports::download');
        $routes->get('dashboard', 'Reports::dashboard');
        $routes->get('stats', 'Reports::stats');
    });

    $routes->group('notas', static function ($routes) {
        $routes->get('/', 'Notas::index');
        $routes->get('(:num)', 'Notas::show/$1');
    });

    $routes->group('audit', static function ($routes) {
        $routes->get('/', 'Audit::index', ['filter' => 'role-check:admin']);
    });
});
