<?php
// iTeachXR - Core library functions
// Modified from Moodle for Replit environment

// Prevent direct access to this file
defined('MOODLE_INTERNAL') || die();

/**
 * Returns the current user record
 * @return stdClass current user object
 */
function get_iteachxr_current_user() {
    global $USER;
    return $USER;
}

/**
 * Check if user has a specific role in a context
 * @param string $role Role to check for
 * @param int $userid User ID to check
 * @param context $context Context to check in
 * @return bool Whether the user has the role
 */
function user_has_role($role, $userid, $context) {
    global $DB;
    
    // Simple implementation for demo
    // In a full system, this would check the role assignments table
    
    // For now, just check user's role property
    $user = get_user_by_id($userid);
    return isset($user->role) && $user->role === $role;
}

/**
 * Get a user by ID
 * @param int $userid User ID
 * @return stdClass User object
 */
function get_user_by_id($userid) {
    global $DB;
    
    // Simple implementation for demo
    // In a full system, this would query the user table
    
    $stmt = $DB->prepare("SELECT * FROM mdl_user WHERE id = :userid");
    $stmt->execute(['userid' => $userid]);
    return $stmt->fetch(PDO::FETCH_OBJ);
}

/**
 * Get courses for a specific user
 * @param int $userid User ID
 * @return array Array of course objects
 */
function get_user_courses($userid) {
    global $DB;
    
    // Simple implementation for demo
    // In a full system, this would join user_enrolments, enrol, and course tables
    
    $stmt = $DB->prepare("
        SELECT c.* 
        FROM mdl_course c
        JOIN mdl_enrol e ON e.courseid = c.id
        JOIN mdl_user_enrolments ue ON ue.enrolid = e.id
        WHERE ue.userid = :userid
    ");
    $stmt->execute(['userid' => $userid]);
    return $stmt->fetchAll(PDO::FETCH_OBJ);
}

/**
 * Get a specific course by ID
 * @param int $courseid Course ID
 * @return stdClass Course object
 */
function get_course($courseid) {
    global $DB;
    
    $stmt = $DB->prepare("SELECT * FROM mdl_course WHERE id = :courseid");
    $stmt->execute(['courseid' => $courseid]);
    return $stmt->fetch(PDO::FETCH_OBJ);
}

/**
 * Get recent activity for a user
 * @param int $userid User ID
 * @param int $limit Maximum number of items to return
 * @return array Array of activity objects
 */
function get_recent_activity($userid, $limit = 10) {
    global $DB;
    
    // Simple implementation for demo
    // In a full system, this would query the log store
    
    $stmt = $DB->prepare("
        SELECT l.*, c.fullname as course_name
        FROM mdl_logstore_standard_log l
        LEFT JOIN mdl_course c ON c.id = l.courseid
        WHERE l.userid = :userid
        ORDER BY l.timecreated DESC
        LIMIT :limit
    ");
    $stmt->bindParam(':userid', $userid, PDO::PARAM_INT);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_OBJ);
}

/**
 * Format a timestamp as a human-readable date/time
 * @param int $timestamp Unix timestamp
 * @param string $format Format string (default: 'strftimedatetime')
 * @return string Formatted date/time
 */
function format_time($timestamp, $format = 'strftimedatetime') {
    if ($format === 'strftimedatetime') {
        return date('M d, Y, h:i A', $timestamp);
    } elseif ($format === 'strftimedate') {
        return date('M d, Y', $timestamp);
    } elseif ($format === 'strftimetime') {
        return date('h:i A', $timestamp);
    } else {
        return date($format, $timestamp);
    }
}

/**
 * Integrate with the AI system to get content recommendations
 * @param int $userid User ID
 * @param int $courseid Course ID
 * @return array Recommended content
 */
function get_ai_content_recommendations($userid, $courseid) {
    global $CFG;
    
    if (!$CFG->enableAI || !$CFG->aiFeatures['content_recommendations']) {
        return [];
    }
    
    // Call Python AI module
    $command = "python3 {$CFG->dirroot}/ai/content_recommendations.py --user=$userid --course=$courseid";
    $output = shell_exec($command);
    
    if ($output) {
        return json_decode($output, true);
    }
    
    return [];
}

/**
 * Integrate with the AI system to get automated feedback on student work
 * @param int $submissionid Submission ID
 * @return string AI-generated feedback
 */
function get_ai_automated_feedback($submissionid) {
    global $CFG;
    
    if (!$CFG->enableAI || !$CFG->aiFeatures['automated_feedback']) {
        return '';
    }
    
    // Call Python AI module
    $command = "python3 {$CFG->dirroot}/ai/automated_feedback.py --submission=$submissionid";
    $output = shell_exec($command);
    
    if ($output) {
        $result = json_decode($output, true);
        return $result['feedback'] ?? '';
    }
    
    return '';
}

/**
 * Check if a user can access a course
 * @param int $userid User ID
 * @param int $courseid Course ID
 * @return bool Whether the user can access the course
 */
function can_access_course($userid, $courseid) {
    global $DB;
    
    // Simple implementation for demo
    // Check if user is enrolled in the course
    
    $stmt = $DB->prepare("
        SELECT COUNT(*) as count
        FROM mdl_enrol e
        JOIN mdl_user_enrolments ue ON ue.enrolid = e.id
        WHERE e.courseid = :courseid AND ue.userid = :userid
    ");
    $stmt->execute(['courseid' => $courseid, 'userid' => $userid]);
    $result = $stmt->fetch(PDO::FETCH_OBJ);
    
    return ($result && $result->count > 0);
}

/**
 * Get course modules for a specific course
 * @param int $courseid Course ID
 * @return array Array of course module objects
 */
function get_course_modules($courseid) {
    global $DB;
    
    $stmt = $DB->prepare("
        SELECT cm.*, m.name as modname
        FROM mdl_course_modules cm
        JOIN mdl_modules m ON m.id = cm.module
        WHERE cm.course = :courseid
        ORDER BY cm.section, cm.sequence
    ");
    $stmt->execute(['courseid' => $courseid]);
    return $stmt->fetchAll(PDO::FETCH_OBJ);
}

/**
 * Get assignments for a specific course
 * @param int $courseid Course ID
 * @return array Array of assignment objects
 */
function get_course_assignments($courseid) {
    global $DB;
    
    $stmt = $DB->prepare("
        SELECT a.*, cm.id as cmid
        FROM mdl_assign a
        JOIN mdl_course_modules cm ON cm.instance = a.id
        JOIN mdl_modules m ON m.id = cm.module
        WHERE a.course = :courseid AND m.name = 'assign'
        ORDER BY a.duedate
    ");
    $stmt->execute(['courseid' => $courseid]);
    return $stmt->fetchAll(PDO::FETCH_OBJ);
}

/**
 * Get submissions for a specific assignment
 * @param int $assignid Assignment ID
 * @return array Array of submission objects
 */
function get_assignment_submissions($assignid) {
    global $DB;
    
    $stmt = $DB->prepare("
        SELECT s.*, u.firstname, u.lastname
        FROM mdl_assign_submission s
        JOIN mdl_user u ON u.id = s.userid
        WHERE s.assignment = :assignid
        ORDER BY s.timemodified DESC
    ");
    $stmt->execute(['assignid' => $assignid]);
    return $stmt->fetchAll(PDO::FETCH_OBJ);
}

/**
 * Get grades for a specific user in a specific course
 * @param int $userid User ID
 * @param int $courseid Course ID
 * @return array Array of grade objects
 */
function get_user_grades($userid, $courseid) {
    global $DB;
    
    $stmt = $DB->prepare("
        SELECT g.*, gi.itemname, gi.itemtype
        FROM mdl_grade_grades g
        JOIN mdl_grade_items gi ON gi.id = g.itemid
        WHERE g.userid = :userid AND gi.courseid = :courseid
        ORDER BY gi.sortorder
    ");
    $stmt->execute(['userid' => $userid, 'courseid' => $courseid]);
    return $stmt->fetchAll(PDO::FETCH_OBJ);
}

/**
 * Check if the current request is AJAX
 * @return bool Whether the request is AJAX
 */
function is_ajax() {
    return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
}

/**
 * Return a JSON response for AJAX requests
 * @param mixed $data Data to return
 * @param bool $success Whether the request was successful
 * @param string $message Optional message
 */
function ajax_response($data, $success = true, $message = '') {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

/**
 * Get files for a specific course module
 * @param int $cmid Course module ID
 * @return array Array of file objects
 */
function get_module_files($cmid) {
    global $DB;
    
    $stmt = $DB->prepare("
        SELECT f.*
        FROM mdl_files f
        JOIN mdl_context ctx ON ctx.id = f.contextid
        WHERE ctx.instanceid = :cmid AND ctx.contextlevel = 70
        AND f.filearea <> 'draft' AND f.filename <> '.'
        ORDER BY f.filepath, f.filename
    ");
    $stmt->execute(['cmid' => $cmid]);
    return $stmt->fetchAll(PDO::FETCH_OBJ);
}

/**
 * Integrate with the AI system for chatbot assistant functionality
 * @param string $query User's query
 * @param int $userid User ID
 * @param string $context Context (e.g., 'course', 'assignment')
 * @param int $contextid Context ID (e.g., course ID, assignment ID)
 * @return string AI-generated response
 */
function get_ai_assistant_response($query, $userid, $context = null, $contextid = null) {
    global $CFG;
    
    if (!$CFG->enableAI || !$CFG->aiFeatures['chatbot_assistant']) {
        return 'AI assistant is currently disabled.';
    }
    
    // Prepare parameters
    $params = [
        'query' => $query,
        'userid' => $userid
    ];
    
    if ($context) {
        $params['context'] = $context;
    }
    
    if ($contextid) {
        $params['contextid'] = $contextid;
    }
    
    // Call Python AI module
    $command = "python3 {$CFG->dirroot}/ai/ai_assistant.py " . 
               escapeshellarg(json_encode($params));
    $output = shell_exec($command);
    
    if ($output) {
        $result = json_decode($output, true);
        return $result['response'] ?? 'Sorry, I could not process your request.';
    }
    
    return 'Sorry, I could not process your request.';
}
