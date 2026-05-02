<?php
/**
 * PHP built-in server router for iTeachXR
 * Maps clean URL paths to PHP files and serves static assets.
 */

$uri  = $_SERVER['REQUEST_URI'];
$path = parse_url($uri, PHP_URL_PATH);

// Clean path — strip trailing slash for matching (except root)
$clean = rtrim($path, '/') ?: '/';

$routes = [
    // Auth
    '/api/auth/sso/finish' => __DIR__ . '/api/auth/sso/finish.php',
    '/auth/logout'         => __DIR__ . '/auth/logout.php',
    '/auth/login'          => __DIR__ . '/auth/login.php',

    // Student portal
    '/student'             => __DIR__ . '/student/dashboard.php',
    '/student/dashboard'   => __DIR__ . '/student/dashboard.php',
    '/student/courses'     => __DIR__ . '/student/courses.php',
    '/student/transcript'  => __DIR__ . '/student/transcript.php',

    // Teacher portal
    '/teacher'             => __DIR__ . '/teacher/dashboard.php',
    '/teacher/dashboard'   => __DIR__ . '/teacher/dashboard.php',
];

if (isset($routes[$clean])) {
    require $routes[$clean];
    return true;
}

// Serve existing .php / static files normally
return false;
