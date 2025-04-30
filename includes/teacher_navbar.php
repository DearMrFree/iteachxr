<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="/index.php">
            <img src="/images/logo-royal.svg" alt="iTeachXR Logo" height="42" class="d-inline-block align-text-top me-2">
            <span class="fw-bold" style="font-family: 'Playfair Display', serif;">iTeachXR</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <style>
            .navbar .nav-link {
                color: rgba(255, 255, 255, 0.9) !important;
                font-weight: 500;
                padding: 0.5rem 1rem;
                margin: 0 2px;
                border-radius: 4px;
                transition: all 0.2s ease;
                position: relative;
                text-shadow: 0 1px 2px rgba(0,0,0,0.2);
            }
            
            .navbar .nav-link:hover {
                color: #FFFFFF !important;
                background-color: rgba(255, 255, 255, 0.1);
            }
            
            .navbar .nav-link.active {
                background-color: rgba(255, 255, 255, 0.15);
                color: #FFFFFF !important;
                box-shadow: 0 2px 0 #F9A602;
            }
            
            .navbar .nav-link i {
                color: #F9A602;
            }
        </style>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : ''; ?>" href="/teacher/dashboard.php">
                        <i class="fas fa-home me-1"></i> Home
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'courses.php' ? 'active' : ''; ?>" href="/teacher/courses.php">
                        <i class="fas fa-book me-1"></i> Courses
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) === 'collaborative_workspace.php' || basename($_SERVER['PHP_SELF']) === 'workspace_detail.php' || basename($_SERVER['PHP_SELF']) === 'document_editor.php') ? 'active' : ''; ?>" href="/teacher/collaborative_workspace.php">
                        <i class="fas fa-users me-1"></i> Collaboration
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'ai_demo.php' ? 'active' : ''; ?>" href="/ai_demo.php">
                        <i class="fas fa-magic me-1"></i> AI Tools
                    </a>
                </li>
            </ul>
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-bell me-1"></i>
                        <span class="badge bg-danger">3</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                        <li><h6 class="dropdown-header">Notifications</h6></li>
                        <li><a class="dropdown-item" href="#">New comment on your lesson plan</a></li>
                        <li><a class="dropdown-item" href="#">Jane Smith joined your workspace</a></li>
                        <li><a class="dropdown-item" href="#">Assignment submission from John Doe</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="#">View all notifications</a></li>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-user-circle me-1"></i> <?php echo htmlspecialchars($user['full_name'] ?? 'Teacher'); ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li><a class="dropdown-item" href="/teacher/profile.php"><i class="fas fa-user me-2"></i> Profile</a></li>
                        <li><a class="dropdown-item" href="/teacher/settings.php"><i class="fas fa-cog me-2"></i> Settings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="/logout.php"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>