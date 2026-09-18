<?php
/**
 * File: includes/header.php
 * Author: Ben George
 * Description: Reusable global header with dynamic session navigation.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CAMS Portal</title>
    <style>
        * { box-sizing: border-box; font-family: Arial, sans-serif; }
        body { margin: 0; padding: 0; background-color: #f4f6f9; color: #333; }
        .navbar { background: #1a252f; color: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        .navbar h1 { margin: 0; font-size: 1.5rem; }
        .nav-links { list-style: none; margin: 0; padding: 0; display: flex; gap: 15px; }
        .nav-links a { color: #ecf0f1; text-decoration: none; font-weight: bold; padding: 5px 10px; border-radius: 4px; transition: background 0.2s; }
        .nav-links a:hover { background: #34495e; }
        .container { padding: 30px; max-width: 1200px; margin: 0 auto; min-height: 80vh; }
    </style>
</head>
<body>

<nav class="navbar">
    <h1>CAMS Portal</h1>
    <ul class="nav-links">
        <?php if (isset($_SESSION['user_id'])): ?>
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="booking.php">Booking</a></li>
            <li><a href="search.php">Search</a></li>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <li><a href="admin_dashboard.php">Admin Panel</a></li>
            <?php endif; ?>
            <li><a href="logout.php">Logout (<?= htmlspecialchars($_SESSION['user_name'] ?? 'User') ?>)</a></li>
        <?php else: ?>
            <li><a href="login.php">Login</a></li>
            <li><a href="register.php">Register</a></li>
        <?php endif; ?>
    </ul>
</nav>

<div class="container">