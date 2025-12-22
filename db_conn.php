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
    $conn = new PDO("mysql:host=$sname;dbname=$db_name", $uname, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    exit();
}
