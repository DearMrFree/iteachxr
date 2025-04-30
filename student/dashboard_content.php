<?php
// iTeachXR - Student Dashboard Content

// Prevent direct access
defined('MOODLE_INTERNAL') || die();

// Get current user's enrolled courses
$user_id = $USER->id;
$courses = []; // In a real system, this would be fetched from the database

// Sample course data for testing
$courses = [
    (object)[
        'id' => 1,
        'fullname' => 'Introduction to Virtual Reality',
        'shortname' => 'IntroVR',
        'summary' => 'Learn the fundamentals of VR development and theory',
        'teacher' => 'Dr. Sarah Chen',
        'progress' => 65,
        'next_activity' => 'VR User Interface Design Principles'
    ],
    (object)[
        'id' => 2,
        'fullname' => 'Advanced Augmented Reality Applications',
        'shortname' => 'AdvAR',
        'summary' => 'Develop sophisticated AR applications',
        'teacher' => 'Prof. Michael Johnson',
        'progress' => 42,
        'next_activity' => 'Spatial Mapping Lab'
    ]
];

// Get upcoming assignments
$upcoming_assignments = []; // In a real system, this would be fetched from the database

// Sample assignments data for testing
$upcoming_assignments = [
    (object)[
        'id' => 101,
        'name' => 'VR Environment Design',
        'course' => 'Introduction to Virtual Reality',
        'due_date' => '2025-05-10 23:59:59',
        'status' => 'Not submitted'
    ],
    (object)[
        'id' => 102,
        'name' => 'AR Marker Implementation',
        'course' => 'Advanced Augmented Reality Applications',
        'due_date' => '2025-05-05 23:59:59',
        'status' => 'Not submitted'
    ]
];

// Get personalized learning recommendations (Simplified for demo)
$personalized_recommendations = [
    'learning_path' => [
        'current_module' => 'VR User Interface Design',
        'next_steps' => [
            'Complete UI Design Quiz',
            'Watch "Advanced Interaction Patterns" video',
            'Submit UI Mockup Assignment'
        ]
    ],
    'resources' => [
        [
            'title' => 'VR Best Practices Guide',
            'description' => 'Industry-standard guidelines for VR development',
            'relevance' => 'high',
            'type' => 'document'
        ],
        [
            'title' => 'Unity VR Interaction Framework',
            'description' => 'Tutorial on implementing VR interactions in Unity',
            'relevance' => 'high',
            'type' => 'video'
        ],
        [
            'title' => 'UI Design Principles for 3D Spaces',
            'description' => 'Academic paper on spatial UI design considerations',
            'relevance' => 'medium',
            'type' => 'academic'
        ]
    ]
];

// Get recent grades and feedback
$recent_grades = []; // In a real system, this would be fetched from the database

// Sample grades data for testing
$recent_grades = [
    (object)[
        'id' => 201,
        'assignment' => 'VR Hardware Overview Quiz',
        'course' => 'Introduction to Virtual Reality',
        'grade' => '85/100',
        'feedback' => 'Good understanding of tracking systems, but review the section on display technologies.',
        'date' => '2025-04-25 14:30:00'
    ],
    (object)[
        'id' => 202,
        'assignment' => 'AR Foundation Setup',
        'course' => 'Advanced Augmented Reality Applications',
        'grade' => '92/100',
        'feedback' => 'Excellent work, your implementation was clear and well-documented.',
        'date' => '2025-04-20 11:15:00'
    ]
];

?>

<div class="student-dashboard">
    <div class="row mb-4">
        <div class="col">
            <h2>Student Dashboard</h2>
        </div>
    </div>
    
    <div class="row">
        <!-- Main Content Column -->
        <div class="col-md-8">
            <!-- My Courses Section -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title mb-0">My Courses</h3>
                </div>
                <div class="card-body">
                    <?php if (!empty($courses)): ?>
                        <?php foreach ($courses as $course): ?>
                            <div class="course-card mb-3">
                                <div class="row">
                                    <div class="col-md-8">
                                        <h4>
                                            <a href="/course/view.php?id=<?php echo $course->id; ?>">
                                                <?php echo $course->fullname; ?>
                                            </a>
                                        </h4>
                                        <p class="text-muted mb-1"><?php echo $course->teacher; ?></p>
                                        <p class="small mb-2"><?php echo $course->summary; ?></p>
                                        <div class="mt-2">
                                            <strong>Next:</strong> <?php echo $course->next_activity; ?>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="text-center">
                                            <div class="progress-circle" data-progress="<?php echo $course->progress; ?>">
                                                <div class="progress-circle-inner">
                                                    <span class="progress-percentage"><?php echo $course->progress; ?>%</span>
                                                    <span class="progress-label">Complete</span>
                                                </div>
                                            </div>
                                            <a href="/course/view.php?id=<?php echo $course->id; ?>" class="btn btn-sm btn-primary mt-2">
                                                Continue Learning
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <hr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>You are not enrolled in any courses yet.</p>
                        <a href="/course/browse.php" class="btn btn-primary">Browse available courses</a>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Upcoming Assignments Section -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h3 class="card-title mb-0">Upcoming Assignments</h3>
                </div>
                <div class="card-body">
                    <?php if (!empty($upcoming_assignments)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Assignment</th>
                                        <th>Course</th>
                                        <th>Due Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($upcoming_assignments as $assignment): ?>
                                        <tr>
                                            <td><?php echo $assignment->name; ?></td>
                                            <td><?php echo $assignment->course; ?></td>
                                            <td>
                                                <?php 
                                                    $due_date = strtotime($assignment->due_date);
                                                    $now = time();
                                                    $days_left = floor(($due_date - $now) / (60 * 60 * 24));
                                                    
                                                    echo date('M d, g:i a', $due_date);
                                                    
                                                    if ($days_left < 3 && $days_left >= 0) {
                                                        echo ' <span class="badge bg-danger">Due soon</span>';
                                                    } elseif ($days_left < 0) {
                                                        echo ' <span class="badge bg-secondary">Overdue</span>';
                                                    }
                                                ?>
                                            </td>
                                            <td><?php echo $assignment->status; ?></td>
                                            <td>
                                                <a href="/mod/assignment/view.php?id=<?php echo $assignment->id; ?>" class="btn btn-sm btn-primary">
                                                    View
                                                </a>
                                                <a href="/mod/assignment/submit.php?id=<?php echo $assignment->id; ?>" class="btn btn-sm btn-success">
                                                    Submit
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p>No upcoming assignments.</p>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Recent Grades Section -->
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h3 class="card-title mb-0">Recent Grades & Feedback</h3>
                </div>
                <div class="card-body">
                    <?php if (!empty($recent_grades)): ?>
                        <?php foreach ($recent_grades as $grade): ?>
                            <div class="grade-card mb-3">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h5 class="mb-1"><?php echo $grade->assignment; ?></h5>
                                        <p class="text-muted small mb-0"><?php echo $grade->course; ?> • <?php echo date('M d, Y', strtotime($grade->date)); ?></p>
                                    </div>
                                    <div>
                                        <span class="badge bg-primary"><?php echo $grade->grade; ?></span>
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <strong>Feedback:</strong>
                                    <p class="mb-0"><?php echo $grade->feedback; ?></p>
                                </div>
                            </div>
                            <hr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No recent grades available.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Sidebar Column -->
        <div class="col-md-4">
            <!-- Personalized Learning Path -->
            <div class="card mb-4">
                <div class="card-header bg-warning text-dark">
                    <h3 class="card-title mb-0">Your Learning Path</h3>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h5>Current Module</h5>
                        <p><?php echo $personalized_recommendations['learning_path']['current_module']; ?></p>
                    </div>
                    
                    <div class="mb-3">
                        <h5>Next Steps</h5>
                        <ul>
                            <?php foreach ($personalized_recommendations['learning_path']['next_steps'] as $step): ?>
                                <li><?php echo $step; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    
                    <a href="/user/learning_path.php" class="btn btn-sm btn-outline-secondary">
                        View Full Learning Path
                    </a>
                </div>
            </div>
            
            <!-- AI Study Assistant -->
            <div class="card mb-4">
                <div class="card-header bg-secondary text-white">
                    <h3 class="card-title mb-0">AI Study Assistant</h3>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="studyAssistantPrompt" class="form-label">What would you like help with?</label>
                        <textarea class="form-control" id="studyAssistantPrompt" rows="3" placeholder="E.g., Explain VR locomotion methods"></textarea>
                    </div>
                    <button class="btn btn-primary w-100" id="askStudyAssistantBtn">
                        <i class="fa fa-question-circle"></i> Ask Study Assistant
                    </button>
                </div>
            </div>
            
            <!-- Recommended Resources -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h3 class="card-title mb-0">Recommended Resources</h3>
                </div>
                <div class="card-body">
                    <?php if (!empty($personalized_recommendations['resources'])): ?>
                        <?php foreach ($personalized_recommendations['resources'] as $resource): ?>
                            <div class="resource-card mb-2">
                                <div class="d-flex">
                                    <div class="resource-icon me-2">
                                        <?php if ($resource['type'] == 'video'): ?>
                                            <i class="fa fa-video text-danger"></i>
                                        <?php elseif ($resource['type'] == 'document'): ?>
                                            <i class="fa fa-file-text text-primary"></i>
                                        <?php elseif ($resource['type'] == 'academic'): ?>
                                            <i class="fa fa-graduation-cap text-info"></i>
                                        <?php else: ?>
                                            <i class="fa fa-link text-secondary"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <h6 class="mb-0"><?php echo $resource['title']; ?></h6>
                                        <p class="small text-muted mb-0"><?php echo $resource['description']; ?></p>
                                        <span class="badge bg-<?php echo ($resource['relevance'] == 'high') ? 'danger' : 'warning'; ?>">
                                            <?php echo ucfirst($resource['relevance']); ?> relevance
                                        </span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No resources available at this time.</p>
                    <?php endif; ?>
                    
                    <div class="text-center mt-3">
                        <a href="/resources/recommended.php" class="btn btn-sm btn-outline-secondary">
                            View All Resources
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Progress circles
    document.querySelectorAll('.progress-circle').forEach(function(circle) {
        const progress = parseInt(circle.getAttribute('data-progress'));
        circle.style.background = `conic-gradient(var(--bs-primary) ${progress}%, #f0f0f0 0)`;
    });
    
    // Ask Study Assistant button
    document.getElementById('askStudyAssistantBtn').addEventListener('click', function() {
        const prompt = document.getElementById('studyAssistantPrompt').value.trim();
        if (prompt) {
            // In a real implementation, this would call the AI API
            alert('Asking study assistant: ' + prompt);
        }
    });
});
</script>

<style>
.course-card {
    padding: 15px;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.course-card:hover {
    background-color: #f8f9fa;
}

.progress-circle {
    position: relative;
    width: 100px;
    height: 100px;
    border-radius: 50%;
    background: conic-gradient(var(--bs-primary) 0%, #f0f0f0 0);
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.progress-circle-inner {
    position: absolute;
    width: 80px;
    height: 80px;
    background: white;
    border-radius: 50%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

.progress-percentage {
    font-size: 1.5rem;
    font-weight: bold;
    line-height: 1;
}

.progress-label {
    font-size: 0.75rem;
    color: #6c757d;
}

.resource-card {
    padding: 10px;
    border-radius: 5px;
    transition: all 0.2s ease;
}

.resource-card:hover {
    background-color: #f8f9fa;
}

.grade-card {
    padding: 10px;
    border-radius: 5px;
    transition: all 0.2s ease;
}

.grade-card:hover {
    background-color: #f8f9fa;
}
</style>