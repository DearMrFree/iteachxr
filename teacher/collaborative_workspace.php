<?php
// Teacher Collaborative Workspace
$pageTitle = "Collaborative Workspace - iTeachXR";
$user = [
    'id' => 1,
    'full_name' => 'Dr. Sarah Chen',
    'email' => 'sarah.chen@example.edu',
    'role' => 'teacher'
];

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
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .main-content {
            padding: 30px;
        }
        .sidebar {
            background-color: #f8f9fa;
            border-right: 1px solid #e3e6f0;
            min-height: 100vh;
        }
        .workspace-card {
            transition: transform 0.2s, box-shadow 0.2s;
            margin-bottom: 20px;
            border-radius: 10px;
            overflow: hidden;
        }
        .workspace-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .tag {
            font-size: 0.8rem;
            padding: 0.2rem 0.5rem;
            border-radius: 50px;
            margin-right: 5px;
            background-color: #e9ecef;
        }
        .activity-item {
            padding: 15px;
            border-left: 3px solid #4e73df;
            margin-bottom: 10px;
            background-color: white;
            border-radius: 0 5px 5px 0;
        }
        .navbar {
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .custom-card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #e3e6f0;
            padding: 15px 20px;
        }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-2 d-none d-md-block sidebar p-0">
            <?php include_once('../includes/teacher_sidebar.php'); ?>
        </div>

        <!-- Main Content -->
        <div class="col-md-10 main-content">
            <!-- Navbar -->
            <?php include_once('../includes/teacher_navbar.php'); ?>
            
            <div class="d-flex justify-content-between align-items-center mb-4 mt-4">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">Collaborative Workspace</h1>
                    <p class="text-muted">Work together with colleagues to create and share educational resources</p>
                </div>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createWorkspaceModal">
                    <i class="fas fa-plus me-2"></i> New Workspace
                </button>
            </div>
            
            <div class="row mb-4">
                <!-- Workspaces Section -->
                <div class="col-lg-8">
                    <div class="card shadow mb-4">
                        <div class="card-header custom-card-header d-flex justify-content-between align-items-center">
                            <h6 class="m-0 font-weight-bold text-primary">My Workspaces</h6>
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
                            <div class="input-group mb-3">
                                <input type="text" class="form-control" placeholder="Search workspaces..." aria-label="Search workspaces">
                                <button class="btn btn-outline-secondary" type="button"><i class="fas fa-search"></i></button>
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
                                                    <button class="btn btn-sm btn-outline-primary" onclick="window.location.href='workspace_detail.php?id=<?php echo $workspace['id']; ?>'">
                                                        <i class="fas fa-arrow-right"></i>
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
                            <h6 class="m-0 font-weight-bold text-primary">Recent Activity</h6>
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
                            <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <button class="btn btn-outline-primary">
                                    <i class="fas fa-file-alt me-2"></i> Create New Document
                                </button>
                                <button class="btn btn-outline-primary">
                                    <i class="fas fa-user-plus me-2"></i> Invite Colleagues
                                </button>
                                <button class="btn btn-outline-primary">
                                    <i class="fas fa-cloud-upload-alt me-2"></i> Upload Resources
                                </button>
                                <button class="btn btn-outline-primary">
                                    <i class="fas fa-magic me-2"></i> Generate with AI
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
            <div class="modal-header">
                <h5 class="modal-title" id="createWorkspaceModalLabel">Create New Workspace</h5>
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
                <button type="button" class="btn btn-primary">Create Workspace</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>