<?php
/**
 * iTeachXR — Database Connection
 * Priority: FLY_DATABASE_URL → DATABASE_URL → individual PG* vars
 */
function get_db_connection(): ?PDO {
    // Prefer Fly.io Postgres, fall back to Replit/Neon, then individual vars
    $url = getenv('FLY_DATABASE_URL') ?: getenv('DATABASE_URL') ?: null;

    if ($url) {
        $p    = parse_url($url);
        $host = $p['host'] ?? '';
        $port = $p['port'] ?? 5432;
        $user = $p['user'] ?? '';
        $pass = isset($p['pass']) ? urldecode($p['pass']) : '';
        $db   = ltrim($p['path'] ?? '', '/');
        $extra = isset($p['query'])
            ? ';' . str_replace('&', ';', $p['query'])
            : ';sslmode=require';
        $dsn = "pgsql:host=$host;port=$port;dbname=$db$extra";
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

    // Individual PG* vars
    $host = getenv('PGHOST');
    $port = getenv('PGPORT') ?: 5432;
    $db   = getenv('PGDATABASE');
    $user = getenv('PGUSER');
    $pass = getenv('PGPASSWORD');
    $dsn  = "pgsql:host=$host;port=$port;dbname=$db;sslmode=require";
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

/**
 * Auto-provision a user + student_profile row on first Google login.
 * Returns the user row (array).
 */
function db_upsert_user(PDO $db, string $email, string $name, string $avatar = ''): array {
    $parts     = explode(' ', trim($name), 2);
    $firstname = $parts[0] ?? '';
    $lastname  = $parts[1] ?? '';

    $db->prepare("
        INSERT INTO users (email, firstname, lastname, avatar_url, last_login)
        VALUES (:email, :fn, :ln, :av, NOW())
        ON CONFLICT (email) DO UPDATE
          SET firstname  = EXCLUDED.firstname,
              lastname   = EXCLUDED.lastname,
              avatar_url = EXCLUDED.avatar_url,
              last_login = NOW()
    ")->execute([':email'=>$email, ':fn'=>$firstname, ':ln'=>$lastname, ':av'=>$avatar]);

    $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    // Auto-provision student profile if none exists
    if ($user && $user['role'] === 'student') {
        $check = $db->prepare("SELECT id FROM student_profiles WHERE user_id = ?");
        $check->execute([$user['id']]);
        if (!$check->fetch()) {
            // Generate a unique student ID:  random 8-digit + VR suffix
            $sid = strtoupper(substr(md5($email . time()), 0, 8)) . 'VR';
            $db->prepare("
                INSERT INTO student_profiles
                  (user_id, student_id, current_grade, enrollment_status, graduation_date, gpa, total_credits)
                VALUES
                  (:uid, :sid, 9, 'Good Standing', '2030-06-15', NULL, 0)
                ON CONFLICT (user_id) DO NOTHING
            ")->execute([':uid'=>$user['id'], ':sid'=>$sid]);
        }
    }

    return $user ?: [];
}
