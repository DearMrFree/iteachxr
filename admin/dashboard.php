<?php
// Admin Dashboard Demo
$pageTitle = "Admin Dashboard - iTeachXR";
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
            background-color: #343a40;
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
        .action-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .list-group-item:hover {
            background-color: #f8f9fa;
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
                            <i class="fa fa-users me-2"></i>
                            Users
                        </a>
                    </li>
                    <li>
                        <a href="#" class="nav-link text-white">
                            <i class="fa fa-book me-2"></i>
                            Courses
                        </a>
                    </li>
                    <li>
                        <a href="#" class="nav-link text-white">
                            <i class="fa fa-cogs me-2"></i>
                            System Settings
                        </a>
                    </li>
                    <li>
                        <a href="#" class="nav-link text-white">
                            <i class="fa fa-robot me-2"></i>
                            AI Settings
                        </a>
                    </li>
                    <li>
                        <a href="#" class="nav-link text-white">
                            <i class="fa fa-bar-chart me-2"></i>
                            Analytics
                        </a>
                    </li>
                </ul>
                <hr>
                <div class="dropdown">
                    <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" id="dropdownUser1" data-bs-toggle="dropdown">
                        <img src="https://via.placeholder.com/32" alt="Admin" width="32" height="32" class="rounded-circle me-2">
                        <strong>Admin User</strong>
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
            <h1 class="mb-4">Admin Dashboard</h1>
            
            <!-- System Overview -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <h5 class="card-title">Users</h5>
                            <div class="d-flex align-items-center">
                                <h2 class="display-4 mb-0">135</h2>
                                <div class="ms-auto">
                                    <small class="text-white-50">+12 this week</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <h5 class="card-title">Courses</h5>
                            <div class="d-flex align-items-center">
                                <h2 class="display-4 mb-0">24</h2>
                                <div class="ms-auto">
                                    <small class="text-white-50">+3 this week</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <h5 class="card-title">Storage</h5>
                            <div class="d-flex align-items-center">
                                <h2 class="display-4 mb-0">75%</h2>
                                <div class="ms-auto">
                                    <small class="text-white-50">2.4GB/3.2GB</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-danger text-white">
                        <div class="card-body">
                            <h5 class="card-title">System Load</h5>
                            <div class="d-flex align-items-center">
                                <h2 class="display-4 mb-0">42%</h2>
                                <div class="ms-auto">
                                    <small class="text-white-50">Normal</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- User and AI Insights -->
            <div class="row mb-4">
                <!-- User Role Distribution -->
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="mb-0">User Breakdown</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label d-flex justify-content-between mb-1">
                                    <span>Students</span>
                                    <span>112 (83%)</span>
                                </label>
                                <div class="progress">
                                    <div class="progress-bar bg-primary" role="progressbar" style="width: 83%"></div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label d-flex justify-content-between mb-1">
                                    <span>Teachers</span>
                                    <span>18 (13%)</span>
                                </label>
                                <div class="progress">
                                    <div class="progress-bar bg-success" role="progressbar" style="width: 13%"></div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label d-flex justify-content-between mb-1">
                                    <span>Administrators</span>
                                    <span>5 (4%)</span>
                                </label>
                                <div class="progress">
                                    <div class="progress-bar bg-danger" role="progressbar" style="width: 4%"></div>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <button class="btn btn-sm btn-outline-primary">
                                    <i class="fa fa-users me-1"></i> Manage Users
                                </button>
                                <button class="btn btn-sm btn-outline-success">
                                    <i class="fa fa-plus me-1"></i> Add User
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- AI Status -->
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="mb-0">AI System Status</h5>
                        </div>
                        <div class="card-body">
                            <div class="bg-success bg-opacity-10 p-3 rounded mb-3">
                                <div class="d-flex align-items-center">
                                    <i class="fa fa-check-circle text-success fs-2 me-3"></i>
                                    <div>
                                        <h5 class="mb-1">AI System Active</h5>
                                        <p class="mb-0 small">All AI features are running correctly</p>
                                    </div>
                                </div>
                            </div>
                            
                            <h6>Feature Usage</h6>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Feature</th>
                                            <th class="text-end">Status</th>
                                            <th class="text-end">Usage</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Content Recommendations</td>
                                            <td class="text-end"><span class="badge bg-success">Active</span></td>
                                            <td class="text-end">152</td>
                                        </tr>
                                        <tr>
                                            <td>Automated Feedback</td>
                                            <td class="text-end"><span class="badge bg-success">Active</span></td>
                                            <td class="text-end">87</td>
                                        </tr>
                                        <tr>
                                            <td>Personalized Learning</td>
                                            <td class="text-end"><span class="badge bg-success">Active</span></td>
                                            <td class="text-end">64</td>
                                        </tr>
                                        <tr>
                                            <td>Chatbot Assistant</td>
                                            <td class="text-end"><span class="badge bg-success">Active</span></td>
                                            <td class="text-end">219</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <button class="btn btn-sm btn-outline-primary">
                                    <i class="fa fa-cog me-1"></i> AI Settings
                                </button>
                                <button class="btn btn-sm btn-outline-secondary">
                                    <i class="fa fa-list me-1"></i> View Logs
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="mb-0">Quick Actions</h5>
                        </div>
                        <div class="card-body">
                            <div class="list-group list-group-flush">
                                <a href="#" class="list-group-item list-group-item-action border-0">
                                    <div class="d-flex align-items-center">
                                        <div class="action-icon bg-primary text-white rounded-circle me-3">
                                            <i class="fa fa-user-plus"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-1">Add New User</h6>
                                            <p class="mb-0 small text-muted">Create a new student, teacher, or admin account</p>
                                        </div>
                                    </div>
                                </a>
                                
                                <a href="#" class="list-group-item list-group-item-action border-0">
                                    <div class="d-flex align-items-center">
                                        <div class="action-icon bg-success text-white rounded-circle me-3">
                                            <i class="fa fa-book"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-1">Create New Course</h6>
                                            <p class="mb-0 small text-muted">Set up a new course with AI-assisted content</p>
                                        </div>
                                    </div>
                                </a>
                                
                                <a href="#" class="list-group-item list-group-item-action border-0">
                                    <div class="d-flex align-items-center">
                                        <div class="action-icon bg-info text-white rounded-circle me-3">
                                            <i class="fa fa-cog"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-1">System Settings</h6>
                                            <p class="mb-0 small text-muted">Configure site-wide settings and preferences</p>
                                        </div>
                                    </div>
                                </a>
                                
                                <a href="#" class="list-group-item list-group-item-action border-0">
                                    <div class="d-flex align-items-center">
                                        <div class="action-icon bg-warning text-white rounded-circle me-3">
                                            <i class="fa fa-download"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-1">Backup System</h6>
                                            <p class="mb-0 small text-muted">Create a full backup of the system</p>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Recent Activity and System Health -->
            <div class="row">
                <!-- Recent Activity -->
                <div class="col-md-7">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Recent Activity</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Time</th>
                                            <th>User</th>
                                            <th>Action</th>
                                            <th>Details</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>5 mins ago</td>
                                            <td>John Doe</td>
                                            <td>Submitted</td>
                                            <td>Assignment "VR Environment Design"</td>
                                        </tr>
                                        <tr>
                                            <td>18 mins ago</td>
                                            <td>Dr. Sarah Chen</td>
                                            <td>Created</td>
                                            <td>New assignment in "Introduction to VR"</td>
                                        </tr>
                                        <tr>
                                            <td>32 mins ago</td>
                                            <td>System</td>
                                            <td>Generated</td>
                                            <td>Daily analytics report</td>
                                        </tr>
                                        <tr>
                                            <td>1 hour ago</td>
                                            <td>Jane Smith</td>
                                            <td>Enrolled</td>
                                            <td>Course "Advanced Augmented Reality"</td>
                                        </tr>
                                        <tr>
                                            <td>2 hours ago</td>
                                            <td>Admin</td>
                                            <td>Updated</td>
                                            <td>System settings</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="text-end">
                                <a href="#" class="btn btn-sm btn-outline-primary">View All Activity</a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- System Health -->
                <div class="col-md-5">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">System Health</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <!-- Storage -->
                                <div class="col-md-6 mb-3">
                                    <h6>Storage Usage</h6>
                                    <div class="mt-2">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span>Used: 2.4 GB</span>
                                            <span>75%</span>
                                        </div>
                                        <div class="progress">
                                            <div class="progress-bar bg-primary" role="progressbar" style="width: 75%"></div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Database -->
                                <div class="col-md-6 mb-3">
                                    <h6>Database</h6>
                                    <div class="mt-2">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span>Size: 450 MB</span>
                                            <span>45%</span>
                                        </div>
                                        <div class="progress">
                                            <div class="progress-bar bg-success" role="progressbar" style="width: 45%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- System Checks -->
                            <h6 class="mt-3">System Checks</h6>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <span><i class="fa fa-check-circle text-success me-2"></i> PHP Version</span>
                                    <span><?php echo phpversion(); ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <span><i class="fa fa-check-circle text-success me-2"></i> Database Connection</span>
                                    <span class="badge bg-success">Healthy</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <span><i class="fa fa-check-circle text-success me-2"></i> File Permissions</span>
                                    <span class="badge bg-success">Correct</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <span><i class="fa fa-check-circle text-success me-2"></i> SSL Certificate</span>
                                    <span class="badge bg-success">Valid</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <span><i class="fa fa-check-circle text-success me-2"></i> OpenAI API</span>
                                    <span class="badge bg-success">Connected</span>
                                </li>
                            </ul>
                            
                            <div class="d-flex justify-content-between mt-3">
                                <button class="btn btn-sm btn-outline-primary">Run All Checks</button>
                                <button class="btn btn-sm btn-outline-danger">View Issues</button>
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