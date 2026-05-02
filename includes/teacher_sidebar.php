<div class="position-sticky p-3">
    <div class="d-flex align-items-center mb-4 px-2">
        <img src="/images/logo-royal.svg" alt="iTeachXR Logo" height="48" class="me-2">
        <span class="fs-4 fw-bold" style="font-family: 'Playfair Display', serif; color: #FFFFFF; text-shadow: 0 1px 3px rgba(0,0,0,0.3);">iTeachXR</span>
    </div>
    
    <!-- Enhanced contrast for better readability -->
    <style>
        .sidebar-nav-link {
            color: #FFFFFF !important;
            font-weight: 500;
            padding: 0.6rem 1rem;
            border-radius: 6px;
            margin-bottom: 3px;
            transition: all 0.2s ease;
            text-shadow: 0 1px 2px rgba(0,0,0,0.3);
            background-color: rgba(255,255,255,0.05);
            border-left: 3px solid transparent;
        }
        
        .sidebar-nav-link:hover {
            background-color: rgba(255,255,255,0.15);
            transform: translateX(3px);
            border-left: 3px solid #F9A602;
        }
        
        .sidebar-nav-link.active {
            background-color: rgba(255,255,255,0.2) !important;
            border-left: 3px solid #F9A602 !important;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        
        .nav-link i {
            color: #F9A602;
            width: 20px;
            text-align: center;
        }
    </style>
    
    <ul class="nav flex-column mb-4">
        <li class="nav-item">
            <a class="nav-link sidebar-nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : ''; ?>" href="/teacher/dashboard.php">
                <i class="fas fa-tachometer-alt me-2"></i> Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link sidebar-nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'courses.php' ? 'active' : ''; ?>" href="/teacher/courses.php">
                <i class="fas fa-book me-2"></i> My Courses
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link sidebar-nav-link <?php echo (basename($_SERVER['PHP_SELF']) === 'collaborative_workspace.php' || basename($_SERVER['PHP_SELF']) === 'workspace_detail.php' || basename($_SERVER['PHP_SELF']) === 'document_editor.php') ? 'active' : ''; ?>" href="/teacher/collaborative_workspace.php">
                <i class="fas fa-users me-2"></i> Collaborative Workspace
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link sidebar-nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'ai_demo.php' ? 'active' : ''; ?>" href="/ai_demo.php">
                <i class="fas fa-robot me-2"></i> AI Features
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link sidebar-nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'assignments.php' ? 'active' : ''; ?>" href="/teacher/assignments.php">
                <i class="fas fa-tasks me-2"></i> Assignments
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link sidebar-nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'students.php' ? 'active' : ''; ?>" href="/teacher/students.php">
                <i class="fas fa-user-graduate me-2"></i> Students
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link sidebar-nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'grades.php' ? 'active' : ''; ?>" href="/teacher/grades.php">
                <i class="fas fa-chart-line me-2"></i> Grades
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link sidebar-nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'resources.php' ? 'active' : ''; ?>" href="/teacher/resources.php">
                <i class="fas fa-folder-open me-2"></i> Resources
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link sidebar-nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'profile.php' ? 'active' : ''; ?>" href="/teacher/profile.php">
                <i class="fas fa-user-circle me-2"></i> Profile
            </a>
        </li>
    </ul>
    
    <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-2 fw-bold" style="color: #FFFFFF; text-shadow: 0 1px 2px rgba(0,0,0,0.3);">
        <span>Quick Tools</span>
        <i class="fas fa-bolt" style="color: #F9A602;"></i>
    </h6>
    <div class="px-3">
        <div class="d-grid gap-2">
            <a class="btn btn-sm btn-outline-light border-2 fw-semibold" href="/ai_demo.php?section=course_structure" style="color:#F9A602; border-color:#F9A602; background-color: rgba(255,255,255,0.1);">
                <i class="fas fa-crown me-2"></i> Generate Course Structure
            </a>
            <a class="btn btn-sm btn-outline-light border-2 fw-semibold" href="/teacher/collaborative_workspace.php" style="color:#F9A602; border-color:#F9A602; background-color: rgba(255,255,255,0.1);">
                <i class="fas fa-file-alt me-2"></i> Lesson Plans
            </a>
        </div>
    </div>
    
    <div class="mt-auto pt-4 px-3">
        <?php if (!empty($current_user)): ?>
        <div class="d-flex align-items-center gap-2 mb-3 p-2 rounded" style="background:rgba(255,255,255,0.1);">
            <?php if (!empty($current_user['image'])): ?>
            <img src="<?= htmlspecialchars($current_user['image']) ?>" alt="Avatar" width="32" height="32" class="rounded-circle">
            <?php else: ?>
            <div style="width:32px;height:32px;border-radius:50%;background:#F9A602;display:flex;align-items:center;justify-content:center;font-weight:700;color:#1a3a6b;font-size:0.85rem;">
                <?= htmlspecialchars(strtoupper(substr($current_user['name'] ?: $current_user['email'], 0, 1))) ?>
            </div>
            <?php endif; ?>
            <div style="min-width:0;">
                <div style="color:#fff;font-size:0.8rem;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                    <?= htmlspecialchars($current_user['name'] ?: 'Teacher') ?>
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
        <p class="text-center mb-0" style="color:#F9A602;font-family:'Playfair Display',serif;font-style:italic;font-size:0.8rem;text-shadow:0 1px 2px rgba(0,0,0,0.3);">iTeachXR v1.0<br>Excellence in Education</p>
    </div>
</div>