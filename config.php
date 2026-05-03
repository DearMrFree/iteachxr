<?php
/**
 * iTeachXR — Application Configuration
 *
 * Deployment-aware: works on Replit dev, Railway, Fly.io, or any host.
 * All secrets are read from environment variables — nothing is hardcoded.
 */

// ── Site URL detection ────────────────────────────────────────
function iteachxr_site_url(): string {
    $explicit = getenv('APP_URL');
    if ($explicit) return rtrim($explicit, '/');

    $host  = $_SERVER['HTTP_HOST'] ?? 'localhost:5000';
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
          || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https'
          || ($_SERVER['SERVER_PORT'] ?? '') === '443';

    return ($https ? 'https' : 'http') . '://' . $host;
}

// ── Auth / SSO ────────────────────────────────────────────────
define('CANONICAL_AUTH_URL', getenv('CANONICAL_AUTH_URL') ?: 'https://ai.thevrschool.org');
define('APP_DOMAIN',         getenv('APP_DOMAIN') ?: ($_SERVER['HTTP_HOST'] ?? 'iteachxr.com'));
define('NEXTAUTH_SECRET',    getenv('NEXTAUTH_SECRET') ?: '');

// ── OpenAI ────────────────────────────────────────────────────
define('OPENAI_API_KEY', getenv('OPENAI_API_KEY') ?: '');

// ── Environment ───────────────────────────────────────────────
$_host  = $_SERVER['HTTP_HOST'] ?? '';
$is_dev = in_array($_host, ['localhost', 'localhost:5000'], true)
       || str_contains($_host, '.replit.dev')
       || str_contains($_host, '.spock.replit.dev');

define('APP_ENV',   getenv('APP_ENV') ?: ($is_dev ? 'development' : 'production'));
define('APP_DEBUG', APP_ENV === 'development');

// ── Legacy $CFG (backwards compat) ───────────────────────────
$CFG = new stdClass();
$CFG->wwwroot        = iteachxr_site_url();
$CFG->openai_api_key = OPENAI_API_KEY;
$CFG->theme          = 'iteachxr';
$CFG->debug          = APP_DEBUG;
$CFG->debugdisplay   = APP_DEBUG;
$CFG->sessiontimeout = 30 * 24 * 60 * 60;
$CFG->lang           = 'en';
$CFG->locale         = 'en_US.UTF-8';
$CFG->enableAI       = true;
$CFG->aiFeatures     = [
    'content_recommendations' => true,
    'automated_feedback'      => true,
    'plagiarism_detection'    => true,
    'personalized_learning'   => true,
    'chatbot_assistant'       => true,
];

date_default_timezone_set('UTC');

require_once __DIR__ . '/lib/db_connection.php';
