<?php
session_start();
require 'db_conn.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Fetch stats
$product_count = $conn->query("SELECT COUNT(*) FROM products")->fetchColumn();
$order_count = $conn->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$user_count = $conn->query("SELECT COUNT(*) FROM users")->fetchColumn();
$pending_orders = $conn->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn();

// Fetch recent products
$products = $conn->query("SELECT * FROM products ORDER BY created_at DESC LIMIT 10")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Aswath Naturale</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            background-color: #2c5f2d;
        }
        .sidebar a {
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            padding: 10px 20px;
            display: block;
        }
        .sidebar a:hover, .sidebar a.active {
            color: #fff;
            background-color: rgba(255,255,255,0.1);
        }
        .card-stat {
            border-left: 4px solid #2c5f2d;
        }
    </style>
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <div class="sidebar p-3 text-white" style="width: 250px;">
            <h4 class="mb-4 ps-2">Admin Panel</h4>
            <nav>
                <a href="admin_dashboard.php" class="active"><i class="fas fa-tachometer-alt me-2"></i> Dashboard</a>
                <a href="add_product.php"><i class="fas fa-plus me-2"></i> Add Product</a>
                <a href="#"><i class="fas fa-box me-2"></i> Manage Products</a>
                <a href="#"><i class="fas fa-shopping-bag me-2"></i> Orders</a>
                <a href="#"><i class="fas fa-users me-2"></i> Users</a>
                <a href="index.php" target="_blank" class="mt-4"><i class="fas fa-external-link-alt me-2"></i> View Website</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt me-2"></i> Logout</a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-grow-1 p-4 bg-light">
            <h2 class="mb-4">Dashboard Overview</h2>
            
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card card-stat shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted">Total Products</h6>
                            <h3><?php echo $product_count; ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-stat shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted">Total Orders</h6>
                            <h3><?php echo $order_count; ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-stat shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted">Pending Orders</h6>
                            <h3 class="text-warning"><?php echo $pending_orders; ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-stat shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted">Registered Users</h6>
                            <h3><?php echo $user_count; ?></h3>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Recent Products</h5>
                    <a href="add_product.php" class="btn btn-sm btn-success">Add New</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Image</th>
                                    <th>Name</th>
                                    <th>Category</th>
                                    <th>Price</th>
                                    <th>Stock</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($products as $p): ?>
                                <tr>
                                    <td>#<?php echo $p['product_id']; ?></td>
                                    <td>
                                        <?php if($p['image_url']): ?>
                                            <img src="<?php echo htmlspecialchars($p['image_url']); ?>" height="40" alt="img">
                                        <?php else: ?>
                                            <span class="text-muted">No img</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($p['name']); ?></td>
                                    <td><span class="badge bg-secondary"><?php echo htmlspecialchars($p['category']); ?></span></td>
                                    <td>₹<?php echo number_format($p['price'], 2); ?></td>
                                    <td><?php echo $p['stock_quantity']; ?></td>
                                    <td>
                                        <a href="edit_product.php?id=<?php echo $p['product_id']; ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                                        <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if(empty($products)): ?>
                                    <tr><td colspan="6" class="text-center">No products found.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
