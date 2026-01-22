<?php
session_start();
require 'db_conn.php';

// If user tries to access this page without passing Step 1, redirect back
if (!isset($_SESSION['login_identifier'])) {
    header("Location: login.php");
    exit();
}

$identifier = $_SESSION['login_identifier'];
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $password = $_POST['password'];

    // Fetch the user based on the session identifier
    $stmt = $conn->prepare("SELECT user_id, username, password_hash, role FROM users WHERE email = ? OR mobile_number = ?");
    $stmt->execute([$identifier, $identifier]);

    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (password_verify($password, $user['password_hash'])) {
            // Success: Set session variables
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            // Remove the temporary login identifier
            unset($_SESSION['login_identifier']);

            header("Location: index.php");
            exit();
        } else {
            $error = "Incorrect password.";
        }
    } else {
        // Fallback (shouldn't happen unless user was deleted between steps)
        $error = "User not found.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enter Password - Aswath Naturale</title>
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

        .user-pill {
            background: #e9ecef;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.9em;
            display: inline-block;
            margin-bottom: 20px;
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
        </div>
    </nav>

    <div class="container">
        <div class="auth-container">
            <div class="text-center">
                <h2 class="mb-2" style="color: #2c5f2d;">Welcome Back</h2>
                <div class="user-pill text-muted">
                    <?php echo htmlspecialchars($identifier); ?>
                    <a href="login.php" class="text-decoration-none ms-2" title="Change User"><i
                            class="fas fa-edit"></i></a>
                </div>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mb-4">
                    <label class="form-label">Enter Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" name="password" id="password" class="form-control" required>
                        <span class="input-group-text"
                            onclick="togglePasswordVisibility('password', 'togglePasswordIcon')"
                            style="cursor: pointer;">
                            <i class="fas fa-eye" id="togglePasswordIcon"></i>
                        </span>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2">Login</button>
            </form>

            <div class="text-center mt-3">
                <a href="forgot_password.php" style="color: #666; font-size: 0.9em;">Forgot Password?</a>
            </div>
        </div>
    </div>

    <script>
        function togglePasswordVisibility(inputId, iconId) {
            const passwordInput = document.getElementById(inputId);
            const icon = document.getElementById(iconId);

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
</body>

</html>