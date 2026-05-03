<?php
/**
 * iTeachXR — Sign In
 *
 * Redirects to the canonical SSO hub at ai.thevrschool.org.
 * After the user authenticates there (Google or magic link) the hub
 * mints a 60-second bridge token and redirects back to
 * /api/auth/sso/finish, which sets the PHP session and lands the user
 * on the correct dashboard.
 *
 * If already signed in, bounce straight to the right dashboard.
 */

require_once __DIR__ . '/../auth/session.php';

// Already signed in → redirect immediately
$existing = auth_user();
if ($existing) {
    $role = $existing['role'] ?? 'student';
    $dest = ($role === 'admin' || $role === 'teacher') ? '/admin/dashboard.php' : '/student/dashboard.php';
    header('Location: ' . $dest);
    exit;
}

$next  = $_GET['next'] ?? '/';
$error = $_GET['error'] ?? '';

$canonical = rtrim(getenv('CANONICAL_AUTH_URL') ?: 'https://ai.thevrschool.org', '/');
$domain    = getenv('APP_DOMAIN') ?: 'iteachxr.com';

// Build the handoff URL — if $next is '/' the SSO finish handler picks
// the role-appropriate dashboard automatically.
$handoff = $canonical . '/api/auth/sso/handoff'
         . '?domain=' . urlencode($domain)
         . ($next !== '/' ? '&next=' . urlencode($next) : '');

$errorMessages = [
    'missing_token'  => 'Sign-in link was incomplete. Please try again.',
    'invalid_token'  => 'Sign-in link expired or was tampered with. Please try again.',
    'bad_signature'  => 'Security check failed. Please try signing in again.',
    'SessionExpired' => 'Your session expired. Please sign in again.',
];
$errorText = $errorMessages[$error] ?? ($error ? 'Sign-in did not complete. Please try again.' : '');
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Sign In — iTeachXR · The VR School</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
:root{--navy:#0d1f3c;--navy2:#1a3a6b;--gold:#c9a84c;--gold2:#f0c040}
*{box-sizing:border-box;margin:0;padding:0}
body{
  min-height:100vh;
  background:linear-gradient(135deg,#060d1a 0%,#0d1f3c 50%,#060d1a 100%);
  font-family:'Poppins',sans-serif;
  display:flex;flex-direction:column;align-items:center;justify-content:center;
  padding:2rem 1rem;
  position:relative;overflow:hidden;
}
body::before{
  content:'';position:absolute;inset:0;
  background-image:linear-gradient(rgba(255,255,255,.025) 1px,transparent 1px),
                   linear-gradient(90deg,rgba(255,255,255,.025) 1px,transparent 1px);
  background-size:52px 52px;
  mask-image:radial-gradient(ellipse 80% 80% at 50% 50%,black,transparent);
}
body::after{
  content:'';position:absolute;
  width:600px;height:600px;border-radius:50%;
  background:radial-gradient(circle,rgba(201,168,76,.07),transparent 70%);
  top:-100px;right:-100px;pointer-events:none;
}

/* Card */
.card{
  background:#fff;border-radius:20px;
  width:100%;max-width:420px;
  padding:2.5rem;
  box-shadow:0 32px 80px rgba(0,0,0,.45),0 0 0 1px rgba(255,255,255,.06);
  position:relative;z-index:1;
}
.brand{text-align:center;margin-bottom:2rem}
.brand-logo{font-family:'Playfair Display',serif;font-size:1.9rem;font-weight:700;color:var(--navy);letter-spacing:-.5px}
.brand-logo span{color:var(--gold)}
.brand-sub{font-size:.78rem;color:#718096;margin-top:.25rem}
.brand-school{font-size:.72rem;color:var(--navy2);font-weight:600;margin-top:.2rem;display:flex;align-items:center;justify-content:center;gap:.35rem}

/* Error */
.error-box{background:#fff3cd;border:1px solid #ffc107;border-radius:10px;padding:.8rem 1rem;font-size:.84rem;color:#664d03;margin-bottom:1.5rem;display:flex;align-items:flex-start;gap:.6rem}

/* SSO button */
.sso-btn{
  display:flex;align-items:center;justify-content:center;gap:.85rem;width:100%;
  padding:.9rem 1.25rem;border-radius:10px;font-weight:600;font-size:.95rem;
  text-decoration:none;cursor:pointer;transition:transform .15s,box-shadow .15s;
  border:none;
}
.sso-btn:hover{transform:translateY(-2px)}
.btn-primary-sso{
  background:linear-gradient(135deg,var(--navy2),var(--navy));color:#fff;
  box-shadow:0 6px 24px rgba(13,31,60,.3);
}
.btn-primary-sso:hover{box-shadow:0 10px 32px rgba(13,31,60,.4);color:#fff}
.btn-google-sso{
  background:#fff;color:#3c4043;border:2px solid #e4e8ee;
  box-shadow:0 2px 8px rgba(0,0,0,.08);
  margin-top:.75rem;
}
.btn-google-sso:hover{border-color:#c8d0e0;box-shadow:0 6px 18px rgba(0,0,0,.12);color:#3c4043}

/* Google glyph */
.g-glyph{width:20px;height:20px;flex-shrink:0}

/* Divider */
.or{display:flex;align-items:center;gap:.75rem;margin:.5rem 0;color:#a0aec0;font-size:.78rem}
.or::before,.or::after{content:'';flex:1;height:1px;background:#e8ecf4}

/* Features list */
.feat-list{margin-top:1.75rem;display:flex;flex-direction:column;gap:.6rem}
.feat-item{display:flex;align-items:center;gap:.7rem;font-size:.8rem;color:#4a5568}
.feat-icon{width:28px;height:28px;border-radius:7px;display:flex;align-items:center;justify-content:center;font-size:.72rem;flex-shrink:0}

/* Footer */
.signin-footer{margin-top:1.5rem;text-align:center;font-size:.72rem;color:rgba(255,255,255,.38);position:relative;z-index:1}
.signin-footer a{color:rgba(255,255,255,.55);text-decoration:none;transition:color .2s}
.signin-footer a:hover{color:var(--gold)}
.signin-footer span{margin:0 .5rem;color:rgba(255,255,255,.2)}

/* VR School badge strip */
.badge-strip{display:flex;justify-content:center;gap:1.25rem;margin-top:1.5rem;flex-wrap:wrap}
.badge-item{font-size:.65rem;color:#718096;display:flex;align-items:center;gap:.3rem}
.badge-dot{width:4px;height:4px;background:var(--gold);border-radius:50%}
</style>
</head>
<body>

<div class="card">

  <div class="brand">
    <div class="brand-logo">iTeach<span>XR</span></div>
    <div class="brand-sub">AI-first Learning Management System</div>
    <div class="brand-school">
      <i class="fas fa-graduation-cap" style="font-size:.7rem;color:var(--gold)"></i>
      The VR School · Stanford, CA
    </div>
  </div>

  <?php if ($errorText): ?>
  <div class="error-box">
    <i class="fas fa-exclamation-triangle" style="color:#ffc107;margin-top:.05rem;flex-shrink:0"></i>
    <span><?= htmlspecialchars($errorText) ?></span>
  </div>
  <?php endif; ?>

  <a href="<?= htmlspecialchars($handoff) ?>" class="sso-btn btn-primary-sso">
    <i class="fas fa-sign-in-alt"></i>
    Sign in with The VR School
  </a>

  <div class="or">or</div>

  <a href="<?= htmlspecialchars($handoff) ?>" class="sso-btn btn-google-sso">
    <svg class="g-glyph" viewBox="0 0 18 18" fill="none" aria-hidden="true">
      <path d="M17.64 9.2c0-.64-.06-1.25-.16-1.84H9v3.48h4.84a4.14 4.14 0 0 1-1.8 2.72v2.26h2.92c1.7-1.57 2.68-3.88 2.68-6.62z" fill="#4285F4"/>
      <path d="M9 18c2.43 0 4.47-.8 5.96-2.18l-2.92-2.26c-.81.55-1.85.87-3.04.87-2.34 0-4.32-1.58-5.03-3.7H.92v2.33A9 9 0 0 0 9 18z" fill="#34A853"/>
      <path d="M3.97 10.73A5.4 5.4 0 0 1 3.68 9c0-.6.1-1.18.29-1.73V4.94H.92A9 9 0 0 0 0 9c0 1.45.35 2.82.92 4.06l3.05-2.33z" fill="#FBBC05"/>
      <path d="M9 3.58c1.32 0 2.5.45 3.44 1.34l2.58-2.58A9 9 0 0 0 9 0 9 9 0 0 0 .92 4.94l3.05 2.33C4.68 5.16 6.66 3.58 9 3.58z" fill="#EA4335"/>
    </svg>
    Continue with Google
  </a>

  <div class="feat-list">
    <div class="feat-item">
      <div class="feat-icon" style="background:#ede9fe"><i class="fas fa-shield-alt" style="color:#7c3aed"></i></div>
      <span>Secured by Google — no passwords stored in iTeachXR</span>
    </div>
    <div class="feat-item">
      <div class="feat-icon" style="background:#dbeafe"><i class="fas fa-link" style="color:#2563eb"></i></div>
      <span>Single sign-on across VR School, sof.ai & AI School</span>
    </div>
    <div class="feat-item">
      <div class="feat-icon" style="background:#dcfce7"><i class="fas fa-bolt" style="color:#16a34a"></i></div>
      <span>Role-based redirect — admin and student portals auto-detected</span>
    </div>
  </div>

  <div class="badge-strip">
    <span class="badge-item"><span class="badge-dot"></span>WASC Accredited</span>
    <span class="badge-item"><span class="badge-dot"></span>UC A-G Approved</span>
    <span class="badge-item"><span class="badge-dot"></span>Fly.io PG17</span>
  </div>

</div>

<div class="signin-footer">
  <a href="/">← Back to iTeachXR</a>
  <span>·</span>
  <a href="https://sof.ai" target="_blank">sof.ai</a>
  <span>·</span>
  <a href="https://ai.thevrschool.org" target="_blank">ai.thevrschool.org</a>
</div>

</body>
</html>
