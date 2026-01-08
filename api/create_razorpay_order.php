<?php

error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);

header('Content-Type: application/json');
session_start();
require '../db_conn.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$env = parse_ini_file('../.env');
$key_id = $env['KEY_ID'];
$key_secret = $env['KEY_SECRET'];

// Calculate total amount from session cart
$cart = $_SESSION['cart'] ?? [];
$total_amount = 0;
foreach ($cart as $item) {
    $total_amount += $item['price'] * $item['quantity'];
}

if ($total_amount <= 0) {
    echo json_encode(['success' => false, 'message' => 'Cart is empty']);
    exit;
}

// Razorpay order creation
$data = [
    'amount' => $total_amount * 100, // Amount in paise
    'currency' => 'INR',
    'receipt' => 'order_' . uniqid(),
    'payment_capture' => 1
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://api.razorpay.com/v1/orders");
curl_setopt($ch, CURLOPT_USERPWD, $key_id . ':' . $key_secret);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if (curl_errno($ch)) {
    echo json_encode(['success' => false, 'message' => 'Curl error: ' . curl_error($ch)]);
    curl_close($ch);
    exit;
}
curl_close($ch);

if ($http_status === 200) {
    $order_data = json_decode($response, true);

    // Create local order entry (optional, but good for tracking attempts, 
    // real order finalization happens in place_order.php after payment success)
    // For now, we just return the order ID to frontend.

    echo json_encode([
        'success' => true,
        'order_id' => $order_data['id'],
        'amount' => $data['amount'],
        'currency' => $data['currency'],
        'key_id' => $key_id,
        'user_name' => $_SESSION['username'] ?? 'User', // Assume username in session or fetch from DB if needed
        'user_email' => $_SESSION['email'] ?? 'test@example.com', // Needs to be fetched if not in session, but skipping for brevity
        'user_contact' => $_SESSION['mobile_number'] ?? ''
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Razorpay Order Error',
        'details' => json_decode($response)
    ]);
}
?>