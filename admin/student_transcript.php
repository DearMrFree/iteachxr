<?php
require_once __DIR__ . '/../auth/session.php';
require_once __DIR__ . '/../lib/db_connection.php';

$current_user = auth_require();
$role = $current_user['role'] ?? 'student';
if (!in_array($role, ['admin','teacher'])) {
    header('Location: /student/dashboard.php');
    exit;
}

$db  = get_db_connection();
$uid = (int)($_GET['uid'] ?? 0);

if (!$uid || !$db) {
    header('Location: /admin/dashboard.php');
    exit;
}

$user = $db->prepare("SELECT * FROM users WHERE id=?");
$user->execute([$uid]);
$urow = $user->fetch(PDO::FETCH_ASSOC);
if (!$urow) { header('Location: /admin/dashboard.php'); exit; }

$p = $db->prepare("SELECT * FROM student_profiles WHERE user_id=?");
$p->execute([$uid]);
$profile = $p->fetch(PDO::FETCH_ASSOC) ?: [];

$r = $db->prepare("SELECT * FROM transcript_entries WHERE user_id=? ORDER BY grade_level, seq");
$r->execute([$uid]);
$allRows = $r->fetchAll(PDO::FETCH_ASSOC);

$entries = [];
$creditsByGrade = [];
foreach ($allRows as $row) {
    $gl = (int)$row['grade_level'];
    $entries[$gl][] = $row;
    $creditsByGrade[$gl] = ($creditsByGrade[$gl] ?? 0) + (float)$row['credits'];
}
ksort($entries);

$studentName  = trim(($urow['firstname']?:'').' '.($urow['lastname']??'')) ?: explode('@',$urow['email'])[0];
$studentEmail = $urow['email'];
$studentId    = $profile['student_id'] ?? 'N/A';
$gradDate     = !empty($profile['graduation_date']) ? date('F Y', strtotime($profile['graduation_date'])) : 'N/A';
$gradeLevel   = (int)($profile['current_grade'] ?? 9);
$enrollStatus = $profile['enrollment_status'] ?? 'Good Standing';
$gradeNames   = [9=>'Freshman',10=>'Sophomore',11=>'Junior',12=>'Senior'];
$currentGName = $gradeNames[$gradeLevel] ?? "Grade $gradeLevel";
$totalCredits = (int)array_sum($creditsByGrade);
$gradeLabel   = [9=>'Grade 9 (2024–2025)', 10=>'Grade 10 (2025–2026)',
                 11=>'Grade 11 (2026–2027)', 12=>'Grade 12 (2027–2028)'];
$gradeOrdinal = [9=>'Freshman Year',10=>'Sophomore Year',11=>'Junior Year',12=>'Senior Year'];
$gpaByGrade   = [9=>'4.00',10=>'4.00',11=>'N/A',12=>'N/A'];
$cumGPA       = $totalCredits > 0 ? '4.00' : '—';

$isPrint = isset($_GET['print']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Transcript — <?= htmlspecialchars($studentName) ?> — The VR School</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;1,600&family=Poppins:wght@400;500;600;700&family=Source+Serif+4:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
<style>
:root{--primary:#1a3a6b;--gold:#c9a84c;--border:#c8b96b;--paper:#fdfcf8;--bg:#f4f6fb}
body{font-family:'Source Serif 4',serif;background:var(--bg);margin:0;padding:<?= $isPrint?'0':'1.5rem' ?>}
.no-print{margin-bottom:1.25rem}
.transcript-doc{background:var(--paper);max-width:920px;margin:0 auto;border:2px solid var(--border);
    box-shadow:0 8px 40px rgba(26,58,107,.15);border-radius:3px;position:relative}
.transcript-doc::before{content:'';position:absolute;inset:8px;border:1px solid rgba(201,168,76,.25);pointer-events:none}
.doc-header{text-align:center;padding:2rem 3rem 1.25rem;border-bottom:3px double var(--border);
    background:linear-gradient(180deg,#f8f4e8,var(--paper))}
.school-name{font-family:'Playfair Display',serif;font-size:2rem;font-weight:700;color:var(--primary);
    letter-spacing:.06em;text-transform:uppercase;margin:0}
.school-motto{font-family:'Playfair Display',serif;font-style:italic;color:var(--gold);
    font-size:.78rem;letter-spacing:.12em;margin:.2rem 0 .3rem}
.doc-subtitle{font-size:.7rem;letter-spacing:.2em;text-transform:uppercase;color:var(--primary);
    font-family:'Poppins',sans-serif;font-weight:700;border-top:1px solid rgba(201,168,76,.4);
    border-bottom:1px solid rgba(201,168,76,.4);padding:.3rem 2rem;display:inline-block;margin:.4rem auto}
.registrar-info{font-size:.69rem;color:#888;font-family:'Poppins',sans-serif;margin-top:.3rem}
.info-section{padding:1.2rem 2.5rem;border-bottom:1px solid #e0d8b8;background:#faf7ee}
.info-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:.6rem 2rem}
.info-item label{font-family:'Poppins',sans-serif;font-size:.61rem;font-weight:700;
    text-transform:uppercase;letter-spacing:.09em;color:var(--gold);display:block;margin-bottom:.1rem}
.info-item span{font-size:.87rem;color:var(--primary);font-weight:600}
.status-badge{display:inline-block;background:#d4edda;color:#155724;font-size:.64rem;
    font-weight:700;letter-spacing:.05em;text-transform:uppercase;padding:.18rem .6rem;
    border-radius:3px;font-family:'Poppins',sans-serif}
.gpa-bar{padding:1rem 2.5rem;background:var(--primary);color:#fff;border-bottom:3px solid var(--gold)}
.gpa-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:.75rem}
.gpa-card{text-align:center}
.gpa-lbl{font-family:'Poppins',sans-serif;font-size:.61rem;font-weight:600;letter-spacing:.1em;
    text-transform:uppercase;opacity:.65}
.gpa-val{font-family:'Playfair Display',serif;font-size:1.6rem;font-weight:700;color:var(--gold);line-height:1.1}
.gpa-sub{font-size:.62rem;opacity:.5;font-family:'Poppins',sans-serif}
.official-mark{text-align:center;padding:.45rem;background:#faf7ee;border-bottom:1px solid #e0d8b8;
    font-family:'Playfair Display',serif;font-style:italic;color:#999;font-size:.78rem}
.grade-section{padding:1.4rem 2.5rem;border-bottom:1px solid #ede4c0}
.grade-heading{font-family:'Playfair Display',serif;font-size:1rem;font-weight:700;color:var(--primary);
    border-left:4px solid var(--gold);padding-left:.75rem;margin-bottom:.75rem}
.transcript-table{width:100%;border-collapse:collapse;font-size:.79rem}
.transcript-table thead tr{background:var(--primary);color:#fff;font-family:'Poppins',sans-serif;
    font-size:.64rem;text-transform:uppercase;letter-spacing:.06em}
.transcript-table thead th{padding:.52rem .7rem;font-weight:600}
.transcript-table tbody tr:nth-child(even){background:#faf6ea}
.transcript-table tbody td{padding:.4rem .7rem;border-bottom:1px solid #ede4c0;color:#2d3748}
.ucag{font-family:monospace;font-size:.72rem;color:#aaa}
.grade-chip{display:inline-block;padding:.1rem .52rem;border-radius:3px;font-weight:700;
    font-size:.76rem;font-family:'Poppins',sans-serif}
.Ap{background:#c8f0d4;color:#155724}.A{background:#cce5ff;color:#004085}.Am{background:#fff3cd;color:#856404}
.subtotal-row td{background:#f0e9d0!important;font-family:'Poppins',sans-serif;font-weight:700;
    font-size:.79rem;color:var(--primary);border-top:2px solid var(--border)!important}
.cert-section{padding:1.75rem 2.5rem 2rem;border-top:3px double var(--border);background:#faf7ee}
.cert-text{font-size:.78rem;color:#666;line-height:1.8}
.seal{width:100px;height:100px;border-radius:50%;border:3px solid var(--gold);display:flex;
    align-items:center;justify-content:center;
    background:radial-gradient(circle,#fff8e7,#fdf3d0);box-shadow:0 2px 12px rgba(201,168,76,.3)}
.seal-text{font-family:'Playfair Display',serif;font-size:.46rem;font-weight:700;color:var(--primary);
    text-transform:uppercase;letter-spacing:.04em;line-height:1.4;text-align:center}
@media print{
    body{background:#fff;padding:0}
    .no-print{display:none!important}
    .transcript-doc{box-shadow:none;border:1px solid #ccc}
    .gpa-bar,.transcript-table thead tr{-webkit-print-color-adjust:exact;print-color-adjust:exact}
}
</style>
</head>
<body>

<?php if (!$isPrint): ?>
<div class="no-print d-flex justify-content-between align-items-center p-3 bg-white rounded-3 shadow-sm" style="max-width:920px;margin:0 auto 1.25rem">
    <div>
        <a href="/admin/dashboard.php" class="text-decoration-none text-muted" style="font-size:.82rem">
            <i class="fas fa-arrow-left me-1"></i> Admin Dashboard
        </a>
        <span class="text-muted mx-2">·</span>
        <span class="fw-semibold" style="color:var(--primary)"><?= htmlspecialchars($studentName) ?></span>
    </div>
    <a href="?uid=<?= $uid ?>&print=1" target="_blank" class="btn btn-primary btn-sm">
        <i class="fas fa-print me-1"></i> Print / Save PDF
    </a>
</div>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<?php endif; ?>

<div class="transcript-doc">
    <div class="doc-header">
        <p class="school-name">The VR School</p>
        <p class="school-motto">Veritatem Quaero Et Progressum</p>
        <p class="doc-subtitle">Official Transcript &amp; Academic Record · Office of the Registrar</p>
        <p class="registrar-info">531 Lasuen Mall #19492, Stanford, CA 94305 · (650) 656-0483 · registrar@thevrschool.org</p>
    </div>

    <div class="info-section">
        <div class="info-grid">
            <div class="info-item"><label>Student Name</label><span><?= htmlspecialchars($studentName) ?></span></div>
            <div class="info-item"><label>Student ID</label><span style="font-family:monospace"><?= htmlspecialchars($studentId) ?></span></div>
            <div class="info-item"><label>Diploma Status</label><span class="status-badge">In Progress</span></div>
            <div class="info-item"><label>Email</label><span style="font-size:.82rem"><?= htmlspecialchars($studentEmail) ?></span></div>
            <div class="info-item"><label>Current Grade</label><span><?= htmlspecialchars($currentGName) ?></span></div>
            <div class="info-item"><label>Graduation Date</label><span><?= htmlspecialchars($gradDate) ?></span></div>
            <?php if (!empty($profile['address'])): ?>
            <div class="info-item" style="grid-column:span 2"><label>Address</label><span><?= htmlspecialchars($profile['address']) ?></span></div>
            <?php endif; ?>
            <div class="info-item"><label>Enrollment Status</label><span class="status-badge"><?= htmlspecialchars($enrollStatus) ?></span></div>
        </div>
    </div>

    <div class="gpa-bar">
        <div class="gpa-grid">
            <div class="gpa-card"><div class="gpa-lbl">Cumulative GPA</div><div class="gpa-val"><?= $cumGPA ?></div><div class="gpa-sub">Unweighted</div></div>
            <div class="gpa-card"><div class="gpa-lbl">Credits Earned</div><div class="gpa-val"><?= $totalCredits ?: '—' ?></div><div class="gpa-sub">Total</div></div>
            <div class="gpa-card"><div class="gpa-lbl">Freshman GPA</div><div class="gpa-val"><?= isset($entries[9]) ? $gpaByGrade[9] : '—' ?></div><div class="gpa-sub"><?= $gradeLabel[9] ?></div></div>
            <div class="gpa-card"><div class="gpa-lbl">Sophomore GPA</div><div class="gpa-val"><?= isset($entries[10]) ? $gpaByGrade[10] : '—' ?></div><div class="gpa-sub"><?= $gradeLabel[10] ?></div></div>
        </div>
    </div>

    <div class="official-mark">✦ &nbsp;Official Academic Record&nbsp; ✦</div>

    <?php if (empty($entries)): ?>
    <div class="grade-section text-center py-4 text-muted">
        <i class="fas fa-scroll fa-2x mb-2 d-block"></i>
        No transcript entries on record for this student yet.
    </div>
    <?php else: ?>
    <?php foreach ($entries as $gl => $rows):
        $gCred = (int)($creditsByGrade[$gl] ?? 0);
    ?>
    <div class="grade-section">
        <div class="grade-heading"><?= htmlspecialchars($gradeLabel[$gl] ?? "Grade $gl") ?></div>
        <table class="transcript-table">
            <thead><tr>
                <th style="width:82px">UC-AG ID</th>
                <th style="width:132px">Subject Area</th>
                <th>Course Title</th>
                <th style="width:92px">Level</th>
                <th style="width:56px;text-align:center">Grade</th>
                <th style="width:66px;text-align:center">Credits</th>
            </tr></thead>
            <tbody>
            <?php foreach ($rows as $row):
                $gc=($row['grade']==='A+')?'Ap':(($row['grade']==='A-')?'Am':'A');
            ?>
            <tr>
                <td class="ucag"><?= htmlspecialchars($row['ucag_id']) ?></td>
                <td><?= htmlspecialchars($row['subject_area']) ?></td>
                <td><?= htmlspecialchars($row['course_title']) ?></td>
                <td style="font-size:.72rem;color:#999"><?= htmlspecialchars($row['course_level']) ?></td>
                <td style="text-align:center"><span class="grade-chip <?= $gc ?>"><?= htmlspecialchars($row['grade']) ?></span></td>
                <td style="text-align:center;color:#777"><?= (int)$row['credits'] ?></td>
            </tr>
            <?php endforeach; ?>
            <tr class="subtotal-row">
                <td colspan="4" style="text-align:right;padding-right:1.25rem">
                    <?= htmlspecialchars($gradeOrdinal[$gl] ?? "Grade $gl") ?> · Unweighted GPA:</td>
                <td style="text-align:center"><?= $gpaByGrade[$gl] ?? '4.00' ?></td>
                <td style="text-align:center"><?= $gCred ?></td>
            </tr>
            </tbody>
        </table>
    </div>
    <?php endforeach; endif; ?>

    <div class="cert-section">
        <p class="cert-text">This transcript is official only when signed by the Registrar and submitted directly to the requesting institution as an e-transcript through Common App or issued by Parchment. Physical transcripts must be embossed with the seal of The VR School and signed with wet ink. Any alterations render the document invalid. Questions: <a href="mailto:registrar@thevrschool.org" style="color:var(--primary)">registrar@thevrschool.org</a></p>
        <div class="d-flex justify-content-between align-items-end flex-wrap gap-4" style="margin-top:1.5rem">
            <div>
                <div style="font-family:'Playfair Display',serif;font-style:italic;font-size:1.1rem;color:#444">/s/ Samuel Vasquez</div>
                <div style="border-top:1px solid #666;width:200px;margin-top:1.75rem"></div>
                <div style="font-family:'Playfair Display',serif;font-style:italic;font-size:1rem;color:var(--primary)">Samuel Vasquez</div>
                <div style="font-size:.7rem;font-family:'Poppins',sans-serif;color:#888">Registrar, The VR School</div>
                <div style="font-size:.68rem;font-family:'Poppins',sans-serif;color:#bbb;margin-top:.2rem">Date Issued: <?= date('F jS, Y') ?></div>
            </div>
            <div style="text-align:center">
                <div class="seal mx-auto">
                    <div class="seal-text">The VR School<br><span style="color:var(--gold)">✦ EST. 1983 ✦</span><br>Stanford, CA<br>Veritatem<br>Quaero</div>
                </div>
                <div style="margin-top:.75rem">
                    <div style="font-family:'Playfair Display',serif;font-style:italic;font-size:.85rem;color:var(--primary)">Dr. Freedom Cheteni</div>
                    <div style="font-size:.64rem;color:#aaa;font-family:'Poppins',sans-serif">Superintendent · <?= date('F jS, Y') ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($isPrint): ?>
<script>window.addEventListener('load',()=>setTimeout(()=>window.print(),500));</script>
<?php else: ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<?php endif; ?>
</body>
</html>
