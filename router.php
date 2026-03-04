<?php
/**
 * router.php — PHP Built-in Server Router
 *
 * Usage (run from inside the mr_qr_v1 directory):
 *   php -S localhost:8000 router.php
 *
 * This file is ONLY needed for the built-in dev server.
 * On Apache/Nginx the .htaccess handles routing automatically.
 */

$uri  = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri  = rtrim($uri, '/') ?: '/';

// Strip /mr_qr_v1 prefix if accessed with it (compatibility)
$route = preg_replace('#^/mr_qr_v1#', '', $uri) ?: '/';

// Serve real files (CSS, JS, images, etc.) directly
$realFile = __DIR__ . $route;
if ($route !== '/' && file_exists($realFile) && is_file($realFile)) {
    return false; // built-in server serves the file as-is
}

// Route map: clean path → actual PHP file
$routes = [
    '/'              => 'index.php',
    '/login'         => 'auth/login.php',
    '/register'      => 'auth/register.php',
    '/logout'        => 'auth/logout.php',
    '/profile'       => 'auth/profile.php',
    '/dashboard'     => 'dashboard.php',
    '/generate'      => 'generate.php',
    '/history'       => 'history.php',
    '/bulk'          => 'bulk.php',
    '/api/generate'  => 'api/generate.php',
];

if (isset($routes[$route])) {
    // Keep $_GET query string intact
    require __DIR__ . '/' . $routes[$route];
    exit;
}

// 404 fallback
http_response_code(404);
echo '<!doctype html><html><body style="font-family:sans-serif;padding:2rem">
<h2>404 — Page Not Found</h2><p>Route <code>' . htmlspecialchars($route) . '</code> not matched.</p>
<a href="/">← Home</a></body></html>';
