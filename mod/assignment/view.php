<?php
// iTeachXR - Assignment View
// View an assignment

require_once('../../config.php');
require_once($CFG->dirroot . '/lib/moodlelib.php');

// Get assignment ID
$id = required_param('id', PARAM_INT);

// Check if user is logged in
if (!isloggedin()) {
    redirect($CFG->wwwroot . '/login/index.php');
}

// Get assignment information from course_modules table
global $DB;
$sql = "SELECT cm.*, a.*, c.fullname as course_name, c.id as course_id
         FROM {course_modules} cm
         JOIN {modules} m ON m.id = cm.module
         JOIN {assign} a ON a.id = cm.instance
         JOIN {course} c ON c.id = cm.course
         WHERE cm.id = :cmid AND m.name = 'assign'";
$assignment = $DB->get_record_sql($sql, ['cmid' => $id]);

if (!$assignment) {
    print_error('invalidcoursemodule', 'error');
}

// Check if user can access this course
if (!can_access_course($USER->id, $assignment->course_id)) {
    print_error('nopermissions', 'error', '', 'view this assignment');
}

// Get submissions for this user (if any)
$userSubmission = $DB->get_record('assign_submission', [
    'assignment' => $assignment->id,
    'userid' => $USER->id
]);

// Set up page
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('standard');
$PAGE->set_title($assignment->name);
$PAGE->set_heading($assignment->name);
$PAGE->set_url(new moodle_url('/mod/assignment/view.php', ['id' => $id]));

// Start output
echo $OUTPUT->header();

// Breadcrumb navigation
echo '<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="' . $CFG->wwwroot . '/">Home</a></li>
        <li class="breadcrumb-item"><a href="' . $CFG->wwwroot . '/course/view.php?id=' . $assignment->course_id . '">' . $assignment->course_name . '</a></li>
        <li class="breadcrumb-item active" aria-current="page">' . $assignment->name . '</li>
    </ol>
</nav>';

// Assignment details
echo '<div class="card mb-4">
    <div class="card-header">
        <h3 class="mb-0">Assignment Details</h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-8">
                <h4>' . $assignment->name . '</h4>
                <div class="assignment-description mt-3">
                    ' . $assignment->intro . '
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-light">
                        <h5 class="mb-0 h6">Key Information</h5>
                    </div>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            <strong><i class="fa fa-calendar me-2"></i>Due Date:</strong>
                            <span class="float-end">' . format_time($assignment->duedate, 'strftimedatetime') . '</span>
                        </li>';

// Calculate time remaining
$timeRemaining = $assignment->duedate - time();
$isOverdue = $timeRemaining < 0;
$statusClass = $isOverdue ? 'text-danger' : ($timeRemaining < 86400 ? 'text-warning' : 'text-success');

echo '          <li class="list-group-item">
                            <strong><i class="fa fa-clock me-2"></i>Status:</strong>
                            <span class="float-end ' . $statusClass . '">';

if ($isOverdue) {
    echo 'Overdue by ' . format_time_duration(abs($timeRemaining));
} else {
    echo format_time_duration($timeRemaining) . ' remaining';
}

echo '                  </span>
                        </li>';

// Show submission status
echo '          <li class="list-group-item">
                            <strong><i class="fa fa-upload me-2"></i>Submission Status:</strong>
                            <span class="float-end">';

if ($userSubmission) {
    if ($userSubmission->status === 'submitted') {
        echo '<span class="badge bg-success">Submitted</span>';
    } elseif ($userSubmission->status === 'draft') {
        echo '<span class="badge bg-warning">Draft</span>';
    }
    
    if (isset($userSubmission->grade)) {
        echo ' <span class="badge bg-info">Graded: ' . $userSubmission->grade . '</span>';
    }
} else {
    echo '<span class="badge bg-secondary">Not Submitted</span>';
}

echo '                  </span>
                        </li>';

// Show max points
echo '          <li class="list-group-item">
                            <strong><i class="fa fa-star me-2"></i>Maximum Points:</strong>
                            <span class="float-end">' . ($assignment->grade ?: 100) . '</span>
                        </li>
                    </ul>
                </div>';

// Show submission button or submitted info
if (!$userSubmission || ($userSubmission && $userSubmission->status !== 'submitted') || $isOverdue) {
    echo '<div class="d-grid gap-2 mt-3">
            <a href="' . $CFG->wwwroot . '/mod/assignment/submit.php?id=' . $id . '" class="btn btn-primary">
                <i class="fa fa-upload me-1"></i> ' . ($userSubmission ? 'Edit Submission' : 'Submit Assignment') . '
            </a>
        </div>';
}

// Show AI assistant for this assignment
if ($CFG->enableAI && $CFG->aiFeatures['chatbot_assistant']) {
    echo '<div class="ai-recommendation mt-3">
            <h5><i class="fa fa-robot me-2"></i>Assignment AI Assistant</h5>
            <p class="small">Get help understanding this assignment:</p>
            <div class="d-grid gap-2">
                <button class="btn btn-sm btn-outline-primary" id="explainAssignmentBtn">
                    <i class="fa fa-question-circle me-1"></i> Explain This Assignment
                </button>
                <button class="btn btn-sm btn-outline-info" id="startingPointsBtn">
                    <i class="fa fa-lightbulb me-1"></i> Suggest Starting Points
                </button>
                <button class="btn btn-sm btn-outline-secondary" id="resourceSuggestionsBtn">
                    <i class="fa fa-search me-1"></i> Find Relevant Resources
                </button>
            </div>
            <div id="aiAssistantResponse" class="mt-2" style="display:none;">
                <div class="card">
                    <div class="card-body">
                        <div id="aiAssistantContent"></div>
                    </div>
                </div>
            </div>
        </div>';
}

echo '      </div>
        </div>
    </div>
</div>';

// For teachers, show submission management
if (has_capability('moodle/course:update', context_system::instance())) {
    echo '<div class="card mb-4">
        <div class="card-header">
            <h3 class="mb-0">Submissions</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Status</th>
                            <th>Submission Date</th>
                            <th>Grade</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>';
    
    // Get all submissions for this assignment
    $submissions = get_assignment_submissions($assignment->id);
    
    if ($submissions) {
        foreach ($submissions as $submission) {
            echo '<tr>
                <td>' . $submission->firstname . ' ' . $submission->lastname . '</td>
                <td>';
                
            if ($submission->status === 'submitted') {
                echo '<span class="badge bg-success">Submitted</span>';
            } elseif ($submission->status === 'draft') {
                echo '<span class="badge bg-warning">Draft</span>';
            } else {
                echo '<span class="badge bg-secondary">Not Submitted</span>';
            }
                
            echo '</td>
                <td>' . format_time($submission->timemodified) . '</td>
                <td>';
                
            if (isset($submission->grade)) {
                echo $submission->grade . ' / ' . ($assignment->grade ?: 100);
            } else {
                echo '<span class="text-muted">Not graded</span>';
            }
                
            echo '</td>
                <td>
                    <div class="btn-group btn-group-sm">
                        <a href="' . $CFG->wwwroot . '/mod/assignment/viewsubmission.php?id=' . $submission->id . '" class="btn btn-primary">
                            <i class="fa fa-eye me-1"></i> View
                        </a>
                        <a href="' . $CFG->wwwroot . '/mod/assignment/grade.php?id=' . $submission->id . '" class="btn btn-success">
                            <i class="fa fa-check me-1"></i> Grade
                        </a>
                    </div>
                </td>
            </tr>';
        }
    } else {
        echo '<tr>
            <td colspan="5" class="text-center">No submissions yet</td>
        </tr>';
    }
    
    echo '      </tbody>
                </table>
            </div>';
    
    // AI-assisted grading
    if ($CFG->enableAI && $CFG->aiFeatures['automated_feedback']) {
        echo '<div class="mt-4">
            <h4 class="h5"><i class="fa fa-robot me-2"></i>AI-Assisted Grading</h4>
            <p>Use AI to help provide consistent feedback and grading suggestions:</p>
            <div class="d-flex flex-wrap gap-2">
                <button class="btn btn-outline-primary" id="batchAiFeedbackBtn">
                    <i class="fa fa-magic me-1"></i> Generate Batch Feedback
                </button>
                <button class="btn btn-outline-info" id="gradingRubricBtn">
                    <i class="fa fa-list-alt me-1"></i> Suggest Grading Rubric
                </button>
                <button class="btn btn-outline-warning" id="plagiarismCheckBtn">
                    <i class="fa fa-search me-1"></i> Check for Plagiarism
                </button>
            </div>
        </div>';
    }
    
    echo '</div>
    </div>';
}

// Add JavaScript for interactions
echo '<script>
document.addEventListener("DOMContentLoaded", function() {
    // AI Assistant buttons
    const explainAssignmentBtn = document.getElementById("explainAssignmentBtn");
    const startingPointsBtn = document.getElementById("startingPointsBtn");
    const resourceSuggestionsBtn = document.getElementById("resourceSuggestionsBtn");
    const aiAssistantResponse = document.getElementById("aiAssistantResponse");
    const aiAssistantContent = document.getElementById("aiAssistantContent");
    
    // AI-assisted grading buttons
    const batchAiFeedbackBtn = document.getElementById("batchAiFeedbackBtn");
    const gradingRubricBtn = document.getElementById("gradingRubricBtn");
    const plagiarismCheckBtn = document.getElementById("plagiarismCheckBtn");
    
    // Handle explanation request
    if (explainAssignmentBtn) {
        explainAssignmentBtn.addEventListener("click", function() {
            showAILoading();
            
            fetch("' . $CFG->wwwroot . '/api/ai_service.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    action: "explain_assignment",
                    assignment_id: ' . $assignment->id . ',
                    assignment_name: "' . addslashes($assignment->name) . '",
                    assignment_description: "' . addslashes($assignment->intro) . '"
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayAIResponse(data.explanation);
                } else {
                    displayAIError(data.message);
                }
            })
            .catch(error => {
                console.error("Error:", error);
                displayAIError("An error occurred while processing your request");
            });
        });
    }
    
    // Handle starting points suggestion
    if (startingPointsBtn) {
        startingPointsBtn.addEventListener("click", function() {
            showAILoading();
            
            fetch("' . $CFG->wwwroot . '/api/ai_service.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    action: "suggest_starting_points",
                    assignment_id: ' . $assignment->id . ',
                    assignment_name: "' . addslashes($assignment->name) . '",
                    assignment_description: "' . addslashes($assignment->intro) . '"
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayAIResponse(data.starting_points);
                } else {
                    displayAIError(data.message);
                }
            })
            .catch(error => {
                console.error("Error:", error);
                displayAIError("An error occurred while processing your request");
            });
        });
    }
    
    // Handle resource suggestions
    if (resourceSuggestionsBtn) {
        resourceSuggestionsBtn.addEventListener("click", function() {
            showAILoading();
            
            fetch("' . $CFG->wwwroot . '/api/ai_service.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    action: "find_resources",
                    assignment_id: ' . $assignment->id . ',
                    assignment_name: "' . addslashes($assignment->name) . '",
                    assignment_description: "' . addslashes($assignment->intro) . '"
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    let resourcesHTML = "<h5>Recommended Resources</h5><ul>";
                    data.resources.forEach(resource => {
                        resourcesHTML += `<li><strong>${resource.title}</strong>: ${resource.description}`;
                        if (resource.url) {
                            resourcesHTML += ` <a href="${resource.url}" target="_blank" class="small">View Resource <i class="fa fa-external-link-alt"></i></a>`;
                        }
                        resourcesHTML += "</li>";
                    });
                    resourcesHTML += "</ul>";
                    displayAIResponse(resourcesHTML);
                } else {
                    displayAIError(data.message);
                }
            })
            .catch(error => {
                console.error("Error:", error);
                displayAIError("An error occurred while processing your request");
            });
        });
    }
    
    // Function to show loading message
    function showAILoading() {
        aiAssistantResponse.style.display = "block";
        aiAssistantContent.innerHTML = `
            <div class="text-center p-3">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Analyzing assignment...</p>
            </div>
        `;
    }
    
    // Function to display AI response
    function displayAIResponse(content) {
        aiAssistantResponse.style.display = "block";
        aiAssistantContent.innerHTML = content;
    }
    
    // Function to display error
    function displayAIError(message) {
        aiAssistantResponse.style.display = "block";
        aiAssistantContent.innerHTML = `
            <div class="alert alert-danger">
                <p><i class="fa fa-exclamation-circle me-2"></i>${message}</p>
            </div>
        `;
    }
    
    // Handle batch AI feedback
    if (batchAiFeedbackBtn) {
        batchAiFeedbackBtn.addEventListener("click", function() {
            alert("Generating batch feedback for all submissions...");
            // This would call the AI service to process all submissions
        });
    }
    
    // Handle grading rubric generation
    if (gradingRubricBtn) {
        gradingRubricBtn.addEventListener("click", function() {
            alert("Generating suggested grading rubric based on assignment description...");
            // This would call the AI service to generate a rubric
        });
    }
    
    // Handle plagiarism check
    if (plagiarismCheckBtn) {
        plagiarismCheckBtn.addEventListener("click", function() {
            alert("Running plagiarism checks on all submissions...");
            // This would call the AI service to check for plagiarism
        });
    }
});
</script>';

// End page
echo $OUTPUT->footer();

/**
 * Format a time duration in a human-readable format
 * @param int $seconds Time in seconds
 * @return string Formatted duration
 */
function format_time_duration($seconds) {
    if ($seconds < 60) {
        return "$seconds seconds";
    } elseif ($seconds < 3600) {
        $minutes = floor($seconds / 60);
        return "$minutes " . ($minutes == 1 ? "minute" : "minutes");
    } elseif ($seconds < 86400) {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        return "$hours " . ($hours == 1 ? "hour" : "hours") . 
               ($minutes > 0 ? ", $minutes " . ($minutes == 1 ? "minute" : "minutes") : "");
    } else {
        $days = floor($seconds / 86400);
        $hours = floor(($seconds % 86400) / 3600);
        return "$days " . ($days == 1 ? "day" : "days") . 
               ($hours > 0 ? ", $hours " . ($hours == 1 ? "hour" : "hours") : "");
    }
}
