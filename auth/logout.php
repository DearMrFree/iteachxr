<?php
/**
 * iTeachXR Logout
 * Clears the local PHP session then signs out of the canonical SSO
 * so the user is fully logged out across all sister sites.
 */

require_once __DIR__ . '/session.php';
auth_clear();

$canonical = rtrim(getenv('CANONICAL_AUTH_URL') ?: 'https://ai.thevrschool.org', '/');
$home      = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'iteachxr.com') . '/';

$signout = $canonical . '/api/auth/sso/signout?next=' . urlencode($home);

header('Location: ' . $signout);
exit;
