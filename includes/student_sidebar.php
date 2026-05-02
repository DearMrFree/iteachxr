<div class="position-sticky p-3">
    <div class="d-flex align-items-center mb-4 px-2">
        <img src="/images/logo-royal.svg" alt="iTeachXR Logo" height="44" class="me-2">
        <span class="fs-5 fw-bold" style="font-family:'Playfair Display',serif;color:#FFFFFF;text-shadow:0 1px 3px rgba(0,0,0,0.3);">iTeachXR</span>
    </div>

    <style>
        .student-nav-link {
            color: #FFFFFF !important;
            font-weight: 500;
            padding: 0.55rem 1rem;
            border-radius: 6px;
            margin-bottom: 3px;
            transition: all 0.2s ease;
            text-shadow: 0 1px 2px rgba(0,0,0,0.3);
            background-color: rgba(255,255,255,0.05);
            border-left: 3px solid transparent;
            display: flex;
            align-items: center;
            text-decoration: none;
        }
        .student-nav-link:hover {
            background-color: rgba(255,255,255,0.15);
            transform: translateX(3px);
            border-left: 3px solid #F9A602;
        }
        .student-nav-link.active {
            background-color: rgba(255,255,255,0.2) !important;
            border-left: 3px solid #F9A602 !important;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .student-nav-link i {
            color: #F9A602;
            width: 20px;
            text-align: center;
            margin-right: 0.5rem;
        }
        .sidebar-section-label {
            color: rgba(255,255,255,0.5);
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            padding: 0.5rem 1rem 0.25rem;
        }
    </style>

    <?php
    $page = basename($_SERVER['PHP_SELF']);
    function sActive(string $file, string $page): string {
        return $page === $file ? 'active' : '';
    }
    ?>

    <ul class="nav flex-column mb-3">
        <li class="nav-item">
            <a class="student-nav-link <?= sActive('dashboard.php',$page) ?>" href="/student/dashboard.php">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a class="student-nav-link <?= sActive('courses.php',$page) ?>" href="/student/courses.php">
                <i class="fas fa-book"></i> My Courses
            </a>
        </li>
        <li class="nav-item">
            <a class="student-nav-link <?= sActive('transcript.php',$page) ?>" href="/student/transcript.php">
                <i class="fas fa-scroll"></i> Transcript
            </a>
        </li>
        <li class="nav-item">
            <a class="student-nav-link <?= sActive('assignments.php',$page) ?>" href="/student/assignments.php">
                <i class="fas fa-tasks"></i> Assignments
            </a>
        </li>
        <li class="nav-item">
            <a class="student-nav-link <?= sActive('grades.php',$page) ?>" href="/student/grades.php">
                <i class="fas fa-chart-bar"></i> Grades
            </a>
        </li>
        <li class="nav-item">
            <a class="student-nav-link <?= sActive('profile.php',$page) ?>" href="/student/profile.php">
                <i class="fas fa-user-circle"></i> Profile
            </a>
        </li>
    </ul>

    <div class="sidebar-section-label">Quick Links</div>
    <ul class="nav flex-column mb-4">
        <li class="nav-item">
            <a class="student-nav-link" href="/ai_demo.php">
                <i class="fas fa-robot"></i> AI Tutor
            </a>
        </li>
        <li class="nav-item">
            <a class="student-nav-link" href="/student/transcript.php?print=1" target="_blank">
                <i class="fas fa-print"></i> Print Transcript
            </a>
        </li>
    </ul>

    <div class="mt-auto pt-3 px-3">
        <?php if (!empty($current_user)): ?>
        <div class="d-flex align-items-center gap-2 mb-3 p-2 rounded" style="background:rgba(255,255,255,0.1);">
            <?php if (!empty($current_user['image'])): ?>
            <img src="<?= htmlspecialchars($current_user['image']) ?>" alt="Avatar" width="32" height="32" class="rounded-circle">
            <?php else: ?>
            <div style="width:32px;height:32px;border-radius:50%;background:#F9A602;display:flex;align-items:center;justify-content:center;font-weight:700;color:#1a3a6b;font-size:0.85rem;flex-shrink:0;">
                <?= htmlspecialchars(strtoupper(substr($current_user['name'] ?: $current_user['email'], 0, 1))) ?>
            </div>
            <?php endif; ?>
            <div style="min-width:0;">
                <div style="color:#fff;font-size:0.8rem;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                    <?= htmlspecialchars($current_user['name'] ?: 'Student') ?>
                </div>
                <div style="color:#F9A602;font-size:0.7rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                    <?= htmlspecialchars($current_user['email']) ?>
                </div>
            </div>
        </div>
        <a href="/auth/logout.php" class="btn btn-sm w-100 fw-semibold mb-3" style="background:rgba(255,255,255,0.1);color:#fff;border:1px solid rgba(255,255,255,0.3);">
            <i class="fas fa-sign-out-alt me-1"></i> Sign Out
        </a>
        <?php endif; ?>
        <p class="text-center mb-0" style="color:#F9A602;font-family:'Playfair Display',serif;font-style:italic;font-size:0.75rem;text-shadow:0 1px 2px rgba(0,0,0,0.3);">iTeachXR v1.0<br>Excellence in Education</p>
    </div>
</div>
