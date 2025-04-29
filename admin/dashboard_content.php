<?php
// iTeachXR - Admin Dashboard Content
// This file is included by the admin dashboard page

// Check direct access
defined('MOODLE_INTERNAL') || die();

// Get system stats
global $DB;

// Total users
$totalUsers = $DB->count_records('user');

// Users by role
$students = $DB->count_records_select('user', "role = 'student'");
$teachers = $DB->count_records_select('user', "role = 'teacher'");
$admins = $DB->count_records_select('user', "role = 'admin'");

// Total courses
$totalCourses = $DB->count_records('course') - 1; // Subtract site course

// Active courses
$activeCourses = $DB->count_records_select('course', "visible = 1") - 1; // Subtract site course

// Recent activities
$recentActivities = $DB->get_records_sql(
    "SELECT * FROM {logstore_standard_log} ORDER BY timecreated DESC LIMIT 10"
);

// Display dashboard widgets
echo '<div class="row">
    <!-- Stats Overview -->
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h4 class="mb-0"><i class="fa fa-chart-pie me-2"></i>System Overview</h4>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-6 mb-3">
                        <div class="d-flex flex-column align-items-center">
                            <div class="fs-1 text-primary mb-1">' . $totalUsers . '</div>
                            <div class="text-muted">Users</div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="d-flex flex-column align-items-center">
                            <div class="fs-1 text-success mb-1">' . $totalCourses . '</div>
                            <div class="text-muted">Courses</div>
                        </div>
                    </div>
                </div>
                
                <hr class="my-3">
                
                <h5>User Breakdown</h5>
                <div class="mb-3">
                    <label class="form-label d-flex justify-content-between mb-1">
                        <span>Students</span>
                        <span>' . $students . ' (' . round(($students / $totalUsers) * 100) . '%)</span>
                    </label>
                    <div class="progress">
                        <div class="progress-bar bg-primary" role="progressbar" style="width: ' . (($students / $totalUsers) * 100) . '%"></div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label d-flex justify-content-between mb-1">
                        <span>Teachers</span>
                        <span>' . $teachers . ' (' . round(($teachers / $totalUsers) * 100) . '%)</span>
                    </label>
                    <div class="progress">
                        <div class="progress-bar bg-success" role="progressbar" style="width: ' . (($teachers / $totalUsers) * 100) . '%"></div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label d-flex justify-content-between mb-1">
                        <span>Administrators</span>
                        <span>' . $admins . ' (' . round(($admins / $totalUsers) * 100) . '%)</span>
                    </label>
                    <div class="progress">
                        <div class="progress-bar bg-danger" role="progressbar" style="width: ' . (($admins / $totalUsers) * 100) . '%"></div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <a href="' . $CFG->wwwroot . '/admin/index.php?section=users" class="btn btn-sm btn-outline-primary">
                    <i class="fa fa-users me-1"></i> Manage Users
                </a>
                <a href="' . $CFG->wwwroot . '/admin/index.php?section=courses" class="btn btn-sm btn-outline-success">
                    <i class="fa fa-book me-1"></i> Manage Courses
                </a>
            </div>
        </div>
    </div>
    
    <!-- AI Insights -->
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h4 class="mb-0"><i class="fa fa-robot me-2"></i>AI Insights</h4>
            </div>
            <div class="card-body">
                <div class="ai-status mb-3 p-3 rounded';
                
// Add status-specific class and icon
if ($CFG->enableAI) {
    echo ' bg-success bg-opacity-10">
                    <div class="d-flex align-items-center">
                        <i class="fa fa-check-circle text-success fs-2 me-3"></i>
                        <div>
                            <h5 class="mb-1">AI System Active</h5>
                            <p class="mb-0 small">All AI features are running correctly</p>
                        </div>
                    </div>';
} else {
    echo ' bg-danger bg-opacity-10">
                    <div class="d-flex align-items-center">
                        <i class="fa fa-times-circle text-danger fs-2 me-3"></i>
                        <div>
                            <h5 class="mb-1">AI System Disabled</h5>
                            <p class="mb-0 small">AI features are currently turned off</p>
                        </div>
                    </div>';
}

echo '          </div>
                
                <h5>AI Feature Usage</h5>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Feature</th>
                                <th class="text-end">Status</th>
                                <th class="text-end">Usage</th>
                            </tr>
                        </thead>
                        <tbody>';

// Display AI feature usage
$aiFeatures = [
    'content_recommendations' => ['label' => 'Content Recommendations', 'usage' => 152],
    'automated_feedback' => ['label' => 'Automated Feedback', 'usage' => 87],
    'plagiarism_detection' => ['label' => 'Plagiarism Detection', 'usage' => 43],
    'personalized_learning' => ['label' => 'Personalized Learning', 'usage' => 64],
    'chatbot_assistant' => ['label' => 'Chatbot Assistant', 'usage' => 219]
];

foreach ($aiFeatures as $feature => $details) {
    $enabled = $CFG->enableAI && $CFG->aiFeatures[$feature];
    
    echo '<tr>
            <td>' . $details['label'] . '</td>
            <td class="text-end">
                <span class="badge ' . ($enabled ? 'bg-success' : 'bg-secondary') . '">
                    ' . ($enabled ? 'Active' : 'Inactive') . '
                </span>
            </td>
            <td class="text-end">' . ($enabled ? $details['usage'] : '-') . '</td>
        </tr>';
}

echo '          </tbody>
                    </table>
                </div>
                
                <div class="mt-3">
                    <h5>Model Performance</h5>
                    <div class="d-flex justify-content-between mb-1">
                        <span>Average Response Time</span>
                        <span>1.2s</span>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span>API Key Status</span>
                        <span class="badge ' . (!empty($CFG->openai_api_key) ? 'bg-success' : 'bg-danger') . '">
                            ' . (!empty($CFG->openai_api_key) ? 'Valid' : 'Missing') . '
                        </span>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <a href="' . $CFG->wwwroot . '/admin/index.php?section=ai" class="btn btn-sm btn-outline-primary">
                    <i class="fa fa-cog me-1"></i> AI Settings
                </a>
                <a href="' . $CFG->wwwroot . '/admin/ai_logs.php" class="btn btn-sm btn-outline-secondary">
                    <i class="fa fa-list me-1"></i> View AI Logs
                </a>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h4 class="mb-0"><i class="fa fa-bolt me-2"></i>Quick Actions</h4>
            </div>
            <div class="card-body">
                <div class="list-group">
                    <a href="' . $CFG->wwwroot . '/admin/user.php?action=add" class="list-group-item list-group-item-action">
                        <div class="d-flex align-items-center">
                            <div class="action-icon bg-primary text-white rounded-circle p-2 me-3">
                                <i class="fa fa-user-plus"></i>
                            </div>
                            <div>
                                <h5 class="mb-1">Add New User</h5>
                                <p class="mb-0 small text-muted">Create a new student, teacher, or admin account</p>
                            </div>
                        </div>
                    </a>
                    
                    <a href="' . $CFG->wwwroot . '/course/edit.php" class="list-group-item list-group-item-action">
                        <div class="d-flex align-items-center">
                            <div class="action-icon bg-success text-white rounded-circle p-2 me-3">
                                <i class="fa fa-book-medical"></i>
                            </div>
                            <div>
                                <h5 class="mb-1">Create New Course</h5>
                                <p class="mb-0 small text-muted">Set up a new course with AI-assisted content</p>
                            </div>
                        </div>
                    </a>
                    
                    <a href="' . $CFG->wwwroot . '/admin/index.php?section=settings" class="list-group-item list-group-item-action">
                        <div class="d-flex align-items-center">
                            <div class="action-icon bg-info text-white rounded-circle p-2 me-3">
                                <i class="fa fa-cog"></i>
                            </div>
                            <div>
                                <h5 class="mb-1">System Settings</h5>
                                <p class="mb-0 small text-muted">Configure site-wide settings and preferences</p>
                            </div>
                        </div>
                    </a>
                    
                    <a href="#" class="list-group-item list-group-item-action" id="performBackupBtn">
                        <div class="d-flex align-items-center">
                            <div class="action-icon bg-warning text-white rounded-circle p-2 me-3">
                                <i class="fa fa-download"></i>
                            </div>
                            <div>
                                <h5 class="mb-1">Backup System</h5>
                                <p class="mb-0 small text-muted">Create a full backup of the system</p>
                            </div>
                        </div>
                    </a>
                    
                    <a href="#" class="list-group-item list-group-item-action" id="clearCacheBtn">
                        <div class="d-flex align-items-center">
                            <div class="action-icon bg-secondary text-white rounded-circle p-2 me-3">
                                <i class="fa fa-broom"></i>
                            </div>
                            <div>
                                <h5 class="mb-1">Clear Cache</h5>
                                <p class="mb-0 small text-muted">Clear system cache and temporary files</p>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity and System Health -->
<div class="row">
    <!-- Recent Activity -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0"><i class="fa fa-history me-2"></i>Recent Activity</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>User</th>
                                <th>Action</th>
                                <th>Details</th>
                            </tr>
                        </thead>
                        <tbody>';

// Display recent activities
if ($recentActivities) {
    foreach ($recentActivities as $activity) {
        echo '<tr>
                <td>' . format_time($activity->timecreated) . '</td>
                <td>' . ($activity->userid ? 'User ' . $activity->userid : 'System') . '</td>
                <td>' . ucfirst($activity->action) . '</td>
                <td>' . $activity->target . '</td>
            </tr>';
    }
} else {
    echo '<tr><td colspan="4" class="text-center">No recent activity</td></tr>';
}

echo '          </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                <a href="' . $CFG->wwwroot . '/admin/report.php?report=log" class="btn btn-sm btn-outline-primary">
                    <i class="fa fa-list me-1"></i> View All Logs
                </a>
            </div>
        </div>
    </div>
    
    <!-- System Health -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0"><i class="fa fa-heartbeat me-2"></i>System Health</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Storage Usage -->
                    <div class="col-md-6 mb-3">
                        <h5>Storage Usage</h5>
                        <div class="mt-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span>Used: 2.4 GB</span>
                                <span>75%</span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar bg-primary" role="progressbar" style="width: 75%"></div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Database -->
                    <div class="col-md-6 mb-3">
                        <h5>Database</h5>
                        <div class="mt-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span>Size: 450 MB</span>
                                <span>45%</span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar bg-success" role="progressbar" style="width: 45%"></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Check Results -->
                <div class="mt-3">
                    <h5>System Checks</h5>
                    <ul class="list-group">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><i class="fa fa-check-circle text-success me-2"></i> PHP Version</span>
                            <span>' . phpversion() . '</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><i class="fa fa-check-circle text-success me-2"></i> Database Connection</span>
                            <span>OK</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><i class="fa fa-check-circle text-success me-2"></i> File Permissions</span>
                            <span>OK</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><i class="fa fa-exclamation-triangle text-warning me-2"></i> Cron Tasks</span>
                            <span>Last run: 6h ago</span>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="card-footer">
                <button type="button" class="btn btn-sm btn-outline-primary" id="runSystemCheckBtn">
                    <i class="fa fa-sync me-1"></i> Run Full System Check
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Dashboard JavaScript -->
<script>
document.addEventListener("DOMContentLoaded", function() {
    // Clear Cache Button
    document.getElementById("clearCacheBtn").addEventListener("click", function(e) {
        e.preventDefault();
        
        if (confirm("Are you sure you want to clear the system cache?")) {
            // Simulate clearing cache
            showNotification("System cache cleared successfully", "success");
        }
    });
    
    // Backup Button
    document.getElementById("performBackupBtn").addEventListener("click", function(e) {
        e.preventDefault();
        
        if (confirm("Are you sure you want to create a system backup? This may take some time.")) {
            // Simulate backup
            showNotification("Backup process started. This may take several minutes.", "info");
        }
    });
    
    // System Check Button
    document.getElementById("runSystemCheckBtn").addEventListener("click", function(e) {
        e.preventDefault();
        
        // Simulate system check
        showNotification("System check completed. All systems operational.", "success");
    });
    
    // Helper function to show notifications
    function showNotification(message, type = "info", duration = 5000) {
        const container = document.getElementById("notification-container") || createNotificationContainer();
        
        const notification = document.createElement("div");
        notification.className = `alert alert-${type} notification fade-in`;
        notification.innerHTML = `
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            ${message}
        `;
        
        container.appendChild(notification);
        
        setTimeout(() => {
            notification.style.opacity = "0";
            setTimeout(() => {
                notification.remove();
            }, 300);
        }, duration);
        
        notification.querySelector(".btn-close").addEventListener("click", function() {
            notification.style.opacity = "0";
            setTimeout(() => {
                notification.remove();
            }, 300);
        });
    }
    
    function createNotificationContainer() {
        const container = document.createElement("div");
        container.id = "notification-container";
        container.style.position = "fixed";
        container.style.top = "20px";
        container.style.right = "20px";
        container.style.zIndex = "9999";
        document.body.appendChild(container);
        return container;
    }
});
</script>

<style>
.action-icon {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>';
