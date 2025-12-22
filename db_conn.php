<?php
// Load environment variables
$env = parse_ini_file('.env');
if ($env === false) {
    die("Configuration error: .env file not found or invalid");
}

$sname = $env['DB_HOST'] ?? 'localhost';
$uname = $env['DB_USERNAME'] ?? 'root';
$password = $env['DB_PASSWORD'] ?? '';
$db_name = $env['DB_DATABASE'] ?? 'herbhari';

try {
    $conn = new PDO(
        "mysql:host={$env['DB_HOST']};dbname={$env['DB_NAME']}",
        $env['DB_USER'],
        $env['DB_PASS']
    );
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}