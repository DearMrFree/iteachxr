<?php
require_once __DIR__ . '/../auth/session.php';
$current_user = auth_require();
require_once __DIR__ . '/../lib/db_connection.php';

$db = get_db_connection();
$pageTitle = "Student Dashboard — iTeachXR";

// Resolve user record
$stmt = $db->prepare("SELECT id, firstname, lastname FROM users WHERE email = ?");
$stmt->execute([$current_user['email']]);
$urow = $stmt->fetch(PDO::FETCH_ASSOC);
$uid  = $urow ? (int)$urow['id'] : 0;
$displayName = $current_user['name'] ?: (($urow['firstname'] ?? '') . ' ' . ($urow['lastname'] ?? ''));

// Student profile (GPA, grade, etc.)
$profile = [];
$gpa = '4.00'; $gradeLevel = 10; $totalCredits = 240; $enrollStatus = 'Good Standing';
if ($uid) {
    $p = $db->prepare("SELECT * FROM student_profiles WHERE user_id = ?");
    $p->execute([$uid]);
    $profile = $p->fetch(PDO::FETCH_ASSOC) ?: [];
    $gradeLevel   = $profile['current_grade']    ?? 10;
    $enrollStatus = $profile['enrollment_status'] ?? 'Good Standing';
    // Compute GPA from transcript
    $gpaRow = $db->prepare("SELECT COUNT(*) as cnt, SUM(credits) as tc FROM transcript_entries WHERE user_id = ?");
    $gpaRow->execute([$uid]);
    $gr = $gpaRow->fetch(PDO::FETCH_ASSOC);
    $totalCredits = (int)($gr['tc'] ?? 240);
}

$gradeNames = [9=>'Freshman',10=>'Sophomore',11=>'Junior',12=>'Senior'];
$gradeName  = $gradeNames[$gradeLevel] ?? "Grade $gradeLevel";

// Recent transcript entries (last 8 courses)
$recent = [];
if ($uid) {
    $r = $db->prepare("SELECT subject_area, course_title, grade, credits, school_year
                       FROM transcript_entries WHERE user_id = ?
                       ORDER BY grade_level DESC, seq ASC LIMIT 8");
    $r->execute([$uid]);
    $recent = $r->fetchAll(PDO::FETCH_ASSOC);
}

// Enrolled LMS courses
$courses = [];
if ($uid) {
    $c = $db->prepare("SELECT c.id, c.title, c.code, e.completion_percentage,
                              u.firstname||' '||u.lastname AS instructor
                       FROM enrollments e
                       JOIN courses c ON c.id=e.course_id
                       LEFT JOIN users u ON u.id=c.created_by
                       WHERE e.user_id=? AND e.role='student' AND c.is_active=TRUE
                       ORDER BY e.enrolled_at DESC LIMIT 4");
    $c->execute([$uid]);
    $courses = $c->fetchAll(PDO::FETCH_ASSOC);
}

// Grade counts from transcript
$gradeCount = ['A+'=>0,'A'=>0,'A-'=>0];
if ($uid) {
    $gc = $db->prepare("SELECT grade, COUNT(*) as n FROM transcript_entries WHERE user_id=? GROUP BY grade");
    $gc->execute([$uid]);
    foreach ($gc->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $gradeCount[$row['grade']] = (int)$row['n'];
    }
}
$totalCourses = array_sum($gradeCount);
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
:root { --primary:#1a3a6b; --gold:#F9A602; --bg:#f4f6fb; }
body { font-family:'Poppins',sans-serif; background:var(--bg); color:#2d3748; }
.sidebar { background:linear-gradient(180deg,#1a3a6b 0%,#0d1f3c 100%); min-height:100vh; width:260px; position:fixed; top:0; left:0; overflow-y:auto; z-index:100; }
.main-wrap { margin-left:260px; }
.topbar { background:#fff; border-bottom:1px solid #e8ecf4; padding:1rem 2rem; position:sticky; top:0; z-index:99; }
.content-area { padding:2rem; }
.stat-card { border:none; border-radius:14px; padding:1.5rem; position:relative; overflow:hidden; }
.stat-card .stat-icon { position:absolute; right:1.25rem; top:50%; transform:translateY(-50%); opacity:0.15; font-size:3rem; }
.welcome-banner { background:linear-gradient(135deg,var(--primary) 0%,#2c5364 100%); border-radius:16px; color:#fff; padding:2rem; position:relative; overflow:hidden; }
.welcome-banner::after { content:''; position:absolute; right:-40px; top:-40px; width:200px; height:200px; border-radius:50%; background:rgba(249,166,2,0.15); }
.gpa-ring { width:90px; height:90px; border-radius:50%; background:conic-gradient(var(--gold) 100%, rgba(255,255,255,0.15) 0); display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.gpa-ring-inner { width:68px; height:68px; border-radius:50%; background:linear-gradient(135deg,var(--primary),#2c5364); display:flex; flex-direction:column; align-items:center; justify-content:center; }
.course-row { border-radius:10px; border:1px solid #e8ecf4; padding:0.85rem 1rem; background:#fff; margin-bottom:0.5rem; transition:box-shadow .15s; }
.course-row:hover { box-shadow:0 4px 16px rgba(26,58,107,0.1); }
.grade-badge { font-size:0.75rem; font-weight:700; padding:0.3rem 0.6rem; border-radius:6px; }
.grade-A-plus  { background:#d4edda; color:#155724; }
.grade-A       { background:#cce5ff; color:#004085; }
.grade-A-minus { background:#fff3cd; color:#856404; }
.transcript-row td { padding:0.5rem 0.75rem; font-size:0.82rem; vertical-align:middle; }
.section-title { font-size:0.7rem; font-weight:700; letter-spacing:0.1em; text-transform:uppercase; color:#8896a7; margin-bottom:0.75rem; }
@media(max-width:768px){ .sidebar{position:relative;width:100%;min-height:auto;} .main-wrap{margin-left:0;} }
</style>
</head>
<body>
<div class="d-flex">
    <nav class="sidebar">
        <?php include __DIR__ . '/../includes/student_sidebar.php'; ?>
    </nav>
    <div class="main-wrap flex-grow-1">
        <!-- Topbar -->
        <div class="topbar d-flex align-items-center justify-content-between">
            <div>
                <span class="fw-semibold" style="color:var(--primary);">Student Portal</span>
                <span class="text-muted ms-2" style="font-size:0.82rem;">The VR School</span>
            </div>
            <div class="d-flex align-items-center gap-3">
                <a href="/student/transcript.php" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-scroll me-1"></i> View Transcript
                </a>
                <small class="text-muted"><?= date('F j, Y') ?></small>
            </div>
        </div>

        <div class="content-area">
            <!-- Welcome Banner -->
            <div class="welcome-banner mb-4">
                <div class="d-flex align-items-center gap-4 flex-wrap">
                    <div class="gpa-ring">
                        <div class="gpa-ring-inner">
                            <span style="font-size:1.1rem;font-weight:700;color:var(--gold);"><?= $gpa ?></span>
                            <span style="font-size:0.58rem;color:rgba(255,255,255,0.7);">GPA</span>
                        </div>
                    </div>
                    <div>
                        <h3 class="fw-bold mb-0">Welcome back, <?= htmlspecialchars(explode(' ', trim($displayName))[0]) ?>!</h3>
                        <p class="mb-1" style="opacity:.85;"><?= $gradeName ?> · <?= htmlspecialchars($enrollStatus) ?> · The VR School Stanford</p>
                        <div class="d-flex gap-2 flex-wrap mt-2">
                            <span class="badge" style="background:rgba(249,166,2,0.25);color:var(--gold);">
                                <i class="fas fa-star me-1"></i><?= $totalCredits ?> Credits Earned
                            </span>
                            <span class="badge" style="background:rgba(255,255,255,0.15);color:#fff;">
                                <i class="fas fa-book me-1"></i><?= $totalCourses ?> Courses Completed
                            </span>
                            <?php if (!empty($profile['student_id'])): ?>
                            <span class="badge" style="background:rgba(255,255,255,0.1);color:rgba(255,255,255,0.7);">
                                ID: <?= htmlspecialchars($profile['student_id']) ?>
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stat Row -->
            <div class="row g-3 mb-4">
                <div class="col-6 col-lg-3">
                    <div class="stat-card" style="background:linear-gradient(135deg,#1a3a6b,#2c5364);color:#fff;">
                        <div class="stat-icon"><i class="fas fa-star"></i></div>
                        <div style="font-size:1.8rem;font-weight:700;"><?= $gpa ?></div>
                        <div style="font-size:0.78rem;opacity:.8;">Cumulative GPA</div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="stat-card" style="background:linear-gradient(135deg,#d4a017,#f9a602);color:#fff;">
                        <div class="stat-icon"><i class="fas fa-graduation-cap"></i></div>
                        <div style="font-size:1.8rem;font-weight:700;"><?= $totalCredits ?></div>
                        <div style="font-size:0.78rem;opacity:.8;">Credits Earned</div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="stat-card" style="background:linear-gradient(135deg,#1a6b4b,#2c7a59);color:#fff;">
                        <div class="stat-icon"><i class="fas fa-book-open"></i></div>
                        <div style="font-size:1.8rem;font-weight:700;"><?= $totalCourses ?></div>
                        <div style="font-size:0.78rem;opacity:.8;">Courses on Transcript</div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="stat-card" style="background:linear-gradient(135deg,#6b1a5e,#8c2a7a);color:#fff;">
                        <div class="stat-icon"><i class="fas fa-layer-group"></i></div>
                        <div style="font-size:1.8rem;font-weight:700;"><?= $gradeName ?></div>
                        <div style="font-size:0.78rem;opacity:.8;">Current Grade Level</div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <!-- Recent Transcript -->
                <div class="col-lg-7">
                    <div class="card border-0 shadow-sm" style="border-radius:14px;">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="section-title mb-0">Recent Academic Record</div>
                                <a href="/student/transcript.php" class="text-decoration-none" style="font-size:0.8rem;color:var(--primary);">
                                    Full Transcript <i class="fas fa-arrow-right ms-1"></i>
                                </a>
                            </div>
                            <?php if (empty($recent)): ?>
                            <p class="text-muted text-center py-3">No transcript entries found.</p>
                            <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-borderless mb-0">
                                    <thead>
                                        <tr style="font-size:0.72rem;color:#8896a7;text-transform:uppercase;letter-spacing:.05em;">
                                            <th>Course</th>
                                            <th>Subject</th>
                                            <th class="text-center">Grade</th>
                                            <th class="text-center">Credits</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($recent as $row):
                                        $gclass = 'grade-' . str_replace('+','-plus', str_replace('-','-minus', $row['grade']));
                                    ?>
                                    <tr class="transcript-row">
                                        <td class="fw-medium" style="max-width:200px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                            <?= htmlspecialchars($row['course_title']) ?>
                                        </td>
                                        <td class="text-muted"><?= htmlspecialchars($row['subject_area']) ?></td>
                                        <td class="text-center">
                                            <span class="grade-badge <?= $gclass ?>"><?= htmlspecialchars($row['grade']) ?></span>
                                        </td>
                                        <td class="text-center text-muted"><?= (int)$row['credits'] ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- LMS Courses + Quick Actions -->
                <div class="col-lg-5">
                    <!-- LMS Courses -->
                    <div class="card border-0 shadow-sm mb-3" style="border-radius:14px;">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="section-title mb-0">iTeachXR Courses</div>
                                <a href="/student/courses.php" style="font-size:0.8rem;color:var(--primary);text-decoration:none;">All <i class="fas fa-arrow-right ms-1"></i></a>
                            </div>
                            <?php if (empty($courses)): ?>
                            <p class="text-muted" style="font-size:0.85rem;">No active enrollments.</p>
                            <?php else: ?>
                            <?php foreach ($courses as $c):
                                $pct = (int)$c['completion_percentage'];
                                $pc  = $pct>=80?'#28a745':($pct>=40?'#ffc107':'#4b6cb7');
                            ?>
                            <div class="course-row">
                                <div class="fw-medium" style="font-size:0.85rem;color:var(--primary);"><?= htmlspecialchars($c['title']) ?></div>
                                <div class="text-muted" style="font-size:0.75rem;"><?= htmlspecialchars($c['instructor'] ?? '') ?></div>
                                <div class="d-flex align-items-center gap-2 mt-1">
                                    <div class="flex-grow-1" style="background:#e8ecf4;border-radius:3px;height:5px;">
                                        <div style="width:<?= $pct ?>%;background:<?= $pc ?>;height:5px;border-radius:3px;transition:width .3s;"></div>
                                    </div>
                                    <small style="color:<?= $pc ?>;font-weight:600;min-width:30px;font-size:0.72rem;"><?= $pct ?>%</small>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="card border-0 shadow-sm" style="border-radius:14px;">
                        <div class="card-body p-4">
                            <div class="section-title">Quick Actions</div>
                            <div class="d-grid gap-2">
                                <a href="/student/transcript.php" class="btn btn-primary btn-sm">
                                    <i class="fas fa-scroll me-2"></i>View Full Transcript
                                </a>
                                <a href="/student/transcript.php?print=1" target="_blank" class="btn btn-outline-secondary btn-sm">
                                    <i class="fas fa-print me-2"></i>Print / Download Transcript
                                </a>
                                <a href="/ai_demo.php" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-robot me-2"></i>AI Tutor
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
