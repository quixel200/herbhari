<?php
session_start();
require 'db_conn.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $identifier = trim($_POST['identifier']); // Can be email or mobile

    // Check if the identifier exists in email OR mobile_number column
    $stmt = $conn->prepare("SELECT user_id, username, email, mobile_number FROM users WHERE email = ? OR mobile_number = ?");
    $stmt->execute([$identifier, $identifier]);

    if ($stmt->rowCount() > 0) {
        // User exists: Save identifier to session and move to password page
        $_SESSION['login_identifier'] = $identifier;
        header("Location: login_verify.php");
        exit();
    } else {
        // User does not exist: Redirect to register
        header("Location: register.php");
        exit();
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
            <h2 class="text-center mb-4" style="color: #2c5f2d;">Sign In</h2>
            <p class="text-center text-muted mb-4">Enter your email or mobile number to continue</p>

            <form method="POST" action="">
                <div class="mb-4">
                    <label class="form-label">Email or Mobile Number</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                        <input type="text" name="identifier" class="form-control"
                            placeholder="e.g. user@email.com or +9198765..." required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2">Next</button>
            </form>

            <div class="text-center mt-3">
                <p>New here? <a href="register.php" style="color: #2c5f2d;">Create an account</a></p>
            </div>
        </div>
    </div>
</body>

</html>