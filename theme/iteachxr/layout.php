<?php
// iTeachXR Theme - Layout file
// Provides the main layout structure for the iTeachXR theme

/**
 * Get the body classes based on the current page
 * @return string Space-separated list of body classes
 */
function iteachxr_body_classes() {
    global $PAGE;
    
    $classes = array('iteachxr-theme');
    
    // Add layout class
    $classes[] = $PAGE->layout . '-layout';
    
    // Add user role class if available
    if (isloggedin() && !isguestuser()) {
        global $USER;
        if (isset($USER->role)) {
            $classes[] = 'role-' . $USER->role;
        }
    }
    
    // Add class for guest users
    if (isguestuser()) {
        $classes[] = 'guest-user';
    }
    
    // Add class for logged-in users
    if (isloggedin()) {
        $classes[] = 'logged-in';
    } else {
        $classes[] = 'not-logged-in';
    }
    
    return implode(' ', $classes);
}

/**
 * Render the page header
 */
function iteachxr_header() {
    global $CFG, $PAGE, $USER, $SITE;
    
    $title = $PAGE->title;
    $heading = $PAGE->heading;
    
    // Build the page header HTML
    $output = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . $title . '</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/font-awesome@4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="' . $CFG->wwwroot . '/theme/iteachxr/styles.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="' . $CFG->wwwroot . '/theme/iteachxr/javascript.js"></script>
</head>
<body class="' . iteachxr_body_classes() . '">
    <div id="page-wrapper">
        <header id="page-header">
            <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
                <div class="container-fluid">
                    <a class="navbar-brand" href="' . $CFG->wwwroot . '/">
                        <i class="fa fa-graduation-cap me-2"></i>iTeachXR
                    </a>
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="navbarNavDropdown">
                        <ul class="navbar-nav me-auto">';
    
    // Main navigation items
    $output .= '
                            <li class="nav-item">
                                <a class="nav-link" href="' . $CFG->wwwroot . '/course/index.php">
                                    <i class="fa fa-book me-1"></i>Courses
                                </a>
                            </li>';
    
    // Show different navigation options based on user role
    if (isloggedin() && !isguestuser()) {
        if (isset($USER->role) && $USER->role === 'teacher') {
            // Teacher navigation items
            $output .= '
                            <li class="nav-item">
                                <a class="nav-link" href="' . $CFG->wwwroot . '/course/edit.php">
                                    <i class="fa fa-plus-circle me-1"></i>Create Course
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="' . $CFG->wwwroot . '/mod/assignment/index.php">
                                    <i class="fa fa-tasks me-1"></i>Assignments
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="' . $CFG->wwwroot . '/grade/report/index.php">
                                    <i class="fa fa-chart-bar me-1"></i>Gradebook
                                </a>
                            </li>';
        } elseif (isset($USER->role) && $USER->role === 'admin') {
            // Admin navigation items
            $output .= '
                            <li class="nav-item">
                                <a class="nav-link" href="' . $CFG->wwwroot . '/admin/index.php">
                                    <i class="fa fa-cogs me-1"></i>Site Administration
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="' . $CFG->wwwroot . '/admin/user.php">
                                    <i class="fa fa-users me-1"></i>Users
                                </a>
                            </li>';
        } else {
            // Student navigation items
            $output .= '
                            <li class="nav-item">
                                <a class="nav-link" href="' . $CFG->wwwroot . '/calendar/view.php">
                                    <i class="fa fa-calendar me-1"></i>Calendar
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="' . $CFG->wwwroot . '/mod/assignment/index.php">
                                    <i class="fa fa-tasks me-1"></i>My Assignments
                                </a>
                            </li>';
        }
    }
    
    $output .= '
                        </ul>';
    
    // User menu for logged-in users
    if (isloggedin() && !isguestuser()) {
        $output .= '
                        <div class="d-flex">
                            <ul class="navbar-nav">
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownUser" role="button" data-bs-toggle="dropdown">
                                        <i class="fa fa-user-circle me-1"></i>' . $USER->firstname . ' ' . $USER->lastname . '
                                    </a>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><a class="dropdown-item" href="' . $CFG->wwwroot . '/user/profile.php"><i class="fa fa-user me-2"></i>Profile</a></li>
                                        <li><a class="dropdown-item" href="' . $CFG->wwwroot . '/user/preferences.php"><i class="fa fa-cog me-2"></i>Preferences</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item" href="' . $CFG->wwwroot . '/login/logout.php"><i class="fa fa-sign-out me-2"></i>Logout</a></li>
                                    </ul>
                                </li>
                            </ul>
                        </div>';
    } else {
        // Login button for not logged-in users
        $output .= '
                        <div class="d-flex">
                            <a href="' . $CFG->wwwroot . '/login/index.php" class="btn btn-outline-light">
                                <i class="fa fa-sign-in me-1"></i>Login
                            </a>
                        </div>';
    }
    
    $output .= '
                    </div>
                </div>
            </nav>';
    
    // Page heading section
    $output .= '
            <div class="page-header-content py-3">
                <div class="container">
                    <h1>' . $heading . '</h1>
                </div>
            </div>
        </header>
        <div id="page-content" class="container py-4">';
    
    return $output;
}

/**
 * Render the page footer
 */
function iteachxr_footer() {
    global $CFG;
    
    $current_year = date('Y');
    
    // Build the page footer HTML
    $output = '
        </div><!-- End #page-content -->
        <footer id="page-footer" class="bg-light py-4 mt-auto">
            <div class="container">
                <div class="row">
                    <div class="col-md-6">
                        <h5 class="text-primary">iTeachXR</h5>
                        <p>An AI-enhanced version of Moodle LMS optimized for Replit</p>
                    </div>
                    <div class="col-md-3">
                        <h5>Resources</h5>
                        <ul class="list-unstyled">
                            <li><a href="' . $CFG->wwwroot . '/help.php">Help Center</a></li>
                            <li><a href="' . $CFG->wwwroot . '/about.php">About</a></li>
                            <li><a href="' . $CFG->wwwroot . '/contact.php">Contact</a></li>
                        </ul>
                    </div>
                    <div class="col-md-3">
                        <h5>Legal</h5>
                        <ul class="list-unstyled">
                            <li><a href="' . $CFG->wwwroot . '/privacy.php">Privacy Policy</a></li>
                            <li><a href="' . $CFG->wwwroot . '/terms.php">Terms of Service</a></li>
                        </ul>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-12 text-center">
                        <p>&copy; ' . $current_year . ' iTeachXR - All rights reserved.</p>
                    </div>
                </div>
            </div>
        </footer>
    </div><!-- End #page-wrapper -->
</body>
</html>';
    
    return $output;
}

// Standard Moodle page rendering functions
function standard_head_html() {
    return '';  // Empty since our header already includes everything we need
}

function standard_top_of_body_html() {
    return '';  // Empty since our header already includes everything we need
}

function standard_end_of_body_html() {
    return '';  // Empty since our footer already includes everything we need
}
