<?php
// Teacher Document Editor page
require_once(__DIR__ . '/../auth/session.php');
$current_user = auth_require();
$pageTitle = "Document Editor - iTeachXR";
$user = [
    'id' => 1,
    'full_name' => 'Dr. Sarah Chen',
    'email' => 'sarah.chen@example.edu',
    'role' => 'teacher'
];

// Get document ID from URL parameter (with validation)
$document_id = isset($_GET['id']) ? intval($_GET['id']) : 1;

// Sample document data (in a real app, this would come from a database)
$document = [
    'id' => $document_id,
    'name' => 'VR Headset Comparison Guide',
    'content' => '<h1>VR Headset Comparison Guide</h1>
<p>This guide provides a comprehensive comparison of VR headsets for educational use, focusing on features relevant to classroom implementation.</p>

<h2>Standalone VR Headsets</h2>
<h3>Meta Quest 3</h3>
<ul>
    <li><strong>Price Range:</strong> $499 - $650</li>
    <li><strong>Resolution:</strong> 2064 x 2208 per eye</li>
    <li><strong>Field of View:</strong> ~110 degrees</li>
    <li><strong>Weight:</strong> 515g</li>
    <li><strong>Battery Life:</strong> 2-3 hours</li>
    <li><strong>Educational Features:</strong>
        <ul>
            <li>Standalone operation (no PC required)</li>
            <li>Hand tracking for intuitive interaction</li>
            <li>Group management tools for classrooms</li>
            <li>Educational content library</li>
        </ul>
    </li>
    <li><strong>Pros:</strong> Portable, easy setup, growing educational content library</li>
    <li><strong>Cons:</strong> Battery life limitations, requires Meta account</li>
</ul>

<h3>Pico 4</h3>
<ul>
    <li><strong>Price Range:</strong> $429 - $499</li>
    <li><strong>Resolution:</strong> 2160 x 2160 per eye</li>
    <li><strong>Field of View:</strong> ~105 degrees</li>
    <li><strong>Weight:</strong> 586g</li>
    <li><strong>Battery Life:</strong> 2-3 hours</li>
    <li><strong>Educational Features:</strong>
        <ul>
            <li>Standalone operation</li>
            <li>Classroom management software</li>
            <li>Content filtering options</li>
        </ul>
    </li>
    <li><strong>Pros:</strong> Competitive price, good display quality</li>
    <li><strong>Cons:</strong> Smaller content library than Meta</li>
</ul>

<h2>PC-Connected VR Headsets</h2>
<h3>Valve Index</h3>
<ul>
    <li><strong>Price Range:</strong> $999 (full kit)</li>
    <li><strong>Resolution:</strong> 1440 x 1600 per eye</li>
    <li><strong>Field of View:</strong> ~130 degrees</li>
    <li><strong>Weight:</strong> 809g</li>
    <li><strong>Educational Features:</strong>
        <ul>
            <li>Superior tracking accuracy</li>
            <li>High-fidelity visuals for detailed simulations</li>
            <li>Advanced controller input</li>
        </ul>
    </li>
    <li><strong>Pros:</strong> Premium experience, wide field of view</li>
    <li><strong>Cons:</strong> Expensive, requires powerful PC, complex setup</li>
</ul>

<h3>HP Reverb G2</h3>
<ul>
    <li><strong>Price Range:</strong> $599</li>
    <li><strong>Resolution:</strong> 2160 x 2160 per eye</li>
    <li><strong>Field of View:</strong> ~114 degrees</li>
    <li><strong>Weight:</strong> 550g</li>
    <li><strong>Educational Features:</strong>
        <ul>
            <li>High resolution for detailed content</li>
            <li>Windows Mixed Reality integration</li>
            <li>Compatible with educational STEM software</li>
        </ul>
    </li>
    <li><strong>Pros:</strong> Excellent visual clarity, comfortable for extended use</li>
    <li><strong>Cons:</strong> Requires PC connection, tracking not as robust as other systems</li>
</ul>

<h2>Classroom Implementation Considerations</h2>
<ol>
    <li><strong>Budget Constraints:</strong> Consider total cost including headsets, supporting hardware, and content licensing</li>
    <li><strong>Technical Support:</strong> Ensure IT staff are trained to support the chosen platform</li>
    <li><strong>Content Availability:</strong> Verify educational content availability for your subject areas</li>
    <li><strong>Space Requirements:</strong> Assess classroom space for safe VR use</li>
    <li><strong>Hygiene Protocol:</strong> Develop cleaning procedures for shared headsets</li>
    <li><strong>Accessibility:</strong> Consider options for students with different abilities</li>
</ol>

<h2>Recommended Classroom Setups</h2>
<h3>Budget Option</h3>
<p>Meta Quest 3 (6 units) with shared classroom use and rotation system</p>

<h3>Premium Option</h3>
<p>HP Reverb G2 (12 units) connected to capable workstations for detailed scientific visualization</p>

<h3>Hybrid Approach</h3>
<p>Combination of standalone headsets for general use and PC-connected systems for specialized applications</p>',
    'workspace_id' => 1,
    'workspace_name' => 'VR Curriculum Planning',
    'type' => 'document',
    'created_by' => 'Dr. Sarah Chen',
    'created_at' => '2025-04-24',
    'updated_at' => '2025-04-28',
    'status' => 'Published',
    'version' => 3,
    'collaborators' => [
        ['id' => 1, 'name' => 'Dr. Sarah Chen', 'avatar' => 'https://via.placeholder.com/32'],
        ['id' => 2, 'name' => 'John Davis', 'avatar' => 'https://via.placeholder.com/32'],
        ['id' => 3, 'name' => 'Maria Rodriguez', 'avatar' => 'https://via.placeholder.com/32']
    ]
];

// Sample comments
$comments = [
    [
        'id' => 1,
        'user' => 'John Davis',
        'user_avatar' => 'https://via.placeholder.com/32',
        'content' => 'Should we add a section about the accessibility features of each headset?',
        'time' => '2 days ago',
        'replies' => [
            [
                'id' => 2,
                'user' => 'Dr. Sarah Chen',
                'user_avatar' => 'https://via.placeholder.com/32',
                'content' => 'Great idea, John. Let\'s add that in the next version.',
                'time' => '1 day ago'
            ]
        ]
    ],
    [
        'id' => 3,
        'user' => 'Maria Rodriguez',
        'user_avatar' => 'https://via.placeholder.com/32',
        'content' => 'I think we should highlight which headsets work best for younger students vs. older students.',
        'time' => '3 days ago',
        'replies' => []
    ]
];

// Document version history
$versions = [
    [
        'id' => 3,
        'version' => 3,
        'updated_by' => 'Dr. Sarah Chen',
        'updated_at' => '2025-04-28',
        'changes' => 'Added classroom implementation considerations section'
    ],
    [
        'id' => 2,
        'version' => 2,
        'updated_by' => 'John Davis',
        'updated_at' => '2025-04-26',
        'changes' => 'Added PC-connected headset comparisons'
    ],
    [
        'id' => 1,
        'version' => 1,
        'updated_by' => 'Dr. Sarah Chen',
        'updated_at' => '2025-04-24',
        'changes' => 'Initial document creation'
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
    <!-- Add Quill Rich Text Editor -->
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
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
        .document-sidebar {
            background-color: #f8f9fa;
            border-left: 1px solid #e3e6f0;
            height: calc(100vh - 70px);
            position: sticky;
            top: 70px;
            overflow-y: auto;
        }
        .editor-container {
            background-color: white;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            border-radius: 0.35rem;
        }
        .comment-card {
            border-left: 3px solid #4e73df;
            margin-bottom: 15px;
        }
        .reply-card {
            border-left: 3px solid #1cc88a;
            margin-left: 25px;
            margin-bottom: 10px;
        }
        .comment-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
        }
        .custom-card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #e3e6f0;
            padding: 15px 20px;
        }
        .collaborator-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            margin-right: -10px;
            border: 2px solid #fff;
        }
        #editor {
            height: 600px;
            font-size: 16px;
        }
        .ql-toolbar {
            border-top-left-radius: 0.35rem;
            border-top-right-radius: 0.35rem;
            background-color: #f8f9fa;
        }
        .ql-container {
            border-bottom-left-radius: 0.35rem;
            border-bottom-right-radius: 0.35rem;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .status-indicator {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 5px;
        }
        .status-indicator.published {
            background-color: #1cc88a;
        }
        .status-indicator.draft {
            background-color: #f6c23e;
        }
        .tab-content {
            height: calc(100vh - 220px);
            overflow-y: auto;
        }
        .sticky-top-bar {
            position: sticky;
            top: 0;
            z-index: 1000;
            background-color: white;
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
        <div class="col-md-10 px-0">
            <!-- Navbar -->
            <div class="sticky-top-bar">
                <?php include_once('../includes/teacher_navbar.php'); ?>
                
                <!-- Document Toolbar -->
                <div class="bg-white py-2 px-4 border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="collaborative_workspace.php">Workspaces</a></li>
                                <li class="breadcrumb-item"><a href="workspace_detail.php?id=<?php echo $document['workspace_id']; ?>"><?php echo htmlspecialchars($document['workspace_name']); ?></a></li>
                                <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($document['name']); ?></li>
                            </ol>
                        </nav>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <?php foreach ($document['collaborators'] as $collaborator): ?>
                            <img src="<?php echo $collaborator['avatar']; ?>" alt="<?php echo htmlspecialchars($collaborator['name']); ?>" class="collaborator-avatar" data-bs-toggle="tooltip" title="<?php echo htmlspecialchars($collaborator['name']); ?>">
                            <?php endforeach; ?>
                        </div>
                        <div class="dropdown me-2">
                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="shareDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-share-alt me-1"></i> Share
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="shareDropdown">
                                <li><a class="dropdown-item" href="#"><i class="fas fa-user-plus me-2"></i> Invite Collaborators</a></li>
                                <li><a class="dropdown-item" href="#"><i class="fas fa-link me-2"></i> Copy Link</a></li>
                                <li><a class="dropdown-item" href="#"><i class="fas fa-envelope me-2"></i> Email</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i> Sharing Settings</a></li>
                            </ul>
                        </div>
                        <div class="dropdown me-2">
                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="versionDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-history me-1"></i> Version <?php echo $document['version']; ?>
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="versionDropdown">
                                <?php foreach ($versions as $version): ?>
                                <li>
                                    <a class="dropdown-item" href="#">
                                        <div class="d-flex justify-content-between">
                                            <div>Version <?php echo $version['version']; ?> (<?php echo $version['updated_by']; ?>)</div>
                                            <small class="text-muted"><?php echo $version['updated_at']; ?></small>
                                        </div>
                                        <small class="text-muted"><?php echo $version['changes']; ?></small>
                                    </a>
                                </li>
                                <?php endforeach; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="#"><i class="fas fa-clock me-2"></i> View Version History</a></li>
                            </ul>
                        </div>
                        <div class="me-2">
                            <span class="badge <?php echo $document['status'] == 'Published' ? 'bg-success' : 'bg-warning'; ?>">
                                <?php echo $document['status']; ?>
                            </span>
                        </div>
                        <button class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Save
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="row m-0">
                <!-- Document Editor -->
                <div class="col-md-9 p-4">
                    <div class="editor-container mb-4">
                        <div id="editor-container">
                            <div id="editor"><?php echo $document['content']; ?></div>
                        </div>
                    </div>
                </div>
                
                <!-- Document Sidebar -->
                <div class="col-md-3 p-0 document-sidebar">
                    <div class="sticky-top pt-3">
                        <ul class="nav nav-tabs nav-fill" id="sidebarTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="comments-tab" data-bs-toggle="tab" data-bs-target="#comments" type="button" role="tab" aria-controls="comments" aria-selected="true">
                                    <i class="fas fa-comments me-1"></i> Comments
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="ai-tab" data-bs-toggle="tab" data-bs-target="#ai" type="button" role="tab" aria-controls="ai" aria-selected="false">
                                    <i class="fas fa-robot me-1"></i> AI Assistant
                                </button>
                            </li>
                        </ul>
                        <div class="tab-content p-3" id="sidebarTabContent">
                            <!-- Comments Tab -->
                            <div class="tab-pane fade show active" id="comments" role="tabpanel" aria-labelledby="comments-tab">
                                <div class="mb-3">
                                    <textarea class="form-control" rows="3" placeholder="Add a comment..."></textarea>
                                    <div class="d-flex justify-content-end mt-2">
                                        <button class="btn btn-primary btn-sm">
                                            <i class="fas fa-comment me-1"></i> Comment
                                        </button>
                                    </div>
                                </div>
                                
                                <div>
                                    <?php foreach ($comments as $comment): ?>
                                    <div class="card comment-card mb-3">
                                        <div class="card-body py-2">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <div class="d-flex align-items-center">
                                                    <img src="<?php echo $comment['user_avatar']; ?>" alt="<?php echo htmlspecialchars($comment['user']); ?>" class="comment-avatar me-2">
                                                    <strong><?php echo htmlspecialchars($comment['user']); ?></strong>
                                                </div>
                                                <small class="text-muted"><?php echo $comment['time']; ?></small>
                                            </div>
                                            <p class="mb-2"><?php echo htmlspecialchars($comment['content']); ?></p>
                                            <div class="d-flex">
                                                <button class="btn btn-sm btn-link p-0">Reply</button>
                                                <span class="mx-2">•</span>
                                                <button class="btn btn-sm btn-link p-0">Resolve</button>
                                            </div>
                                            
                                            <?php if(!empty($comment['replies'])): ?>
                                                <?php foreach ($comment['replies'] as $reply): ?>
                                                <div class="card reply-card mt-2">
                                                    <div class="card-body py-2">
                                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                                            <div class="d-flex align-items-center">
                                                                <img src="<?php echo $reply['user_avatar']; ?>" alt="<?php echo htmlspecialchars($reply['user']); ?>" class="comment-avatar me-2">
                                                                <strong><?php echo htmlspecialchars($reply['user']); ?></strong>
                                                            </div>
                                                            <small class="text-muted"><?php echo $reply['time']; ?></small>
                                                        </div>
                                                        <p class="mb-0"><?php echo htmlspecialchars($reply['content']); ?></p>
                                                    </div>
                                                </div>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            
                            <!-- AI Assistant Tab -->
                            <div class="tab-pane fade" id="ai" role="tabpanel" aria-labelledby="ai-tab">
                                <div class="d-flex justify-content-center mb-3">
                                    <div class="btn-group" role="group" aria-label="AI Actions">
                                        <button type="button" class="btn btn-outline-primary">Improve</button>
                                        <button type="button" class="btn btn-outline-primary">Summarize</button>
                                        <button type="button" class="btn btn-outline-primary">Check</button>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <textarea class="form-control" rows="3" placeholder="Ask AI for help with this document..."></textarea>
                                    <div class="d-flex justify-content-end mt-2">
                                        <button class="btn btn-primary btn-sm">
                                            <i class="fas fa-robot me-1"></i> Ask AI
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="card mb-3">
                                    <div class="card-header bg-light py-2">
                                        <h6 class="mb-0">AI Suggestions</h6>
                                    </div>
                                    <div class="card-body">
                                        <h6>Content Enhancement Ideas</h6>
                                        <ul class="mb-0">
                                            <li>Add a comparison table for quick reference</li>
                                            <li>Include information about software compatibility</li>
                                            <li>Add a section about future trends in educational VR</li>
                                        </ul>
                                    </div>
                                </div>
                                
                                <div class="card mb-3">
                                    <div class="card-header bg-light py-2">
                                        <h6 class="mb-0">Related Resources</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="fas fa-file-pdf text-danger me-2 fa-lg"></i>
                                            <div>
                                                <div>VR in Education Research Paper</div>
                                                <small class="text-muted">PDF - Workspace: Research Repository</small>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-file-alt text-primary me-2 fa-lg"></i>
                                            <div>
                                                <div>XR Technology Budget Proposal</div>
                                                <small class="text-muted">Document - Workspace: Administration</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
<script>
    // Initialize Quill editor
    var quill = new Quill('#editor', {
        theme: 'snow',
        modules: {
            toolbar: [
                [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'color': [] }, { 'background': [] }],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                [{ 'script': 'sub'}, { 'script': 'super' }],
                [{ 'indent': '-1'}, { 'indent': '+1' }],
                [{ 'align': [] }],
                ['link', 'image'],
                ['clean']
            ]
        },
        placeholder: 'Start writing your document...'
    });
    
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });
    
    // Autosave functionality
    var lastEdit = new Date();
    quill.on('text-change', function() {
        lastEdit = new Date();
        document.getElementById('autosaveStatus').innerText = 'Editing...';
        
        // Simulating autosave after 2 seconds of inactivity
        setTimeout(function() {
            var now = new Date();
            if ((now - lastEdit) >= 2000) {
                document.getElementById('autosaveStatus').innerText = 'All changes saved';
            }
        }, 2000);
    });
</script>
</body>
</html>