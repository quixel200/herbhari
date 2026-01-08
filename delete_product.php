<?php
session_start();
require 'db_conn.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Check if ID is provided
if (isset($_GET['id'])) {
    $product_id = $_GET['id'];

    try {
        // Prepare delete statement
        $stmt = $conn->prepare("DELETE FROM products WHERE product_id = :id");
        $stmt->bindParam(':id', $product_id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            // Redirect with success message
            header("Location: admin_dashboard.php?msg=" . urlencode("Product deleted successfully"));
            exit();
        } else {
            // Redirect with error message
            header("Location: admin_dashboard.php?error=" . urlencode("Failed to delete product"));
            exit();
        }
    } catch (PDOException $e) {
        // Redirect with exception message
        header("Location: admin_dashboard.php?error=" . urlencode("Database error: " . $e->getMessage()));
        exit();
    }
} else {
    header("Location: admin_dashboard.php");
    exit();
}
