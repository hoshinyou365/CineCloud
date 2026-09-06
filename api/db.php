<?php
// CineCloud database connection.
// For AWS, replace the placeholder values with your Amazon RDS values.
// Do NOT put the RDS password in JavaScript or HTML.

$host = getenv('CINECLOUD_DB_HOST') ?: 'YOUR_RDS_ENDPOINT';
$db   = getenv('CINECLOUD_DB_NAME') ?: 'cinecloud';
$user = getenv('CINECLOUD_DB_USER') ?: 'admin';
$pass = getenv('CINECLOUD_DB_PASS') ?: 'YOUR_RDS_PASSWORD';
$port = getenv('CINECLOUD_DB_PORT') ?: '3306';

if (strpos($host, 'YOUR_') === 0 || strpos($pass, 'YOUR_') === 0) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['success'=>false,'message'=>'Database configuration is not set on the server.']);
    exit;
}

$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
} catch (PDOException $e) {
    error_log('CineCloud DB connection error: '.$e->getMessage());
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['success'=>false,'message'=>'Database connection failed.']);
    exit;
}

header('Content-Type: application/json; charset=utf-8');
