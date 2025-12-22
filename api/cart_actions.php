<?php
session_start();

header('Content-Type: application/json');

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$action = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'count') {
    // Return total items in cart
    $count = 0;
    foreach ($_SESSION['cart'] as $item) {
        $count += $item['quantity'];
    }
    echo json_encode(['success' => true, 'count' => $count]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'add') {
    $input = json_decode(file_get_contents('php://input'), true);

    $productId = $input['product_id'] ?? null;
    $productName = $input['name'] ?? 'Unknown Product';
    $price = $input['price'] ?? 0;

    if (!$productId) {
        echo json_encode(['success' => false, 'message' => 'Product ID required']);
        exit;
    }

    // Check if product already in cart
    if (isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId]['quantity']++;
    } else {
        $_SESSION['cart'][$productId] = [
            'product_id' => $productId,
            'name' => $productName,
            'price' => $price,
            'quantity' => 1
        ];
    }

    // Calculate new total count
    $count = 0;
    foreach ($_SESSION['cart'] as $item) {
        $count += $item['quantity'];
    }

    echo json_encode(['success' => true, 'count' => $count, 'message' => 'Added to cart']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($action, ['remove', 'increment', 'decrement'])) {
    $input = json_decode(file_get_contents('php://input'), true);
    $productId = $input['product_id'] ?? null;

    if (!$productId || !isset($_SESSION['cart'][$productId])) {
        echo json_encode(['success' => false, 'message' => 'Item not found']);
        exit;
    }

    if ($action === 'remove') {
        unset($_SESSION['cart'][$productId]);
    } elseif ($action === 'increment') {
        $_SESSION['cart'][$productId]['quantity']++;
    } elseif ($action === 'decrement') {
        if ($_SESSION['cart'][$productId]['quantity'] > 1) {
            $_SESSION['cart'][$productId]['quantity']--;
        } else {
            // Optional: remove if qty goes to 0, or just do nothing
            // unset($_SESSION['cart'][$productId]);
        }
    }

    // Recalculate count
    $count = 0;
    foreach ($_SESSION['cart'] as $item) {
        $count += $item['quantity'];
    }

    echo json_encode(['success' => true, 'count' => $count]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid action']);
