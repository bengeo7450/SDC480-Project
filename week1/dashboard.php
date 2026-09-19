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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Dashboard - CAMS Portal</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f6f9; margin: 0; padding: 20px; }
        .header { display: flex; justify-content: space-between; align-items: center; background: #007bff; color: #fff; padding: 15px 20px; border-radius: 6px; }
        .card { background: #fff; padding: 20px; border-radius: 8px; margin-top: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .search-container { background: #e9ecef; padding: 15px; border-radius: 6px; margin-bottom: 20px; }
        .search-form { display: flex; gap: 10px; }
        .search-form input[type="text"] { flex-grow: 1; padding: 8px; border: 1px solid #ced4da; border-radius: 4px; }
        .btn { padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; color: white; text-decoration: none; }
        .btn-primary { background: #007bff; }
        .btn-danger { background: #dc3545; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
        th { background-color: #f8f9fa; }
    </style>
</head>
<body>

    <!-- Header bar with username and logout link -->
    <div class="header">
        <h2>Client Portal</h2>
        <div>
            Welcome, <strong><?php echo htmlspecialchars($user_name, ENT_QUOTES, 'UTF-8'); ?></strong> |
            <a href="logout.php" style="color: #ffc107; text-decoration: none; font-weight: bold;">Logout</a>
        </div>
    </div>

    <div class="card">
        <!-- Integrated Search redirecting to week2 search engine -->
        <div class="search-container">
            <strong>Quick Search Appointments:</strong>
            <form action="../week2/search.php" method="GET" class="search-form" style="margin-top: 10px;">
                <input type="text" name="query" placeholder="Search by service or status..." required>
                <button type="submit" class="btn btn-primary">Search</button>
            </form>
        </div>

        <h3>My Scheduled Appointments</h3>
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
                            <!-- Cancel action posting to schedule.php engine -->
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
                        <td colspan="5" style="text-align: center; color: #6c757d;">No appointments found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</body>
</html>