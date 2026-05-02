<?php
require_once __DIR__ . '/../auth/session.php';
$current_user = auth_require();
require_once __DIR__ . '/../lib/db_connection.php';

$db = get_db_connection();
$isPrint = isset($_GET['print']);

// Resolve user
$stmt = $db->prepare("SELECT id, firstname, lastname FROM users WHERE email = ?");
$stmt->execute([$current_user['email']]);
$urow = $stmt->fetch(PDO::FETCH_ASSOC);
$uid  = $urow ? (int)$urow['id'] : 0;

// Student profile
$profile = [];
if ($uid) {
    $p = $db->prepare("SELECT * FROM student_profiles WHERE user_id = ?");
    $p->execute([$uid]);
    $profile = $p->fetch(PDO::FETCH_ASSOC) ?: [];
}

$studentName      = trim(($urow['firstname'] ?? '') . ' ' . ($urow['lastname'] ?? ''));
$studentEmail     = $current_user['email'];
$studentId        = $profile['student_id'] ?? 'N/A';
$address          = $profile['address'] ?? '';
$graduationDate   = !empty($profile['graduation_date']) ? date('F Y', strtotime($profile['graduation_date'])) : 'N/A';
$currentGrade     = (int)($profile['current_grade'] ?? 10);
$enrollStatus     = $profile['enrollment_status'] ?? 'Good Standing';
$gradeNames       = [9=>'Freshman',10=>'Sophomore',11=>'Junior',12=>'Senior'];
$currentGradeName = $gradeNames[$currentGrade] ?? "Grade $currentGrade";

// Transcript entries grouped by grade
$entries = [];
$creditsByGrade = [];
if ($uid) {
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

$totalCredits = array_sum($creditsByGrade);
$cumGPA = '4.00';

// Grade-level GPA map (all 4.00 per transcript)
$gpaByGrade = [9 => '4.00', 10 => '4.00'];

$gradeLabel = [9=>'Grade 9 (2024-2025)', 10=>'Grade 10 (2025-2026)'];
$gradeOrdinal = [9=>'Freshman Year', 10=>'Sophomore Year', 11=>'Junior Year', 12=>'Senior Year'];

$pageTitle = "Official Transcript — " . htmlspecialchars($studentName) . " — The VR School";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= $pageTitle ?></title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;1,600&family=Poppins:wght@300;400;500;600&family=Source+Serif+4:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
<style>
:root {
    --primary: #1a3a6b;
    --gold: #c9a84c;
    --gold-light: #f5e9c8;
    --border: #c8b96b;
    --bg: #f4f6fb;
    --paper: #fdfcf8;
}
body { font-family:'Source Serif 4',serif; background:var(--bg); }
.sidebar { background:linear-gradient(180deg,#1a3a6b 0%,#0d1f3c 100%); min-height:100vh; width:260px; position:fixed; top:0; left:0; overflow-y:auto; z-index:100; }
.main-wrap { margin-left:260px; padding:2rem; }

/* Transcript Document */
.transcript-doc {
    background: var(--paper);
    max-width: 860px;
    margin: 0 auto;
    border: 2px solid var(--border);
    box-shadow: 0 4px 32px rgba(26,58,107,0.12);
    border-radius: 4px;
    position: relative;
}
.transcript-doc::before {
    content: '';
    position: absolute;
    inset: 6px;
    border: 1px solid rgba(201,168,76,0.3);
    pointer-events: none;
    z-index: 1;
}

/* Header */
.doc-header {
    text-align: center;
    padding: 2rem 3rem 1rem;
    border-bottom: 3px double var(--border);
    background: linear-gradient(180deg, #f9f5e8 0%, var(--paper) 100%);
}
.school-name {
    font-family: 'Playfair Display', serif;
    font-size: 1.9rem;
    font-weight: 700;
    color: var(--primary);
    letter-spacing: 0.04em;
    text-transform: uppercase;
}
.doc-subtitle {
    font-size: 0.8rem;
    letter-spacing: 0.15em;
    text-transform: uppercase;
    color: var(--gold);
    font-family: 'Poppins', sans-serif;
    font-weight: 600;
    margin-top: 0.2rem;
}
.registrar-info {
    font-size: 0.72rem;
    color: #666;
    font-family: 'Poppins', sans-serif;
    margin-top: 0.5rem;
}

/* Student Info Section */
.info-section {
    padding: 1.25rem 3rem;
    border-bottom: 1px solid #ddd4a8;
    background: #faf8f2;
}
.info-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0.5rem 2rem;
}
.info-item label {
    font-family: 'Poppins', sans-serif;
    font-size: 0.65rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--gold);
    display: block;
    margin-bottom: 0.1rem;
}
.info-item span {
    font-size: 0.85rem;
    color: var(--primary);
    font-weight: 600;
}
.status-badge {
    display: inline-block;
    background: #d4edda;
    color: #155724;
    font-size: 0.65rem;
    font-weight: 700;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    padding: 0.2rem 0.5rem;
    border-radius: 3px;
    font-family: 'Poppins', sans-serif;
}

/* GPA Summary */
.gpa-bar {
    padding: 1rem 3rem;
    border-bottom: 2px solid var(--border);
    background: var(--primary);
    color: #fff;
}
.gpa-title {
    font-family: 'Playfair Display', serif;
    font-size: 0.8rem;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    opacity: 0.7;
}
.gpa-value {
    font-family: 'Playfair Display', serif;
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--gold);
}
.gpa-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; }
.gpa-card { text-align: center; }

/* Course Tables */
.grade-section { padding: 1.5rem 3rem; border-bottom: 1px solid #e8e0c8; }
.grade-heading {
    font-family: 'Playfair Display', serif;
    font-size: 1rem;
    font-weight: 700;
    color: var(--primary);
    border-left: 4px solid var(--gold);
    padding-left: 0.75rem;
    margin-bottom: 0.75rem;
}
.transcript-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.78rem;
}
.transcript-table thead tr {
    background: var(--primary);
    color: #fff;
    font-family: 'Poppins', sans-serif;
    font-size: 0.67rem;
    text-transform: uppercase;
    letter-spacing: 0.06em;
}
.transcript-table thead th { padding: 0.55rem 0.6rem; font-weight: 600; }
.transcript-table tbody tr:nth-child(even) { background: #f9f7f0; }
.transcript-table tbody tr:nth-child(odd) { background: #fff; }
.transcript-table tbody td {
    padding: 0.42rem 0.6rem;
    vertical-align: middle;
    border-bottom: 1px solid #ede8d4;
    color: #2d3748;
}
.transcript-table .ucag { font-family:monospace; font-size:0.72rem; color:#777; }
.grade-chip {
    display: inline-block;
    padding: 0.1rem 0.45rem;
    border-radius: 3px;
    font-weight: 700;
    font-size: 0.75rem;
    font-family: 'Poppins', sans-serif;
}
.grade-chip.Ap { background:#c8f0d4; color:#155724; }
.grade-chip.A  { background:#cce5ff; color:#004085; }
.grade-chip.Am { background:#fff3cd; color:#856404; }
.subtotal-row td {
    background: #f0ebda !important;
    font-family: 'Poppins', sans-serif;
    font-weight: 700;
    font-size: 0.78rem;
    color: var(--primary);
    border-top: 2px solid var(--border) !important;
}

/* Certification Section */
.cert-section {
    padding: 1.5rem 3rem;
    border-top: 3px double var(--border);
    background: #faf8f2;
}
.cert-text { font-size: 0.78rem; color: #555; line-height: 1.7; }
.sig-line { border-top: 1px solid #333; width: 220px; margin-top: 2rem; }
.sig-name { font-family: 'Playfair Display', serif; font-style: italic; font-size: 1rem; color: var(--primary); margin-top: 0.25rem; }
.seal-box { display: flex; align-items: center; gap: 1rem; }

/* Print styles */
@media print {
    body { background: white; }
    .sidebar, .no-print { display: none !important; }
    .main-wrap { margin-left: 0; padding: 0; }
    .transcript-doc { box-shadow: none; border: 1px solid #ccc; }
    .transcript-doc::before { display: none; }
    .gpa-bar { background: #1a3a6b !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .transcript-table thead tr { background: #1a3a6b !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .grade-section { page-break-inside: avoid; }
}
@media(max-width:768px){ .sidebar{display:none;} .main-wrap{margin-left:0;padding:0.5rem;} .info-grid{grid-template-columns:1fr 1fr;} .gpa-grid{grid-template-columns:1fr 1fr;} }
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
                <small class="text-muted">The VR School · Office of the Registrar</small>
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

        <!-- ═══════════════ TRANSCRIPT DOCUMENT ═══════════════ -->
        <div class="transcript-doc">

            <!-- Header -->
            <div class="doc-header">
                <div class="school-name">The VR School</div>
                <div class="doc-subtitle">Official Transcript &amp; Academic Record</div>
                <div class="registrar-info">
                    Office of the Registrar · 531 Lasuen Mall #19492, Stanford, CA 94305<br>
                    (650) 656-0483 · registrar@thevrschool.org
                </div>
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
                        <span><?= htmlspecialchars($studentId) ?></span>
                    </div>
                    <div class="info-item">
                        <label>Diploma Status</label>
                        <span class="status-badge">In Progress</span>
                    </div>
                    <div class="info-item">
                        <label>Email</label>
                        <span style="font-size:0.78rem;"><?= htmlspecialchars($studentEmail) ?></span>
                    </div>
                    <div class="info-item">
                        <label>Current Grade</label>
                        <span><?= htmlspecialchars($currentGradeName) ?></span>
                    </div>
                    <div class="info-item">
                        <label>Graduation Date</label>
                        <span><?= htmlspecialchars($graduationDate) ?></span>
                    </div>
                    <?php if ($address): ?>
                    <div class="info-item" style="grid-column:span 3;">
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

            <!-- GPA Summary Bar -->
            <div class="gpa-bar">
                <div class="gpa-grid">
                    <div class="gpa-card">
                        <div class="gpa-title">Cumulative GPA</div>
                        <div class="gpa-value"><?= $cumGPA ?></div>
                        <div style="font-size:0.7rem;opacity:.6;font-family:'Poppins',sans-serif;">Unweighted</div>
                    </div>
                    <div class="gpa-card">
                        <div class="gpa-title">Total Credits</div>
                        <div class="gpa-value"><?= (int)$totalCredits ?></div>
                        <div style="font-size:0.7rem;opacity:.6;font-family:'Poppins',sans-serif;">Earned</div>
                    </div>
                    <?php foreach ([9=>'Freshman',10=>'Sophomore'] as $gl => $lbl): ?>
                    <div class="gpa-card">
                        <div class="gpa-title"><?= $lbl ?> GPA</div>
                        <div class="gpa-value"><?= $gpaByGrade[$gl] ?? 'N/A' ?></div>
                        <div style="font-size:0.7rem;opacity:.6;font-family:'Poppins',sans-serif;"><?= $gradeLabel[$gl] ?? '' ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Academic Record marker -->
            <div style="text-align:center;padding:0.6rem;background:#faf8f2;border-bottom:1px solid #ddd4a8;">
                <span style="font-family:'Playfair Display',serif;font-style:italic;font-size:0.82rem;color:#666;">
                    ✦ Official Academic Record ✦
                </span>
            </div>

            <!-- Grade Sections -->
            <?php foreach ($entries as $gl => $rows):
                $yr    = $gradeLabel[$gl] ?? "Grade $gl";
                $gGpa  = $gpaByGrade[$gl] ?? '4.00';
                $gCred = (int)($creditsByGrade[$gl] ?? 0);
            ?>
            <div class="grade-section">
                <div class="grade-heading"><?= htmlspecialchars($yr) ?></div>
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
                            <td style="font-size:0.72rem;color:#666;"><?= htmlspecialchars($row['course_level']) ?></td>
                            <td style="text-align:center;">
                                <span class="grade-chip <?= $gc ?>"><?= htmlspecialchars($row['grade']) ?></span>
                            </td>
                            <td style="text-align:center;color:#555;"><?= (int)$row['credits'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <tr class="subtotal-row">
                            <td colspan="4" style="text-align:right;padding-right:1rem;">
                                <?= htmlspecialchars($gradeOrdinal[$gl] ?? "Grade $gl") ?> Unweighted GPA:
                            </td>
                            <td style="text-align:center;"><?= $gGpa ?></td>
                            <td style="text-align:center;"><?= $gCred ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <?php endforeach; ?>

            <!-- Certification -->
            <div class="cert-section">
                <p class="cert-text">
                    This transcript is official only when signed by the Registrar, submitted by College Counselor directly to the
                    requesting institution as an e-transcript through the Common App or issued directly by Parchment.
                    Physical transcripts must be embossed with the seal of The VR School and signed with wet ink.
                    Any alterations render the document invalid. For questions, please email
                    <a href="mailto:registrar@thevrschool.org">registrar@thevrschool.org</a>
                </p>
                <div class="d-flex justify-content-between align-items-end flex-wrap gap-4 mt-3">
                    <div>
                        <div style="font-family:'Playfair Display',serif;font-style:italic;font-size:1.1rem;color:#444;">/s/ Samuel Vasquez</div>
                        <div class="sig-line"></div>
                        <div class="sig-name">Samuel Vasquez</div>
                        <div style="font-size:0.72rem;font-family:'Poppins',sans-serif;color:#777;">Registrar, The VR School</div>
                        <div style="font-size:0.72rem;font-family:'Poppins',sans-serif;color:#999;margin-top:.25rem;">
                            Date Issued: <?= date('F jS, Y') ?>
                        </div>
                    </div>
                    <div style="text-align:center;">
                        <div style="width:90px;height:90px;border-radius:50%;border:3px solid var(--gold);display:flex;align-items:center;justify-content:center;margin:0 auto;">
                            <div style="text-align:center;padding:0.5rem;">
                                <div style="font-family:'Playfair Display',serif;font-size:0.55rem;font-weight:700;color:var(--primary);text-transform:uppercase;letter-spacing:0.05em;line-height:1.3;">
                                    The VR School<br>
                                    <span style="color:var(--gold);">✦</span><br>
                                    Stanford, CA<br>
                                    Est. 1983
                                </div>
                            </div>
                        </div>
                        <div style="font-size:0.65rem;color:#888;font-family:'Poppins',sans-serif;margin-top:0.4rem;">Official Seal</div>
                        <div style="font-family:'Playfair Display',serif;font-style:italic;font-size:0.72rem;color:var(--primary);margin-top:.25rem;">
                            Dr. Freedom Cheteni<br>
                        </div>
                        <div style="font-size:0.65rem;color:#777;font-family:'Poppins',sans-serif;">Superintendent</div>
                    </div>
                </div>
            </div>

        </div><!-- /.transcript-doc -->

    </div>
</div>

<?php if ($isPrint): ?>
<script>window.onload = function(){ window.print(); }</script>
<?php else: ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<?php endif; ?>
</body>
</html>
