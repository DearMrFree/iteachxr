<div class="position-sticky p-3">
    <div class="d-flex align-items-center mb-4 px-2">
        <img src="/images/logo-royal.svg" alt="iTeachXR Logo" height="48" class="me-2">
        <span class="fs-4 fw-bold text-white" style="font-family: 'Playfair Display', serif;">iTeachXR</span>
    </div>
    
    <ul class="nav flex-column mb-4">
        <li class="nav-item mb-1">
            <a class="nav-link text-white rounded <?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active bg-white bg-opacity-25' : ''; ?>" href="/teacher/dashboard.php">
                <i class="fas fa-tachometer-alt me-2"></i> Dashboard
            </a>
        </li>
        <li class="nav-item mb-1">
            <a class="nav-link text-white rounded <?php echo basename($_SERVER['PHP_SELF']) === 'courses.php' ? 'active bg-white bg-opacity-25' : ''; ?>" href="/teacher/courses.php">
                <i class="fas fa-book me-2"></i> My Courses
            </a>
        </li>
        <li class="nav-item mb-1">
            <a class="nav-link text-white rounded <?php echo (basename($_SERVER['PHP_SELF']) === 'collaborative_workspace.php' || basename($_SERVER['PHP_SELF']) === 'workspace_detail.php' || basename($_SERVER['PHP_SELF']) === 'document_editor.php') ? 'active bg-white bg-opacity-25' : ''; ?>" href="/teacher/collaborative_workspace.php">
                <i class="fas fa-users me-2"></i> Collaborative Workspace
            </a>
        </li>
        <li class="nav-item mb-1">
            <a class="nav-link text-white rounded <?php echo basename($_SERVER['PHP_SELF']) === 'ai_demo.php' ? 'active bg-white bg-opacity-25' : ''; ?>" href="/ai_demo.php">
                <i class="fas fa-robot me-2"></i> AI Features
            </a>
        </li>
        <li class="nav-item mb-1">
            <a class="nav-link text-white rounded <?php echo basename($_SERVER['PHP_SELF']) === 'assignments.php' ? 'active bg-white bg-opacity-25' : ''; ?>" href="/teacher/assignments.php">
                <i class="fas fa-tasks me-2"></i> Assignments
            </a>
        </li>
        <li class="nav-item mb-1">
            <a class="nav-link text-white rounded <?php echo basename($_SERVER['PHP_SELF']) === 'students.php' ? 'active bg-white bg-opacity-25' : ''; ?>" href="/teacher/students.php">
                <i class="fas fa-user-graduate me-2"></i> Students
            </a>
        </li>
        <li class="nav-item mb-1">
            <a class="nav-link text-white rounded <?php echo basename($_SERVER['PHP_SELF']) === 'grades.php' ? 'active bg-white bg-opacity-25' : ''; ?>" href="/teacher/grades.php">
                <i class="fas fa-chart-line me-2"></i> Grades
            </a>
        </li>
        <li class="nav-item mb-1">
            <a class="nav-link text-white rounded <?php echo basename($_SERVER['PHP_SELF']) === 'resources.php' ? 'active bg-white bg-opacity-25' : ''; ?>" href="/teacher/resources.php">
                <i class="fas fa-folder-open me-2"></i> Resources
            </a>
        </li>
        <li class="nav-item mb-1">
            <a class="nav-link text-white rounded <?php echo basename($_SERVER['PHP_SELF']) === 'profile.php' ? 'active bg-white bg-opacity-25' : ''; ?>" href="/teacher/profile.php">
                <i class="fas fa-user-circle me-2"></i> Profile
            </a>
        </li>
    </ul>
    
    <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-2 text-white text-opacity-75 fw-bold">
        <span>Quick Tools</span>
        <i class="fas fa-bolt"></i>
    </h6>
    <div class="px-3">
        <div class="d-grid gap-2">
            <a class="btn btn-sm btn-outline-light border-2 fw-semibold" href="/ai_demo.php?section=course_structure" style="color:#F9A602; border-color:#F9A602;">
                <i class="fas fa-crown me-2"></i> Generate Course Structure
            </a>
            <a class="btn btn-sm btn-outline-light border-2 fw-semibold" href="/teacher/collaborative_workspace.php" style="color:#F9A602; border-color:#F9A602;">
                <i class="fas fa-file-alt me-2"></i> Lesson Plans
            </a>
        </div>
    </div>
    
    <div class="mt-auto pt-5 text-center small">
        <p style="color:#F9A602; font-family: 'Playfair Display', serif; font-style: italic;">iTeachXR v1.0<br>Excellence in Education</p>
    </div>
</div>