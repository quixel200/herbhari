<?php
session_start();
require 'db_conn.php';

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $country_code = $_POST['country_code'];
    $mobile_number = $_POST['mobile'];
    $mobile = $country_code . $mobile_number;
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } elseif (
        strlen($password) < 8 ||
        !preg_match("/[A-Z]/", $password) ||
        !preg_match("/[a-z]/", $password) ||
        !preg_match("/[0-9]/", $password) ||
        !preg_match("/[\W_]/", $password)
    ) {
        $error = "Password must be at least 8 characters long and include an uppercase letter, a lowercase letter, a number, and a special character.";
    } else {
        // Check if email or mobile already exists
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ? OR mobile_number = ?");
        $stmt->execute([$email, $mobile]);

        if ($stmt->rowCount() > 0) {
            $error = "Email or Mobile number already registered!";
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert user
            $sql = "INSERT INTO users (username, email, mobile_number, password_hash) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);

            if ($stmt->execute([$username, $email, $mobile, $hashed_password])) {
                $success = "Registration successful! You can now <a href='login.php'>Login</a>";
            } else {
                $error = "Something went wrong. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Aswath Naturale</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <style>
        .auth-container {
            max-width: 500px;
            margin: 100px auto;
            padding: 30px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }

        .form-control:focus {
            border-color: #2c5f2d;
            box-shadow: 0 0 0 0.2rem rgba(44, 95, 45, 0.25);
        }

        .btn-primary {
            background-color: #2c5f2d;
            border-color: #2c5f2d;
        }

        .btn-primary:hover {
            background-color: #1e421f;
            border-color: #1e421f;
        }
    </style>
</head>

<body class="bg-light">
    <!-- Navbar (Simplified) -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-success fixed-top" style="background-color: #2c5f2d !important;">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <img src="images/logo.png" alt="Aswath Naturale" height="40" class="me-2">
                Aswath Naturale
            </a>
            <div class="ms-auto">
                <a href="index.php" class="text-white text-decoration-none me-3">Home</a>
                <a href="login.php" class="text-white text-decoration-none">Login</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="auth-container">
            <h2 class="text-center mb-4" style="color: #2c5f2d;">Create Account</h2>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mb-3">
                    <label class="form-label">Full Name</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Mobile Number</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-phone"></i></span>
                        <select name="country_code" class="form-select" style="max-width: 100px;">
                            <option value="+91">+91 (IN)</option>
                            <option value="+1">+1 (US)</option>
                            <option value="+44">+44 (UK)</option>
                            <option value="+971">+971 (UAE)</option>
                            <option value="+65">+65 (SG)</option>
                        </select>
                        <input type="tel" name="mobile" class="form-control" placeholder="Mobile Number" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <small class="text-muted" style="font-size: 0.8em;">
                        Must be at least 8 chars, with uppercase, lowercase, number & special char.
                    </small>
                </div>

                <div class="mb-4">
                    <label class="form-label">Confirm Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" name="confirm_password" class="form-control" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2">Register</button>
            </form>

            <div class="text-center mt-3">
                <p>Already have an account? <a href="login.php" style="color: #2c5f2d;">Login here</a></p>
            </div>
        </div>
    </div>
</body>

</html>