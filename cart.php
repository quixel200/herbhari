<?php
session_start();
require 'db_conn.php';

$cart = $_SESSION['cart'] ?? [];
$total = 0;
foreach ($cart as $item) {
    $total += $item['price'] * $item['quantity'];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Aswath Naturale</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>

<body class="bg-light">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-success fixed-top" style="background-color: #2c5f2d !important;">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <img src="images/logo.png" alt="Aswath Naturale" height="40" class="me-2">
                Aswath Naturale
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="profile.php">Profile</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">Login</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container" style="margin-top: 100px; margin-bottom: 50px;">
        <h2 class="mb-4 text-center" style="color: #2c5f2d;">Your Shopping Cart</h2>

        <?php if (empty($cart)): ?>
            <div class="text-center py-5">
                <i class="fas fa-shopping-basket fa-4x text-muted mb-3"></i>
                <p class="lead">Your cart is empty.</p>
                <a href="index.php" class="btn btn-success">Continue Shopping</a>
            </div>
        <?php else: ?>
            <div class="row">
                <div class="col-md-8">
                    <div class="card shadow-sm mb-4">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0 align-middle">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="ps-4">Product</th>
                                            <th>Price</th>
                                            <th style="width: 150px;">Quantity</th>
                                            <th>Total</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($cart as $id => $item): ?>
                                            <tr data-id="<?php echo $id; ?>">
                                                <td class="ps-4">
                                                    <h6 class="mb-0"><?php echo htmlspecialchars($item['name']); ?></h6>
                                                </td>
                                                <td>₹<?php echo number_format($item['price'], 2); ?></td>
                                                <td>
                                                    <div class="input-group input-group-sm">
                                                        <button class="btn btn-outline-secondary btn-minus"
                                                            type="button">-</button>
                                                        <input type="text" class="form-control text-center quantity-input"
                                                            value="<?php echo $item['quantity']; ?>" readonly>
                                                        <button class="btn btn-outline-secondary btn-plus"
                                                            type="button">+</button>
                                                    </div>
                                                </td>
                                                <td class="fw-bold">₹<span
                                                        class="item-total"><?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm btn-link text-danger btn-remove"><i
                                                            class="fas fa-trash"></i></button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">Order Summary</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal</span>
                                <span class="fw-bold">₹<span
                                        id="cart-total"><?php echo number_format($total, 2); ?></span></span>
                            </div>
                            <div class="d-flex justify-content-between mb-3 text-success">
                                <span>Shipping</span>
                                <span>Free</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between mb-4">
                                <span class="h5">Total</span>
                                <span class="h5 fw-bold" style="color: #2c5f2d;">₹<span
                                        id="grand-total"><?php echo number_format($total, 2); ?></span></span>
                            </div>
                            <d-grid>
                                <a href="checkout.php" class="btn btn-success w-100 py-2">Proceed to Checkout</a>
                            </d-grid>
                            <div class="text-center mt-3">
                                <a href="index.php" class="text-secondary text-decoration-none"><i
                                        class="fas fa-arrow-left me-1"></i> Continue Shopping</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Function to update cart via API
            async function updateCart(productId, action, currentQty = 0) {
                try {
                    const response = await fetch('api/cart_actions.php?action=' + action, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ product_id: productId })
                    });
                    const data = await response.json();

                    if (data.success) {
                        location.reload(); // Simple reload for now to reflect changes
                    } else {
                        alert('Error: ' + data.message);
                    }
                } catch (error) {
                    console.error('Error:', error);
                }
            }

            document.querySelectorAll('.btn-plus').forEach(btn => {
                btn.addEventListener('click', function () {
                    const row = this.closest('tr');
                    const id = row.dataset.id;
                    updateCart(id, 'increment');
                });
            });

            document.querySelectorAll('.btn-minus').forEach(btn => {
                btn.addEventListener('click', function () {
                    const row = this.closest('tr');
                    const input = row.querySelector('.quantity-input');
                    if (parseInt(input.value) > 1) {
                        const id = row.dataset.id;
                        updateCart(id, 'decrement');
                    }
                });
            });

            document.querySelectorAll('.btn-remove').forEach(btn => {
                btn.addEventListener('click', function () {
                    if (confirm('Are you sure you want to remove this item?')) {
                        const row = this.closest('tr');
                        const id = row.dataset.id;
                        updateCart(id, 'remove');
                    }
                });
            });
        });
    </script>
</body>

</html>