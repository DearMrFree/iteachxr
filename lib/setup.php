<?php
// iTeachXR - Modified Moodle setup library

// Prevent direct access
defined('MOODLE_INTERNAL') || die();

// Setup basic Moodle environment
global $CFG, $DB, $USER, $SESSION, $SITE, $PAGE, $OUTPUT;

// Initialize standard libraries
require_once($CFG->dirroot . '/lib/moodlelib.php');

// Setup custom error handling
set_error_handler('iteachxr_error_handler');
set_exception_handler('iteachxr_exception_handler');

/**
 * Custom error handler for iTeachXR
 */
function iteachxr_error_handler($errno, $errstr, $errfile, $errline) {
    if (!(error_reporting() & $errno)) {
        // This error code is not included in error_reporting
        return false;
    }

    switch ($errno) {
        case E_USER_ERROR:
            echo "<div class='error'>";
            echo "<p>Fatal Error: $errstr</p>";
            echo "<p>Error on line $errline in file $errfile</p>";
            echo "</div>";
            exit(1);
            break;

        case E_USER_WARNING:
            echo "<div class='warning'>";
            echo "<p>Warning: $errstr</p>";
            echo "</div>";
            break;

        case E_USER_NOTICE:
            echo "<div class='notice'>";
            echo "<p>Notice: $errstr</p>";
            echo "</div>";
            break;

        default:
            echo "<div class='warning'>";
            echo "<p>Unknown error type [$errno]: $errstr</p>";
            echo "</div>";
            break;
    }

    // Log error
    error_log("Error [$errno]: $errstr in $errfile on line $errline");
    
    return true;
}

/**
 * Custom exception handler for iTeachXR
 */
function iteachxr_exception_handler($exception) {
    echo "<div class='error'>";
    echo "<h2>Unhandled Exception</h2>";
    echo "<p>" . $exception->getMessage() . "</p>";
    echo "<p>File: " . $exception->getFile() . " on line " . $exception->getLine() . "</p>";
    echo "</div>";
    
    // Log exception
    error_log("Exception: " . $exception->getMessage() . " in " . $exception->getFile() . " on line " . $exception->getLine());
}

/**
 * Initializes session and checks authentication
 */
function init_session() {
    global $CFG, $USER, $SESSION;
    
    // Start session if not already started
    if (!session_id()) {
        session_start();
    }
    
    // Initialize $USER if not set
    if (!isset($USER) || empty($USER->id)) {
        $USER = new stdClass();
        $USER->id = 0;  // Not logged in
        $USER->confirmed = false;
        $USER->username = 'guest';
        $USER->firstname = 'Guest';
        $USER->lastname = 'User';
        $USER->email = '';
        $USER->picture = 0;
        $USER->lang = $CFG->lang;
        $USER->theme = $CFG->theme;
        $USER->timezone = 99;  // Server's timezone
        $USER->trackforums = 0;
        $USER->mnethostid = 1;  // Local host
    }
    
    // Initialize $SESSION if not set
    if (!isset($SESSION)) {
        $SESSION = new stdClass();
    }
}

/**
 * Checks if a user is logged in
 */
function isloggedin() {
    global $USER;
    return (!empty($USER->id) && $USER->id > 0);
}

/**
 * Checks if a user is a guest
 */
function isguestuser() {
    global $USER;
    return ($USER->username === 'guest');
}

/**
 * Redirects to a different URL
 */
function redirect($url, $message = '', $delay = 0) {
    if ($delay == 0 && empty($message)) {
        header('Location: ' . $url);
        exit;
    }
    
    echo '<!DOCTYPE html>
    <html>
    <head>
        <meta http-equiv="refresh" content="' . $delay . '; url=' . $url . '">
        <title>Redirecting...</title>
    </head>
    <body>
        <h1>Redirecting...</h1>
        <p>' . $message . '</p>
        <p>If you are not redirected automatically, follow this <a href="' . $url . '">link</a>.</p>
    </body>
    </html>';
    exit;
}

/**
 * Check if user is a site admin
 */
function is_siteadmin() {
    global $USER;
    // Simple implementation for demonstration
    // In a full system, we would check against the database
    return isset($USER->role) && $USER->role === 'admin';
}

/**
 * Initialize the database connection
 */
function init_database() {
    global $CFG, $DB;
    
    // Initialize database connection (simplified for demonstration)
    // In a full system, we would use proper Moodle DB abstraction
    try {
        $dsn = $CFG->dbtype . ':host=' . $CFG->dbhost . ';dbname=' . $CFG->dbname;
        $DB = new PDO($dsn, $CFG->dbuser, $CFG->dbpass);
        $DB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        die('Connection failed: ' . $e->getMessage());
    }
}

/**
 * Check capability (simplified for demo)
 */
function has_capability($capability, $context) {
    global $USER;
    
    // Simplified capability checking for demo
    if ($capability === 'moodle/course:update' && isset($USER->role)) {
        return in_array($USER->role, ['admin', 'teacher']);
    }
    
    return false;
}

// Initialize key components
init_session();
init_database();

// Set up $SITE
$SITE = new stdClass();
$SITE->fullname = 'iTeachXR Learning Management System';
$SITE->shortname = 'iTeachXR';
$SITE->summary = 'An AI-enhanced version of Moodle optimized for Replit';

// Set up $PAGE
$PAGE = new stdClass();
$PAGE->context = null;
$PAGE->layout = 'standard';
$PAGE->title = '';
$PAGE->heading = '';
$PAGE->url = null;

$PAGE->set_context = function($context) use (&$PAGE) {
    $PAGE->context = $context;
};

$PAGE->set_pagelayout = function($layout) use (&$PAGE) {
    $PAGE->layout = $layout;
};

$PAGE->set_title = function($title) use (&$PAGE) {
    $PAGE->title = $title;
};

$PAGE->set_heading = function($heading) use (&$PAGE) {
    $PAGE->heading = $heading;
};

$PAGE->set_url = function($url) use (&$PAGE) {
    $PAGE->url = $url;
};

// Set up $OUTPUT
$OUTPUT = new stdClass();
$OUTPUT->header = function() use ($PAGE) {
    $title = $PAGE->title;
    return "<!DOCTYPE html>
    <html>
    <head>
        <meta charset='utf-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>$title</title>
        <link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css'>
        <link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/font-awesome@4.7.0/css/font-awesome.min.css'>
        <link rel='stylesheet' href='/theme/iteachxr/styles.css'>
        <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js'></script>
        <script src='/theme/iteachxr/javascript.js'></script>
    </head>
    <body class='iteachxr-theme " . $PAGE->layout . "-layout'>
        <header id='page-header'>
            <nav class='navbar navbar-expand-lg navbar-dark bg-primary'>
                <div class='container-fluid'>
                    <a class='navbar-brand' href='/'>iTeachXR</a>
                    <button class='navbar-toggler' type='button' data-bs-toggle='collapse' data-bs-target='#navbarNav'>
                        <span class='navbar-toggler-icon'></span>
                    </button>
                    <div class='collapse navbar-collapse' id='navbarNav'>
                        <ul class='navbar-nav me-auto'>
                            <li class='nav-item'>
                                <a class='nav-link' href='/course/index.php'>Courses</a>
                            </li>
                            <li class='nav-item'>
                                <a class='nav-link' href='/calendar/view.php'>Calendar</a>
                            </li>
                        </ul>
                        <div class='d-flex'>
                            <ul class='navbar-nav'>
                                <li class='nav-item dropdown'>
                                    <a class='nav-link dropdown-toggle' href='#' id='navbarDropdown' role='button' data-bs-toggle='dropdown'>
                                        <i class='fa fa-user-circle'></i> ".($PAGE->context ? get_user_fullname() : 'Account')."
                                    </a>
                                    <ul class='dropdown-menu dropdown-menu-end'>
                                        <li><a class='dropdown-item' href='/user/profile.php'><i class='fa fa-user'></i> Profile</a></li>
                                        <li><a class='dropdown-item' href='/user/preferences.php'><i class='fa fa-cog'></i> Preferences</a></li>
                                        <li><hr class='dropdown-divider'></li>
                                        <li><a class='dropdown-item' href='/login/logout.php'><i class='fa fa-sign-out'></i> Logout</a></li>
                                    </ul>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </nav>
            <div class='page-header-content'>
                <div class='container'>
                    <h1>" . $PAGE->heading . "</h1>
                </div>
            </div>
        </header>
        <div id='page-content' class='container mt-4'>";
};

$OUTPUT->footer = function() {
    return "</div>
        <footer id='page-footer' class='mt-5 py-3 bg-light'>
            <div class='container'>
                <div class='row'>
                    <div class='col-md-6'>
                        <p>&copy; " . date('Y') . " iTeachXR - An AI-enhanced learning platform</p>
                    </div>
                    <div class='col-md-6 text-end'>
                        <p>Powered by Moodle with AI enhancements</p>
                    </div>
                </div>
            </div>
        </footer>
    </body>
    </html>";
};

/**
 * Get the full name of the current user
 */
function get_user_fullname() {
    global $USER;
    return $USER->firstname . ' ' . $USER->lastname;
}

/**
 * Context class (simplified for demo)
 */
class context {
    public $id;
    public $contextlevel;
    public $instanceid;
    
    public function __construct($id, $contextlevel, $instanceid) {
        $this->id = $id;
        $this->contextlevel = $contextlevel;
        $this->instanceid = $instanceid;
    }
    
    public static function instance() {
        return new context(1, 'system', 0);
    }
}

/**
 * System context class
 */
class context_system extends context {
    public static function instance() {
        return new context_system(1, 'system', 0);
    }
}

/**
 * URL class (simplified for demo)
 */
class moodle_url {
    private $url;
    
    public function __construct($url, $params = null) {
        $this->url = $url;
        
        if ($params) {
            $query = http_build_query($params);
            $this->url .= (strpos($url, '?') === false) ? '?' . $query : '&' . $query;
        }
    }
    
    public function __toString() {
        return $this->url;
    }
}

// Clean up some PHP settings
error_reporting($CFG->debug);
ini_set('display_errors', $CFG->debugdisplay ? '1' : '0');
