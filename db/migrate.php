<?php
/**
 * iTeachXR — Complete Schema Migration
 *
 * Creates all tables from scratch, idempotently (CREATE TABLE IF NOT EXISTS).
 * Safe to run on a fresh Fly.io / Railway Postgres instance.
 *
 * Usage:
 *   php db/migrate.php
 *
 * Tables created (in dependency order):
 *   users → student_profiles → transcript_entries
 */
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    die('CLI only — run: php db/migrate.php' . PHP_EOL);
}

require_once __DIR__ . '/../lib/db_connection.php';

$db = get_db_connection();
if (!$db) {
    fwrite(STDERR, "ERROR: Could not connect to database. Check DATABASE_URL / FLY_DATABASE_URL.\n");
    exit(1);
}

function run(PDO $db, string $sql, string $label): void {
    try {
        $db->exec($sql);
        echo "  OK  $label\n";
    } catch (PDOException $e) {
        echo "  ERR $label: " . $e->getMessage() . "\n";
    }
}

echo "\n=== iTeachXR Schema Migration ===\n";
echo "  Connected to: " . $db->query('SELECT current_database()')->fetchColumn() . "\n\n";

// ─────────────────────────────────────────────────────────────
// 1. USERS
//    Central identity table.  All authentication is Google SSO;
//    no password is stored here.
// ─────────────────────────────────────────────────────────────
run($db, "
CREATE TABLE IF NOT EXISTS users (
    id          SERIAL PRIMARY KEY,
    email       TEXT NOT NULL UNIQUE,
    firstname   TEXT NOT NULL DEFAULT '',
    lastname    TEXT NOT NULL DEFAULT '',
    role        TEXT NOT NULL DEFAULT 'student'
                     CHECK (role IN ('student','teacher','admin')),
    avatar_url  TEXT,
    created_at  TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    last_login  TIMESTAMPTZ
);
CREATE INDEX IF NOT EXISTS users_email_idx ON users (email);
", "users table");

// ─────────────────────────────────────────────────────────────
// 2. STUDENT PROFILES
//    One row per student.  GPA and total_credits are maintained
//    by the seed / transcript logic (not derived live to keep
//    queries fast on the dashboard).
// ─────────────────────────────────────────────────────────────
run($db, "
CREATE TABLE IF NOT EXISTS student_profiles (
    id                SERIAL PRIMARY KEY,
    user_id           INTEGER NOT NULL UNIQUE
                           REFERENCES users(id) ON DELETE CASCADE,
    student_id        TEXT NOT NULL UNIQUE,
    address           TEXT NOT NULL DEFAULT '',
    current_grade     SMALLINT NOT NULL DEFAULT 9
                           CHECK (current_grade BETWEEN 9 AND 12),
    enrollment_status TEXT NOT NULL DEFAULT 'Good Standing',
    graduation_date   DATE,
    gpa               NUMERIC(4,2),
    total_credits     INTEGER NOT NULL DEFAULT 0,
    created_at        TIMESTAMPTZ NOT NULL DEFAULT NOW()
);
CREATE INDEX IF NOT EXISTS sp_user_idx ON student_profiles (user_id);
", "student_profiles table");

// ─────────────────────────────────────────────────────────────
// 3. TRANSCRIPT ENTRIES
//    One row per course per student per academic year.
//    ucag_id is the UC a-g course code (empty string when N/A).
//    The unique constraint prevents duplicate imports.
// ─────────────────────────────────────────────────────────────
run($db, "
CREATE TABLE IF NOT EXISTS transcript_entries (
    id            SERIAL PRIMARY KEY,
    user_id       INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    grade_level   SMALLINT NOT NULL CHECK (grade_level BETWEEN 9 AND 12),
    school_year   TEXT NOT NULL DEFAULT '',
    seq           SMALLINT NOT NULL DEFAULT 0,
    ucag_id       TEXT NOT NULL DEFAULT '',
    subject_area  TEXT NOT NULL,
    course_title  TEXT NOT NULL,
    course_level  TEXT,
    grade         TEXT NOT NULL,
    credits       NUMERIC(5,1) NOT NULL,
    created_at    TIMESTAMPTZ NOT NULL DEFAULT NOW(),

    UNIQUE (user_id, grade_level, ucag_id, course_title)
);
CREATE INDEX IF NOT EXISTS te_user_grade_idx ON transcript_entries (user_id, grade_level);
", "transcript_entries table");

// ─────────────────────────────────────────────────────────────
// 4. ADDITIVE CHANGES — safe to run on an existing DB
//    The unique index powers ON CONFLICT in seed.php even on databases
//    whose transcript_entries table predates the inline UNIQUE constraint.
// ─────────────────────────────────────────────────────────────

run($db, "
CREATE UNIQUE INDEX IF NOT EXISTS te_unique_course_idx
    ON transcript_entries (user_id, grade_level, ucag_id, course_title);
", "transcript_entries: te_unique_course unique index");

// ─────────────────────────────────────────────────────────────
// Done
// ─────────────────────────────────────────────────────────────
$tables = $db->query("
    SELECT tablename FROM pg_tables
    WHERE schemaname = 'public'
    ORDER BY tablename
")->fetchAll(PDO::FETCH_COLUMN);

echo "\n=== Migration complete ===\n";
echo "  Tables in public schema: " . implode(', ', $tables) . "\n\n";
echo "  Next step: php db/seed.php\n\n";
