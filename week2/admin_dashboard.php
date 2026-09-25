<?php
/**
 * File: admin_dashboard.php
 * Path: ProjectFiles/week2/admin_dashboard.php
 * Module: Administrative Control Panel with Direct CRUD Actions
 * Author: Ben George
 */

session_start();
require_once dirname(__DIR__) . '/db.php';

// Access Control Guard - Redirect to week1 login if unauthorized
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: ../week1/login.php");
    exit();
}

$msg = $_GET['msg'] ?? '';

// Handle Admin Deletion directly from Dashboard
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $appt_id = intval($_POST['appointment_id']);
    $stmt_del = $pdo->prepare("DELETE FROM appointments WHERE id = :id");
    $stmt_del->execute([':id' => $appt_id]);
    
    // Redirect back to this dashboard in week2
    header("Location: admin_dashboard.php?msg=deleted");
    exit();
}

// Fetch all system appointments directly from appointments table
$stmt = $pdo->prepare("SELECT id, user_id, service_type, appointment_date, status FROM appointments ORDER BY appointment_date DESC");
$stmt->execute();
$all_appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Portal - CAMS Portal</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f6f9; margin: 0; padding: 20px; }
        .container { max-width: 1000px; margin: 0 auto; background: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #eee; padding-bottom: 15px; margin-bottom: 20px; }
        .btn { padding: 6px 12px; border: none; border-radius: 4px; font-weight: bold; color: white; text-decoration: none; cursor: pointer; display: inline-block; }
        .btn-info { background: #17a2b8; }
        .btn-warning { background: #ffc107; color: #000; }
        .btn-danger { background: #dc3545; }
        .alert { background: #d4edda; color: #155724; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h2>Admin Portal</h2>
        <div>
            <!-- Calendar resides in week2 -->
            <a href="calendar.php" class="btn btn-info">📅 View Calendar</a>
            <!-- Logout resides in week1 -->
            <a href="../week1/logout.php" class="btn btn-danger">Logout</a>
        </div>
    </div>

    <?php if ($msg === 'deleted'): ?>
        <div class="alert">Appointment permanently deleted from system records.</div>
    <?php elseif ($msg === 'cancelled'): ?>
        <div class="alert">Appointment status updated successfully.</div>
    <?php endif; ?>

    <h3>Master System Appointments</h3>
    <table>
        <thead>
            <tr>
                <th>Appt ID</th>
                <th>Client User ID</th>
                <th>Service Requested</th>
                <th>Date & Time</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($all_appointments)): ?>
                <?php foreach ($all_appointments as $row): ?>
                <tr>
                    <td><?php echo htmlspecialchars((string)$row['id'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars((string)$row['user_id'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars((string)$row['service_type'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars((string)$row['appointment_date'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><strong><?php echo htmlspecialchars((string)$row['status'], ENT_QUOTES, 'UTF-8'); ?></strong></td>
                    <td>
                        <!-- Edit link to booking in week1 -->
                        <a href="../week1/booking.php?edit_id=<?php echo htmlspecialchars((string)$row['id'], ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-warning">Edit</a>
                        
                        <!-- Delete Form -->
                        <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this appointment as Admin?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="appointment_id" value="<?php echo htmlspecialchars((string)$row['id'], ENT_QUOTES, 'UTF-8'); ?>">
                            <button type="submit" class="btn btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" style="text-align: center; color: #6c757d;">No customer appointments registered in the database.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>