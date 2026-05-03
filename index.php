<?php
/**
 * iTeachXR — Landing Page
 * The AI-first LMS for The VR School (Stanford, CA)
 */

require_once __DIR__ . '/auth/session.php';
$user = auth_user();
$role = $user['role'] ?? 'student';
$dashUrl  = $user ? ($role === 'admin' || $role === 'teacher' ? '/admin/dashboard.php' : '/student/dashboard.php') : '/auth/login.php';
$loginUrl = '/auth/login.php';
$year     = date('Y');
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>iTeachXR — AI-First Learning Platform · The VR School</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;0,900;1,700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
:root{--navy:#0d1f3c;--navy2:#1a3a6b;--gold:#c9a84c;--gold2:#f0c040;--cream:#f8f5ee;--txt:#2d3748;--muted:#718096}
*{box-sizing:border-box;margin:0;padding:0}
html{scroll-behavior:smooth}
body{font-family:'Poppins',sans-serif;color:var(--txt);background:#fff;overflow-x:hidden}

/* ── NAV ─────────────────────────────────────────────────────── */
.site-nav{position:fixed;top:0;left:0;right:0;z-index:999;padding:.9rem 2rem;display:flex;align-items:center;justify-content:space-between;transition:background .3s,box-shadow .3s}
.site-nav.scrolled{background:rgba(13,31,60,.97);box-shadow:0 2px 24px rgba(0,0,0,.35)}
.nav-brand{font-family:'Playfair Display',serif;font-size:1.4rem;font-weight:700;color:#fff;text-decoration:none;letter-spacing:-.5px}
.nav-brand span{color:var(--gold)}
.nav-links{display:flex;align-items:center;gap:1.5rem}
.nav-links a{color:rgba(255,255,255,.78);font-size:.86rem;text-decoration:none;transition:color .2s}
.nav-links a:hover{color:#fff}
.nav-cta{background:var(--gold);color:var(--navy)!important;padding:.45rem 1.25rem;border-radius:6px;font-weight:700;font-size:.85rem!important;transition:background .2s,transform .15s!important;white-space:nowrap}
.nav-cta:hover{background:var(--gold2)!important;transform:translateY(-1px)!important}

/* ── HERO ────────────────────────────────────────────────────── */
.hero{min-height:100vh;background:linear-gradient(135deg,#060d1a 0%,#0d1f3c 45%,#122040 70%,#060d1a 100%);position:relative;overflow:hidden;display:flex;align-items:center;padding:120px 0 80px}
.hero::before{content:'';position:absolute;inset:0;background:radial-gradient(ellipse 70% 55% at 65% 38%,rgba(201,168,76,.09) 0%,transparent 70%)}
.hero-grid{position:absolute;inset:0;background-image:linear-gradient(rgba(255,255,255,.025) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.025) 1px,transparent 1px);background-size:56px 56px;mask-image:radial-gradient(ellipse 90% 90% at 50% 50%,black,transparent)}
.hero-badge{display:inline-flex;align-items:center;gap:.5rem;background:rgba(201,168,76,.12);border:1px solid rgba(201,168,76,.28);color:var(--gold);padding:.35rem 1rem;border-radius:30px;font-size:.72rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;margin-bottom:1.5rem}
.hero h1{font-family:'Playfair Display',serif;font-size:clamp(2.6rem,6.5vw,4.8rem);font-weight:900;color:#fff;line-height:1.08;letter-spacing:-.025em;margin-bottom:1.3rem}
.hero h1 .accent{background:linear-gradient(135deg,var(--gold),var(--gold2));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.hero-sub{font-size:1.1rem;color:rgba(255,255,255,.68);line-height:1.75;max-width:530px;margin-bottom:2.5rem}
.hero-btns{display:flex;gap:1rem;flex-wrap:wrap;align-items:center;margin-bottom:2.75rem}
.btn-gold{display:inline-flex;align-items:center;gap:.6rem;background:linear-gradient(135deg,var(--gold),var(--gold2));color:var(--navy);padding:.85rem 2rem;border-radius:8px;font-weight:700;font-size:.95rem;text-decoration:none;box-shadow:0 8px 28px rgba(201,168,76,.32);transition:transform .2s,box-shadow .2s}
.btn-gold:hover{transform:translateY(-2px);box-shadow:0 14px 40px rgba(201,168,76,.44);color:var(--navy)}
.btn-ghost{display:inline-flex;align-items:center;gap:.6rem;border:1px solid rgba(255,255,255,.22);color:rgba(255,255,255,.82);padding:.85rem 1.75rem;border-radius:8px;font-size:.95rem;text-decoration:none;transition:border-color .2s,color .2s,background .2s}
.btn-ghost:hover{border-color:rgba(255,255,255,.55);color:#fff;background:rgba(255,255,255,.06)}
.trust-row{display:flex;gap:1.75rem;flex-wrap:wrap}
.trust-item{font-size:.77rem;color:rgba(255,255,255,.45);display:flex;align-items:center;gap:.4rem}
.trust-dot{width:5px;height:5px;background:var(--gold);border-radius:50%;flex-shrink:0}

/* Hero card */
.hero-card{background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.09);backdrop-filter:blur(20px);border-radius:20px;padding:1.75rem;box-shadow:0 32px 80px rgba(0,0,0,.45)}
.hc-bar{display:flex;align-items:center;gap:.55rem;margin-bottom:1.5rem;padding-bottom:1rem;border-bottom:1px solid rgba(255,255,255,.07)}
.hc-dot{width:10px;height:10px;border-radius:50%}
.hc-label{font-size:.73rem;color:rgba(255,255,255,.38);margin-left:.25rem}
.chip-grid{display:grid;grid-template-columns:1fr 1fr;gap:.6rem;margin-bottom:1.25rem}
.chip{background:rgba(255,255,255,.055);border:1px solid rgba(255,255,255,.08);border-radius:11px;padding:.9rem 1rem;text-align:center}
.chip-val{font-family:'Playfair Display',serif;font-size:1.7rem;font-weight:700;color:#fff;line-height:1}
.chip-val.gold{color:var(--gold)}
.chip-lbl{font-size:.65rem;color:rgba(255,255,255,.42);margin-top:.2rem;text-transform:uppercase;letter-spacing:.07em}
.mini-row{background:rgba(255,255,255,.045);border-radius:10px;padding:.7rem .9rem;display:flex;align-items:center;gap:.7rem;margin-bottom:.45rem}
.mi-icon{width:30px;height:30px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:.78rem;flex-shrink:0}
.mi-name{font-size:.79rem;color:rgba(255,255,255,.82);font-weight:500}
.mi-sub{font-size:.68rem;color:rgba(255,255,255,.38);margin-top:.1rem}
.mi-grade{font-size:.8rem;color:var(--gold);font-weight:700;margin-left:auto;flex-shrink:0}
.mi-bar{height:2px;border-radius:2px;background:rgba(255,255,255,.08);margin-top:.35rem}
.mi-fill{height:2px;border-radius:2px;background:linear-gradient(90deg,var(--gold),var(--gold2))}

/* ── STATS BAR ───────────────────────────────────────────────── */
.stats-bar{background:var(--navy);padding:2.5rem 0;border-top:1px solid rgba(255,255,255,.06);border-bottom:1px solid rgba(255,255,255,.06)}
.sbar-item{text-align:center}
.sbar-num{font-family:'Playfair Display',serif;font-size:2.3rem;font-weight:700;color:var(--gold);line-height:1}
.sbar-lbl{font-size:.73rem;color:rgba(255,255,255,.48);margin-top:.3rem;text-transform:uppercase;letter-spacing:.08em}

/* ── FEATURES ────────────────────────────────────────────────── */
.features{padding:100px 0;background:#fff}
.eyebrow{font-size:.72rem;font-weight:700;letter-spacing:.15em;text-transform:uppercase;color:var(--gold);margin-bottom:.75rem}
.sec-title{font-family:'Playfair Display',serif;font-size:clamp(1.9rem,4vw,2.75rem);font-weight:700;color:var(--navy);line-height:1.22;margin-bottom:1rem}
.sec-sub{font-size:.97rem;color:var(--muted);line-height:1.72;max-width:510px}
.feat-card{border:1px solid #e8ecf4;border-radius:16px;padding:1.75rem;height:100%;transition:transform .2s,box-shadow .2s,border-color .2s;background:#fff}
.feat-card:hover{transform:translateY(-4px);box-shadow:0 18px 50px rgba(13,31,60,.11);border-color:#c8d0e4}
.feat-icon{width:50px;height:50px;border-radius:13px;display:flex;align-items:center;justify-content:center;font-size:1.25rem;margin-bottom:1.2rem}
.feat-card h4{font-size:1.02rem;font-weight:700;color:var(--navy);margin-bottom:.45rem}
.feat-card p{font-size:.84rem;color:var(--muted);line-height:1.68;margin:0}

/* ── PORTALS ─────────────────────────────────────────────────── */
.portals{padding:100px 0;background:var(--cream)}
.portal-card{background:#fff;border-radius:20px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,.07);transition:transform .2s,box-shadow .2s;text-decoration:none;display:block;border:1px solid rgba(0,0,0,.04)}
.portal-card:hover{transform:translateY(-6px);box-shadow:0 22px 60px rgba(0,0,0,.14)}
.ph{padding:2rem 2rem 1.4rem}
.ph-icon{font-size:2.2rem;margin-bottom:.9rem}
.portal-card h3{font-family:'Playfair Display',serif;font-size:1.45rem;font-weight:700;color:var(--navy);margin-bottom:.35rem}
.portal-card p{font-size:.85rem;color:var(--muted);line-height:1.65;margin:0}
.pf{padding:1.1rem 2rem;display:flex;align-items:center;justify-content:space-between;border-top:1px solid #f0f2f8;font-size:.81rem;font-weight:600;color:var(--navy2)}
.pill{font-size:.65rem;font-weight:700;padding:.2rem .65rem;border-radius:20px;text-transform:uppercase;letter-spacing:.07em}

/* ── ECOSYSTEM ───────────────────────────────────────────────── */
.ecosystem{padding:100px 0;background:linear-gradient(135deg,var(--navy) 0%,#091526 100%)}
.eco-card{background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.09);border-radius:16px;padding:1.75rem;text-decoration:none;display:block;transition:background .2s,border-color .2s,transform .2s;height:100%}
.eco-card:hover{background:rgba(255,255,255,.09);border-color:rgba(201,168,76,.35);transform:translateY(-3px)}
.eco-icon{font-size:1.8rem;margin-bottom:.9rem}
.eco-card h4{font-size:1rem;font-weight:700;color:#fff;margin-bottom:.4rem}
.eco-card p{font-size:.82rem;color:rgba(255,255,255,.52);line-height:1.64;margin:0}
.eco-url{font-size:.73rem;color:var(--gold);margin-top:.75rem;font-weight:600}

/* ── FOOTER ──────────────────────────────────────────────────── */
.footer{background:#060d1a;padding:3rem 2rem 2rem;color:rgba(255,255,255,.38);font-size:.81rem;border-top:1px solid rgba(255,255,255,.05)}
.footer a{color:rgba(255,255,255,.38);text-decoration:none;transition:color .2s}
.footer a:hover{color:var(--gold)}

@media(max-width:767px){
  .hero h1{font-size:2.4rem}
  .hero-card{display:none}
  .hide-sm{display:none!important}
}
</style>
</head>
<body>

<!-- NAV -->
<nav class="site-nav" id="sitenav">
  <a href="/" class="nav-brand">iTeach<span>XR</span></a>
  <div class="nav-links">
    <a href="#features" class="hide-sm">Features</a>
    <a href="#portals" class="hide-sm">Portals</a>
    <a href="https://sof.ai" target="_blank" class="hide-sm">sof.ai</a>
    <a href="<?= $user ? $dashUrl : $loginUrl ?>" class="nav-cta">
      <?= $user ? 'My Dashboard →' : 'Sign In →' ?>
    </a>
  </div>
</nav>

<!-- HERO -->
<section class="hero">
  <div class="hero-grid"></div>
  <div class="container position-relative">
    <div class="row align-items-center g-5">

      <!-- Left copy -->
      <div class="col-lg-6">
        <div class="hero-badge">
          <i class="fas fa-circle" style="font-size:.45rem"></i>
          WASC Accredited · Stanford, CA · UC A-G Approved
        </div>
        <h1>The AI-first LMS for <span class="accent">The VR School</span></h1>
        <p class="hero-sub">
          One platform for student transcripts, UC A-G coursework, real-time
          academic records, and an AI tutor — unified by a single Google sign-in
          at <strong style="color:rgba(255,255,255,.9)">ai.thevrschool.org</strong>.
        </p>
        <div class="hero-btns">
          <?php if ($user): ?>
          <a href="<?= $dashUrl ?>" class="btn-gold">
            <i class="fas fa-tachometer-alt"></i> Go to My Dashboard
          </a>
          <?php else: ?>
          <a href="<?= $loginUrl ?>" class="btn-gold">
            <i class="fas fa-sign-in-alt"></i> Sign In with VR School
          </a>
          <?php endif; ?>
          <a href="https://ai.thevrschool.org" target="_blank" class="btn-ghost">
            <i class="fas fa-external-link-alt" style="font-size:.8rem"></i> AI School Portal
          </a>
        </div>
        <div class="trust-row">
          <span class="trust-item"><span class="trust-dot"></span>Google SSO via ai.thevrschool.org</span>
          <span class="trust-item"><span class="trust-dot"></span>Fly.io PostgreSQL 17</span>
          <span class="trust-item"><span class="trust-dot"></span>WASC Accredited</span>
        </div>
      </div>

      <!-- Right card -->
      <div class="col-lg-6">
        <div class="hero-card">
          <div class="hc-bar">
            <div class="hc-dot" style="background:#ff5f57"></div>
            <div class="hc-dot" style="background:#febc2e"></div>
            <div class="hc-dot" style="background:#28c840"></div>
            <span class="hc-label">Ian Jiang · Student Dashboard · iTeachXR</span>
          </div>
          <div class="chip-grid">
            <div class="chip"><div class="chip-val gold">4.00</div><div class="chip-lbl">Cumulative GPA</div></div>
            <div class="chip"><div class="chip-val">240</div><div class="chip-lbl">Credits Earned</div></div>
            <div class="chip"><div class="chip-val">32</div><div class="chip-lbl">Courses</div></div>
            <div class="chip"><div class="chip-val" style="font-size:.95rem;padding:.35rem 0">Good Standing</div><div class="chip-lbl">Status</div></div>
          </div>
          <div class="mini-row">
            <div class="mi-icon" style="background:rgba(79,70,229,.18)"><i class="fas fa-flask" style="color:#818cf8"></i></div>
            <div class="flex-grow-1">
              <div class="mi-name">AP Physics C: Mechanics</div>
              <div class="mi-bar"><div class="mi-fill" style="width:100%"></div></div>
            </div>
            <div class="mi-grade">A+</div>
          </div>
          <div class="mini-row">
            <div class="mi-icon" style="background:rgba(16,185,129,.18)"><i class="fas fa-calculator" style="color:#34d399"></i></div>
            <div class="flex-grow-1">
              <div class="mi-name">AP Calculus BC</div>
              <div class="mi-bar"><div class="mi-fill" style="width:100%"></div></div>
            </div>
            <div class="mi-grade">A+</div>
          </div>
          <div class="mini-row">
            <div class="mi-icon" style="background:rgba(245,158,11,.18)"><i class="fas fa-brain" style="color:#fbbf24"></i></div>
            <div class="flex-grow-1">
              <div class="mi-name">AP Seminar · Research</div>
              <div class="mi-bar"><div class="mi-fill" style="width:100%"></div></div>
            </div>
            <div class="mi-grade">A+</div>
          </div>
          <div class="mini-row">
            <div class="mi-icon" style="background:rgba(239,68,68,.18)"><i class="fas fa-dna" style="color:#f87171"></i></div>
            <div class="flex-grow-1">
              <div class="mi-name">AP Biology</div>
              <div class="mi-bar"><div class="mi-fill" style="width:100%"></div></div>
            </div>
            <div class="mi-grade">A+</div>
          </div>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- STATS BAR -->
<section class="stats-bar">
  <div class="container">
    <div class="row g-4">
      <div class="col-6 col-md-3 sbar-item"><div class="sbar-num">402+</div><div class="sbar-lbl">Pioneers Enrolled</div></div>
      <div class="col-6 col-md-3 sbar-item"><div class="sbar-num">32</div><div class="sbar-lbl">UC A-G Courses</div></div>
      <div class="col-6 col-md-3 sbar-item"><div class="sbar-num">4.00</div><div class="sbar-lbl">Ian Jiang GPA</div></div>
      <div class="col-6 col-md-3 sbar-item"><div class="sbar-num">Live</div><div class="sbar-lbl">Fly.io Database</div></div>
    </div>
  </div>
</section>

<!-- FEATURES -->
<section class="features" id="features">
  <div class="container">
    <div class="row mb-5 align-items-end">
      <div class="col-lg-6">
        <div class="eyebrow">Platform capabilities</div>
        <h2 class="sec-title">Built for serious academic outcomes</h2>
        <p class="sec-sub">iTeachXR combines AI tutoring, live Postgres transcript records, Google SSO, and UC A-G course management in one seamless platform built for The VR School.</p>
      </div>
    </div>
    <div class="row g-4">
      <div class="col-md-6 col-lg-4">
        <div class="feat-card">
          <div class="feat-icon" style="background:#ede9fe"><i class="fas fa-robot" style="color:#7c3aed"></i></div>
          <h4>AI Tutor</h4>
          <p>Personalized AI assistance powered by OpenAI GPT-4. Ask questions, get concept explanations, and receive instant feedback on any course material — 24/7.</p>
        </div>
      </div>
      <div class="col-md-6 col-lg-4">
        <div class="feat-card">
          <div class="feat-icon" style="background:#dbeafe"><i class="fas fa-scroll" style="color:#1d4ed8"></i></div>
          <h4>Live Transcripts</h4>
          <p>Official UC A-G formatted transcripts stored in Fly.io PostgreSQL 17. Complete grade history, credit totals, and GPA — updated in real time.</p>
        </div>
      </div>
      <div class="col-md-6 col-lg-4">
        <div class="feat-card">
          <div class="feat-icon" style="background:#dcfce7"><i class="fas fa-shield-alt" style="color:#166534"></i></div>
          <h4>Unified Google SSO</h4>
          <p>Sign in once at ai.thevrschool.org and move seamlessly between iTeachXR, sof.ai, and thevrschool.org. No separate passwords, no silos, zero friction.</p>
        </div>
      </div>
      <div class="col-md-6 col-lg-4">
        <div class="feat-card">
          <div class="feat-icon" style="background:#fef3c7"><i class="fas fa-graduation-cap" style="color:#92400e"></i></div>
          <h4>UC A-G Curriculum</h4>
          <p>Full UC A-G catalog — History, English, Math, Science, Language, Visual Arts, Electives. Every course tracked and formatted for college eligibility verification.</p>
        </div>
      </div>
      <div class="col-md-6 col-lg-4">
        <div class="feat-card">
          <div class="feat-icon" style="background:#fee2e2"><i class="fas fa-chart-line" style="color:#b91c1c"></i></div>
          <h4>Progress Analytics</h4>
          <p>Real-time academic dashboards for students and administrators. GPA trends, credit accumulation, enrollment standing — all at a glance.</p>
        </div>
      </div>
      <div class="col-md-6 col-lg-4">
        <div class="feat-card">
          <div class="feat-icon" style="background:#e0f2fe"><i class="fas fa-users-cog" style="color:#0369a1"></i></div>
          <h4>Admin Command Center</h4>
          <p>Full admin dashboard for Dr. Freedom Cheteni — student roster, transcript viewer, enrollment management, and ecosystem integration status.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- PORTALS -->
<section class="portals" id="portals">
  <div class="container">
    <div class="text-center mb-5">
      <div class="eyebrow">Role-based access</div>
      <h2 class="sec-title" style="max-width:none">Your portal, tailored to your role</h2>
    </div>
    <div class="row g-4">
      <div class="col-md-4">
        <a href="/student/dashboard.php" class="portal-card">
          <div class="ph" style="background:linear-gradient(135deg,#f0ebff,#ddd6fe)">
            <div class="ph-icon">🎓</div>
            <h3>Student Portal</h3>
            <p>View your GPA, transcript, course progress, and AI tutor — everything in one elegant dashboard.</p>
          </div>
          <div class="pf">
            <span>Enter Student Portal</span>
            <span class="pill" style="background:#ede9fe;color:#6d28d9">Ian Jiang</span>
          </div>
        </a>
      </div>
      <div class="col-md-4">
        <a href="/admin/dashboard.php" class="portal-card">
          <div class="ph" style="background:linear-gradient(135deg,#dbeafe,#bfdbfe)">
            <div class="ph-icon">⚡</div>
            <h3>Admin Dashboard</h3>
            <p>Full control — student records, transcripts, enrollment management, and system health at a glance.</p>
          </div>
          <div class="pf">
            <span>Enter Admin Dashboard</span>
            <span class="pill" style="background:#dbeafe;color:#1e40af">Dr. Freedom</span>
          </div>
        </a>
      </div>
      <div class="col-md-4">
        <a href="/teacher/dashboard.php" class="portal-card">
          <div class="ph" style="background:linear-gradient(135deg,#dcfce7,#bbf7d0)">
            <div class="ph-icon">📚</div>
            <h3>Teacher Workspace</h3>
            <p>Collaborative course creation, student workspaces, and document management for faculty.</p>
          </div>
          <div class="pf">
            <span>Enter Teacher Workspace</span>
            <span class="pill" style="background:#dcfce7;color:#166534">Faculty Only</span>
          </div>
        </a>
      </div>
    </div>
  </div>
</section>

<!-- ECOSYSTEM -->
<section class="ecosystem">
  <div class="container">
    <div class="text-center mb-5">
      <div class="eyebrow" style="color:var(--gold)">School of Freedom Ecosystem</div>
      <h2 class="sec-title" style="color:#fff;max-width:none">One identity. Three schools.</h2>
      <p style="color:rgba(255,255,255,.58);max-width:480px;margin:0 auto;font-size:.97rem;line-height:1.72">
        Sign in once at ai.thevrschool.org and move seamlessly between all three
        platforms with a single Google account — no re-authentication ever.
      </p>
    </div>
    <div class="row g-4">
      <div class="col-md-4">
        <a href="https://sof.ai" target="_blank" class="eco-card">
          <div class="eco-icon">🌐</div>
          <h4>School of Freedom</h4>
          <p>The gateway. One profile for individuals, corporations, and partner institutions across all three schools.</p>
          <div class="eco-url">sof.ai ↗</div>
        </a>
      </div>
      <div class="col-md-4">
        <a href="https://ai.thevrschool.org" target="_blank" class="eco-card" style="border-color:rgba(201,168,76,.25)">
          <div class="eco-icon">🔐</div>
          <h4>School of AI · Auth Hub</h4>
          <p>The canonical sign-in surface for the entire ecosystem. Google SSO, magic links, and bridge tokens flow from here.</p>
          <div class="eco-url">ai.thevrschool.org ↗</div>
        </a>
      </div>
      <div class="col-md-4">
        <a href="https://www.thevrschool.org" target="_blank" class="eco-card">
          <div class="eco-icon">🥽</div>
          <h4>The VR School</h4>
          <p>WASC-accredited virtual reality education. UC A-G approved courses, immersive labs, and proof-of-learning portfolios.</p>
          <div class="eco-url">thevrschool.org ↗</div>
        </a>
      </div>
    </div>
    <div class="text-center mt-5">
      <a href="<?= $loginUrl ?>" class="btn-gold" style="display:inline-flex">
        <i class="fas fa-sign-in-alt"></i> Sign in with your VR School account
      </a>
    </div>
  </div>
</section>

<!-- FOOTER -->
<footer class="footer">
  <div class="container">
    <div class="row align-items-center">
      <div class="col-md-4 mb-3 mb-md-0">
        <div style="font-family:'Playfair Display',serif;font-size:1.1rem;color:rgba(255,255,255,.65);font-weight:700">
          iTeach<span style="color:var(--gold)">XR</span>
        </div>
        <div style="margin-top:.25rem">AI-first LMS · The VR School · Stanford, CA</div>
      </div>
      <div class="col-md-4 text-center mb-3 mb-md-0">
        <div style="display:flex;justify-content:center;gap:1.75rem">
          <a href="https://sof.ai" target="_blank">sof.ai</a>
          <a href="https://ai.thevrschool.org" target="_blank">AI School</a>
          <a href="https://www.thevrschool.org" target="_blank">VR School</a>
        </div>
      </div>
      <div class="col-md-4 text-md-end">
        <div>&copy; <?= $year ?> School of Freedom. All rights reserved.</div>
      </div>
    </div>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
const nav = document.getElementById('sitenav');
window.addEventListener('scroll', () => {
  nav.classList.toggle('scrolled', window.scrollY > 60);
}, { passive: true });
</script>
</body>
</html>
