<?php
/**
 * iTeachXR Session Helper
 * Manages the PHP session created after SSO bridge token verification.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 30 * 24 * 60 * 60,
        'path'     => '/',
        'secure'   => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function auth_user(): ?array {
    if (!empty($_SESSION['user']) && is_array($_SESSION['user'])) {
        return $_SESSION['user'];
    }
    return null;
}

function auth_require(): array {
    $user = auth_user();
    if (!$user) {
        $next = '/' . ltrim($_SERVER['REQUEST_URI'] ?? '/', '/');
        header('Location: /auth/login.php?next=' . urlencode($next));
        exit;
    }
    return $user;
}

function auth_set(array $user): void {
    session_regenerate_id(true);
    $_SESSION['user'] = [
        'email'        => $user['email'],
        'name'         => $user['name'] ?? '',
        'image'        => $user['image'] ?? null,
        'authenticated_at' => time(),
    ];
}

function auth_clear(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}
