<?php
/**
 * Student Dashboard Preview — auth-bypass view of Ian Jiang's dashboard.
 * DEV ONLY: blocked in production deployments.
 */

// Block in production — only reachable on .replit.dev / localhost dev domains
$_host = $_SERVER['HTTP_HOST'] ?? '';
$_isDev = str_contains($_host, '.replit.dev')
       || str_contains($_host, 'localhost')
       || str_contains($_host, '127.0.0.1')
       || $_host === '';
if (!$_isDev) {
    http_response_code(403);
    header('Content-Type: text/plain');
    echo '403 Forbidden — preview pages are disabled in production.';
    exit;
}

require_once __DIR__ . '/../lib/db_connection.php';

$db = get_db_connection();

$email        = 'ian09jiang@gmail.com';
$displayName  = 'Ian Jiang';
$firstName    = 'Ian';
$gpa          = '—';
$gradeLevel   = 10;
$totalCredits = 0;
$enrollStatus = 'Good Standing';
$gradeName    = 'Sophomore';
$totalCourses = 0;
$profile      = [];
$recent       = [];
$uid          = 0;

if ($db) {
    $u = $db->prepare("SELECT * FROM users WHERE email=?");
    $u->execute([$email]);
    $urow = $u->fetch(PDO::FETCH_ASSOC);
    if ($urow) {
        $uid = (int)$urow['id'];
        $p = $db->prepare("SELECT * FROM student_profiles WHERE user_id=?");
        $p->execute([$uid]);
        $profile = $p->fetch(PDO::FETCH_ASSOC) ?: [];
        $gradeNames   = [9=>'Freshman',10=>'Sophomore',11=>'Junior',12=>'Senior'];
        $gradeLevel   = (int)($profile['current_grade'] ?? 10);
        $gradeName    = $gradeNames[$gradeLevel] ?? "Grade $gradeLevel";
        $enrollStatus = $profile['enrollment_status'] ?? 'Good Standing';
        $gr = $db->prepare("SELECT SUM(credits) AS tc, COUNT(*) AS nc FROM transcript_entries WHERE user_id=?");
        $gr->execute([$uid]);
        $gd = $gr->fetch(PDO::FETCH_ASSOC);
        $totalCredits = (int)($gd['tc'] ?? 0);
        $totalCourses = (int)($gd['nc'] ?? 0);
        $gpa = $totalCourses > 0 ? ($profile['gpa'] ?? '4.00') : '—';
        $r = $db->prepare("SELECT subject_area,course_title,grade,credits,school_year
                           FROM transcript_entries WHERE user_id=?
                           ORDER BY grade_level DESC, seq ASC LIMIT 8");
        $r->execute([$uid]);
        $recent = $r->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Emergency fallback
if (!$uid) {
    $gpa          = '4.00';
    $totalCredits = 240;
    $totalCourses = 32;
    $profile      = ['student_id'=>'28467382VR'];
    $recent = [
        ['subject_area'=>'Mathematics',      'course_title'=>'Advanced Calculus BC',              'grade'=>'A+','credits'=>5,'school_year'=>'2025-2026'],
        ['subject_area'=>'Computer Science',  'course_title'=>'ACC Design as Discovery (Stanford)', 'grade'=>'A+','credits'=>5,'school_year'=>'2025-2026'],
        ['subject_area'=>'Science',           'course_title'=>'ADV Experimental Archaeology in VR', 'grade'=>'A+','credits'=>5,'school_year'=>'2025-2026'],
        ['subject_area'=>'Social Science',    'course_title'=>'ENRCHD Government and Policy',       'grade'=>'A+','credits'=>5,'school_year'=>'2025-2026'],
        ['subject_area'=>'English',           'course_title'=>'ACC College Writing',                'grade'=>'A+','credits'=>5,'school_year'=>'2025-2026'],
        ['subject_area'=>'Science',           'course_title'=>'Advanced Physics C Mechanics',       'grade'=>'A+','credits'=>5,'school_year'=>'2025-2026'],
        ['subject_area'=>'History',           'course_title'=>'CPrep United States History',        'grade'=>'A+','credits'=>5,'school_year'=>'2025-2026'],
        ['subject_area'=>'College Prep',      'course_title'=>'GFTED Research in Tibet',            'grade'=>'A+','credits'=>5,'school_year'=>'2025-2026'],
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Ian Jiang — Student Dashboard — iTeachXR</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
:root{--primary:#1a3a6b;--gold:#F9A602;--bg:#f4f6fb}
*{box-sizing:border-box}
body{font-family:'Poppins',sans-serif;background:var(--bg);color:#2d3748;margin:0}
.sidebar{background:linear-gradient(180deg,#1a3a6b 0%,#0d1f3c 100%);min-height:100vh;width:220px;position:fixed;top:0;left:0;overflow-y:auto;z-index:100}
.main-wrap{margin-left:220px}
.sb-brand{padding:1.25rem 1rem .75rem;border-bottom:1px solid rgba(255,255,255,.1)}
.sb-brand h1{font-family:'Playfair Display',serif;font-size:1.2rem;color:#fff;margin:0}
.sb-brand p{font-size:.6rem;color:rgba(255,255,255,.4);text-transform:uppercase;letter-spacing:.1em;margin:0}
.sb-sec{padding:.75rem 1rem .2rem;font-size:.58rem;font-weight:700;text-transform:uppercase;letter-spacing:.12em;color:rgba(255,255,255,.3)}
.sb-nav a{display:flex;align-items:center;gap:.6rem;padding:.5rem 1rem;color:rgba(255,255,255,.72);text-decoration:none;font-size:.8rem;transition:all .15s}
.sb-nav a:hover,.sb-nav a.active{background:rgba(255,255,255,.1);color:#fff}
.sb-nav a.active{border-left:3px solid var(--gold)}
.sb-nav a i{width:16px;text-align:center;font-size:.8rem}
.sb-user{padding:.75rem 1rem;border-top:1px solid rgba(255,255,255,.1)}
.av{width:32px;height:32px;border-radius:50%;background:var(--gold);display:flex;align-items:center;justify-content:center;font-weight:700;color:#1a3a6b;font-size:.8rem}
.topbar{background:#fff;border-bottom:1px solid #e8ecf4;padding:.8rem 1.5rem;position:sticky;top:0;z-index:99}
.content{padding:1.5rem}
.welcome{background:linear-gradient(135deg,#1a3a6b 0%,#2c5364 100%);border-radius:16px;color:#fff;padding:1.75rem;position:relative;overflow:hidden}
.welcome::after{content:'';position:absolute;right:-40px;top:-40px;width:200px;height:200px;border-radius:50%;background:rgba(249,166,2,.12)}
.gpa-ring{width:80px;height:80px;border-radius:50%;background:conic-gradient(var(--gold) 100%,rgba(255,255,255,.12) 0);display:flex;align-items:center;justify-content:center;flex-shrink:0}
.gpa-inner{width:62px;height:62px;border-radius:50%;background:linear-gradient(135deg,#1a3a6b,#2c5364);display:flex;flex-direction:column;align-items:center;justify-content:center}
.stat-card{border:none;border-radius:12px;padding:1.2rem;color:#fff;position:relative;overflow:hidden}
.stat-icon{position:absolute;right:1rem;top:50%;transform:translateY(-50%);opacity:.13;font-size:2.5rem}
.gc-Ap{background:#c8f0d4;color:#155724}.gc-A{background:#cce5ff;color:#004085}.gc-Am{background:#fff3cd;color:#856404}
.grade-chip{font-size:.72rem;font-weight:700;padding:.16rem .48rem;border-radius:4px;font-family:'Poppins',sans-serif}
.db-badge{display:inline-flex;align-items:center;gap:.3rem;background:#d4edda;color:#155724;font-size:.65rem;font-weight:700;padding:.2rem .55rem;border-radius:12px;font-family:'Poppins',sans-serif}
</style>
</head>
<body>
<div class="d-flex">
<nav class="sidebar">
    <div class="sb-brand"><h1>iTeachXR</h1><p>Student Portal</p></div>
    <div class="flex-grow-1 py-2">
        <div class="sb-sec">My Learning</div>
        <div class="sb-nav">
            <a href="#" class="active"><i class="fas fa-gauge-high"></i> Dashboard</a>
            <a href="#"><i class="fas fa-book-open"></i> My Courses</a>
            <a href="#"><i class="fas fa-scroll"></i> Transcript</a>
        </div>
        <div class="sb-sec">Account</div>
        <div class="sb-nav">
            <a href="#"><i class="fas fa-robot"></i> AI Tutor</a>
            <a href="#"><i class="fas fa-right-from-bracket"></i> Sign Out</a>
        </div>
    </div>
    <div class="sb-user">
        <div class="d-flex align-items-center gap-2">
            <div class="av">I</div>
            <div style="min-width:0">
                <div style="font-size:.78rem;color:#fff;font-weight:600">Ian Jiang</div>
                <div style="font-size:.62rem;color:rgba(255,255,255,.4)">Student · Grade 10</div>
            </div>
        </div>
    </div>
</nav>

<div class="main-wrap flex-grow-1">
    <div class="topbar d-flex align-items-center justify-content-between">
        <div>
            <span class="fw-semibold" style="color:var(--primary)">Student Portal</span>
            <span class="text-muted ms-2" style="font-size:.8rem">The VR School</span>
        </div>
        <div class="d-flex align-items-center gap-2">
            <?php if ($uid): ?>
            <span class="db-badge"><i class="fas fa-circle" style="font-size:.5rem"></i> Live from DB</span>
            <?php endif; ?>
            <small class="text-muted d-none d-md-inline"><?= date('F j, Y') ?></small>
        </div>
    </div>

    <div class="content">
        <!-- Welcome -->
        <div class="welcome mb-3">
            <div class="d-flex align-items-center gap-3 flex-wrap">
                <div class="gpa-ring">
                    <div class="gpa-inner">
                        <span style="font-size:1.05rem;font-weight:700;color:var(--gold)"><?= $gpa ?></span>
                        <span style="font-size:.54rem;color:rgba(255,255,255,.6)">GPA</span>
                    </div>
                </div>
                <div>
                    <h4 class="fw-bold mb-1">Welcome back, Ian!</h4>
                    <p class="mb-2" style="opacity:.82;font-size:.87rem"><?= htmlspecialchars($gradeName) ?> · <?= htmlspecialchars($enrollStatus) ?> · The VR School Stanford</p>
                    <div class="d-flex gap-2 flex-wrap">
                        <span class="badge" style="background:rgba(249,166,2,.25);color:var(--gold)">
                            <i class="fas fa-star me-1"></i><?= $totalCredits ?> Credits
                        </span>
                        <span class="badge" style="background:rgba(255,255,255,.15);color:#fff">
                            <i class="fas fa-book me-1"></i><?= $totalCourses ?> Courses
                        </span>
                        <?php if (!empty($profile['student_id'])): ?>
                        <span class="badge" style="background:rgba(255,255,255,.1);color:rgba(255,255,255,.7)">
                            ID: <?= htmlspecialchars($profile['student_id']) ?>
                        </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats -->
        <div class="row g-3 mb-3">
            <div class="col-6 col-lg-3">
                <div class="stat-card" style="background:linear-gradient(135deg,#1a3a6b,#2c5364)">
                    <div class="stat-icon"><i class="fas fa-star"></i></div>
                    <div style="font-size:1.6rem;font-weight:700"><?= $gpa ?></div>
                    <div style="font-size:.73rem;opacity:.75">Cumulative GPA</div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-card" style="background:linear-gradient(135deg,#c9a035,#f9a602)">
                    <div class="stat-icon"><i class="fas fa-graduation-cap"></i></div>
                    <div style="font-size:1.6rem;font-weight:700"><?= $totalCredits ?></div>
                    <div style="font-size:.73rem;opacity:.75">Credits Earned</div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-card" style="background:linear-gradient(135deg,#1a6b4b,#2c7a59)">
                    <div class="stat-icon"><i class="fas fa-book-open"></i></div>
                    <div style="font-size:1.6rem;font-weight:700"><?= $totalCourses ?></div>
                    <div style="font-size:.73rem;opacity:.75">Courses Completed</div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-card" style="background:linear-gradient(135deg,#6b1a5e,#8c2a7a)">
                    <div class="stat-icon"><i class="fas fa-layer-group"></i></div>
                    <div style="font-size:1.3rem;font-weight:700;padding-top:.1rem"><?= htmlspecialchars($gradeName) ?></div>
                    <div style="font-size:.73rem;opacity:.75">Grade Level</div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <!-- Recent Transcript -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm" style="border-radius:12px">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div style="font-size:.63rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:#8896a7">Recent Academic Record</div>
                            <a href="/student/transcript.php" style="font-size:.78rem;color:var(--primary);text-decoration:none">Full Transcript <i class="fas fa-arrow-right ms-1"></i></a>
                        </div>
                        <table class="table table-borderless mb-0" style="font-size:.8rem">
                            <thead><tr style="font-size:.67rem;color:#8896a7;text-transform:uppercase;letter-spacing:.05em">
                                <th>Course</th>
                                <th class="d-none d-md-table-cell">Subject</th>
                                <th class="text-center">Grade</th>
                                <th class="text-center">Cr.</th>
                            </tr></thead>
                            <tbody>
                            <?php foreach ($recent as $row):
                                $gc='gc-'.(($row['grade']==='A+')?'Ap':(($row['grade']==='A-')?'Am':'A'));
                            ?>
                            <tr>
                                <td class="fw-medium" style="max-width:200px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;padding:.4rem .4rem">
                                    <?= htmlspecialchars($row['course_title']) ?>
                                </td>
                                <td class="text-muted d-none d-md-table-cell" style="padding:.4rem .4rem"><?= htmlspecialchars($row['subject_area']) ?></td>
                                <td class="text-center" style="padding:.4rem .4rem"><span class="grade-chip <?= $gc ?>"><?= htmlspecialchars($row['grade']) ?></span></td>
                                <td class="text-center text-muted" style="padding:.4rem .4rem"><?= (int)$row['credits'] ?></td>
                            </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm" style="border-radius:12px">
                    <div class="card-body p-3">
                        <div style="font-size:.63rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:#8896a7;margin-bottom:.65rem">Quick Actions</div>
                        <div class="d-grid gap-2">
                            <a href="/student/transcript.php" class="btn btn-primary btn-sm"><i class="fas fa-scroll me-2"></i>View Full Transcript</a>
                            <a href="/student/transcript.php?print=1" target="_blank" class="btn btn-outline-secondary btn-sm"><i class="fas fa-print me-2"></i>Print / Download PDF</a>
                            <a href="/student/courses.php" class="btn btn-outline-primary btn-sm"><i class="fas fa-book me-2"></i>My Courses</a>
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
