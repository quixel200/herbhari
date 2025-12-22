<?php
session_start();
require 'db_conn.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT user_id, username, password_hash, role FROM users WHERE email = ?");
    $stmt->execute([$email]);

    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            header("Location: index.php");
            exit();
        } else {
            $error = "Invalid password!";
        }
    } else {
        $error = "User not found!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Aswath Naturale</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <style>
        .auth-container {
            max-width: 400px;
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
                <a href="register.php" class="text-white text-decoration-none">Register</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="auth-container">
            <h2 class="text-center mb-4" style="color: #2c5f2d;">Welcome Back</h2>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mb-3">
                    <label class="form-label">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2">Login</button>
            </form>

            <div class="text-center mt-3">
                <p>Don't have an account? <a href="register.php" style="color: #2c5f2d;">Register here</a></p>
            </div>
        </div>
    </div>
</body>

</html>