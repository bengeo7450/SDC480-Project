<?php
/**
 * File: dashboard.php
 * Module: Client Dashboard
 * Author: Ben George
 */

session_start();

// Connect to database looking up one folder
require_once dirname(__DIR__) . '/db.php';

// Protect page: require user to be logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?error=unauthorized");
    exit();
}

$user_id   = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? $_SESSION['username'] ?? 'Client';

// Fetch only appointments belonging to the logged-in user
$stmt = $pdo->prepare("SELECT id AS appointment_id, service_type, appointment_date, status FROM appointments WHERE user_id = :user_id ORDER BY appointment_date DESC");
$stmt->execute([':user_id' => $user_id]);
$my_appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

$msg = $_GET['msg'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Dashboard - CAMS Portal</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f6f9; margin: 0; padding: 20px; }
        .header { display: flex; justify-content: space-between; align-items: center; background: #007bff; color: #fff; padding: 15px 20px; border-radius: 6px; }
        .nav-links a { color: #fff; text-decoration: none; margin-left: 15px; font-weight: bold; }
        .nav-links a:hover { text-decoration: underline; }
        .card { background: #fff; padding: 25px; border-radius: 8px; margin-top: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .actions-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .search-container { background: #e9ecef; padding: 15px; border-radius: 6px; margin-bottom: 20px; }
        .search-form { display: flex; gap: 10px; }
        .search-form input[type="text"] { flex-grow: 1; padding: 8px; border: 1px solid #ced4da; border-radius: 4px; }
        .btn { padding: 9px 18px; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; color: white; text-decoration: none; display: inline-block; }
        .btn-success { background: #28a745; }
        .btn-primary { background: #007bff; }
        .btn-danger { background: #dc3545; }
        .alert-success { background: #d4edda; color: #155724; padding: 12px; border-radius: 4px; margin-bottom: 15px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 12px; border: 1px solid #ddd; text-align: left; }
        th { background-color: #f8f9fa; }
    </style>
</head>
<body>

    <!-- Header navigation bar -->
    <div class="header">
        <h2>Client Portal</h2>
        <div class="nav-links">
            Welcome, <strong><?php echo htmlspecialchars($user_name, ENT_QUOTES, 'UTF-8'); ?></strong> |
            <a href="../week2/calendar.php">View Calendar</a> |
            <a href="logout.php" style="color: #ffc107;">Logout</a>
        </div>
    </div>

    <div class="card">
        <!-- Message Confirmation Banners -->
        <?php if ($msg === 'booked'): ?>
            <div class="alert-success">Your service request and appointment have been successfully submitted!</div>
        <?php elseif ($msg === 'cancelled'): ?>
            <div class="alert-success">Your appointment has been successfully cancelled.</div>
        <?php endif; ?>

        <!-- Quick Action & Booking Button -->
        <div class="actions-bar">
            <h3 style="margin: 0;">My Scheduled Appointments</h3>
            <a href="../week1/booking.php" class="btn btn-success">+ Schedule New Appointment</a>
        </div>

        <!-- Integrated Search Engine Link -->
        <div class="search-container">
            <strong>Quick Search Appointments:</strong>
            <form action="../week2/search.php" method="GET" class="search-form" style="margin-top: 10px;">
                <input type="text" name="query" placeholder="Search by service or status..." required>
                <button type="submit" class="btn btn-primary">Search</button>
            </form>
        </div>

        <!-- Appointments Table -->
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Service Requested</th>
                    <th>Date & Time</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($my_appointments)): ?>
                    <?php foreach ($my_appointments as $app): ?>
                    <tr>
                        <td><?php echo htmlspecialchars((string)$app['appointment_id'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars((string)$app['service_type'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars((string)$app['appointment_date'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><strong><?php echo htmlspecialchars((string)$app['status'], ENT_QUOTES, 'UTF-8'); ?></strong></td>
                        <td>
                            <form action="../week2/schedule.php" method="POST" style="display:inline;">
                                <input type="hidden" name="action" value="cancel">
                                <input type="hidden" name="appointment_id" value="<?php echo htmlspecialchars((string)$app['appointment_id'], ENT_QUOTES, 'UTF-8'); ?>">
                                <button type="submit" class="btn btn-danger" onclick="return confirm('Cancel this appointment?');">Cancel</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align: center; color: #6c757d; padding: 25px;">
                            No active appointments found.<br><br>
                            <a href="../week1/booking.php" class="btn btn-primary">Book Your First Appointment</a>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</body>
</html>