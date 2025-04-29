<?php
// iTeachXR - Assignment Submission Page
// Submit an assignment

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
    print_error('nopermissions', 'error', '', 'submit to this assignment');
}

// Check if submission is past due date
$isOverdue = time() > $assignment->duedate;

// Get existing submission if any
$userSubmission = $DB->get_record('assign_submission', [
    'assignment' => $assignment->id,
    'userid' => $USER->id
]);

// Process form submission
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submissiontext = optional_param('submissiontext', '', PARAM_TEXT);
    $submissiontype = optional_param('submissiontype', 'text', PARAM_ALPHA);
    $submitaction = optional_param('submitaction', '', PARAM_ALPHA);
    
    // Validate submission
    if (empty($submissiontext) && $submissiontype === 'text') {
        $message = 'Please enter some text for your submission.';
        $messageType = 'danger';
    } else {
        // Prepare submission data
        $submissionData = [
            'assignment' => $assignment->id,
            'userid' => $USER->id,
            'timecreated' => time(),
            'timemodified' => time(),
            'status' => ($submitaction === 'draft') ? 'draft' : 'submitted',
            'attemptnumber' => 1
        ];
        
        if ($submissiontype === 'text') {
            $submissionData['submissiontext'] = $submissiontext;
        }
        
        // Save to database
        try {
            if ($userSubmission) {
                // Update existing submission
                $submissionData['id'] = $userSubmission->id;
                
                if ($userSubmission->attemptnumber) {
                    $submissionData['attemptnumber'] = $userSubmission->attemptnumber + 1;
                }
                
                $DB->update_record('assign_submission', (object)$submissionData);
                
                $message = ($submitaction === 'draft') ? 
                    'Draft saved successfully.' : 
                    'Submission updated successfully.';
            } else {
                // Create new submission
                $DB->insert_record('assign_submission', (object)$submissionData);
                
                $message = ($submitaction === 'draft') ? 
                    'Draft saved successfully.' : 
                    'Assignment submitted successfully.';
            }
            
            $messageType = 'success';
            
            // If this was a final submission (not a draft), redirect to view page
            if ($submitaction !== 'draft') {
                redirect($CFG->wwwroot . '/mod/assignment/view.php?id=' . $id, $message, 3);
            }
            
            // Refresh submission data
            $userSubmission = $DB->get_record('assign_submission', [
                'assignment' => $assignment->id,
                'userid' => $USER->id
            ]);
        } catch (Exception $e) {
            $message = 'Error saving submission: ' . $e->getMessage();
            $messageType = 'danger';
        }
    }
}

// Set up page
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('standard');
$PAGE->set_title('Submit Assignment: ' . $assignment->name);
$PAGE->set_heading('Submit Assignment: ' . $assignment->name);
$PAGE->set_url(new moodle_url('/mod/assignment/submit.php', ['id' => $id]));

// Start output
echo $OUTPUT->header();

// Display message if any
if (!empty($message)) {
    echo '<div class="alert alert-' . $messageType . ' alert-dismissible fade show" role="alert">
        ' . $message . '
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>';
}

// Breadcrumb navigation
echo '<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="' . $CFG->wwwroot . '/">Home</a></li>
        <li class="breadcrumb-item"><a href="' . $CFG->wwwroot . '/course/view.php?id=' . $assignment->course_id . '">' . $assignment->course_name . '</a></li>
        <li class="breadcrumb-item"><a href="' . $CFG->wwwroot . '/mod/assignment/view.php?id=' . $id . '">' . $assignment->name . '</a></li>
        <li class="breadcrumb-item active" aria-current="page">Submit</li>
    </ol>
</nav>';

// Show warning if overdue
if ($isOverdue) {
    echo '<div class="alert alert-warning">
        <i class="fa fa-exclamation-triangle me-2"></i>
        <strong>Warning:</strong> This assignment is now overdue. The due date was ' . format_time($assignment->duedate, 'strftimedatetime') . '.
        You can still submit, but your submission will be marked as late.
    </div>';
}

// Assignment submission form
echo '<div class="card">
    <div class="card-header">
        <h3 class="mb-0">Submit Assignment: ' . $assignment->name . '</h3>
    </div>
    <div class="card-body">
        <form method="post" action="" id="assignment-submission-form" enctype="multipart/form-data" data-assignment-id="' . $assignment->id . '">
            <div class="mb-3">
                <label for="submissiontype" class="form-label">Submission Type</label>
                <select class="form-select" id="submissiontype" name="submissiontype">
                    <option value="text" selected>Text Submission</option>
                    <option value="file" disabled>File Submission (Not available)</option>
                </select>
            </div>
            
            <div id="text-submission-section">
                <div class="mb-3">
                    <label for="submissiontext" class="form-label">Submission Text</label>
                    <textarea class="form-control" id="submissiontext" name="submissiontext" rows="10">' . 
                        (isset($userSubmission->submissiontext) ? htmlspecialchars($userSubmission->submissiontext) : '') . '</textarea>
                </div>';

// Add AI Feedback preview if enabled                
if ($CFG->enableAI && $CFG->aiFeatures['automated_feedback']) {
    echo '<div class="mb-3">
                <button type="button" class="btn btn-outline-primary btn-sm" id="preview-feedback-button">
                    <i class="fa fa-robot me-1"></i> Preview AI Feedback
                </button>
                <div id="ai-feedback-preview" class="mt-3"></div>
            </div>';
}

echo '      </div>
            
            <div id="file-submission-section" style="display: none;">
                <div class="mb-3">
                    <label for="submission-file" class="form-label">Upload File(s)</label>
                    <input class="form-control" type="file" id="submission-file" name="submissionfile" disabled>
                    <div class="form-text">Maximum file size: 20MB. Allowed file types: PDF, DOC, DOCX, PPT, PPTX, XLS, XLSX, ZIP.</div>
                </div>
                <div id="file-preview" class="mb-3"></div>
            </div>
            
            <div class="mt-4 d-flex gap-2">
                <button type="submit" name="submitaction" value="submit" class="btn btn-primary">
                    <i class="fa fa-upload me-1"></i> Submit Assignment
                </button>
                <button type="submit" name="submitaction" value="draft" class="btn btn-secondary">
                    <i class="fa fa-save me-1"></i> Save as Draft
                </button>
                <a href="' . $CFG->wwwroot . '/mod/assignment/view.php?id=' . $id . '" class="btn btn-link">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>';

// Add assignment guidance section (if AI is enabled)
if ($CFG->enableAI) {
    echo '<div class="card mt-4">
        <div class="card-header">
            <h3 class="mb-0"><i class="fa fa-lightbulb me-2"></i>Assignment Guidance</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="ai-recommendation">
                        <h4>Understanding the Assignment</h4>
                        <p>Based on the assignment description, you should focus on:</p>
                        <ul id="assignment-focus-points">
                            <li>Loading assignment focus points...</li>
                        </ul>
                        <button class="btn btn-sm btn-outline-primary" id="refresh-focus-btn">
                            <i class="fa fa-sync me-1"></i> Refresh Analysis
                        </button>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="ai-recommendation">
                        <h4>Resource Suggestions</h4>
                        <p>These resources might help with your assignment:</p>
                        <ul id="assignment-resources">
                            <li>Loading resource suggestions...</li>
                        </ul>
                        <button class="btn btn-sm btn-outline-primary" id="refresh-resources-btn">
                            <i class="fa fa-sync me-1"></i> Refresh Suggestions
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>';
}

// JavaScript for submission page functionality
echo '<script>
document.addEventListener("DOMContentLoaded", function() {
    // Submission type toggle
    const submissionType = document.getElementById("submissiontype");
    const textSubmissionSection = document.getElementById("text-submission-section");
    const fileSubmissionSection = document.getElementById("file-submission-section");
    
    submissionType.addEventListener("change", function() {
        if (this.value === "text") {
            textSubmissionSection.style.display = "block";
            fileSubmissionSection.style.display = "none";
        } else {
            textSubmissionSection.style.display = "none";
            fileSubmissionSection.style.display = "block";
        }
    });
    
    // File preview functionality (disabled for now)
    const fileInput = document.getElementById("submission-file");
    const filePreview = document.getElementById("file-preview");
    
    if (fileInput && filePreview) {
        fileInput.addEventListener("change", function() {
            // Clear previous preview
            filePreview.innerHTML = "";
            
            if (this.files && this.files.length > 0) {
                const file = this.files[0];
                const fileItem = document.createElement("div");
                fileItem.className = "file-item";
                fileItem.innerHTML = `
                    <i class="fa fa-file"></i>
                    <span>${file.name}</span>
                    <small>(${formatFileSize(file.size)})</small>
                `;
                filePreview.appendChild(fileItem);
            }
        });
    }
    
    // AI Feedback preview functionality
    const previewButton = document.getElementById("preview-feedback-button");
    const aiFeedbackPreview = document.getElementById("ai-feedback-preview");
    const submissionText = document.getElementById("submissiontext");
    
    if (previewButton && aiFeedbackPreview && submissionText) {
        previewButton.addEventListener("click", function() {
            const text = submissionText.value.trim();
            if (!text) {
                aiFeedbackPreview.innerHTML = `<div class="alert alert-warning">
                    <i class="fa fa-exclamation-circle me-2"></i>
                    Please enter some text to get feedback
                </div>`;
                return;
            }
            
            // Show loading state
            aiFeedbackPreview.innerHTML = `<div class="text-center p-3">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Analyzing your submission...</p>
            </div>`;
            
            // Call AI service for feedback preview
            fetch("' . $CFG->wwwroot . '/api/ai_service.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    action: "preview_feedback",
                    assignment_id: ' . $assignment->id . ',
                    assignment_name: "' . addslashes($assignment->name) . '",
                    assignment_description: "' . addslashes($assignment->intro) . '",
                    submission_text: text
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Display feedback preview
                    const feedback = data.feedback;
                    aiFeedbackPreview.innerHTML = `
                        <div class="ai-feedback">
                            <h4><i class="fa fa-robot me-2"></i>AI Feedback Preview</h4>
                            <p class="mb-3"><strong>Overall assessment:</strong> ${feedback.overall_assessment}</p>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <h5>Strengths:</h5>
                                    <ul>
                                        ${feedback.strengths.map(item => `<li>${item}</li>`).join("")}
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <h5>Areas for improvement:</h5>
                                    <ul>
                                        ${feedback.areas_for_improvement.map(item => `<li>${item}</li>`).join("")}
                                    </ul>
                                </div>
                            </div>
                            
                            <h5>Specific suggestions:</h5>
                            <ul>
                                ${feedback.specific_suggestions.map(item => `<li>${item}</li>`).join("")}
                            </ul>
                            
                            <div class="alert alert-info mt-3">
                                <i class="fa fa-info-circle me-2"></i>
                                This is an AI-generated preview to help improve your work before final submission.
                                The instructor's feedback may differ.
                            </div>
                        </div>
                    `;
                } else {
                    // Display error
                    aiFeedbackPreview.innerHTML = `<div class="alert alert-danger">
                        <i class="fa fa-exclamation-circle me-2"></i>
                        ${data.message || "An error occurred while generating feedback."}
                    </div>`;
                }
            })
            .catch(error => {
                console.error("Error:", error);
                aiFeedbackPreview.innerHTML = `<div class="alert alert-danger">
                    <i class="fa fa-exclamation-circle me-2"></i>
                    An error occurred while generating feedback. Please try again.
                </div>`;
            });
        });
    }
    
    // AI Guidance functionality
    const refreshFocusBtn = document.getElementById("refresh-focus-btn");
    const assignmentFocusPoints = document.getElementById("assignment-focus-points");
    const refreshResourcesBtn = document.getElementById("refresh-resources-btn");
    const assignmentResources = document.getElementById("assignment-resources");
    
    // Load focus points
    if (refreshFocusBtn && assignmentFocusPoints) {
        loadAssignmentFocus();
        
        refreshFocusBtn.addEventListener("click", function() {
            loadAssignmentFocus();
        });
    }
    
    // Load resource suggestions
    if (refreshResourcesBtn && assignmentResources) {
        loadResourceSuggestions();
        
        refreshResourcesBtn.addEventListener("click", function() {
            loadResourceSuggestions();
        });
    }
    
    function loadAssignmentFocus() {
        assignmentFocusPoints.innerHTML = `<li><div class="spinner-border spinner-border-sm text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div> Analyzing assignment...</li>`;
        
        fetch("' . $CFG->wwwroot . '/api/ai_service.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                action: "analyze_assignment",
                assignment_id: ' . $assignment->id . ',
                assignment_name: "' . addslashes($assignment->name) . '",
                assignment_description: "' . addslashes($assignment->intro) . '"
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.focus_points && data.focus_points.length > 0) {
                assignmentFocusPoints.innerHTML = data.focus_points.map(point => `<li>${point}</li>`).join("");
            } else {
                assignmentFocusPoints.innerHTML = `<li>Could not analyze assignment. Please try again.</li>`;
            }
        })
        .catch(error => {
            console.error("Error:", error);
            assignmentFocusPoints.innerHTML = `<li>Error loading focus points. Please try again.</li>`;
        });
    }
    
    function loadResourceSuggestions() {
        assignmentResources.innerHTML = `<li><div class="spinner-border spinner-border-sm text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div> Finding resources...</li>`;
        
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
            if (data.success && data.resources && data.resources.length > 0) {
                assignmentResources.innerHTML = data.resources.map(resource => {
                    let html = `<li><strong>${resource.title}</strong>: ${resource.description}`;
                    if (resource.url) {
                        html += ` <a href="${resource.url}" target="_blank" class="small">View <i class="fa fa-external-link-alt"></i></a>`;
                    }
                    html += `</li>`;
                    return html;
                }).join("");
            } else {
                assignmentResources.innerHTML = `<li>Could not find resources. Please try again.</li>`;
            }
        })
        .catch(error => {
            console.error("Error:", error);
            assignmentResources.innerHTML = `<li>Error loading resources. Please try again.</li>`;
        });
    }
    
    // Function to format file size
    function formatFileSize(bytes) {
        if (bytes === 0) return "0 Bytes";
        
        const k = 1024;
        const sizes = ["Bytes", "KB", "MB", "GB"];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + " " + sizes[i];
    }
});
</script>';

// End page
echo $OUTPUT->footer();

