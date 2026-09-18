<?php
/**
 * File: register.php
 * Module: User Account Registration & Role Assignment
 * Author: Ben George
 */
session_start();
require_once dirname(__DIR__) . '/db.php';

$error = '';
$success = '';

// Password complexity validation function
function validatePasswordComplexity($password) {
    // Requires: Min 8 chars, 1 uppercase, 1 lowercase, 1 number, 1 special character
    return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $password);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $username  = trim($_POST['username'] ?? '');
    $password  = $_POST['password'] ?? '';
    $role      = $_POST['role'] ?? 'client';

    if (empty($full_name) || empty($username) || empty($password)) {
        $error = "All fields are required.";
    } elseif (!validatePasswordComplexity($password)) {
        $error = "Password does not meet complexity requirements.";
    } else {
        // Check for existing user
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username");
        $stmt->execute([':username' => $username]);
        
        if ($stmt->fetch()) {
            $error = "Username is already taken.";
        } else {
            // Hash password and save account
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $insertStmt = $pdo->prepare("INSERT INTO users (full_name, username, password, role) VALUES (:name, :user, :pass, :role)");
            $insertStmt->execute([
                ':name' => $full_name,
                ':user' => $username,
                ':pass' => $hashedPassword,
                ':role' => $role
            ]);

            $success = "Account created successfully! You can now log in.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register Account - CAMS Portal</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f9; padding: 40px; }
        .card { max-width: 450px; margin: 0 auto; background: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"], input[type="password"], select { width: 100%; padding: 8px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px; }
        button { width: 100%; padding: 10px; background: #007bff; color: white; border: none; border-radius: 4px; font-weight: bold; cursor: pointer; }
        .alert-error { color: #dc3545; background: #f8d7da; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
        .alert-success { color: #155724; background: #d4edda; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
        .password-rules { background: #e9ecef; border-left: 4px solid #007bff; padding: 10px; margin-bottom: 15px; font-size: 0.85em; border-radius: 0 4px 4px 0; }
        .password-rules ul { margin: 5px 0 0 18px; padding: 0; }
    </style>
</head>
<body>

<div class="card">
    <h2>Create an Account</h2>

    <?php if ($error): ?>
        <div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert-success">
            <?php echo htmlspecialchars($success); ?><br><br>
            <a href="login.php" style="color: #155724; font-weight: bold;">Click here to Login</a>
        </div>
    <?php else: ?>
        <form action="register.php" method="POST">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="full_name" required>
            </div>

            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" required>
            </div>

            <div class="form-group">
                <label>Account Role</label>
                <select name="role">
                    <option value="client">Client</option>
                    <option value="admin">Administrator</option>
                </select>
            </div>

            <!-- Displayed Password Complexity Rules -->
            <div class="password-rules">
                <strong>Password Requirements:</strong>
                <ul>
                    <li>At least 8 characters long</li>
                    <li>At least one uppercase letter (A-Z)</li>
                    <li>At least one lowercase letter (a-z)</li>
                    <li>At least one number (0-9)</li>
                    <li>At least one special character (@$!%*?&)</li>
                </ul>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>

            <button type="submit">Register Account</button>
        </form>
    <?php endif; ?>

    <p style="margin-top: 15px; text-align: center;"><a href="login.php">Already have an account? Login</a></p>
</div>

</body>
</html>