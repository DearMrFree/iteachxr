<?php
// iTeachXR - User Profile Page
// View and edit user profile

require_once('../config.php');
require_once($CFG->dirroot . '/lib/moodlelib.php');

// Check if user is logged in
if (!isloggedin()) {
    redirect($CFG->wwwroot . '/login/index.php');
}

// Get user ID from URL (default to current user if not specified)
$id = optional_param('id', $USER->id, PARAM_INT);

// Check if user has permission to view this profile
if ($id != $USER->id && !has_capability('moodle/user:viewdetails', context_system::instance())) {
    print_error('nopermissions', 'error', '', 'view this profile');
}

// Get user data
$profileuser = get_user_by_id($id);
if (!$profileuser) {
    print_error('invaliduserid', 'error', '', $id);
}

// Set up page
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('standard');
$PAGE->set_title('User Profile: ' . $profileuser->firstname . ' ' . $profileuser->lastname);
$PAGE->set_heading('User Profile: ' . $profileuser->firstname . ' ' . $profileuser->lastname);
$PAGE->set_url(new moodle_url('/user/profile.php', array('id' => $id)));

// Process profile edit form
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $id == $USER->id) {
    $firstname = required_param('firstname', PARAM_TEXT);
    $lastname = required_param('lastname', PARAM_TEXT);
    $email = required_param('email', PARAM_EMAIL);
    $description = optional_param('description', '', PARAM_TEXT);
    $city = optional_param('city', '', PARAM_TEXT);
    $country = optional_param('country', '', PARAM_TEXT);
    $timezone = optional_param('timezone', 99, PARAM_INT);
    
    // Validate data
    $errors = array();
    
    if (empty($firstname)) {
        $errors[] = 'First name is required';
    }
    
    if (empty($lastname)) {
        $errors[] = 'Last name is required';
    }
    
    if (empty($email)) {
        $errors[] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email is not valid';
    }
    
    if (empty($errors)) {
        // Prepare user data
        $userdata = array(
            'id' => $USER->id,
            'firstname' => $firstname,
            'lastname' => $lastname,
            'email' => $email,
            'description' => $description,
            'city' => $city,
            'country' => $country,
            'timezone' => $timezone
        );
        
        // Save to database
        global $DB;
        
        try {
            $DB->update_record('user', (object)$userdata);
            
            // Update session user
            $USER->firstname = $firstname;
            $USER->lastname = $lastname;
            $USER->email = $email;
            $USER->description = $description;
            $USER->city = $city;
            $USER->country = $country;
            $USER->timezone = $timezone;
            
            $message = 'Profile updated successfully';
            $messageType = 'success';
            
            // Refresh user data
            $profileuser = get_user_by_id($id);
        } catch (Exception $e) {
            $message = 'Error updating profile: ' . $e->getMessage();
            $messageType = 'danger';
        }
    } else {
        $message = 'Please correct the following errors: ' . implode(', ', $errors);
        $messageType = 'danger';
    }
}

// Start output
echo $OUTPUT->header();

// Display message if any
if (!empty($message)) {
    echo '<div class="alert alert-' . $messageType . '">' . $message . '</div>';
}

// Start profile content
echo '<div class="row">
    <div class="col-md-4">
        <!-- Profile sidebar -->
        <div class="card mb-4">
            <div class="card-body text-center">
                <div class="profile-picture-container mb-3">
                    <div class="profile-picture">
                        <i class="fa fa-user-circle fa-5x"></i>
                    </div>
                </div>
                <h5 class="card-title">' . $profileuser->firstname . ' ' . $profileuser->lastname . '</h5>
                <p class="text-muted">' . (isset($profileuser->role) ? ucfirst($profileuser->role) : 'User') . '</p>';

// Show edit profile button if viewing own profile
if ($id == $USER->id) {
    echo '<button class="btn btn-primary btn-sm" id="edit-profile-btn">
        <i class="fa fa-edit me-1"></i> Edit Profile
    </button>';
}

echo '      </div>
            <ul class="list-group list-group-flush">
                <li class="list-group-item"><i class="fa fa-envelope me-2 text-muted"></i>' . $profileuser->email . '</li>
                <li class="list-group-item"><i class="fa fa-map-marker me-2 text-muted"></i>' . 
                    (!empty($profileuser->city) ? $profileuser->city . ', ' : '') . 
                    (!empty($profileuser->country) ? $profileuser->country : 'Not specified') . 
                '</li>
                <li class="list-group-item"><i class="fa fa-clock me-2 text-muted"></i>Last access: ' . 
                    (isset($profileuser->lastaccess) ? format_time($profileuser->lastaccess) : 'Never') . 
                '</li>
            </ul>';

// Show AI insights if viewing own profile
if ($id == $USER->id && $CFG->enableAI) {
    echo '<div class="card-body">
            <h6><i class="fa fa-robot me-2"></i>AI Learning Insights</h6>
            <div class="ai-recommendation p-3">
                <p class="small mb-2">Based on your activity, we\'ve identified your learning style:</p>
                <div class="learning-style-chart mb-3">
                    <div class="row text-center">
                        <div class="col">
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-primary" style="width: 65%"></div>
                            </div>
                            <span class="small">Visual</span>
                        </div>
                        <div class="col">
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-success" style="width: 35%"></div>
                            </div>
                            <span class="small">Auditory</span>
                        </div>
                        <div class="col">
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-warning" style="width: 85%"></div>
                            </div>
                            <span class="small">Kinesthetic</span>
                        </div>
                    </div>
                </div>
                <p class="small mb-0">You learn best with interactive, hands-on content. We\'ll prioritize these resources in your recommendations.</p>
            </div>
        </div>';
}

echo '  </div>
    </div>
    
    <div class="col-md-8">
        <!-- Profile tabs -->
        <div class="card">
            <div class="card-header">
                <ul class="nav nav-tabs card-header-tabs" id="profileTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="courses-tab" data-bs-toggle="tab" data-bs-target="#courses" type="button" role="tab" aria-controls="courses" aria-selected="true">
                            <i class="fa fa-book me-1"></i> Courses
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="activity-tab" data-bs-toggle="tab" data-bs-target="#activity" type="button" role="tab" aria-controls="activity" aria-selected="false">
                            <i class="fa fa-chart-line me-1"></i> Activity
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="details-tab" data-bs-toggle="tab" data-bs-target="#details" type="button" role="tab" aria-controls="details" aria-selected="false">
                            <i class="fa fa-user me-1"></i> Details
                        </button>
                    </li>';

if ($id == $USER->id) {
    echo '
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="preferences-tab" data-bs-toggle="tab" data-bs-target="#preferences" type="button" role="tab" aria-controls="preferences" aria-selected="false">
                            <i class="fa fa-cog me-1"></i> Preferences
                        </button>
                    </li>';
}

echo '
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content" id="profileTabContent">
                    <!-- Courses tab -->
                    <div class="tab-pane fade show active" id="courses" role="tabpanel" aria-labelledby="courses-tab">';

// Get user's courses
$courses = get_user_courses($id);

if (!empty($courses)) {
    echo '<div class="row row-cols-1 row-cols-md-2 g-4">';
    
    foreach ($courses as $course) {
        echo '<div class="col">
                <div class="card h-100 course-card">
                    <div class="course-image">
                        <i class="fa fa-graduation-cap"></i>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title"><a href="' . $CFG->wwwroot . '/course/view.php?id=' . $course->id . '">' . $course->fullname . '</a></h5>
                        <p class="card-text small">' . (isset($course->summary) ? substr($course->summary, 0, 100) . '...' : 'No description') . '</p>
                    </div>
                    <div class="card-footer">
                        <small class="text-muted">
                            <i class="fa fa-calendar-alt me-1"></i> ' . date('M Y', $course->startdate) . '
                        </small>';
        
        // Show progress if it's the current user
        if ($id == $USER->id) {
            // Simplified progress calculation - in a real implementation, this would be based on actual completion data
            $progress = mt_rand(0, 100);
            echo '<div class="progress mt-2" style="height: 5px;">
                    <div class="progress-bar course-progress" role="progressbar" style="width: ' . $progress . '%;" 
                        aria-valuenow="' . $progress . '" aria-valuemin="0" aria-valuemax="100" data-value="' . $progress . '"></div>
                </div>
                <div class="d-flex justify-content-between mt-1">
                    <small class="text-muted">' . $progress . '% complete</small>';
            
            if ($progress < 100) {
                echo '<a href="' . $CFG->wwwroot . '/course/view.php?id=' . $course->id . '" class="small">Continue <i class="fa fa-arrow-right"></i></a>';
            } else {
                echo '<span class="badge bg-success">Completed</span>';
            }
            
            echo '</div>';
        }
        
        echo '    </div>
                </div>
            </div>';
    }
    
    echo '</div>';
} else {
    echo '<div class="alert alert-info">
            <i class="fa fa-info-circle me-2"></i> No courses found.
        </div>';
}

echo '      </div>
                    
                    <!-- Activity tab -->
                    <div class="tab-pane fade" id="activity" role="tabpanel" aria-labelledby="activity-tab">
                        <h4 class="h5 mb-3">Recent Activity</h4>';

// Get recent activity
$recentActivity = get_recent_activity($id, 10);

if (!empty($recentActivity)) {
    echo '<div class="activity-timeline">';
    
    foreach ($recentActivity as $activity) {
        echo '<div class="activity-item">
                <div class="activity-time">' . format_time($activity->timecreated) . '</div>
                <div class="activity-content">
                    <strong>' . ucfirst($activity->action) . ':</strong> ' . 
                    (isset($activity->course_name) ? 'in ' . $activity->course_name : '') . ' - ' . 
                    $activity->target . '
                </div>
              </div>';
    }
    
    echo '</div>';
} else {
    echo '<div class="alert alert-info">
            <i class="fa fa-info-circle me-2"></i> No recent activity.
        </div>';
}

// Add learning analytics if viewing own profile
if ($id == $USER->id && $CFG->enableAI) {
    echo '<h4 class="h5 mb-3 mt-4">Learning Analytics</h4>
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">Weekly Engagement</h5>
                        <canvas id="engagementChart" width="400" height="200"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">Content Interaction</h5>
                        <canvas id="contentChart" width="400" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="ai-recommendation p-3">
            <h5><i class="fa fa-robot me-2"></i>Personalized Learning Recommendations</h5>
            <p>Based on your activity patterns and learning style, we recommend:</p>
            <ul>
                <li>Schedule more regular study sessions - your best performance is in the morning</li>
                <li>Try interactive simulations - they match your hands-on learning style</li>
                <li>Review course materials more frequently before assignments</li>
            </ul>
            <a href="' . $CFG->wwwroot . '/ai/learning_path.php" class="btn btn-sm btn-outline-primary">
                <i class="fa fa-route me-1"></i> View Personalized Learning Path
            </a>
        </div>';
}

echo '      </div>
                    
                    <!-- Details tab -->
                    <div class="tab-pane fade" id="details" role="tabpanel" aria-labelledby="details-tab">
                        <div class="row">
                            <div class="col-md-6">
                                <h4 class="h5 mb-3">Personal Information</h4>
                                <table class="table">
                                    <tbody>
                                        <tr>
                                            <th scope="row">Full Name</th>
                                            <td>' . $profileuser->firstname . ' ' . $profileuser->lastname . '</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">Email</th>
                                            <td>' . $profileuser->email . '</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">City/Town</th>
                                            <td>' . (!empty($profileuser->city) ? $profileuser->city : 'Not specified') . '</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">Country</th>
                                            <td>' . (!empty($profileuser->country) ? $profileuser->country : 'Not specified') . '</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h4 class="h5 mb-3">Account Information</h4>
                                <table class="table">
                                    <tbody>
                                        <tr>
                                            <th scope="row">Username</th>
                                            <td>' . $profileuser->username . '</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">Role</th>
                                            <td>' . (isset($profileuser->role) ? ucfirst($profileuser->role) : 'User') . '</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">First Access</th>
                                            <td>' . (isset($profileuser->firstaccess) ? format_time($profileuser->firstaccess) : 'Never') . '</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">Last Access</th>
                                            <td>' . (isset($profileuser->lastaccess) ? format_time($profileuser->lastaccess) : 'Never') . '</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <h4 class="h5 mb-3 mt-4">Description</h4>
                        <div class="card">
                            <div class="card-body">
                                ' . (!empty($profileuser->description) ? nl2br(htmlspecialchars($profileuser->description)) : 'No description available.') . '
                            </div>
                        </div>
                    </div>';

// Preferences tab (only for own profile)
if ($id == $USER->id) {
    echo '
                    <!-- Preferences tab -->
                    <div class="tab-pane fade" id="preferences" role="tabpanel" aria-labelledby="preferences-tab">
                        <h4 class="h5 mb-3">Edit Profile</h4>
                        <form method="post" action="" id="profile-edit-form" style="display: none;">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="firstname" class="form-label">First Name *</label>
                                    <input type="text" class="form-control" id="firstname" name="firstname" required
                                        value="' . htmlspecialchars($profileuser->firstname) . '">
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="lastname" class="form-label">Last Name *</label>
                                    <input type="text" class="form-control" id="lastname" name="lastname" required
                                        value="' . htmlspecialchars($profileuser->lastname) . '">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email *</label>
                                <input type="email" class="form-control" id="email" name="email" required
                                    value="' . htmlspecialchars($profileuser->email) . '">
                            </div>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="4">' . 
                                    htmlspecialchars($profileuser->description ?? '') . '</textarea>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="city" class="form-label">City/Town</label>
                                    <input type="text" class="form-control" id="city" name="city"
                                        value="' . htmlspecialchars($profileuser->city ?? '') . '">
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="country" class="form-label">Country</label>
                                    <input type="text" class="form-control" id="country" name="country"
                                        value="' . htmlspecialchars($profileuser->country ?? '') . '">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="timezone" class="form-label">Timezone</label>
                                <select class="form-select" id="timezone" name="timezone">
                                    <option value="99" ' . ($profileuser->timezone == 99 ? 'selected' : '') . '>Server\'s timezone</option>
                                    <option value="0" ' . ($profileuser->timezone == 0 ? 'selected' : '') . '>UTC</option>
                                    <option value="-12" ' . ($profileuser->timezone == -12 ? 'selected' : '') . '>UTC-12:00</option>
                                    <option value="-11" ' . ($profileuser->timezone == -11 ? 'selected' : '') . '>UTC-11:00</option>
                                    <option value="-10" ' . ($profileuser->timezone == -10 ? 'selected' : '') . '>UTC-10:00</option>
                                    <option value="-9.5" ' . ($profileuser->timezone == -9.5 ? 'selected' : '') . '>UTC-09:30</option>
                                    <option value="-9" ' . ($profileuser->timezone == -9 ? 'selected' : '') . '>UTC-09:00</option>
                                    <option value="-8" ' . ($profileuser->timezone == -8 ? 'selected' : '') . '>UTC-08:00</option>
                                    <option value="-7" ' . ($profileuser->timezone == -7 ? 'selected' : '') . '>UTC-07:00</option>
                                    <option value="-6" ' . ($profileuser->timezone == -6 ? 'selected' : '') . '>UTC-06:00</option>
                                    <option value="-5" ' . ($profileuser->timezone == -5 ? 'selected' : '') . '>UTC-05:00</option>
                                    <option value="-4" ' . ($profileuser->timezone == -4 ? 'selected' : '') . '>UTC-04:00</option>
                                    <option value="-3.5" ' . ($profileuser->timezone == -3.5 ? 'selected' : '') . '>UTC-03:30</option>
                                    <option value="-3" ' . ($profileuser->timezone == -3 ? 'selected' : '') . '>UTC-03:00</option>
                                    <option value="-2" ' . ($profileuser->timezone == -2 ? 'selected' : '') . '>UTC-02:00</option>
                                    <option value="-1" ' . ($profileuser->timezone == -1 ? 'selected' : '') . '>UTC-01:00</option>
                                    <option value="1" ' . ($profileuser->timezone == 1 ? 'selected' : '') . '>UTC+01:00</option>
                                    <option value="2" ' . ($profileuser->timezone == 2 ? 'selected' : '') . '>UTC+02:00</option>
                                    <option value="3" ' . ($profileuser->timezone == 3 ? 'selected' : '') . '>UTC+03:00</option>
                                    <option value="3.5" ' . ($profileuser->timezone == 3.5 ? 'selected' : '') . '>UTC+03:30</option>
                                    <option value="4" ' . ($profileuser->timezone == 4 ? 'selected' : '') . '>UTC+04:00</option>
                                    <option value="4.5" ' . ($profileuser->timezone == 4.5 ? 'selected' : '') . '>UTC+04:30</option>
                                    <option value="5" ' . ($profileuser->timezone == 5 ? 'selected' : '') . '>UTC+05:00</option>
                                    <option value="5.5" ' . ($profileuser->timezone == 5.5 ? 'selected' : '') . '>UTC+05:30</option>
                                    <option value="5.75" ' . ($profileuser->timezone == 5.75 ? 'selected' : '') . '>UTC+05:45</option>
                                    <option value="6" ' . ($profileuser->timezone == 6 ? 'selected' : '') . '>UTC+06:00</option>
                                    <option value="6.5" ' . ($profileuser->timezone == 6.5 ? 'selected' : '') . '>UTC+06:30</option>
                                    <option value="7" ' . ($profileuser->timezone == 7 ? 'selected' : '') . '>UTC+07:00</option>
                                    <option value="8" ' . ($profileuser->timezone == 8 ? 'selected' : '') . '>UTC+08:00</option>
                                    <option value="8.75" ' . ($profileuser->timezone == 8.75 ? 'selected' : '') . '>UTC+08:45</option>
                                    <option value="9" ' . ($profileuser->timezone == 9 ? 'selected' : '') . '>UTC+09:00</option>
                                    <option value="9.5" ' . ($profileuser->timezone == 9.5 ? 'selected' : '') . '>UTC+09:30</option>
                                    <option value="10" ' . ($profileuser->timezone == 10 ? 'selected' : '') . '>UTC+10:00</option>
                                    <option value="10.5" ' . ($profileuser->timezone == 10.5 ? 'selected' : '') . '>UTC+10:30</option>
                                    <option value="11" ' . ($profileuser->timezone == 11 ? 'selected' : '') . '>UTC+11:00</option>
                                    <option value="12" ' . ($profileuser->timezone == 12 ? 'selected' : '') . '>UTC+12:00</option>
                                    <option value="12.75" ' . ($profileuser->timezone == 12.75 ? 'selected' : '') . '>UTC+12:45</option>
                                    <option value="13" ' . ($profileuser->timezone == 13 ? 'selected' : '') . '>UTC+13:00</option>
                                    <option value="14" ' . ($profileuser->timezone == 14 ? 'selected' : '') . '>UTC+14:00</option>
                                </select>
                            </div>
                            
                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-save me-1"></i> Save Changes
                                </button>
                                <button type="button" class="btn btn-secondary" id="cancel-edit-btn">
                                    Cancel
                                </button>
                            </div>
                        </form>
                        
                        <h4 class="h5 mb-3 mt-4">System Preferences</h4>
                        <div class="card mb-4">
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="lang" class="form-label">Language</label>
                                    <select class="form-select" id="lang">
                                        <option value="en" selected>English</option>
                                        <option value="es">Español</option>
                                        <option value="fr">Français</option>
                                        <option value="de">Deutsch</option>
                                        <option value="zh">中文</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3 form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="enableNotifications" checked>
                                    <label class="form-check-label" for="enableNotifications">Enable notifications</label>
                                </div>
                                
                                <div class="mb-3 form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="enableAI" checked>
                                    <label class="form-check-label" for="enableAI">Enable AI features</label>
                                </div>
                            </div>
                        </div>
                        
                        <h4 class="h5 mb-3">AI Preferences</h4>
                        <div class="card">
                            <div class="card-body">
                                <p>Configure how AI features work for you:</p>
                                
                                <div class="mb-3">
                                    <label for="learningStyle" class="form-label">Preferred Learning Style</label>
                                    <select class="form-select" id="learningStyle">
                                        <option value="visual">Visual</option>
                                        <option value="auditory">Auditory</option>
                                        <option value="kinesthetic" selected>Kinesthetic (Hands-on)</option>
                                        <option value="reading">Reading/Writing</option>
                                        <option value="multimodal">Multimodal</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="contentLevel" class="form-label">Content Difficulty Preference</label>
                                    <select class="form-select" id="contentLevel">
                                        <option value="easier">Easier - I prefer simpler explanations</option>
                                        <option value="matched" selected>Matched - Content at my current level</option>
                                        <option value="challenging">Challenging - I like to be pushed</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3 form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="aiSuggestions" checked>
                                    <label class="form-check-label" for="aiSuggestions">Show AI content suggestions</label>
                                </div>
                                
                                <div class="mb-3 form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="aiFeedback" checked>
                                    <label class="form-check-label" for="aiFeedback">Enable AI feedback on assignments</label>
                                </div>
                                
                                <div class="mb-3 form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="dataSharingForAI" checked>
                                    <label class="form-check-label" for="dataSharingForAI">Share my learning data to improve AI recommendations</label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <button type="button" class="btn btn-primary" id="save-preferences-btn">
                                <i class="fa fa-save me-1"></i> Save Preferences
                            </button>
                        </div>
                    </div>';
}

echo '
                </div>
            </div>
        </div>
    </div>
</div>';

// Add JS for charts and profile editing
echo '<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    // Edit profile functionality
    const editProfileBtn = document.getElementById("edit-profile-btn");
    const cancelEditBtn = document.getElementById("cancel-edit-btn");
    const profileEditForm = document.getElementById("profile-edit-form");
    
    if (editProfileBtn && cancelEditBtn && profileEditForm) {
        editProfileBtn.addEventListener("click", function() {
            profileEditForm.style.display = "block";
            document.getElementById("preferences-tab").click();
        });
        
        cancelEditBtn.addEventListener("click", function() {
            profileEditForm.style.display = "none";
        });
    }
    
    // Save preferences button
    const savePreferencesBtn = document.getElementById("save-preferences-btn");
    if (savePreferencesBtn) {
        savePreferencesBtn.addEventListener("click", function() {
            // Simulate saving preferences
            showNotification("Preferences saved successfully", "success");
        });
    }
    
    // Initialize charts if they exist
    if (document.getElementById("engagementChart")) {
        const engagementCtx = document.getElementById("engagementChart").getContext("2d");
        const engagementChart = new Chart(engagementCtx, {
            type: "line",
            data: {
                labels: ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"],
                datasets: [{
                    label: "Hours Spent",
                    data: [1.5, 2.3, 0.8, 3.2, 2.7, 0.5, 1.9],
                    borderColor: "rgba(87, 98, 213, 1)",
                    backgroundColor: "rgba(87, 98, 213, 0.1)",
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 5
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    }
    
    if (document.getElementById("contentChart")) {
        const contentCtx = document.getElementById("contentChart").getContext("2d");
        const contentChart = new Chart(contentCtx, {
            type: "doughnut",
            data: {
                labels: ["Videos", "Readings", "Quizzes", "Discussions", "Assignments"],
                datasets: [{
                    data: [25, 15, 30, 10, 20],
                    backgroundColor: [
                        "rgba(255, 99, 132, 0.7)",
                        "rgba(54, 162, 235, 0.7)",
                        "rgba(255, 206, 86, 0.7)",
                        "rgba(75, 192, 192, 0.7)",
                        "rgba(153, 102, 255, 0.7)"
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: "right"
                    }
                }
            }
        });
    }
});

// Function to show notification
function showNotification(message, type = "info", duration = 3000) {
    // Create notification container if it doesn\'t exist
    let container = document.getElementById("notification-container");
    if (!container) {
        container = document.createElement("div");
        container.id = "notification-container";
        container.style.position = "fixed";
        container.style.top = "20px";
        container.style.right = "20px";
        container.style.zIndex = "9999";
        document.body.appendChild(container);
    }
    
    // Create notification
    const notification = document.createElement("div");
    notification.className = `alert alert-${type} notification fade-in`;
    notification.innerHTML = `
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        ${message}
    `;
    
    // Add to container
    container.appendChild(notification);
    
    // Auto-dismiss after duration
    setTimeout(() => {
        notification.style.opacity = "0";
        setTimeout(() => {
            notification.remove();
        }, 300);
    }, duration);
}
</script>';

// End page
echo $OUTPUT->footer();
