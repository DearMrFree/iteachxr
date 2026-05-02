<?php
/**
 * iTeachXR — Database Connection
 * Prefers DATABASE_URL (Replit/Neon), falls back to individual PG* vars.
 */
function get_db_connection(): ?PDO {
    $url = getenv('DATABASE_URL');

    if ($url) {
        $p    = parse_url($url);
        $host = $p['host'] ?? '';
        $port = $p['port'] ?? 5432;
        $user = $p['user'] ?? '';
        $pass = $p['pass'] ?? '';
        $db   = ltrim($p['path'] ?? '', '/');
        // Preserve any query params (e.g. sslmode=require)
        $extra = isset($p['query']) ? ';' . str_replace('&', ';', $p['query']) : ';sslmode=require';
        $dsn = "pgsql:host=$host;port=$port;dbname=$db;user=$user;password=$pass$extra";
    } else {
        $host = getenv('PGHOST');
        $port = getenv('PGPORT') ?: 5432;
        $db   = getenv('PGDATABASE');
        $user = getenv('PGUSER');
        $pass = getenv('PGPASSWORD');
        $dsn  = "pgsql:host=$host;port=$port;dbname=$db;user=$user;password=$pass;sslmode=require";
    }

    try {
        $pdo = new PDO($dsn, null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        return $pdo;
    } catch (PDOException $e) {
        error_log('iTeachXR DB error: ' . $e->getMessage());
        return null;
    }
}
