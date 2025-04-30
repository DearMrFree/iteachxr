<div class="position-sticky">
    <ul class="nav flex-column">
        <li class="nav-item">
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : ''; ?>" href="/teacher/dashboard.php">
                <i class="fas fa-tachometer-alt me-2"></i> Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'courses.php' ? 'active' : ''; ?>" href="/teacher/courses.php">
                <i class="fas fa-book me-2"></i> My Courses
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) === 'collaborative_workspace.php' || basename($_SERVER['PHP_SELF']) === 'workspace_detail.php' || basename($_SERVER['PHP_SELF']) === 'document_editor.php') ? 'active' : ''; ?>" href="/teacher/collaborative_workspace.php">
                <i class="fas fa-users-gear me-2"></i> Collaborative Workspace
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'ai_demo.php' ? 'active' : ''; ?>" href="/ai_demo.php">
                <i class="fas fa-robot me-2"></i> AI Features
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'assignments.php' ? 'active' : ''; ?>" href="/teacher/assignments.php">
                <i class="fas fa-tasks me-2"></i> Assignments
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'students.php' ? 'active' : ''; ?>" href="/teacher/students.php">
                <i class="fas fa-user-graduate me-2"></i> Students
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'grades.php' ? 'active' : ''; ?>" href="/teacher/grades.php">
                <i class="fas fa-chart-line me-2"></i> Grades
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'resources.php' ? 'active' : ''; ?>" href="/teacher/resources.php">
                <i class="fas fa-folder-open me-2"></i> Resources
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'profile.php' ? 'active' : ''; ?>" href="/teacher/profile.php">
                <i class="fas fa-user-circle me-2"></i> Profile
            </a>
        </li>
    </ul>
    
    <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
        <span>Quick Links</span>
    </h6>
    <ul class="nav flex-column mb-2">
        <li class="nav-item">
            <a class="nav-link" href="/ai_demo.php?section=course_structure">
                <i class="fas fa-magic me-2"></i> Generate Course Structure
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="/teacher/collaborative_workspace.php">
                <i class="fas fa-file-alt me-2"></i> Lesson Plans
            </a>
        </li>
    </ul>
</div>