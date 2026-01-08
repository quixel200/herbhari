<?php
session_start();
require 'db_conn.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$cart = $_SESSION['cart'] ?? [];
if (empty($cart)) {
    header("Location: cart.php");
    exit;
}

$total = 0;
foreach ($cart as $item) {
    $total += $item['price'] * $item['quantity'];
}

// Fetch user details for pre-filling
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Aswath Naturale</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
</head>

<body class="bg-light">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-success fixed-top" style="background-color: #2c5f2d !important;">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <img src="images/logo.png" alt="Aswath Naturale" height="40" class="me-2">
                Aswath Naturale
            </a>
        </div>
    </nav>

    <div class="container" style="margin-top: 100px; margin-bottom: 50px;">
        <h2 class="mb-4 text-center" style="color: #2c5f2d;">Checkout</h2>

        <div class="row">
            <div class="col-md-7">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Shipping Details</h5>
                    </div>
                    <div class="card-body">
                        <form>
                            <div class="mb-3">
                                <label class="form-label">Name</label>
                                <input type="text" class="form-control"
                                    value="<?php echo htmlspecialchars($user['username']); ?>" readonly>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control"
                                    value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Mobile Number</label>
                                <input type="text" class="form-control"
                                    value="<?php echo htmlspecialchars($user['mobile_number'] ?? ''); ?>" readonly>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Shipping Address</label>
                                <textarea class="form-control" rows="3"
                                    readonly><?php echo htmlspecialchars($user['address'] ?? 'No address provided'); ?></textarea>
                                <div class="form-text"><a href="profile.php">Update Address in Profile</a></div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-5">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Order Summary</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush mb-3">
                            <?php foreach ($cart as $item): ?>
                                <li class="list-group-item d-flex justify-content-between lh-sm">
                                    <div>
                                        <h6 class="my-0">
                                            <?php echo htmlspecialchars($item['name']); ?>
                                        </h6>
                                        <small class="text-muted">Qty:
                                            <?php echo $item['quantity']; ?>
                                        </small>
                                    </div>
                                    <span class="text-muted">₹
                                        <?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                                    </span>
                                </li>
                            <?php endforeach; ?>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Total (INR)</span>
                                <strong>₹
                                    <?php echo number_format($total, 2); ?>
                                </strong>
                            </li>
                        </ul>

                        <button id="pay-btn" class="btn btn-success w-100 py-2">Pay Now</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('pay-btn').onclick = async function (e) {
            e.preventDefault();
            const btn = this;
            btn.disabled = true;
            btn.innerHTML = 'Processing...';

            try {
                // 1. Create Order on Backend
                const response = await fetch('api/create_razorpay_order.php', { method: 'POST' });
                const orderData = await response.json();

                if (!orderData.success) {
                    alert('Error creating order: ' + orderData.message);
                    btn.disabled = false;
                    btn.innerHTML = 'Pay Now';
                    return;
                }

                // 2. Initialize Razorpay Checkout
                var options = {
                    "key": orderData.key_id,
                    "amount": orderData.amount,
                    "currency": orderData.currency,
                    "name": "Aswath Naturale",
                    "description": "Payment for Order",
                    "image": "images/logo.png",
                    "order_id": orderData.order_id,
                    "handler": async function (response) {
                        // 3. Verify Payment on Backend
                        const verifyResponse = await fetch('api/place_order.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({
                                razorpay_payment_id: response.razorpay_payment_id,
                                razorpay_order_id: response.razorpay_order_id,
                                razorpay_signature: response.razorpay_signature
                            })
                        });

                        const verifyData = await verifyResponse.json();

                        if (verifyData.success) {
                            window.location.href = 'order_success.php?order_id=' + verifyData.order_id;
                        } else {
                            alert('Payment verification failed: ' + verifyData.message);
                            btn.disabled = false;
                            btn.innerHTML = 'Pay Now';
                        }
                    },
                    "prefill": {
                        "name": orderData.user_name,
                        "email": orderData.user_email,
                        "contact": orderData.user_contact
                    },
                    "theme": {
                        "color": "#2c5f2d"
                    }
                };
                var rzp1 = new Razorpay(options);
                rzp1.on('payment.failed', function (response) {
                    alert('Payment Failed: ' + response.error.description);
                    btn.disabled = false;
                    btn.innerHTML = 'Pay Now';
                });
                rzp1.open();

            } catch (error) {
                console.error('Error:', error);
                alert('Something went wrong!');
                btn.disabled = false;
                btn.innerHTML = 'Pay Now';
            }
        };
    </script>
</body>

</html>