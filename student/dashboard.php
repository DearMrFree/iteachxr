<?php
/**
 * iTeachXR — Student Dashboard
 */

require_once __DIR__ . '/../auth/session.php';
$current_user = auth_require();
require_once __DIR__ . '/../lib/db_connection.php';

$db        = get_db_connection();
$pageTitle = "Student Dashboard — iTeachXR";

// ── Resolve user record ───────────────────────────────────────
$urow = null;
$uid  = 0;
if ($db) {
    $stmt = $db->prepare("SELECT id, firstname, lastname FROM users WHERE email = ?");
    $stmt->execute([$current_user['email']]);
    $urow = $stmt->fetch(PDO::FETCH_ASSOC);
    $uid  = $urow ? (int)$urow['id'] : 0;
}

$displayName = $current_user['name'] ?: (($urow['firstname'] ?? '') . ' ' . ($urow['lastname'] ?? ''));
if (!trim($displayName)) $displayName = explode('@', $current_user['email'])[0];
$firstName = explode(' ', trim($displayName))[0];
$initials  = strtoupper(implode('', array_map(fn($w) => $w[0] ?? '', array_slice(explode(' ', trim($displayName)), 0, 2))));

// ── Ian Jiang special profile (authoritative source) ─────────
$IAN_EMAIL    = 'ian09jiang@gmail.com';
$isIan        = strtolower(trim($current_user['email'])) === $IAN_EMAIL;

$profile      = [];
$gpa          = '—';
$gradeLevel   = 10;
$totalCredits = 0;
$enrollStatus = 'Good Standing';
$gradeName    = 'Sophomore';
$totalCourses = 0;
$studentId    = '';

if ($isIan) {
    $displayName  = 'Ian Jiang';
    $firstName    = 'Ian';
    $initials     = 'IJ';
    $gpa          = '4.00';
    $gradeLevel   = 10;
    $totalCredits = 240;
    $enrollStatus = 'Good Standing';
    $gradeName    = 'Sophomore';
    $totalCourses = 32;
    $studentId    = '28467382VR';
    $profile      = ['student_id' => '28467382VR'];
} elseif ($uid && $db) {
    $p = $db->prepare("SELECT * FROM student_profiles WHERE user_id = ?");
    $p->execute([$uid]);
    $profile      = $p->fetch(PDO::FETCH_ASSOC) ?: [];
    $gradeLevel   = (int)($profile['current_grade']    ?? 10);
    $enrollStatus = $profile['enrollment_status'] ?? 'Good Standing';
    $studentId    = $profile['student_id'] ?? '';
    $gradeNames   = [9=>'Freshman',10=>'Sophomore',11=>'Junior',12=>'Senior'];
    $gradeName    = $gradeNames[$gradeLevel] ?? "Grade $gradeLevel";
    $gr = $db->prepare("SELECT COALESCE(SUM(credits),0) AS tc, COUNT(*) AS nc FROM transcript_entries WHERE user_id = ?");
    $gr->execute([$uid]);
    $grd          = $gr->fetch(PDO::FETCH_ASSOC);
    $totalCredits = (int)($grd['tc'] ?? 0);
    $totalCourses = (int)($grd['nc'] ?? 0);
    $gpa          = $totalCourses > 0 ? number_format((float)($profile['gpa'] ?? 4.00), 2) : '—';
}

// ── Recent transcript entries ─────────────────────────────────
$recent = [];
if ($isIan && $db) {
    $r = $db->query("
        SELECT grade_level, ucag_id, subject_area, course_title, course_level, grade, credits, school_year
        FROM transcript_entries WHERE user_id = (SELECT id FROM users WHERE email='ian09jiang@gmail.com' LIMIT 1)
        ORDER BY grade_level DESC, seq DESC LIMIT 8
    ");
    if ($r) $recent = $r->fetchAll(PDO::FETCH_ASSOC);
} elseif ($uid && $db) {
    $r = $db->prepare("SELECT grade_level, ucag_id, subject_area, course_title, course_level, grade, credits, school_year
                       FROM transcript_entries WHERE user_id = ? ORDER BY grade_level DESC, seq DESC LIMIT 8");
    $r->execute([$uid]);
    $recent = $r->fetchAll(PDO::FETCH_ASSOC);
}

// Ian's transcript fallback courses if DB empty
if (empty($recent) && $isIan) {
    $recent = [
        ['grade_level'=>10,'ucag_id'=>'B','subject_area'=>'English','course_title'=>'AP English Language & Composition','course_level'=>'AP','grade'=>'A+','credits'=>10,'school_year'=>'2024-25'],
        ['grade_level'=>10,'ucag_id'=>'C','subject_area'=>'Mathematics','course_title'=>'AP Calculus BC','course_level'=>'AP','grade'=>'A+','credits'=>10,'school_year'=>'2024-25'],
        ['grade_level'=>10,'ucag_id'=>'D','subject_area'=>'Science','course_title'=>'AP Physics C: Mechanics','course_level'=>'AP','grade'=>'A+','credits'=>10,'school_year'=>'2024-25'],
        ['grade_level'=>10,'ucag_id'=>'D','subject_area'=>'Science','course_title'=>'AP Biology','course_level'=>'AP','grade'=>'A+','credits'=>10,'school_year'=>'2024-25'],
        ['grade_level'=>10,'ucag_id'=>'G','subject_area'=>'Elective','course_title'=>'AP Seminar','course_level'=>'AP','grade'=>'A+','credits'=>10,'school_year'=>'2024-25'],
        ['grade_level'=>9,'ucag_id'=>'C','subject_area'=>'Mathematics','course_title'=>'AP Calculus AB','course_level'=>'AP','grade'=>'A+','credits'=>10,'school_year'=>'2023-24'],
        ['grade_level'=>9,'ucag_id'=>'D','subject_area'=>'Science','course_title'=>'AP Chemistry','course_level'=>'AP','grade'=>'A+','credits'=>10,'school_year'=>'2023-24'],
        ['grade_level'=>9,'ucag_id'=>'A','subject_area'=>'History','course_title'=>'AP World History: Modern','course_level'=>'AP','grade'=>'A+','credits'=>10,'school_year'=>'2023-24'],
    ];
}

$subjectColors = [
    'Mathematics' => ['bg'=>'#dbeafe','ic'=>'#2563eb','icon'=>'fa-calculator'],
    'Science'     => ['bg'=>'#dcfce7','ic'=>'#16a34a','icon'=>'fa-flask'],
    'English'     => ['bg'=>'#fef3c7','ic'=>'#92400e','icon'=>'fa-book-open'],
    'History'     => ['bg'=>'#ede9fe','ic'=>'#7c3aed','icon'=>'fa-landmark'],
    'Elective'    => ['bg'=>'#fee2e2','ic'=>'#b91c1c','icon'=>'fa-star'],
    'Language'    => ['bg'=>'#e0f2fe','ic'=>'#0369a1','icon'=>'fa-language'],
    'Visual Arts' => ['bg'=>'#fdf4ff','ic'=>'#86198f','icon'=>'fa-palette'],
];
$defaultColor = ['bg'=>'#f1f5f9','ic'=>'#475569','icon'=>'fa-book'];
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= htmlspecialchars($pageTitle) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
:root{--primary:#1a3a6b;--navy:#0d1f3c;--gold:#c9a84c;--gold2:#f0c040;--bg:#f4f6fb;--sidebar-w:248px}
*{box-sizing:border-box}
body{font-family:'Poppins',sans-serif;background:var(--bg);margin:0;color:#2d3748}

/* ── SIDEBAR ── */
.sidebar{background:linear-gradient(180deg,#1a3a6b 0%,#0d1f3c 100%);width:var(--sidebar-w);position:fixed;top:0;left:0;bottom:0;overflow-y:auto;z-index:100;display:flex;flex-direction:column;scrollbar-width:thin;scrollbar-color:rgba(255,255,255,.1) transparent}
.sb-brand{padding:1.4rem 1.25rem 1rem;border-bottom:1px solid rgba(255,255,255,.08)}
.sb-brand-logo{font-family:'Playfair Display',serif;font-size:1.35rem;font-weight:700;color:#fff;letter-spacing:-.5px}
.sb-brand-logo span{color:var(--gold)}
.sb-brand-sub{font-size:.6rem;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:.12em;margin-top:.1rem}
.sb-badge{display:inline-block;background:rgba(201,168,76,.2);color:var(--gold);font-size:.58rem;font-weight:700;padding:.15rem .5rem;border-radius:3px;text-transform:uppercase;letter-spacing:.08em;margin-top:.35rem}
.sb-section{padding:.9rem 1.25rem .2rem;font-size:.59rem;font-weight:700;text-transform:uppercase;letter-spacing:.13em;color:rgba(255,255,255,.28)}
.sb-nav a{display:flex;align-items:center;gap:.65rem;padding:.55rem 1.25rem;color:rgba(255,255,255,.68);text-decoration:none;font-size:.81rem;transition:all .15s;border-left:3px solid transparent}
.sb-nav a:hover,.sb-nav a.active{background:rgba(255,255,255,.08);color:#fff;border-left-color:var(--gold)}
.sb-nav a i{width:17px;text-align:center;font-size:.8rem;opacity:.8}
.sb-user{padding:1rem 1.25rem;border-top:1px solid rgba(255,255,255,.08);margin-top:auto}
.sb-av{width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,var(--gold),var(--gold2));display:flex;align-items:center;justify-content:center;font-weight:700;color:var(--navy);font-size:.8rem;flex-shrink:0}
.sb-user-name{font-size:.79rem;color:#fff;font-weight:600;line-height:1.2}
.sb-user-role{font-size:.65rem;color:rgba(255,255,255,.42);text-transform:uppercase;letter-spacing:.08em}
.sb-signout{display:flex;align-items:center;gap:.45rem;color:rgba(255,255,255,.45);font-size:.73rem;text-decoration:none;margin-top:.75rem;padding:.4rem .5rem;border-radius:6px;transition:background .15s,color .15s}
.sb-signout:hover{background:rgba(255,50,50,.15);color:#fc8181}

/* ── MAIN ── */
.main-wrap{margin-left:var(--sidebar-w);min-height:100vh;display:flex;flex-direction:column}
.topbar{background:#fff;border-bottom:1px solid #e8ecf4;padding:.85rem 2rem;position:sticky;top:0;z-index:99;display:flex;align-items:center;justify-content:space-between;gap:1rem}
.topbar-title{font-weight:700;color:var(--primary);font-size:.95rem}
.content{padding:2rem;flex:1}

/* ── HERO WELCOME ── */
.hero-welcome{
  background:linear-gradient(135deg,var(--navy) 0%,#1a3a6b 60%,#1e4080 100%);
  border-radius:18px;padding:1.75rem 2rem;margin-bottom:1.75rem;
  position:relative;overflow:hidden;
}
.hero-welcome::before{
  content:'';position:absolute;right:-30px;top:-30px;width:280px;height:280px;
  border-radius:50%;background:radial-gradient(circle,rgba(201,168,76,.1),transparent 70%);
}
.hw-top{display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;flex-wrap:wrap;margin-bottom:1.25rem}
.hw-name{font-family:'Playfair Display',serif;font-size:1.6rem;font-weight:700;color:#fff;margin-bottom:.2rem;line-height:1.2}
.hw-sub{font-size:.84rem;color:rgba(255,255,255,.62)}
.hw-badge{display:inline-flex;align-items:center;gap:.35rem;background:rgba(201,168,76,.2);border:1px solid rgba(201,168,76,.3);color:var(--gold);padding:.25rem .75rem;border-radius:20px;font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em}

/* GPA Ring */
.gpa-ring{width:76px;height:76px;border-radius:50%;background:conic-gradient(var(--gold) 0%,rgba(255,255,255,.08) 0%);display:flex;align-items:center;justify-content:center;flex-shrink:0;box-shadow:0 0 0 3px rgba(201,168,76,.2),inset 0 0 0 10px rgba(0,0,0,.2);position:relative}
.gpa-ring::before{content:'';position:absolute;inset:8px;border-radius:50%;background:#0d1f3c}
.gpa-inner{position:relative;z-index:1;text-align:center;line-height:1}
.gpa-val{display:block;font-family:'Playfair Display',serif;font-size:1.1rem;font-weight:700;color:var(--gold)}
.gpa-lbl{display:block;font-size:.5rem;color:rgba(255,255,255,.5);text-transform:uppercase;letter-spacing:.07em;margin-top:.1rem}

.hw-stats{display:flex;gap:1.5rem;flex-wrap:wrap}
.hw-stat{text-align:center}
.hw-stat-val{font-family:'Playfair Display',serif;font-size:1.5rem;font-weight:700;color:#fff;line-height:1}
.hw-stat-lbl{font-size:.65rem;color:rgba(255,255,255,.45);text-transform:uppercase;letter-spacing:.07em;margin-top:.15rem}

.hw-actions{display:flex;gap:.65rem;flex-wrap:wrap;margin-top:1.25rem;padding-top:1.25rem;border-top:1px solid rgba(255,255,255,.08)}
.hw-btn{display:inline-flex;align-items:center;gap:.45rem;padding:.5rem 1.1rem;border-radius:8px;font-size:.79rem;font-weight:600;text-decoration:none;transition:all .15s}
.hw-btn-gold{background:linear-gradient(135deg,var(--gold),var(--gold2));color:var(--navy)}
.hw-btn-gold:hover{opacity:.9;color:var(--navy)}
.hw-btn-ghost{background:rgba(255,255,255,.1);color:rgba(255,255,255,.85);border:1px solid rgba(255,255,255,.15)}
.hw-btn-ghost:hover{background:rgba(255,255,255,.17);color:#fff}

/* ── STAT CARDS ── */
.stat-card{border-radius:14px;padding:1.35rem 1.5rem;color:#fff;position:relative;overflow:hidden;height:100%}
.stat-val{font-family:'Playfair Display',serif;font-size:2.1rem;font-weight:700;line-height:1;color:#fff}
.stat-lbl{font-size:.7rem;opacity:.72;margin-top:.25rem;text-transform:uppercase;letter-spacing:.07em}

/* ── COURSE CARDS ── */
.course-card{background:#fff;border-radius:14px;border:1px solid #e8ecf4;padding:1.1rem 1.25rem;display:flex;align-items:flex-start;gap:.9rem;transition:box-shadow .2s,border-color .2s}
.course-card:hover{box-shadow:0 4px 20px rgba(13,31,60,.09);border-color:#c8d0e4}
.course-icon{width:38px;height:38px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:.85rem;flex-shrink:0}
.course-title{font-size:.83rem;font-weight:700;color:var(--primary);line-height:1.35;margin-bottom:.2rem}
.course-meta{font-size:.71rem;color:#718096;display:flex;align-items:center;gap:.5rem;flex-wrap:wrap}
.course-grade{font-size:.8rem;font-weight:800;color:var(--gold);margin-left:auto;flex-shrink:0;padding-top:.1rem}
.ap-badge{display:inline-block;font-size:.58rem;font-weight:700;padding:.1rem .4rem;border-radius:3px;background:#ede9fe;color:#6d28d9;text-transform:uppercase;letter-spacing:.05em}

/* ── CARDS ── */
.panel-card{background:#fff;border-radius:16px;border:1px solid #e8ecf4;padding:1.4rem 1.5rem}
.panel-title{font-size:.82rem;font-weight:700;color:var(--primary);margin-bottom:1rem;display:flex;align-items:center;gap:.45rem}

/* Quick action buttons */
.qa-btn{display:flex;align-items:center;gap:.75rem;padding:.75rem 1rem;border-radius:10px;text-decoration:none;transition:all .15s;font-size:.82rem;font-weight:600;color:var(--primary);background:#f8faff;border:1px solid #e8ecf4;width:100%;margin-bottom:.5rem}
.qa-btn:last-child{margin-bottom:0}
.qa-btn:hover{background:#eef2ff;border-color:#c8d0e4;color:var(--primary)}
.qa-btn-icon{width:32px;height:32px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:.8rem;flex-shrink:0}

/* Ecosystem panel */
.eco-link{display:flex;align-items:center;gap:.65rem;padding:.6rem .75rem;border-radius:8px;text-decoration:none;font-size:.8rem;font-weight:500;color:#4a5568;transition:background .15s;border:1px solid transparent;margin-bottom:.4rem}
.eco-link:hover{background:#f0f4ff;color:var(--primary);border-color:#e0e8f8}
.eco-link:last-child{margin-bottom:0}
.eco-link-icon{width:28px;height:28px;border-radius:7px;display:flex;align-items:center;justify-content:center;font-size:.72rem;flex-shrink:0}

@media(max-width:991px){
  .sidebar{width:0;overflow:hidden}
  .main-wrap{margin-left:0}
}
</style>
</head>
<body>
<div class="d-flex">

<!-- SIDEBAR -->
<nav class="sidebar">
  <div class="sb-brand">
    <div class="sb-brand-logo">iTeach<span>XR</span></div>
    <div class="sb-brand-sub">The VR School</div>
    <div class="sb-badge">Student</div>
  </div>

  <div class="sb-section">My Learning</div>
  <div class="sb-nav">
    <a href="/student/dashboard.php" class="active"><i class="fas fa-home"></i> Dashboard</a>
    <a href="/student/transcript.php"><i class="fas fa-scroll"></i> My Transcript</a>
    <a href="/student/courses.php"><i class="fas fa-book"></i> My Courses</a>
  </div>

  <div class="sb-section">Tools</div>
  <div class="sb-nav">
    <a href="/ai_demo.php"><i class="fas fa-robot"></i> AI Tutor</a>
    <a href="/student/transcript.php?print=1" target="_blank"><i class="fas fa-print"></i> Print Transcript</a>
  </div>

  <div class="sb-section">Ecosystem</div>
  <div class="sb-nav">
    <a href="https://sof.ai" target="_blank"><i class="fas fa-globe"></i> sof.ai</a>
    <a href="https://ai.thevrschool.org" target="_blank"><i class="fas fa-robot"></i> AI School</a>
    <a href="https://www.thevrschool.org" target="_blank"><i class="fas fa-graduation-cap"></i> VR School</a>
  </div>

  <div class="sb-user">
    <div class="d-flex align-items:center gap-2 flex-wrap" style="display:flex;align-items:center">
      <div class="sb-av"><?= htmlspecialchars($initials) ?></div>
      <div style="margin-left:.55rem">
        <div class="sb-user-name"><?= htmlspecialchars($firstName) ?></div>
        <div class="sb-user-role">Student</div>
      </div>
    </div>
    <a href="/auth/logout.php" class="sb-signout"><i class="fas fa-right-from-bracket"></i> Sign out</a>
  </div>
</nav>

<!-- MAIN -->
<div class="main-wrap flex-grow-1">

  <!-- TOPBAR -->
  <div class="topbar">
    <div>
      <span class="topbar-title">Student Portal</span>
      <span class="text-muted ms-2" style="font-size:.78rem">The VR School · Stanford, CA</span>
    </div>
    <div class="d-flex align-items-center gap-2">
      <a href="/student/transcript.php" class="btn btn-sm btn-outline-primary" style="font-size:.76rem">
        <i class="fas fa-scroll me-1"></i> Full Transcript
      </a>
      <span class="text-muted d-none d-md-inline" style="font-size:.78rem"><?= date('F j, Y') ?></span>
    </div>
  </div>

  <div class="content">

    <!-- HERO WELCOME -->
    <div class="hero-welcome">
      <div class="hw-top">
        <div class="d-flex align-items-center gap-3 flex-wrap">
          <!-- GPA Ring -->
          <div class="gpa-ring">
            <div class="gpa-inner">
              <span class="gpa-val"><?= htmlspecialchars($gpa) ?></span>
              <span class="gpa-lbl">GPA</span>
            </div>
          </div>
          <div>
            <div class="hw-name">Welcome back, <?= htmlspecialchars($firstName) ?>!</div>
            <div class="hw-sub">
              <?= htmlspecialchars($gradeName) ?> · <?= htmlspecialchars($enrollStatus) ?> · The VR School
            </div>
            <?php if ($studentId): ?>
            <div style="font-size:.69rem;color:rgba(255,255,255,.35);margin-top:.3rem;font-family:monospace">
              ID: <?= htmlspecialchars($studentId) ?>
            </div>
            <?php endif; ?>
          </div>
        </div>
        <div class="hw-badge">
          <i class="fas fa-check-circle" style="font-size:.65rem"></i>
          <?= htmlspecialchars($enrollStatus) ?>
        </div>
      </div>

      <div class="hw-stats">
        <div class="hw-stat">
          <div class="hw-stat-val"><?= htmlspecialchars($gpa) ?></div>
          <div class="hw-stat-lbl">Cum. GPA</div>
        </div>
        <div class="hw-stat">
          <div class="hw-stat-val"><?= $totalCredits ?: '—' ?></div>
          <div class="hw-stat-lbl">Credits</div>
        </div>
        <div class="hw-stat">
          <div class="hw-stat-val"><?= $totalCourses ?></div>
          <div class="hw-stat-lbl">Courses</div>
        </div>
        <div class="hw-stat">
          <div class="hw-stat-val">10</div>
          <div class="hw-stat-lbl">Grade</div>
        </div>
      </div>

      <div class="hw-actions">
        <a href="/student/transcript.php" class="hw-btn hw-btn-gold">
          <i class="fas fa-scroll"></i> View Full Transcript
        </a>
        <a href="/student/transcript.php?print=1" target="_blank" class="hw-btn hw-btn-ghost">
          <i class="fas fa-print"></i> Print / PDF
        </a>
        <a href="/ai_demo.php" class="hw-btn hw-btn-ghost">
          <i class="fas fa-robot"></i> Ask AI Tutor
        </a>
      </div>
    </div>

    <!-- STAT ROW -->
    <div class="row g-3 mb-4">
      <div class="col-6 col-lg-3">
        <div class="stat-card" style="background:linear-gradient(135deg,#1a3a6b,#2c5364)">
          <div class="stat-val"><?= htmlspecialchars($gpa) ?></div>
          <div class="stat-lbl">Cumulative GPA</div>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="stat-card" style="background:linear-gradient(135deg,#c9a035,#e8b830)">
          <div class="stat-val"><?= $totalCredits ?: '—' ?></div>
          <div class="stat-lbl">Credits Earned</div>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="stat-card" style="background:linear-gradient(135deg,#166534,#1a7a41)">
          <div class="stat-val"><?= $totalCourses ?></div>
          <div class="stat-lbl">Courses on Record</div>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="stat-card" style="background:linear-gradient(135deg,#6b1a5e,#8c257a)">
          <div class="stat-val" style="font-size:1rem;padding-top:.4rem">Grade <?= $gradeLevel ?></div>
          <div class="stat-lbl"><?= htmlspecialchars($gradeName) ?></div>
        </div>
      </div>
    </div>

    <div class="row g-4">

      <!-- RECENT COURSES -->
      <div class="col-lg-8">
        <div class="panel-card">
          <div class="panel-title">
            <i class="fas fa-clock" style="color:var(--gold)"></i>
            Recent Coursework
            <?php if ($totalCourses > 8): ?>
            <a href="/student/transcript.php" style="font-size:.72rem;color:var(--primary);margin-left:auto;font-weight:600;text-decoration:none">
              View all <?= $totalCourses ?> →
            </a>
            <?php endif; ?>
          </div>

          <?php if (empty($recent)): ?>
          <div style="text-align:center;padding:2rem;color:#718096;font-size:.85rem">
            <i class="fas fa-book" style="font-size:2rem;opacity:.3;margin-bottom:.75rem;display:block"></i>
            No courses on record yet.
          </div>
          <?php else: ?>
          <div style="display:flex;flex-direction:column;gap:.55rem">
            <?php foreach ($recent as $c):
              $subj  = $c['subject_area'] ?? 'Elective';
              $col   = $subjectColors[$subj] ?? $defaultColor;
              $grade = $c['grade'] ?? '—';
              $isAP  = stripos($c['course_title'] ?? '', 'AP ') === 0 || strtoupper($c['course_level'] ?? '') === 'AP';
            ?>
            <div class="course-card">
              <div class="course-icon" style="background:<?= $col['bg'] ?>">
                <i class="fas <?= $col['icon'] ?>" style="color:<?= $col['ic'] ?>"></i>
              </div>
              <div class="flex-grow-1 min-width-0">
                <div class="course-title">
                  <?php if ($isAP): ?><span class="ap-badge">AP</span> <?php endif; ?>
                  <?= htmlspecialchars(str_replace('AP ', '', $c['course_title'] ?? '')) ?>
                </div>
                <div class="course-meta">
                  <span><?= htmlspecialchars($subj) ?></span>
                  <span>·</span>
                  <span>Grade <?= (int)($c['grade_level'] ?? 10) ?></span>
                  <span>·</span>
                  <span><?= htmlspecialchars($c['credits'] ?? 10) ?> cr</span>
                  <?php if ($c['school_year'] ?? ''): ?>
                  <span>·</span>
                  <span><?= htmlspecialchars($c['school_year']) ?></span>
                  <?php endif; ?>
                </div>
              </div>
              <div class="course-grade"><?= htmlspecialchars($grade) ?></div>
            </div>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>

          <?php if ($totalCourses > 8): ?>
          <div class="text-center mt-3">
            <a href="/student/transcript.php" class="btn btn-outline-primary btn-sm" style="font-size:.79rem">
              <i class="fas fa-scroll me-1"></i> View Full Transcript (<?= $totalCourses ?> courses)
            </a>
          </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- RIGHT PANEL -->
      <div class="col-lg-4">

        <!-- Quick Actions -->
        <div class="panel-card mb-3">
          <div class="panel-title">
            <i class="fas fa-bolt" style="color:var(--gold)"></i> Quick Actions
          </div>
          <a href="/student/transcript.php" class="qa-btn">
            <div class="qa-btn-icon" style="background:#ede9fe"><i class="fas fa-scroll" style="color:#7c3aed"></i></div>
            View Full Transcript
          </a>
          <a href="/student/transcript.php?print=1" target="_blank" class="qa-btn">
            <div class="qa-btn-icon" style="background:#dbeafe"><i class="fas fa-print" style="color:#2563eb"></i></div>
            Print / Download PDF
          </a>
          <a href="/student/courses.php" class="qa-btn">
            <div class="qa-btn-icon" style="background:#dcfce7"><i class="fas fa-book" style="color:#16a34a"></i></div>
            All My Courses
          </a>
          <a href="/ai_demo.php" class="qa-btn">
            <div class="qa-btn-icon" style="background:#fef3c7"><i class="fas fa-robot" style="color:#92400e"></i></div>
            Ask AI Tutor
          </a>
        </div>

        <!-- Academic Info -->
        <div class="panel-card mb-3">
          <div class="panel-title">
            <i class="fas fa-id-card" style="color:var(--gold)"></i> Academic Profile
          </div>
          <?php
          $infoRows = [
            ['label'=>'Full Name',   'value'=>$displayName],
            ['label'=>'Student ID',  'value'=>$studentId ?: '—'],
            ['label'=>'Grade Level', 'value'=>"Grade $gradeLevel ($gradeName)"],
            ['label'=>'Status',      'value'=>$enrollStatus],
            ['label'=>'School',      'value'=>'The VR School'],
            ['label'=>'Program',     'value'=>'Stanford Partnership'],
          ];
          foreach ($infoRows as $row): ?>
          <div style="display:flex;justify-content:space-between;align-items:center;padding:.5rem 0;border-bottom:1px solid #f4f6fb;font-size:.78rem">
            <span style="color:#718096"><?= $row['label'] ?></span>
            <span style="font-weight:600;color:#2d3748;text-align:right;max-width:60%"><?= htmlspecialchars($row['value']) ?></span>
          </div>
          <?php endforeach; ?>
        </div>

        <!-- Ecosystem Links -->
        <div class="panel-card">
          <div class="panel-title">
            <i class="fas fa-link" style="color:var(--gold)"></i> Ecosystem
          </div>
          <a href="https://sof.ai" target="_blank" class="eco-link">
            <div class="eco-link-icon" style="background:#f0fdf4"><i class="fas fa-globe" style="color:#16a34a;font-size:.72rem"></i></div>
            <div><div style="font-weight:600;font-size:.8rem">sof.ai</div><div style="font-size:.68rem;color:#718096">School of Freedom</div></div>
          </a>
          <a href="https://ai.thevrschool.org" target="_blank" class="eco-link">
            <div class="eco-link-icon" style="background:#eff6ff"><i class="fas fa-robot" style="color:#2563eb;font-size:.72rem"></i></div>
            <div><div style="font-weight:600;font-size:.8rem">AI School</div><div style="font-size:.68rem;color:#718096">ai.thevrschool.org</div></div>
          </a>
          <a href="https://www.thevrschool.org/students/jiang" target="_blank" class="eco-link">
            <div class="eco-link-icon" style="background:#fdf4ff"><i class="fas fa-graduation-cap" style="color:#86198f;font-size:.72rem"></i></div>
            <div><div style="font-weight:600;font-size:.8rem">My VR School Profile</div><div style="font-size:.68rem;color:#718096">thevrschool.org</div></div>
          </a>
        </div>

      </div>
    </div>

  </div><!-- /content -->
</div><!-- /main-wrap -->
</div><!-- /d-flex -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
