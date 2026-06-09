<?php

/*
 *---------------------------------------------------------------
 * API DISPATCHER for nginx (no .htaccess support)
 *---------------------------------------------------------------
 * 
 * This file is the entry point for all API calls when the server
 * does not support Apache .htaccess rewrites (e.g., nginx).
 *
 * The JS client calls: /api.php?path=/auth/login
 * This script converts the `path` parameter into REQUEST_URI
 * and bootstraps CodeIgniter so the router can match routes.
 */

// Extract the API path from the query string
$apiPath = $_GET['path'] ?? '/';

// Reconstruct additional query parameters (strip 'path' from query string)
$queryString = $_SERVER['QUERY_STRING'] ?? '';
$queryString = preg_replace('/^path=[^&]*&?/', '', $queryString);

// Simulate REQUEST_URI so CodeIgniter router matches /api/... routes
$_SERVER['REQUEST_URI'] = '/api' . $apiPath . ($queryString ? '?' . $queryString : '');
$_SERVER['PATH_INFO']   = '/api' . $apiPath;

// ---------------------------------------------------------------
// BOOTSTRAP (same as index.php)
// ---------------------------------------------------------------

$minPhpVersion = '8.2';
if (version_compare(PHP_VERSION, $minPhpVersion, '<')) {
    header('HTTP/1.1 503 Service Unavailable.', true, 503);
    echo 'PHP 8.2+ required. Current: ' . PHP_VERSION;
    exit(1);
}

define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);

if (getcwd() . DIRECTORY_SEPARATOR !== FCPATH) {
    chdir(FCPATH);
}

require FCPATH . '../app/Config/Paths.php';

$paths = new Config\Paths();
require $paths->systemDirectory . '/Boot.php';
exit(\CodeIgniter\Boot::bootWeb($paths));
