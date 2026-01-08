<?php
session_start();
require 'db_conn.php';

// Ensure user is admin
// (Assuming you have a role check, if not, add basic auth check here)
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Fetch all orders with user details
$sql = "SELECT o.*, u.username, u.email 
        FROM orders o 
        JOIN users u ON o.user_id = u.user_id 
        ORDER BY o.created_at DESC";
$stmt = $conn->query($sql);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle Status Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $status = $_POST['status'];
    $updateStmt = $conn->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
    $updateStmt->execute([$status, $order_id]);
    header("Location: admin_orders.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Orders - Aswath Naturale</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body class="bg-light">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-success fixed-top" style="background-color: #2c5f2d !important;">
        <div class="container">
            <a class="navbar-brand" href="admin_dashboard.php">Aswath Admin</a>
             <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto">
                     <li class="nav-item"><a class="nav-link" href="admin_dashboard.php">Dashboard</a></li>
                     <li class="nav-item"><a class="nav-link active" href="admin_orders.php">Orders</a></li>
                     <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container" style="margin-top: 100px;">
        <h2 class="mb-4">Order Management</h2>
        
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Total</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td>#<?php echo $order['order_id']; ?></td>
                                    <td>
                                        <div><?php echo htmlspecialchars($order['username']); ?></div>
                                        <small class="text-muted"><?php echo htmlspecialchars($order['email']); ?></small>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo match($order['status']) {
                                                'paid', 'delivered' => 'success',
                                                'shipped' => 'primary',
                                                'cancelled' => 'danger',
                                                default => 'warning'
                                            };
                                        ?>">
                                            <?php echo ucfirst($order['status']); ?>
                                        </span>
                                    </td>
                                    <td>₹<?php echo number_format($order['total_amount'], 2); ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#orderModal<?php echo $order['order_id']; ?>">
                                            Details
                                        </button>
                                    </td>
                                </tr>

                                <!-- Modal -->
                                <div class="modal fade" id="orderModal<?php echo $order['order_id']; ?>" tabindex="-1">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Order Details #<?php echo $order['order_id']; ?></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row mb-3">
                                                    <div class="col-md-6">
                                                        <h6>Shipping Address</h6>
                                                        <p class="text-muted"><?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></p>
                                                    </div>
                                                    <div class="col-md-6 text-end">
                                                        <h6>Payment Info</h6>
                                                        <p class="text-muted mb-0">Rzp ID: <?php echo $order['razorpay_payment_id']; ?></p>
                                                    </div>
                                                </div>
                                                
                                                <h6>Items</h6>
                                                <table class="table table-sm table-bordered">
                                                    <thead>
                                                        <tr>
                                                            <th>Product</th>
                                                            <th>Qty</th>
                                                            <th>Price</th>
                                                            <th>Total</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php
                                                            $itemStmt = $conn->prepare("
                                                                SELECT oi.*, p.name 
                                                                FROM order_items oi 
                                                                JOIN products p ON oi.product_id = p.product_id 
                                                                WHERE oi.order_id = ?
                                                            ");
                                                            $itemStmt->execute([$order['order_id']]);
                                                            $items = $itemStmt->fetchAll(PDO::FETCH_ASSOC);
                                                            foreach ($items as $item):
                                                        ?>
                                                        <tr>
                                                            <td><?php echo htmlspecialchars($item['name']); ?></td>
                                                            <td><?php echo $item['quantity']; ?></td>
                                                            <td>₹<?php echo number_format($item['price'], 2); ?></td>
                                                            <td>₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                                        </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>

                                                <hr>
                                                <form method="POST" class="d-flex align-items-center gap-2">
                                                    <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                                    <label class="fw-bold">Update Status:</label>
                                                    <select name="status" class="form-select form-select-sm w-auto">
                                                        <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                        <option value="paid" <?php echo $order['status'] == 'paid' ? 'selected' : ''; ?>>Paid</option>
                                                        <option value="shipped" <?php echo $order['status'] == 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                                        <option value="delivered" <?php echo $order['status'] == 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                                        <option value="cancelled" <?php echo $order['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                                    </select>
                                                    <button type="submit" name="update_status" class="btn btn-sm btn-primary">Update</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>
