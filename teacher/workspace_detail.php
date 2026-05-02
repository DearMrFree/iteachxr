<?php
// Teacher Workspace Detail page
require_once(__DIR__ . '/../auth/session.php');
$current_user = auth_require();
$pageTitle = "Workspace Detail - iTeachXR";
$user = [
    'id' => 1,
    'full_name' => 'Dr. Sarah Chen',
    'email' => 'sarah.chen@example.edu',
    'role' => 'teacher'
];

// Get workspace ID from URL parameter (with validation)
$workspace_id = isset($_GET['id']) ? intval($_GET['id']) : 1;

// Sample workspace data (in a real app, this would come from a database)
$workspace = [
    'id' => $workspace_id,
    'name' => 'VR Curriculum Planning',
    'description' => 'Collaborative workspace for developing our VR curriculum for the upcoming semester. This workspace is designed to help us coordinate the development of immersive learning experiences that align with educational standards.',
    'created_at' => '2025-04-15',
    'updated_at' => '2025-04-29',
    'members' => [
        ['id' => 1, 'name' => 'Dr. Sarah Chen', 'role' => 'Owner', 'avatar' => 'https://via.placeholder.com/40'],
        ['id' => 2, 'name' => 'John Davis', 'role' => 'Editor', 'avatar' => 'https://via.placeholder.com/40'],
        ['id' => 3, 'name' => 'Maria Rodriguez', 'role' => 'Editor', 'avatar' => 'https://via.placeholder.com/40'],
        ['id' => 4, 'name' => 'Alex Johnson', 'role' => 'Viewer', 'avatar' => 'https://via.placeholder.com/40'],
        ['id' => 5, 'name' => 'Emily Williams', 'role' => 'Editor', 'avatar' => 'https://via.placeholder.com/40']
    ],
    'documents' => [
        [
            'id' => 1,
            'name' => 'VR Headset Comparison Guide',
            'type' => 'document',
            'created_by' => 'Dr. Sarah Chen',
            'created_at' => '2025-04-24',
            'updated_at' => '2025-04-28',
            'status' => 'Published'
        ],
        [
            'id' => 2,
            'name' => 'Introduction to VR - Lesson Plan',
            'type' => 'lesson',
            'created_by' => 'Dr. Sarah Chen',
            'created_at' => '2025-04-19',
            'updated_at' => '2025-04-26',
            'status' => 'Draft'
        ],
        [
            'id' => 3,
            'name' => 'VR Ethics Discussion Guide',
            'type' => 'document',
            'created_by' => 'John Davis',
            'created_at' => '2025-04-22',
            'updated_at' => '2025-04-27',
            'status' => 'Published'
        ],
        [
            'id' => 4,
            'name' => 'Virtual Field Trip - Solar System',
            'type' => 'lesson',
            'created_by' => 'Maria Rodriguez',
            'created_at' => '2025-04-21',
            'updated_at' => '2025-04-23',
            'status' => 'Published'
        ],
        [
            'id' => 5,
            'name' => 'VR Hardware Requirements',
            'type' => 'document',
            'created_by' => 'Dr. Sarah Chen',
            'created_at' => '2025-04-17',
            'updated_at' => '2025-04-20',
            'status' => 'Published'
        ],
        [
            'id' => 6,
            'name' => 'VR Safety Guidelines',
            'type' => 'document',
            'created_by' => 'Emily Williams',
            'created_at' => '2025-04-18',
            'updated_at' => '2025-04-22',
            'status' => 'Published'
        ],
        [
            'id' => 7,
            'name' => 'Interactive Chemistry Lab - VR Design',
            'type' => 'lesson',
            'created_by' => 'Maria Rodriguez',
            'created_at' => '2025-04-24',
            'updated_at' => '2025-04-29',
            'status' => 'Draft'
        ],
        [
            'id' => 8,
            'name' => 'Assessment Strategies for VR Learning',
            'type' => 'document',
            'created_by' => 'John Davis',
            'created_at' => '2025-04-22',
            'updated_at' => '2025-04-25',
            'status' => 'Draft'
        ]
    ],
    'tags' => ['VR', 'Curriculum', 'Planning', 'Immersive Learning']
];

// Recent comments
$comments = [
    [
        'id' => 1,
        'user' => 'John Davis',
        'document' => 'VR Ethics Discussion Guide',
        'content' => 'I\'ve added a section on privacy concerns that we should address with students.',
        'time' => '3 hours ago'
    ],
    [
        'id' => 2,
        'user' => 'Maria Rodriguez',
        'document' => 'Virtual Field Trip - Solar System',
        'content' => 'The new NASA imagery has been incorporated. Let me know if you have any suggestions!',
        'time' => '1 day ago'
    ],
    [
        'id' => 3,
        'user' => 'Dr. Sarah Chen',
        'document' => 'VR Headset Comparison Guide',
        'content' => 'We should update this with the latest Quest models before the fall semester.',
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
        .document-card {
            transition: transform 0.2s;
            border-left: 4px solid #4e73df;
        }
        .document-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .document-card.draft {
            border-left-color: #f6c23e;
        }
        .document-card.published {
            border-left-color: #1cc88a;
        }
        .tag {
            font-size: 0.8rem;
            padding: 0.2rem 0.5rem;
            border-radius: 50px;
            margin-right: 5px;
            margin-bottom: 5px;
            display: inline-block;
            background-color: #e9ecef;
        }
        .comment-item {
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
        .member-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: -10px;
            border: 2px solid #fff;
        }
        .document-icon {
            font-size: 1.5rem;
            margin-right: 10px;
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
            
            <!-- Workspace Header -->
            <div class="d-flex justify-content-between align-items-center mb-4 mt-4">
                <div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="collaborative_workspace.php">Workspaces</a></li>
                            <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($workspace['name']); ?></li>
                        </ol>
                    </nav>
                    <h1 class="h3 mb-0 text-gray-800"><?php echo htmlspecialchars($workspace['name']); ?></h1>
                    <p class="text-muted"><?php echo htmlspecialchars($workspace['description']); ?></p>
                    <div class="mb-3">
                        <?php foreach ($workspace['tags'] as $tag): ?>
                        <span class="tag"><?php echo htmlspecialchars($tag); ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createDocumentModal">
                        <i class="fas fa-plus me-2"></i> New Document
                    </button>
                    <button class="btn btn-outline-primary">
                        <i class="fas fa-cog me-2"></i> Settings
                    </button>
                </div>
            </div>
            
            <div class="row mb-4">
                <!-- Documents Section -->
                <div class="col-lg-8">
                    <div class="card shadow mb-4">
                        <div class="card-header custom-card-header d-flex justify-content-between align-items-center">
                            <h6 class="m-0 font-weight-bold text-primary">Documents</h6>
                            <div class="d-flex">
                                <div class="input-group me-2" style="width: 250px;">
                                    <input type="text" class="form-control form-control-sm" placeholder="Search documents...">
                                    <button class="btn btn-outline-secondary btn-sm" type="button"><i class="fas fa-search"></i></button>
                                </div>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="documentFilterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                        Filter
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="documentFilterDropdown">
                                        <li><a class="dropdown-item" href="#">All Documents</a></li>
                                        <li><a class="dropdown-item" href="#">My Documents</a></li>
                                        <li><a class="dropdown-item" href="#">Published</a></li>
                                        <li><a class="dropdown-item" href="#">Drafts</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item" href="#">Recently Updated</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="list-group">
                                <?php foreach ($workspace['documents'] as $document): ?>
                                <a href="document_editor.php?id=<?php echo $document['id']; ?>" class="list-group-item list-group-item-action document-card <?php echo strtolower($document['status']); ?> mb-2">
                                    <div class="d-flex w-100 justify-content-between align-items-center">
                                        <div>
                                            <?php if($document['type'] == 'document'): ?>
                                            <i class="fas fa-file-alt document-icon text-primary"></i>
                                            <?php else: ?>
                                            <i class="fas fa-book document-icon text-success"></i>
                                            <?php endif; ?>
                                            <span class="h5 mb-0"><?php echo htmlspecialchars($document['name']); ?></span>
                                            <?php if($document['status'] == 'Draft'): ?>
                                            <span class="badge bg-warning ms-2">Draft</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="dropdown">
                                            <button class="btn btn-sm" type="button" id="docActions<?php echo $document['id']; ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <ul class="dropdown-menu" aria-labelledby="docActions<?php echo $document['id']; ?>">
                                                <li><a class="dropdown-item" href="#"><i class="fas fa-edit me-2"></i> Edit</a></li>
                                                <li><a class="dropdown-item" href="#"><i class="fas fa-share me-2"></i> Share</a></li>
                                                <li><a class="dropdown-item" href="#"><i class="fas fa-download me-2"></i> Download</a></li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li><a class="dropdown-item text-danger" href="#"><i class="fas fa-trash me-2"></i> Delete</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="mt-2 d-flex justify-content-between">
                                        <small class="text-muted">Created by <?php echo htmlspecialchars($document['created_by']); ?> on <?php echo $document['created_at']; ?></small>
                                        <small class="text-muted">Updated: <?php echo $document['updated_at']; ?></small>
                                    </div>
                                </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Sidebar Section -->
                <div class="col-lg-4">
                    <!-- Members Card -->
                    <div class="card shadow mb-4">
                        <div class="card-header custom-card-header d-flex justify-content-between align-items-center">
                            <h6 class="m-0 font-weight-bold text-primary">Members</h6>
                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#inviteMemberModal">
                                <i class="fas fa-user-plus"></i> Invite
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="d-flex mb-3">
                                <?php foreach ($workspace['members'] as $member): ?>
                                <img src="<?php echo $member['avatar']; ?>" alt="<?php echo htmlspecialchars($member['name']); ?>" class="member-avatar" data-bs-toggle="tooltip" title="<?php echo htmlspecialchars($member['name']); ?> (<?php echo $member['role']; ?>)">
                                <?php endforeach; ?>
                                <button class="btn btn-sm btn-light rounded-circle member-avatar d-flex align-items-center justify-content-center" data-bs-toggle="modal" data-bs-target="#inviteMemberModal">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                            
                            <ul class="list-group list-group-flush">
                                <?php foreach ($workspace['members'] as $member): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <div class="d-flex align-items-center">
                                        <img src="<?php echo $member['avatar']; ?>" alt="<?php echo htmlspecialchars($member['name']); ?>" class="member-avatar me-2">
                                        <div>
                                            <div><?php echo htmlspecialchars($member['name']); ?></div>
                                            <small class="text-muted"><?php echo $member['role']; ?></small>
                                        </div>
                                    </div>
                                    <?php if($member['id'] != 1): // Not the owner ?>
                                    <div class="dropdown">
                                        <button class="btn btn-sm" type="button" id="memberActions<?php echo $member['id']; ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="memberActions<?php echo $member['id']; ?>">
                                            <li><a class="dropdown-item" href="#"><i class="fas fa-user-edit me-2"></i> Change Role</a></li>
                                            <li><a class="dropdown-item text-danger" href="#"><i class="fas fa-user-minus me-2"></i> Remove</a></li>
                                        </ul>
                                    </div>
                                    <?php endif; ?>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- Recent Comments Card -->
                    <div class="card shadow mb-4">
                        <div class="card-header custom-card-header">
                            <h6 class="m-0 font-weight-bold text-primary">Recent Comments</h6>
                        </div>
                        <div class="card-body">
                            <?php foreach ($comments as $comment): ?>
                            <div class="comment-item">
                                <div class="d-flex justify-content-between">
                                    <strong><?php echo htmlspecialchars($comment['user']); ?></strong>
                                    <small class="text-muted"><?php echo $comment['time']; ?></small>
                                </div>
                                <div>
                                    on <a href="#" class="text-decoration-none"><?php echo htmlspecialchars($comment['document']); ?></a>
                                </div>
                                <div class="mt-2">
                                    <?php echo htmlspecialchars($comment['content']); ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- Workspace Info Card -->
                    <div class="card shadow">
                        <div class="card-header custom-card-header">
                            <h6 class="m-0 font-weight-bold text-primary">Workspace Info</h6>
                        </div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex justify-content-between px-0">
                                    <span>Created</span>
                                    <span><?php echo $workspace['created_at']; ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between px-0">
                                    <span>Last Updated</span>
                                    <span><?php echo $workspace['updated_at']; ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between px-0">
                                    <span>Members</span>
                                    <span><?php echo count($workspace['members']); ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between px-0">
                                    <span>Documents</span>
                                    <span><?php echo count($workspace['documents']); ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between px-0">
                                    <span>Owner</span>
                                    <span>Dr. Sarah Chen</span>
                                </li>
                            </ul>
                            <div class="d-grid gap-2 mt-3">
                                <button class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-download me-2"></i> Export Workspace
                                </button>
                                <button class="btn btn-outline-danger btn-sm">
                                    <i class="fas fa-trash me-2"></i> Delete Workspace
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Document Modal -->
<div class="modal fade" id="createDocumentModal" tabindex="-1" aria-labelledby="createDocumentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createDocumentModalLabel">Create New Document</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="mb-3">
                        <label for="documentName" class="form-label">Document Name</label>
                        <input type="text" class="form-control" id="documentName" placeholder="Enter document name">
                    </div>
                    <div class="mb-3">
                        <label for="documentType" class="form-label">Type</label>
                        <select class="form-select" id="documentType">
                            <option value="document">Document</option>
                            <option value="lesson">Lesson Plan</option>
                            <option value="assessment">Assessment</option>
                            <option value="resource">Resource</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="documentDescription" class="form-label">Description (Optional)</label>
                        <textarea class="form-control" id="documentDescription" rows="3" placeholder="Brief description of this document"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Template</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="documentTemplate" id="templateBlank" value="blank" checked>
                            <label class="form-check-label" for="templateBlank">
                                Blank Document
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="documentTemplate" id="templateLesson" value="lesson">
                            <label class="form-check-label" for="templateLesson">
                                Lesson Plan Template
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="documentTemplate" id="templateAI" value="ai">
                            <label class="form-check-label" for="templateAI">
                                AI-Generated (based on title)
                            </label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="window.location.href='document_editor.php'">Create Document</button>
            </div>
        </div>
    </div>
</div>

<!-- Invite Member Modal -->
<div class="modal fade" id="inviteMemberModal" tabindex="-1" aria-labelledby="inviteMemberModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="inviteMemberModalLabel">Invite Members</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="mb-3">
                        <label for="inviteEmail" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="inviteEmail" placeholder="Enter colleague's email">
                    </div>
                    <div class="mb-3">
                        <label for="inviteRole" class="form-label">Role</label>
                        <select class="form-select" id="inviteRole">
                            <option value="editor">Editor (can edit documents)</option>
                            <option value="viewer">Viewer (can only view)</option>
                            <option value="contributor">Contributor (can comment and suggest)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="inviteMessage" class="form-label">Personal Message (Optional)</label>
                        <textarea class="form-control" id="inviteMessage" rows="3" placeholder="Add a personal message to your invitation"></textarea>
                    </div>
                </form>
                
                <div class="mt-4">
                    <h6>Suggested Colleagues</h6>
                    <div class="list-group">
                        <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            Robert Anderson <span class="badge bg-primary rounded-pill">Science Dept.</span>
                        </button>
                        <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            Jennifer Lee <span class="badge bg-primary rounded-pill">Technology Dept.</span>
                        </button>
                        <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            Michael Thompson <span class="badge bg-primary rounded-pill">Mathematics Dept.</span>
                        </button>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary">Send Invitation</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    })
</script>
</body>
</html>