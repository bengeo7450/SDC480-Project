<?php
/**
 * File: admin_dashboard.php
 * Module: Admin Control Center
 * Author: Ben George
 */

session_start();

// Connect to database using root directory path
require_once dirname(__DIR__) . '/db.php';

// Enforce admin privileges
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../week1/login.php?error=unauthorized");
    exit();
}

// Fetch all system appointments joining user names
$sql = "SELECT 
            a.id AS appointment_id,
            a.user_id,
            u.full_name AS client_name,
            a.service_type,
            a.appointment_date,
            a.status
        FROM appointments a
        LEFT JOIN users u ON a.user_id = u.id
        ORDER BY a.appointment_date DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$all_appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

$adminName = $_SESSION['user_name'] ?? $_SESSION['username'] ?? 'Admin';
$msg = $_GET['msg'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CAMS Admin Control Center</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f6f9; margin: 0; padding: 20px; }
        .header { display: flex; justify-content: space-between; align-items: center; background: #343a40; color: #fff; padding: 15px 20px; border-radius: 6px; }
        .card { background: #fff; padding: 20px; border-radius: 8px; margin-top: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .search-container { background: #e9ecef; padding: 15px; border-radius: 6px; margin-bottom: 20px; }
        .search-form { display: flex; gap: 10px; margin-top: 10px; }
        .search-form input[type="text"] { flex-grow: 1; padding: 8px; border: 1px solid #ced4da; border-radius: 4px; }
        .btn-search { background: #007bff; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; font-weight: bold; }
        .alert-success { background: #d4edda; color: #155724; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; vertical-align: middle; }
        th { background-color: #007bff; color: white; }
        .btn-danger { background: #dc3545; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; }
        .btn-warning { background: #ffc107; color: #212529; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-weight: bold; }
        .action-cell { display: flex; gap: 10px; align-items: center; }
    </style>
</head>
<body>

    <div class="header">
        <h2>CAMS Admin Control Center</h2>
        <div>
            Welcome, <strong><?php echo htmlspecialchars($adminName, ENT_QUOTES, 'UTF-8'); ?></strong> (Admin) |
            <a href="../week1/logout.php" style="color: #ffc107; text-decoration: none;">Logout</a>
        </div>
    </div>

    <div class="card">
        <!-- Display action message banners -->
        <?php if ($msg === 'rescheduled'): ?>
            <div class="alert-success">Appointment successfully rescheduled!</div>
        <?php elseif ($msg === 'deleted'): ?>
            <div class="alert-success">Appointment successfully deleted!</div>
        <?php endif; ?>

        <!-- Global Search integration pointing to search.php -->
        <div class="search-container">
            <strong>System Search:</strong>
            <form action="search.php" method="GET" class="search-form">
                <input type="text" name="query" placeholder="Search appointments by service or status..." required>
                <button type="submit" class="btn-search">Search Engine</button>
            </form>
        </div>

        <h3>Master Appointment Records</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Client Name</th>
                    <th>Service Type</th>
                    <th>Date & Time</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($all_appointments)): ?>
                    <?php foreach ($all_appointments as $app): 
                        $appId      = $app['appointment_id'] ?? 'N/A';
                        $clientName = $app['client_name'] ?? 'Guest / Unknown';
                        $service    = $app['service_type'] ?? 'General Service';
                        $date       = $app['appointment_date'] ?? 'N/A';
                        $status     = $app['status'] ?? 'Scheduled';
                        $formattedDate = ($date !== 'N/A') ? date('Y-m-d\TH:i', strtotime($date)) : '';
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars((string)$appId, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars((string)$clientName, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars((string)$service, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars((string)$date, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><strong><?php echo htmlspecialchars((string)$status, ENT_QUOTES, 'UTF-8'); ?></strong></td>
                        <td>
                            <div class="action-cell">
                                <!-- Reschedule Form -->
                                <form action="schedule.php" method="POST" style="display:flex; gap:5px;">
                                    <input type="hidden" name="action" value="reschedule">
                                    <input type="hidden" name="appointment_id" value="<?php echo htmlspecialchars((string)$appId, ENT_QUOTES, 'UTF-8'); ?>">
                                    <input type="datetime-local" name="new_date" value="<?php echo $formattedDate; ?>" required>
                                    <button type="submit" class="btn-warning">Reschedule</button>
                                </form>

                                <!-- Delete Form -->
                                <form action="schedule.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="action" value="cancel">
                                    <input type="hidden" name="appointment_id" value="<?php echo htmlspecialchars((string)$appId, ENT_QUOTES, 'UTF-8'); ?>">
                                    <button type="submit" class="btn-danger" onclick="return confirm('Delete this record?');">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center; color: #6c757d;">No records found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</body>
</html>