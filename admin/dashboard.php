<?php
require_once __DIR__ . '/../auth/session.php';
require_once __DIR__ . '/../lib/db_connection.php';

$current_user = auth_require();

// Role guard — admin and teacher only
$role = $current_user['role'] ?? 'student';
if (!in_array($role, ['admin','teacher'])) {
    header('Location: /student/dashboard.php');
    exit;
}

$pageTitle = "Admin Dashboard — The VR School";
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

$adminName  = $current_user['name'] ?: 'Administrator';
$gradeNames = [9=>'Freshman',10=>'Sophomore',11=>'Junior',12=>'Senior'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= htmlspecialchars($pageTitle) ?></title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
:root{--primary:#1a3a6b;--gold:#c9a84c;--bg:#f4f6fb}
*{box-sizing:border-box}
body{font-family:'Poppins',sans-serif;background:var(--bg);margin:0;color:#2d3748}
.sidebar{background:linear-gradient(180deg,#1a3a6b 0%,#0d1f3c 100%);min-height:100vh;width:240px;position:fixed;top:0;left:0;overflow-y:auto;z-index:100;display:flex;flex-direction:column}
.sb-brand{padding:1.5rem 1.25rem 1rem;border-bottom:1px solid rgba(255,255,255,.1)}
.sb-brand h1{font-family:'Playfair Display',serif;font-size:1.3rem;color:#fff;margin:0}
.sb-brand p{font-size:.62rem;color:rgba(255,255,255,.4);text-transform:uppercase;letter-spacing:.1em;margin:0}
.sb-sec{padding:.8rem 1.25rem .2rem;font-size:.6rem;font-weight:700;text-transform:uppercase;letter-spacing:.12em;color:rgba(255,255,255,.3)}
.sb-nav a{display:flex;align-items:center;gap:.65rem;padding:.55rem 1.25rem;color:rgba(255,255,255,.72);text-decoration:none;font-size:.82rem;transition:all .15s}
.sb-nav a:hover,.sb-nav a.active{background:rgba(255,255,255,.1);color:#fff}
.sb-nav a.active{border-left:3px solid var(--gold)}
.sb-nav a i{width:18px;text-align:center;font-size:.82rem}
.sb-user{padding:1rem 1.25rem;border-top:1px solid rgba(255,255,255,.1);margin-top:auto}
.av{width:36px;height:36px;border-radius:50%;background:var(--gold);display:flex;align-items:center;justify-content:center;font-weight:700;color:#1a3a6b;font-size:.9rem;flex-shrink:0}
.main-wrap{margin-left:240px}
.topbar{background:#fff;border-bottom:1px solid #e8ecf4;padding:.85rem 2rem;position:sticky;top:0;z-index:99;display:flex;align-items:center;justify-content:space-between}
.content{padding:2rem}
.stat-card{border:none;border-radius:14px;padding:1.4rem;color:#fff;position:relative;overflow:hidden}
.stat-icon{position:absolute;right:1.2rem;top:50%;transform:translateY(-50%);opacity:.13;font-size:3rem}
.stat-val{font-size:2rem;font-weight:700;line-height:1}
.stat-lbl{font-size:.74rem;opacity:.8;margin-top:.3rem}
.roster{background:#fff;border-radius:14px;border:1px solid #e8ecf4;overflow:hidden}
.roster table{margin:0;font-size:.82rem}
.roster thead{background:var(--primary);color:#fff;font-size:.68rem;text-transform:uppercase;letter-spacing:.06em}
.roster thead th{padding:.7rem 1rem;font-weight:600;border:none}
.roster tbody td{padding:.62rem 1rem;vertical-align:middle;border-bottom:1px solid #f0f2f8;color:#2d3748}
.roster tbody tr:last-child td{border-bottom:none}
.roster tbody tr:hover{background:#f8faff}
.pill-grade{display:inline-block;font-size:.68rem;font-weight:600;padding:.14rem .55rem;border-radius:12px;background:#e8ecf4;color:var(--primary)}
.pill-good{background:#d4edda;color:#155724;font-size:.67rem;font-weight:700;padding:.18rem .55rem;border-radius:4px}
.pill-watch{background:#fff3cd;color:#856404;font-size:.67rem;font-weight:700;padding:.18rem .55rem;border-radius:4px}
.sec-hd{font-size:.65rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:#8896a7;margin-bottom:.75rem}
.search-box{border-radius:8px;border:1px solid #e8ecf4;padding:.45rem 1rem;font-size:.83rem;width:260px}
.search-box:focus{outline:none;border-color:var(--primary);box-shadow:0 0 0 3px rgba(26,58,107,.1)}
.qcard{border:none;border-radius:12px;text-decoration:none}
.qcard .card-body{display:flex;align-items:center;gap:.9rem}
.qcard-icon{border-radius:10px;padding:.72rem;display:flex;align-items:center;justify-content:center}
@media(max-width:768px){.sidebar{position:relative;width:100%;min-height:auto}.main-wrap{margin-left:0}}
</style>
</head>
<body>
<div class="d-flex">

<nav class="sidebar">
    <div class="sb-brand">
        <h1>iTeachXR</h1>
        <p>Admin Portal · The VR School</p>
    </div>
    <div class="flex-grow-1 py-2">
        <div class="sb-sec">Overview</div>
        <div class="sb-nav">
            <a href="/admin/dashboard.php" class="active"><i class="fas fa-gauge-high"></i> Dashboard</a>
            <a href="/admin/student_transcript.php"><i class="fas fa-scroll"></i> Student Transcripts</a>
        </div>
        <div class="sb-sec">Portals</div>
        <div class="sb-nav">
            <a href="/teacher/dashboard.php"><i class="fas fa-chalkboard-teacher"></i> Teacher View</a>
            <a href="/auth/logout.php"><i class="fas fa-right-from-bracket"></i> Sign Out</a>
        </div>
    </div>
    <div class="sb-user">
        <div class="d-flex align-items-center gap-2">
            <?php if (!empty($current_user['image'])): ?>
            <img src="<?= htmlspecialchars($current_user['image']) ?>"
                 style="width:36px;height:36px;border-radius:50%;object-fit:cover;" alt="">
            <?php else: ?>
            <div class="av"><?= strtoupper(substr($adminName,0,1)) ?></div>
            <?php endif; ?>
            <div style="min-width:0">
                <div style="font-size:.8rem;color:#fff;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                    <?= htmlspecialchars($adminName) ?></div>
                <div style="font-size:.63rem;color:rgba(255,255,255,.4);">Administrator</div>
            </div>
        </div>
    </div>
</nav>

<div class="main-wrap flex-grow-1">
    <div class="topbar">
        <div>
            <span class="fw-semibold" style="color:var(--primary);">Admin Dashboard</span>
            <span class="text-muted ms-2" style="font-size:.82rem;">The VR School · Stanford, CA</span>
        </div>
        <div class="d-flex align-items-center gap-3">
            <?php if ($db): ?>
            <span class="badge" style="background:#d4edda;color:#155724;font-size:.68rem;">
                <i class="fas fa-circle me-1" style="font-size:.5rem;"></i>DB Live
            </span>
            <?php else: ?>
            <span class="badge" style="background:#f8d7da;color:#721c24;font-size:.68rem;">DB Offline</span>
            <?php endif; ?>
            <small class="text-muted d-none d-md-inline"><?= date('F j, Y') ?></small>
        </div>
    </div>

    <div class="content">

        <!-- Welcome -->
        <div class="mb-4 p-4 rounded-3" style="background:linear-gradient(135deg,#1a3a6b,#2c5364);color:#fff">
            <div class="d-flex align-items-center gap-3 flex-wrap">
                <div>
                    <h4 class="fw-bold mb-1">Welcome, <?= htmlspecialchars(explode(' ',$adminName)[0]) ?>.</h4>
                    <p class="mb-0" style="opacity:.8;font-size:.9rem;">
                        You have administrative access to all student records, transcripts, and system settings.
                    </p>
                </div>
            </div>
        </div>

        <!-- Stats -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-lg-3">
                <div class="stat-card" style="background:linear-gradient(135deg,#1a3a6b,#2c5364)">
                    <div class="stat-icon"><i class="fas fa-users"></i></div>
                    <div class="stat-val"><?= $totalStudents ?></div>
                    <div class="stat-lbl">Registered Students</div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-card" style="background:linear-gradient(135deg,#c9a035,#f9a602)">
                    <div class="stat-icon"><i class="fas fa-scroll"></i></div>
                    <div class="stat-val"><?= $totalTranscripts ?></div>
                    <div class="stat-lbl">Transcript Entries</div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-card" style="background:linear-gradient(135deg,#1a6b4b,#2c7a59)">
                    <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                    <div class="stat-val"><?= $goodStanding ?></div>
                    <div class="stat-lbl">Good Standing</div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-card" style="background:linear-gradient(135deg,#6b1a5e,#8c2a7a)">
                    <div class="stat-icon"><i class="fas fa-database"></i></div>
                    <div class="stat-val" style="font-size:1.1rem;padding-top:.2rem">Fly.io PG17</div>
                    <div class="stat-lbl">sofaitranscript.fly.dev</div>
                </div>
            </div>
        </div>

        <!-- Student Roster -->
        <div class="sec-hd">Student Roster</div>
        <div class="roster shadow-sm mb-4">
            <div class="p-3 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2"
                 style="background:#fafbfd">
                <div class="fw-semibold" style="font-size:.9rem;color:var(--primary)">
                    All Students
                    <span class="badge ms-1" style="background:#e8ecf4;color:#4a5568;font-size:.7rem">
                        <?= count($students) ?>
                    </span>
                </div>
                <input type="text" id="search" class="search-box"
                       placeholder="Search name, email, ID…" oninput="filterTable(this.value)">
            </div>

            <?php if (empty($students)): ?>
            <div class="text-center py-5 text-muted">
                <i class="fas fa-users fa-2x mb-3 d-block"></i>
                Students appear here automatically after their first Google sign-in.
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table mb-0" id="tbl">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Student ID</th>
                            <th>Grade</th>
                            <th>Credits</th>
                            <th>Courses</th>
                            <th>Status</th>
                            <th>Last Login</th>
                            <th></th>
                        </tr>
                    </thead>
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
                            <div class="fw-semibold" style="color:var(--primary)"><?= htmlspecialchars($full) ?></div>
                            <div class="text-muted" style="font-size:.72rem"><?= htmlspecialchars($s['email']) ?></div>
                        </td>
                        <td style="font-family:monospace;font-size:.78rem;color:#666">
                            <?= htmlspecialchars($s['student_id'] ?? '—') ?>
                        </td>
                        <td>
                            <?= $gl ? '<span class="pill-grade">'.htmlspecialchars($gn).'</span>' : '<span class="text-muted">—</span>' ?>
                        </td>
                        <td class="fw-semibold"><?= (int)($s['total_credits'] ?? 0) ?></td>
                        <td><?= (int)($s['course_count'] ?? 0) ?></td>
                        <td>
                            <?php if ($s['student_id']): ?>
                            <span class="<?= $good ? 'pill-good' : 'pill-watch' ?>">
                                <?= htmlspecialchars($s['enrollment_status'] ?? '—') ?>
                            </span>
                            <?php else: ?>
                            <span class="text-muted" style="font-size:.75rem">No profile</span>
                            <?php endif; ?>
                        </td>
                        <td style="font-size:.76rem;color:#999"><?= $ll ? htmlspecialchars($ll) : '<span class="text-muted">Never</span>' ?></td>
                        <td>
                            <a href="/admin/student_transcript.php?uid=<?= (int)$s['id'] ?>"
                               class="btn btn-sm btn-outline-primary" style="font-size:.72rem;padding:.22rem .65rem">
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

        <!-- Quick actions -->
        <div class="sec-hd">Quick Actions</div>
        <div class="row g-3">
            <div class="col-md-4">
                <a href="/admin/student_transcript.php?uid=1" class="card qcard shadow-sm">
                    <div class="card-body">
                        <div class="qcard-icon" style="background:#e8f4fd;color:#0d6efd"><i class="fas fa-scroll fa-lg"></i></div>
                        <div>
                            <div class="fw-semibold" style="color:var(--primary)">Ian Jiang's Transcript</div>
                            <div class="text-muted" style="font-size:.76rem">View · Print · Download PDF</div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="/teacher/dashboard.php" class="card qcard shadow-sm">
                    <div class="card-body">
                        <div class="qcard-icon" style="background:#f0fff4;color:#1a6b4b"><i class="fas fa-chalkboard-teacher fa-lg"></i></div>
                        <div>
                            <div class="fw-semibold" style="color:var(--primary)">Teacher Dashboard</div>
                            <div class="text-muted" style="font-size:.76rem">Courses &amp; workspaces</div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="/auth/logout.php" class="card qcard shadow-sm">
                    <div class="card-body">
                        <div class="qcard-icon" style="background:#fff5f5;color:#c53030"><i class="fas fa-right-from-bracket fa-lg"></i></div>
                        <div>
                            <div class="fw-semibold" style="color:var(--primary)">Sign Out</div>
                            <div class="text-muted" style="font-size:.76rem">End admin session</div>
                        </div>
                    </div>
                </a>
            </div>
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
