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