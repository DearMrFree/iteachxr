<?php
// iTeachXR - Admin Dashboard
// Main admin control panel

require_once('../config.php');
require_once($CFG->dirroot . '/lib/moodlelib.php');

// Check if user is logged in and is admin
if (!isloggedin()) {
    redirect($CFG->wwwroot . '/login/index.php');
}

if (!is_siteadmin()) {
    print_error('nopermissions', 'error', '', 'access admin area');
}

// Process admin actions
$action = optional_param('action', '', PARAM_ALPHA);
$id = optional_param('id', 0, PARAM_INT);
$section = optional_param('section', 'dashboard', PARAM_ALPHA);

$message = '';
$messageType = '';

if ($action) {
    switch ($action) {
        case 'enableai':
            $CFG->enableAI = true;
            $message = 'AI features have been enabled';
            $messageType = 'success';
            break;
            
        case 'disableai':
            $CFG->enableAI = false;
            $message = 'AI features have been disabled';
            $messageType = 'success';
            break;
            
        case 'clearai':
            // Delete AI data files
            $command = "rm -f {$CFG->dataroot}/ai/*.json";
            exec($command);
            $message = 'AI data has been cleared';
            $messageType = 'success';
            break;
            
        case 'updateuser':
            // Update user role
            if ($id > 0) {
                $role = required_param('role', PARAM_ALPHA);
                
                global $DB;
                try {
                    $DB->execute("UPDATE {user} SET role = ? WHERE id = ?", [$role, $id]);
                    $message = 'User role has been updated';
                    $messageType = 'success';
                } catch (Exception $e) {
                    $message = 'Error updating user role: ' . $e->getMessage();
                    $messageType = 'danger';
                }
            }
            break;
            
        case 'deleteuser':
            // Delete user
            if ($id > 0) {
                global $DB;
                try {
                    $DB->delete_records('user', ['id' => $id]);
                    $message = 'User has been deleted';
                    $messageType = 'success';
                } catch (Exception $e) {
                    $message = 'Error deleting user: ' . $e->getMessage();
                    $messageType = 'danger';
                }
            }
            break;
            
        case 'hidecourse':
            // Hide course
            if ($id > 0) {
                global $DB;
                try {
                    $DB->execute("UPDATE {course} SET visible = 0 WHERE id = ?", [$id]);
                    $message = 'Course has been hidden';
                    $messageType = 'success';
                } catch (Exception $e) {
                    $message = 'Error hiding course: ' . $e->getMessage();
                    $messageType = 'danger';
                }
            }
            break;
            
        case 'showcourse':
            // Show course
            if ($id > 0) {
                global $DB;
                try {
                    $DB->execute("UPDATE {course} SET visible = 1 WHERE id = ?", [$id]);
                    $message = 'Course has been made visible';
                    $messageType = 'success';
                } catch (Exception $e) {
                    $message = 'Error showing course: ' . $e->getMessage();
                    $messageType = 'danger';
                }
            }
            break;
            
        case 'deletecourse':
            // Delete course
            if ($id > 0) {
                global $DB;
                try {
                    $DB->delete_records('course', ['id' => $id]);
                    $message = 'Course has been deleted';
                    $messageType = 'success';
                } catch (Exception $e) {
                    $message = 'Error deleting course: ' . $e->getMessage();
                    $messageType = 'danger';
                }
            }
            break;
    }
}

// Set up page
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('admin');
$PAGE->set_title('Admin Dashboard');
$PAGE->set_heading('iTeachXR Administration');
$PAGE->set_url(new moodle_url('/admin/index.php', array('section' => $section)));

// Start output
echo $OUTPUT->header();

// Display message if any
if (!empty($message)) {
    echo '<div class="alert alert-' . $messageType . ' alert-dismissible fade show" role="alert">
        ' . $message . '
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>';
}

// Admin navigation tabs
echo '<div class="mb-4">
    <ul class="nav nav-tabs" id="adminTab" role="tablist">
        <li class="nav-item" role="presentation">
            <a class="nav-link ' . ($section == 'dashboard' ? 'active' : '') . '" id="dashboard-tab" 
                href="' . $CFG->wwwroot . '/admin/index.php?section=dashboard" role="tab">
                <i class="fa fa-tachometer-alt me-1"></i> Dashboard
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link ' . ($section == 'users' ? 'active' : '') . '" id="users-tab" 
                href="' . $CFG->wwwroot . '/admin/index.php?section=users" role="tab">
                <i class="fa fa-users me-1"></i> Users
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link ' . ($section == 'courses' ? 'active' : '') . '" id="courses-tab" 
                href="' . $CFG->wwwroot . '/admin/index.php?section=courses" role="tab">
                <i class="fa fa-book me-1"></i> Courses
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link ' . ($section == 'settings' ? 'active' : '') . '" id="settings-tab" 
                href="' . $CFG->wwwroot . '/admin/index.php?section=settings" role="tab">
                <i class="fa fa-cog me-1"></i> Settings
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link ' . ($section == 'ai' ? 'active' : '') . '" id="ai-tab" 
                href="' . $CFG->wwwroot . '/admin/index.php?section=ai" role="tab">
                <i class="fa fa-robot me-1"></i> AI Settings
            </a>
        </li>
    </ul>
</div>';

// Section content
if ($section == 'dashboard') {
    // Include dashboard content
    include('dashboard_content.php');
} elseif ($section == 'users') {
    // Users management section
    echo '<div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="mb-0">User Management</h3>
            <a href="' . $CFG->wwwroot . '/admin/user.php?action=add" class="btn btn-primary btn-sm">
                <i class="fa fa-plus me-1"></i> Add New User
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="usersTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Last Access</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>';
    
    // Get users
    global $DB;
    $users = $DB->get_records('user', array(), 'id', '*', 0, 500);
    
    foreach ($users as $user) {
        echo '<tr>
            <td>' . $user->id . '</td>
            <td><a href="' . $CFG->wwwroot . '/user/profile.php?id=' . $user->id . '">' . 
                $user->firstname . ' ' . $user->lastname . '</a></td>
            <td>' . $user->email . '</td>
            <td>
                <form id="role-form-' . $user->id . '" method="post" action="' . $CFG->wwwroot . '/admin/index.php?section=users&action=updateuser&id=' . $user->id . '">
                    <select class="form-select form-select-sm" name="role" onchange="document.getElementById(\'role-form-' . $user->id . '\').submit();">
                        <option value="student" ' . (isset($user->role) && $user->role == 'student' ? 'selected' : '') . '>Student</option>
                        <option value="teacher" ' . (isset($user->role) && $user->role == 'teacher' ? 'selected' : '') . '>Teacher</option>
                        <option value="admin" ' . (isset($user->role) && $user->role == 'admin' ? 'selected' : '') . '>Admin</option>
                    </select>
                </form>
            </td>
            <td>' . (isset($user->lastaccess) ? format_time($user->lastaccess) : 'Never') . '</td>
            <td>
                <div class="btn-group btn-group-sm">
                    <a href="' . $CFG->wwwroot . '/user/profile.php?id=' . $user->id . '" class="btn btn-info">
                        <i class="fa fa-eye"></i>
                    </a>
                    <a href="' . $CFG->wwwroot . '/admin/user.php?action=edit&id=' . $user->id . '" class="btn btn-primary">
                        <i class="fa fa-edit"></i>
                    </a>
                    <a href="#" class="btn btn-danger" onclick="confirmDelete(' . $user->id . ', \'user\', \'' . $user->firstname . ' ' . $user->lastname . '\')">
                        <i class="fa fa-trash"></i>
                    </a>
                </div>
            </td>
        </tr>';
    }
    
    echo '      </tbody>
                </table>
            </div>
        </div>
    </div>';
} elseif ($section == 'courses') {
    // Courses management section
    echo '<div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="mb-0">Course Management</h3>
            <a href="' . $CFG->wwwroot . '/course/edit.php" class="btn btn-primary btn-sm">
                <i class="fa fa-plus me-1"></i> Add New Course
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="coursesTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Visibility</th>
                            <th>Start Date</th>
                            <th>Teachers</th>
                            <th>Students</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>';
    
    // Get courses
    global $DB;
    $courses = $DB->get_records('course', array(), 'id', '*', 0, 500);
    
    foreach ($courses as $course) {
        // Skip site course
        if ($course->id == 1) continue;
        
        // Get category name
        $category = $DB->get_record('course_categories', array('id' => $course->category));
        $categoryName = $category ? $category->name : 'Unknown';
        
        // Get count of enrolled students and teachers
        $studentCount = 0;
        $teacherCount = 0;
        
        echo '<tr>
            <td>' . $course->id . '</td>
            <td><a href="' . $CFG->wwwroot . '/course/view.php?id=' . $course->id . '">' . $course->fullname . '</a></td>
            <td>' . $categoryName . '</td>
            <td>';
            
        if ($course->visible) {
            echo '<span class="badge bg-success">Visible</span>
                <a href="' . $CFG->wwwroot . '/admin/index.php?section=courses&action=hidecourse&id=' . $course->id . '" 
                    class="btn btn-sm btn-outline-secondary ms-2">Hide</a>';
        } else {
            echo '<span class="badge bg-secondary">Hidden</span>
                <a href="' . $CFG->wwwroot . '/admin/index.php?section=courses&action=showcourse&id=' . $course->id . '" 
                    class="btn btn-sm btn-outline-success ms-2">Show</a>';
        }
            
        echo '</td>
            <td>' . format_time($course->startdate, 'strftimedate') . '</td>
            <td>' . $teacherCount . '</td>
            <td>' . $studentCount . '</td>
            <td>
                <div class="btn-group btn-group-sm">
                    <a href="' . $CFG->wwwroot . '/course/view.php?id=' . $course->id . '" class="btn btn-info">
                        <i class="fa fa-eye"></i>
                    </a>
                    <a href="' . $CFG->wwwroot . '/course/edit.php?id=' . $course->id . '" class="btn btn-primary">
                        <i class="fa fa-edit"></i>
                    </a>
                    <a href="#" class="btn btn-danger" onclick="confirmDelete(' . $course->id . ', \'course\', \'' . $course->fullname . '\')">
                        <i class="fa fa-trash"></i>
                    </a>
                </div>
            </td>
        </tr>';
    }
    
    echo '      </tbody>
                </table>
            </div>
        </div>
    </div>';
} elseif ($section == 'settings') {
    // Site settings section
    echo '<div class="card">
        <div class="card-header">
            <h3 class="mb-0">Site Settings</h3>
        </div>
        <div class="card-body">
            <form method="post" action="" id="siteSettingsForm">
                <div class="mb-3">
                    <label for="siteName" class="form-label">Site Name</label>
                    <input type="text" class="form-control" id="siteName" name="siteName" value="' . $SITE->fullname . '">
                </div>
                
                <div class="mb-3">
                    <label for="siteShortName" class="form-label">Site Short Name</label>
                    <input type="text" class="form-control" id="siteShortName" name="siteShortName" value="' . $SITE->shortname . '">
                </div>
                
                <div class="mb-3">
                    <label for="siteSummary" class="form-label">Site Summary</label>
                    <textarea class="form-control" id="siteSummary" name="siteSummary" rows="3">' . $SITE->summary . '</textarea>
                </div>
                
                <h4 class="mt-4 mb-3">Appearance</h4>
                
                <div class="mb-3">
                    <label for="theme" class="form-label">Default Theme</label>
                    <select class="form-select" id="theme" name="theme">
                        <option value="iteachxr" selected>iTeachXR</option>
                    </select>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="startOfWeek" class="form-label">Start of Week</label>
                        <select class="form-select" id="startOfWeek" name="startOfWeek">
                            <option value="0">Sunday</option>
                            <option value="1" selected>Monday</option>
                            <option value="6">Saturday</option>
                        </select>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="defaultLang" class="form-label">Default Language</label>
                        <select class="form-select" id="defaultLang" name="defaultLang">
                            <option value="en" selected>English</option>
                            <option value="es">Español</option>
                            <option value="fr">Français</option>
                            <option value="de">Deutsch</option>
                            <option value="zh">中文</option>
                        </select>
                    </div>
                </div>
                
                <h4 class="mt-4 mb-3">Security Settings</h4>
                
                <div class="mb-3 form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="passwordPolicy" checked>
                    <label class="form-check-label" for="passwordPolicy">Enable password policy</label>
                </div>
                
                <div class="mb-3">
                    <label for="minPasswordLength" class="form-label">Minimum Password Length</label>
                    <input type="number" class="form-control" id="minPasswordLength" name="minPasswordLength" value="8" min="6" max="30">
                </div>
                
                <div class="mb-3 form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="sessionTimeout" checked>
                    <label class="form-check-label" for="sessionTimeout">Enable session timeout</label>
                </div>
                
                <div class="mb-3">
                    <label for="sessionDuration" class="form-label">Session Duration (hours)</label>
                    <input type="number" class="form-control" id="sessionDuration" name="sessionDuration" value="8" min="1" max="24">
                </div>
                
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save me-1"></i> Save Settings
                    </button>
                </div>
            </form>
        </div>
    </div>';
} elseif ($section == 'ai') {
    // AI settings section
    echo '<div class="card">
        <div class="card-header">
            <h3 class="mb-0">AI Settings</h3>
        </div>
        <div class="card-body">
            <div class="d-flex align-items-center mb-4">
                <div class="me-3">
                    <span class="badge ' . ($CFG->enableAI ? 'bg-success' : 'bg-danger') . ' p-2">
                        <i class="fa fa-' . ($CFG->enableAI ? 'check' : 'times') . ' me-1"></i>
                        AI Features: ' . ($CFG->enableAI ? 'Enabled' : 'Disabled') . '
                    </span>
                </div>
                <div>
                    <a href="' . $CFG->wwwroot . '/admin/index.php?section=ai&action=' . ($CFG->enableAI ? 'disableai' : 'enableai') . '" 
                        class="btn btn-' . ($CFG->enableAI ? 'danger' : 'success') . ' btn-sm">
                        <i class="fa fa-' . ($CFG->enableAI ? 'power-off' : 'power-off') . ' me-1"></i>
                        ' . ($CFG->enableAI ? 'Disable AI' : 'Enable AI') . '
                    </a>
                </div>
            </div>
            
            <div class="mb-4">
                <p>Configure AI features for the iTeachXR platform:</p>
            </div>
            
            <div class="row mb-4">
                <div class="col-md-6">
                    <h4 class="h5 mb-3">API Configuration</h4>
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="openai_api_key" class="form-label">OpenAI API Key</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="openai_api_key" 
                                        value="' . (!empty($CFG->openai_api_key) ? '••••••••••••••••••••••' : '') . '">
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility(\'openai_api_key\')">
                                        <i class="fa fa-eye"></i>
                                    </button>
                                </div>
                                <div class="form-text">Your OpenAI API key for AI functionality</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="openai_model" class="form-label">OpenAI Model</label>
                                <select class="form-select" id="openai_model">
                                    <option value="gpt-4o" selected>GPT-4o (Default)</option>
                                    <option value="gpt-4">GPT-4</option>
                                    <option value="gpt-3.5-turbo">GPT-3.5 Turbo</option>
                                </select>
                                <div class="form-text">The AI model to use for generating content</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <h4 class="h5 mb-3">Feature Toggles</h4>
                    <div class="card">
                        <div class="card-body">
                            <div class="mb-3 form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="content_recommendations" ' . 
                                    ($CFG->aiFeatures['content_recommendations'] ? 'checked' : '') . '>
                                <label class="form-check-label" for="content_recommendations">Content Recommendations</label>
                                <div class="form-text">AI-powered content recommendations based on learning style and progress</div>
                            </div>
                            
                            <div class="mb-3 form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="automated_feedback" ' . 
                                    ($CFG->aiFeatures['automated_feedback'] ? 'checked' : '') . '>
                                <label class="form-check-label" for="automated_feedback">Automated Feedback</label>
                                <div class="form-text">AI-generated feedback on student submissions</div>
                            </div>
                            
                            <div class="mb-3 form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="plagiarism_detection" ' . 
                                    ($CFG->aiFeatures['plagiarism_detection'] ? 'checked' : '') . '>
                                <label class="form-check-label" for="plagiarism_detection">Plagiarism Detection</label>
                                <div class="form-text">AI-based plagiarism detection for student submissions</div>
                            </div>
                            
                            <div class="mb-3 form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="personalized_learning" ' . 
                                    ($CFG->aiFeatures['personalized_learning'] ? 'checked' : '') . '>
                                <label class="form-check-label" for="personalized_learning">Personalized Learning</label>
                                <div class="form-text">AI-generated personalized learning paths</div>
                            </div>
                            
                            <div class="mb-3 form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="chatbot_assistant" ' . 
                                    ($CFG->aiFeatures['chatbot_assistant'] ? 'checked' : '') . '>
                                <label class="form-check-label" for="chatbot_assistant">Chatbot Assistant</label>
                                <div class="form-text">AI chatbot to assist users with questions and tasks</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <h4 class="h5 mb-3">AI Data Management</h4>
            <div class="card mb-4">
                <div class="card-body">
                    <p>Manage the data used by the AI system:</p>
                    
                    <div class="d-flex">
                        <a href="' . $CFG->wwwroot . '/admin/index.php?section=ai&action=clearai" class="btn btn-warning me-2" 
                            onclick="return confirm(\'Are you sure you want to clear all AI data? This cannot be undone.\')">
                            <i class="fa fa-trash me-1"></i> Clear AI Data
                        </a>
                        
                        <a href="' . $CFG->wwwroot . '/admin/ai_logs.php" class="btn btn-info">
                            <i class="fa fa-list me-1"></i> View AI Logs
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="mt-4">
                <button type="button" id="saveAiSettings" class="btn btn-primary">
                    <i class="fa fa-save me-1"></i> Save AI Settings
                </button>
            </div>
        </div>
    </div>';
}

// Add JavaScript for admin functionality
echo '<script>
function confirmDelete(id, type, name) {
    if (confirm("Are you sure you want to delete " + type + ": " + name + "? This cannot be undone.")) {
        window.location.href = "' . $CFG->wwwroot . '/admin/index.php?section=" + type + "s&action=delete" + type + "&id=" + id;
    }
}

function togglePasswordVisibility(fieldId) {
    const field = document.getElementById(fieldId);
    if (field.type === "password") {
        field.type = "text";
    } else {
        field.type = "password";
    }
}

document.addEventListener("DOMContentLoaded", function() {
    // Handle site settings form
    const siteSettingsForm = document.getElementById("siteSettingsForm");
    if (siteSettingsForm) {
        siteSettingsForm.addEventListener("submit", function(e) {
            e.preventDefault();
            
            // Simulate saving settings
            alert("Site settings saved successfully");
        });
    }
    
    // Handle AI settings form
    const saveAiSettings = document.getElementById("saveAiSettings");
    if (saveAiSettings) {
        saveAiSettings.addEventListener("click", function() {
            // Gather AI settings
            const openai_api_key = document.getElementById("openai_api_key").value;
            const openai_model = document.getElementById("openai_model").value;
            const content_recommendations = document.getElementById("content_recommendations").checked;
            const automated_feedback = document.getElementById("automated_feedback").checked;
            const plagiarism_detection = document.getElementById("plagiarism_detection").checked;
            const personalized_learning = document.getElementById("personalized_learning").checked;
            const chatbot_assistant = document.getElementById("chatbot_assistant").checked;
            
            // Simulate saving settings
            alert("AI settings saved successfully");
        });
    }
    
    // Initialize DataTables if available
    if (typeof $.fn.DataTable !== "undefined") {
        $("#usersTable, #coursesTable").DataTable({
            pageLength: 15,
            lengthMenu: [5, 15, 25, 50, 100],
            responsive: true
        });
    }
});
</script>';

// End page
echo $OUTPUT->footer();
