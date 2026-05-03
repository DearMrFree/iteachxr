<?php
/**
 * Transcript Preview — auth-bypass render of Demo Student's official transcript.
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

$studentName    = 'Demo Student';
$studentEmail   = 'demo.student@thevrschool.org';
$studentId      = 'VRS-DEMO-0001';
$address        = '123 University Ave, Palo Alto, CA 94301';
$gradDate       = 'June 2029';
$cumGPA         = '4.00';
$totalCredits   = 240;
$enrollStatus   = 'Good Standing';
$gradeName      = 'Sophomore';
$isPrint        = isset($_GET['print']);

$gpaByGrade   = [9 => '4.00', 10 => '4.00'];
$gradeLabel   = [9 => 'Grade 9 (2024–2025)', 10 => 'Grade 10 (2025–2026)'];
$gradeOrdinal = [9 => 'Freshman Year', 10 => 'Sophomore Year'];

$entries = [
    9 => [
        ['ucag_id'=>'YMES6W','subject_area'=>'English',          'course_title'=>'INTSV English Language and Composition',    'course_level'=>'HGH HON',  'grade'=>'A',  'credits'=>10],
        ['ucag_id'=>'CNJ3S5','subject_area'=>'Computer Science',  'course_title'=>'Advanced Computer Science Principles',      'course_level'=>'HGH HNR',  'grade'=>'A',  'credits'=>10],
        ['ucag_id'=>'PM956E','subject_area'=>'History',           'course_title'=>'Advanced European History',                 'course_level'=>'HON',      'grade'=>'A+', 'credits'=>10],
        ['ucag_id'=>'QS7A5X','subject_area'=>'Mathematics',       'course_title'=>'Advanced Statistics',                       'course_level'=>'HON',      'grade'=>'A+', 'credits'=>10],
        ['ucag_id'=>'N6LNRQ','subject_area'=>'Science',           'course_title'=>'INTSV Advanced Environmental Science',      'course_level'=>'HON',      'grade'=>'A',  'credits'=>10],
        ['ucag_id'=>'PTF4DP','subject_area'=>'Visual Perf Art',   'course_title'=>'ENRCHD Advanced Projects in Digital Arts',  'course_level'=>'HGH HNR',  'grade'=>'A+', 'credits'=>10],
        ['ucag_id'=>'QAL2W8','subject_area'=>'College Prep',      'course_title'=>'INTSV BUILD INNOVATION & ENTREPRENEURSHIP', 'course_level'=>'C PREP',   'grade'=>'A+', 'credits'=>10],
        ['ucag_id'=>'LLKNS5','subject_area'=>'Mathematics',       'course_title'=>'ENRCHD Data Science Mathematics (Stanford)','course_level'=>'HON/DUAL', 'grade'=>'A+', 'credits'=>10],
        ['ucag_id'=>'MBRMQ6','subject_area'=>'Mathematics',       'course_title'=>'Advanced PreCalculus',                      'course_level'=>'HON',      'grade'=>'A+', 'credits'=>10],
        ['ucag_id'=>'DW47FQ','subject_area'=>'Computer Science',  'course_title'=>'INTSV 3D Computer Aided Design',            'course_level'=>'ADV',      'grade'=>'A+', 'credits'=>10],
        ['ucag_id'=>'',      'subject_area'=>'Foreign Language',  'course_title'=>'Advanced Chinese Language',                 'course_level'=>'HON',      'grade'=>'A',  'credits'=>10],
        ['ucag_id'=>'K62Q5G','subject_area'=>'Design',            'course_title'=>'ACC Design Thinking',                       'course_level'=>'GFTED',    'grade'=>'A+', 'credits'=>10],
        ['ucag_id'=>'TDRJH7','subject_area'=>'Science',           'course_title'=>'Advanced Chemistry',                        'course_level'=>'HON',      'grade'=>'A+', 'credits'=>10],
        ['ucag_id'=>'EFWC4A','subject_area'=>'Science',           'course_title'=>'Advanced Physics 1: Algebra Based',         'course_level'=>'HON',      'grade'=>'A+', 'credits'=>10],
        ['ucag_id'=>'HK3WME','subject_area'=>'Science',           'course_title'=>'INTSV Biology Honors',                      'course_level'=>'HGH HON',  'grade'=>'A+', 'credits'=>10],
        ['ucag_id'=>'EKMF7G','subject_area'=>'Social Science',    'course_title'=>'CPrep Microeconomics',                      'course_level'=>'HON',      'grade'=>'A+', 'credits'=>10],
    ],
    10 => [
        ['ucag_id'=>'',      'subject_area'=>'English',           'course_title'=>'Advanced English Literature & Composition', 'course_level'=>'HON',      'grade'=>'A',  'credits'=>5],
        ['ucag_id'=>'R9B7NM','subject_area'=>'Science',           'course_title'=>'CPrep Biology',                             'course_level'=>'HON',      'grade'=>'A',  'credits'=>5],
        ['ucag_id'=>'TJ8AHE','subject_area'=>'History',           'course_title'=>'CPrep World History: Modern',               'course_level'=>'HON',      'grade'=>'A+', 'credits'=>5],
        ['ucag_id'=>'HJ9C2B','subject_area'=>'History',           'course_title'=>'CPrep United States History',               'course_level'=>'HON',      'grade'=>'A+', 'credits'=>5],
        ['ucag_id'=>'',      'subject_area'=>'Social Science',    'course_title'=>'CPrep Macroeconomics',                      'course_level'=>'HON',      'grade'=>'A',  'credits'=>5],
        ['ucag_id'=>'P5QAJ3','subject_area'=>'Science',           'course_title'=>'Advanced Environmental Science',            'course_level'=>'HGH HNR',  'grade'=>'A+', 'credits'=>5],
        ['ucag_id'=>'ZNR2J4','subject_area'=>'College Prep',      'course_title'=>'GFTED Research in Tibet',                   'course_level'=>'HON',      'grade'=>'A+', 'credits'=>5],
        ['ucag_id'=>'J8KLTN','subject_area'=>'Mathematics',       'course_title'=>'Advanced Calculus BC',                      'course_level'=>'HON',      'grade'=>'A+', 'credits'=>5],
        ['ucag_id'=>'MBXM7H','subject_area'=>'Social Science',    'course_title'=>'CPrep Psychology',                          'course_level'=>'HON',      'grade'=>'A+', 'credits'=>5],
        ['ucag_id'=>'TX93D7','subject_area'=>'Computer Science',  'course_title'=>'ACC Design as Discovery (Stanford)',         'course_level'=>'ADV',      'grade'=>'A+', 'credits'=>5],
        ['ucag_id'=>'JN43W9','subject_area'=>'Computer Science',  'course_title'=>'CPrep Computer Science A',                  'course_level'=>'HON',      'grade'=>'A',  'credits'=>5],
        ['ucag_id'=>'J9TR5X','subject_area'=>'English',           'course_title'=>'ACC College Writing',                       'course_level'=>'GFTED',    'grade'=>'A+', 'credits'=>5],
        ['ucag_id'=>'D2DTCB','subject_area'=>'Science',           'course_title'=>'Advanced Physics 2: Algebra Based',         'course_level'=>'HON',      'grade'=>'A+', 'credits'=>5],
        ['ucag_id'=>'ALX47Y','subject_area'=>'Science',           'course_title'=>'Advanced Physics C Mechanics',              'course_level'=>'HON',      'grade'=>'A+', 'credits'=>5],
        ['ucag_id'=>'KFA8SX','subject_area'=>'Science',           'course_title'=>'ADV Experimental Archaeology in VR',        'course_level'=>'HGH HON',  'grade'=>'A+', 'credits'=>5],
        ['ucag_id'=>'H3QAHT','subject_area'=>'Social Science',    'course_title'=>'ENRCHD Government and Policy',              'course_level'=>'HON',      'grade'=>'A+', 'credits'=>5],
    ],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Official Transcript — Demo Student — The VR School</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;1,600&family=Poppins:wght@300;400;500;600;700&family=Source+Serif+4:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
<style>
:root {
    --primary:#1a3a6b; --gold:#c9a84c; --gold-light:#f5e9c8;
    --border:#c8b96b; --paper:#fdfcf8; --bg:#f0f2f8;
}
* { box-sizing: border-box; }
body { font-family:'Source Serif 4',serif; background:var(--bg); margin:0; padding:1.5rem; }

/* ── Document ── */
.transcript-doc {
    background:var(--paper); max-width:920px; margin:0 auto;
    border:2px solid var(--border);
    box-shadow:0 12px 50px rgba(26,58,107,0.18);
    border-radius:3px; position:relative;
}
.transcript-doc::before {
    content:''; position:absolute; inset:8px;
    border:1px solid rgba(201,168,76,0.3); pointer-events:none; z-index:1;
    border-radius:1px;
}

/* ── Header ── */
.doc-header {
    text-align:center; padding:2.25rem 3rem 1.5rem;
    border-bottom:3px double var(--border);
    background:linear-gradient(180deg,#f8f4e8 0%,var(--paper) 100%);
}
.school-name {
    font-family:'Playfair Display',serif; font-size:2.1rem; font-weight:700;
    color:var(--primary); letter-spacing:0.06em; text-transform:uppercase; margin:0;
}
.school-motto {
    font-family:'Playfair Display',serif; font-style:italic;
    color:var(--gold); font-size:0.8rem; letter-spacing:0.12em; margin:.25rem 0 .4rem;
}
.doc-subtitle {
    font-size:0.72rem; letter-spacing:0.2em; text-transform:uppercase;
    color:var(--primary); font-family:'Poppins',sans-serif; font-weight:700;
    border-top:1px solid rgba(201,168,76,.4); border-bottom:1px solid rgba(201,168,76,.4);
    padding:.3rem 2rem; display:inline-block; margin:.4rem auto .2rem;
}
.registrar-info { font-size:0.7rem; color:#888; font-family:'Poppins',sans-serif; margin-top:.3rem; }

/* ── Info Section ── */
.info-section {
    padding:1.25rem 2.75rem; border-bottom:1px solid #e0d8b8; background:#faf7ee;
}
.info-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:.65rem 2.5rem; }
.info-item label {
    font-family:'Poppins',sans-serif; font-size:0.62rem; font-weight:700;
    text-transform:uppercase; letter-spacing:.1em; color:var(--gold); display:block; margin-bottom:.1rem;
}
.info-item span { font-size:0.88rem; color:var(--primary); font-weight:600; }
.status-badge {
    display:inline-block; background:#d4edda; color:#155724;
    font-size:0.64rem; font-weight:700; letter-spacing:.05em;
    text-transform:uppercase; padding:.18rem .6rem; border-radius:3px; font-family:'Poppins',sans-serif;
}

/* ── GPA Bar ── */
.gpa-bar {
    padding:1.1rem 2.75rem; background:var(--primary); color:#fff;
    border-bottom:3px solid var(--gold);
}
.gpa-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:1rem; }
.gpa-card { text-align:center; }
.gpa-lbl { font-family:'Poppins',sans-serif; font-size:0.62rem; font-weight:600;
           letter-spacing:.1em; text-transform:uppercase; opacity:.65; }
.gpa-val { font-family:'Playfair Display',serif; font-size:1.7rem; font-weight:700;
           color:var(--gold); line-height:1.1; }
.gpa-sub { font-size:0.63rem; opacity:.5; font-family:'Poppins',sans-serif; }

.official-mark {
    text-align:center; padding:.5rem; background:#faf7ee;
    border-bottom:1px solid #e0d8b8;
    font-family:'Playfair Display',serif; font-style:italic;
    color:#999; font-size:0.78rem; letter-spacing:.05em;
}

/* ── Grade Section ── */
.grade-section { padding:1.6rem 2.75rem; border-bottom:1px solid #ede4c0; }
.grade-heading {
    font-family:'Playfair Display',serif; font-size:1.05rem; font-weight:700;
    color:var(--primary); border-left:4px solid var(--gold);
    padding-left:.8rem; margin-bottom:.8rem;
}
.transcript-table { width:100%; border-collapse:collapse; font-size:0.8rem; }
.transcript-table thead tr {
    background:var(--primary); color:#fff;
    font-family:'Poppins',sans-serif; font-size:0.64rem;
    text-transform:uppercase; letter-spacing:.07em;
}
.transcript-table thead th { padding:.55rem .7rem; font-weight:600; }
.transcript-table tbody tr:nth-child(even) { background:#faf6ea; }
.transcript-table tbody tr:nth-child(odd)  { background:#fff; }
.transcript-table tbody td {
    padding:.42rem .7rem; vertical-align:middle;
    border-bottom:1px solid #ede4c0; color:#2d3748;
}
.ucag { font-family:monospace; font-size:0.73rem; color:#aaa; }
.grade-chip {
    display:inline-block; padding:.1rem .55rem; border-radius:3px;
    font-weight:700; font-size:0.77rem; font-family:'Poppins',sans-serif;
}
.Ap { background:#c8f0d4; color:#155724; }
.A  { background:#cce5ff; color:#004085; }
.Am { background:#fff3cd; color:#856404; }
.subtotal-row td {
    background:#f0e9d0 !important; font-family:'Poppins',sans-serif;
    font-weight:700; font-size:0.8rem; color:var(--primary);
    border-top:2px solid var(--border) !important;
}

/* ── Cert ── */
.cert-section {
    padding:1.75rem 2.75rem 2rem; border-top:3px double var(--border); background:#faf7ee;
}
.cert-text { font-size:0.79rem; color:#666; line-height:1.8; }
.sig-block { margin-top:1.5rem; }
.sig-name { font-family:'Playfair Display',serif; font-style:italic; font-size:1.05rem; color:var(--primary); }
.seal {
    width:108px; height:108px; border-radius:50%;
    border:3px solid var(--gold);
    display:flex; align-items:center; justify-content:center;
    background:radial-gradient(circle, #fff8e7, #fdf3d0);
    box-shadow:0 3px 18px rgba(201,168,76,.35);
}
.seal-inner { text-align:center; padding:.5rem; }
.seal-text {
    font-family:'Playfair Display',serif; font-size:0.47rem; font-weight:700;
    color:var(--primary); text-transform:uppercase; letter-spacing:.04em; line-height:1.5;
}

/* ── Print Strip ── */
.print-strip {
    background:linear-gradient(90deg,#1a3a6b,#2c5364);
    color:#fff; padding:.6rem 2.75rem;
    display:flex; justify-content:space-between; align-items:center;
    font-family:'Poppins',sans-serif; font-size:.78rem;
    border-radius:0 0 3px 3px;
}
.print-strip button {
    background:var(--gold); color:#1a3a6b; border:none; border-radius:4px;
    padding:.35rem 1rem; font-weight:700; font-size:.75rem; cursor:pointer;
    font-family:'Poppins',sans-serif; letter-spacing:.05em;
}
.print-strip button:hover { background:#f0c040; }

@media print {
    body { background:#fff; padding:0; }
    .print-strip { display:none; }
    .transcript-doc { box-shadow:none; }
    .gpa-bar, .transcript-table thead tr {
        -webkit-print-color-adjust:exact; print-color-adjust:exact;
    }
}
</style>
</head>
<body>

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
                <span style="font-size:.82rem;"><?= htmlspecialchars($studentEmail) ?></span>
            </div>
            <div class="info-item">
                <label>Current Grade</label>
                <span><?= htmlspecialchars($gradeName) ?></span>
            </div>
            <div class="info-item">
                <label>Graduation Date</label>
                <span><?= htmlspecialchars($gradDate) ?></span>
            </div>
            <div class="info-item" style="grid-column:span 2;">
                <label>Address</label>
                <span><?= htmlspecialchars($address) ?></span>
            </div>
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
            <div class="gpa-card">
                <div class="gpa-lbl">Freshman GPA</div>
                <div class="gpa-val"><?= $gpaByGrade[9] ?></div>
                <div class="gpa-sub"><?= $gradeLabel[9] ?></div>
            </div>
            <div class="gpa-card">
                <div class="gpa-lbl">Sophomore GPA</div>
                <div class="gpa-val"><?= $gpaByGrade[10] ?></div>
                <div class="gpa-sub"><?= $gradeLabel[10] ?></div>
            </div>
        </div>
    </div>

    <!-- Official Mark -->
    <div class="official-mark">✦ &nbsp; Official Academic Record &nbsp; ✦</div>

    <!-- Grade Sections -->
    <?php foreach ($entries as $gl => $rows):
        $gCred = array_sum(array_column($rows, 'credits'));
    ?>
    <div class="grade-section">
        <div class="grade-heading"><?= htmlspecialchars($gradeLabel[$gl]) ?></div>
        <table class="transcript-table">
            <thead>
                <tr>
                    <th style="width:82px;">UC-AG ID</th>
                    <th style="width:138px;">Subject Area</th>
                    <th>Course Title</th>
                    <th style="width:96px;">Level</th>
                    <th style="width:58px;text-align:center;">Grade</th>
                    <th style="width:68px;text-align:center;">Credits</th>
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
                    <td style="font-size:.73rem;color:#999;"><?= htmlspecialchars($row['course_level']) ?></td>
                    <td style="text-align:center;">
                        <span class="grade-chip <?= $gc ?>"><?= htmlspecialchars($row['grade']) ?></span>
                    </td>
                    <td style="text-align:center;color:#777;"><?= (int)$row['credits'] ?></td>
                </tr>
                <?php endforeach; ?>
                <tr class="subtotal-row">
                    <td colspan="4" style="text-align:right;padding-right:1.4rem;">
                        <?= htmlspecialchars($gradeOrdinal[$gl]) ?> &nbsp;·&nbsp; Unweighted GPA:
                    </td>
                    <td style="text-align:center;"><?= $gpaByGrade[$gl] ?></td>
                    <td style="text-align:center;"><?= (int)$gCred ?></td>
                </tr>
            </tbody>
        </table>
    </div>
    <?php endforeach; ?>

    <!-- Certification -->
    <div class="cert-section">
        <p class="cert-text">
            This transcript is official only when signed by the Registrar and submitted by the College Counselor
            directly to the requesting institution as an e-transcript through the Common App, or issued directly
            by Parchment. Physical transcripts must be embossed with the seal of The VR School and signed with
            wet ink. Any alterations render the document invalid. Questions: 
            <a href="mailto:registrar@thevrschool.org" style="color:var(--primary);">registrar@thevrschool.org</a>
        </p>

        <div class="d-flex justify-content-between align-items-end flex-wrap gap-4 sig-block">
            <div>
                <div style="font-family:'Playfair Display',serif;font-style:italic;font-size:1.2rem;color:#444;">
                    /s/ Samuel Vasquez
                </div>
                <div style="border-top:1px solid #666;width:210px;margin-top:1.75rem;"></div>
                <div class="sig-name">Samuel Vasquez</div>
                <div style="font-size:.72rem;font-family:'Poppins',sans-serif;color:#888;">Registrar, The VR School</div>
                <div style="font-size:.7rem;font-family:'Poppins',sans-serif;color:#bbb;margin-top:.2rem;">
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
                <div style="margin-top:.8rem;">
                    <div style="font-family:'Playfair Display',serif;font-style:italic;font-size:.88rem;color:var(--primary);">
                        Dr. Freedom Cheteni
                    </div>
                    <div style="font-size:.65rem;color:#aaa;font-family:'Poppins',sans-serif;">
                        Superintendent, The VR School<br>
                        Date: <?= date('F jS, Y') ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Print Strip -->
    <div class="print-strip">
        <span>Demo Student &nbsp;·&nbsp; <?= $studentId ?> &nbsp;·&nbsp; The VR School Official Transcript</span>
        <button onclick="window.print()">⬇ Print / Save PDF</button>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
