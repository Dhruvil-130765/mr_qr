<?php
/**
 * API: Generate & Save QR Code
 * POST /api/generate  (JSON body)
 */
header('Content-Type: application/json');

session_start();
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

// ── Auth guard ────────────────────────────────────────────────────────────────
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// ── Parse input (JSON body or form-encoded) ───────────────────────────────────
$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;

$type     = trim($input['type']     ?? 'text');
$data     = $input['data']          ?? [];
$title    = trim($input['title']    ?? '');
$size     = min(1024, max(64, (int)($input['size'] ?? 256)));
$fgColor  = preg_match('/^#[0-9a-fA-F]{6}$/', $input['fg_color'] ?? '') ? $input['fg_color'] : '#000000';
$bgColor  = preg_match('/^#[0-9a-fA-F]{6}$/', $input['bg_color'] ?? '') ? $input['bg_color'] : '#ffffff';

// ── FIX: Validate type against allowed whitelist ──────────────────────────────
$allowedTypes = array_keys(getQRTypes());
if (!in_array($type, $allowedTypes, true)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid QR type: ' . htmlspecialchars($type)]);
    exit;
}

// ── Encode & save ─────────────────────────────────────────────────────────────
try {
    $content = encodeQRContent($type, $data);

    if (empty(trim($content))) {
        throw new Exception('No content to encode. Please fill in the required fields.');
    }

    $filename = generateFilename($type);
    $settings = [
        'fg_color' => $fgColor,
        'bg_color' => $bgColor,
        'size'     => $size,
    ];

    $id = saveQRCode(
        $_SESSION['user_id'],
        $type,
        $title ?: ($type . ' QR'),
        $content,
        $filename,
        $settings
    );

    echo json_encode([
        'success'  => true,
        'id'       => $id,
        'content'  => $content,
        'filename' => $filename,
        'settings' => $settings,
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
