<?php
/**
 * Logout — properly destroys the session and redirects to login.
 */
session_start();
require_once __DIR__ . '/../config/database.php';

// 1. Clear all session variables from memory
session_unset();

// 2. Destroy the session data on the server
session_destroy();

// 3. Expire the session cookie in the browser
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

header('Location: ' . url('/login'));
exit;
