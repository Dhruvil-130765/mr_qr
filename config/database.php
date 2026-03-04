<?php
/**
 * QRCode Pro — Database & App Configuration
 * Uses SQLite for zero-config setup
 */

// ─── AUTO-DETECT BASE PATH ───────────────────────────────────────────────────
// Works correctly whether served from Apache (/mr_qr_v1 subdir) or
// from PHP built-in server (root of the mr_qr_v1 directory).
//
// If you need to force a path, set the APP_BASE_PATH environment variable:
//   APP_BASE_PATH=/mr_qr_v1 php -S localhost:8000 router.php
//
(function () {
    // Use env override if provided
    if (($env = getenv('APP_BASE_PATH')) !== false) {
        define('BASE_PATH', rtrim($env, '/'));
        return;
    }

    // Auto-detect: compare app root against document root
    $docRoot = realpath($_SERVER['DOCUMENT_ROOT'] ?? '') ?: '';
    $appRoot = realpath(__DIR__ . '/..') ?: ''; // config/ lives one level inside app root

    if ($docRoot && $appRoot && strpos($appRoot, $docRoot) === 0) {
        $rel = substr($appRoot, strlen($docRoot));
        $rel = str_replace('\\', '/', $rel); // Windows path normalisation
        define('BASE_PATH', rtrim($rel, '/'));
    } else {
        // Fallback: nothing to prepend (root-level deployment or built-in server)
        define('BASE_PATH', '');
    }
})();

define('DB_PATH',       __DIR__ . '/../data/qrcode_pro.db');
define('APP_NAME',      'QRCode Pro');
define('UPLOAD_DIR',    __DIR__ . '/../uploads/');
define('GENERATED_DIR', __DIR__ . '/../generated/');
define('MAX_BULK_QR',   100);

/**
 * Build a full URL path with the detected base prefix.
 *   url('/dashboard')  →  /mr_qr_v1/dashboard   (Apache subdir)
 *   url('/dashboard')  →  /dashboard             (built-in server root)
 *   url('/')           →  /mr_qr_v1/  or  /
 */
function url(string $path = '/'): string {
    $base = rtrim(BASE_PATH, '/');
    if ($path === '/') return $base . '/';
    return $base . '/' . ltrim($path, '/');
}

// ─── ENSURE RUNTIME DIRECTORIES EXIST ────────────────────────────────────────
foreach ([dirname(DB_PATH), UPLOAD_DIR, GENERATED_DIR] as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

// ─── DATABASE CONNECTION ──────────────────────────────────────────────────────
function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO('sqlite:' . DB_PATH);
        $pdo->setAttribute(PDO::ATTR_ERRMODE,            PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $pdo->exec('PRAGMA journal_mode=WAL');
        $pdo->exec('PRAGMA foreign_keys=ON');
    }
    return $pdo;
}

// ─── SCHEMA INIT ─────────────────────────────────────────────────────────────
function initDatabase(): void {
    $db = getDB();

    $db->exec("CREATE TABLE IF NOT EXISTS users (
        id          INTEGER PRIMARY KEY AUTOINCREMENT,
        username    TEXT UNIQUE NOT NULL,
        email       TEXT UNIQUE NOT NULL,
        password    TEXT NOT NULL,
        full_name   TEXT DEFAULT '',
        avatar      TEXT DEFAULT '',
        plan        TEXT DEFAULT 'free',
        created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at  DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    $db->exec("CREATE TABLE IF NOT EXISTS qr_codes (
        id          INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id     INTEGER NOT NULL,
        type        TEXT NOT NULL,
        title       TEXT DEFAULT '',
        content     TEXT NOT NULL,
        filename    TEXT NOT NULL,
        settings    TEXT DEFAULT '{}',
        scans       INTEGER DEFAULT 0,
        created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");

    $db->exec("CREATE TABLE IF NOT EXISTS bulk_jobs (
        id           INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id      INTEGER NOT NULL,
        job_name     TEXT DEFAULT '',
        total_codes  INTEGER DEFAULT 0,
        zip_filename TEXT DEFAULT '',
        created_at   DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");

    $db->exec("CREATE INDEX IF NOT EXISTS idx_qr_user ON qr_codes(user_id)");
    $db->exec("CREATE INDEX IF NOT EXISTS idx_qr_type ON qr_codes(type)");
}

// Auto-initialize on every request (cheap: all CREATE IF NOT EXISTS)
initDatabase();
