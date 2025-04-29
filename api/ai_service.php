<?php
// iTeachXR - AI Service API
// Connect the frontend PHP code with the Python AI backend

// Include configuration
require_once('../config.php');
require_once($CFG->dirroot . '/lib/moodlelib.php');

// Set content type to JSON
header('Content-Type: application/json');

// Check if AI is enabled
if (!$CFG->enableAI) {
    echo json_encode([
        'success' => false,
        'message' => 'AI services are currently disabled'
    ]);
    exit;
}

// Check for API key
if (empty($CFG->openai_api_key)) {
    echo json_encode([
        'success' => false,
        'message' => 'OpenAI API key is not configured'
    ]);
    exit;
}

// Get request data
$requestBody = file_get_contents('php://input');
$data = json_decode($requestBody, true);

// Check for valid JSON
if (empty($data) || !is_array($data)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request format'
    ]);
    exit;
}

// Check for required action
if (!isset($data['action']) && !isset($data['query'])) {
    echo json_encode([
        'success' => false,
        'message' => 'No action or query specified'
    ]);
    exit;
}

// Process different types of AI requests
try {
    // If there's a query without a specific action, treat it as an AI assistant query
    if (isset($data['query']) && !isset($data['action'])) {
        process_ai_assistant_query($data);
    } else {
        // Otherwise, dispatch based on action
        $action = $data['action'];
        
        switch ($action) {
            case 'preview_feedback':
                process_feedback_preview($data);
                break;
                
            case 'analyze_assignment':
                process_assignment_analysis($data);
                break;
                
            case 'explain_assignment':
                process_assignment_explanation($data);
                break;
                
            case 'suggest_starting_points':
                process_starting_points($data);
                break;
                
            case 'find_resources':
                process_resource_finder($data);
                break;
                
            case 'generate_course_structure':
                process_course_structure($data);
                break;
                
            case 'generate_assessment':
                process_assessment_generation($data);
                break;
                
            default:
                echo json_encode([
                    'success' => false,
                    'message' => 'Unknown action: ' . $action
                ]);
                exit;
        }
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error processing request: ' . $e->getMessage()
    ]);
    exit;
}

/**
 * Process AI assistant query
 */
function process_ai_assistant_query($data) {
    global $CFG, $USER;
    
    // Check if chatbot assistant is enabled
    if (!$CFG->aiFeatures['chatbot_assistant']) {
        echo json_encode([
            'success' => false,
            'message' => 'AI assistant is currently disabled'
        ]);
        exit;
    }
    
    // Prepare query and context
    $query = $data['query'];
    $context = isset($data['context']) ? $data['context'] : [];
    
    // Add user ID if not present
    if (!isset($context['user_id'])) {
        $context['user_id'] = $USER->id;
    }
    
    // Convert context to JSON
    $contextJson = json_encode($context);
    
    // Call Python script
    $command = escapeshellcmd("python3 {$CFG->dirroot}/ai/ai_assistant.py");
    $escapedQuery = escapeshellarg($query);
    $escapedContext = escapeshellarg($contextJson);
    
    // Run the command
    $jsonInput = json_encode([
        'query' => $query,
        'user_id' => $context['user_id'],
        'context' => $context['context'] ?? null,
        'context_id' => $context['context_id'] ?? null
    ]);
    
    // Execute the Python script
    $descriptorspec = [
        0 => ["pipe", "r"], // stdin
        1 => ["pipe", "w"], // stdout
        2 => ["pipe", "w"]  // stderr
    ];
    
    $process = proc_open("python3 {$CFG->dirroot}/ai/ai_assistant.py", $descriptorspec, $pipes);
    
    if (is_resource($process)) {
        // Write to stdin
        fwrite($pipes[0], $jsonInput);
        fclose($pipes[0]);
        
        // Read from stdout
        $output = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        
        // Read from stderr
        $error = stream_get_contents($pipes[2]);
        fclose($pipes[2]);
        
        $returnValue = proc_close($process);
        
        if ($returnValue !== 0) {
            // Process failed
            echo json_encode([
                'success' => false,
                'message' => 'AI assistant process failed: ' . $error
            ]);
            exit;
        }
        
        // Parse the output
        $result = json_decode($output, true);
        
        if ($result && isset($result['response'])) {
            echo json_encode([
                'success' => true,
                'response' => $result['response']
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to get response from AI assistant'
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to start AI assistant process'
        ]);
    }
}

/**
 * Process assignment submission feedback preview
 */
function process_feedback_preview($data) {
    global $CFG;
    
    // Check if automated feedback is enabled
    if (!$CFG->aiFeatures['automated_feedback']) {
        echo json_encode([
            'success' => false,
            'message' => 'Automated feedback is currently disabled'
        ]);
        exit;
    }
    
    // Check for required fields
    if (!isset($data['submission_text']) || !isset($data['assignment_id'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Missing required fields: submission_text, assignment_id'
        ]);
        exit;
    }
    
    // Prepare data for Python script
    $submissionText = $data['submission_text'];
    $assignmentId = $data['assignment_id'];
    
    // Create a temporary file to store the submission text
    $tempDir = "{$CFG->dataroot}/temp";
    if (!is_dir($tempDir)) {
        mkdir($tempDir, 0777, true);
    }
    
    $tempFile = tempnam($tempDir, 'submission_');
    file_put_contents($tempFile, $submissionText);
    
    // Prepare the assignment data
    $assignmentData = [
        'id' => $assignmentId,
        'name' => $data['assignment_name'] ?? "Assignment $assignmentId",
        'requirements' => $data['assignment_description'] ?? "No description available"
    ];
    
    // Write assignment data to a temporary file
    $assignmentDataFile = tempnam($tempDir, 'assignment_');
    file_put_contents($assignmentDataFile, json_encode($assignmentData));
    
    // Call Python script
    $command = escapeshellcmd("python3 {$CFG->dirroot}/ai/automated_feedback.py");
    $command .= " --submission " . escapeshellarg($assignmentId);
    $command .= " --text " . escapeshellarg($submissionText);
    
    // Execute the command
    $output = shell_exec($command);
    
    // Clean up temporary files
    unlink($tempFile);
    unlink($assignmentDataFile);
    
    // Parse the result
    $result = json_decode($output, true);
    
    if ($result) {
        if (isset($result['error'])) {
            echo json_encode([
                'success' => false,
                'message' => 'Error generating feedback: ' . $result['error']
            ]);
        } else {
            echo json_encode([
                'success' => true,
                'feedback' => $result
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to generate feedback. Invalid response from AI service.'
        ]);
    }
}

/**
 * Process assignment analysis
 */
function process_assignment_analysis($data) {
    global $CFG;
    
    // Check for required fields
    if (!isset($data['assignment_id']) || !isset($data['assignment_name'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Missing required fields: assignment_id, assignment_name'
        ]);
        exit;
    }
    
    // Use the AI integration library to analyze the assignment
    $assignmentText = $data['assignment_description'] ?? '';
    
    // Initialize OpenAI client
    $openaiApiKey = $CFG->openai_api_key;
    putenv("OPENAI_API_KEY=$openaiApiKey");
    
    try {
        // Directly call Python integration from PHP using shell_exec
        $prompt = "Analyze the following assignment and identify 4-6 key focus points that students should concentrate on to succeed:";
        $prompt .= "\n\nAssignment: " . $data['assignment_name'];
        
        if (!empty($assignmentText)) {
            $prompt .= "\n\nDescription: " . $assignmentText;
        }
        
        $prompt .= "\n\nPlease provide your response as a JSON array of strings, each representing a key focus point.";
        
        // Create a temporary file for the prompt
        $tempDir = "{$CFG->dataroot}/temp";
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0777, true);
        }
        
        $promptFile = tempnam($tempDir, 'prompt_');
        file_put_contents($promptFile, $prompt);
        
        // Execute Python command using OpenAI directly
        $pythonCode = <<<EOT
import os
import json
import sys
from openai import OpenAI

client = OpenAI(api_key=os.environ.get("OPENAI_API_KEY"))

# Read prompt from file
with open(sys.argv[1], 'r') as f:
    prompt = f.read()

try:
    response = client.chat.completions.create(
        model="gpt-4o",
        messages=[
            {"role": "system", "content": "You are an educational analysis assistant. Analyze assignments and provide focused guidance."},
            {"role": "user", "content": prompt}
        ],
        response_format={"type": "json_object"}
    )
    
    print(response.choices[0].message.content)
except Exception as e:
    print(json.dumps({"error": str(e)}))
EOT;
        
        // Write Python code to a temporary file
        $pythonFile = tempnam($tempDir, 'analyze_');
        file_put_contents($pythonFile, $pythonCode);
        
        // Execute the Python script
        $command = "python3 $pythonFile " . escapeshellarg($promptFile);
        $output = shell_exec($command);
        
        // Clean up temporary files
        unlink($promptFile);
        unlink($pythonFile);
        
        // Parse the result
        $result = json_decode($output, true);
        
        if ($result) {
            if (isset($result['error'])) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Error analyzing assignment: ' . $result['error']
                ]);
            } else {
                // The response should contain a "focus_points" array
                $focusPoints = $result['focus_points'] ?? [];
                
                if (empty($focusPoints) && isset($result[0])) {
                    // Handle the case where the response is a direct array
                    $focusPoints = $result;
                }
                
                echo json_encode([
                    'success' => true,
                    'focus_points' => $focusPoints
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to analyze assignment. Invalid response from AI service.'
            ]);
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error analyzing assignment: ' . $e->getMessage()
        ]);
    }
}

/**
 * Process assignment explanation
 */
function process_assignment_explanation($data) {
    global $CFG;
    
    // Check for required fields
    if (!isset($data['assignment_id']) || !isset($data['assignment_name'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Missing required fields: assignment_id, assignment_name'
        ]);
        exit;
    }
    
    // Use the AI integration library to explain the assignment
    $assignmentText = $data['assignment_description'] ?? '';
    
    // Initialize OpenAI client
    $openaiApiKey = $CFG->openai_api_key;
    putenv("OPENAI_API_KEY=$openaiApiKey");
    
    try {
        // Directly call Python integration from PHP using shell_exec
        $prompt = "Explain the following assignment in clear, student-friendly terms. Break down what is expected, what the key challenges might be, and how to approach it successfully:";
        $prompt .= "\n\nAssignment: " . $data['assignment_name'];
        
        if (!empty($assignmentText)) {
            $prompt .= "\n\nDescription: " . $assignmentText;
        }
        
        // Create a temporary file for the prompt
        $tempDir = "{$CFG->dataroot}/temp";
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0777, true);
        }
        
        $promptFile = tempnam($tempDir, 'prompt_');
        file_put_contents($promptFile, $prompt);
        
        // Execute Python command using OpenAI directly
        $pythonCode = <<<EOT
import os
import json
import sys
from openai import OpenAI

client = OpenAI(api_key=os.environ.get("OPENAI_API_KEY"))

# Read prompt from file
with open(sys.argv[1], 'r') as f:
    prompt = f.read()

try:
    response = client.chat.completions.create(
        model="gpt-4o",
        messages=[
            {"role": "system", "content": "You are an educational assistant who helps students understand assignments."},
            {"role": "user", "content": prompt}
        ]
    )
    
    print(json.dumps({"explanation": response.choices[0].message.content}))
except Exception as e:
    print(json.dumps({"error": str(e)}))
EOT;
        
        // Write Python code to a temporary file
        $pythonFile = tempnam($tempDir, 'explain_');
        file_put_contents($pythonFile, $pythonCode);
        
        // Execute the Python script
        $command = "python3 $pythonFile " . escapeshellarg($promptFile);
        $output = shell_exec($command);
        
        // Clean up temporary files
        unlink($promptFile);
        unlink($pythonFile);
        
        // Parse the result
        $result = json_decode($output, true);
        
        if ($result) {
            if (isset($result['error'])) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Error explaining assignment: ' . $result['error']
                ]);
            } else {
                echo json_encode([
                    'success' => true,
                    'explanation' => $result['explanation']
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to explain assignment. Invalid response from AI service.'
            ]);
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error explaining assignment: ' . $e->getMessage()
        ]);
    }
}

/**
 * Process starting points suggestion
 */
function process_starting_points($data) {
    global $CFG;
    
    // Check for required fields
    if (!isset($data['assignment_id']) || !isset($data['assignment_name'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Missing required fields: assignment_id, assignment_name'
        ]);
        exit;
    }
    
    // Use the AI integration library to suggest starting points
    $assignmentText = $data['assignment_description'] ?? '';
    
    // Initialize OpenAI client
    $openaiApiKey = $CFG->openai_api_key;
    putenv("OPENAI_API_KEY=$openaiApiKey");
    
    try {
        // Directly call Python integration from PHP using shell_exec
        $prompt = "Suggest some starting points and steps for students working on the following assignment:";
        $prompt .= "\n\nAssignment: " . $data['assignment_name'];
        
        if (!empty($assignmentText)) {
            $prompt .= "\n\nDescription: " . $assignmentText;
        }
        
        $prompt .= "\n\nPlease provide a structured approach with initial steps, research tips, and best practices for completing this assignment successfully. Format your response with HTML markup for display on a webpage.";
        
        // Create a temporary file for the prompt
        $tempDir = "{$CFG->dataroot}/temp";
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0777, true);
        }
        
        $promptFile = tempnam($tempDir, 'prompt_');
        file_put_contents($promptFile, $prompt);
        
        // Execute Python command using OpenAI directly
        $pythonCode = <<<EOT
import os
import json
import sys
from openai import OpenAI

client = OpenAI(api_key=os.environ.get("OPENAI_API_KEY"))

# Read prompt from file
with open(sys.argv[1], 'r') as f:
    prompt = f.read()

try:
    response = client.chat.completions.create(
        model="gpt-4o",
        messages=[
            {"role": "system", "content": "You are an educational assistant who helps students plan their approach to assignments. Provide clear, actionable steps with HTML formatting."},
            {"role": "user", "content": prompt}
        ]
    )
    
    print(json.dumps({"starting_points": response.choices[0].message.content}))
except Exception as e:
    print(json.dumps({"error": str(e)}))
EOT;
        
        // Write Python code to a temporary file
        $pythonFile = tempnam($tempDir, 'starting_');
        file_put_contents($pythonFile, $pythonCode);
        
        // Execute the Python script
        $command = "python3 $pythonFile " . escapeshellarg($promptFile);
        $output = shell_exec($command);
        
        // Clean up temporary files
        unlink($promptFile);
        unlink($pythonFile);
        
        // Parse the result
        $result = json_decode($output, true);
        
        if ($result) {
            if (isset($result['error'])) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Error generating starting points: ' . $result['error']
                ]);
            } else {
                echo json_encode([
                    'success' => true,
                    'starting_points' => $result['starting_points']
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to generate starting points. Invalid response from AI service.'
            ]);
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error generating starting points: ' . $e->getMessage()
        ]);
    }
}

/**
 * Process resource finder
 */
function process_resource_finder($data) {
    global $CFG;
    
    // Initialize OpenAI client
    $openaiApiKey = $CFG->openai_api_key;
    putenv("OPENAI_API_KEY=$openaiApiKey");
    
    try {
        // Determine the topic based on the data
        $topic = '';
        
        if (isset($data['topic'])) {
            $topic = $data['topic'];
        } elseif (isset($data['assignment_name'])) {
            $topic = $data['assignment_name'];
            if (isset($data['assignment_description'])) {
                $topic .= "\n\n" . $data['assignment_description'];
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Missing topic or assignment information'
            ]);
            exit;
        }
        
        // Determine resource type
        $resourceType = $data['type'] ?? 'all';
        
        // Directly call Python integration from PHP using shell_exec
        $prompt = "Find and suggest educational resources related to the following topic:";
        $prompt .= "\n\nTopic: " . $topic;
        
        if ($resourceType !== 'all') {
            $prompt .= "\n\nFocus on resources of type: " . $resourceType;
        }
        
        $prompt .= "\n\nProvide 5-8 high-quality educational resources including articles, videos, tutorials, or interactive tools that would help someone learn about this topic. For each resource, include title, brief description, type, and URL if available.";
        
        $prompt .= "\n\nFormat your response as a JSON array with the following structure for each resource:";
        $prompt .= "\n{\n  \"id\": \"unique identifier\",\n  \"title\": \"resource title\",\n  \"description\": \"brief description\",\n  \"type\": \"resource type (e.g., video, article, interactive)\",\n  \"url\": \"URL if available\"\n}";
        
        // Create a temporary file for the prompt
        $tempDir = "{$CFG->dataroot}/temp";
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0777, true);
        }
        
        $promptFile = tempnam($tempDir, 'prompt_');
        file_put_contents($promptFile, $prompt);
        
        // Execute Python command using OpenAI directly
        $pythonCode = <<<EOT
import os
import json
import sys
import uuid
from openai import OpenAI

client = OpenAI(api_key=os.environ.get("OPENAI_API_KEY"))

# Read prompt from file
with open(sys.argv[1], 'r') as f:
    prompt = f.read()

try:
    response = client.chat.completions.create(
        model="gpt-4o",
        messages=[
            {"role": "system", "content": "You are an educational resource finder who helps students find high-quality learning resources."},
            {"role": "user", "content": prompt}
        ],
        response_format={"type": "json_object"}
    )
    
    # Parse the response and ensure proper structure
    content = json.loads(response.choices[0].message.content)
    
    # Check if the content is directly an array or has a resources key
    if isinstance(content, list):
        resources = content
    else:
        resources = content.get("resources", [])
    
    # Ensure each resource has an ID
    for i, resource in enumerate(resources):
        if "id" not in resource:
            resource["id"] = str(i+1)
    
    print(json.dumps({"resources": resources}))
except Exception as e:
    print(json.dumps({"error": str(e)}))
EOT;
        
        // Write Python code to a temporary file
        $pythonFile = tempnam($tempDir, 'resources_');
        file_put_contents($pythonFile, $pythonCode);
        
        // Execute the Python script
        $command = "python3 $pythonFile " . escapeshellarg($promptFile);
        $output = shell_exec($command);
        
        // Clean up temporary files
        unlink($promptFile);
        unlink($pythonFile);
        
        // Parse the result
        $result = json_decode($output, true);
        
        if ($result) {
            if (isset($result['error'])) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Error finding resources: ' . $result['error']
                ]);
            } else {
                echo json_encode([
                    'success' => true,
                    'resources' => $result['resources']
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to find resources. Invalid response from AI service.'
            ]);
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error finding resources: ' . $e->getMessage()
        ]);
    }
}

/**
 * Process course structure generation
 */
function process_course_structure($data) {
    global $CFG;
    
    // Check for required fields
    if (!isset($data['topic']) || !isset($data['level']) || !isset($data['duration'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Missing required fields: topic, level, duration'
        ]);
        exit;
    }
    
    // Prepare command
    $command = "python3 {$CFG->dirroot}/ai/course_structure.py";
    $command .= " --courseid " . escapeshellarg(1); // Placeholder course ID
    $command .= " --name " . escapeshellarg($data['topic']);
    $command .= " --topic " . escapeshellarg($data['topic']);
    $command .= " --level " . escapeshellarg($data['level']);
    $command .= " --duration " . escapeshellarg($data['duration']);
    
    // Create output directory if it doesn't exist
    $outputDir = "{$CFG->dataroot}/ai/course_structures";
    if (!is_dir($outputDir)) {
        mkdir($outputDir, 0777, true);
    }
    
    // Set output file
    $outputFile = "{$outputDir}/temp_course_structure.json";
    $command .= " --output " . escapeshellarg($outputFile);
    
    // Execute the command
    $output = shell_exec($command);
    
    // Check if the output file was created
    if (file_exists($outputFile)) {
        $result = json_decode(file_get_contents($outputFile), true);
        
        if ($result) {
            // Use the HTML representations
            $structure = [
                'title' => $result['title'],
                'description' => $result['description'],
                'outline' => $result['outline_html'],
                'objectives' => $result['objectives_html']
            ];
            
            echo json_encode([
                'success' => true,
                'structure' => $structure
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to generate course structure. Invalid output format.'
            ]);
        }
        
        // Clean up the temporary file
        unlink($outputFile);
    } else {
        // Check if there's an error in the output
        $result = json_decode($output, true);
        
        if ($result && isset($result['error'])) {
            echo json_encode([
                'success' => false,
                'message' => 'Error generating course structure: ' . $result['error']
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to generate course structure. Output file not created.'
            ]);
        }
    }
}

/**
 * Process assessment generation
 */
function process_assessment_generation($data) {
    global $CFG;
    
    // Check for required fields
    if (!isset($data['topic']) || !isset($data['type']) || !isset($data['difficulty'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Missing required fields: topic, type, difficulty'
        ]);
        exit;
    }
    
    // Initialize OpenAI client
    $openaiApiKey = $CFG->openai_api_key;
    putenv("OPENAI_API_KEY=$openaiApiKey");
    
    try {
        // Prepare the prompt based on assessment type
        $topic = $data['topic'];
        $type = $data['type'];
        $difficulty = $data['difficulty'];
        
        $prompt = "Generate a {$difficulty} level {$type} about the following topic:";
        $prompt .= "\n\nTopic: {$topic}";
        
        if ($type === 'quiz') {
            $prompt .= "\n\nCreate a quiz with 5-8 questions. Include a mix of multiple choice, true/false, and short answer questions. For each question, provide the correct answer.";
            $prompt .= "\n\nFormat your response as a JSON object with the following structure:";
            $prompt .= "\n{\n  \"title\": \"Quiz title\",\n  \"description\": \"Brief description\",\n  \"questions\": [\n    {\n      \"text\": \"Question text\",\n      \"type\": \"multiple_choice/true_false/short_answer\",\n      \"options\": [\"Option A\", \"Option B\", ...] (for multiple choice only),\n      \"answer\": \"Correct answer\"\n    },\n    ...\n  ]\n}";
        } elseif ($type === 'assignment') {
            $prompt .= "\n\nCreate a detailed assignment prompt. Include a clear description, specific instructions, and grading criteria.";
            $prompt .= "\n\nFormat your response as a JSON object with the following structure:";
            $prompt .= "\n{\n  \"title\": \"Assignment title\",\n  \"description\": \"Detailed description\",\n  \"instructions\": [\"Instruction 1\", \"Instruction 2\", ...],\n  \"grading_criteria\": [\"Criterion 1\", \"Criterion 2\", ...]\n}";
        } elseif ($type === 'discussion') {
            $prompt .= "\n\nCreate a discussion topic that encourages critical thinking and engagement. Include a main question, supporting questions, and suggested resources to consider.";
            $prompt .= "\n\nFormat your response as a JSON object with the following structure:";
            $prompt .= "\n{\n  \"title\": \"Discussion title\",\n  \"main_question\": \"Primary discussion question\",\n  \"supporting_questions\": [\"Question 1\", \"Question 2\", ...],\n  \"resources\": [\"Resource 1\", \"Resource 2\", ...]\n}";
        }
        
        // Create a temporary file for the prompt
        $tempDir = "{$CFG->dataroot}/temp";
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0777, true);
        }
        
        $promptFile = tempnam($tempDir, 'prompt_');
        file_put_contents($promptFile, $prompt);
        
        // Execute Python command using OpenAI directly
        $pythonCode = <<<EOT
import os
import json
import sys
from openai import OpenAI

client = OpenAI(api_key=os.environ.get("OPENAI_API_KEY"))

# Read prompt from file
with open(sys.argv[1], 'r') as f:
    prompt = f.read()

try:
    response = client.chat.completions.create(
        model="gpt-4o",
        messages=[
            {"role": "system", "content": "You are an educational assessment creator who creates high-quality learning assessments."},
            {"role": "user", "content": prompt}
        ],
        response_format={"type": "json_object"}
    )
    
    print(response.choices[0].message.content)
except Exception as e:
    print(json.dumps({"error": str(e)}))
EOT;
        
        // Write Python code to a temporary file
        $pythonFile = tempnam($tempDir, 'assessment_');
        file_put_contents($pythonFile, $pythonCode);
        
        // Execute the Python script
        $command = "python3 $pythonFile " . escapeshellarg($promptFile);
        $output = shell_exec($command);
        
        // Clean up temporary files
        unlink($promptFile);
        unlink($pythonFile);
        
        // Parse the result
        $assessment = json_decode($output, true);
        
        if ($assessment) {
            if (isset($assessment['error'])) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Error generating assessment: ' . $assessment['error']
                ]);
            } else {
                echo json_encode([
                    'success' => true,
                    'assessment' => $assessment
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to generate assessment. Invalid response from AI service.'
            ]);
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error generating assessment: ' . $e->getMessage()
        ]);
    }
}
