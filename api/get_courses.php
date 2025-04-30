<?php
// iTeachXR - API for getting courses

// Include the database connection file
require_once('../lib/db_connection.php');

// Set the content type to JSON
header('Content-Type: application/json');

// Function to get all courses
function get_all_courses() {
    $db = get_db_connection();
    
    if (!$db) {
        return ['error' => 'Database connection failed'];
    }
    
    try {
        $query = "SELECT 
                    c.id, c.title, c.description, c.code, c.level, c.featured,
                    u.firstname, u.lastname
                  FROM courses c
                  JOIN users u ON c.created_by = u.id
                  WHERE c.is_active = TRUE
                  ORDER BY c.title";
        
        $stmt = $db->prepare($query);
        $stmt->execute();
        
        $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Format the result
        $result = [];
        foreach ($courses as $course) {
            $result[] = [
                'id' => $course['id'],
                'title' => $course['title'],
                'description' => $course['description'],
                'code' => $course['code'],
                'level' => $course['level'],
                'featured' => (bool)$course['featured'],
                'instructor' => $course['firstname'] . ' ' . $course['lastname']
            ];
        }
        
        return ['success' => true, 'courses' => $result];
    } catch (PDOException $e) {
        return ['error' => 'Query failed: ' . $e->getMessage()];
    }
}

// Function to get a specific course by ID
function get_course_by_id($id) {
    $db = get_db_connection();
    
    if (!$db) {
        return ['error' => 'Database connection failed'];
    }
    
    try {
        // Get course details
        $query = "SELECT 
                    c.id, c.title, c.description, c.code, c.level, c.featured,
                    u.firstname, u.lastname
                  FROM courses c
                  JOIN users u ON c.created_by = u.id
                  WHERE c.id = :id";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        $course = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$course) {
            return ['error' => 'Course not found'];
        }
        
        // Get course modules
        $query = "SELECT id, title, description, sequence
                  FROM modules
                  WHERE course_id = :course_id
                  ORDER BY sequence";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':course_id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        $modules = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get enrollment count
        $query = "SELECT COUNT(*) as student_count
                  FROM enrollments
                  WHERE course_id = :course_id AND role = 'student'";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':course_id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        $enrollment = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Format the result
        $result = [
            'id' => $course['id'],
            'title' => $course['title'],
            'description' => $course['description'],
            'code' => $course['code'],
            'level' => $course['level'],
            'featured' => (bool)$course['featured'],
            'instructor' => $course['firstname'] . ' ' . $course['lastname'],
            'student_count' => (int)$enrollment['student_count'],
            'modules' => $modules
        ];
        
        return ['success' => true, 'course' => $result];
    } catch (PDOException $e) {
        return ['error' => 'Query failed: ' . $e->getMessage()];
    }
}

// Process the request
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;

if ($id) {
    // Get a specific course
    $response = get_course_by_id($id);
} else {
    // Get all courses
    $response = get_all_courses();
}

// Output the response
echo json_encode($response, JSON_PRETTY_PRINT);
?>