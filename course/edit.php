<?php
// iTeachXR - Course Edit Page
// Create or edit a course

require_once('../config.php');
require_once($CFG->dirroot . '/lib/moodlelib.php');

// Check if user is logged in and has permission to edit courses
if (!isloggedin()) {
    redirect($CFG->wwwroot . '/login/index.php');
}

if (!has_capability('moodle/course:update', context_system::instance())) {
    print_error('nopermissions', 'error', '', 'edit courses');
}

// Get course ID if editing an existing course
$id = optional_param('id', 0, PARAM_INT);
$course = null;

if ($id) {
    // Get existing course data
    $course = get_course($id);
    if (!$course) {
        print_error('invalidcourseid', 'error', '', $id);
    }
    
    $PAGE->set_title('Edit Course: ' . $course->fullname);
    $PAGE->set_heading('Edit Course: ' . $course->fullname);
} else {
    // Set up for new course
    $PAGE->set_title('Create New Course');
    $PAGE->set_heading('Create New Course');
}

// Set up page
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('admin');
$PAGE->set_url(new moodle_url('/course/edit.php', array('id' => $id)));

// Process form submission
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = required_param('fullname', PARAM_TEXT);
    $shortname = required_param('shortname', PARAM_TEXT);
    $summary = required_param('summary', PARAM_TEXT);
    $startdate = required_param('startdate', PARAM_INT);
    $enddate = required_param('enddate', PARAM_INT);
    $category = required_param('category', PARAM_INT);
    $format = required_param('format', PARAM_ALPHA);
    $visible = optional_param('visible', 0, PARAM_INT);
    
    // Validate data
    $errors = array();
    
    if (empty($fullname)) {
        $errors[] = 'Course full name is required';
    }
    
    if (empty($shortname)) {
        $errors[] = 'Course short name is required';
    }
    
    if ($enddate < $startdate) {
        $errors[] = 'End date must be after start date';
    }
    
    if (empty($errors)) {
        // Prepare course data
        $coursedata = array(
            'fullname' => $fullname,
            'shortname' => $shortname,
            'summary' => $summary,
            'startdate' => $startdate,
            'enddate' => $enddate,
            'category' => $category,
            'format' => $format,
            'visible' => $visible
        );
        
        // Save to database
        global $DB;
        
        try {
            if ($id) {
                // Update existing course
                $coursedata['id'] = $id;
                $DB->update_record('course', (object)$coursedata);
                $message = 'Course updated successfully';
            } else {
                // Create new course
                $newid = $DB->insert_record('course', (object)$coursedata);
                
                // Use AI to generate course structure recommendations
                if ($CFG->enableAI && $newid) {
                    $command = "python3 {$CFG->dirroot}/ai/course_structure.py --courseid=$newid --name=" . urlencode($fullname);
                    exec($command);
                }
                
                $message = 'Course created successfully';
                
                // Redirect to the new course
                redirect($CFG->wwwroot . '/course/view.php?id=' . $newid);
            }
            $messageType = 'success';
        } catch (Exception $e) {
            $message = 'Error saving course: ' . $e->getMessage();
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

// AI-Enhanced tools section
echo '<div class="ai-recommendation p-3 mb-4">
    <h4><i class="fa fa-robot me-2"></i>AI Course Design Assistant</h4>
    <p>The AI assistant can help you design your course more effectively:</p>
    <div class="row">
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">Generate Structure</h5>
                    <p class="card-text">Create a recommended course structure based on your course topic.</p>
                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#structureModal">
                        <i class="fa fa-sitemap me-1"></i> Generate Structure
                    </button>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">Create Assessments</h5>
                    <p class="card-text">Generate quizzes, assignments and discussion topics for your course.</p>
                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#assessmentsModal">
                        <i class="fa fa-tasks me-1"></i> Create Assessments
                    </button>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">Resource Suggestions</h5>
                    <p class="card-text">Get suggestions for relevant learning resources and materials.</p>
                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#resourcesModal">
                        <i class="fa fa-file me-1"></i> Find Resources
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>';

// Course Edit Form
echo '<div class="card">
    <div class="card-header">
        <h3>' . ($id ? 'Edit Course' : 'Create New Course') . '</h3>
    </div>
    <div class="card-body">
        <form method="post" action="" id="courseEditForm">
            <div class="mb-3">
                <label for="fullname" class="form-label">Course Full Name *</label>
                <input type="text" class="form-control" id="fullname" name="fullname" required
                    value="' . ($course ? htmlspecialchars($course->fullname) : '') . '">
            </div>
            
            <div class="mb-3">
                <label for="shortname" class="form-label">Course Short Name *</label>
                <input type="text" class="form-control" id="shortname" name="shortname" required
                    value="' . ($course ? htmlspecialchars($course->shortname) : '') . '">
            </div>
            
            <div class="mb-3">
                <label for="summary" class="form-label">Course Summary</label>
                <textarea class="form-control" id="summary" name="summary" rows="5">' . 
                    ($course ? htmlspecialchars($course->summary) : '') . '</textarea>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="startdate" class="form-label">Start Date</label>
                    <input type="date" class="form-control" id="startdate" name="startdate"
                        value="' . ($course ? date('Y-m-d', $course->startdate) : date('Y-m-d')) . '">
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="enddate" class="form-label">End Date</label>
                    <input type="date" class="form-control" id="enddate" name="enddate"
                        value="' . ($course ? date('Y-m-d', $course->enddate) : date('Y-m-d', strtotime('+3 months'))) . '">
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="category" class="form-label">Category</label>
                    <select class="form-select" id="category" name="category">
                        <option value="1" ' . ($course && $course->category == 1 ? 'selected' : '') . '>General</option>
                        <option value="2" ' . ($course && $course->category == 2 ? 'selected' : '') . '>Science</option>
                        <option value="3" ' . ($course && $course->category == 3 ? 'selected' : '') . '>Mathematics</option>
                        <option value="4" ' . ($course && $course->category == 4 ? 'selected' : '') . '>Language Arts</option>
                        <option value="5" ' . ($course && $course->category == 5 ? 'selected' : '') . '>Social Studies</option>
                        <option value="6" ' . ($course && $course->category == 6 ? 'selected' : '') . '>Computer Science</option>
                        <option value="7" ' . ($course && $course->category == 7 ? 'selected' : '') . '>Arts</option>
                    </select>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="format" class="form-label">Course Format</label>
                    <select class="form-select" id="format" name="format">
                        <option value="topics" ' . ($course && $course->format == 'topics' ? 'selected' : '') . '>Topics format</option>
                        <option value="weeks" ' . ($course && $course->format == 'weeks' ? 'selected' : '') . '>Weekly format</option>
                        <option value="social" ' . ($course && $course->format == 'social' ? 'selected' : '') . '>Social format</option>
                    </select>
                </div>
            </div>
            
            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="visible" name="visible" value="1" 
                    ' . ($course && $course->visible ? 'checked' : '') . '>
                <label class="form-check-label" for="visible">Course is visible to students</label>
            </div>
            
            <div class="mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fa fa-save me-1"></i> ' . ($id ? 'Update Course' : 'Create Course') . '
                </button>
                <a href="' . ($id ? $CFG->wwwroot . '/course/view.php?id=' . $id : $CFG->wwwroot . '/course/index.php') . '" class="btn btn-secondary">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>';

// AI Structure Generator Modal
echo '<div class="modal fade" id="structureModal" tabindex="-1" aria-labelledby="structureModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="structureModalLabel">AI Course Structure Generator</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="structureTopic" class="form-label">Course Topic or Learning Objectives</label>
                    <textarea class="form-control" id="structureTopic" rows="3" placeholder="Enter your course topic, key learning objectives, or course description..."></textarea>
                </div>
                <div class="mb-3">
                    <label for="structureLevel" class="form-label">Educational Level</label>
                    <select class="form-select" id="structureLevel">
                        <option value="elementary">Elementary</option>
                        <option value="secondary">Secondary/High School</option>
                        <option value="undergraduate" selected>Undergraduate</option>
                        <option value="graduate">Graduate</option>
                        <option value="professional">Professional Development</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="structureDuration" class="form-label">Course Duration</label>
                    <select class="form-select" id="structureDuration">
                        <option value="short">Short (1-4 weeks)</option>
                        <option value="medium" selected>Medium (5-10 weeks)</option>
                        <option value="semester">Semester (11-16 weeks)</option>
                        <option value="long">Long (> 16 weeks)</option>
                    </select>
                </div>
                <div id="structureResults" class="d-none">
                    <div class="alert alert-info">
                        <div class="d-flex justify-content-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                        <p class="text-center mt-2">Generating course structure...</p>
                    </div>
                    <div id="structureContent" class="d-none"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="generateStructureBtn">
                    <i class="fa fa-robot me-1"></i> Generate Structure
                </button>
                <button type="button" class="btn btn-success d-none" id="applyStructureBtn">
                    <i class="fa fa-check me-1"></i> Apply to Course
                </button>
            </div>
        </div>
    </div>
</div>';

// AI Assessment Generator Modal
echo '<div class="modal fade" id="assessmentsModal" tabindex="-1" aria-labelledby="assessmentsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="assessmentsModalLabel">AI Assessment Generator</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="assessmentTopic" class="form-label">Topic or Learning Objective</label>
                    <textarea class="form-control" id="assessmentTopic" rows="3" placeholder="Enter the specific topic or learning objective for this assessment..."></textarea>
                </div>
                <div class="mb-3">
                    <label for="assessmentType" class="form-label">Assessment Type</label>
                    <select class="form-select" id="assessmentType">
                        <option value="quiz">Quiz</option>
                        <option value="assignment">Assignment</option>
                        <option value="discussion">Discussion Topic</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="assessmentDifficulty" class="form-label">Difficulty Level</label>
                    <select class="form-select" id="assessmentDifficulty">
                        <option value="beginner">Beginner</option>
                        <option value="intermediate" selected>Intermediate</option>
                        <option value="advanced">Advanced</option>
                    </select>
                </div>
                <div id="assessmentResults" class="d-none">
                    <div class="alert alert-info assessment-loading">
                        <div class="d-flex justify-content-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                        <p class="text-center mt-2">Generating assessment...</p>
                    </div>
                    <div id="assessmentContent" class="d-none"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="generateAssessmentBtn">
                    <i class="fa fa-robot me-1"></i> Generate Assessment
                </button>
                <button type="button" class="btn btn-success d-none" id="applyAssessmentBtn">
                    <i class="fa fa-check me-1"></i> Add to Course
                </button>
            </div>
        </div>
    </div>
</div>';

// AI Resources Finder Modal
echo '<div class="modal fade" id="resourcesModal" tabindex="-1" aria-labelledby="resourcesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="resourcesModalLabel">AI Resource Finder</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="resourceTopic" class="form-label">Topic</label>
                    <textarea class="form-control" id="resourceTopic" rows="3" placeholder="Enter the specific topic for which you need resources..."></textarea>
                </div>
                <div class="mb-3">
                    <label for="resourceType" class="form-label">Resource Type</label>
                    <select class="form-select" id="resourceType">
                        <option value="readings">Readings</option>
                        <option value="videos">Videos</option>
                        <option value="interactives">Interactive Activities</option>
                        <option value="all" selected>All Types</option>
                    </select>
                </div>
                <div id="resourceResults" class="d-none">
                    <div class="alert alert-info resource-loading">
                        <div class="d-flex justify-content-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                        <p class="text-center mt-2">Finding resources...</p>
                    </div>
                    <div id="resourceContent" class="d-none"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="findResourcesBtn">
                    <i class="fa fa-robot me-1"></i> Find Resources
                </button>
                <button type="button" class="btn btn-success d-none" id="addResourcesBtn">
                    <i class="fa fa-check me-1"></i> Add to Course
                </button>
            </div>
        </div>
    </div>
</div>';

// JavaScript for AI functionality
echo '<script>
document.addEventListener("DOMContentLoaded", function() {
    // Handle structure generation
    const generateStructureBtn = document.getElementById("generateStructureBtn");
    const applyStructureBtn = document.getElementById("applyStructureBtn");
    const structureResults = document.getElementById("structureResults");
    const structureContent = document.getElementById("structureContent");
    
    generateStructureBtn.addEventListener("click", function() {
        const topic = document.getElementById("structureTopic").value.trim();
        const level = document.getElementById("structureLevel").value;
        const duration = document.getElementById("structureDuration").value;
        
        if (!topic) {
            alert("Please enter a course topic or learning objectives");
            return;
        }
        
        // Show loading
        structureResults.classList.remove("d-none");
        structureContent.classList.add("d-none");
        applyStructureBtn.classList.add("d-none");
        
        // Call API to generate structure
        fetch("/api/ai_service.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                action: "generate_course_structure",
                topic: topic,
                level: level,
                duration: duration
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Display results
                structureContent.innerHTML = `
                    <h4>Suggested Course Structure</h4>
                    <div class="course-structure-outline">
                        ${data.structure.outline}
                    </div>
                    <h4 class="mt-4">Learning Objectives</h4>
                    <div class="learning-objectives">
                        ${data.structure.objectives}
                    </div>
                `;
                structureContent.classList.remove("d-none");
                applyStructureBtn.classList.remove("d-none");
            } else {
                structureContent.innerHTML = `
                    <div class="alert alert-danger">
                        <p><strong>Error:</strong> ${data.message}</p>
                    </div>
                `;
                structureContent.classList.remove("d-none");
            }
        })
        .catch(error => {
            console.error("Error:", error);
            structureContent.innerHTML = `
                <div class="alert alert-danger">
                    <p><strong>Error:</strong> An unexpected error occurred. Please try again.</p>
                </div>
            `;
            structureContent.classList.remove("d-none");
        });
    });
    
    // Handle assessment generation
    const generateAssessmentBtn = document.getElementById("generateAssessmentBtn");
    const applyAssessmentBtn = document.getElementById("applyAssessmentBtn");
    const assessmentResults = document.getElementById("assessmentResults");
    const assessmentContent = document.getElementById("assessmentContent");
    
    generateAssessmentBtn.addEventListener("click", function() {
        const topic = document.getElementById("assessmentTopic").value.trim();
        const type = document.getElementById("assessmentType").value;
        const difficulty = document.getElementById("assessmentDifficulty").value;
        
        if (!topic) {
            alert("Please enter a topic or learning objective");
            return;
        }
        
        // Show loading
        assessmentResults.classList.remove("d-none");
        assessmentContent.classList.add("d-none");
        applyAssessmentBtn.classList.add("d-none");
        
        // Call API to generate assessment
        fetch("/api/ai_service.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                action: "generate_assessment",
                topic: topic,
                type: type,
                difficulty: difficulty
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Display results
                let content = `<h4>${data.assessment.title}</h4>`;
                
                if (type === "quiz") {
                    content += `<p class="text-muted">A ${difficulty} quiz with ${data.assessment.questions.length} questions</p>
                    <div class="quiz-questions">`;
                    
                    data.assessment.questions.forEach((question, index) => {
                        content += `
                            <div class="card mb-3">
                                <div class="card-header">Question ${index + 1}</div>
                                <div class="card-body">
                                    <p><strong>${question.text}</strong></p>`;
                                    
                        if (question.type === "multiple_choice") {
                            question.options.forEach((option, optIndex) => {
                                content += `
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="q${index}" id="q${index}o${optIndex}" ${option === question.answer ? 'checked' : ''}>
                                        <label class="form-check-label" for="q${index}o${optIndex}">
                                            ${option}
                                        </label>
                                    </div>`;
                            });
                        } else if (question.type === "true_false") {
                            content += `
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="q${index}" id="q${index}true" ${question.answer === "True" ? 'checked' : ''}>
                                    <label class="form-check-label" for="q${index}true">
                                        True
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="q${index}" id="q${index}false" ${question.answer === "False" ? 'checked' : ''}>
                                    <label class="form-check-label" for="q${index}false">
                                        False
                                    </label>
                                </div>`;
                        } else {
                            content += `
                                <div class="form-floating">
                                    <textarea class="form-control" id="q${index}answer" style="height: 100px"></textarea>
                                    <label for="q${index}answer">Your answer</label>
                                </div>
                                <div class="text-muted mt-2"><small>Expected answer: ${question.answer}</small></div>`;
                        }
                            
                        content += `
                                </div>
                            </div>`;
                    });
                    
                    content += `</div>`;
                } else if (type === "assignment") {
                    content += `
                        <div class="assignment-details">
                            <div class="card mb-3">
                                <div class="card-header">Assignment Details</div>
                                <div class="card-body">
                                    <p><strong>Description:</strong></p>
                                    <p>${data.assessment.description}</p>
                                    
                                    <p><strong>Instructions:</strong></p>
                                    <ol>`;
                    
                    data.assessment.instructions.forEach(instruction => {
                        content += `<li>${instruction}</li>`;
                    });
                    
                    content += `
                                    </ol>
                                    
                                    <p><strong>Grading Criteria:</strong></p>
                                    <ul>`;
                                    
                    data.assessment.grading_criteria.forEach(criterion => {
                        content += `<li>${criterion}</li>`;
                    });
                    
                    content += `
                                    </ul>
                                </div>
                            </div>
                        </div>`;
                } else if (type === "discussion") {
                    content += `
                        <div class="discussion-details">
                            <div class="card mb-3">
                                <div class="card-header">Discussion Topic</div>
                                <div class="card-body">
                                    <p><strong>Main Question:</strong></p>
                                    <p>${data.assessment.main_question}</p>
                                    
                                    <p><strong>Supporting Questions:</strong></p>
                                    <ul>`;
                    
                    data.assessment.supporting_questions.forEach(question => {
                        content += `<li>${question}</li>`;
                    });
                    
                    content += `
                                    </ul>
                                    
                                    <p><strong>Resources to Consider:</strong></p>
                                    <ul>`;
                                    
                    data.assessment.resources.forEach(resource => {
                        content += `<li>${resource}</li>`;
                    });
                    
                    content += `
                                    </ul>
                                </div>
                            </div>
                        </div>`;
                }
                
                assessmentContent.innerHTML = content;
                assessmentContent.classList.remove("d-none");
                applyAssessmentBtn.classList.remove("d-none");
            } else {
                assessmentContent.innerHTML = `
                    <div class="alert alert-danger">
                        <p><strong>Error:</strong> ${data.message}</p>
                    </div>
                `;
                assessmentContent.classList.remove("d-none");
            }
        })
        .catch(error => {
            console.error("Error:", error);
            assessmentContent.innerHTML = `
                <div class="alert alert-danger">
                    <p><strong>Error:</strong> An unexpected error occurred. Please try again.</p>
                </div>
            `;
            assessmentContent.classList.remove("d-none");
        });
    });
    
    // Handle resource finding
    const findResourcesBtn = document.getElementById("findResourcesBtn");
    const addResourcesBtn = document.getElementById("addResourcesBtn");
    const resourceResults = document.getElementById("resourceResults");
    const resourceContent = document.getElementById("resourceContent");
    
    findResourcesBtn.addEventListener("click", function() {
        const topic = document.getElementById("resourceTopic").value.trim();
        const type = document.getElementById("resourceType").value;
        
        if (!topic) {
            alert("Please enter a topic");
            return;
        }
        
        // Show loading
        resourceResults.classList.remove("d-none");
        resourceContent.classList.add("d-none");
        addResourcesBtn.classList.add("d-none");
        
        // Call API to find resources
        fetch("/api/ai_service.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                action: "find_resources",
                topic: topic,
                type: type
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Display results
                let content = `<h4>Suggested Resources</h4>
                               <p class="text-muted">Here are some resources that might be helpful for your course:</p>`;
                
                content += `<div class="list-group">`;
                
                data.resources.forEach(resource => {
                    content += `
                        <div class="list-group-item">
                            <div class="d-flex w-100 justify-content-between">
                                <h5 class="mb-1">${resource.title}</h5>
                                <span class="badge bg-${resource.type === 'video' ? 'danger' : resource.type === 'interactive' ? 'success' : 'primary'}">${resource.type}</span>
                            </div>
                            <p class="mb-1">${resource.description}</p>
                            ${resource.url ? `<a href="${resource.url}" target="_blank" class="small">View Resource</a>` : ''}
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" value="" id="resource${resource.id}">
                                <label class="form-check-label" for="resource${resource.id}">
                                    Add to course
                                </label>
                            </div>
                        </div>
                    `;
                });
                
                content += `</div>`;
                
                resourceContent.innerHTML = content;
                resourceContent.classList.remove("d-none");
                addResourcesBtn.classList.remove("d-none");
            } else {
                resourceContent.innerHTML = `
                    <div class="alert alert-danger">
                        <p><strong>Error:</strong> ${data.message}</p>
                    </div>
                `;
                resourceContent.classList.remove("d-none");
            }
        })
        .catch(error => {
            console.error("Error:", error);
            resourceContent.innerHTML = `
                <div class="alert alert-danger">
                    <p><strong>Error:</strong> An unexpected error occurred. Please try again.</p>
                </div>
            `;
            resourceContent.classList.remove("d-none");
        });
    });
    
    // Handle form date conversion
    document.getElementById("courseEditForm").addEventListener("submit", function(e) {
        e.preventDefault();
        
        // Convert date inputs to timestamps
        const startDateInput = document.getElementById("startdate");
        const endDateInput = document.getElementById("enddate");
        
        const startDateTimestamp = new Date(startDateInput.value).getTime() / 1000;
        const endDateTimestamp = new Date(endDateInput.value).getTime() / 1000;
        
        // Create hidden inputs for the timestamps
        const startHidden = document.createElement("input");
        startHidden.type = "hidden";
        startHidden.name = "startdate";
        startHidden.value = startDateTimestamp;
        
        const endHidden = document.createElement("input");
        endHidden.type = "hidden";
        endHidden.name = "enddate";
        endHidden.value = endDateTimestamp;
        
        // Replace the date inputs with the timestamp inputs
        startDateInput.name = "startdate_display";
        endDateInput.name = "enddate_display";
        
        this.appendChild(startHidden);
        this.appendChild(endHidden);
        
        // Submit the form
        this.submit();
    });
});
</script>';

// End page
echo $OUTPUT->footer();
