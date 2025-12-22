<?php
session_start();
require 'db_conn.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $mobile = $_POST['mobile'];
    $address = $_POST['address'];

    // Update query
    $sql = "UPDATE users SET username = ?, mobile_number = ?, address = ? WHERE user_id = ?";
    try {
        $stmt = $conn->prepare($sql);
        if ($stmt->execute([$username, $mobile, $address, $user_id])) {
            $message = "Profile updated successfully!";
            $_SESSION['username'] = $username; // Update session if name changed
        } else {
            $error = "Failed to update profile.";
        }
    } catch (PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Fetch user data
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    echo "User not found!";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Aswath Naturale</title>
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
                    <li class="nav-item">
                        <a class="nav-link" href="cart.php"><i class="fas fa-shopping-cart"></i> Cart</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container" style="margin-top: 100px; margin-bottom: 50px;">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h4 class="mb-0" style="color: #2c5f2d;"><i class="fas fa-user-circle me-2"></i>My Profile</h4>
                    </div>
                    <div class="card-body p-4">
                        <?php if ($message): ?>
                            <div class="alert alert-success alert-dismissible fade show">
                                <?php echo $message; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Full Name</label>
                                    <input type="text" name="username" class="form-control"
                                        value="<?php echo htmlspecialchars($user['username']); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Email Address</label>
                                    <input type="email" class="form-control"
                                        value="<?php echo htmlspecialchars($user['email']); ?>" disabled readonly>
                                    <small class="text-muted">Email cannot be changed</small>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Mobile Number</label>
                                <input type="text" name="mobile" class="form-control"
                                    value="<?php echo htmlspecialchars($user['mobile_number'] ?? ''); ?>"
                                    placeholder="+919876543210">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Shipping Address</label>
                                <textarea name="address" class="form-control" rows="4"
                                    placeholder="Enter your full delivery address"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <button type="submit" class="btn btn-primary px-4"
                                    style="background-color: #2c5f2d; border-color: #2c5f2d;">
                                    Update Profile
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Order History Link (Placeholder for now) -->
                <div class="card shadow-sm mt-4">
                    <div class="card-body">
                        <h5 class="card-title">My Orders</h5>
                        <p class="card-text">View your past orders and their status.</p>
                        <a href="history.php" class="btn btn-outline-success">View Order History</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>

</html>