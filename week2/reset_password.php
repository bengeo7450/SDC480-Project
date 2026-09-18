<?php
/**
 * File: reset_password.php
 * Module: Password Reset & Complexity Enforcement
 * Author: Ben George
 */

session_start();

// Connect to the database using an absolute path to the db file
require_once dirname(__DIR__) . '/db.php';

$error = '';
$success = '';

// Check GET or POST to grab the user's ID, defaulting to 0 if not present
$user_id = intval($_GET['user_id'] ?? $_POST['user_id'] ?? 0);

// If no valid user ID was passed, redirect back to the forgot password screen
if ($user_id === 0) {
    header("Location: forgot_password.php");
    exit();
}

/**
 * Helper function using regular expressions (regex) to check password strength.
 * Requires: >= 8 chars, 1 lowercase, 1 uppercase, 1 digit, and 1 special char.
 */
function validatePasswordComplexity($password) {
    return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $password);
}

// Handle password reset submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_password     = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Form input validation checks
    if (empty($new_password) || empty($confirm_password)) {
        $error = "Please fill in all fields.";
    } elseif ($new_password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (!validatePasswordComplexity($new_password)) {
        $error = "Password must be at least 8 characters long and contain at least one uppercase letter, one lowercase letter, one number, and one special character (@$!%*?&).";
    } else {
        // Securely hash the new password using BCRYPT before saving
        $new_hash = password_hash($new_password, PASSWORD_BCRYPT);
        
        // Update the password in the database using a prepared statement
        $stmt = $pdo->prepare("UPDATE users SET password = :password WHERE id = :id");
        $stmt->execute([':password' => $new_hash, ':id' => $user_id]);

        $success = "Your password has been successfully reset! You can now login.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password - CAMS Portal</title>
    
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f9; padding: 40px; }
        .card { max-width: 400px; margin: 0 auto; background: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="password"] { width: 100%; padding: 8px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px; }
        button { width: 100%; padding: 10px; background: #28a745; color: white; border: none; border-radius: 4px; font-weight: bold; cursor: pointer; }
        .alert-error { color: #dc3545; background: #f8d7da; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
        .alert-success { color: #155724; background: #d4edda; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
        .rules { font-size: 0.8em; color: #6c757d; margin-top: 4px; }
    </style>
</head>
<body>

<div class="card">
    <h2>Set New Password</h2>

    <!-- Display error message block if validation fails -->
    <?php if ($error): ?>
        <div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <!-- Display success message block or the reset form -->
    <?php if ($success): ?>
        <div class="alert-success">
            <?php echo htmlspecialchars($success); ?><br><br>
            <a href="login.php" style="color: #155724; font-weight: bold;">Click here to Login</a>
        </div>
    <?php else: ?>
        <!-- Form posts to itself to process password update -->
        <form action="reset_password.php" method="POST">
            <!-- Pass user_id secretly back to the server when form submits -->
            <input type="hidden" name="user_id" value="<?php echo htmlspecialchars((string)$user_id); ?>">

            <div class="form-group">
                <label>New Password</label>
                <input type="password" name="new_password" required>
                <div class="rules">Min. 8 characters, 1 uppercase, 1 lowercase, 1 number, & 1 special character (@$!%*?&).</div>
            </div>

            <div class="form-group">
                <label>Confirm New Password</label>
                <input type="password" name="confirm_password" required>
            </div>

            <button type="submit">Reset Password</button>
        </form>
    <?php endif; ?>
</div>

</body>
</html>