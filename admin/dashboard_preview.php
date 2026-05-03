<?php
/**
 * Admin Dashboard Preview — auth-bypass view for canvas/review.
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

$totalStudents    = 0;
$totalTranscripts = 0;
$goodStanding     = 0;
$students         = [];

if ($db) {
    $totalStudents    = (int)$db->query("SELECT COUNT(*) FROM users WHERE role='student'")->fetchColumn();
    $totalTranscripts = (int)$db->query("SELECT COUNT(*) FROM transcript_entries")->fetchColumn();
    $goodStanding     = (int)$db->query("SELECT COUNT(*) FROM student_profiles WHERE enrollment_status='Good Standing'")->fetchColumn();

    $students = $db->query("
        SELECT u.id, u.email, u.firstname, u.lastname, u.created_at, u.last_login,
               sp.student_id, sp.current_grade, sp.enrollment_status, sp.gpa, sp.total_credits,
               (SELECT COUNT(*) FROM transcript_entries te WHERE te.user_id=u.id) AS course_count
        FROM users u
        LEFT JOIN student_profiles sp ON sp.user_id=u.id
        WHERE u.role='student'
        ORDER BY u.firstname, u.lastname
    ")->fetchAll(PDO::FETCH_ASSOC);
}

$adminName  = 'Freedom Cheteni';
$gradeNames = [9=>'Freshman',10=>'Sophomore',11=>'Junior',12=>'Senior'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin Dashboard Preview — The VR School</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
:root{--primary:#1a3a6b;--gold:#c9a84c;--bg:#f4f6fb}
*{box-sizing:border-box}
body{font-family:'Poppins',sans-serif;background:var(--bg);color:#2d3748;margin:0}
.sidebar{background:linear-gradient(180deg,#1a3a6b 0%,#0d1f3c 100%);min-height:100vh;width:220px;position:fixed;top:0;left:0;overflow-y:auto;z-index:100;display:flex;flex-direction:column}
.sb-brand{padding:1.25rem 1rem .75rem;border-bottom:1px solid rgba(255,255,255,.1)}
.sb-brand h1{font-family:'Playfair Display',serif;font-size:1.2rem;color:#fff;margin:0}
.sb-brand p{font-size:.6rem;color:rgba(255,255,255,.4);text-transform:uppercase;letter-spacing:.1em;margin:0}
.sb-sec{padding:.75rem 1rem .2rem;font-size:.58rem;font-weight:700;text-transform:uppercase;letter-spacing:.12em;color:rgba(255,255,255,.3)}
.sb-nav a{display:flex;align-items:center;gap:.6rem;padding:.5rem 1rem;color:rgba(255,255,255,.72);text-decoration:none;font-size:.8rem;transition:all .15s}
.sb-nav a:hover,.sb-nav a.active{background:rgba(255,255,255,.1);color:#fff}
.sb-nav a.active{border-left:3px solid var(--gold)}
.sb-nav a i{width:16px;text-align:center;font-size:.8rem}
.sb-user{padding:.75rem 1rem;border-top:1px solid rgba(255,255,255,.1);margin-top:auto}
.av{width:32px;height:32px;border-radius:50%;background:var(--gold);display:flex;align-items:center;justify-content:center;font-weight:700;color:#1a3a6b;font-size:.8rem}
.main-wrap{margin-left:220px}
.topbar{background:#fff;border-bottom:1px solid #e8ecf4;padding:.8rem 1.5rem;position:sticky;top:0;z-index:99;display:flex;align-items:center;justify-content:space-between}
.content{padding:1.5rem}
.stat-card{border:none;border-radius:12px;padding:1.2rem;color:#fff;position:relative;overflow:hidden}
.stat-icon{position:absolute;right:1rem;top:50%;transform:translateY(-50%);opacity:.13;font-size:2.5rem}
.roster{background:#fff;border-radius:12px;border:1px solid #e8ecf4;overflow:hidden}
.roster table{margin:0;font-size:.8rem}
.roster thead{background:var(--primary);color:#fff;font-size:.66rem;text-transform:uppercase;letter-spacing:.06em}
.roster thead th{padding:.65rem 1rem;font-weight:600;border:none}
.roster tbody td{padding:.58rem 1rem;vertical-align:middle;border-bottom:1px solid #f0f2f8}
.roster tbody tr:last-child td{border-bottom:none}
.roster tbody tr:hover{background:#f8faff}
.pill-grade{display:inline-block;font-size:.67rem;font-weight:600;padding:.12rem .5rem;border-radius:12px;background:#e8ecf4;color:var(--primary)}
.pill-good{background:#d4edda;color:#155724;font-size:.65rem;font-weight:700;padding:.15rem .5rem;border-radius:4px}
.db-badge{display:inline-flex;align-items:center;gap:.3rem;background:#d4edda;color:#155724;font-size:.63rem;font-weight:700;padding:.18rem .52rem;border-radius:12px}
</style>
</head>
<body>
<div class="d-flex">
<nav class="sidebar">
    <div class="sb-brand"><h1>iTeachXR</h1><p>Admin Portal · The VR School</p></div>
    <div class="flex-grow-1 py-2">
        <div class="sb-sec">Overview</div>
        <div class="sb-nav">
            <a href="#" class="active"><i class="fas fa-gauge-high"></i> Dashboard</a>
            <a href="#"><i class="fas fa-scroll"></i> Student Transcripts</a>
        </div>
        <div class="sb-sec">Portals</div>
        <div class="sb-nav">
            <a href="#"><i class="fas fa-chalkboard-teacher"></i> Teacher View</a>
            <a href="#"><i class="fas fa-right-from-bracket"></i> Sign Out</a>
        </div>
    </div>
    <div class="sb-user">
        <div class="d-flex align-items-center gap-2">
            <div class="av">F</div>
            <div style="min-width:0">
                <div style="font-size:.78rem;color:#fff;font-weight:600">Freedom Cheteni</div>
                <div style="font-size:.62rem;color:rgba(255,255,255,.4);">Administrator</div>
            </div>
        </div>
    </div>
</nav>

<div class="main-wrap flex-grow-1">
    <div class="topbar">
        <div>
            <span class="fw-semibold" style="color:var(--primary)">Admin Dashboard</span>
            <span class="text-muted ms-2" style="font-size:.8rem">The VR School · Stanford, CA</span>
        </div>
        <div class="d-flex align-items-center gap-2">
            <?php if ($db): ?>
            <span class="db-badge"><i class="fas fa-circle" style="font-size:.48rem"></i> DB Live</span>
            <?php endif; ?>
            <small class="text-muted d-none d-md-inline"><?= date('F j, Y') ?></small>
        </div>
    </div>

    <div class="content">
        <!-- Welcome banner -->
        <div class="mb-3 p-3 rounded-3" style="background:linear-gradient(135deg,#1a3a6b,#2c5364);color:#fff">
            <h5 class="fw-bold mb-1">Welcome, Freedom.</h5>
            <p class="mb-0" style="opacity:.8;font-size:.85rem">You have administrative access to all student records, transcripts, and system settings.</p>
        </div>

        <!-- Stats -->
        <div class="row g-3 mb-3">
            <div class="col-6 col-lg-3">
                <div class="stat-card" style="background:linear-gradient(135deg,#1a3a6b,#2c5364)">
                    <div class="stat-icon"><i class="fas fa-users"></i></div>
                    <div style="font-size:1.8rem;font-weight:700;line-height:1"><?= $totalStudents ?></div>
                    <div style="font-size:.72rem;opacity:.75;margin-top:.25rem">Registered Students</div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-card" style="background:linear-gradient(135deg,#c9a035,#f9a602)">
                    <div class="stat-icon"><i class="fas fa-scroll"></i></div>
                    <div style="font-size:1.8rem;font-weight:700;line-height:1"><?= $totalTranscripts ?></div>
                    <div style="font-size:.72rem;opacity:.75;margin-top:.25rem">Transcript Entries</div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-card" style="background:linear-gradient(135deg,#1a6b4b,#2c7a59)">
                    <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                    <div style="font-size:1.8rem;font-weight:700;line-height:1"><?= $goodStanding ?></div>
                    <div style="font-size:.72rem;opacity:.75;margin-top:.25rem">Good Standing</div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-card" style="background:linear-gradient(135deg,#6b1a5e,#8c2a7a)">
                    <div class="stat-icon"><i class="fas fa-database"></i></div>
                    <div style="font-size:1rem;font-weight:700;padding-top:.2rem">Fly.io PG17</div>
                    <div style="font-size:.7rem;opacity:.7">sofaitranscript.fly.dev</div>
                </div>
            </div>
        </div>

        <!-- Student Roster -->
        <div style="font-size:.63rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:#8896a7;margin-bottom:.6rem">Student Roster</div>
        <div class="roster shadow-sm">
            <div class="p-3 border-bottom d-flex align-items-center justify-content-between gap-2" style="background:#fafbfd">
                <div class="fw-semibold" style="font-size:.88rem;color:var(--primary)">
                    All Students
                    <span class="badge ms-1" style="background:#e8ecf4;color:#4a5568;font-size:.68rem"><?= count($students) ?></span>
                </div>
                <input type="text" class="form-control form-control-sm" style="width:220px;border-radius:8px;font-size:.8rem"
                       placeholder="Search…" oninput="filterTable(this.value)">
            </div>
            <?php if (empty($students)): ?>
            <div class="text-center py-4 text-muted">
                <i class="fas fa-users fa-2x mb-2 d-block"></i>Students appear here after first Google sign-in.
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table mb-0" id="tbl">
                    <thead><tr>
                        <th>Student</th><th>ID</th><th>Grade</th><th>Credits</th><th>Courses</th><th>Status</th><th>Last Login</th><th></th>
                    </tr></thead>
                    <tbody>
                    <?php foreach ($students as $s):
                        $full = trim(($s['firstname']?:'').' '.($s['lastname']??'')) ?: explode('@',$s['email'])[0];
                        $gl   = (int)($s['current_grade'] ?? 0);
                        $gn   = $gradeNames[$gl] ?? ($gl ? "Grade $gl" : '—');
                        $good = ($s['enrollment_status'] ?? '') === 'Good Standing';
                        $ll   = $s['last_login'] ? date('M j, Y', strtotime($s['last_login'])) : '';
                    ?>
                    <tr class="sr">
                        <td>
                            <div class="fw-semibold" style="color:var(--primary);font-size:.83rem"><?= htmlspecialchars($full) ?></div>
                            <div class="text-muted" style="font-size:.7rem"><?= htmlspecialchars($s['email']) ?></div>
                        </td>
                        <td style="font-family:monospace;font-size:.76rem;color:#666"><?= htmlspecialchars($s['student_id'] ?? '—') ?></td>
                        <td><?= $gl ? '<span class="pill-grade">'.htmlspecialchars($gn).'</span>' : '<span class="text-muted">—</span>' ?></td>
                        <td class="fw-semibold"><?= (int)($s['total_credits'] ?? 0) ?></td>
                        <td><?= (int)($s['course_count'] ?? 0) ?></td>
                        <td><?= $s['student_id'] ? '<span class="pill-'.($good?'good':'watch').'">'.htmlspecialchars($s['enrollment_status']).'</span>' : '<span class="text-muted" style="font-size:.73rem">No profile</span>' ?></td>
                        <td style="font-size:.74rem;color:#999"><?= $ll ? htmlspecialchars($ll) : '<span class="text-muted">Never</span>' ?></td>
                        <td>
                            <a href="/admin/student_transcript.php?uid=<?= (int)$s['id'] ?>"
                               class="btn btn-sm btn-outline-primary" style="font-size:.7rem;padding:.2rem .6rem">
                                <i class="fas fa-scroll me-1"></i>Transcript
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function filterTable(q){
    q=q.toLowerCase();
    document.querySelectorAll('#tbl tbody .sr').forEach(r=>{
        r.style.display=r.textContent.toLowerCase().includes(q)?'':'none';
    });
}
</script>
</body>
</html>
