<?php
/**
 * Authentication Helper Functions
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

// ─── SESSION HELPERS ──────────────────────────────────────────────────────────

function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        header('Location: ' . url('/login'));
        exit;
    }
}

function getCurrentUser(): ?array {
    if (!isLoggedIn()) return null;
    $db   = getDB();
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch() ?: null;
}

// ─── REGISTER ────────────────────────────────────────────────────────────────

function registerUser(string $username, string $email, string $password, string $fullName = ''): array {
    $db = getDB();

    // Check for existing username / email
    $stmt = $db->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $email]);
    if ($stmt->fetch()) {
        return ['success' => false, 'error' => 'Username or email already exists'];
    }

    $hash = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $db->prepare("INSERT INTO users (username, email, password, full_name) VALUES (?, ?, ?, ?)");
    $stmt->execute([$username, $email, $hash, $fullName]);

    $userId = (int)$db->lastInsertId();

    // FIX: regenerate session ID after privilege change (prevents session fixation)
    session_regenerate_id(true);
    $_SESSION['user_id'] = $userId;

    return ['success' => true, 'user_id' => $userId];
}

// ─── LOGIN ────────────────────────────────────────────────────────────────────

function loginUser(string $login, string $password): array {
    $db   = getDB();
    $stmt = $db->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$login, $login]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        return ['success' => false, 'error' => 'Invalid credentials'];
    }

    // FIX: regenerate session ID after successful login (prevents session fixation)
    session_regenerate_id(true);
    $_SESSION['user_id'] = $user['id'];

    return ['success' => true, 'user' => $user];
}

// ─── LOGOUT (callable from code) ─────────────────────────────────────────────

function logoutUser(): void {
    session_unset();
    session_destroy();
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    header('Location: ' . url('/login'));
    exit;
}

// ─── USER STATS ───────────────────────────────────────────────────────────────

function getUserStats(int $userId): array {
    $db = getDB();

    $stmt = $db->prepare("SELECT COUNT(*) as total FROM qr_codes WHERE user_id = ?");
    $stmt->execute([$userId]);
    $total = (int)$stmt->fetch()['total'];

    $stmt = $db->prepare("SELECT COALESCE(SUM(scans), 0) as total_scans FROM qr_codes WHERE user_id = ?");
    $stmt->execute([$userId]);
    $scans = (int)$stmt->fetch()['total_scans'];

    $stmt = $db->prepare("SELECT type, COUNT(*) as count FROM qr_codes WHERE user_id = ? GROUP BY type ORDER BY count DESC");
    $stmt->execute([$userId]);
    $byType = $stmt->fetchAll();

    return [
        'total_codes' => $total,
        'total_scans' => $scans,
        'by_type'     => $byType,
    ];
}
