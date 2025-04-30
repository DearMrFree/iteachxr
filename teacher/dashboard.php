<?php
// Teacher Dashboard Demo
$pageTitle = "Teacher Dashboard - iTeachXR";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/font-awesome@4.7.0/css/font-awesome.min.css">
    <style>
        .sidebar {
            min-height: 100vh;
            background-color: #4b6cb7;
            color: white;
        }
        .sidebar a {
            color: white;
            text-decoration: none;
        }
        .sidebar a:hover {
            background-color: rgba(255,255,255,0.1);
        }
        .main-content {
            padding: 30px;
        }
        .card {
            margin-bottom: 20px;
            box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
        }
        .course-card {
            cursor: pointer;
            transition: transform 0.2s;
        }
        .course-card:hover {
            transform: translateY(-5px);
        }
        .progress {
            height: 8px;
        }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-2 p-0 sidebar">
            <div class="d-flex flex-column p-3">
                <a href="../index.php" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-decoration-none">
                    <span class="fs-4">iTeachXR</span>
                </a>
                <hr>
                <ul class="nav nav-pills flex-column mb-auto">
                    <li class="nav-item">
                        <a href="#" class="nav-link active">
                            <i class="fa fa-dashboard me-2"></i>
                            Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="#" class="nav-link text-white">
                            <i class="fa fa-book me-2"></i>
                            Courses
                        </a>
                    </li>
                    <li>
                        <a href="/teacher/collaborative_workspace.php" class="nav-link text-white">
                            <i class="fa fa-users me-2"></i>
                            Collaboration
                        </a>
                    </li>
                    <li>
                        <a href="/ai_demo.php" class="nav-link text-white">
                            <i class="fa fa-magic me-2"></i>
                            AI Tools
                        </a>
                    </li>
                    <li>
                        <a href="#" class="nav-link text-white">
                            <i class="fa fa-users me-2"></i>
                            Students
                        </a>
                    </li>
                    <li>
                        <a href="#" class="nav-link text-white">
                            <i class="fa fa-calendar me-2"></i>
                            Calendar
                        </a>
                    </li>
                    <li>
                        <a href="#" class="nav-link text-white">
                            <i class="fa fa-comments me-2"></i>
                            Messages
                        </a>
                    </li>
                    <li>
                        <a href="#" class="nav-link text-white">
                            <i class="fa fa-cog me-2"></i>
                            Settings
                        </a>
                    </li>
                </ul>
                <hr>
                <div class="dropdown">
                    <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" id="dropdownUser1" data-bs-toggle="dropdown">
                        <img src="https://via.placeholder.com/32" alt="Teacher" width="32" height="32" class="rounded-circle me-2">
                        <strong>Dr. Sarah Chen</strong>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-dark text-small shadow" aria-labelledby="dropdownUser1">
                        <li><a class="dropdown-item" href="#">Profile</a></li>
                        <li><a class="dropdown-item" href="#">Settings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="#">Sign out</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-md-10 main-content">
            <h1 class="mb-4">Teacher Dashboard</h1>
            
            <!-- Stats Overview -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <h5 class="card-title">Courses</h5>
                            <h2 class="display-4">3</h2>
                            <p class="card-text">Active courses</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <h5 class="card-title">Students</h5>
                            <h2 class="display-4">74</h2>
                            <p class="card-text">Total enrolled</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-dark">
                        <div class="card-body">
                            <h5 class="card-title">Assignments</h5>
                            <h2 class="display-4">12</h2>
                            <p class="card-text">Pending review</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <h5 class="card-title">Messages</h5>
                            <h2 class="display-4">5</h2>
                            <p class="card-text">Unread messages</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Courses -->
            <div class="row mb-4">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">My Courses</h5>
                            <button class="btn btn-primary btn-sm">
                                <i class="fa fa-plus me-1"></i> New Course
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <!-- Course 1 -->
                                <div class="col-md-6 mb-3">
                                    <div class="card course-card">
                                        <div class="card-body">
                                            <h5 class="card-title">Introduction to Virtual Reality</h5>
                                            <p class="card-text text-muted">32 students enrolled</p>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <small class="text-muted">Progress: 65%</small>
                                                <div class="progress w-50">
                                                    <div class="progress-bar bg-success" role="progressbar" style="width: 65%" aria-valuenow="65" aria-valuemin="0" aria-valuemax="100"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Course 2 -->
                                <div class="col-md-6 mb-3">
                                    <div class="card course-card">
                                        <div class="card-body">
                                            <h5 class="card-title">Advanced Augmented Reality</h5>
                                            <p class="card-text text-muted">18 students enrolled</p>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <small class="text-muted">Progress: 42%</small>
                                                <div class="progress w-50">
                                                    <div class="progress-bar bg-warning" role="progressbar" style="width: 42%" aria-valuenow="42" aria-valuemin="0" aria-valuemax="100"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Course 3 -->
                                <div class="col-md-6">
                                    <div class="card course-card">
                                        <div class="card-body">
                                            <h5 class="card-title">Interactive 3D Education</h5>
                                            <p class="card-text text-muted">24 students enrolled</p>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <small class="text-muted">Progress: 78%</small>
                                                <div class="progress w-50">
                                                    <div class="progress-bar bg-success" role="progressbar" style="width: 78%" aria-valuenow="78" aria-valuemin="0" aria-valuemax="100"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <!-- AI Assistant -->
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">AI Teaching Assistant</h5>
                        </div>
                        <div class="card-body">
                            <p>What would you like help with today?</p>
                            <div class="mb-3">
                                <textarea class="form-control" rows="3" placeholder="E.g., Create a quiz about VR headsets"></textarea>
                            </div>
                            <button class="btn btn-primary w-100">
                                <i class="fa fa-magic me-1"></i> Generate Content
                            </button>
                            <hr>
                            <h6>Quick Actions</h6>
                            <div class="d-grid gap-2">
                                <a href="/ai_demo.php" class="btn btn-outline-primary btn-sm">
                                    <i class="fa fa-magic me-1"></i> AI Tools
                                </a>
                                <a href="/teacher/collaborative_workspace.php" class="btn btn-outline-primary btn-sm">
                                    <i class="fa fa-users me-1"></i> Collaborative Workspace
                                </a>
                                <button class="btn btn-outline-primary btn-sm">Analyze Performance</button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Calendar -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Upcoming Deadlines</h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item px-0">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h6 class="mb-0">VR Environment Design</h6>
                                            <small class="text-muted">Introduction to Virtual Reality</small>
                                        </div>
                                        <span class="badge bg-danger">Tomorrow</span>
                                    </div>
                                </li>
                                <li class="list-group-item px-0">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h6 class="mb-0">AR Marker Implementation</h6>
                                            <small class="text-muted">Advanced Augmented Reality</small>
                                        </div>
                                        <span class="badge bg-warning">3 days</span>
                                    </div>
                                </li>
                                <li class="list-group-item px-0">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h6 class="mb-0">Mid-term Exam</h6>
                                            <small class="text-muted">Interactive 3D Education</small>
                                        </div>
                                        <span class="badge bg-info">1 week</span>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Recent Submissions -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Recent Submissions</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Assignment</th>
                                    <th>Course</th>
                                    <th>Submitted</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>John Doe</td>
                                    <td>VR Environment Design</td>
                                    <td>Introduction to Virtual Reality</td>
                                    <td>April 28, 2025</td>
                                    <td><span class="badge bg-warning">Not Graded</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-primary">Grade</button>
                                        <button class="btn btn-sm btn-outline-secondary">AI Feedback</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Jane Smith</td>
                                    <td>AR Marker Implementation</td>
                                    <td>Advanced Augmented Reality</td>
                                    <td>April 29, 2025</td>
                                    <td><span class="badge bg-warning">Not Graded</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-primary">Grade</button>
                                        <button class="btn btn-sm btn-outline-secondary">AI Feedback</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Robert Johnson</td>
                                    <td>3D Model Creation</td>
                                    <td>Interactive 3D Education</td>
                                    <td>April 25, 2025</td>
                                    <td><span class="badge bg-success">Graded (85%)</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary">View</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>