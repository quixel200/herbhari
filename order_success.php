<?php
session_start();
if (!isset($_GET['order_id'])) {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Success - Aswath Naturale</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
</head>

<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-success fixed-top" style="background-color: #2c5f2d !important;">
        <div class="container">
            <a class="navbar-brand" href="index.php">Aswath Naturale</a>
        </div>
    </nav>

    <div class="container" style="margin-top: 150px;">
        <div class="text-center">
            <i class="fas fa-check-circle text-success" style="font-size: 80px;"></i>
            <h1 class="mt-4">Thank You!</h1>
            <p class="lead">Your order has been placed successfully.</p>
            <p>Order ID: #
                <?php echo htmlspecialchars($_GET['order_id']); ?>
            </p>

            <div class="mt-5">
                <a href="index.php" class="btn btn-outline-success">Continue Shopping</a>
                <a href="profile.php" class="btn btn-success ms-2">View Orders</a>
            </div>
        </div>
    </div>

    <script>
        // Confetti effect
        var duration = 3 * 1000;
        var end = Date.now() + duration;

        (function frame() {
            confetti({
                particleCount: 5,
                angle: 60,
                spread: 55,
                origin: { x: 0 },
                colors: ['#2c5f2d', '#90EE90']
            });
            confetti({
                particleCount: 5,
                angle: 120,
                spread: 55,
                origin: { x: 1 },
                colors: ['#2c5f2d', '#90EE90']
            });

            if (Date.now() < end) {
                requestAnimationFrame(frame);
            }
        }());
    </script>
</body>

</html>