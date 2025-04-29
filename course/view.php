<?php
// iTeachXR - Course View
// View a course and its contents

require_once('../config.php');
require_once($CFG->dirroot . '/lib/moodlelib.php');

// Get the course ID from the URL
$id = optional_param('id', 0, PARAM_INT);

// Redirect to course list if no ID provided
if (!$id) {
    redirect($CFG->wwwroot . '/course/index.php');
}

// Get the course
$course = get_course($id);
if (!$course) {
    print_error('invalidcourseid', 'error', '', $id);
}

// Check if user can access this course
if (!can_access_course($USER->id, $id)) {
    print_error('coursenotaccessible', 'error');
}

// Get course modules
$modules = get_course_modules($id);

// Get AI content recommendations for this user and course
$recommendations = get_ai_content_recommendations($USER->id, $id);

// Set up page
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('course');
$PAGE->set_title($course->fullname);
$PAGE->set_heading($course->fullname);
$PAGE->set_url(new moodle_url('/course/view.php', array('id' => $id)));

// Start output
echo $OUTPUT->header();

// Course header with image and details
echo '<div class="course-header mb-4">
    <div class="course-header-image">
        <div class="course-image-placeholder">
            <i class="fa fa-graduation-cap"></i>
        </div>
    </div>
    <div class="course-header-info p-4">
        <h2>' . $course->fullname . '</h2>
        <p class="course-description">' . $course->summary . '</p>
        <div class="course-meta">
            <span><i class="fa fa-user-circle"></i> ' . (isset($course->teacher) ? $course->teacher : 'Unknown Teacher') . '</span>
            <span><i class="fa fa-calendar"></i> ' . date('M Y', $course->startdate) . '</span>
        </div>
    </div>
</div>';

// Main course content in tabs
echo '<div class="mb-4">
    <ul class="nav nav-tabs" id="courseTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="content-tab" data-bs-toggle="tab" data-bs-target="#content" type="button" role="tab" aria-controls="content" aria-selected="true">
                <i class="fa fa-book-open me-1"></i> Content
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="assignments-tab" data-bs-toggle="tab" data-bs-target="#assignments" type="button" role="tab" aria-controls="assignments" aria-selected="false">
                <i class="fa fa-tasks me-1"></i> Assignments
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="discussion-tab" data-bs-toggle="tab" data-bs-target="#discussion" type="button" role="tab" aria-controls="discussion" aria-selected="false">
                <i class="fa fa-comments me-1"></i> Discussion
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="resources-tab" data-bs-toggle="tab" data-bs-target="#resources" type="button" role="tab" aria-controls="resources" aria-selected="false">
                <i class="fa fa-file me-1"></i> Resources
            </button>
        </li>
    </ul>
    <div class="tab-content border border-top-0 rounded-bottom p-4" id="courseTabContent">
        <div class="tab-pane fade show active" id="content" role="tabpanel" aria-labelledby="content-tab">
            <div class="row">';

// Show AI recommendations if available
if (!empty($recommendations)) {
    echo '<div class="col-md-4 mb-4">
        <div class="ai-recommendation p-3">
            <h4><i class="fa fa-robot me-2"></i>Personalized Learning Path</h4>
            <p>Based on your learning style and progress, here are recommended resources:</p>
            <ul class="list-group list-group-flush">';
            
    foreach ($recommendations as $recommendation) {
        echo '<li class="list-group-item bg-transparent">
            <a href="' . $recommendation['url'] . '">
                <strong>' . $recommendation['title'] . '</strong>
            </a>
            <p class="mb-0 small text-muted">' . $recommendation['reason'] . '</p>
        </li>';
    }
            
    echo '</ul>
            <div class="mt-3">
                <a href="' . $CFG->wwwroot . '/ai/learning_path.php?course=' . $id . '" class="btn btn-sm btn-outline-primary">
                    <i class="fa fa-route me-1"></i> View Full Learning Path
                </a>
            </div>
        </div>
    </div>';
    
    echo '<div class="col-md-8">';
} else {
    echo '<div class="col-12">';
}

// Display course sections and modules
echo '<div class="course-sections">';

// Group modules by section
$sections = array();
foreach ($modules as $module) {
    if (!isset($sections[$module->section])) {
        $sections[$module->section] = array();
    }
    $sections[$module->section][] = $module;
}

// Display each section
foreach ($sections as $sectionNum => $sectionModules) {
    $sectionName = "Section " . $sectionNum;
    
    echo '<div class="course-section card mb-4">
        <div class="card-header">
            <h3 class="h5 mb-0">' . $sectionName . '</h3>
        </div>
        <div class="card-body">
            <div class="list-group list-group-flush">';
    
    // Display each module in this section
    foreach ($sectionModules as $module) {
        $moduleIcon = 'fa-file-alt';
        $moduleTypeClass = '';
        
        // Set appropriate icon based on module type
        if ($module->modname === 'assign') {
            $moduleIcon = 'fa-tasks';
            $moduleTypeClass = 'text-danger';
        } elseif ($module->modname === 'forum') {
            $moduleIcon = 'fa-comments';
            $moduleTypeClass = 'text-primary';
        } elseif ($module->modname === 'quiz') {
            $moduleIcon = 'fa-question-circle';
            $moduleTypeClass = 'text-warning';
        } elseif ($module->modname === 'resource') {
            $moduleIcon = 'fa-file';
            $moduleTypeClass = 'text-info';
        }
        
        echo '<a href="' . $CFG->wwwroot . '/mod/' . $module->modname . '/view.php?id=' . $module->id . '" 
                 class="list-group-item list-group-item-action">
                <div class="d-flex w-100 justify-content-between align-items-center">
                    <div>
                        <i class="fa ' . $moduleIcon . ' me-2 ' . $moduleTypeClass . '"></i>
                        <span>' . $module->name . '</span>
                    </div>
                    <span class="badge bg-light text-dark">
                        ' . ucfirst($module->modname) . '
                    </span>
                </div>
            </a>';
    }
    
    echo '</div></div></div>';
}

echo '</div>'; // End course-sections
echo '</div>'; // End column
echo '</div>'; // End row

// Assignments tab content
echo '<div class="tab-pane fade" id="assignments" role="tabpanel" aria-labelledby="assignments-tab">';

// Get assignments for this course
$assignments = get_course_assignments($id);

if (!empty($assignments)) {
    echo '<div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Assignment</th>
                    <th>Due Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>';
    
    foreach ($assignments as $assignment) {
        // Determine status (for demo purposes - would be queried from DB in real implementation)
        $status = 'Not submitted';
        $statusClass = 'status-not-submitted';
        
        if (isset($assignment->submitted) && $assignment->submitted) {
            if (isset($assignment->graded) && $assignment->graded) {
                $status = 'Graded';
                $statusClass = 'status-graded';
            } else {
                $status = 'Submitted';
                $statusClass = 'status-submitted';
            }
        } elseif ($assignment->duedate < time()) {
            $status = 'Overdue';
            $statusClass = 'status-overdue';
        }
        
        echo '<tr>
            <td>' . $assignment->name . '</td>
            <td>' . format_time($assignment->duedate, 'strftimedate') . '</td>
            <td><span class="assignment-status ' . $statusClass . '">' . $status . '</span></td>
            <td>
                <a href="' . $CFG->wwwroot . '/mod/assignment/view.php?id=' . $assignment->cmid . '" class="btn btn-sm btn-primary">
                    <i class="fa fa-eye me-1"></i> View
                </a>';
                
        if ($status !== 'Graded') {
            echo ' <a href="' . $CFG->wwwroot . '/mod/assignment/submit.php?id=' . $assignment->cmid . '" class="btn btn-sm btn-success">
                <i class="fa fa-upload me-1"></i> Submit
            </a>';
        }
                
        echo '</td></tr>';
    }
    
    echo '</tbody></table></div>';
} else {
    echo '<div class="alert alert-info">
        <i class="fa fa-info-circle me-2"></i> No assignments have been added to this course yet.
    </div>';
}

echo '</div>';

// Discussion tab content
echo '<div class="tab-pane fade" id="discussion" role="tabpanel" aria-labelledby="discussion-tab">';

// Simplified forum display
echo '<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="h5 mb-0">Discussion Forums</h3>
    <a href="' . $CFG->wwwroot . '/mod/forum/post.php?course=' . $id . '" class="btn btn-primary btn-sm">
        <i class="fa fa-plus me-1"></i> New Discussion
    </a>
</div>';

// Demo forum topics
echo '<div class="card mb-3">
    <div class="card-body">
        <div class="d-flex justify-content-between">
            <h4 class="h6"><a href="' . $CFG->wwwroot . '/mod/forum/discuss.php?d=1">Welcome to the course</a></h4>
            <span class="badge bg-secondary">5 replies</span>
        </div>
        <p class="mb-0 small text-muted">Started by Teacher, ' . date('M d, Y', time() - 604800) . '</p>
    </div>
</div>
<div class="card mb-3">
    <div class="card-body">
        <div class="d-flex justify-content-between">
            <h4 class="h6"><a href="' . $CFG->wwwroot . '/mod/forum/discuss.php?d=2">Questions about Assignment 1</a></h4>
            <span class="badge bg-secondary">12 replies</span>
        </div>
        <p class="mb-0 small text-muted">Started by Student, ' . date('M d, Y', time() - 432000) . '</p>
    </div>
</div>
<div class="card mb-3">
    <div class="card-body">
        <div class="d-flex justify-content-between">
            <h4 class="h6"><a href="' . $CFG->wwwroot . '/mod/forum/discuss.php?d=3">Additional resources for Chapter 3</a></h4>
            <span class="badge bg-secondary">2 replies</span>
        </div>
        <p class="mb-0 small text-muted">Started by Teacher, ' . date('M d, Y', time() - 259200) . '</p>
    </div>
</div>';

echo '</div>';

// Resources tab content
echo '<div class="tab-pane fade" id="resources" role="tabpanel" aria-labelledby="resources-tab">';

echo '<div class="row">
    <div class="col-md-8">
        <h3 class="h5 mb-3">Course Materials</h3>
        <div class="list-group mb-4">';

// Display sample resources (in a real implementation, these would be queried from DB)
$resources = [
    ['name' => 'Course Syllabus', 'type' => 'pdf', 'size' => '420 KB', 'date' => time() - 1209600],
    ['name' => 'Lecture Notes - Week 1', 'type' => 'pdf', 'size' => '1.2 MB', 'date' => time() - 1036800],
    ['name' => 'Reading List', 'type' => 'docx', 'size' => '140 KB', 'date' => time() - 864000],
    ['name' => 'Tutorial Worksheet', 'type' => 'pdf', 'size' => '350 KB', 'date' => time() - 604800],
    ['name' => 'Project Guidelines', 'type' => 'pdf', 'size' => '580 KB', 'date' => time() - 432000]
];

foreach ($resources as $resource) {
    $icon = 'fa-file-pdf';
    if ($resource['type'] === 'docx') {
        $icon = 'fa-file-word';
    } elseif ($resource['type'] === 'pptx') {
        $icon = 'fa-file-powerpoint';
    }
    
    echo '<a href="#" class="list-group-item list-group-item-action">
        <div class="d-flex w-100 justify-content-between align-items-center">
            <div>
                <i class="fa ' . $icon . ' me-2 text-danger"></i>
                <span>' . $resource['name'] . '</span>
                <span class="badge bg-light text-dark ms-2">' . $resource['size'] . '</span>
            </div>
            <small>' . format_time($resource['date'], 'strftimedate') . '</small>
        </div>
    </a>';
}

echo '</div>
        
        <h3 class="h5 mb-3">External Resources</h3>
        <div class="list-group">';

$external_resources = [
    ['name' => 'Introduction to the Topic (Video)', 'url' => 'https://www.youtube.com/watch?v=example'],
    ['name' => 'Additional Reading - Journal Article', 'url' => 'https://doi.org/example'],
    ['name' => 'Interactive Tutorial', 'url' => 'https://www.example.com/tutorial']
];

foreach ($external_resources as $resource) {
    echo '<a href="' . $resource['url'] . '" target="_blank" class="list-group-item list-group-item-action">
        <div class="d-flex w-100 justify-content-between align-items-center">
            <div>
                <i class="fa fa-external-link-alt me-2 text-primary"></i>
                <span>' . $resource['name'] . '</span>
            </div>
            <i class="fa fa-external-link-alt"></i>
        </div>
    </a>';
}

echo '</div>
    </div>
    
    <div class="col-md-4">
        <div class="ai-recommendation p-3">
            <h4><i class="fa fa-robot me-2"></i>AI Study Tools</h4>
            <p>Enhance your learning with these AI-powered tools:</p>
            <div class="list-group list-group-flush">
                <a href="' . $CFG->wwwroot . '/ai/flashcards.php?course=' . $id . '" class="list-group-item bg-transparent">
                    <i class="fa fa-clone me-2"></i> Generate Flashcards
                </a>
                <a href="' . $CFG->wwwroot . '/ai/summarize.php?course=' . $id . '" class="list-group-item bg-transparent">
                    <i class="fa fa-file-alt me-2"></i> Summarize Content
                </a>
                <a href="' . $CFG->wwwroot . '/ai/practice_quiz.php?course=' . $id . '" class="list-group-item bg-transparent">
                    <i class="fa fa-question-circle me-2"></i> Generate Practice Quiz
                </a>
                <a href="' . $CFG->wwwroot . '/ai/explain.php?course=' . $id . '" class="list-group-item bg-transparent">
                    <i class="fa fa-lightbulb me-2"></i> Explain Concepts
                </a>
            </div>
        </div>
    </div>
</div>';

echo '</div>';
echo '</div>'; // End tab content
echo '</div>'; // End tabs container

// End page
echo $OUTPUT->footer();
