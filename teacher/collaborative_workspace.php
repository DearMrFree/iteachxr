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

// Get workspaces for the current user
$user_id = $user['id'];
$workspaces = [];

try {
    $db = get_db_connection();
    
    // Get workspaces where user is a member
    $stmt = $db->prepare("
        SELECT cw.id, cw.title, cw.description, cw.created_at, cw.updated_at, cw.status,
               u.full_name as creator_name, 
               COUNT(DISTINCT wm.user_id) as member_count,
               COUNT(DISTINCT wd.id) as document_count
        FROM collaborative_workspaces cw
        JOIN workspace_members wm ON cw.id = wm.workspace_id
        JOIN users u ON cw.created_by = u.id
        LEFT JOIN workspace_documents wd ON cw.id = wd.workspace_id
        WHERE wm.user_id = :user_id
        GROUP BY cw.id, cw.title, cw.description, cw.created_at, cw.updated_at, cw.status, u.full_name
        ORDER BY cw.updated_at DESC
    ");
    
    $stmt->execute(['user_id' => $user_id]);
    $workspaces = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get recent activity for each workspace
    foreach ($workspaces as &$workspace) {
        $activityStmt = $db->prepare("
            SELECT wa.id, wa.activity_type, wa.created_at, wa.activity_data,
                   u.full_name as user_name
            FROM workspace_activities wa
            JOIN users u ON wa.user_id = u.id
            WHERE wa.workspace_id = :workspace_id
            ORDER BY wa.created_at DESC
            LIMIT 5
        ");
        
        $activityStmt->execute(['workspace_id' => $workspace['id']]);
        $workspace['recent_activities'] = $activityStmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) {
    $error = "Error fetching workspaces: " . $e->getMessage();
}

// Handle workspace creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_workspace') {
    if (isset($_POST['title']) && !empty($_POST['title'])) {
        $title = $_POST['title'];
        $description = $_POST['description'] ?? '';
        $is_public = isset($_POST['is_public']) ? true : false;
        
        try {
            $db = get_db_connection();
            
            // Start a transaction
            $db->beginTransaction();
            
            // Create the workspace
            $stmt = $db->prepare("
                INSERT INTO collaborative_workspaces 
                (title, description, created_by, is_public) 
                VALUES (:title, :description, :created_by, :is_public)
                RETURNING id
            ");
            
            $stmt->execute([
                'title' => $title,
                'description' => $description,
                'created_by' => $user_id,
                'is_public' => $is_public
            ]);
            
            $workspace_id = $stmt->fetchColumn();
            
            // Add the creator as a member with 'owner' role
            $memberStmt = $db->prepare("
                INSERT INTO workspace_members 
                (workspace_id, user_id, role) 
                VALUES (:workspace_id, :user_id, 'owner')
            ");
            
            $memberStmt->execute([
                'workspace_id' => $workspace_id,
                'user_id' => $user_id
            ]);
            
            // Log the activity
            $activityStmt = $db->prepare("
                INSERT INTO workspace_activities 
                (workspace_id, user_id, activity_type, activity_data) 
                VALUES (:workspace_id, :user_id, 'create_workspace', :activity_data)
            ");
            
            $activityStmt->execute([
                'workspace_id' => $workspace_id,
                'user_id' => $user_id,
                'activity_data' => json_encode(['workspace_title' => $title])
            ]);
            
            // Commit the transaction
            $db->commit();
            
            // Redirect to the workspace
            header("Location: workspace_detail.php?id=$workspace_id");
            exit;
        } catch (Exception $e) {
            // Roll back the transaction in case of error
            $db->rollBack();
            $error = "Error creating workspace: " . $e->getMessage();
        }
    } else {
        $error = "Workspace title is required";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Collaborative Workspaces - iTeachXR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <style>
        .workspace-card {
            transition: transform 0.2s ease;
        }
        .workspace-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .activity-item {
            border-left: 3px solid #0d6efd;
            padding-left: 15px;
            margin-bottom: 10px;
        }
        .create-workspace-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 1000;
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
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1><i class="fas fa-users-gear me-2"></i> Collaborative Workspaces</h1>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createWorkspaceModal">
                        <i class="fas fa-plus me-2"></i> New Workspace
                    </button>
                </div>
                
                <?php if (isset($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                
                <!-- Workspaces Grid -->
                <?php if (empty($workspaces)): ?>
                <div class="text-center my-5">
                    <div class="display-1 text-muted mb-3">
                        <i class="fas fa-users-slash"></i>
                    </div>
                    <h3 class="mb-3">No Collaborative Workspaces Found</h3>
                    <p class="mb-4">Create your first workspace to start collaborating with other teachers.</p>
                    <button class="btn btn-lg btn-primary" data-bs-toggle="modal" data-bs-target="#createWorkspaceModal">
                        <i class="fas fa-plus me-2"></i> Create Workspace
                    </button>
                </div>
                <?php else: ?>
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4 mb-4">
                    <?php foreach ($workspaces as $workspace): ?>
                    <div class="col">
                        <div class="card h-100 workspace-card">
                            <div class="card-header bg-primary text-white">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0"><?php echo htmlspecialchars($workspace['title']); ?></h5>
                                    <span class="badge bg-light text-dark">
                                        <?php echo ucfirst($workspace['status']); ?>
                                    </span>
                                </div>
                            </div>
                            <div class="card-body">
                                <p class="card-text">
                                    <?php 
                                    echo !empty($workspace['description']) 
                                        ? htmlspecialchars(substr($workspace['description'], 0, 100)) . (strlen($workspace['description']) > 100 ? '...' : '')
                                        : '<em>No description provided</em>';
                                    ?>
                                </p>
                                <div class="d-flex justify-content-between mb-3">
                                    <small class="text-muted">
                                        <i class="fas fa-users me-1"></i> <?php echo $workspace['member_count']; ?> members
                                    </small>
                                    <small class="text-muted">
                                        <i class="fas fa-file-alt me-1"></i> <?php echo $workspace['document_count']; ?> documents
                                    </small>
                                </div>
                                
                                <h6 class="mt-3 mb-2">Recent Activity</h6>
                                <?php if (empty($workspace['recent_activities'])): ?>
                                <p class="text-muted small">No recent activity</p>
                                <?php else: ?>
                                <div class="recent-activities small">
                                    <?php foreach (array_slice($workspace['recent_activities'], 0, 2) as $activity): ?>
                                    <div class="activity-item">
                                        <span class="fw-bold"><?php echo htmlspecialchars($activity['user_name']); ?></span>
                                        <?php 
                                        $activity_text = '';
                                        switch ($activity['activity_type']) {
                                            case 'create_workspace':
                                                $activity_text = 'created this workspace';
                                                break;
                                            case 'add_document':
                                                $data = json_decode($activity['activity_data'], true);
                                                $activity_text = 'added document "' . htmlspecialchars($data['document_title'] ?? 'Untitled') . '"';
                                                break;
                                            case 'update_document':
                                                $data = json_decode($activity['activity_data'], true);
                                                $activity_text = 'updated document "' . htmlspecialchars($data['document_title'] ?? 'Untitled') . '"';
                                                break;
                                            case 'add_member':
                                                $data = json_decode($activity['activity_data'], true);
                                                $activity_text = 'added ' . htmlspecialchars($data['member_name'] ?? 'someone') . ' to the workspace';
                                                break;
                                            default:
                                                $activity_text = 'performed an action';
                                        }
                                        echo $activity_text;
                                        ?>
                                        <div class="text-muted mt-1">
                                            <?php echo time_ago($activity['created_at']); ?>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                            <div class="card-footer bg-white">
                                <a href="workspace_detail.php?id=<?php echo $workspace['id']; ?>" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-arrow-right me-1"></i> Open Workspace
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </main>
        </div>
    </div>
    
    <!-- Create Workspace Modal -->
    <div class="modal fade" id="createWorkspaceModal" tabindex="-1" aria-labelledby="createWorkspaceModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="post" action="">
                    <input type="hidden" name="action" value="create_workspace">
                    <div class="modal-header">
                        <h5 class="modal-title" id="createWorkspaceModalLabel">Create New Workspace</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="workspace-title" class="form-label">Workspace Title</label>
                            <input type="text" class="form-control" id="workspace-title" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="workspace-description" class="form-label">Description (Optional)</label>
                            <textarea class="form-control" id="workspace-description" name="description" rows="3"></textarea>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="workspace-public" name="is_public">
                            <label class="form-check-label" for="workspace-public">Make this workspace public to all teachers</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create Workspace</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
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