<?php
/**
 * iTeachXR — Seed Script
 *
 * Idempotent: safe to run multiple times (uses ON CONFLICT DO UPDATE).
 *
 * Seeds:
 *   • Demo Student — student   (demo.student@thevrschool.org)
 *   • Freedom      — admin     (freedom@thevrschool.org)
 *   • Demo student's full 32-course transcript (Grade 9 + Grade 10)
 *   • Demo student profile  (GPA 4.00, 240 credits, ID VRS-DEMO-0001)
 *
 * Usage:
 *   php db/seed.php
 */
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    die('CLI only — run: php db/seed.php' . PHP_EOL);
}

require_once __DIR__ . '/../lib/db_connection.php';

$db = get_db_connection();
if (!$db) {
    fwrite(STDERR, "ERROR: Could not connect to database. Check DATABASE_URL / FLY_DATABASE_URL.\n");
    exit(1);
}

function run(PDO $db, string $sql, string $label, array $params = []): void {
    try {
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        echo "  OK  $label\n";
    } catch (PDOException $e) {
        echo "  ERR $label: " . $e->getMessage() . "\n";
    }
}

echo "\n=== iTeachXR Seed ===\n";
echo "  Connected to: " . $db->query('SELECT current_database()')->fetchColumn() . "\n\n";

// ─────────────────────────────────────────────────────────────
// 1. USERS
// ─────────────────────────────────────────────────────────────
echo "── Users ──\n";

run($db, "
    INSERT INTO users (email, firstname, lastname, role)
    VALUES ('demo.student@thevrschool.org', 'Demo', 'Student', 'student')
    ON CONFLICT (email) DO UPDATE
        SET firstname = 'Demo',
            lastname  = 'Student',
            role      = 'student'
", "Demo Student (student)");

run($db, "
    INSERT INTO users (email, firstname, lastname, role)
    VALUES ('freedom@thevrschool.org', 'Freedom', 'Admin', 'admin')
    ON CONFLICT (email) DO UPDATE
        SET firstname = 'Freedom',
            lastname  = 'Admin',
            role      = 'admin'
", "Freedom (admin)");

$ianId = $db->query("SELECT id FROM users WHERE email = 'demo.student@thevrschool.org'")->fetchColumn();
echo "  Demo Student user_id = $ianId\n\n";

// ─────────────────────────────────────────────────────────────
// 2. STUDENT PROFILE — Demo Student
// ─────────────────────────────────────────────────────────────
echo "── Student profile ──\n";

run($db, "
    INSERT INTO student_profiles
        (user_id, student_id, address, current_grade, enrollment_status,
         graduation_date, gpa, total_credits)
    VALUES
        (?, 'VRS-DEMO-0001',
         '123 University Ave, Palo Alto, CA 94301',
         10, 'Good Standing',
         '2029-06-15', 4.00, 240)
    ON CONFLICT (user_id) DO UPDATE
        SET student_id        = 'VRS-DEMO-0001',
            address           = '123 University Ave, Palo Alto, CA 94301',
            current_grade     = 10,
            enrollment_status = 'Good Standing',
            graduation_date   = '2029-06-15',
            gpa               = 4.00,
            total_credits     = 240
", "Demo Student profile", [$ianId]);

echo "\n";

// ─────────────────────────────────────────────────────────────
// 3. TRANSCRIPT — Grade 9 (2024-2025)
//    16 courses × 10 credits = 160 credits
//    Format: [ucag_id, subject_area, course_title, course_level, grade, credits]
// ─────────────────────────────────────────────────────────────
echo "── Transcript: Grade 9 (2024-2025) ──\n";

$g9 = [
    ['YMES6W', 'English',          'INTSV English Language and Composition',    'HGH HON',  'A',  10],
    ['CNJ3S5', 'Computer Science', 'Advanced Computer Science Principles',       'HGH HNR',  'A',  10],
    ['PM956E', 'History',          'Advanced European History',                  'HON',      'A+', 10],
    ['QS7A5X', 'Mathematics',      'Advanced Statistics',                        'HON',      'A+', 10],
    ['N6LNRQ', 'Science',          'INTSV Advanced Environmental Science',       'HON',      'A',  10],
    ['PTF4DP', 'Visual Perf Art',  'ENRCHD Advanced Projects in Digital Arts',   'HGH HNR',  'A+', 10],
    ['QAL2W8', 'College Prep',     'INTSV BUILD INNOVATION & ENTREPRENEURSHIP',  'C PREP',   'A+', 10],
    ['LLKNS5', 'Mathematics',      'ENRCHD Data Science Mathematics (Stanford)', 'HON/DUAL', 'A+', 10],
    ['MBRMQ6', 'Mathematics',      'Advanced PreCalculus',                       'HON',      'A+', 10],
    ['DW47FQ', 'Computer Science', 'INTSV 3D Computer Aided Design',             'ADV',      'A+', 10],
    ['',       'Foreign Language', 'Advanced Chinese Language',                  'HON',      'A',  10],
    ['K62Q5G', 'Design',           'ACC Design Thinking',                        'GFTED',    'A+', 10],
    ['TDRJH7', 'Science',          'Advanced Chemistry',                         'HON',      'A+', 10],
    ['EFWC4A', 'Science',          'Advanced Physics 1: Algebra Based',          'HON',      'A+', 10],
    ['HK3WME', 'Science',          'INTSV Biology Honors',                       'HGH HON',  'A+', 10],
    ['EKMF7G', 'Social Science',   'CPrep Microeconomics',                       'HON',      'A+', 10],
];

$stmt = $db->prepare("
    INSERT INTO transcript_entries
        (user_id, grade_level, school_year, seq, ucag_id,
         subject_area, course_title, course_level, grade, credits)
    VALUES (?, 9, '2024-2025', ?, ?, ?, ?, ?, ?, ?)
    ON CONFLICT (user_id, grade_level, ucag_id, course_title) DO UPDATE
        SET grade  = EXCLUDED.grade,
            credits = EXCLUDED.credits,
            seq     = EXCLUDED.seq
");
foreach ($g9 as $i => $r) {
    try {
        $stmt->execute([$ianId, $i + 1, $r[0], $r[1], $r[2], $r[3], $r[4], $r[5]]);
    } catch (PDOException $e) {
        echo "  ERR G9 row $i: " . $e->getMessage() . "\n";
    }
}
echo "  OK  " . count($g9) . " Grade-9 transcript rows\n\n";

// ─────────────────────────────────────────────────────────────
// 4. TRANSCRIPT — Grade 10 (2025-2026)
//    16 courses × 5 credits = 80 credits
// ─────────────────────────────────────────────────────────────
echo "── Transcript: Grade 10 (2025-2026) ──\n";

$g10 = [
    ['',       'English',          'Advanced English Literature & Composition',  'HON',     'A',  5],
    ['R9B7NM', 'Science',          'CPrep Biology',                              'HON',     'A',  5],
    ['TJ8AHE', 'History',          'CPrep World History: Modern',                'HON',     'A+', 5],
    ['HJ9C2B', 'History',          'CPrep United States History',                'HON',     'A+', 5],
    ['',       'Social Science',   'CPrep Macroeconomics',                       'HON',     'A',  5],
    ['P5QAJ3', 'Science',          'Advanced Environmental Science',             'HGH HNR', 'A+', 5],
    ['ZNR2J4', 'College Prep',     'GFTED Research in Tibet',                    'HON',     'A+', 5],
    ['J8KLTN', 'Mathematics',      'Advanced Calculus BC',                       'HON',     'A+', 5],
    ['MBXM7H', 'Social Science',   'CPrep Psychology',                           'HON',     'A+', 5],
    ['TX93D7', 'Computer Science', 'ACC Design as Discovery (Stanford)',          'ADV',     'A+', 5],
    ['JN43W9', 'Computer Science', 'CPrep Computer Science A',                   'HON',     'A',  5],
    ['J9TR5X', 'English',          'ACC College Writing',                        'GFTED',   'A+', 5],
    ['D2DTCB', 'Science',          'Advanced Physics 2: Algebra Based',          'HON',     'A+', 5],
    ['ALX47Y', 'Science',          'Advanced Physics C Mechanics',               'HON',     'A+', 5],
    ['KFA8SX', 'Science',          'ADV Experimental Archaeology in VR',         'HGH HON', 'A+', 5],
    ['H3QAHT', 'Social Science',   'ENRCHD Government and Policy',               'HON',     'A+', 5],
];

$stmt = $db->prepare("
    INSERT INTO transcript_entries
        (user_id, grade_level, school_year, seq, ucag_id,
         subject_area, course_title, course_level, grade, credits)
    VALUES (?, 10, '2025-2026', ?, ?, ?, ?, ?, ?, ?)
    ON CONFLICT (user_id, grade_level, ucag_id, course_title) DO UPDATE
        SET grade   = EXCLUDED.grade,
            credits = EXCLUDED.credits,
            seq     = EXCLUDED.seq
");
foreach ($g10 as $i => $r) {
    try {
        $stmt->execute([$ianId, $i + 1, $r[0], $r[1], $r[2], $r[3], $r[4], $r[5]]);
    } catch (PDOException $e) {
        echo "  ERR G10 row $i: " . $e->getMessage() . "\n";
    }
}
echo "  OK  " . count($g10) . " Grade-10 transcript rows\n\n";

// ─────────────────────────────────────────────────────────────
// 5. VERIFICATION
// ─────────────────────────────────────────────────────────────
echo "── Verification ──\n";
$tc = $db->query("SELECT SUM(credits) FROM transcript_entries WHERE user_id = $ianId")->fetchColumn();
$nc = $db->query("SELECT COUNT(*)     FROM transcript_entries WHERE user_id = $ianId")->fetchColumn();
$sp = $db->query("SELECT gpa, total_credits, student_id, current_grade FROM student_profiles WHERE user_id = $ianId")->fetch();

printf("  Demo Student (#%d):\n",  $ianId);
printf("    Transcript rows : %d\n", $nc);
printf("    Credits (sum)   : %s\n", $tc);
printf("    GPA (profile)   : %s\n", $sp['gpa']);
printf("    Total cr (prof) : %d\n", $sp['total_credits']);
printf("    Student ID      : %s\n", $sp['student_id']);
printf("    Grade           : %d\n", $sp['current_grade']);

$totUsers = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
printf("\n  Total users: %d\n", $totUsers);

echo "\n=== Seed complete ===\n\n";
