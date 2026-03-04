<?php
/**
 * QR Code Generation Functions
 * Supports 24+ QR code types with customisation
 */

require_once __DIR__ . '/../config/database.php';

// ─── QR TYPES REGISTRY ────────────────────────────────────────────────────────
function getQRTypes(): array {
    return [
        'url'       => ['name' => 'URL / Website',      'icon' => 'link',          'color' => '#4F7BF7',  'desc' => 'Link to any website or webpage'],
        'text'      => ['name' => 'Plain Text',         'icon' => 'type',          'color' => '#8B6CF6',  'desc' => 'Encode any text message'],
        'email'     => ['name' => 'Email',              'icon' => 'mail',          'color' => '#F472B6',  'desc' => 'Pre-filled email with subject & body'],
        'phone'     => ['name' => 'Phone Call',         'icon' => 'phone',         'color' => '#34D399',  'desc' => 'Direct dial a phone number'],
        'sms'       => ['name' => 'SMS Message',        'icon' => 'message-square','color' => '#FBBF24',  'desc' => 'Pre-filled SMS text message'],
        'whatsapp'  => ['name' => 'WhatsApp',           'icon' => 'message-circle','color' => '#25D366',  'desc' => 'Open WhatsApp chat with message'],
        'wifi'      => ['name' => 'WiFi Network',       'icon' => 'wifi',          'color' => '#818CF8',  'desc' => 'Auto-connect to WiFi network'],
        'vcard'     => ['name' => 'vCard Contact',      'icon' => 'user',          'color' => '#38BDF8',  'desc' => 'Share contact information'],
        'event'     => ['name' => 'Calendar Event',     'icon' => 'calendar',      'color' => '#FB923C',  'desc' => 'Add event to calendar'],
        'geo'       => ['name' => 'Location / GPS',     'icon' => 'map-pin',       'color' => '#F87171',  'desc' => 'Share a map location'],
        'upi'       => ['name' => 'UPI Payment',        'icon' => 'credit-card',   'color' => '#A78BFA',  'desc' => 'UPI payment QR code (India)'],
        'bitcoin'   => ['name' => 'Bitcoin Address',    'icon' => 'bitcoin',       'color' => '#F7931A',  'desc' => 'Bitcoin payment address'],
        'youtube'   => ['name' => 'YouTube',            'icon' => 'youtube',       'color' => '#FF0000',  'desc' => 'Link to YouTube video or channel'],
        'twitter'   => ['name' => 'Twitter / X',        'icon' => 'twitter',       'color' => '#1DA1F2',  'desc' => 'Link to Twitter/X profile or post'],
        'instagram' => ['name' => 'Instagram',          'icon' => 'instagram',     'color' => '#E4405F',  'desc' => 'Link to Instagram profile'],
        'facebook'  => ['name' => 'Facebook',           'icon' => 'facebook',      'color' => '#1877F2',  'desc' => 'Link to Facebook page or profile'],
        'linkedin'  => ['name' => 'LinkedIn',           'icon' => 'linkedin',      'color' => '#0A66C2',  'desc' => 'Link to LinkedIn profile or company'],
        'spotify'   => ['name' => 'Spotify',            'icon' => 'music',         'color' => '#1DB954',  'desc' => 'Link to Spotify track, album, or artist'],
        'zoom'      => ['name' => 'Zoom Meeting',       'icon' => 'video',         'color' => '#2D8CFF',  'desc' => 'Join a Zoom meeting directly'],
        'pdf'       => ['name' => 'PDF Document',       'icon' => 'file-text',     'color' => '#EF4444',  'desc' => 'Link to a PDF file URL'],
        'image'     => ['name' => 'Image / Photo',      'icon' => 'image',         'color' => '#EC4899',  'desc' => 'Link to an image URL'],
        'mecard'    => ['name' => 'MeCard Contact',     'icon' => 'contact',       'color' => '#14B8A6',  'desc' => 'Compact contact card format'],
        'appstore'  => ['name' => 'App Store Link',     'icon' => 'smartphone',    'color' => '#007AFF',  'desc' => 'Link to an app on the App Store or Play Store'],
    ];
}

// ─── ENCODE QR CONTENT ────────────────────────────────────────────────────────
/**
 * Given a $type and an array of $data fields, returns the correctly
 * formatted string to encode into the QR code.
 * Used by api/generate.php (the JSON API route).
 */
function encodeQRContent(string $type, array $data): string {
    switch ($type) {
        case 'url':
        case 'image':
        case 'pdf':
        case 'zoom':
        case 'spotify':
        case 'appstore':
            return $data['url'] ?? $data['content'] ?? '';

        case 'text':
            return $data['text'] ?? $data['content'] ?? '';

        // FIX: phone was previously missing — fell through to default and lost the "tel:" prefix
        case 'phone':
            $number = preg_replace('/\s+/', '', $data['number'] ?? $data['phone'] ?? '');
            return $number ? 'tel:' . $number : '';

        case 'sms':
            $phone   = $data['phone'] ?? '';
            $message = $data['message'] ?? '';
            return $phone ? 'sms:' . $phone . ($message ? '?body=' . rawurlencode($message) : '') : '';

        case 'email':
            $email   = $data['email'] ?? '';
            $subject = $data['subject'] ?? '';
            $body    = $data['body'] ?? '';
            if (!$email) return '';
            $url = 'mailto:' . $email;
            $qs  = [];
            if ($subject) $qs[] = 'subject=' . rawurlencode($subject);
            if ($body)    $qs[] = 'body='    . rawurlencode($body);
            return $qs ? $url . '?' . implode('&', $qs) : $url;

        case 'whatsapp':
            $phone   = preg_replace('/[^0-9]/', '', $data['phone'] ?? '');
            $message = $data['message'] ?? '';
            return $phone ? 'https://wa.me/' . $phone . ($message ? '?text=' . rawurlencode($message) : '') : '';

        case 'wifi':
            $ssid   = $data['ssid']   ?? '';
            $pass   = $data['password'] ?? '';
            $enc    = $data['encryption'] ?? 'WPA';
            $hidden = !empty($data['hidden']) ? 'true' : 'false';
            return $ssid ? "WIFI:T:{$enc};S:{$ssid};P:{$pass};H:{$hidden};;" : '';

        case 'vcard':
            $lines   = ['BEGIN:VCARD', 'VERSION:3.0'];
            $lines[] = 'N:'  . ($data['last_name'] ?? '') . ';' . ($data['first_name'] ?? '');
            $lines[] = 'FN:' . trim(($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? ''));
            if (!empty($data['org']))     $lines[] = 'ORG:'   . $data['org'];
            if (!empty($data['title']))   $lines[] = 'TITLE:' . $data['title'];
            if (!empty($data['phone']))   $lines[] = 'TEL:'   . $data['phone'];
            if (!empty($data['email']))   $lines[] = 'EMAIL:' . $data['email'];
            if (!empty($data['url']))     $lines[] = 'URL:'   . $data['url'];
            if (!empty($data['address'])) $lines[] = 'ADR:;;' . $data['address'];
            $lines[] = 'END:VCARD';
            return implode("\n", $lines);

        case 'event':
            $title = $data['title'] ?? '';
            if (!$title) return '';
            $start = str_replace(['-', ':', 'T'], '', $data['start'] ?? '') . '00';
            $end   = str_replace(['-', ':', 'T'], '', $data['end']   ?? '') . '00';
            $lines = ['BEGIN:VEVENT', 'SUMMARY:' . $title];
            if (!empty($data['location']))    $lines[] = 'LOCATION:'    . $data['location'];
            if (!empty($data['description'])) $lines[] = 'DESCRIPTION:' . $data['description'];
            $lines[] = 'DTSTART:' . $start;
            $lines[] = 'DTEND:'   . $end;
            $lines[] = 'END:VEVENT';
            return "BEGIN:VCALENDAR\nVERSION:2.0\n" . implode("\n", $lines) . "\nEND:VCALENDAR";

        case 'geo':
            $lat   = $data['latitude']  ?? ($data['lat'] ?? '0');
            $lng   = $data['longitude'] ?? ($data['lng'] ?? '0');
            $query = $data['query'] ?? '';
            return $query ? "geo:{$lat},{$lng}?q=" . rawurlencode($query) : "geo:{$lat},{$lng}";

        case 'upi':
            $pa = $data['pa'] ?? '';
            if (!$pa) return '';
            $pn = $data['pn'] ?? '';
            $am = $data['amount'] ?? '';
            $tn = $data['note']   ?? '';
            $url = 'upi://pay?pa=' . $pa . '&pn=' . rawurlencode($pn);
            if ($am) $url .= '&am=' . $am;
            if ($tn) $url .= '&tn=' . rawurlencode($tn);
            return $url;

        case 'bitcoin':
            $addr   = $data['address'] ?? '';
            $amount = $data['amount']  ?? '';
            return $addr ? 'bitcoin:' . $addr . ($amount ? '?amount=' . $amount : '') : '';

        case 'youtube':
            $id = $data['video_id'] ?? $data['url'] ?? '';
            if ($id && strpos($id, 'http') === false) $id = 'https://youtube.com/watch?v=' . $id;
            return $id;

        case 'twitter':
            $handle = ltrim($data['handle'] ?? '', '@');
            return $handle ? 'https://twitter.com/' . $handle : '';

        case 'instagram':
            $handle = ltrim($data['handle'] ?? '', '@');
            return $handle ? 'https://instagram.com/' . $handle : '';

        case 'facebook':
            $url = $data['url'] ?? $data['handle'] ?? '';
            if ($url && strpos($url, 'http') === false) $url = 'https://facebook.com/' . $url;
            return $url;

        case 'linkedin':
            $url = $data['url'] ?? $data['handle'] ?? '';
            if ($url && strpos($url, 'http') === false) $url = 'https://linkedin.com/in/' . $url;
            return $url;

        case 'mecard':
            $n   = ($data['last_name'] ?? '') . ',' . ($data['first_name'] ?? '');
            $str = 'MECARD:N:' . $n . ';';
            if (!empty($data['phone'])) $str .= 'TEL:'   . $data['phone'] . ';';
            if (!empty($data['email'])) $str .= 'EMAIL:' . $data['email'] . ';';
            $str .= ';';
            return $str;

        default:
            return $data['content'] ?? $data['text'] ?? '';
    }
}

// ─── SAVE QR TO DATABASE ──────────────────────────────────────────────────────
function saveQRCode(int $userId, string $type, string $title, string $content, string $filename, array $settings = []): int {
    $db   = getDB();
    $stmt = $db->prepare("INSERT INTO qr_codes (user_id, type, title, content, filename, settings) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$userId, $type, $title, $content, $filename, json_encode($settings)]);
    return (int)$db->lastInsertId();
}

// ─── GET USER'S QR CODES ──────────────────────────────────────────────────────
function getUserQRCodes(int $userId, int $limit = 50, int $offset = 0, string $type = ''): array {
    $db  = getDB();
    $sql = "SELECT * FROM qr_codes WHERE user_id = ?";
    $params = [$userId];
    if ($type) { $sql .= " AND type = ?"; $params[] = $type; }
    $sql     .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

// ─── DELETE QR CODE ───────────────────────────────────────────────────────────
function deleteQRCode(int $id, int $userId): bool {
    $db   = getDB();
    $stmt = $db->prepare("DELETE FROM qr_codes WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $userId]);
    return $stmt->rowCount() > 0;
}

// ─── GENERATE FILENAME ────────────────────────────────────────────────────────
function generateFilename(string $type): string {
    return 'qr_' . preg_replace('/[^a-z0-9]/', '', $type) . '_' . uniqid() . '_' . time() . '.png';
}

// ─── FLASH MESSAGES ───────────────────────────────────────────────────────────
function setFlash(string $type, string $message): void {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash(): ?array {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// ─── CSRF TOKEN ───────────────────────────────────────────────────────────────
function generateCSRF(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRF(string $token): bool {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
