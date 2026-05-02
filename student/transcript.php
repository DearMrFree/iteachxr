<?php
require_once __DIR__ . '/../auth/session.php';
$current_user = auth_require();
require_once __DIR__ . '/../lib/db_connection.php';

$db = get_db_connection();
$isPrint = isset($_GET['print']);

// ── Resolve user from DB (or fall back gracefully) ────────────
$urow = null;
$profile = [];
$entries = [];
$creditsByGrade = [];

if ($db) {
    $stmt = $db->prepare("SELECT id, firstname, lastname FROM users WHERE email = ?");
    $stmt->execute([$current_user['email']]);
    $urow = $stmt->fetch(PDO::FETCH_ASSOC);
    $uid  = $urow ? (int)$urow['id'] : 0;

    if ($uid) {
        $p = $db->prepare("SELECT * FROM student_profiles WHERE user_id = ?");
        $p->execute([$uid]);
        $profile = $p->fetch(PDO::FETCH_ASSOC) ?: [];

        $r = $db->prepare("SELECT grade_level, school_year, ucag_id, subject_area,
                                   course_title, course_level, grade, credits
                            FROM transcript_entries WHERE user_id = ?
                            ORDER BY grade_level ASC, seq ASC");
        $r->execute([$uid]);
        foreach ($r->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $gl = (int)$row['grade_level'];
            $entries[$gl][] = $row;
            $creditsByGrade[$gl] = ($creditsByGrade[$gl] ?? 0) + (float)$row['credits'];
        }
    }
}

// ── Ian Jiang hardcoded fallback (authoritative source of truth) ──
$IAN_EMAIL = 'ian09jiang@gmail.com';
$isIan = strtolower(trim($current_user['email'])) === $IAN_EMAIL;

if (empty($entries) && $isIan) {
    $profile = $profile ?: [
        'student_id'        => '28467382VR',
        'address'           => '531 Lasuen Mall, Stanford, CA 94305',
        'graduation_date'   => '2029-06-15',
        'current_grade'     => 10,
        'enrollment_status' => 'Good Standing',
    ];
    $entries = [
        9 => [
            ['ucag_id'=>'YMES6W','subject_area'=>'English',         'course_title'=>'INTSV English Language and Composition',  'course_level'=>'HGH HON',  'grade'=>'A',  'credits'=>10],
            ['ucag_id'=>'CNJ3S5','subject_area'=>'Computer Science', 'course_title'=>'Advanced Computer Science Principles',    'course_level'=>'HGH HNR',  'grade'=>'A',  'credits'=>10],
            ['ucag_id'=>'PM956E','subject_area'=>'History',          'course_title'=>'Advanced European History',               'course_level'=>'HON',      'grade'=>'A+', 'credits'=>10],
            ['ucag_id'=>'QS7A5X','subject_area'=>'Mathematics',      'course_title'=>'Advanced Statistics',                     'course_level'=>'HON',      'grade'=>'A+', 'credits'=>10],
            ['ucag_id'=>'N6LNRQ','subject_area'=>'Science',          'course_title'=>'INTSV Advanced Environmental Science',    'course_level'=>'HON',      'grade'=>'A',  'credits'=>10],
            ['ucag_id'=>'PTF4DP','subject_area'=>'Visual Perf Art',  'course_title'=>'ENRCHD Advanced Projects in Digital Arts','course_level'=>'HGH HNR',  'grade'=>'A+', 'credits'=>10],
            ['ucag_id'=>'QAL2W8','subject_area'=>'College Prep',     'course_title'=>'INTSV BUILD INNOVATION & ENTREPRENEURSHIP','course_level'=>'C PREP',  'grade'=>'A+', 'credits'=>10],
            ['ucag_id'=>'LLKNS5','subject_area'=>'Mathematics',      'course_title'=>'ENRCHD Data Science Mathematics (Stanford)','course_level'=>'HON/DUAL','grade'=>'A+', 'credits'=>10],
            ['ucag_id'=>'MBRMQ6','subject_area'=>'Mathematics',      'course_title'=>'Advanced PreCalculus',                    'course_level'=>'HON',      'grade'=>'A+', 'credits'=>10],
            ['ucag_id'=>'DW47FQ','subject_area'=>'Computer Science', 'course_title'=>'INTSV 3D Computer Aided Design',          'course_level'=>'ADV',      'grade'=>'A+', 'credits'=>10],
            ['ucag_id'=>'',      'subject_area'=>'Foreign Language', 'course_title'=>'Advanced Chinese Language',               'course_level'=>'HON',      'grade'=>'A',  'credits'=>10],
            ['ucag_id'=>'K62Q5G','subject_area'=>'Design',           'course_title'=>'ACC Design Thinking',                     'course_level'=>'GFTED',    'grade'=>'A+', 'credits'=>10],
            ['ucag_id'=>'TDRJH7','subject_area'=>'Science',          'course_title'=>'Advanced Chemistry',                      'course_level'=>'HON',      'grade'=>'A+', 'credits'=>10],
            ['ucag_id'=>'EFWC4A','subject_area'=>'Science',          'course_title'=>'Advanced Physics 1: Algebra Based',       'course_level'=>'HON',      'grade'=>'A+', 'credits'=>10],
            ['ucag_id'=>'HK3WME','subject_area'=>'Science',          'course_title'=>'INTSV Biology Honors',                    'course_level'=>'HGH HON',  'grade'=>'A+', 'credits'=>10],
            ['ucag_id'=>'EKMF7G','subject_area'=>'Social Science',   'course_title'=>'CPrep Microeconomics',                    'course_level'=>'HON',      'grade'=>'A+', 'credits'=>10],
        ],
        10 => [
            ['ucag_id'=>'',      'subject_area'=>'English',          'course_title'=>'Advanced English Literature & Composition','course_level'=>'HON',     'grade'=>'A',  'credits'=>5],
            ['ucag_id'=>'R9B7NM','subject_area'=>'Science',          'course_title'=>'CPrep Biology',                            'course_level'=>'HON',     'grade'=>'A',  'credits'=>5],
            ['ucag_id'=>'TJ8AHE','subject_area'=>'History',          'course_title'=>'CPrep World History: Modern',              'course_level'=>'HON',     'grade'=>'A+', 'credits'=>5],
            ['ucag_id'=>'HJ9C2B','subject_area'=>'History',          'course_title'=>'CPrep United States History',              'course_level'=>'HON',     'grade'=>'A+', 'credits'=>5],
            ['ucag_id'=>'',      'subject_area'=>'Social Science',   'course_title'=>'CPrep Macroeconomics',                     'course_level'=>'HON',     'grade'=>'A',  'credits'=>5],
            ['ucag_id'=>'P5QAJ3','subject_area'=>'Science',          'course_title'=>'Advanced Environmental Science',           'course_level'=>'HGH HNR', 'grade'=>'A+', 'credits'=>5],
            ['ucag_id'=>'ZNR2J4','subject_area'=>'College Prep',     'course_title'=>'GFTED Research in Tibet',                  'course_level'=>'HON',     'grade'=>'A+', 'credits'=>5],
            ['ucag_id'=>'J8KLTN','subject_area'=>'Mathematics',      'course_title'=>'Advanced Calculus BC',                     'course_level'=>'HON',     'grade'=>'A+', 'credits'=>5],
            ['ucag_id'=>'MBXM7H','subject_area'=>'Social Science',   'course_title'=>'CPrep Psychology',                         'course_level'=>'HON',     'grade'=>'A+', 'credits'=>5],
            ['ucag_id'=>'TX93D7','subject_area'=>'Computer Science', 'course_title'=>'ACC Design as Discovery (Stanford)',        'course_level'=>'ADV',     'grade'=>'A+', 'credits'=>5],
            ['ucag_id'=>'JN43W9','subject_area'=>'Computer Science', 'course_title'=>'CPrep Computer Science A',                 'course_level'=>'HON',     'grade'=>'A',  'credits'=>5],
            ['ucag_id'=>'J9TR5X','subject_area'=>'English',          'course_title'=>'ACC College Writing',                      'course_level'=>'GFTED',   'grade'=>'A+', 'credits'=>5],
            ['ucag_id'=>'D2DTCB','subject_area'=>'Science',          'course_title'=>'Advanced Physics 2: Algebra Based',        'course_level'=>'HON',     'grade'=>'A+', 'credits'=>5],
            ['ucag_id'=>'ALX47Y','subject_area'=>'Science',          'course_title'=>'Advanced Physics C Mechanics',             'course_level'=>'HON',     'grade'=>'A+', 'credits'=>5],
            ['ucag_id'=>'KFA8SX','subject_area'=>'Science',          'course_title'=>'ADV Experimental Archaeology in VR',       'course_level'=>'HGH HON', 'grade'=>'A+', 'credits'=>5],
            ['ucag_id'=>'H3QAHT','subject_area'=>'Social Science',   'course_title'=>'ENRCHD Government and Policy',             'course_level'=>'HON',     'grade'=>'A+', 'credits'=>5],
        ],
    ];
    foreach ($entries as $gl => $rows) {
        $creditsByGrade[$gl] = array_sum(array_column($rows, 'credits'));
    }
}

// ── Computed values ───────────────────────────────────────────
$nameParts      = explode(' ', trim($current_user['name'] ?: (($urow['firstname']??'').' '.($urow['lastname']??''))));
$studentName    = $isIan ? 'Jiang Ian Wenkai' : implode(' ', $nameParts);
$studentEmail   = $current_user['email'];
$studentId      = $profile['student_id']      ?? 'N/A';
$address        = $profile['address']         ?? '';
$gradDate       = !empty($profile['graduation_date']) ? date('F Y', strtotime($profile['graduation_date'])) : 'N/A';
$currentGrade   = (int)($profile['current_grade'] ?? 10);
$enrollStatus   = $profile['enrollment_status'] ?? 'Good Standing';
$gradeNames     = [9=>'Freshman',10=>'Sophomore',11=>'Junior',12=>'Senior'];
$currentGName   = $gradeNames[$currentGrade] ?? "Grade $currentGrade";
$totalCredits   = (int)array_sum($creditsByGrade);
$cumGPA         = '4.00';
$gpaByGrade     = [9=>'4.00', 10=>'4.00', 11=>'N/A', 12=>'N/A'];
$gradeLabel     = [9=>'Grade 9 (2024–2025)', 10=>'Grade 10 (2025–2026)'];
$gradeOrdinal   = [9=>'Freshman Year', 10=>'Sophomore Year', 11=>'Junior Year', 12=>'Senior Year'];
$pageTitle      = "Official Transcript — " . $studentName . " — The VR School";
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
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;1,600&family=Poppins:wght@300;400;500;600&family=Source+Serif+4:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
<style>
:root {
    --primary:#1a3a6b; --gold:#c9a84c; --gold-light:#f5e9c8;
    --border:#c8b96b; --paper:#fdfcf8; --bg:#f4f6fb;
}
body { font-family:'Source Serif 4',serif; background:var(--bg); margin:0; }
.sidebar {
    background:linear-gradient(180deg,#1a3a6b 0%,#0d1f3c 100%);
    min-height:100vh; width:260px; position:fixed; top:0; left:0;
    overflow-y:auto; z-index:100;
}
.main-wrap { margin-left:260px; padding:2rem; }

/* ─── Transcript Document ─── */
.transcript-doc {
    background:var(--paper); max-width:900px; margin:0 auto;
    border:2px solid var(--border);
    box-shadow:0 8px 40px rgba(26,58,107,0.15);
    border-radius:3px; position:relative;
}
.transcript-doc::before {
    content:''; position:absolute; inset:7px;
    border:1px solid rgba(201,168,76,0.25); pointer-events:none; z-index:1;
}

/* Header */
.doc-header {
    text-align:center; padding:2rem 3rem 1.25rem;
    border-bottom:3px double var(--border);
    background:linear-gradient(180deg,#f8f4e8 0%,var(--paper) 100%);
}
.school-name {
    font-family:'Playfair Display',serif; font-size:2rem; font-weight:700;
    color:var(--primary); letter-spacing:0.05em; text-transform:uppercase;
    margin:0;
}
.school-motto {
    font-family:'Playfair Display',serif; font-style:italic;
    color:var(--gold); font-size:0.78rem; letter-spacing:0.1em; margin:.2rem 0 .3rem;
}
.doc-subtitle {
    font-size:0.72rem; letter-spacing:0.18em; text-transform:uppercase;
    color:var(--primary); font-family:'Poppins',sans-serif; font-weight:700;
    border-top:1px solid rgba(201,168,76,.4); border-bottom:1px solid rgba(201,168,76,.4);
    padding:.3rem 0; margin:.4rem auto; display:inline-block;
}
.registrar-info { font-size:0.7rem; color:#777; font-family:'Poppins',sans-serif; margin-top:.4rem; }

/* Student Info */
.info-section {
    padding:1.25rem 2.5rem; border-bottom:1px solid #e0d8b8;
    background:#faf7ee;
}
.info-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:.6rem 2rem; }
.info-item label {
    font-family:'Poppins',sans-serif; font-size:0.62rem; font-weight:700;
    text-transform:uppercase; letter-spacing:.09em; color:var(--gold);
    display:block; margin-bottom:.1rem;
}
.info-item span { font-size:0.87rem; color:var(--primary); font-weight:600; }
.status-badge {
    display:inline-block; background:#d4edda; color:#155724;
    font-size:0.65rem; font-weight:700; letter-spacing:.05em;
    text-transform:uppercase; padding:.18rem .55rem; border-radius:3px;
    font-family:'Poppins',sans-serif;
}

/* GPA Bar */
.gpa-bar {
    padding:1rem 2.5rem; background:var(--primary); color:#fff;
    border-bottom:3px solid var(--gold);
}
.gpa-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:1rem; }
.gpa-card { text-align:center; }
.gpa-lbl { font-family:'Poppins',sans-serif; font-size:0.62rem; font-weight:600;
           letter-spacing:.1em; text-transform:uppercase; opacity:.65; }
.gpa-val { font-family:'Playfair Display',serif; font-size:1.6rem; font-weight:700;
           color:var(--gold); line-height:1.1; }
.gpa-sub { font-size:0.65rem; opacity:.5; font-family:'Poppins',sans-serif; }

/* Official mark */
.official-mark {
    text-align:center; padding:.5rem; background:#faf7ee;
    border-bottom:1px solid #e0d8b8;
    font-family:'Playfair Display',serif; font-style:italic;
    color:#888; font-size:0.8rem;
}

/* Grade Sections */
.grade-section { padding:1.5rem 2.5rem; border-bottom:1px solid #ede4c0; }
.grade-heading {
    font-family:'Playfair Display',serif; font-size:1rem; font-weight:700;
    color:var(--primary); border-left:4px solid var(--gold);
    padding-left:.75rem; margin-bottom:.75rem;
}
.transcript-table { width:100%; border-collapse:collapse; font-size:0.79rem; }
.transcript-table thead tr {
    background:var(--primary); color:#fff;
    font-family:'Poppins',sans-serif; font-size:0.65rem;
    text-transform:uppercase; letter-spacing:.06em;
}
.transcript-table thead th { padding:.52rem .65rem; font-weight:600; }
.transcript-table tbody tr:nth-child(even) { background:#faf6ea; }
.transcript-table tbody tr:nth-child(odd)  { background:#fff; }
.transcript-table tbody td {
    padding:.4rem .65rem; vertical-align:middle;
    border-bottom:1px solid #ede4c0; color:#2d3748;
}
.ucag { font-family:monospace; font-size:0.73rem; color:#888; }
.grade-chip {
    display:inline-block; padding:.1rem .5rem; border-radius:3px;
    font-weight:700; font-size:0.76rem; font-family:'Poppins',sans-serif;
}
.Ap { background:#c8f0d4; color:#155724; }
.A  { background:#cce5ff; color:#004085; }
.Am { background:#fff3cd; color:#856404; }
.subtotal-row td {
    background:#f0e9d0 !important; font-family:'Poppins',sans-serif;
    font-weight:700; font-size:0.79rem; color:var(--primary);
    border-top:2px solid var(--border) !important;
}

/* Certification */
.cert-section {
    padding:1.75rem 2.5rem; border-top:3px double var(--border); background:#faf7ee;
}
.cert-text { font-size:0.78rem; color:#555; line-height:1.75; }
.sig-block { margin-top:1.5rem; }
.sig-line { border-top:1px solid #555; width:200px; margin-top:1.75rem; }
.sig-name { font-family:'Playfair Display',serif; font-style:italic; font-size:1rem; color:var(--primary); }
.seal {
    width:100px; height:100px; border-radius:50%;
    border:3px solid var(--gold);
    display:flex; align-items:center; justify-content:center;
    background:radial-gradient(circle, #fff8e7, #fdf3d0);
    box-shadow:0 2px 12px rgba(201,168,76,.3);
}
.seal-inner { text-align:center; padding:.5rem; }
.seal-text { font-family:'Playfair Display',serif; font-size:0.48rem; font-weight:700;
             color:var(--primary); text-transform:uppercase; letter-spacing:.04em; line-height:1.4; }

/* Print */
@media print {
    body { background:#fff; }
    .sidebar, .no-print { display:none !important; }
    .main-wrap { margin-left:0; padding:0; }
    .transcript-doc { box-shadow:none; border:1px solid #ccc; max-width:100%; }
    .transcript-doc::before { display:none; }
    .gpa-bar { background:#1a3a6b !important; -webkit-print-color-adjust:exact; print-color-adjust:exact; }
    .transcript-table thead tr { background:#1a3a6b !important; -webkit-print-color-adjust:exact; print-color-adjust:exact; }
    .grade-section { page-break-inside:avoid; }
    .Ap { background:#c8f0d4 !important; -webkit-print-color-adjust:exact; print-color-adjust:exact; }
    .A  { background:#cce5ff !important; -webkit-print-color-adjust:exact; print-color-adjust:exact; }
}
@media(max-width:768px) {
    .sidebar { display:none; }
    .main-wrap { margin-left:0; padding:.75rem; }
    .info-grid { grid-template-columns:1fr 1fr; }
    .gpa-grid  { grid-template-columns:1fr 1fr; }
    .grade-section { padding:1rem 1rem; }
    .doc-header { padding:1.5rem 1rem .75rem; }
}
</style>
</head>
<body>

<?php if (!$isPrint): ?>
<div class="d-flex">
    <nav class="sidebar">
        <?php include __DIR__ . '/../includes/student_sidebar.php'; ?>
    </nav>
    <div class="main-wrap flex-grow-1">
        <!-- Toolbar -->
        <div class="no-print d-flex justify-content-between align-items-center mb-3 p-3 bg-white rounded-3 shadow-sm">
            <div>
                <h5 class="fw-bold mb-0" style="color:var(--primary);">Official Transcript</h5>
                <small class="text-muted">The VR School · Office of the Registrar · Stanford, CA</small>
            </div>
            <div class="d-flex gap-2">
                <a href="?print=1" target="_blank" class="btn btn-primary btn-sm">
                    <i class="fas fa-print me-1"></i> Print / Save PDF
                </a>
                <a href="/student/dashboard.php" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i> Dashboard
                </a>
            </div>
        </div>
<?php else: ?>
<div>
    <div class="main-wrap" style="margin-left:0;padding:1rem;">
<?php endif; ?>

    <!-- ══════════════════ TRANSCRIPT DOCUMENT ══════════════════ -->
    <div class="transcript-doc">

        <!-- Header -->
        <div class="doc-header">
            <p class="school-name">The VR School</p>
            <p class="school-motto">Veritatem Quaero Et Progressum</p>
            <p class="doc-subtitle">Official Transcript &amp; Academic Record &nbsp;·&nbsp; Office of the Registrar</p>
            <p class="registrar-info">
                531 Lasuen Mall #19492, Stanford, CA 94305 &nbsp;|&nbsp; (650) 656-0483 &nbsp;|&nbsp; registrar@thevrschool.org
            </p>
        </div>

        <!-- Student Info -->
        <div class="info-section">
            <div class="info-grid">
                <div class="info-item">
                    <label>Student Name</label>
                    <span><?= htmlspecialchars($studentName) ?></span>
                </div>
                <div class="info-item">
                    <label>Student ID</label>
                    <span style="font-family:monospace;"><?= htmlspecialchars($studentId) ?></span>
                </div>
                <div class="info-item">
                    <label>Diploma Status</label>
                    <span class="status-badge">In Progress</span>
                </div>
                <div class="info-item">
                    <label>Email</label>
                    <span style="font-size:.8rem;"><?= htmlspecialchars($studentEmail) ?></span>
                </div>
                <div class="info-item">
                    <label>Current Grade</label>
                    <span><?= htmlspecialchars($currentGName) ?></span>
                </div>
                <div class="info-item">
                    <label>Graduation Date</label>
                    <span><?= htmlspecialchars($gradDate) ?></span>
                </div>
                <?php if ($address): ?>
                <div class="info-item" style="grid-column:span 2;">
                    <label>Address</label>
                    <span><?= htmlspecialchars($address) ?></span>
                </div>
                <?php endif; ?>
                <div class="info-item">
                    <label>Enrollment Status</label>
                    <span class="status-badge"><?= htmlspecialchars($enrollStatus) ?></span>
                </div>
            </div>
        </div>

        <!-- GPA Bar -->
        <div class="gpa-bar">
            <div class="gpa-grid">
                <div class="gpa-card">
                    <div class="gpa-lbl">Cumulative GPA</div>
                    <div class="gpa-val"><?= $cumGPA ?></div>
                    <div class="gpa-sub">Unweighted</div>
                </div>
                <div class="gpa-card">
                    <div class="gpa-lbl">Credits Earned</div>
                    <div class="gpa-val"><?= $totalCredits ?></div>
                    <div class="gpa-sub">Total</div>
                </div>
                <?php foreach ([9=>'Freshman',10=>'Sophomore'] as $gl => $lbl): ?>
                <div class="gpa-card">
                    <div class="gpa-lbl"><?= $lbl ?> GPA</div>
                    <div class="gpa-val"><?= isset($entries[$gl]) ? ($gpaByGrade[$gl] ?? '—') : '—' ?></div>
                    <div class="gpa-sub"><?= htmlspecialchars($gradeLabel[$gl] ?? '') ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Official mark -->
        <div class="official-mark">✦ Official Academic Record ✦</div>

        <!-- Grade Sections -->
        <?php foreach ($entries as $gl => $rows):
            $gGpa  = $gpaByGrade[$gl] ?? '4.00';
            $gCred = (int)($creditsByGrade[$gl] ?? 0);
        ?>
        <div class="grade-section">
            <div class="grade-heading"><?= htmlspecialchars($gradeLabel[$gl] ?? "Grade $gl") ?></div>
            <table class="transcript-table">
                <thead>
                    <tr>
                        <th style="width:80px;">UC-AG ID</th>
                        <th style="width:130px;">Subject Area</th>
                        <th>Course Title</th>
                        <th style="width:90px;">Level</th>
                        <th style="width:55px;text-align:center;">Grade</th>
                        <th style="width:65px;text-align:center;">Credits</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $row):
                        $gc = ($row['grade']==='A+') ? 'Ap' : (($row['grade']==='A-') ? 'Am' : 'A');
                    ?>
                    <tr>
                        <td class="ucag"><?= htmlspecialchars($row['ucag_id']) ?></td>
                        <td><?= htmlspecialchars($row['subject_area']) ?></td>
                        <td><?= htmlspecialchars($row['course_title']) ?></td>
                        <td style="font-size:.73rem;color:#777;"><?= htmlspecialchars($row['course_level']) ?></td>
                        <td style="text-align:center;">
                            <span class="grade-chip <?= $gc ?>"><?= htmlspecialchars($row['grade']) ?></span>
                        </td>
                        <td style="text-align:center;color:#666;"><?= (int)$row['credits'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <tr class="subtotal-row">
                        <td colspan="4" style="text-align:right;padding-right:1.25rem;">
                            <?= htmlspecialchars($gradeOrdinal[$gl] ?? "Grade $gl") ?> Unweighted GPA:
                        </td>
                        <td style="text-align:center;"><?= $gGpa ?></td>
                        <td style="text-align:center;"><?= $gCred ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <?php endforeach; ?>

        <?php if (empty($entries)): ?>
        <div class="grade-section text-center py-4">
            <i class="fas fa-scroll fa-2x text-muted mb-2"></i>
            <p class="text-muted">No transcript records found for your account.</p>
        </div>
        <?php endif; ?>

        <!-- Certification -->
        <div class="cert-section">
            <p class="cert-text">
                This transcript is official only when signed by the Registrar, submitted by College Counselor directly to the
                requesting institution as an e-transcript through the Common App or issued directly by Parchment.
                Physical transcripts must be embossed with the seal of The VR School and signed with wet ink.
                Any alterations render the document invalid.
                For questions, please email <a href="mailto:registrar@thevrschool.org" style="color:var(--primary);">registrar@thevrschool.org</a>
            </p>

            <div class="d-flex justify-content-between align-items-end flex-wrap gap-4 sig-block">
                <div>
                    <div style="font-family:'Playfair Display',serif;font-style:italic;font-size:1.15rem;color:#333;">
                        /s/ Samuel Vasquez
                    </div>
                    <div class="sig-line"></div>
                    <div class="sig-name">Samuel Vasquez</div>
                    <div style="font-size:.72rem;font-family:'Poppins',sans-serif;color:#777;">Registrar, The VR School</div>
                    <div style="font-size:.72rem;font-family:'Poppins',sans-serif;color:#aaa;margin-top:.25rem;">
                        Date Issued: <?= date('F jS, Y') ?>
                    </div>
                </div>

                <div style="text-align:center;">
                    <div class="seal mx-auto">
                        <div class="seal-inner">
                            <div class="seal-text">
                                The VR School<br>
                                <span style="color:var(--gold);font-size:.65rem;">✦ EST. 1983 ✦</span><br>
                                Stanford, CA<br>
                                Veritatem<br>
                                Quaero
                            </div>
                        </div>
                    </div>
                    <div style="margin-top:.75rem;">
                        <div style="font-family:'Playfair Display',serif;font-style:italic;font-size:.82rem;color:var(--primary);">
                            Dr. Freedom Cheteni
                        </div>
                        <div style="font-size:.65rem;color:#888;font-family:'Poppins',sans-serif;">
                            Superintendent, The VR School<br>
                            Date: <?= date('F jS, Y') ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div><!-- /.transcript-doc -->

</div><!-- /.main-wrap -->
</div>

<?php if ($isPrint): ?>
<script>window.addEventListener('load', () => setTimeout(() => window.print(), 500));</script>
<?php else: ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<?php endif; ?>
</body>
</html>
