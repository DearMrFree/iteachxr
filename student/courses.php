<?php
require_once __DIR__ . '/../auth/session.php';
$current_user = auth_require();
require_once __DIR__ . '/../lib/db_connection.php';

$db = get_db_connection();
$pageTitle = "My Courses — iTeachXR";

// Get this student's user_id
$stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$current_user['email']]);
$uid = (int)$stmt->fetchColumn();

// Fetch enrolled courses with progress
$courses = [];
if ($uid) {
    $stmt = $db->prepare("
        SELECT c.id, c.title, c.description, c.code, c.level,
               e.completion_percentage, e.enrolled_at,
               u.firstname || ' ' || u.lastname AS instructor
        FROM enrollments e
        JOIN courses c ON c.id = e.course_id
        LEFT JOIN users u ON u.id = c.created_by
        WHERE e.user_id = ? AND e.role = 'student' AND c.is_active = TRUE
        ORDER BY c.title
    ");
    $stmt->execute([$uid]);
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$levelColors = [
    'undergraduate' => '#1a3a6b',
    'graduate'      => '#6b2d5e',
];

function progressColor(int $pct): string {
    if ($pct >= 80) return '#28a745';
    if ($pct >= 40) return '#ffc107';
    return '#4b6cb7';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= htmlspecialchars($pageTitle) ?></title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
:root { --primary:#1a3a6b; --gold:#F9A602; --sidebar-bg:linear-gradient(180deg,#1a3a6b 0%,#0d1f3c 100%); }
body { font-family:'Poppins',sans-serif; background:#f4f6fb; }
.sidebar { background:var(--sidebar-bg); min-height:100vh; width:260px; position:fixed; top:0; left:0; overflow-y:auto; z-index:100; }
.main-wrap { margin-left:260px; padding:2rem; }
.course-card { border:none; border-radius:12px; box-shadow:0 2px 12px rgba(26,58,107,0.08); transition:transform .2s,box-shadow .2s; height:100%; }
.course-card:hover { transform:translateY(-4px); box-shadow:0 8px 24px rgba(26,58,107,0.15); }
.level-badge { font-size:0.68rem; font-weight:700; letter-spacing:0.05em; text-transform:uppercase; }
.progress { height:6px; border-radius:3px; }
@media(max-width:768px){ .sidebar{position:relative;width:100%;min-height:auto;} .main-wrap{margin-left:0;} }
</style>
</head>
<body>
<div class="d-flex">
    <nav class="sidebar">
        <?php include __DIR__ . '/../includes/student_sidebar.php'; ?>
    </nav>
    <main class="main-wrap flex-grow-1">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-0" style="color:var(--primary);">My Courses</h2>
                <p class="text-muted mb-0"><?= count($courses) ?> enrolled course<?= count($courses) !== 1 ? 's' : '' ?></p>
            </div>
            <a href="/student/transcript.php" class="btn btn-outline-primary">
                <i class="fas fa-scroll me-2"></i>View Transcript
            </a>
        </div>

        <?php if (empty($courses)): ?>
        <div class="text-center py-5">
            <i class="fas fa-book-open fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">No courses yet</h5>
            <p class="text-muted">Your enrolled courses will appear here.</p>
        </div>
        <?php else: ?>
        <div class="row g-4">
            <?php foreach ($courses as $c):
                $pct = (int)$c['completion_percentage'];
                $bg  = $levelColors[$c['level']] ?? '#1a3a6b';
                $pc  = progressColor($pct);
            ?>
            <div class="col-md-6 col-lg-4">
                <div class="card course-card p-0">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="badge level-badge" style="background:<?= $bg ?>20;color:<?= $bg ?>;">
                                <?= htmlspecialchars($c['level'] ?? 'Course') ?>
                            </span>
                            <?php if ($c['code']): ?>
                            <small class="text-muted fw-mono"><?= htmlspecialchars($c['code']) ?></small>
                            <?php endif; ?>
                        </div>
                        <h5 class="fw-semibold mb-1" style="color:var(--primary);font-size:1rem;">
                            <?= htmlspecialchars($c['title']) ?>
                        </h5>
                        <?php if ($c['instructor']): ?>
                        <p class="text-muted mb-3" style="font-size:0.82rem;">
                            <i class="fas fa-chalkboard-teacher me-1"></i><?= htmlspecialchars($c['instructor']) ?>
                        </p>
                        <?php endif; ?>
                        <?php if ($c['description']): ?>
                        <p class="text-muted mb-3" style="font-size:0.82rem;line-height:1.5;">
                            <?= htmlspecialchars(mb_strimwidth($c['description'], 0, 100, '…')) ?>
                        </p>
                        <?php endif; ?>
                        <div class="mb-1 d-flex justify-content-between">
                            <small class="text-muted">Progress</small>
                            <small class="fw-semibold" style="color:<?= $pc ?>;"><?= $pct ?>%</small>
                        </div>
                        <div class="progress mb-3">
                            <div class="progress-bar" style="width:<?= $pct ?>%;background:<?= $pc ?>;"></div>
                        </div>
                        <div class="d-grid">
                            <a href="/course/view.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-primary">
                                <i class="fas fa-play me-1"></i><?= $pct > 0 ? 'Continue' : 'Start' ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
