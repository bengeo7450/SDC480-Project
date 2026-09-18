<?php
/**
 * File: login.php (Role-Aware Authentication)
 * Author: Ben George
 */
session_start();
require_once '../db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['username'] ?? $_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!empty($email) && !empty($password)) {
        // Query matching your database column ('email')
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Fetch password hash column
        $hash = $user['password_hash'] ?? $user['password'] ?? '';

        if ($user && password_verify($password, $hash)) {
            session_regenerate_id(true);

            // Populate session values
            $_SESSION['user_id']   = $user['id'] ?? $user['user_id'];
            $_SESSION['user_name'] = $user['full_name'] ?? $user['email'];
            $_SESSION['role']      = $user['role'] ?? 'client';

            // Role-based redirection across folders
            if ($_SESSION['role'] === 'admin') {
                header("Location: ../week2/admin_dashboard.php");
            } else {
                header("Location: dashboard.php");
            }
            exit();
        } else {
            $error = "Invalid email or password.";
        }
    } else {
        $error = "Please fill in all fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CAMS Portal - Login</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f9; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-card { background: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); width: 100%; max-width: 380px; }
        .login-card h2 { margin-top: 0; color: #333; text-align: center; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; color: #555; font-weight: bold; }
        .form-group input { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .btn-submit { width: 100%; padding: 10px; background: #007bff; border: none; color: white; font-weight: bold; border-radius: 4px; cursor: pointer; }
        .btn-submit:hover { background: #0056b3; }
        .alert-error { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 15px; text-align: center; font-size: 0.9rem; }
        .form-footer { margin-top: 15px; text-align: center; font-size: 0.9rem; }
    </style>
</head>
<body>

<div class="login-card">
    <h2>CAMS Portal Login</h2>

    <?php if (!empty($error)): ?>
        <div class="alert-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <form action="login.php" method="POST">
        <div class="form-group">
            <label for="username">Email Address</label>
            <input type="email" id="username" name="username" placeholder="name@example.com" required autocomplete="email">
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required autocomplete="current-password">
        </div>

        <button type="submit" class="btn-submit">Sign In</button>
    </form>
    <form action="login.php" method="POST">
        <!-- Existing username/password fields -->
        
        <button type="submit">Login</button>
        
        <div style="margin-top: 15px; text-align: center;">
            <a href="forgot_password.php" style="color: #007bff; text-decoration: none;">Forgot Password?</a>
        </div>
    </form>
    <div class="form-footer">
        <p>Don't have an account? <a href="register.php">Register here</a></p>
    </div>
</div>

</body>
</html>