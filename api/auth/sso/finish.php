<?php
/**
 * GET /api/auth/sso/finish
 *
 * iTeachXR SSO bridge — receiver side.
 *
 * The canonical auth surface (ai.thevrschool.org) sends visitors here
 * with a short-lived signed bridge token after they authenticate there.
 * We verify the token, set a PHP session, then redirect to $next.
 *
 * Token format (matches lib/sso/bridgeToken.ts on the canonical side):
 *   base64url(JSON payload) . base64url(HMAC-SHA256(payload, NEXTAUTH_SECRET))
 *
 * Payload fields: iss, aud, sub (email), name, image, iat, exp, jti
 */

require_once __DIR__ . '/../../../auth/session.php';
require_once __DIR__ . '/../../../lib/db_connection.php';

function b64url_decode(string $s): string {
    $s = str_replace(['-', '_'], ['+', '/'], $s);
    $pad = strlen($s) % 4;
    if ($pad) $s .= str_repeat('=', 4 - $pad);
    return base64_decode($s);
}

function verify_bridge_token(string $token, string $expected_aud): array|false {
    $secret = getenv('NEXTAUTH_SECRET');
    if (!$secret) {
        error_log('iTeachXR SSO: NEXTAUTH_SECRET not set');
        return false;
    }

    $parts = explode('.', $token, 2);
    if (count($parts) !== 2) return false;
    [$payload_b64, $sig_b64] = $parts;

    $expected_sig = hash_hmac('sha256', $payload_b64, $secret, true);
    $actual_sig   = b64url_decode($sig_b64);

    if (strlen($actual_sig) !== strlen($expected_sig) ||
        !hash_equals($expected_sig, $actual_sig)) {
        error_log('iTeachXR SSO: bad token signature');
        return false;
    }

    $payload = json_decode(b64url_decode($payload_b64), true);
    if (!is_array($payload)) return false;

    if (($payload['exp'] ?? 0) < time()) {
        error_log('iTeachXR SSO: token expired');
        return false;
    }

    if (strtolower($payload['aud'] ?? '') !== strtolower($expected_aud)) {
        error_log('iTeachXR SSO: audience mismatch');
        return false;
    }

    if (!str_contains($payload['sub'] ?? '', '@')) return false;

    return $payload;
}

$token = trim($_GET['token'] ?? '');
$next  = $_GET['next'] ?? '/';

if (!$token) {
    header('Location: /auth/login.php?error=missing_token');
    exit;
}

// The token audience is always "iteachxr.com" — the canonical domain
// registered in the SSO allowlist — regardless of which hostname
// (Railway, custom domain, etc.) this deployment runs on.
$expected_aud = 'iteachxr.com';

$payload = verify_bridge_token($token, $expected_aud);
if (!$payload) {
    header('Location: /auth/login.php?error=invalid_token');
    exit;
}

// Upsert user + auto-provision student profile in Fly.io DB
$db = get_db_connection();
$dbUser = [];
if ($db) {
    $dbUser = db_upsert_user($db, $payload['sub'], $payload['name'] ?? '', $payload['image'] ?? '');
}

$role = $dbUser['role'] ?? 'student';

auth_set([
    'email' => $payload['sub'],
    'name'  => $payload['name'] ?? '',
    'image' => $payload['image'] ?? null,
    'role'  => $role,
]);

// Role-based default redirect when no $next is specified
if ($next === '/') {
    $next = match($role) {
        'admin', 'teacher' => '/admin/dashboard.php',
        default            => '/student/dashboard.php',
    };
}

$next = (str_starts_with($next, '/') && !str_starts_with($next, '//')) ? $next : '/student/dashboard.php';
header('Location: ' . $next);
exit;
