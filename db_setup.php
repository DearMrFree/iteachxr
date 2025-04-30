<?php
// iTeachXR - Database Setup Script
// This script initializes the database tables for the iTeachXR system

// Exit if accessed directly
if (php_sapi_name() !== 'cli') {
    die('This script can only be run from the command line');
}

// Get database credentials from environment variables
$dbHost = getenv('PGHOST');
$dbPort = getenv('PGPORT');
$dbName = getenv('PGDATABASE');
$dbUser = getenv('PGUSER');
$dbPass = getenv('PGPASSWORD');

// Connect to PostgreSQL database
try {
    $dsn = "pgsql:host=$dbHost;port=$dbPort;dbname=$dbName;user=$dbUser;password=$dbPass";
    $db = new PDO($dsn);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to database successfully!\n";
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage() . "\n");
}

// Function to execute SQL queries
function executeQuery($db, $sql, $tableName = null) {
    try {
        $db->exec($sql);
        if ($tableName) {
            echo "Table '$tableName' created or updated successfully\n";
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
}

// Create users table
$sql = "
CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    username VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    firstname VARCHAR(255) NOT NULL,
    lastname VARCHAR(255) NOT NULL,
    role VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE
);
";
executeQuery($db, $sql, "users");

// Create courses table
$sql = "
CREATE TABLE IF NOT EXISTS courses (
    id SERIAL PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    code VARCHAR(100) UNIQUE,
    created_by INTEGER REFERENCES users(id),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    start_date DATE,
    end_date DATE,
    is_active BOOLEAN DEFAULT TRUE,
    level VARCHAR(50),
    featured BOOLEAN DEFAULT FALSE
);
";
executeQuery($db, $sql, "courses");

// Create enrollments table
$sql = "
CREATE TABLE IF NOT EXISTS enrollments (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id),
    course_id INTEGER REFERENCES courses(id),
    role VARCHAR(50) DEFAULT 'student',
    enrolled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_access TIMESTAMP,
    completion_percentage INTEGER DEFAULT 0,
    UNIQUE(user_id, course_id)
);
";
executeQuery($db, $sql, "enrollments");

// Create modules table
$sql = "
CREATE TABLE IF NOT EXISTS modules (
    id SERIAL PRIMARY KEY,
    course_id INTEGER REFERENCES courses(id),
    title VARCHAR(255) NOT NULL,
    description TEXT,
    sequence INTEGER,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE
);
";
executeQuery($db, $sql, "modules");

// Create activities table
$sql = "
CREATE TABLE IF NOT EXISTS activities (
    id SERIAL PRIMARY KEY,
    module_id INTEGER REFERENCES modules(id),
    title VARCHAR(255) NOT NULL,
    description TEXT,
    type VARCHAR(50) NOT NULL,
    content TEXT,
    sequence INTEGER,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    due_date TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE,
    points INTEGER DEFAULT 0
);
";
executeQuery($db, $sql, "activities");

// Create submissions table
$sql = "
CREATE TABLE IF NOT EXISTS submissions (
    id SERIAL PRIMARY KEY,
    activity_id INTEGER REFERENCES activities(id),
    user_id INTEGER REFERENCES users(id),
    content TEXT,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    grade NUMERIC(5,2),
    feedback TEXT,
    ai_feedback TEXT,
    graded_by INTEGER REFERENCES users(id),
    graded_at TIMESTAMP
);
";
executeQuery($db, $sql, "submissions");

// Create ai_recommendations table
$sql = "
CREATE TABLE IF NOT EXISTS ai_recommendations (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id),
    course_id INTEGER REFERENCES courses(id),
    content TEXT NOT NULL,
    type VARCHAR(50),
    relevance_score NUMERIC(3,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    viewed BOOLEAN DEFAULT FALSE,
    acted_upon BOOLEAN DEFAULT FALSE
);
";
executeQuery($db, $sql, "ai_recommendations");

// Create learning_paths table
$sql = "
CREATE TABLE IF NOT EXISTS learning_paths (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id),
    course_id INTEGER REFERENCES courses(id),
    path_data JSONB NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE
);
";
executeQuery($db, $sql, "learning_paths");

// Create xr_resources table
$sql = "
CREATE TABLE IF NOT EXISTS xr_resources (
    id SERIAL PRIMARY KEY,
    course_id INTEGER REFERENCES courses(id),
    activity_id INTEGER REFERENCES activities(id),
    title VARCHAR(255) NOT NULL,
    description TEXT,
    resource_type VARCHAR(50) NOT NULL,
    resource_url VARCHAR(255),
    resource_data JSONB,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INTEGER REFERENCES users(id),
    is_active BOOLEAN DEFAULT TRUE
);
";
executeQuery($db, $sql, "xr_resources");

// Create user_activity_logs table
$sql = "
CREATE TABLE IF NOT EXISTS user_activity_logs (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id),
    activity_type VARCHAR(50) NOT NULL,
    activity_detail JSONB,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
";
executeQuery($db, $sql, "user_activity_logs");

// Insert sample data - Admin user
$sql = "
INSERT INTO users (username, password, email, firstname, lastname, role)
VALUES ('admin', '" . password_hash('admin123', PASSWORD_DEFAULT) . "', 'admin@iteachxr.edu', 'Admin', 'User', 'admin')
ON CONFLICT (username) DO NOTHING;
";
executeQuery($db, $sql, "Sample admin user");

// Insert sample data - Teacher users
$sql = "
INSERT INTO users (username, password, email, firstname, lastname, role)
VALUES 
('sarah.chen', '" . password_hash('teacher123', PASSWORD_DEFAULT) . "', 'sarah.chen@iteachxr.edu', 'Sarah', 'Chen', 'teacher'),
('michael.johnson', '" . password_hash('teacher123', PASSWORD_DEFAULT) . "', 'michael.johnson@iteachxr.edu', 'Michael', 'Johnson', 'teacher')
ON CONFLICT (username) DO NOTHING;
";
executeQuery($db, $sql, "Sample teacher users");

// Insert sample data - Student users
$sql = "
INSERT INTO users (username, password, email, firstname, lastname, role)
VALUES 
('alex.johnson', '" . password_hash('student123', PASSWORD_DEFAULT) . "', 'alex.johnson@student.edu', 'Alex', 'Johnson', 'student'),
('john.doe', '" . password_hash('student123', PASSWORD_DEFAULT) . "', 'john.doe@student.edu', 'John', 'Doe', 'student'),
('jane.smith', '" . password_hash('student123', PASSWORD_DEFAULT) . "', 'jane.smith@student.edu', 'Jane', 'Smith', 'student')
ON CONFLICT (username) DO NOTHING;
";
executeQuery($db, $sql, "Sample student users");

// Insert sample data - Courses
$sql = "
INSERT INTO courses (title, description, code, created_by, level, featured)
VALUES 
('Introduction to Virtual Reality', 'A comprehensive introduction to VR concepts, hardware, and development.', 'VR101', 
 (SELECT id FROM users WHERE username = 'sarah.chen'), 'undergraduate', TRUE),
('Advanced Augmented Reality', 'Advanced concepts in AR development and spatial computing.', 'AR201', 
 (SELECT id FROM users WHERE username = 'michael.johnson'), 'graduate', TRUE),
('Interactive 3D Education', 'Principles of creating interactive 3D educational experiences.', '3D301', 
 (SELECT id FROM users WHERE username = 'sarah.chen'), 'undergraduate', FALSE)
ON CONFLICT (code) DO NOTHING;
";
executeQuery($db, $sql, "Sample courses");

// Insert sample data - Enrollments
$sql = "
-- Enroll teacher Sarah Chen in her courses
INSERT INTO enrollments (user_id, course_id, role)
SELECT 
    (SELECT id FROM users WHERE username = 'sarah.chen'),
    id,
    'teacher'
FROM courses 
WHERE code IN ('VR101', '3D301')
ON CONFLICT (user_id, course_id) DO NOTHING;

-- Enroll teacher Michael Johnson in his course
INSERT INTO enrollments (user_id, course_id, role)
SELECT 
    (SELECT id FROM users WHERE username = 'michael.johnson'),
    id,
    'teacher'
FROM courses 
WHERE code = 'AR201'
ON CONFLICT (user_id, course_id) DO NOTHING;

-- Enroll students in various courses
INSERT INTO enrollments (user_id, course_id, role, completion_percentage)
VALUES 
((SELECT id FROM users WHERE username = 'alex.johnson'), 
 (SELECT id FROM courses WHERE code = 'VR101'), 'student', 65),
((SELECT id FROM users WHERE username = 'alex.johnson'), 
 (SELECT id FROM courses WHERE code = 'AR201'), 'student', 42),
((SELECT id FROM users WHERE username = 'john.doe'), 
 (SELECT id FROM courses WHERE code = 'VR101'), 'student', 78),
((SELECT id FROM users WHERE username = 'jane.smith'), 
 (SELECT id FROM courses WHERE code = 'AR201'), 'student', 56)
ON CONFLICT (user_id, course_id) DO NOTHING;
";
executeQuery($db, $sql, "Sample enrollments");

// Insert sample data - Modules and activities for VR101
$sql = "
-- Create modules for VR101
INSERT INTO modules (course_id, title, description, sequence)
VALUES 
((SELECT id FROM courses WHERE code = 'VR101'), 'Introduction to VR Concepts', 'Fundamental concepts of virtual reality', 1),
((SELECT id FROM courses WHERE code = 'VR101'), 'VR Hardware', 'Overview of VR headsets and controllers', 2),
((SELECT id FROM courses WHERE code = 'VR101'), 'VR User Interface Design', 'Principles of designing user interfaces for VR', 3)
ON CONFLICT DO NOTHING;

-- Create activities for VR101 modules
INSERT INTO activities (module_id, title, description, type, sequence, due_date, points)
VALUES 
((SELECT id FROM modules WHERE title = 'VR User Interface Design' AND course_id = (SELECT id FROM courses WHERE code = 'VR101')), 
 'VR Environment Design', 'Create a virtual environment with appropriate user interface elements', 'assignment', 1, 
 (CURRENT_DATE + INTERVAL '1 DAY'), 100),
((SELECT id FROM modules WHERE title = 'VR Hardware' AND course_id = (SELECT id FROM courses WHERE code = 'VR101')), 
 'VR Hardware Comparison', 'Compare different VR headsets and their specifications', 'assignment', 1, 
 (CURRENT_DATE + INTERVAL '7 DAYS'), 50),
((SELECT id FROM modules WHERE title = 'Introduction to VR Concepts' AND course_id = (SELECT id FROM courses WHERE code = 'VR101')), 
 'Introduction to VR Quiz', 'Test your knowledge of basic VR concepts', 'quiz', 1, 
 (CURRENT_DATE + INTERVAL '3 DAYS'), 25)
ON CONFLICT DO NOTHING;
";
executeQuery($db, $sql, "Sample modules and activities for VR101");

// Insert sample data - Modules and activities for AR201
$sql = "
-- Create modules for AR201
INSERT INTO modules (course_id, title, description, sequence)
VALUES 
((SELECT id FROM courses WHERE code = 'AR201'), 'Advanced AR Concepts', 'Advanced concepts in augmented reality', 1),
((SELECT id FROM courses WHERE code = 'AR201'), 'Spatial Mapping', 'Techniques for mapping real-world environments', 2),
((SELECT id FROM courses WHERE code = 'AR201'), 'AR Interaction Patterns', 'Patterns for user interaction in AR applications', 3)
ON CONFLICT DO NOTHING;

-- Create activities for AR201 modules
INSERT INTO activities (module_id, title, description, type, sequence, due_date, points)
VALUES 
((SELECT id FROM modules WHERE title = 'Spatial Mapping' AND course_id = (SELECT id FROM courses WHERE code = 'AR201')), 
 'AR Marker Implementation', 'Implement AR markers in a sample application', 'assignment', 1, 
 (CURRENT_DATE + INTERVAL '5 DAYS'), 100),
((SELECT id FROM modules WHERE title = 'Advanced AR Concepts' AND course_id = (SELECT id FROM courses WHERE code = 'AR201')), 
 'AR Concepts Quiz', 'Test your knowledge of advanced AR concepts', 'quiz', 1, 
 (CURRENT_DATE + INTERVAL '2 DAYS'), 50),
((SELECT id FROM modules WHERE title = 'AR Interaction Patterns' AND course_id = (SELECT id FROM courses WHERE code = 'AR201')), 
 'AR Interaction Design', 'Design an AR interaction for a specific use case', 'assignment', 1, 
 (CURRENT_DATE + INTERVAL '10 DAYS'), 75)
ON CONFLICT DO NOTHING;
";
executeQuery($db, $sql, "Sample modules and activities for AR201");

// Create a database connection function file
$dbConnFile = <<<'EOD'
<?php
// iTeachXR - Database Connection Function

/**
 * Get a connection to the database
 * @return PDO Database connection object
 */
function get_db_connection() {
    $dbHost = getenv('PGHOST');
    $dbPort = getenv('PGPORT');
    $dbName = getenv('PGDATABASE');
    $dbUser = getenv('PGUSER');
    $dbPass = getenv('PGPASSWORD');
    
    $dsn = "pgsql:host=$dbHost;port=$dbPort;dbname=$dbName;user=$dbUser;password=$dbPass";
    
    try {
        $db = new PDO($dsn);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $db;
    } catch (PDOException $e) {
        error_log("Database connection error: " . $e->getMessage());
        return null;
    }
}
EOD;

// Create the database connection file
file_put_contents('lib/db_connection.php', $dbConnFile);
echo "Created database connection file: lib/db_connection.php\n";

echo "\nDatabase setup complete!\n";
?>