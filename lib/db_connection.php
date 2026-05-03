<?php
/**
 * iTeachXR — Database Connection & Auto-Provisioning
 *
 * Connection priority: FLY_DATABASE_URL → DATABASE_URL → individual PG* vars
 */

// ─────────────────────────────────────────────────────────────
// Connection
// ─────────────────────────────────────────────────────────────
function get_db_connection(): ?PDO {
    $url = getenv('FLY_DATABASE_URL') ?: getenv('DATABASE_URL') ?: null;

    if ($url) {
        $p    = parse_url($url);
        $host = $p['host'] ?? '';
        $port = $p['port'] ?? 5432;
        $user = $p['user'] ?? '';
        $pass = isset($p['pass']) ? urldecode($p['pass']) : '';
        $name = ltrim($p['path'] ?? '', '/');
        // Honour SSL params in query string; default to require
        $extra = isset($p['query'])
            ? ';' . str_replace('&', ';', $p['query'])
            : ';sslmode=require';
        $dsn  = "pgsql:host=$host;port=$port;dbname=$name$extra";
        $opts = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];
        try {
            return new PDO($dsn, $user, $pass, $opts);
        } catch (PDOException $e) {
            error_log('iTeachXR DB error (url): ' . $e->getMessage());
            return null;
        }
    }

    // Fallback: individual PG* environment variables
    $host = getenv('PGHOST');
    $port = getenv('PGPORT') ?: 5432;
    $name = getenv('PGDATABASE');
    $user = getenv('PGUSER');
    $pass = getenv('PGPASSWORD');
    $dsn  = "pgsql:host=$host;port=$port;dbname=$name;sslmode=require";
    try {
        return new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    } catch (PDOException $e) {
        error_log('iTeachXR DB error (pgvars): ' . $e->getMessage());
        return null;
    }
}

// ─────────────────────────────────────────────────────────────
// Auto-Provisioning — called from api/auth/sso/finish.php
// ─────────────────────────────────────────────────────────────

/**
 * Upsert a user on every Google SSO sign-in, then auto-provision a
 * student_profile row the first time a student logs in.
 *
 * For students who already have transcript data (e.g. Ian Jiang, whose
 * transcript was seeded before first login), GPA and credit totals are
 * computed live from transcript_entries so the profile is always accurate.
 *
 * @return array  Full users row (assoc)
 */
function db_upsert_user(PDO $db, string $email, string $name, string $avatar = ''): array {
    $parts     = explode(' ', trim($name), 2);
    $firstname = $parts[0] ?? '';
    $lastname  = $parts[1] ?? '';

    // ── 1. Upsert the user row, always refresh avatar + last_login ────────
    $db->prepare("
        INSERT INTO users (email, firstname, lastname, avatar_url, last_login)
        VALUES (:email, :fn, :ln, :av, NOW())
        ON CONFLICT (email) DO UPDATE
          SET firstname  = EXCLUDED.firstname,
              lastname   = EXCLUDED.lastname,
              avatar_url = EXCLUDED.avatar_url,
              last_login = NOW()
    ")->execute([':email' => $email, ':fn' => $firstname, ':ln' => $lastname, ':av' => $avatar]);

    $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || $user['role'] !== 'student') {
        return $user ?: [];
    }

    $uid = (int) $user['id'];

    // ── 2. Check whether a student_profile already exists ────────────────
    $profile = $db->prepare("SELECT * FROM student_profiles WHERE user_id = ?");
    $profile->execute([$uid]);
    $existing = $profile->fetch();

    if ($existing) {
        // Profile exists — refresh GPA + total_credits from real transcript data
        // so the dashboard is always accurate after any transcript update.
        _db_refresh_student_totals($db, $uid);
        return $user;
    }

    // ── 3. First login: auto-provision a student_profile ────────────────
    // Use a cryptographically random, unpredictable student ID
    $sid = strtoupper(bin2hex(random_bytes(4))) . 'VR';

    // If a transcript was seeded before first login, compute real totals
    [$computedGpa, $computedCredits] = _db_compute_student_totals($db, $uid);

    $db->prepare("
        INSERT INTO student_profiles
            (user_id, student_id, current_grade, enrollment_status,
             graduation_date, gpa, total_credits)
        VALUES
            (:uid, :sid, 9, 'Good Standing', '2030-06-15', :gpa, :cr)
        ON CONFLICT (user_id) DO NOTHING
    ")->execute([
        ':uid' => $uid,
        ':sid' => $sid,
        ':gpa' => $computedGpa,
        ':cr'  => $computedCredits,
    ]);

    return $user;
}

/**
 * Recompute GPA and total_credits from transcript_entries and write them
 * back to student_profiles.  Called on every login for existing students.
 *
 * GPA scale: A+ = 4.3, A = 4.0, A- = 3.7, B+ = 3.3, B = 3.0, …
 * Weighted by credit hours.
 */
function _db_refresh_student_totals(PDO $db, int $uid): void {
    [$gpa, $credits] = _db_compute_student_totals($db, $uid);
    if ($credits === 0) return; // no transcript yet, leave defaults

    $db->prepare("
        UPDATE student_profiles
           SET gpa = :gpa, total_credits = :cr
         WHERE user_id = :uid
    ")->execute([':gpa' => $gpa, ':cr' => $credits, ':uid' => $uid]);
}

/**
 * Returns [float|null $gpa, int $totalCredits] computed from transcript_entries.
 */
function _db_compute_student_totals(PDO $db, int $uid): array {
    $scale = [
        'A+' => 4.3, 'A' => 4.0, 'A-' => 3.7,
        'B+' => 3.3, 'B' => 3.0, 'B-' => 2.7,
        'C+' => 2.3, 'C' => 2.0, 'C-' => 1.7,
        'D+' => 1.3, 'D' => 1.0, 'D-' => 0.7,
        'F'  => 0.0,
    ];

    $stmt = $db->prepare("
        SELECT grade, credits FROM transcript_entries WHERE user_id = ?
    ");
    $stmt->execute([$uid]);
    $rows = $stmt->fetchAll();

    $totalCredits = 0;
    $weightedSum  = 0.0;
    $graded       = 0;

    foreach ($rows as $r) {
        $cr = (float) $r['credits'];
        $totalCredits += (int) $cr;
        $g = strtoupper(trim($r['grade']));
        if (isset($scale[$g])) {
            $weightedSum += $scale[$g] * $cr;
            $graded      += $cr;
        }
    }

    $gpa = $graded > 0 ? round($weightedSum / $graded, 2) : null;
    return [$gpa, $totalCredits];
}
