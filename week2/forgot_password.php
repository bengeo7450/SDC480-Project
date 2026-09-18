<?php
/**
 * File: forgot_password.php
 * Module: Password Recovery Initiation
 * Author: Ben George
 */
session_start();
require_once dirname(__DIR__) . '/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');

    if (empty($username)) {
        $error = "Please enter your username.";
    } else {
        // Check if user exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username");
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Redirect to reset page passing the account ID
            header("Location: reset_password.php?user_id=" . $user['id']);
            exit();
        } else {
            $error = "No account found with that username.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password - CAMS Portal</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f9; padding: 40px; }
        .card { max-width: 400px; margin: 0 auto; background: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"] { width: 100%; padding: 8px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px; }
        button { width: 100%; padding: 10px; background: #007bff; color: white; border: none; border-radius: 4px; font-weight: bold; cursor: pointer; }
        .alert-error { color: #dc3545; background: #f8d7da; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
    </style>
</head>
<body>

<div class="card">
    <h2>Account Recovery</h2>
    <p style="color: #6c757d; font-size: 0.9em;">Enter your username to reset your password.</p>

    <?php if ($error): ?>
        <div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form action="forgot_password.php" method="POST">
        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" required>
        </div>
        <button type="submit">Continue</button>
    </form>
    <p style="margin-top: 15px; text-align: center;"><a href="login.php">Back to Login</a></p>
</div>

</body>
</html>