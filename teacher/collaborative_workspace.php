<?php
// Teacher Collaborative Workspace
// Include accessibility helper functions
require_once('../includes/accessibility.php');

$pageTitle = "Collaborative Workspace - iTeachXR";
$user = [
    'id' => 1,
    'full_name' => 'Dr. Sarah Chen',
    'email' => 'sarah.chen@example.edu',
    'role' => 'teacher'
];

// Get user's accessibility settings
$accessibility_settings = get_user_accessibility_settings();
$accessibility_classes = get_accessibility_classes($accessibility_settings);

// Sample workspaces data
$workspaces = [
    [
        'id' => 1,
        'name' => 'VR Curriculum Planning',
        'description' => 'Collaborative workspace for developing our VR curriculum',
        'created_at' => '2025-04-15',
        'members' => 5,
        'documents' => 8,
        'owner' => 'Dr. Sarah Chen',
        'tags' => ['VR', 'Curriculum', 'Planning']
    ],
    [
        'id' => 2,
        'name' => 'AR Science Lessons',
        'description' => 'Augmented reality lesson plans for science courses',
        'created_at' => '2025-04-20',
        'members' => 3,
        'documents' => 4,
        'owner' => 'John Davis',
        'tags' => ['AR', 'Science', 'Lesson Plans']
    ],
    [
        'id' => 3,
        'name' => 'Mathematics in 3D',
        'description' => 'Creating interactive 3D models for teaching mathematics concepts',
        'created_at' => '2025-04-25',
        'members' => 4,
        'documents' => 6,
        'owner' => 'Dr. Sarah Chen',
        'tags' => ['3D', 'Mathematics', 'Models']
    ]
];

// Filter for recent activity (last 7 days)
$recentActivity = [
    [
        'id' => 1,
        'user' => 'John Davis',
        'action' => 'edited document',
        'document' => 'AR Biology Lab Guide',
        'workspace' => 'AR Science Lessons',
        'time' => '2 hours ago'
    ],
    [
        'id' => 2,
        'user' => 'Maria Rodriguez',
        'action' => 'commented on',
        'document' => 'Trigonometry 3D Models',
        'workspace' => 'Mathematics in 3D',
        'time' => '5 hours ago'
    ],
    [
        'id' => 3,
        'user' => 'Dr. Sarah Chen',
        'action' => 'created document',
        'document' => 'VR Headset Comparison Guide',
        'workspace' => 'VR Curriculum Planning',
        'time' => '1 day ago'
    ],
    [
        'id' => 4,
        'user' => 'Alex Johnson',
        'action' => 'joined workspace',
        'workspace' => 'Mathematics in 3D',
        'time' => '2 days ago'
    ]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="iTeachXR Collaborative Workspace - Create and share educational resources with colleagues">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Accessibility CSS -->
    <?php echo get_accessibility_css($accessibility_settings); ?>
    <?php echo get_accessibility_ui_css(); ?>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Poppins:wght@300;400;500;600&display=swap');
        
        :root {
            /* Royal color scheme */
            --primary-color: #2E3192; /* Royal Blue */
            --primary-light: #4153B3;
            --primary-dark: #1B1464;
            --secondary-color: #D4AF37; /* Royal Gold */
            --secondary-light: #F9A602;
            --secondary-dark: #9E7E23;
            --accent-color: #731963; /* Royal Purple */
            --text-color: #2D2D2D;
            --light-text: #6c757d;
            --white: #fff;
            --light-bg: #F8F7FA;
            --border-color: #e9e7f0;
        }
        
        body {
            background-color: var(--light-bg);
            font-family: 'Poppins', sans-serif;
            color: var(--text-color);
        }
        
        h1, h2, h3, h4, h5, h6, .card-title {
            font-family: 'Playfair Display', serif;
            font-weight: 600;
        }
        
        .main-content {
            padding: 30px;
        }
        
        .sidebar {
            background: linear-gradient(180deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            border-right: 1px solid rgba(255,255,255,0.1);
            min-height: 100vh;
            color: var(--white);
            position: relative;
            overflow: hidden;
        }
        
        .sidebar::after {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            left: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            pointer-events: none;
            z-index: 0;
        }
        
        .workspace-card {
            transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
            margin-bottom: 20px;
            border-radius: 12px;
            overflow: hidden;
            border: none;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            background: linear-gradient(145deg, #ffffff, #f8f7fa);
        }
        
        .workspace-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(46,49,146,0.15);
        }
        
        .workspace-card::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .workspace-card:hover::after {
            opacity: 1;
        }
        
        .workspace-card .card-title {
            margin-bottom: 15px;
        }
        
        .workspace-card .card-title a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 700;
            position: relative;
            display: inline-block;
        }
        
        .workspace-card .card-title a::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background: linear-gradient(90deg, var(--secondary-color), var(--secondary-light));
            transition: width 0.3s ease;
        }
        
        .workspace-card .card-title a:hover {
            color: var(--primary-light);
        }
        
        .workspace-card .card-title a:hover::after {
            width: 100%;
        }
        
        .workspace-card .card-footer {
            background-color: rgba(46,49,146,0.03);
            border-top: 1px solid rgba(46,49,146,0.05);
        }
        
        .tag {
            font-size: 0.7rem;
            padding: 0.2rem 0.7rem;
            border-radius: 50px;
            margin-right: 5px;
            margin-bottom: 5px;
            display: inline-block;
            background-color: rgba(212,175,55,0.1);
            color: var(--primary-dark);
            font-weight: 500;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            border: 1px solid rgba(212,175,55,0.2);
        }
        
        .tag:hover {
            background-color: rgba(212,175,55,0.2);
            transform: translateY(-2px);
        }
        
        .activity-item {
            padding: 18px;
            border-left: 3px solid var(--secondary-color);
            margin-bottom: 15px;
            background-color: var(--white);
            border-radius: 0 12px 12px 0;
            transition: all 0.3s ease;
            box-shadow: 0 3px 10px rgba(0,0,0,0.03);
            position: relative;
        }
        
        .activity-item:hover {
            box-shadow: 0 8px 20px rgba(0,0,0,0.07);
            transform: translateX(5px);
        }
        
        .activity-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: -3px;
            width: 3px;
            height: 0;
            background-color: var(--accent-color);
            transition: height 0.3s ease;
        }
        
        .activity-item:hover::before {
            height: 100%;
        }
        
        .navbar {
            background: linear-gradient(90deg, var(--primary-color), var(--primary-dark)) !important;
            box-shadow: 0 3px 15px rgba(0,0,0,0.1);
            border-bottom: 1px solid rgba(255,255,255,0.1);
            position: relative;
            z-index: 1000;
        }
        
        .navbar::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            height: 2px;
            width: 100%;
            background: linear-gradient(90deg, var(--secondary-light), var(--secondary-dark));
        }
        
        .custom-card-header {
            background: linear-gradient(145deg, #ffffff, #f8f7fa);
            border-bottom: 1px solid var(--border-color);
            padding: 18px 20px;
            font-weight: 600;
            font-family: 'Playfair Display', serif;
            color: var(--primary-color);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            border: none;
            box-shadow: 0 4px 10px rgba(46,49,146,0.3);
            transition: all 0.3s ease;
            font-weight: 500;
            letter-spacing: 0.5px;
            position: relative;
            overflow: hidden;
            z-index: 1;
        }
        
        .btn-primary::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, var(--primary-dark), var(--primary-color));
            transition: left 0.4s cubic-bezier(0.19, 1, 0.22, 1);
            z-index: -1;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(46,49,146,0.4);
        }
        
        .btn-primary:hover::after {
            left: 0;
        }
        
        .btn-outline-primary {
            color: var(--primary-color);
            border: 2px solid var(--primary-color);
            background: transparent;
            font-weight: 500;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            position: relative;
            z-index: 1;
            overflow: hidden;
        }
        
        .btn-outline-primary::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            transition: left 0.4s cubic-bezier(0.19, 1, 0.22, 1);
            z-index: -1;
            opacity: 0;
        }
        
        .btn-outline-primary:hover {
            color: var(--white);
            border-color: var(--primary-color);
            box-shadow: 0 4px 10px rgba(46,49,146,0.2);
        }
        
        .btn-outline-primary:hover::after {
            left: 0;
            opacity: 1;
        }
        
        .text-primary {
            color: var(--primary-color) !important;
        }
        
        .card {
            border-radius: 15px;
            border: none;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            overflow: hidden;
        }
        
        .card:hover {
            box-shadow: 0 10px 30px rgba(46,49,146,0.1);
        }
        
        .card-body {
            padding: 1.5rem;
        }
        
        /* Scrollbar customization */
        ::-webkit-scrollbar {
            width: 10px;
        }
        
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        
        ::-webkit-scrollbar-thumb {
            background: linear-gradient(var(--primary-light), var(--primary-color));
            border-radius: 10px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: var(--primary-dark);
        }
    </style>
</head>
<body class="<?php echo $accessibility_classes; ?>">
<?php echo get_skip_to_content_link(); ?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-2 d-none d-md-block sidebar p-0">
            <?php include_once('../includes/teacher_sidebar.php'); ?>
        </div>

        <!-- Main Content -->
        <div class="col-md-10 main-content" role="main" id="main-content">
            <!-- Navbar -->
            <?php include_once('../includes/teacher_navbar.php'); ?>
            
            <div class="d-flex justify-content-between align-items-center mb-4 mt-4">
                <div>
                    <h1 class="h3 mb-2 text-primary" style="font-family: 'Playfair Display', serif; font-weight: 700; letter-spacing: 0.5px;">Collaborative Workspace</h1>
                    <p class="text-muted" style="max-width: 700px; line-height: 1.5;">Work together with colleagues to create and share educational resources for immersive learning experiences</p>
                </div>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createWorkspaceModal" aria-label="Create New Workspace">
                    <i class="fas fa-plus me-2" aria-hidden="true"></i> New Workspace
                </button>
            </div>
            
            <div class="row mb-4">
                <!-- Workspaces Section -->
                <div class="col-lg-8">
                    <div class="card shadow mb-4">
                        <div class="card-header custom-card-header d-flex justify-content-between align-items-center">
                            <h6 class="m-0 font-weight-bold text-primary" style="font-family: 'Playfair Display', serif; letter-spacing: 0.5px; font-size: 1.1rem;">My Workspaces</h6>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="workspaceFilterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                    Filter
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="workspaceFilterDropdown">
                                    <li><a class="dropdown-item" href="#">All Workspaces</a></li>
                                    <li><a class="dropdown-item" href="#">My Workspaces</a></li>
                                    <li><a class="dropdown-item" href="#">Shared With Me</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="#">Recently Updated</a></li>
                                </ul>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="input-group mb-3" role="search">
                                <label for="workspaceSearch" class="sr-only">Search workspaces</label>
                                <input type="text" id="workspaceSearch" class="form-control" placeholder="Search workspaces..." aria-label="Search workspaces">
                                <button class="btn btn-outline-secondary" type="button" aria-label="Search">
                                    <i class="fas fa-search" aria-hidden="true"></i>
                                </button>
                            </div>
                            
                            <div class="row">
                                <?php foreach ($workspaces as $workspace): ?>
                                <div class="col-md-6">
                                    <div class="card workspace-card shadow-sm h-100">
                                        <div class="card-body">
                                            <h5 class="card-title">
                                                <a href="workspace_detail.php?id=<?php echo $workspace['id']; ?>" class="text-decoration-none">
                                                    <?php echo htmlspecialchars($workspace['name']); ?>
                                                </a>
                                            </h5>
                                            <p class="card-text"><?php echo htmlspecialchars($workspace['description']); ?></p>
                                            <div class="mb-2">
                                                <?php foreach ($workspace['tags'] as $tag): ?>
                                                <span class="tag"><?php echo htmlspecialchars($tag); ?></span>
                                                <?php endforeach; ?>
                                            </div>
                                            <div class="d-flex justify-content-between">
                                                <small class="text-muted">
                                                    <i class="fas fa-users me-1"></i> <?php echo $workspace['members']; ?> members
                                                </small>
                                                <small class="text-muted">
                                                    <i class="fas fa-file-alt me-1"></i> <?php echo $workspace['documents']; ?> docs
                                                </small>
                                            </div>
                                        </div>
                                        <div class="card-footer bg-transparent">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <small class="text-muted">Created: <?php echo $workspace['created_at']; ?></small>
                                                <div>
                                                    <button class="btn btn-sm btn-outline-primary" onclick="window.location.href='workspace_detail.php?id=<?php echo $workspace['id']; ?>'" aria-label="View <?php echo htmlspecialchars($workspace['name']); ?> workspace details">
                                                        <i class="fas fa-arrow-right" aria-hidden="true"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Activity Feed -->
                <div class="col-lg-4">
                    <div class="card shadow mb-4">
                        <div class="card-header custom-card-header">
                            <h6 class="m-0 font-weight-bold text-primary" style="font-family: 'Playfair Display', serif; letter-spacing: 0.5px; font-size: 1.1rem;">Recent Activity</h6>
                        </div>
                        <div class="card-body">
                            <?php foreach ($recentActivity as $activity): ?>
                            <div class="activity-item">
                                <div class="d-flex justify-content-between">
                                    <strong><?php echo htmlspecialchars($activity['user']); ?></strong>
                                    <small class="text-muted"><?php echo $activity['time']; ?></small>
                                </div>
                                <div>
                                    <?php echo $activity['action']; ?>
                                    <?php if (isset($activity['document'])): ?>
                                    "<a href="#" class="text-decoration-none"><?php echo htmlspecialchars($activity['document']); ?></a>"
                                    <?php endif; ?>
                                    in <a href="workspace_detail.php" class="text-decoration-none"><?php echo htmlspecialchars($activity['workspace']); ?></a>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            
                            <div class="text-center mt-3">
                                <a href="#" class="btn btn-sm btn-outline-primary">View All Activity</a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Quick Actions Card -->
                    <div class="card shadow">
                        <div class="card-header custom-card-header">
                            <h6 class="m-0 font-weight-bold text-primary" style="font-family: 'Playfair Display', serif; letter-spacing: 0.5px; font-size: 1.1rem;">Quick Actions</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <button class="btn btn-outline-primary" aria-label="Create new document">
                                    <i class="fas fa-file-alt me-2" aria-hidden="true"></i> Create New Document
                                </button>
                                <button class="btn btn-outline-primary" aria-label="Invite colleagues to collaborate">
                                    <i class="fas fa-user-plus me-2" aria-hidden="true"></i> Invite Colleagues
                                </button>
                                <button class="btn btn-outline-primary" aria-label="Upload educational resources">
                                    <i class="fas fa-cloud-upload-alt me-2" aria-hidden="true"></i> Upload Resources
                                </button>
                                <button class="btn btn-outline-primary" aria-label="Generate content with AI">
                                    <i class="fas fa-magic me-2" aria-hidden="true"></i> Generate with AI
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Workspace Modal -->
<div class="modal fade" id="createWorkspaceModal" tabindex="-1" aria-labelledby="createWorkspaceModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(145deg, #ffffff, #f8f7fa); border-bottom: 1px solid var(--border-color);">
                <h5 class="modal-title text-primary" id="createWorkspaceModalLabel" style="font-family: 'Playfair Display', serif; font-weight: 600; letter-spacing: 0.5px;">Create New Workspace</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="mb-3">
                        <label for="workspaceName" class="form-label">Workspace Name</label>
                        <input type="text" class="form-control" id="workspaceName" placeholder="Enter workspace name">
                    </div>
                    <div class="mb-3">
                        <label for="workspaceDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="workspaceDescription" rows="3" placeholder="Describe the purpose of this workspace"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="workspaceTags" class="form-label">Tags</label>
                        <input type="text" class="form-control" id="workspaceTags" placeholder="Enter tags separated by commas">
                        <div class="form-text">Tags help organize and search for workspaces (e.g., VR, Science, Mathematics)</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Visibility</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="workspaceVisibility" id="visibilityPrivate" value="private" checked>
                            <label class="form-check-label" for="visibilityPrivate">
                                Private - Only invited members can access
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="workspaceVisibility" id="visibilityDepartment" value="department">
                            <label class="form-check-label" for="visibilityDepartment">
                                Department - Visible to all members of your department
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="workspaceVisibility" id="visibilityPublic" value="public">
                            <label class="form-check-label" for="visibilityPublic">
                                Public - Visible to all teachers in the school
                            </label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" aria-label="Create and save new workspace">Create Workspace</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Modal Accessibility Script -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Get the modal element
        const modal = document.getElementById('createWorkspaceModal');
        
        if (modal) {
            // Get all focusable elements in the modal
            const focusableElements = modal.querySelectorAll('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
            const firstElement = focusableElements[0];
            const lastElement = focusableElements[focusableElements.length - 1];
            
            // Trap focus in modal
            modal.addEventListener('keydown', function(e) {
                // If tab key is pressed
                if (e.key === 'Tab') {
                    // If shift key is also pressed
                    if (e.shiftKey) {
                        // If we're on the first focusable element, loop to the last
                        if (document.activeElement === firstElement) {
                            lastElement.focus();
                            e.preventDefault();
                        }
                    } else {
                        // If we're on the last focusable element, loop to the first
                        if (document.activeElement === lastElement) {
                            firstElement.focus();
                            e.preventDefault();
                        }
                    }
                }
                
                // Close modal on escape key
                if (e.key === 'Escape') {
                    const bsModal = bootstrap.Modal.getInstance(modal);
                    if (bsModal) {
                        bsModal.hide();
                    }
                }
            });
            
            // Set focus on first element when modal opens
            modal.addEventListener('shown.bs.modal', function() {
                firstElement.focus();
            });
            
            // Return focus to the element that opened the modal
            let lastFocusedElement = null;
            document.querySelector('[data-bs-target="#createWorkspaceModal"]').addEventListener('click', function() {
                lastFocusedElement = this;
            });
            
            modal.addEventListener('hidden.bs.modal', function() {
                if (lastFocusedElement) {
                    lastFocusedElement.focus();
                }
            });
        }
    });
</script>

<!-- Accessibility Controls -->
<?php echo get_accessibility_controls($accessibility_settings); ?>
<?php echo get_accessibility_toggle(); ?>

<!-- Accessibility JavaScript -->
<?php echo get_accessibility_javascript(); ?>
</body>
</html>