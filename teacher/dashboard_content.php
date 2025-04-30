<?php
// iTeachXR - Teacher Dashboard Content

// Prevent direct access
defined('MOODLE_INTERNAL') || die();

// Get current user's courses
$user_id = $USER->id;
$courses = []; // In a real system, this would be fetched from the database

// Sample course data for testing
$courses = [
    (object)[
        'id' => 1,
        'fullname' => 'Introduction to Virtual Reality',
        'shortname' => 'IntroVR',
        'summary' => 'Learn the fundamentals of VR development and theory',
        'students' => 32,
        'progress' => 65
    ],
    (object)[
        'id' => 2,
        'fullname' => 'Advanced Augmented Reality Applications',
        'shortname' => 'AdvAR',
        'summary' => 'Develop sophisticated AR applications',
        'students' => 18,
        'progress' => 42
    ],
    (object)[
        'id' => 3,
        'fullname' => 'Interactive 3D Education',
        'shortname' => '3DEdu',
        'summary' => 'Create engaging 3D educational experiences',
        'students' => 24,
        'progress' => 78
    ]
];

// Get recent submissions that need grading
$submissions_to_grade = []; // In a real system, this would be fetched from the database

// Sample submissions data for testing
$submissions_to_grade = [
    (object)[
        'id' => 101,
        'assignment' => 'VR Environment Design',
        'course' => 'Introduction to Virtual Reality',
        'student' => 'John Doe',
        'submitted' => '2025-04-28 15:30:22',
        'status' => 'Not graded'
    ],
    (object)[
        'id' => 102,
        'assignment' => 'AR Marker Implementation',
        'course' => 'Advanced Augmented Reality Applications',
        'student' => 'Jane Smith',
        'submitted' => '2025-04-29 09:45:13',
        'status' => 'Not graded'
    ]
];

// Get AI-generated insights and recommendations (Simplified for demo)
$ai_insights = [
    'engagement' => [
        'high_engagement_topics' => ['Hands-on VR Labs', 'AR Project Showcase'],
        'low_engagement_topics' => ['Theoretical AR Foundations', 'VR History'],
        'recommended_actions' => [
            'Add more interactive elements to theoretical topics',
            'Consider breaking down longer lectures into shorter segments'
        ]
    ],
    'course_improvement' => [
        'content_areas_to_enhance' => ['VR Physics', 'User Interface Design'],
        'suggested_resources' => [
            'New Unity VR Framework Documentation',
            'IEEE Paper: Best Practices in Educational XR'
        ]
    ]
];

?>

<div class="teacher-dashboard">
    <div class="row mb-4">
        <div class="col">
            <div class="d-flex justify-content-between align-items-center">
                <h2>Teacher Dashboard</h2>
                <a href="/course/create.php" class="btn btn-success">
                    <i class="fa fa-plus"></i> Create New Course
                </a>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- Course Management Column -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title mb-0">My Courses</h3>
                </div>
                <div class="card-body">
                    <?php if (!empty($courses)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Course Name</th>
                                        <th>Students</th>
                                        <th>Progress</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($courses as $course): ?>
                                        <tr>
                                            <td>
                                                <a href="/course/view.php?id=<?php echo $course->id; ?>">
                                                    <?php echo $course->fullname; ?>
                                                </a>
                                                <div class="small text-muted"><?php echo $course->summary; ?></div>
                                            </td>
                                            <td><?php echo $course->students; ?></td>
                                            <td>
                                                <div class="progress">
                                                    <div class="progress-bar" role="progressbar" style="width: <?php echo $course->progress; ?>%" 
                                                         aria-valuenow="<?php echo $course->progress; ?>" aria-valuemin="0" aria-valuemax="100">
                                                        <?php echo $course->progress; ?>%
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="/course/edit.php?id=<?php echo $course->id; ?>" class="btn btn-sm btn-outline-primary">
                                                        <i class="fa fa-edit"></i>
                                                    </a>
                                                    <a href="/course/view.php?id=<?php echo $course->id; ?>" class="btn btn-sm btn-outline-success">
                                                        <i class="fa fa-eye"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p>You are not teaching any courses yet.</p>
                        <a href="/course/create.php" class="btn btn-primary">Create your first course</a>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h3 class="card-title mb-0">Submissions to Grade</h3>
                </div>
                <div class="card-body">
                    <?php if (!empty($submissions_to_grade)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Assignment</th>
                                        <th>Course</th>
                                        <th>Student</th>
                                        <th>Submitted</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($submissions_to_grade as $submission): ?>
                                        <tr>
                                            <td><?php echo $submission->assignment; ?></td>
                                            <td><?php echo $submission->course; ?></td>
                                            <td><?php echo $submission->student; ?></td>
                                            <td><?php echo date('M d, g:i a', strtotime($submission->submitted)); ?></td>
                                            <td>
                                                <a href="/mod/assignment/grade.php?id=<?php echo $submission->id; ?>" class="btn btn-sm btn-primary">
                                                    Grade
                                                </a>
                                                <a href="/mod/assignment/ai_feedback.php?id=<?php echo $submission->id; ?>" class="btn btn-sm btn-outline-secondary">
                                                    AI Feedback
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p>No submissions waiting to be graded.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- AI Insights and Tools Column -->
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h3 class="card-title mb-0">AI Teaching Assistant</h3>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="aiAssistantPrompt" class="form-label">What would you like help with?</label>
                        <textarea class="form-control" id="aiAssistantPrompt" rows="3" placeholder="E.g., Create a quiz about VR input devices"></textarea>
                    </div>
                    <div class="mb-3">
                        <button class="btn btn-primary w-100" id="generateContentBtn">
                            <i class="fa fa-magic"></i> Generate Content
                        </button>
                    </div>
                    <div class="text-center mb-3">- or choose a quick action -</div>
                    <div class="d-grid gap-2">
                        <button class="btn btn-outline-primary" id="generateQuizBtn">
                            <i class="fa fa-question-circle"></i> Generate Quiz
                        </button>
                        <button class="btn btn-outline-primary" id="createLessonBtn">
                            <i class="fa fa-book"></i> Create Lesson Plan
                        </button>
                        <button class="btn btn-outline-primary" id="analyzePerformanceBtn">
                            <i class="fa fa-chart-bar"></i> Analyze Class Performance
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header bg-warning text-dark">
                    <h3 class="card-title mb-0">AI Insights</h3>
                </div>
                <div class="card-body">
                    <h5>Student Engagement</h5>
                    <div class="mb-3">
                        <h6 class="text-success"><i class="fa fa-arrow-up"></i> High Engagement Topics</h6>
                        <ul class="small">
                            <?php foreach ($ai_insights['engagement']['high_engagement_topics'] as $topic): ?>
                                <li><?php echo $topic; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <div class="mb-3">
                        <h6 class="text-danger"><i class="fa fa-arrow-down"></i> Low Engagement Topics</h6>
                        <ul class="small">
                            <?php foreach ($ai_insights['engagement']['low_engagement_topics'] as $topic): ?>
                                <li><?php echo $topic; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    
                    <h5>Recommended Actions</h5>
                    <ul class="small">
                        <?php foreach ($ai_insights['engagement']['recommended_actions'] as $action): ?>
                            <li><?php echo $action; ?></li>
                        <?php endforeach; ?>
                    </ul>
                    
                    <div class="text-center mt-3">
                        <a href="/analytics/teacher_insights.php" class="btn btn-sm btn-outline-secondary">
                            View Detailed Analytics
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // AI Assistant Button Handling
    document.getElementById('generateContentBtn').addEventListener('click', function() {
        const prompt = document.getElementById('aiAssistantPrompt').value.trim();
        if (prompt) {
            // In a real implementation, this would call the AI API
            alert('Generating content based on: ' + prompt);
        }
    });
    
    // Quick action buttons
    document.getElementById('generateQuizBtn').addEventListener('click', function() {
        window.location.href = '/mod/quiz/ai_create.php';
    });
    
    document.getElementById('createLessonBtn').addEventListener('click', function() {
        window.location.href = '/course/ai_lesson_plan.php';
    });
    
    document.getElementById('analyzePerformanceBtn').addEventListener('click', function() {
        window.location.href = '/analytics/class_performance.php';
    });
});
</script>