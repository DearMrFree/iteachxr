<?php
/**
 * Public ecosystem contract for SofAI and sister sites.
 *
 * This endpoint intentionally returns service metadata and aggregate counts
 * only. Private student records stay behind the signed-in dashboards.
 */
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../lib/db_connection.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, max-age=0');

function metric_or_null(?PDO $db, string $sql): ?int {
    if (!$db) {
        return null;
    }

    try {
        $value = $db->query($sql)->fetchColumn();
        return $value === false ? null : (int) $value;
    } catch (Throwable $e) {
        error_log('ecosystem metric failed: ' . $e->getMessage());
        return null;
    }
}

$db = get_db_connection();
$user = auth_user();
$canonicalAuth = getenv('CANONICAL_AUTH_URL') ?: 'https://ai.thevrschool.org';
$publicHost = getenv('APP_DOMAIN') ?: ($_SERVER['HTTP_HOST'] ?? 'iteachxr.com');
$publicUrl = getenv('APP_URL') ?: 'https://' . $publicHost;

$response = [
    'service' => 'iteachxr',
    'name' => 'iTeachXR',
    'role' => 'academic_system_of_record',
    'status' => $db ? 'live' : 'degraded',
    'generated_at' => gmdate(DATE_ATOM),
    'auth' => [
        'canonical_url' => $canonicalAuth,
        'signed_in' => (bool) $user,
        'role' => $user['role'] ?? null,
    ],
    'links' => [
        'home' => $publicUrl . '/',
        'student_dashboard' => $publicUrl . '/student/dashboard',
        'teacher_dashboard' => $publicUrl . '/teacher/dashboard',
        'admin_dashboard' => $publicUrl . '/admin/dashboard',
        'school_of_ai' => 'https://ai.thevrschool.org',
        'the_vr_school' => 'https://www.thevrschool.org',
        'sofai' => 'https://sof.ai',
    ],
    'capabilities' => [
        'google_sso_bridge',
        'student_profiles',
        'teacher_dashboard',
        'admin_dashboard',
        'transcript_entries',
        'uc_a_g_course_records',
    ],
    'metrics' => [
        'users' => metric_or_null($db, 'SELECT COUNT(*) FROM users'),
        'student_profiles' => metric_or_null($db, 'SELECT COUNT(*) FROM student_profiles'),
        'transcript_entries' => metric_or_null($db, 'SELECT COUNT(*) FROM transcript_entries'),
        'students_good_standing' => metric_or_null($db, "SELECT COUNT(*) FROM student_profiles WHERE enrollment_status = 'Good Standing'"),
    ],
];

echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
