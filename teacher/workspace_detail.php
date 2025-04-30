<?php
// Include necessary files
require_once('../config.php');
require_once('../lib/db_connection.php');
require_once('../lib/moodlelib.php');

// Check if user is logged in
$user = check_user_auth();
if (!$user || $user['role'] !== 'teacher') {
    header('Location: ../index.php');
    exit;
}

// Check if workspace ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: collaborative_workspace.php');
    exit;
}

$workspace_id = (int)$_GET['id'];
$user_id = $user['id'];
$workspace = null;
$members = [];
$documents = [];
$resources = [];
$activities = [];
$user_role = null;

try {
    $db = get_db_connection();
    
    // Check if user is a member of this workspace
    $memberStmt = $db->prepare("
        SELECT role FROM workspace_members
        WHERE workspace_id = :workspace_id AND user_id = :user_id
    ");
    
    $memberStmt->execute([
        'workspace_id' => $workspace_id,
        'user_id' => $user_id
    ]);
    
    $user_role = $memberStmt->fetchColumn();
    
    if (!$user_role) {
        // Check if workspace is public
        $publicStmt = $db->prepare("
            SELECT is_public FROM collaborative_workspaces
            WHERE id = :workspace_id
        ");
        
        $publicStmt->execute(['workspace_id' => $workspace_id]);
        $is_public = $publicStmt->fetchColumn();
        
        if (!$is_public) {
            // User is not a member and workspace is not public
            header('Location: collaborative_workspace.php');
            exit;
        }
    }
    
    // Get workspace details
    $workspaceStmt = $db->prepare("
        SELECT cw.*, u.full_name as creator_name
        FROM collaborative_workspaces cw
        JOIN users u ON cw.created_by = u.id
        WHERE cw.id = :workspace_id
    ");
    
    $workspaceStmt->execute(['workspace_id' => $workspace_id]);
    $workspace = $workspaceStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$workspace) {
        header('Location: collaborative_workspace.php');
        exit;
    }
    
    // Get workspace members
    $membersStmt = $db->prepare("
        SELECT wm.user_id, wm.role, wm.joined_at, u.full_name, u.email, u.role as user_role
        FROM workspace_members wm
        JOIN users u ON wm.user_id = u.id
        WHERE wm.workspace_id = :workspace_id
        ORDER BY 
            CASE 
                WHEN wm.role = 'owner' THEN 1
                WHEN wm.role = 'admin' THEN 2
                ELSE 3
            END,
            u.full_name
    ");
    
    $membersStmt->execute(['workspace_id' => $workspace_id]);
    $members = $membersStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get workspace documents
    $documentsStmt = $db->prepare("
        SELECT wd.*, u.full_name as creator_name
        FROM workspace_documents wd
        JOIN users u ON wd.created_by = u.id
        WHERE wd.workspace_id = :workspace_id
        ORDER BY wd.updated_at DESC
    ");
    
    $documentsStmt->execute(['workspace_id' => $workspace_id]);
    $documents = $documentsStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get workspace resources
    $resourcesStmt = $db->prepare("
        SELECT wr.*, u.full_name as uploader_name
        FROM workspace_resources wr
        JOIN users u ON wr.uploaded_by = u.id
        WHERE wr.workspace_id = :workspace_id
        ORDER BY wr.created_at DESC
    ");
    
    $resourcesStmt->execute(['workspace_id' => $workspace_id]);
    $resources = $resourcesStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get workspace activities
    $activitiesStmt = $db->prepare("
        SELECT wa.*, u.full_name as user_name
        FROM workspace_activities wa
        JOIN users u ON wa.user_id = u.id
        WHERE wa.workspace_id = :workspace_id
        ORDER BY wa.created_at DESC
        LIMIT 20
    ");
    
    $activitiesStmt->execute(['workspace_id' => $workspace_id]);
    $activities = $activitiesStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Update user's last active time in this workspace
    if ($user_role) {
        $updateStmt = $db->prepare("
            UPDATE workspace_members
            SET last_active_at = CURRENT_TIMESTAMP
            WHERE workspace_id = :workspace_id AND user_id = :user_id
        ");
        
        $updateStmt->execute([
            'workspace_id' => $workspace_id,
            'user_id' => $user_id
        ]);
    }
} catch (Exception $e) {
    $error = "Error loading workspace: " . $e->getMessage();
}

// Handle document creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    // Check if user has permission to perform the action
    $can_edit = ($user_role === 'owner' || $user_role === 'admin' || $user_role === 'contributor');
    
    if ($can_edit) {
        try {
            $db = get_db_connection();
            
            // Create new document
            if ($_POST['action'] === 'create_document' && isset($_POST['title']) && !empty($_POST['title'])) {
                $title = $_POST['title'];
                $content = $_POST['content'] ?? '';
                $document_type = $_POST['document_type'] ?? 'lesson_plan';
                
                // Start a transaction
                $db->beginTransaction();
                
                // Insert document
                $stmt = $db->prepare("
                    INSERT INTO workspace_documents 
                    (workspace_id, title, content, created_by, document_type) 
                    VALUES (:workspace_id, :title, :content, :created_by, :document_type)
                    RETURNING id
                ");
                
                $stmt->execute([
                    'workspace_id' => $workspace_id,
                    'title' => $title,
                    'content' => $content,
                    'created_by' => $user_id,
                    'document_type' => $document_type
                ]);
                
                $document_id = $stmt->fetchColumn();
                
                // Create initial version
                $versionStmt = $db->prepare("
                    INSERT INTO document_versions 
                    (document_id, version_number, content, modified_by, change_summary) 
                    VALUES (:document_id, 1, :content, :modified_by, 'Initial version')
                ");
                
                $versionStmt->execute([
                    'document_id' => $document_id,
                    'content' => $content,
                    'modified_by' => $user_id
                ]);
                
                // Log activity
                $activityStmt = $db->prepare("
                    INSERT INTO workspace_activities 
                    (workspace_id, user_id, activity_type, activity_data, target_id, target_type) 
                    VALUES (:workspace_id, :user_id, 'add_document', :activity_data, :target_id, 'document')
                ");
                
                $activityStmt->execute([
                    'workspace_id' => $workspace_id,
                    'user_id' => $user_id,
                    'activity_data' => json_encode(['document_title' => $title]),
                    'target_id' => $document_id
                ]);
                
                // Commit the transaction
                $db->commit();
                
                // Redirect to document editor
                header("Location: document_editor.php?id=$document_id");
                exit;
            }
            
            // Add workspace member
            else if ($_POST['action'] === 'add_member' && isset($_POST['email']) && !empty($_POST['email'])) {
                $email = $_POST['email'];
                $role = $_POST['role'] ?? 'contributor';
                
                // Check if user exists
                $userStmt = $db->prepare("
                    SELECT id, full_name FROM users
                    WHERE email = :email AND role = 'teacher'
                ");
                
                $userStmt->execute(['email' => $email]);
                $new_member = $userStmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$new_member) {
                    $error = "Teacher with email $email not found";
                } else {
                    $new_member_id = $new_member['id'];
                    
                    // Check if user is already a member
                    $checkStmt = $db->prepare("
                        SELECT 1 FROM workspace_members
                        WHERE workspace_id = :workspace_id AND user_id = :user_id
                    ");
                    
                    $checkStmt->execute([
                        'workspace_id' => $workspace_id,
                        'user_id' => $new_member_id
                    ]);
                    
                    if ($checkStmt->fetchColumn()) {
                        $error = "User is already a member of this workspace";
                    } else {
                        // Start a transaction
                        $db->beginTransaction();
                        
                        // Add the member
                        $addStmt = $db->prepare("
                            INSERT INTO workspace_members
                            (workspace_id, user_id, role)
                            VALUES (:workspace_id, :user_id, :role)
                        ");
                        
                        $addStmt->execute([
                            'workspace_id' => $workspace_id,
                            'user_id' => $new_member_id,
                            'role' => $role
                        ]);
                        
                        // Log activity
                        $activityStmt = $db->prepare("
                            INSERT INTO workspace_activities
                            (workspace_id, user_id, activity_type, activity_data, target_id, target_type)
                            VALUES (:workspace_id, :user_id, 'add_member', :activity_data, :target_id, 'user')
                        ");
                        
                        $activityStmt->execute([
                            'workspace_id' => $workspace_id,
                            'user_id' => $user_id,
                            'activity_data' => json_encode([
                                'member_id' => $new_member_id,
                                'member_name' => $new_member['full_name'],
                                'role' => $role
                            ]),
                            'target_id' => $new_member_id
                        ]);
                        
                        // Commit the transaction
                        $db->commit();
                        
                        // Refresh the page
                        header("Location: workspace_detail.php?id=$workspace_id&success=1");
                        exit;
                    }
                }
            }
            
            // Upload resource
            else if ($_POST['action'] === 'upload_resource') {
                // Handle resource upload
                // Implementation for file uploads would go here
                // This would involve file validation, storage, and database updating
                // For now, we'll implement a simpler version without file uploads
                
                if (isset($_POST['title']) && !empty($_POST['title'])) {
                    $title = $_POST['title'];
                    $description = $_POST['description'] ?? '';
                    $resource_type = $_POST['resource_type'] ?? 'link';
                    $resource_url = $_POST['resource_url'] ?? '';
                    
                    // Insert resource
                    $stmt = $db->prepare("
                        INSERT INTO workspace_resources
                        (workspace_id, title, description, resource_type, resource_url, uploaded_by)
                        VALUES (:workspace_id, :title, :description, :resource_type, :resource_url, :uploaded_by)
                        RETURNING id
                    ");
                    
                    $stmt->execute([
                        'workspace_id' => $workspace_id,
                        'title' => $title,
                        'description' => $description,
                        'resource_type' => $resource_type,
                        'resource_url' => $resource_url,
                        'uploaded_by' => $user_id
                    ]);
                    
                    $resource_id = $stmt->fetchColumn();
                    
                    // Log activity
                    $activityStmt = $db->prepare("
                        INSERT INTO workspace_activities
                        (workspace_id, user_id, activity_type, activity_data, target_id, target_type)
                        VALUES (:workspace_id, :user_id, 'add_resource', :activity_data, :target_id, 'resource')
                    ");
                    
                    $activityStmt->execute([
                        'workspace_id' => $workspace_id,
                        'user_id' => $user_id,
                        'activity_data' => json_encode(['resource_title' => $title, 'resource_type' => $resource_type]),
                        'target_id' => $resource_id
                    ]);
                    
                    // Refresh the page
                    header("Location: workspace_detail.php?id=$workspace_id&success=1");
                    exit;
                }
            }
        } catch (Exception $e) {
            if (isset($db) && $db->inTransaction()) {
                $db->rollBack();
            }
            $error = "Error: " . $e->getMessage();
        }
    } else {
        $error = "You don't have permission to perform this action";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($workspace['title'] ?? 'Workspace'); ?> - iTeachXR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <style>
        .nav-tabs .nav-link {
            color: #495057;
        }
        .nav-tabs .nav-link.active {
            color: #0d6efd;
            font-weight: 500;
        }
        .activity-item {
            border-left: 3px solid #0d6efd;
            padding: 10px 15px;
            margin-bottom: 10px;
            background-color: #f8f9fa;
        }
        .resource-icon {
            font-size: 24px;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f1f1f1;
            border-radius: 5px;
        }
        .document-card {
            transition: transform 0.2s ease;
        }
        .document-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        .float-action-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 1000;
        }
        .member-badge {
            width: 40px;
            height: 40px;
            background-color: #0d6efd;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <?php include('../includes/teacher_navbar.php'); ?>
    
    <!-- Main Content -->
    <div class="container-fluid mt-3 mb-5">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
                <?php include('../includes/teacher_sidebar.php'); ?>
            </div>
            
            <!-- Main Area -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <!-- Workspace Header -->
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4">
                    <div>
                        <h1 class="h2 mb-0"><?php echo htmlspecialchars($workspace['title'] ?? 'Workspace'); ?></h1>
                        <p class="text-muted mb-0">
                            Created by <?php echo htmlspecialchars($workspace['creator_name'] ?? 'Unknown'); ?> · 
                            <?php echo date('F j, Y', strtotime($workspace['created_at'])); ?> · 
                            <span class="badge <?php echo $workspace['is_public'] ? 'bg-success' : 'bg-secondary'; ?>">
                                <?php echo $workspace['is_public'] ? 'Public' : 'Private'; ?>
                            </span>
                        </p>
                    </div>
                    
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <?php if ($user_role === 'owner' || $user_role === 'admin'): ?>
                            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#inviteMemberModal">
                                <i class="fas fa-user-plus me-1"></i> Invite
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editWorkspaceModal">
                                <i class="fas fa-edit me-1"></i> Edit
                            </button>
                            <?php endif; ?>
                            <a href="collaborative_workspace.php" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-th-large me-1"></i> All Workspaces
                            </a>
                        </div>
                    </div>
                </div>
                
                <?php if (isset($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                
                <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    Action completed successfully!
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                
                <!-- Description -->
                <?php if (!empty($workspace['description'])): ?>
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Description</h5>
                        <p class="card-text"><?php echo nl2br(htmlspecialchars($workspace['description'])); ?></p>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Tabs -->
                <ul class="nav nav-tabs mb-4" id="workspaceTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="documents-tab" data-bs-toggle="tab" data-bs-target="#documents" type="button" role="tab" aria-controls="documents" aria-selected="true">
                            <i class="fas fa-file-alt me-2"></i> Documents
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="resources-tab" data-bs-toggle="tab" data-bs-target="#resources" type="button" role="tab" aria-controls="resources" aria-selected="false">
                            <i class="fas fa-link me-2"></i> Resources
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="members-tab" data-bs-toggle="tab" data-bs-target="#members" type="button" role="tab" aria-controls="members" aria-selected="false">
                            <i class="fas fa-users me-2"></i> Members <span class="badge bg-secondary"><?php echo count($members); ?></span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="activity-tab" data-bs-toggle="tab" data-bs-target="#activity" type="button" role="tab" aria-controls="activity" aria-selected="false">
                            <i class="fas fa-history me-2"></i> Activity
                        </button>
                    </li>
                </ul>
                
                <!-- Tab Content -->
                <div class="tab-content" id="workspaceTabContent">
                    <!-- Documents Tab -->
                    <div class="tab-pane fade show active" id="documents" role="tabpanel" aria-labelledby="documents-tab">
                        <?php if (empty($documents)): ?>
                        <div class="text-center py-5">
                            <div class="display-1 text-muted mb-3">
                                <i class="fas fa-file-alt"></i>
                            </div>
                            <h3 class="mb-3">No Documents Yet</h3>
                            <p class="mb-4">Create your first document to start collaborating.</p>
                            <?php if ($user_role && $user_role !== 'viewer'): ?>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createDocumentModal">
                                <i class="fas fa-plus me-2"></i> Create Document
                            </button>
                            <?php endif; ?>
                        </div>
                        <?php else: ?>
                        <div class="d-flex justify-content-between mb-3">
                            <h4>Documents</h4>
                            <?php if ($user_role && $user_role !== 'viewer'): ?>
                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#createDocumentModal">
                                <i class="fas fa-plus me-1"></i> New Document
                            </button>
                            <?php endif; ?>
                        </div>
                        
                        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                            <?php foreach ($documents as $document): ?>
                            <div class="col">
                                <div class="card h-100 document-card">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between mb-2">
                                            <h5 class="card-title"><?php echo htmlspecialchars($document['title']); ?></h5>
                                            <span class="badge <?php echo $document['status'] === 'published' ? 'bg-success' : 'bg-warning text-dark'; ?>">
                                                <?php echo ucfirst($document['status']); ?>
                                            </span>
                                        </div>
                                        
                                        <p class="card-text small">
                                            <?php 
                                            $content = strip_tags($document['content'] ?? '');
                                            echo !empty($content) 
                                                ? htmlspecialchars(substr($content, 0, 100)) . (strlen($content) > 100 ? '...' : '')
                                                : '<em>No content</em>';
                                            ?>
                                        </p>
                                        
                                        <div class="d-flex justify-content-between mt-3">
                                            <div class="text-muted small">
                                                <i class="fas fa-clock me-1"></i> 
                                                Updated <?php echo time_ago($document['updated_at']); ?>
                                            </div>
                                            <div class="text-muted small">
                                                v<?php echo $document['version']; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-footer bg-white">
                                        <a href="document_editor.php?id=<?php echo $document['id']; ?>" class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-edit me-1"></i> Open
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Resources Tab -->
                    <div class="tab-pane fade" id="resources" role="tabpanel" aria-labelledby="resources-tab">
                        <?php if (empty($resources)): ?>
                        <div class="text-center py-5">
                            <div class="display-1 text-muted mb-3">
                                <i class="fas fa-link"></i>
                            </div>
                            <h3 class="mb-3">No Resources Yet</h3>
                            <p class="mb-4">Add links or files to share with workspace members.</p>
                            <?php if ($user_role && $user_role !== 'viewer'): ?>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addResourceModal">
                                <i class="fas fa-plus me-2"></i> Add Resource
                            </button>
                            <?php endif; ?>
                        </div>
                        <?php else: ?>
                        <div class="d-flex justify-content-between mb-3">
                            <h4>Resources</h4>
                            <?php if ($user_role && $user_role !== 'viewer'): ?>
                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addResourceModal">
                                <i class="fas fa-plus me-1"></i> Add Resource
                            </button>
                            <?php endif; ?>
                        </div>
                        
                        <div class="list-group">
                            <?php foreach ($resources as $resource): ?>
                            <div class="list-group-item list-group-item-action">
                                <div class="d-flex">
                                    <div class="resource-icon me-3">
                                        <?php
                                        $icon = 'fa-link';
                                        switch ($resource['resource_type']) {
                                            case 'document':
                                                $icon = 'fa-file-alt';
                                                break;
                                            case 'image':
                                                $icon = 'fa-image';
                                                break;
                                            case 'video':
                                                $icon = 'fa-video';
                                                break;
                                            case 'audio':
                                                $icon = 'fa-volume-up';
                                                break;
                                            case 'presentation':
                                                $icon = 'fa-file-powerpoint';
                                                break;
                                            case 'spreadsheet':
                                                $icon = 'fa-file-excel';
                                                break;
                                        }
                                        ?>
                                        <i class="fas <?php echo $icon; ?>"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h5 class="mb-1"><?php echo htmlspecialchars($resource['title']); ?></h5>
                                            <small><?php echo time_ago($resource['created_at']); ?></small>
                                        </div>
                                        
                                        <?php if (!empty($resource['description'])): ?>
                                        <p class="mb-1"><?php echo htmlspecialchars($resource['description']); ?></p>
                                        <?php endif; ?>
                                        
                                        <div class="d-flex mt-2 justify-content-between">
                                            <small class="text-muted">
                                                Added by <?php echo htmlspecialchars($resource['uploader_name']); ?>
                                            </small>
                                            
                                            <?php if (!empty($resource['resource_url'])): ?>
                                            <a href="<?php echo htmlspecialchars($resource['resource_url']); ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-external-link-alt me-1"></i> Open
                                            </a>
                                            <?php elseif (!empty($resource['file_path'])): ?>
                                            <a href="<?php echo htmlspecialchars($resource['file_path']); ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-download me-1"></i> Download
                                            </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Members Tab -->
                    <div class="tab-pane fade" id="members" role="tabpanel" aria-labelledby="members-tab">
                        <div class="d-flex justify-content-between mb-3">
                            <h4>Members</h4>
                            <?php if ($user_role === 'owner' || $user_role === 'admin'): ?>
                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#inviteMemberModal">
                                <i class="fas fa-user-plus me-1"></i> Invite Member
                            </button>
                            <?php endif; ?>
                        </div>
                        
                        <div class="list-group">
                            <?php foreach ($members as $member): ?>
                            <div class="list-group-item">
                                <div class="d-flex align-items-center">
                                    <div class="member-badge me-3">
                                        <?php
                                        $initials = '';
                                        $name_parts = explode(' ', $member['full_name']);
                                        if (count($name_parts) >= 2) {
                                            $initials = substr($name_parts[0], 0, 1) . substr($name_parts[count($name_parts)-1], 0, 1);
                                        } else {
                                            $initials = substr($member['full_name'], 0, 2);
                                        }
                                        echo strtoupper($initials);
                                        ?>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h5 class="mb-1">
                                                <?php echo htmlspecialchars($member['full_name']); ?>
                                                <?php if ($member['user_id'] == $user_id): ?>
                                                <span class="badge bg-info text-white">You</span>
                                                <?php endif; ?>
                                            </h5>
                                            <div>
                                                <span class="badge <?php 
                                                    echo $member['role'] === 'owner' ? 'bg-danger' : 
                                                        ($member['role'] === 'admin' ? 'bg-warning text-dark' : 
                                                        ($member['role'] === 'contributor' ? 'bg-primary' : 'bg-secondary')); 
                                                ?>">
                                                    <?php echo ucfirst($member['role']); ?>
                                                </span>
                                            </div>
                                        </div>
                                        <p class="mb-1 text-muted"><?php echo htmlspecialchars($member['email']); ?></p>
                                        <small>
                                            Joined <?php echo date('M j, Y', strtotime($member['joined_at'])); ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- Activity Tab -->
                    <div class="tab-pane fade" id="activity" role="tabpanel" aria-labelledby="activity-tab">
                        <h4 class="mb-3">Recent Activity</h4>
                        
                        <?php if (empty($activities)): ?>
                        <div class="text-center py-4">
                            <p class="text-muted">No activity recorded yet</p>
                        </div>
                        <?php else: ?>
                        <div class="timeline">
                            <?php 
                            $current_date = null;
                            foreach ($activities as $activity): 
                                $activity_date = date('Y-m-d', strtotime($activity['created_at']));
                                if ($activity_date !== $current_date):
                                    $current_date = $activity_date;
                            ?>
                            <h6 class="mt-4 mb-3 border-bottom pb-2">
                                <?php 
                                if ($activity_date === date('Y-m-d')) {
                                    echo 'Today';
                                } else if ($activity_date === date('Y-m-d', strtotime('-1 day'))) {
                                    echo 'Yesterday';
                                } else {
                                    echo date('F j, Y', strtotime($activity['created_at']));
                                }
                                ?>
                            </h6>
                            <?php endif; ?>
                            
                            <div class="activity-item">
                                <div class="d-flex justify-content-between">
                                    <h6 class="mb-0">
                                        <?php echo htmlspecialchars($activity['user_name']); ?>
                                        <?php if ($activity['user_id'] == $user_id): ?>
                                        <span class="badge bg-light text-dark">You</span>
                                        <?php endif; ?>
                                    </h6>
                                    <small class="text-muted"><?php echo date('g:i A', strtotime($activity['created_at'])); ?></small>
                                </div>
                                
                                <p class="mb-0">
                                    <?php 
                                    $activity_text = '';
                                    $data = json_decode($activity['activity_data'] ?? '{}', true);
                                    
                                    switch ($activity['activity_type']) {
                                        case 'create_workspace':
                                            $activity_text = 'created this workspace';
                                            break;
                                        case 'update_workspace':
                                            $activity_text = 'updated workspace details';
                                            break;
                                        case 'add_document':
                                            $doc_title = $data['document_title'] ?? 'Untitled';
                                            $activity_text = "added document \"$doc_title\"";
                                            break;
                                        case 'update_document':
                                            $doc_title = $data['document_title'] ?? 'Untitled';
                                            $activity_text = "updated document \"$doc_title\"";
                                            break;
                                        case 'add_member':
                                            $member_name = $data['member_name'] ?? 'someone';
                                            $role = ucfirst($data['role'] ?? 'contributor');
                                            $activity_text = "added $member_name as a $role";
                                            break;
                                        case 'add_resource':
                                            $resource_title = $data['resource_title'] ?? 'Untitled';
                                            $resource_type = ucfirst($data['resource_type'] ?? 'resource');
                                            $activity_text = "added $resource_type \"$resource_title\"";
                                            break;
                                        case 'add_comment':
                                            $doc_title = $data['document_title'] ?? 'a document';
                                            $activity_text = "commented on \"$doc_title\"";
                                            break;
                                        default:
                                            $activity_text = 'performed an action';
                                    }
                                    
                                    echo $activity_text;
                                    ?>
                                </p>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <!-- Create Document Modal -->
    <div class="modal fade" id="createDocumentModal" tabindex="-1" aria-labelledby="createDocumentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="post" action="">
                    <input type="hidden" name="action" value="create_document">
                    <div class="modal-header">
                        <h5 class="modal-title" id="createDocumentModalLabel">Create New Document</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="document-title" class="form-label">Document Title</label>
                            <input type="text" class="form-control" id="document-title" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="document-type" class="form-label">Document Type</label>
                            <select class="form-select" id="document-type" name="document_type">
                                <option value="lesson_plan">Lesson Plan</option>
                                <option value="unit_plan">Unit Plan</option>
                                <option value="curriculum">Curriculum</option>
                                <option value="assessment">Assessment</option>
                                <option value="notes">Notes</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="document-content" class="form-label">Initial Content (Optional)</label>
                            <textarea class="form-control" id="document-content" name="content" rows="5"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create Document</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Add Resource Modal -->
    <div class="modal fade" id="addResourceModal" tabindex="-1" aria-labelledby="addResourceModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="post" action="">
                    <input type="hidden" name="action" value="upload_resource">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addResourceModalLabel">Add Resource</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="resource-title" class="form-label">Resource Title</label>
                            <input type="text" class="form-control" id="resource-title" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="resource-description" class="form-label">Description (Optional)</label>
                            <textarea class="form-control" id="resource-description" name="description" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="resource-type" class="form-label">Resource Type</label>
                            <select class="form-select" id="resource-type" name="resource_type">
                                <option value="link">Link</option>
                                <option value="document">Document</option>
                                <option value="image">Image</option>
                                <option value="video">Video</option>
                                <option value="audio">Audio</option>
                                <option value="presentation">Presentation</option>
                                <option value="spreadsheet">Spreadsheet</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="resource-url" class="form-label">Resource URL</label>
                            <input type="url" class="form-control" id="resource-url" name="resource_url" placeholder="https://...">
                            <div class="form-text">Enter a URL or upload a file below.</div>
                        </div>
                        <div class="mb-3">
                            <label for="resource-file" class="form-label">File Upload</label>
                            <input type="file" class="form-control" id="resource-file" name="resource_file" disabled>
                            <div class="form-text text-muted">File upload functionality coming soon.</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Resource</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Invite Member Modal -->
    <div class="modal fade" id="inviteMemberModal" tabindex="-1" aria-labelledby="inviteMemberModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="post" action="">
                    <input type="hidden" name="action" value="add_member">
                    <div class="modal-header">
                        <h5 class="modal-title" id="inviteMemberModalLabel">Invite Member</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="member-email" class="form-label">Teacher Email</label>
                            <input type="email" class="form-control" id="member-email" name="email" required>
                            <div class="form-text">Enter the email of a teacher registered in the system.</div>
                        </div>
                        <div class="mb-3">
                            <label for="member-role" class="form-label">Role</label>
                            <select class="form-select" id="member-role" name="role">
                                <option value="contributor">Contributor</option>
                                <option value="admin">Admin</option>
                                <option value="viewer">Viewer</option>
                            </select>
                            <div class="form-text">
                                <strong>Contributor:</strong> Can create and edit documents<br>
                                <strong>Admin:</strong> Can manage workspace settings and members<br>
                                <strong>Viewer:</strong> Can only view documents, cannot edit
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Invite</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Floating Action Button for mobile -->
    <div class="d-block d-md-none">
        <div class="btn-group dropup float-action-btn">
            <button type="button" class="btn btn-primary btn-lg rounded-circle" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-plus"></i>
            </button>
            <ul class="dropdown-menu">
                <?php if ($user_role && $user_role !== 'viewer'): ?>
                <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#createDocumentModal">
                    <i class="fas fa-file-alt me-2"></i> New Document
                </a></li>
                <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#addResourceModal">
                    <i class="fas fa-link me-2"></i> Add Resource
                </a></li>
                <?php endif; ?>
                <?php if ($user_role === 'owner' || $user_role === 'admin'): ?>
                <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#inviteMemberModal">
                    <i class="fas fa-user-plus me-2"></i> Invite Member
                </a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Get the hash from the URL (if any)
        var hash = window.location.hash;
        if (hash) {
            // If there's a hash, find the tab with that ID and activate it
            var tab = document.querySelector('[data-bs-target="' + hash + '"]');
            if (tab) {
                var tabTrigger = new bootstrap.Tab(tab);
                tabTrigger.show();
            }
        }
        
        // Add click event listener to tab links to update the URL hash
        document.querySelectorAll('[data-bs-toggle="tab"]').forEach(function(tab) {
            tab.addEventListener('shown.bs.tab', function(e) {
                // Update the URL hash when a tab is clicked
                var hash = e.target.getAttribute('data-bs-target');
                if (history.pushState) {
                    history.pushState(null, null, hash);
                } else {
                    location.hash = hash;
                }
            });
        });
    });
    </script>
</body>
</html>

<?php
// Helper function to format time ago
function time_ago($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;

    $string = array(
        'y' => 'year',
        'm' => 'month',
        'w' => 'week',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    );
    
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }

    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' ago' : 'just now';
}
?>