<?php
/**
 * File: dashboard.php
 * Path: ProjectFiles/week1/dashboard.php
 * Module: Client Dashboard with Main-Screen Search
 * Author: Ben George
 */

session_start();
require_once dirname(__DIR__) . '/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'] ?? 'Client';

// Fetch client's appointments only
$stmt = $pdo->prepare("SELECT id, service_type, appointment_date, status FROM appointments WHERE user_id = :user_id ORDER BY appointment_date DESC");
$stmt->execute([':user_id' => $user_id]);
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Client Dashboard - CAMS Portal</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f6f9; margin: 0; padding: 20px; }
        .container { max-width: 900px; margin: 0 auto; background: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #eee; padding-bottom: 15px; margin-bottom: 20px; }
        .btn { padding: 8px 14px; border: none; border-radius: 4px; font-weight: bold; color: white; text-decoration: none; cursor: pointer; display: inline-block; }
        .btn-primary { background: #007bff; }
        .btn-success { background: #28a745; }
        .btn-danger { background: #dc3545; }
        .search-box { background: #e9ecef; padding: 15px; border-radius: 6px; margin-bottom: 25px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h2>Welcome, <?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?></h2>
        <div>
            <a href="booking.php" class="btn btn-success">+ Book Appointment</a>
            <a href="logout.php" class="btn btn-danger">Logout</a>
        </div>
    </div>

    <!-- Integrated Main-Page Search Tool -->
    <div class="search-box">
        <h3 style="margin-top: 0;">Search Your Appointments</h3>
        <form method="GET" action="../week2/search.php" style="display: flex; gap: 10px;">
            <input type="text" name="query" placeholder="Search by service or status..." style="flex-grow: 1; padding: 10px; border: 1px solid #ccc; border-radius: 4px;" required>
            <button type="submit" class="btn btn-primary">Search</button>
        </form>
    </div>

    <h3>Your Scheduled Appointments</h3>
    <table>
        <thead>
            <tr>
                <th>Appt ID</th>
                <th>Service Requested</th>
                <th>Date & Time</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($appointments)): ?>
                <?php foreach ($appointments as $row): ?>
                <tr>
                    <td><?php echo htmlspecialchars((string)$row['id'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars((string)$row['service_type'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars((string)$row['appointment_date'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><strong><?php echo htmlspecialchars((string)$row['status'], ENT_QUOTES, 'UTF-8'); ?></strong></td>
                    <td>
                        <a href="booking.php?edit_id=<?php echo htmlspecialchars((string)$row['id'], ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-primary" style="padding: 4px 8px; font-size: 0.9em;">Edit</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" style="text-align: center; color: #6c757d;">No appointments found. Use the button above to book one!</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>