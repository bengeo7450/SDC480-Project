<?php
/**
 * File: forgot_password.php
 * Module: Password Reset Request Engine
 * Author: Ben George
 */

session_start();

// Connect to database using root directory path
require_once dirname(__DIR__) . '/db.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (!empty($email)) {
        // Updated query: look up user by 'email' instead of 'username'
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Generates a secure reset token
            $token = bin2hex(random_bytes(32));
            
            // Redirect to reset_password.php passing the token and email
            header("Location: reset_password.php?token=" . urlencode($token) . "&email=" . urlencode($email));
            exit();
        } else {
            $error = "No account found with that email address.";
        }
    } else {
        $error = "Please enter your email address.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password - CAMS Portal</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f9; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .card { background: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); width: 100%; max-width: 380px; }
        .card h2 { margin-top: 0; color: #333; text-align: center; }
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

<div class="card">
    <h2>Reset Password</h2>

    <?php if (!empty($error)): ?>
        <div class="alert-error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>

    <form action="forgot_password.php" method="POST">
        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" placeholder="name@example.com" required autocomplete="email">
        </div>

        <button type="submit" class="btn-submit">Request Reset Link</button>
    </form>

    <div class="form-footer">
        <p>Remembered your password? <a href="../week1/login.php" style="color: #007bff; text-decoration: none;">Sign in here</a></p>
    </div>
</div>

</body>
</html>