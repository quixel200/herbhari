<?php
header('Content-Type: application/json');
session_start();
require '../db_conn.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$env = parse_ini_file('../.env');
$key_secret = $env['KEY_SECRET'];

$json = file_get_contents('php://input');
$data = json_decode($json, true);

$razorpay_payment_id = $data['razorpay_payment_id'];
$razorpay_order_id = $data['razorpay_order_id'];
$razorpay_signature = $data['razorpay_signature'];

// Verify Signature
$generated_signature = hash_hmac('sha256', $razorpay_order_id . "|" . $razorpay_payment_id, $key_secret);

if ($generated_signature === $razorpay_signature) {
    // Payment Successful
    $user_id = $_SESSION['user_id'];
    $cart = $_SESSION['cart'] ?? [];

    // Calculate total from cart again to be safe
    $total_amount = 0;
    foreach ($cart as $item) {
        $total_amount += $item['price'] * $item['quantity'];
    }

    // Default shipping address if not provided in request (assuming user has one in profile or passed here)
    // For MVP, using a placeholder or one from DB if available.
    // Fetching user address from DB
    $stmt = $conn->prepare("SELECT address FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $shipping_address = $user['address'] ?? 'Address not provided';

    try {
        $conn->beginTransaction();

        // Insert Order
        $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, razorpay_order_id, razorpay_payment_id, status, shipping_address) VALUES (?, ?, ?, ?, 'paid', ?)");
        $stmt->execute([$user_id, $total_amount, $razorpay_order_id, $razorpay_payment_id, $shipping_address]);
        $order_id = $conn->lastInsertId();

        // Insert Order Items
        $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        foreach ($cart as $item) {
            $stmt->execute([$order_id, $item['product_id'], $item['quantity'], $item['price']]);
        }

        // Clear Cart
        unset($_SESSION['cart']);

        $conn->commit();
        echo json_encode(['success' => true, 'order_id' => $order_id]);

    } catch (Exception $e) {
        $conn->rollBack();
        echo json_encode(['success' => false, 'message' => 'Database Error: ' . $e->getMessage()]);
    }

} else {
    echo json_encode(['success' => false, 'message' => 'Payment verification failed']);
}
?>