<?php
header('Content-Type: application/json');

// Load environment variables
$env = parse_ini_file('.env');
if ($env === false) {
    die(json_encode(['success' => false, 'message' => 'Configuration error']));
}

try {
    $conn = new PDO(
        "mysql:host={$env['DB_HOST']};dbname={$env['DB_NAME']}", 
        $env['DB_USER'], 
        $env['DB_PASS']
    );
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);

    $stmt = $conn->prepare("INSERT INTO newsletter_subscribers (email, name, phone) VALUES (?, ?, ?)");
    $stmt->execute([$email, $name, $phone]);

    echo json_encode(['success' => true, 'message' => 'Thank you for subscribing!']);
} catch(PDOException $e) {
    if($e->getCode() == 23000) { // Duplicate email error
        echo json_encode(['success' => false, 'message' => 'This email is already subscribed.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again.']);
    }
}