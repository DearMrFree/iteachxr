<?php
/**
 * iTeachXR Login
 * Redirects users to the canonical SSO at ai.thevrschool.org.
 * After they authenticate there (via Google or email), they are sent
 * back to /api/auth/sso/finish with a signed bridge token.
 */

$next  = $_GET['next'] ?? '/teacher/dashboard.php';
$error = $_GET['error'] ?? '';

$canonical = rtrim(getenv('CANONICAL_AUTH_URL') ?: 'https://ai.thevrschool.org', '/');
$domain    = getenv('APP_DOMAIN') ?: 'iteachxr.com';

$handoff = $canonical . '/api/auth/sso/handoff'
    . '?domain=' . urlencode($domain)
    . ($next !== '/' ? '&next=' . urlencode($next) : '');

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign In — iTeachXR</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <style>
        :root {
            --primary: #1a3a6b;
            --gold:    #c9a84c;
        }
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, var(--primary) 0%, #0d1f3c 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Poppins', sans-serif;
        }
        .signin-card {
            background: #fff;
            border-radius: 16px;
            padding: 2.5rem;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        .brand-logo {
            font-size: 1.6rem;
            font-weight: 700;
            color: var(--primary);
            letter-spacing: -0.5px;
        }
        .brand-logo span { color: var(--gold); }
        .btn-google {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            background: #fff;
            color: #333;
            font-weight: 600;
            font-size: 0.95rem;
            text-decoration: none;
            transition: all 0.2s ease;
        }
        .btn-google:hover {
            border-color: var(--primary);
            background: #f8f9ff;
            color: var(--primary);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(26,58,107,0.15);
        }
        .divider {
            text-align: center;
            color: #aaa;
            font-size: 0.85rem;
            margin: 1.5rem 0;
            position: relative;
        }
        .divider::before, .divider::after {
            content: '';
            position: absolute;
            top: 50%;
            width: 42%;
            height: 1px;
            background: #e0e0e0;
        }
        .divider::before { left: 0; }
        .divider::after  { right: 0; }
        .error-box {
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 8px;
            padding: 0.75rem 1rem;
            font-size: 0.875rem;
            color: #664d03;
            margin-bottom: 1.25rem;
        }
        .powered-by {
            text-align: center;
            font-size: 0.75rem;
            color: #999;
            margin-top: 1.5rem;
        }
        .powered-by a { color: #1a3a6b; }
    </style>
</head>
<body>
<div class="signin-card">
    <div class="text-center mb-4">
        <div class="brand-logo">iTeach<span>XR</span></div>
        <p class="text-muted mt-1 mb-0" style="font-size:0.9rem">
            Sign in to your learning platform
        </p>
    </div>

    <?php if ($error): ?>
    <div class="error-box" role="alert">
        <?php
        $messages = [
            'missing_token'  => 'Sign-in link was incomplete. Please try again.',
            'invalid_token'  => 'Sign-in link expired or was invalid. Please try again.',
            'access_denied'  => 'Access was denied. Please sign in with a registered account.',
        ];
        echo htmlspecialchars($messages[$error] ?? 'Sign-in did not complete. Please try again.');
        ?>
    </div>
    <?php endif; ?>

    <a href="<?= htmlspecialchars($handoff) ?>" class="btn-google">
        <svg width="20" height="20" viewBox="0 0 18 18" fill="none" aria-hidden="true">
            <path d="M17.64 9.2c0-.64-.06-1.25-.16-1.84H9v3.48h4.84a4.14 4.14 0 0 1-1.8 2.72v2.26h2.92c1.7-1.57 2.68-3.88 2.68-6.62z" fill="#4285F4"/>
            <path d="M9 18c2.43 0 4.47-.8 5.96-2.18l-2.92-2.26c-.81.55-1.85.87-3.04.87-2.34 0-4.32-1.58-5.03-3.7H.92v2.33A9 9 0 0 0 9 18z" fill="#34A853"/>
            <path d="M3.97 10.73A5.4 5.4 0 0 1 3.68 9c0-.6.1-1.18.29-1.73V4.94H.92A9 9 0 0 0 0 9c0 1.45.35 2.82.92 4.06l3.05-2.33z" fill="#FBBC05"/>
            <path d="M9 3.58c1.32 0 2.5.45 3.44 1.34l2.58-2.58A9 9 0 0 0 9 0 9 9 0 0 0 .92 4.94l3.05 2.33C4.68 5.16 6.66 3.58 9 3.58z" fill="#EA4335"/>
        </svg>
        Continue with Google
    </a>

    <div class="divider">unified school login</div>

    <p class="text-center text-muted" style="font-size:0.85rem">
        Your account works across the entire School of Freedom ecosystem —
        <strong>ai.thevrschool.org</strong>, <strong>sof.ai</strong>, and <strong>iTeachXR</strong>.
    </p>

    <div class="powered-by">
        Secured by <a href="https://ai.thevrschool.org/signin" target="_blank">AI School · School of Freedom</a>
    </div>
</div>
</body>
</html>
