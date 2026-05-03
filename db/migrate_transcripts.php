<?php
/**
 * iTeachXR - Transcript Schema Migration + Ian Jiang seed data
 * Run: php db/migrate_transcripts.php
 */
if (php_sapi_name() !== 'cli') die('CLI only');

require_once __DIR__ . '/../lib/db_connection.php';
$db = get_db_connection();
if (!$db) die("DB connection failed\n");

function run(PDO $db, string $sql, string $label = ''): void {
    try {
        $db->exec($sql);
        if ($label) echo "  OK: $label\n";
    } catch (PDOException $e) {
        echo "  ERR ($label): " . $e->getMessage() . "\n";
    }
}

echo "=== iTeachXR Transcript Migration ===\n";

// ── schema ────────────────────────────────────────────────────
run($db, "
CREATE TABLE IF NOT EXISTS student_profiles (
    id            SERIAL PRIMARY KEY,
    user_id       INTEGER REFERENCES users(id) ON DELETE CASCADE UNIQUE,
    student_id    VARCHAR(30) UNIQUE,
    dob           DATE,
    address       TEXT,
    graduation_date DATE,
    current_grade INTEGER DEFAULT 9,
    enrollment_status VARCHAR(50) DEFAULT 'Good Standing',
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
", "student_profiles table");

run($db, "
CREATE TABLE IF NOT EXISTS transcript_entries (
    id            SERIAL PRIMARY KEY,
    user_id       INTEGER REFERENCES users(id) ON DELETE CASCADE,
    grade_level   INTEGER NOT NULL,
    school_year   VARCHAR(20) NOT NULL,
    ucag_id       VARCHAR(20),
    subject_area  VARCHAR(100) NOT NULL,
    course_title  VARCHAR(200) NOT NULL,
    course_level  VARCHAR(50),
    grade         VARCHAR(5) NOT NULL,
    credits       NUMERIC(5,1) NOT NULL DEFAULT 5,
    seq           INTEGER DEFAULT 0,
    UNIQUE(user_id, grade_level, ucag_id, course_title)
);
", "transcript_entries table");

// ── Ian Jiang user ────────────────────────────────────────────
// Auth is Google SSO — a password is never used for login.
// Generate a random, unusable token so the column is never empty.
$unusableHash = password_hash(bin2hex(random_bytes(32)), PASSWORD_DEFAULT);
run($db, "
INSERT INTO users (username, password, email, firstname, lastname, role, is_active)
VALUES ('ian.jiang', '$unusableHash', 'ian09jiang@gmail.com', 'Ian', 'Jiang', 'student', TRUE)
ON CONFLICT (email) DO UPDATE SET firstname='Ian', lastname='Jiang', role='student', is_active=TRUE;
", "Ian Jiang user");

$uid = $db->query("SELECT id FROM users WHERE email='ian09jiang@gmail.com'")->fetchColumn();
echo "  Ian Jiang user_id = $uid\n";

// ── student profile ───────────────────────────────────────────
run($db, "
INSERT INTO student_profiles (user_id, student_id, dob, address, graduation_date, current_grade, enrollment_status)
VALUES ($uid, '28467382VR', '2010-11-15',
        '531 Lasuen Mall, Stanford, CA 94305',
        '2029-06-15', 10, 'Good Standing')
ON CONFLICT (user_id) DO UPDATE SET
  student_id='28467382VR', current_grade=10, enrollment_status='Good Standing';
", "Ian profile");

// ── Grade 9 transcript entries ────────────────────────────────
$g9 = [
  ['YMES6W', 'English',         'INTSV English Language and Composition',   'HGH HON',  'A',  10],
  ['CNJ3S5', 'Computer Science','Advanced Computer Science Principles',      'HGH HNR',  'A',  10],
  ['PM956E', 'History',         'Advanced European History',                 'HON',      'A+', 10],
  ['QS7A5X', 'Mathematics',     'Advanced Statistics',                       'HON',      'A+', 10],
  ['N6LNRQ', 'Science',         'INTSV Advanced Environmental Science',      'HON',      'A',  10],
  ['PTF4DP', 'Visual Perf Art', 'ENRCHD Advanced Projects in Digital Arts',  'HGH HNR',  'A+', 10],
  ['QAL2W8', 'College Prep',    'INTSV BUILD INNOVATION & ENTREPRENEURSHIP', 'C PREP',   'A+', 10],
  ['LLKNS5', 'Mathematics',     'ENRCHD Data Science Mathematics (Stanford)','HON/DUAL', 'A+', 10],
  ['MBRMQ6', 'Mathematics',     'Advanced PreCalculus',                      'HON',      'A+', 10],
  ['DW47FQ', 'Computer Science','INTSV 3D Computer Aided Design',            'ADV',      'A+', 10],
  ['',       'Foreign Language','Advanced Chinese Language',                 'HON',      'A',  10],
  ['K62Q5G', 'Design',          'ACC Design Thinking',                       'GFTED',    'A+', 10],
  ['TDRJH7', 'Science',         'Advanced Chemistry',                        'HON',      'A+', 10],
  ['EFWC4A', 'Science',         'Advanced Physics 1: Algebra Based',         'HON',      'A+', 10],
  ['HK3WME', 'Science',         'INTSV Biology Honors',                      'HGH HON',  'A+', 10],
  ['EKMF7G', 'Social Science',  'CPrep Microeconomics',                      'HON',      'A+', 10],
];

$stmt = $db->prepare("
  INSERT INTO transcript_entries (user_id, grade_level, school_year, ucag_id, subject_area, course_title, course_level, grade, credits, seq)
  VALUES (?, 9, '2024-2025', ?, ?, ?, ?, ?, ?, ?)
  ON CONFLICT (user_id, grade_level, ucag_id, course_title) DO UPDATE SET grade=EXCLUDED.grade, credits=EXCLUDED.credits
");
foreach ($g9 as $i => $r) {
    $stmt->execute([$uid, $r[0], $r[1], $r[2], $r[3], $r[4], $r[5], $i]);
}
echo "  OK: Grade 9 entries (" . count($g9) . " courses)\n";

// ── Grade 10 transcript entries ───────────────────────────────
$g10 = [
  ['',       'English',         'Advanced English Literature & Composition', 'HON',     'A',  5],
  ['R9B7NM', 'Science',         'CPrep Biology',                             'HON',     'A',  5],
  ['TJ8AHE', 'History',         'CPrep World History: Modern',               'HON',     'A+', 5],
  ['HJ9C2B', 'History',         'CPrep United States History',               'HON',     'A+', 5],
  ['',       'Social Science',  'CPrep Macroeconomics',                      'HON',     'A',  5],
  ['P5QAJ3', 'Science',         'Advanced Environmental Science',            'HGH HNR', 'A+', 5],
  ['ZNR2J4', 'College Prep',    'GFTED Research in Tibet',                   'HON',     'A+', 5],
  ['J8KLTN', 'Mathematics',     'Advanced Calculus BC',                      'HON',     'A+', 5],
  ['MBXM7H', 'Social Science',  'CPrep Psychology',                          'HON',     'A+', 5],
  ['TX93D7', 'Computer Science','ACC Design as Discovery (Stanford)',         'ADV',     'A+', 5],
  ['JN43W9', 'Computer Science','CPrep Computer Science A',                  'HON',     'A',  5],
  ['J9TR5X', 'English',         'ACC College Writing',                       'GFTED',   'A+', 5],
  ['D2DTCB', 'Science',         'Advanced Physics 2: Algebra Based',         'HON',     'A+', 5],
  ['ALX47Y', 'Science',         'Advanced Physics C Mechanics',              'HON',     'A+', 5],
  ['KFA8SX', 'Science',         'ADV Experimental Archaeology in VR',        'HGH HON', 'A+', 5],
  ['H3QAHT', 'Social Science',  'ENRCHD Government and Policy',              'HON',     'A+', 5],
];

$stmt = $db->prepare("
  INSERT INTO transcript_entries (user_id, grade_level, school_year, ucag_id, subject_area, course_title, course_level, grade, credits, seq)
  VALUES (?, 10, '2025-2026', ?, ?, ?, ?, ?, ?, ?)
  ON CONFLICT (user_id, grade_level, ucag_id, course_title) DO UPDATE SET grade=EXCLUDED.grade, credits=EXCLUDED.credits
");
foreach ($g10 as $i => $r) {
    $stmt->execute([$uid, $r[0], $r[1], $r[2], $r[3], $r[4], $r[5], $i]);
}
echo "  OK: Grade 10 entries (" . count($g10) . " courses)\n";

// ── Enroll Ian in iTeachXR LMS courses ───────────────────────
$courses = $db->query("SELECT id, code FROM courses WHERE is_active=TRUE")->fetchAll(PDO::FETCH_ASSOC);
foreach ($courses as $c) {
    try {
        $db->exec("INSERT INTO enrollments (user_id, course_id, role, completion_percentage)
                   VALUES ($uid, {$c['id']}, 'student', 0)
                   ON CONFLICT (user_id, course_id) DO NOTHING");
    } catch (PDOException $e) {}
}
echo "  OK: Ian enrolled in " . count($courses) . " LMS courses\n";

echo "\n=== Migration complete ===\n";
echo "  Student: Ian Jiang <ian09jiang@gmail.com>\n";
echo "  Student ID: 28467382VR | GPA: 4.00 | Grade: Sophomore\n";
echo "  Total transcript entries: " . (count($g9) + count($g10)) . "\n";
