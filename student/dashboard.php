<?php
// Student Dashboard Demo
$pageTitle = "Student Dashboard - iTeachXR";
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
            height: 100%;
        }
        .course-card:hover {
            transform: translateY(-5px);
        }
        .progress {
            height: 8px;
        }
        .progress-circle {
            position: relative;
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: conic-gradient(#4b6cb7 65%, #f2f2f2 0);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .progress-circle::before {
            content: "";
            position: absolute;
            width: 90px;
            height: 90px;
            border-radius: 50%;
            background: white;
        }
        .progress-circle-content {
            position: relative;
            z-index: 1;
            text-align: center;
        }
        .badge-resource {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 5px 8px;
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
                            My Courses
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
                            <i class="fa fa-file-text me-2"></i>
                            Assignments
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
                        <img src="https://via.placeholder.com/32" alt="Student" width="32" height="32" class="rounded-circle me-2">
                        <strong>Alex Johnson</strong>
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
            <h1 class="mb-4">Student Dashboard</h1>
            
            <!-- Overview Stats -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <h5 class="card-title">Courses</h5>
                            <h2 class="display-4">2</h2>
                            <p class="card-text">Currently enrolled</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <h5 class="card-title">Completed</h5>
                            <h2 class="display-4">18</h2>
                            <p class="card-text">Learning activities</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-dark">
                        <div class="card-body">
                            <h5 class="card-title">Due Soon</h5>
                            <h2 class="display-4">3</h2>
                            <p class="card-text">Upcoming assignments</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <h5 class="card-title">Average</h5>
                            <h2 class="display-4">87%</h2>
                            <p class="card-text">Overall grade</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Continue Learning & Personalized Path -->
            <div class="row mb-4">
                <!-- Continue Learning -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Continue Learning</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <!-- Course 1 -->
                                <div class="col-md-6 mb-4">
                                    <div class="card course-card">
                                        <div class="card-body">
                                            <h5 class="card-title">Introduction to Virtual Reality</h5>
                                            <p class="card-text text-muted">Dr. Sarah Chen</p>
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <div>
                                                    <small class="text-muted">Your Progress: 65%</small>
                                                    <div class="progress mt-1" style="width: 200px;">
                                                        <div class="progress-bar bg-success" role="progressbar" style="width: 65%" aria-valuenow="65" aria-valuemin="0" aria-valuemax="100"></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <small class="text-muted">Next: VR User Interface Design</small>
                                                <a href="#" class="btn btn-sm btn-primary">Continue</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Course 2 -->
                                <div class="col-md-6 mb-4">
                                    <div class="card course-card">
                                        <div class="card-body">
                                            <h5 class="card-title">Advanced Augmented Reality</h5>
                                            <p class="card-text text-muted">Prof. Michael Johnson</p>
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <div>
                                                    <small class="text-muted">Your Progress: 42%</small>
                                                    <div class="progress mt-1" style="width: 200px;">
                                                        <div class="progress-bar bg-warning" role="progressbar" style="width: 42%" aria-valuenow="42" aria-valuemin="0" aria-valuemax="100"></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <small class="text-muted">Next: Spatial Mapping Lab</small>
                                                <a href="#" class="btn btn-sm btn-primary">Continue</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Personalized Learning Path -->
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Your Learning Path</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-center mb-3">
                                <div class="progress-circle">
                                    <div class="progress-circle-content">
                                        <h3 class="mb-0">65%</h3>
                                        <small>Complete</small>
                                    </div>
                                </div>
                            </div>
                            
                            <h6>Current Module: VR User Interface Design</h6>
                            <p class="small text-muted">Based on your learning style and progress, we recommend:</p>
                            
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item px-0">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <i class="fa fa-check-circle text-success me-2"></i>
                                            Complete UI Design Quiz
                                        </div>
                                        <span class="badge bg-light text-dark">Next</span>
                                    </div>
                                </li>
                                <li class="list-group-item px-0">
                                    <div class="d-flex">
                                        <i class="fa fa-video-camera text-primary me-2"></i>
                                        Watch "Advanced Interaction Patterns" video
                                    </div>
                                </li>
                                <li class="list-group-item px-0">
                                    <div class="d-flex">
                                        <i class="fa fa-file-text text-primary me-2"></i>
                                        Submit UI Mockup Assignment
                                    </div>
                                </li>
                            </ul>
                            
                            <div class="text-center mt-3">
                                <button class="btn btn-outline-primary btn-sm">View Full Learning Path</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <!-- Upcoming Assignments -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Upcoming Assignments</h5>
                        </div>
                        <div class="card-body">
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
                                        <tr>
                                            <td>VR Environment Design</td>
                                            <td>Intro to VR</td>
                                            <td>April 30, 2025</td>
                                            <td><span class="badge bg-danger">Due soon</span></td>
                                            <td><a href="#" class="btn btn-sm btn-success">Submit</a></td>
                                        </tr>
                                        <tr>
                                            <td>AR Marker Implementation</td>
                                            <td>Advanced AR</td>
                                            <td>May 5, 2025</td>
                                            <td><span class="badge bg-warning">Not started</span></td>
                                            <td><a href="#" class="btn btn-sm btn-outline-primary">View</a></td>
                                        </tr>
                                        <tr>
                                            <td>UI Design Quiz</td>
                                            <td>Intro to VR</td>
                                            <td>May 3, 2025</td>
                                            <td><span class="badge bg-warning">Not started</span></td>
                                            <td><a href="#" class="btn btn-sm btn-outline-primary">View</a></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Recommended Resources -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Recommended Resources</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="card position-relative">
                                        <span class="badge bg-danger badge-resource">High Relevance</span>
                                        <div class="card-body">
                                            <h6 class="card-title">VR Best Practices Guide</h6>
                                            <p class="card-text small">Industry-standard guidelines for VR development</p>
                                            <div class="d-flex align-items-center">
                                                <i class="fa fa-file-text text-primary me-2"></i>
                                                <small class="text-muted">Document</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="card position-relative">
                                        <span class="badge bg-danger badge-resource">High Relevance</span>
                                        <div class="card-body">
                                            <h6 class="card-title">Unity VR Interaction Framework</h6>
                                            <p class="card-text small">Tutorial on implementing VR interactions in Unity</p>
                                            <div class="d-flex align-items-center">
                                                <i class="fa fa-video-camera text-danger me-2"></i>
                                                <small class="text-muted">Video Tutorial</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="card position-relative">
                                        <span class="badge bg-warning badge-resource">Medium Relevance</span>
                                        <div class="card-body">
                                            <h6 class="card-title">UI Design Principles for 3D Spaces</h6>
                                            <p class="card-text small">Academic paper on spatial UI design considerations</p>
                                            <div class="d-flex align-items-center">
                                                <i class="fa fa-graduation-cap text-info me-2"></i>
                                                <small class="text-muted">Academic Paper</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card position-relative">
                                        <span class="badge bg-warning badge-resource">Medium Relevance</span>
                                        <div class="card-body">
                                            <h6 class="card-title">XR Accessibility Guidelines</h6>
                                            <p class="card-text small">Best practices for inclusive XR experiences</p>
                                            <div class="d-flex align-items-center">
                                                <i class="fa fa-universal-access text-success me-2"></i>
                                                <small class="text-muted">Guidelines</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>