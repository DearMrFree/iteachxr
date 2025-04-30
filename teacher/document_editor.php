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

// Check if document ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: collaborative_workspace.php');
    exit;
}

$document_id = (int)$_GET['id'];
$user_id = $user['id'];
$document = null;
$workspace = null;
$versions = [];
$comments = [];
$user_role = null;
$can_edit = false;

try {
    $db = get_db_connection();
    
    // Get document details
    $docStmt = $db->prepare("
        SELECT wd.*, u.full_name as creator_name, cw.id as workspace_id, cw.title as workspace_title
        FROM workspace_documents wd
        JOIN users u ON wd.created_by = u.id
        JOIN collaborative_workspaces cw ON wd.workspace_id = cw.id
        WHERE wd.id = :document_id
    ");
    
    $docStmt->execute(['document_id' => $document_id]);
    $document = $docStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$document) {
        header('Location: collaborative_workspace.php');
        exit;
    }
    
    $workspace_id = $document['workspace_id'];
    
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
        
        // For public workspaces, non-members are viewers
        $user_role = 'viewer';
    }
    
    // Determine if user can edit
    $can_edit = ($user_role === 'owner' || $user_role === 'admin' || $user_role === 'contributor');
    
    // Get document versions
    $versionsStmt = $db->prepare("
        SELECT dv.*, u.full_name as modifier_name
        FROM document_versions dv
        JOIN users u ON dv.modified_by = u.id
        WHERE dv.document_id = :document_id
        ORDER BY dv.version_number DESC
        LIMIT 10
    ");
    
    $versionsStmt->execute(['document_id' => $document_id]);
    $versions = $versionsStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get document comments
    $commentsStmt = $db->prepare("
        SELECT dc.*, u.full_name as user_name
        FROM document_comments dc
        JOIN users u ON dc.user_id = u.id
        WHERE dc.document_id = :document_id AND dc.parent_id IS NULL
        ORDER BY dc.created_at DESC
    ");
    
    $commentsStmt->execute(['document_id' => $document_id]);
    $comments = $commentsStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get replies for each comment
    foreach ($comments as &$comment) {
        $repliesStmt = $db->prepare("
            SELECT dc.*, u.full_name as user_name
            FROM document_comments dc
            JOIN users u ON dc.user_id = u.id
            WHERE dc.parent_id = :comment_id
            ORDER BY dc.created_at ASC
        ");
        
        $repliesStmt->execute(['comment_id' => $comment['id']]);
        $comment['replies'] = $repliesStmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
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
    $error = "Error loading document: " . $e->getMessage();
}

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!$can_edit && $_POST['action'] !== 'add_comment') {
        $error = "You don't have permission to edit this document";
    } else {
        try {
            $db = get_db_connection();
            
            // Save document changes
            if ($_POST['action'] === 'save_document' && isset($_POST['content'])) {
                $content = $_POST['content'];
                $title = $_POST['title'] ?? $document['title'];
                $status = $_POST['status'] ?? $document['status'];
                $change_summary = $_POST['change_summary'] ?? 'Updated document';
                
                // Start a transaction
                $db->beginTransaction();
                
                // Update the document
                $updateStmt = $db->prepare("
                    UPDATE workspace_documents
                    SET title = :title, content = :content, status = :status, 
                        updated_at = CURRENT_TIMESTAMP, version = version + 1
                    WHERE id = :document_id
                    RETURNING version
                ");
                
                $updateStmt->execute([
                    'title' => $title,
                    'content' => $content,
                    'status' => $status,
                    'document_id' => $document_id
                ]);
                
                $new_version = $updateStmt->fetchColumn();
                
                // Create a new version record
                $versionStmt = $db->prepare("
                    INSERT INTO document_versions
                    (document_id, version_number, content, modified_by, change_summary)
                    VALUES (:document_id, :version_number, :content, :modified_by, :change_summary)
                ");
                
                $versionStmt->execute([
                    'document_id' => $document_id,
                    'version_number' => $new_version,
                    'content' => $content,
                    'modified_by' => $user_id,
                    'change_summary' => $change_summary
                ]);
                
                // Log activity
                $activityStmt = $db->prepare("
                    INSERT INTO workspace_activities
                    (workspace_id, user_id, activity_type, activity_data, target_id, target_type)
                    VALUES (:workspace_id, :user_id, 'update_document', :activity_data, :target_id, 'document')
                ");
                
                $activityStmt->execute([
                    'workspace_id' => $workspace_id,
                    'user_id' => $user_id,
                    'activity_data' => json_encode([
                        'document_title' => $title,
                        'version' => $new_version
                    ]),
                    'target_id' => $document_id
                ]);
                
                // Commit the transaction
                $db->commit();
                
                // Update the document in our local variable
                $document['title'] = $title;
                $document['content'] = $content;
                $document['status'] = $status;
                $document['version'] = $new_version;
                
                $success = "Document saved successfully";
            }
            
            // Add comment
            else if ($_POST['action'] === 'add_comment' && isset($_POST['comment_text']) && !empty($_POST['comment_text'])) {
                $comment_text = $_POST['comment_text'];
                $parent_id = isset($_POST['parent_id']) && !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;
                
                // Insert comment
                $stmt = $db->prepare("
                    INSERT INTO document_comments
                    (document_id, user_id, comment_text, parent_id)
                    VALUES (:document_id, :user_id, :comment_text, :parent_id)
                    RETURNING id
                ");
                
                $stmt->execute([
                    'document_id' => $document_id,
                    'user_id' => $user_id,
                    'comment_text' => $comment_text,
                    'parent_id' => $parent_id
                ]);
                
                $comment_id = $stmt->fetchColumn();
                
                // Log activity
                $activityStmt = $db->prepare("
                    INSERT INTO workspace_activities
                    (workspace_id, user_id, activity_type, activity_data, target_id, target_type)
                    VALUES (:workspace_id, :user_id, 'add_comment', :activity_data, :target_id, 'comment')
                ");
                
                $activityStmt->execute([
                    'workspace_id' => $workspace_id,
                    'user_id' => $user_id,
                    'activity_data' => json_encode([
                        'document_title' => $document['title'],
                        'document_id' => $document_id,
                        'comment_id' => $comment_id,
                        'is_reply' => $parent_id ? true : false
                    ]),
                    'target_id' => $comment_id
                ]);
                
                // Refresh the page to show the new comment
                header("Location: document_editor.php?id=$document_id&success=1#comments");
                exit;
            }
        } catch (Exception $e) {
            if (isset($db) && $db->inTransaction()) {
                $db->rollBack();
            }
            $error = "Error: " . $e->getMessage();
        }
    }
}

// Function to format time ago
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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($document['title'] ?? 'Document'); ?> - iTeachXR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <style>
        .editor-container {
            height: calc(100vh - 250px);
            min-height: 400px;
        }
        #editor {
            height: 100%;
            overflow-y: auto;
        }
        .ql-editor {
            min-height: 100%;
        }
        .comment-container {
            border-left: 3px solid #0d6efd;
            padding-left: 15px;
            margin-bottom: 15px;
        }
        .comment-reply {
            margin-left: 30px;
            border-left: 2px solid #6c757d;
            padding-left: 15px;
        }
        .document-info {
            position: sticky;
            top: 20px;
        }
        .editor-toolbar {
            position: sticky;
            top: 0;
            z-index: 100;
            background-color: #fff;
            border-bottom: 1px solid #dee2e6;
            padding: 10px 0;
        }
        .timeline-item {
            position: relative;
            padding-left: 45px;
            margin-bottom: 20px;
        }
        .timeline-marker {
            position: absolute;
            left: 0;
            top: 0;
            width: 30px;
            height: 30px;
            background-color: #0d6efd;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
        }
        .timeline-item:not(:last-child)::after {
            content: '';
            position: absolute;
            left: 15px;
            top: 30px;
            bottom: -10px;
            width: 2px;
            background-color: #dee2e6;
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
                <!-- Document Header -->
                <div class="editor-toolbar mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <a href="workspace_detail.php?id=<?php echo $workspace_id; ?>" class="btn btn-sm btn-outline-secondary me-2">
                                <i class="fas fa-arrow-left"></i>
                            </a>
                            
                            <?php if ($can_edit): ?>
                            <input type="text" id="document-title" class="form-control form-control-lg border-0 fw-bold" 
                                value="<?php echo htmlspecialchars($document['title']); ?>" 
                                style="max-width: 500px;">
                            <?php else: ?>
                            <h1 class="h2 mb-0"><?php echo htmlspecialchars($document['title']); ?></h1>
                            <?php endif; ?>
                        </div>
                        
                        <div class="d-flex">
                            <?php if ($can_edit): ?>
                            <select id="document-status" class="form-select form-select-sm me-2" style="width: 130px;">
                                <option value="draft" <?php echo $document['status'] === 'draft' ? 'selected' : ''; ?>>Draft</option>
                                <option value="in_review" <?php echo $document['status'] === 'in_review' ? 'selected' : ''; ?>>In Review</option>
                                <option value="published" <?php echo $document['status'] === 'published' ? 'selected' : ''; ?>>Published</option>
                            </select>
                            <button id="save-button" class="btn btn-primary btn-sm">
                                <i class="fas fa-save me-1"></i> Save
                            </button>
                            <?php else: ?>
                            <span class="badge <?php echo $document['status'] === 'published' ? 'bg-success' : 'bg-warning text-dark'; ?> p-2">
                                <?php echo ucfirst(str_replace('_', ' ', $document['status'])); ?>
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <?php if (isset($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                
                <?php if (isset($success)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $success; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                
                <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    Action completed successfully!
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                
                <!-- Document Content -->
                <div class="row">
                    <!-- Editor Column -->
                    <div class="col-md-8 mb-4">
                        <!-- Document Editor -->
                        <div class="card mb-4">
                            <div class="card-body p-0">
                                <div class="editor-container">
                                    <div id="editor"><?php echo $document['content'] ?? ''; ?></div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Comments Section -->
                        <div class="card" id="comments">
                            <div class="card-header bg-light">
                                <h5 class="mb-0">Comments</h5>
                            </div>
                            <div class="card-body">
                                <!-- Comment Form -->
                                <form method="post" action="" class="mb-4">
                                    <input type="hidden" name="action" value="add_comment">
                                    <div class="mb-3">
                                        <textarea class="form-control" name="comment_text" rows="3" placeholder="Add a comment..." required></textarea>
                                    </div>
                                    <div class="text-end">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-comment me-1"></i> Add Comment
                                        </button>
                                    </div>
                                </form>
                                
                                <!-- Comments List -->
                                <?php if (empty($comments)): ?>
                                <div class="text-center py-4">
                                    <p class="text-muted">No comments yet. Be the first to comment!</p>
                                </div>
                                <?php else: ?>
                                <div class="comments-list">
                                    <?php foreach ($comments as $comment): ?>
                                    <div class="comment-container" id="comment-<?php echo $comment['id']; ?>">
                                        <div class="d-flex justify-content-between">
                                            <h6 class="mb-1">
                                                <?php echo htmlspecialchars($comment['user_name']); ?>
                                                <?php if ($comment['user_id'] == $user_id): ?>
                                                <span class="badge bg-light text-dark">You</span>
                                                <?php endif; ?>
                                            </h6>
                                            <small class="text-muted"><?php echo time_ago($comment['created_at']); ?></small>
                                        </div>
                                        <p class="mb-2"><?php echo nl2br(htmlspecialchars($comment['comment_text'])); ?></p>
                                        
                                        <div class="mb-3">
                                            <button class="btn btn-sm btn-link p-0" 
                                                onclick="toggleReplyForm(<?php echo $comment['id']; ?>)">
                                                <i class="fas fa-reply me-1"></i> Reply
                                            </button>
                                        </div>
                                        
                                        <!-- Reply Form (hidden by default) -->
                                        <div id="reply-form-<?php echo $comment['id']; ?>" class="mb-3 d-none">
                                            <form method="post" action="">
                                                <input type="hidden" name="action" value="add_comment">
                                                <input type="hidden" name="parent_id" value="<?php echo $comment['id']; ?>">
                                                <div class="mb-2">
                                                    <textarea class="form-control form-control-sm" name="comment_text" rows="2" placeholder="Write a reply..." required></textarea>
                                                </div>
                                                <div class="text-end">
                                                    <button type="button" class="btn btn-sm btn-secondary me-1" 
                                                        onclick="toggleReplyForm(<?php echo $comment['id']; ?>)">
                                                        Cancel
                                                    </button>
                                                    <button type="submit" class="btn btn-sm btn-primary">
                                                        Reply
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                        
                                        <!-- Replies -->
                                        <?php if (!empty($comment['replies'])): ?>
                                        <?php foreach ($comment['replies'] as $reply): ?>
                                        <div class="comment-reply">
                                            <div class="d-flex justify-content-between">
                                                <h6 class="mb-1">
                                                    <?php echo htmlspecialchars($reply['user_name']); ?>
                                                    <?php if ($reply['user_id'] == $user_id): ?>
                                                    <span class="badge bg-light text-dark">You</span>
                                                    <?php endif; ?>
                                                </h6>
                                                <small class="text-muted"><?php echo time_ago($reply['created_at']); ?></small>
                                            </div>
                                            <p class="mb-2"><?php echo nl2br(htmlspecialchars($reply['comment_text'])); ?></p>
                                        </div>
                                        <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Sidebar Column -->
                    <div class="col-md-4">
                        <div class="document-info">
                            <!-- Document Info -->
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">Document Info</h5>
                                </div>
                                <div class="card-body">
                                    <p class="mb-1">
                                        <strong>Workspace:</strong> 
                                        <a href="workspace_detail.php?id=<?php echo $workspace_id; ?>">
                                            <?php echo htmlspecialchars($document['workspace_title']); ?>
                                        </a>
                                    </p>
                                    <p class="mb-1">
                                        <strong>Type:</strong> 
                                        <?php echo ucfirst(str_replace('_', ' ', $document['document_type'])); ?>
                                    </p>
                                    <p class="mb-1">
                                        <strong>Created by:</strong> 
                                        <?php echo htmlspecialchars($document['creator_name']); ?>
                                    </p>
                                    <p class="mb-1">
                                        <strong>Created:</strong> 
                                        <?php echo date('F j, Y', strtotime($document['created_at'])); ?>
                                    </p>
                                    <p class="mb-1">
                                        <strong>Last updated:</strong> 
                                        <?php echo date('F j, Y g:i A', strtotime($document['updated_at'])); ?>
                                    </p>
                                    <p class="mb-3">
                                        <strong>Current version:</strong> 
                                        <?php echo $document['version']; ?>
                                    </p>
                                    
                                    <?php if ($can_edit): ?>
                                    <div class="mb-3">
                                        <label for="change-summary" class="form-label">Change Summary</label>
                                        <input type="text" class="form-control form-control-sm" id="change-summary" 
                                            placeholder="Describe your changes">
                                    </div>
                                    <?php endif; ?>
                                    
                                    <div class="d-grid gap-2">
                                        <?php if ($can_edit): ?>
                                        <button id="save-button-sidebar" class="btn btn-primary">
                                            <i class="fas fa-save me-1"></i> Save Document
                                        </button>
                                        <?php endif; ?>
                                        <a href="#" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#versionsModal">
                                            <i class="fas fa-history me-1"></i> View Version History
                                        </a>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- AI Suggestions (optional) -->
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">AI Suggestions</h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <button class="btn btn-outline-primary btn-sm w-100" id="ai-analyze-btn">
                                            <i class="fas fa-magic me-1"></i> Analyze Content
                                        </button>
                                    </div>
                                    <div id="ai-suggestions" class="d-none">
                                        <h6>Content Analysis</h6>
                                        <ul class="small">
                                            <li>Consider adding more engagement activities</li>
                                            <li>Objectives could be more measurable</li>
                                            <li>Good alignment between activities and assessments</li>
                                        </ul>
                                        
                                        <h6 class="mt-3">XR Enhancement Ideas</h6>
                                        <p class="small">Try incorporating 3D models for the key concepts, or a virtual field trip to illustrate real-world applications.</p>
                                        
                                        <div class="d-grid">
                                            <button class="btn btn-sm btn-outline-secondary" id="generate-more-btn">
                                                Generate More Ideas
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <!-- Versions Modal -->
    <div class="modal fade" id="versionsModal" tabindex="-1" aria-labelledby="versionsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="versionsModalLabel">Version History</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php if (empty($versions)): ?>
                    <p class="text-center">No version history available</p>
                    <?php else: ?>
                    <div class="timeline">
                        <?php foreach ($versions as $version): ?>
                        <div class="timeline-item">
                            <div class="timeline-marker">
                                <?php echo $version['version_number']; ?>
                            </div>
                            <div class="timeline-content">
                                <h6 class="mb-1">Version <?php echo $version['version_number']; ?></h6>
                                <p class="mb-1 small">
                                    <?php echo htmlspecialchars($version['change_summary']); ?>
                                </p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        <?php echo htmlspecialchars($version['modifier_name']); ?> · 
                                        <?php echo date('M j, Y g:i A', strtotime($version['created_at'])); ?>
                                    </small>
                                    <?php if ($can_edit && $version['version_number'] != $document['version']): ?>
                                    <button class="btn btn-sm btn-outline-secondary revert-btn" 
                                        data-version="<?php echo $version['version_number']; ?>">
                                        Restore
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Save Confirmation Modal -->
    <div class="modal fade" id="saveConfirmModal" tabindex="-1" aria-labelledby="saveConfirmModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="saveConfirmModalLabel">Save Document</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Your changes will be saved and visible to all workspace members.</p>
                    <div class="mb-3">
                        <label for="save-change-summary" class="form-label">Change Summary</label>
                        <input type="text" class="form-control" id="save-change-summary" 
                            placeholder="Describe your changes">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="confirm-save-btn">Save Document</button>
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
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                [{ 'indent': '-1'}, { 'indent': '+1' }],
                [{ 'align': [] }],
                ['link', 'image'],
                ['clean']
            ]
        },
        placeholder: 'Write your document content here...',
        <?php if (!$can_edit): ?>
        readOnly: true
        <?php endif; ?>
    });
    
    <?php if ($can_edit): ?>
    // Save document functionality
    document.getElementById('save-button').addEventListener('click', function() {
        // Show save confirmation modal
        var changeSummary = document.getElementById('change-summary').value;
        document.getElementById('save-change-summary').value = changeSummary;
        var saveModal = new bootstrap.Modal(document.getElementById('saveConfirmModal'));
        saveModal.show();
    });
    
    document.getElementById('save-button-sidebar').addEventListener('click', function() {
        // Show save confirmation modal
        var changeSummary = document.getElementById('change-summary').value;
        document.getElementById('save-change-summary').value = changeSummary;
        var saveModal = new bootstrap.Modal(document.getElementById('saveConfirmModal'));
        saveModal.show();
    });
    
    document.getElementById('confirm-save-btn').addEventListener('click', function() {
        // Get form values
        var title = document.getElementById('document-title').value;
        var status = document.getElementById('document-status').value;
        var content = quill.root.innerHTML;
        var changeSummary = document.getElementById('save-change-summary').value;
        
        // Create a form and submit
        var form = document.createElement('form');
        form.method = 'POST';
        form.action = '';
        
        // Action
        var actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'save_document';
        form.appendChild(actionInput);
        
        // Title
        var titleInput = document.createElement('input');
        titleInput.type = 'hidden';
        titleInput.name = 'title';
        titleInput.value = title;
        form.appendChild(titleInput);
        
        // Status
        var statusInput = document.createElement('input');
        statusInput.type = 'hidden';
        statusInput.name = 'status';
        statusInput.value = status;
        form.appendChild(statusInput);
        
        // Content
        var contentInput = document.createElement('input');
        contentInput.type = 'hidden';
        contentInput.name = 'content';
        contentInput.value = content;
        form.appendChild(contentInput);
        
        // Change Summary
        var summaryInput = document.createElement('input');
        summaryInput.type = 'hidden';
        summaryInput.name = 'change_summary';
        summaryInput.value = changeSummary || 'Updated document';
        form.appendChild(summaryInput);
        
        document.body.appendChild(form);
        form.submit();
    });
    
    // Version revert functionality
    document.querySelectorAll('.revert-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            if (confirm('Are you sure you want to restore this version? Unsaved changes will be lost.')) {
                var versionNumber = this.getAttribute('data-version');
                // Create a form and submit
                var form = document.createElement('form');
                form.method = 'POST';
                form.action = '';
                
                // Action
                var actionInput = document.createElement('input');
                actionInput.type = 'hidden';
                actionInput.name = 'action';
                actionInput.value = 'revert_version';
                form.appendChild(actionInput);
                
                // Version Number
                var versionInput = document.createElement('input');
                versionInput.type = 'hidden';
                versionInput.name = 'version_number';
                versionInput.value = versionNumber;
                form.appendChild(versionInput);
                
                document.body.appendChild(form);
                form.submit();
            }
        });
    });
    <?php endif; ?>
    
    // Toggle reply form
    function toggleReplyForm(commentId) {
        var replyForm = document.getElementById('reply-form-' + commentId);
        if (replyForm.classList.contains('d-none')) {
            replyForm.classList.remove('d-none');
        } else {
            replyForm.classList.add('d-none');
        }
    }
    
    // AI Suggestions toggle
    document.getElementById('ai-analyze-btn').addEventListener('click', function() {
        var suggestionsDiv = document.getElementById('ai-suggestions');
        if (suggestionsDiv.classList.contains('d-none')) {
            suggestionsDiv.classList.remove('d-none');
            this.innerHTML = '<i class="fas fa-times me-1"></i> Hide Analysis';
        } else {
            suggestionsDiv.classList.add('d-none');
            this.innerHTML = '<i class="fas fa-magic me-1"></i> Analyze Content';
        }
    });
    
    document.getElementById('generate-more-btn').addEventListener('click', function() {
        // In a real implementation, this would call the AI service
        alert('This feature will be implemented in the future using the OpenAI integration.');
    });
    </script>
</body>
</html>