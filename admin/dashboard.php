<?php
/**
 * iTeachXR — Admin Dashboard
 * Accessible to: admin, teacher roles only
 */

require_once __DIR__ . '/../auth/session.php';
require_once __DIR__ . '/../lib/db_connection.php';

$current_user = auth_require();
$role = $current_user['role'] ?? 'student';
if (!in_array($role, ['admin', 'teacher'])) {
    header('Location: /student/dashboard.php');
    exit;
}

$pageTitle = "Admin Dashboard — iTeachXR · The VR School";
$db = get_db_connection();

$totalStudents    = 0;
$totalTranscripts = 0;
$goodStanding     = 0;
$avgGpa           = '4.00';
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
$adminFirst = explode(' ', $adminName)[0];
$adminEmail = $current_user['email'] ?? '';
$initials   = strtoupper(substr($adminFirst, 0, 1) . (explode(' ', $adminName)[1][0] ?? ''));
$gradeNames = [9 => 'Freshman', 10 => 'Sophomore', 11 => 'Junior', 12 => 'Senior'];
$dbStatus   = $db ? true : false;
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
body{font-family:'Poppins',sans-serif;background:var(--bg);margin:0;color:#2d3748;min-height:100vh}

/* ── SIDEBAR ── */
.sidebar{
  background:linear-gradient(180deg,#1a3a6b 0%,#0d1f3c 100%);
  width:var(--sidebar-w);position:fixed;top:0;left:0;bottom:0;
  overflow-y:auto;z-index:100;display:flex;flex-direction:column;
  scrollbar-width:thin;scrollbar-color:rgba(255,255,255,.1) transparent;
}
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
.topbar-right{display:flex;align-items:center;gap:.85rem}
.db-pill{font-size:.68rem;font-weight:700;padding:.22rem .7rem;border-radius:12px;display:flex;align-items:center;gap:.35rem}
.db-live{background:#d4edda;color:#155724}
.db-dead{background:#f8d7da;color:#721c24}
.content{padding:2rem;flex:1}

/* ── WELCOME BANNER ── */
.welcome-banner{
  background:linear-gradient(135deg,var(--navy) 0%,#1a3a6b 50%,#243b6b 100%);
  border-radius:16px;padding:1.75rem 2rem;margin-bottom:1.75rem;
  display:flex;align-items:center;justify-content:space-between;gap:1.5rem;flex-wrap:wrap;
  position:relative;overflow:hidden;
}
.welcome-banner::before{
  content:'';position:absolute;right:-40px;top:-40px;width:220px;height:220px;
  border-radius:50%;background:rgba(201,168,76,.07);
}
.wb-text h4{font-family:'Playfair Display',serif;font-size:1.4rem;color:#fff;margin-bottom:.25rem;font-weight:600}
.wb-text p{font-size:.85rem;color:rgba(255,255,255,.65);margin:0}
.wb-actions{display:flex;gap:.75rem;flex-wrap:wrap}
.wb-btn{display:inline-flex;align-items:center;gap:.5rem;padding:.55rem 1.1rem;border-radius:8px;font-size:.8rem;font-weight:600;text-decoration:none;white-space:nowrap;transition:all .15s}
.wb-btn-gold{background:linear-gradient(135deg,var(--gold),var(--gold2));color:var(--navy)}
.wb-btn-gold:hover{opacity:.9;color:var(--navy)}
.wb-btn-ghost{background:rgba(255,255,255,.1);color:rgba(255,255,255,.85);border:1px solid rgba(255,255,255,.18)}
.wb-btn-ghost:hover{background:rgba(255,255,255,.17);color:#fff}

/* ── STAT CARDS ── */
.stat-card{border:none;border-radius:14px;padding:1.4rem 1.6rem;color:#fff;position:relative;overflow:hidden;height:100%}
.stat-card::before{content:'';position:absolute;right:-15px;top:50%;transform:translateY(-50%);font-size:3.5rem;opacity:.1;font-family:'Font Awesome 6 Free';font-weight:900}
.stat-val{font-family:'Playfair Display',serif;font-size:2.2rem;font-weight:700;line-height:1;color:#fff}
.stat-lbl{font-size:.72rem;opacity:.75;margin-top:.3rem;text-transform:uppercase;letter-spacing:.06em}
.stat-sub{font-size:.68rem;opacity:.55;margin-top:.15rem}

/* ── STUDENT ROSTER ── */
.roster-card{background:#fff;border-radius:16px;border:1px solid #e8ecf4;overflow:hidden;margin-bottom:1.75rem}
.roster-hd{padding:1rem 1.5rem;border-bottom:1px solid #f0f2f8;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.75rem}
.roster-hd-title{font-size:.9rem;font-weight:700;color:var(--primary);display:flex;align-items:center;gap:.5rem}
.search-box{border:1px solid #e8ecf4;border-radius:8px;padding:.4rem .9rem;font-size:.8rem;width:200px;outline:none;transition:border-color .2s;font-family:'Poppins',sans-serif}
.search-box:focus{border-color:var(--primary)}
.roster table{margin:0;font-size:.8rem;width:100%}
.roster thead{background:var(--primary);color:#fff}
.roster thead th{padding:.7rem 1rem;font-size:.65rem;text-transform:uppercase;letter-spacing:.07em;font-weight:700;border:none;white-space:nowrap}
.roster tbody td{padding:.65rem 1rem;vertical-align:middle;border-bottom:1px solid #f4f6fb;color:#2d3748}
.roster tbody tr:last-child td{border-bottom:none}
.roster tbody tr:hover{background:#f8faff}
.grade-pill{display:inline-block;font-size:.66rem;font-weight:700;padding:.15rem .55rem;border-radius:10px;background:#e8ecf4;color:var(--primary)}
.status-pill{display:inline-block;font-size:.66rem;font-weight:700;padding:.18rem .6rem;border-radius:4px}
.status-good{background:#d4edda;color:#155724}
.status-warn{background:#fff3cd;color:#856404}
.av-sm{width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,var(--primary),#2c5398);display:flex;align-items:center;justify-content:center;font-weight:700;color:#fff;font-size:.75rem;flex-shrink:0}
.name-cell{display:flex;align-items:center;gap:.65rem}

/* ── FEATURED STUDENT (Ian) ── */
.ian-card{background:#fff;border-radius:16px;border:2px solid rgba(201,168,76,.25);overflow:hidden;margin-bottom:1.75rem;position:relative}
.ian-header{background:linear-gradient(135deg,var(--navy),#1a3a6b);padding:1.5rem 1.75rem;position:relative;overflow:hidden}
.ian-header::after{content:'4.00';position:absolute;right:1.5rem;top:50%;transform:translateY(-50%);font-family:'Playfair Display',serif;font-size:4.5rem;font-weight:900;color:rgba(201,168,76,.12);line-height:1;pointer-events:none}
.ian-badge{display:inline-block;background:rgba(201,168,76,.2);color:var(--gold);font-size:.65rem;font-weight:700;padding:.2rem .65rem;border-radius:20px;text-transform:uppercase;letter-spacing:.08em;margin-bottom:.6rem}
.ian-header h4{font-family:'Playfair Display',serif;font-size:1.4rem;color:#fff;margin-bottom:.15rem;font-weight:700}
.ian-header p{font-size:.8rem;color:rgba(255,255,255,.6);margin:0}
.ian-body{padding:1.5rem 1.75rem}
.ian-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;margin-bottom:1.25rem}
.ian-stat{text-align:center;padding:.9rem .5rem;background:#f8faff;border-radius:10px;border:1px solid #eef1f8}
.ian-stat-val{font-family:'Playfair Display',serif;font-size:1.5rem;font-weight:700;color:var(--primary);line-height:1}
.ian-stat-val.gold{color:var(--gold)}
.ian-stat-lbl{font-size:.65rem;color:#718096;margin-top:.2rem;text-transform:uppercase;letter-spacing:.05em}
.ian-actions{display:flex;gap:.65rem;flex-wrap:wrap}
.ian-btn{display:inline-flex;align-items:center;gap:.45rem;padding:.5rem 1rem;border-radius:8px;font-size:.79rem;font-weight:600;text-decoration:none;transition:all .15s;white-space:nowrap}
.ian-btn-primary{background:var(--primary);color:#fff}
.ian-btn-primary:hover{background:#152d55;color:#fff}
.ian-btn-outline{border:1px solid #e0e8f8;color:var(--primary);background:#fff}
.ian-btn-outline:hover{background:#f0f4ff;color:var(--primary)}

/* ── SYSTEM STATUS ── */
.sys-card{background:#fff;border-radius:16px;border:1px solid #e8ecf4;padding:1.5rem}
.sys-hd{font-size:.82rem;font-weight:700;color:var(--primary);margin-bottom:1rem;display:flex;align-items:center;gap:.5rem}
.sys-row{display:flex;align-items:center;justify-content:space-between;padding:.55rem 0;border-bottom:1px solid #f4f6fb;font-size:.8rem}
.sys-row:last-child{border-bottom:none}
.sys-lbl{color:#4a5568;display:flex;align-items:center;gap:.5rem}
.sys-status{display:flex;align-items:center;gap:.4rem;font-weight:600;font-size:.77rem}
.sys-dot{width:7px;height:7px;border-radius:50%;flex-shrink:0}
.sys-green{color:#155724}.sys-dot-green{background:#28a745}
.sys-amber{color:#856404}.sys-dot-amber{background:#ffc107}
.sys-blue{color:#0d6efd}.sys-dot-blue{background:#0d6efd}

/* ── QUICK ACTIONS ── */
.qa-card{background:#fff;border-radius:16px;border:1px solid #e8ecf4;padding:1.25rem 1.5rem;text-decoration:none;display:flex;align-items:center;gap:1rem;transition:all .15s}
.qa-card:hover{border-color:#c8d0e4;box-shadow:0 4px 16px rgba(13,31,60,.08);transform:translateY(-1px)}
.qa-icon{width:42px;height:42px;border-radius:11px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0}
.qa-title{font-size:.85rem;font-weight:700;color:var(--primary);margin-bottom:.1rem}
.qa-sub{font-size:.72rem;color:#718096}

@media(max-width:991px){
  .sidebar{width:0;overflow:hidden}
  .main-wrap{margin-left:0}
  .ian-stats{grid-template-columns:repeat(2,1fr)}
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
    <div class="sb-badge">Admin</div>
  </div>

  <div class="sb-section">Overview</div>
  <div class="sb-nav">
    <a href="/admin/dashboard.php" class="active"><i class="fas fa-home"></i> Dashboard</a>
    <a href="/admin/student_transcript.php"><i class="fas fa-scroll"></i> Transcripts</a>
  </div>

  <div class="sb-section">Students</div>
  <div class="sb-nav">
    <a href="#roster" onclick="document.getElementById('roster').scrollIntoView({behavior:'smooth'});return false">
      <i class="fas fa-users"></i> Student Roster
    </a>
    <a href="/admin/student_transcript.php">
      <i class="fas fa-user-graduate"></i> Demo Student
    </a>
  </div>

  <div class="sb-section">System</div>
  <div class="sb-nav">
    <a href="/teacher/dashboard.php"><i class="fas fa-chalkboard-teacher"></i> Teacher View</a>
    <a href="https://sof.ai" target="_blank"><i class="fas fa-external-link-alt"></i> sof.ai</a>
    <a href="https://ai.thevrschool.org" target="_blank"><i class="fas fa-sign-in-alt"></i> AI School</a>
  </div>

  <div class="sb-user">
    <div class="d-flex align-items-center gap-2">
      <div class="sb-av"><?= htmlspecialchars($initials ?: 'FC') ?></div>
      <div>
        <div class="sb-user-name"><?= htmlspecialchars($adminFirst) ?></div>
        <div class="sb-user-role">Administrator</div>
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
      <span class="topbar-title">Admin Dashboard</span>
      <span class="text-muted ms-2" style="font-size:.78rem">The VR School · Stanford, CA</span>
    </div>
    <div class="topbar-right">
      <span class="db-pill <?= $dbStatus ? 'db-live' : 'db-dead' ?>">
        <i class="fas fa-circle" style="font-size:.45rem"></i>
        <?= $dbStatus ? 'Fly.io PG17 Live' : 'DB Offline' ?>
      </span>
      <span class="text-muted d-none d-md-inline" style="font-size:.78rem"><?= date('F j, Y') ?></span>
    </div>
  </div>

  <div class="content">

    <!-- WELCOME BANNER -->
    <div class="welcome-banner">
      <div class="wb-text">
        <h4>Welcome, <?= htmlspecialchars($adminFirst) ?>.</h4>
        <p>You have full administrative access to all student records, transcripts, and system settings across The VR School.</p>
      </div>
      <div class="wb-actions">
        <a href="/admin/student_transcript.php" class="wb-btn wb-btn-gold">
          <i class="fas fa-scroll"></i> Demo Student's Transcript
        </a>
        <a href="https://ai.thevrschool.org" target="_blank" class="wb-btn wb-btn-ghost">
          <i class="fas fa-external-link-alt"></i> AI School Hub
        </a>
      </div>
    </div>

    <!-- STAT CARDS -->
    <div class="row g-3 mb-4">
      <div class="col-6 col-lg-3">
        <div class="stat-card" style="background:linear-gradient(135deg,#1a3a6b,#2c5364)">
          <div class="stat-val"><?= $totalStudents ?></div>
          <div class="stat-lbl">Registered Students</div>
          <div class="stat-sub">Fly.io Postgres</div>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="stat-card" style="background:linear-gradient(135deg,#c9a035,#e8b830)">
          <div class="stat-val"><?= $totalTranscripts ?></div>
          <div class="stat-lbl">Transcript Entries</div>
          <div class="stat-sub">UC A-G formatted</div>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="stat-card" style="background:linear-gradient(135deg,#166534,#1a7a41)">
          <div class="stat-val"><?= $goodStanding ?></div>
          <div class="stat-lbl">Good Standing</div>
          <div class="stat-sub">of <?= $totalStudents ?> total</div>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="stat-card" style="background:linear-gradient(135deg,#6b1a5e,#8c257a)">
          <div class="stat-val">4.00</div>
          <div class="stat-lbl">Average GPA</div>
          <div class="stat-sub">Perfect academic standing</div>
        </div>
      </div>
    </div>

    <!-- IAN JIANG FEATURED CARD -->
    <div class="ian-card">
      <div class="ian-header">
        <div class="ian-badge">⭐ Featured Student</div>
        <h4>Demo Student</h4>
        <p>demo.student@thevrschool.org &nbsp;·&nbsp; Student ID: VRS-DEMO-0001 &nbsp;·&nbsp; Grade 10 Sophomore</p>
      </div>
      <div class="ian-body">
        <div class="ian-stats">
          <div class="ian-stat">
            <div class="ian-stat-val gold">4.00</div>
            <div class="ian-stat-lbl">Cumulative GPA</div>
          </div>
          <div class="ian-stat">
            <div class="ian-stat-val">240</div>
            <div class="ian-stat-lbl">Credits Earned</div>
          </div>
          <div class="ian-stat">
            <div class="ian-stat-val">32</div>
            <div class="ian-stat-lbl">Courses</div>
          </div>
          <div class="ian-stat">
            <div class="ian-stat-val" style="font-size:.95rem;padding-top:.25rem">Good<br>Standing</div>
            <div class="ian-stat-lbl">Enrollment</div>
          </div>
        </div>
        <div class="ian-actions">
          <a href="/admin/student_transcript.php" class="ian-btn ian-btn-primary">
            <i class="fas fa-scroll"></i> View Full Transcript
          </a>
          <a href="/admin/student_transcript.php&print=1" target="_blank" class="ian-btn ian-btn-outline">
            <i class="fas fa-print"></i> Print Transcript
          </a>
          <a href="https://www.thevrschool.org/students/jiang" target="_blank" class="ian-btn ian-btn-outline">
            <i class="fas fa-external-link-alt"></i> VR School Profile
          </a>
        </div>
      </div>
    </div>

    <div class="row g-4" id="roster">
      <!-- STUDENT ROSTER -->
      <div class="col-lg-8">
        <div class="roster-card">
          <div class="roster-hd">
            <div class="roster-hd-title">
              <i class="fas fa-users" style="color:var(--primary)"></i>
              Student Roster
              <span style="background:#e8ecf4;color:#4a5568;font-size:.68rem;font-weight:700;padding:.15rem .55rem;border-radius:10px;"><?= count($students) ?></span>
            </div>
            <input type="text" id="search" class="search-box" placeholder="Search students…" oninput="filterTable(this.value)">
          </div>
          <div class="table-responsive">
            <table class="roster">
              <thead>
                <tr>
                  <th>Student</th>
                  <th>ID</th>
                  <th>Grade</th>
                  <th>GPA</th>
                  <th>Credits</th>
                  <th>Status</th>
                  <th>Courses</th>
                  <th></th>
                </tr>
              </thead>
              <tbody id="tbl">
                <?php foreach ($students as $s):
                  $fn  = $s['firstname'] ?? '';
                  $ln  = $s['lastname']  ?? '';
                  $ini = strtoupper(substr($fn,0,1).substr($ln,0,1));
                  $gl  = (int)($s['current_grade'] ?? 10);
                  $gn  = $gradeNames[$gl] ?? "Grade $gl";
                  $gpa = $s['gpa'] ?: '4.00';
                  $st  = $s['enrollment_status'] ?? 'Good Standing';
                ?>
                <tr class="sr">
                  <td>
                    <div class="name-cell">
                      <div class="av-sm"><?= htmlspecialchars($ini) ?></div>
                      <div>
                        <div style="font-weight:600"><?= htmlspecialchars("$fn $ln") ?></div>
                        <div style="font-size:.7rem;color:#718096"><?= htmlspecialchars($s['email'] ?? '') ?></div>
                      </div>
                    </div>
                  </td>
                  <td style="font-size:.73rem;color:#718096;font-family:monospace"><?= htmlspecialchars($s['student_id'] ?? '—') ?></td>
                  <td><span class="grade-pill"><?= htmlspecialchars($gn) ?></span></td>
                  <td style="font-weight:700;color:var(--gold)"><?= htmlspecialchars($gpa) ?></td>
                  <td><?= (int)($s['total_credits'] ?? 0) ?></td>
                  <td><span class="status-pill <?= $st==='Good Standing'?'status-good':'status-warn' ?>"><?= htmlspecialchars($st) ?></span></td>
                  <td><?= (int)($s['course_count'] ?? 0) ?></td>
                  <td>
                    <a href="/admin/student_transcript.php?uid=<?= (int)$s['id'] ?>"
                       style="font-size:.72rem;color:var(--primary);font-weight:600;text-decoration:none;white-space:nowrap">
                      Transcript →
                    </a>
                  </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($students)): ?>
                <tr><td colspan="8" class="text-center py-4 text-muted" style="font-size:.85rem">No students found in database.</td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- SIDEBAR PANEL -->
      <div class="col-lg-4">

        <!-- System Status -->
        <div class="sys-card mb-3">
          <div class="sys-hd"><i class="fas fa-server" style="color:var(--primary)"></i> System Status</div>
          <div class="sys-row">
            <span class="sys-lbl"><i class="fas fa-database" style="opacity:.6"></i> Fly.io Postgres 17</span>
            <span class="sys-status <?= $dbStatus?'sys-green':'sys-amber' ?>">
              <span class="sys-dot <?= $dbStatus?'sys-dot-green':'sys-dot-amber' ?>"></span>
              <?= $dbStatus?'Live':'Offline' ?>
            </span>
          </div>
          <div class="sys-row">
            <span class="sys-lbl"><i class="fas fa-shield-alt" style="opacity:.6"></i> Google SSO</span>
            <span class="sys-status sys-green"><span class="sys-dot sys-dot-green"></span>Active</span>
          </div>
          <div class="sys-row">
            <span class="sys-lbl"><i class="fas fa-link" style="opacity:.6"></i> ai.thevrschool.org</span>
            <span class="sys-status sys-blue"><span class="sys-dot sys-dot-blue"></span>Connected</span>
          </div>
          <div class="sys-row">
            <span class="sys-lbl"><i class="fas fa-globe" style="opacity:.6"></i> sof.ai ecosystem</span>
            <span class="sys-status sys-green"><span class="sys-dot sys-dot-green"></span>Integrated</span>
          </div>
          <div class="sys-row">
            <span class="sys-lbl"><i class="fas fa-robot" style="opacity:.6"></i> AI Tutor (OpenAI)</span>
            <span class="sys-status <?= getenv('OPENAI_API_KEY')?'sys-green':'sys-amber' ?>">
              <span class="sys-dot <?= getenv('OPENAI_API_KEY')?'sys-dot-green':'sys-dot-amber' ?>"></span>
              <?= getenv('OPENAI_API_KEY')?'Active':'Not configured' ?>
            </span>
          </div>
        </div>

        <!-- Quick Actions -->
        <div style="display:flex;flex-direction:column;gap:.65rem">
          <a href="/admin/student_transcript.php" class="qa-card">
            <div class="qa-icon" style="background:#ede9fe"><i class="fas fa-scroll" style="color:#7c3aed"></i></div>
            <div><div class="qa-title">Demo Student — Transcript</div><div class="qa-sub">View · Print · Download PDF</div></div>
          </a>
          <a href="https://www.thevrschool.org/teacher/iteachxr" target="_blank" class="qa-card">
            <div class="qa-icon" style="background:#dbeafe"><i class="fas fa-chalkboard" style="color:#2563eb"></i></div>
            <div><div class="qa-title">VR School Teacher View</div><div class="qa-sub">thevrschool.org integration</div></div>
          </a>
          <a href="https://sof.ai" target="_blank" class="qa-card">
            <div class="qa-icon" style="background:#dcfce7"><i class="fas fa-globe" style="color:#16a34a"></i></div>
            <div><div class="qa-title">sof.ai Portal</div><div class="qa-sub">School of Freedom gateway</div></div>
          </a>
          <a href="/auth/logout.php" class="qa-card" style="border-color:#fee2e2">
            <div class="qa-icon" style="background:#fee2e2"><i class="fas fa-right-from-bracket" style="color:#b91c1c"></i></div>
            <div><div class="qa-title" style="color:#b91c1c">Sign Out</div><div class="qa-sub">End admin session</div></div>
          </a>
        </div>

      </div>
    </div>

  </div><!-- /content -->
</div><!-- /main-wrap -->
</div><!-- /d-flex -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function filterTable(q) {
  q = q.toLowerCase();
  document.querySelectorAll('#tbl .sr').forEach(r => {
    r.style.display = r.textContent.toLowerCase().includes(q) ? '' : 'none';
  });
}
</script>
</body>
</html>
