<?php
// iTeachXR - AI Service API

// Include the database connection file
require_once('../lib/db_connection.php');

// Set content type to JSON
header('Content-Type: application/json');

/**
 * Execute a Python AI helper command and return the result
 * 
 * @param string $command The AI helper command to run
 * @param array $args Arguments to pass to the command
 * @return array The command result
 */
function execute_ai_helper($command, $args = []) {
    // Set the OPENAI_API_KEY environment variable directly for the process
    $env = $_ENV;
    $env['OPENAI_API_KEY'] = getenv('OPENAI_API_KEY');
    
    // Construct the command
    $cmd = escapeshellcmd('python3 ' . dirname(__DIR__) . '/ai/openai_helper.py ' . $command);
    
    // Add any arguments
    foreach ($args as $arg) {
        $cmd .= ' ' . escapeshellarg($arg);
    }
    
    // Set up the descriptors for proc_open
    $descriptorspec = [
        0 => ["pipe", "r"],  // stdin
        1 => ["pipe", "w"],  // stdout
        2 => ["pipe", "w"]   // stderr
    ];
    
    // Open the process with environment variables
    $process = proc_open($cmd, $descriptorspec, $pipes, NULL, $env);
    
    if (is_resource($process)) {
        // Close unused stdin
        fclose($pipes[0]);
        
        // Read stdout
        $output = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        
        // Read stderr (for debugging)
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[2]);
        
        // Close the process
        $return_value = proc_close($process);
        
        // If there's an error, log it for debugging
        if ($return_value !== 0) {
            error_log("AI helper error ($return_value): $stderr");
        }
    } else {
        return ['error' => 'Failed to execute AI helper process'];
    }
    
    // Parse the result
    if ($output) {
        $result = json_decode($output, true);
        
        // Check if we need to request an API key
        if (isset($result['error']) && 
            (isset($result['request_api_key']) && $result['request_api_key'] === true ||
             strpos($result['error'], 'insufficient_quota') !== false ||
             strpos($result['error'], 'invalid_api_key') !== false)) {
            
            return [
                'error' => $result['error'],
                'api_key_error' => true,
                'message' => 'Please provide a valid OpenAI API key with available quota to use this feature.'
            ];
        }
        
        return $result;
    }
    
    return ['error' => 'Failed to execute AI helper: No output received'];
}

/**
 * Generate a course structure
 */
function generate_course_structure() {
    // Validate inputs
    if (!isset($_POST['name']) || empty($_POST['name'])) {
        echo json_encode(['error' => 'Course name is required']);
        return;
    }
    
    $name = $_POST['name'];
    $topic = isset($_POST['topic']) ? $_POST['topic'] : null;
    $level = isset($_POST['level']) ? $_POST['level'] : 'undergraduate';
    $duration = isset($_POST['duration']) ? $_POST['duration'] : 'medium';
    
    // Execute the AI helper
    $args = [$name];
    if ($topic) $args[] = $topic;
    if ($level) $args[] = $level;
    if ($duration) $args[] = $duration;
    
    $result = execute_ai_helper('course_structure', $args);
    
    // Return the result
    echo json_encode($result);
}

/**
 * Generate automated feedback for a submission
 */
function generate_automated_feedback() {
    // Validate inputs
    if (!isset($_POST['submission_text']) || empty($_POST['submission_text'])) {
        echo json_encode(['error' => 'Submission text is required']);
        return;
    }
    
    if (!isset($_POST['assignment_details']) || empty($_POST['assignment_details'])) {
        echo json_encode(['error' => 'Assignment details are required']);
        return;
    }
    
    $submission_text = $_POST['submission_text'];
    $assignment_details = $_POST['assignment_details'];
    
    // Execute the AI helper
    $result = execute_ai_helper('feedback', [$submission_text, $assignment_details]);
    
    // Return the result
    echo json_encode($result);
}

/**
 * Generate a personalized learning path
 */
function generate_learning_path() {
    // Validate inputs
    if (!isset($_POST['student_profile']) || empty($_POST['student_profile'])) {
        echo json_encode(['error' => 'Student profile is required']);
        return;
    }
    
    if (!isset($_POST['course_content']) || empty($_POST['course_content'])) {
        echo json_encode(['error' => 'Course content is required']);
        return;
    }
    
    $student_profile = $_POST['student_profile'];
    $course_content = $_POST['course_content'];
    
    // Execute the AI helper
    $result = execute_ai_helper('learning_path', [$student_profile, $course_content]);
    
    // Return the result
    echo json_encode($result);
}

/**
 * Process an AI assistant query
 */
function process_assistant_query() {
    // Validate inputs
    if (!isset($_POST['query']) || empty($_POST['query'])) {
        echo json_encode(['error' => 'Query is required']);
        return;
    }
    
    $query = $_POST['query'];
    $user_context = isset($_POST['user_context']) ? $_POST['user_context'] : null;
    
    // Execute the AI helper
    $args = [$query];
    if ($user_context) $args[] = $user_context;
    
    $result = execute_ai_helper('assistant', $args);
    
    // Return the result
    echo json_encode($result);
}

/**
 * Save AI-generated data to the database
 */
function save_ai_data() {
    // Validate inputs
    if (!isset($_POST['type']) || empty($_POST['type'])) {
        echo json_encode(['error' => 'Data type is required']);
        return;
    }
    
    if (!isset($_POST['data']) || empty($_POST['data'])) {
        echo json_encode(['error' => 'Data is required']);
        return;
    }
    
    $db = get_db_connection();
    if (!$db) {
        echo json_encode(['error' => 'Database connection failed']);
        return;
    }
    
    $type = $_POST['type'];
    $data = $_POST['data'];
    $user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : null;
    $course_id = isset($_POST['course_id']) ? (int)$_POST['course_id'] : null;
    
    // Process based on type
    switch ($type) {
        case 'course_structure':
            // Save course structure
            try {
                // Start a transaction
                $db->beginTransaction();
                
                // Decode the data
                $structure = json_decode($data, true);
                if (!$structure) {
                    throw new Exception('Invalid course structure data');
                }
                
                // Insert the course
                $stmt = $db->prepare("
                    INSERT INTO courses (title, description, created_by, level)
                    VALUES (:title, :description, :created_by, :level)
                    RETURNING id
                ");
                
                $stmt->execute([
                    'title' => $structure['title'] ?? 'New Course',
                    'description' => $structure['description'] ?? '',
                    'created_by' => $user_id,
                    'level' => $structure['level'] ?? 'undergraduate'
                ]);
                
                $course_id = $stmt->fetchColumn();
                
                // Insert modules
                if (isset($structure['data']['modules']) && is_array($structure['data']['modules'])) {
                    $moduleStmt = $db->prepare("
                        INSERT INTO modules (course_id, title, description, sequence)
                        VALUES (:course_id, :title, :description, :sequence)
                    ");
                    
                    $sequence = 1;
                    foreach ($structure['data']['modules'] as $module) {
                        $moduleStmt->execute([
                            'course_id' => $course_id,
                            'title' => $module['title'],
                            'description' => $module['description'] ?? '',
                            'sequence' => $sequence++
                        ]);
                    }
                }
                
                // Commit the transaction
                $db->commit();
                
                echo json_encode(['success' => true, 'course_id' => $course_id]);
            } catch (Exception $e) {
                // Rollback the transaction
                $db->rollBack();
                echo json_encode(['error' => 'Failed to save course structure: ' . $e->getMessage()]);
            }
            break;
            
        case 'learning_path':
            // Save personalized learning path
            try {
                $stmt = $db->prepare("
                    INSERT INTO learning_paths (user_id, course_id, path_data)
                    VALUES (:user_id, :course_id, :path_data)
                    ON CONFLICT (user_id, course_id) 
                    DO UPDATE SET path_data = :path_data, updated_at = CURRENT_TIMESTAMP
                    RETURNING id
                ");
                
                $stmt->execute([
                    'user_id' => $user_id,
                    'course_id' => $course_id,
                    'path_data' => $data
                ]);
                
                $path_id = $stmt->fetchColumn();
                
                echo json_encode(['success' => true, 'path_id' => $path_id]);
            } catch (Exception $e) {
                echo json_encode(['error' => 'Failed to save learning path: ' . $e->getMessage()]);
            }
            break;
            
        case 'feedback':
            // Save automated feedback
            try {
                $submission_id = isset($_POST['submission_id']) ? (int)$_POST['submission_id'] : null;
                
                if (!$submission_id) {
                    echo json_encode(['error' => 'Submission ID is required']);
                    return;
                }
                
                $stmt = $db->prepare("
                    UPDATE submissions
                    SET ai_feedback = :ai_feedback
                    WHERE id = :submission_id
                    RETURNING id
                ");
                
                $stmt->execute([
                    'ai_feedback' => $data,
                    'submission_id' => $submission_id
                ]);
                
                $updated_id = $stmt->fetchColumn();
                
                if (!$updated_id) {
                    echo json_encode(['error' => 'Submission not found']);
                    return;
                }
                
                echo json_encode(['success' => true, 'submission_id' => $updated_id]);
            } catch (Exception $e) {
                echo json_encode(['error' => 'Failed to save feedback: ' . $e->getMessage()]);
            }
            break;
            
        case 'recommendation':
            // Save content recommendation
            try {
                $stmt = $db->prepare("
                    INSERT INTO ai_recommendations (user_id, course_id, content, type, relevance_score)
                    VALUES (:user_id, :course_id, :content, :rec_type, :relevance_score)
                    RETURNING id
                ");
                
                $rec_type = isset($_POST['rec_type']) ? $_POST['rec_type'] : 'content';
                $relevance = isset($_POST['relevance']) ? (float)$_POST['relevance'] : 0.8;
                
                $stmt->execute([
                    'user_id' => $user_id,
                    'course_id' => $course_id,
                    'content' => $data,
                    'rec_type' => $rec_type,
                    'relevance_score' => $relevance
                ]);
                
                $rec_id = $stmt->fetchColumn();
                
                echo json_encode(['success' => true, 'recommendation_id' => $rec_id]);
            } catch (Exception $e) {
                echo json_encode(['error' => 'Failed to save recommendation: ' . $e->getMessage()]);
            }
            break;
            
        default:
            echo json_encode(['error' => 'Unknown data type']);
    }
}

// Process the request
$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    case 'course_structure':
        generate_course_structure();
        break;
        
    case 'feedback':
        generate_automated_feedback();
        break;
        
    case 'learning_path':
        generate_learning_path();
        break;
        
    case 'assistant':
        process_assistant_query();
        break;
        
    case 'save':
        save_ai_data();
        break;
        
    default:
        echo json_encode(['error' => 'Unknown action']);
}
?>