<?php
session_start();
// 1. Load database connection
require_once '../db.php';

// 2. Auth Guard
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// 3. Load global navigation header
require_once '../includes/header.php';

$userId   = $_SESSION['user_id'];
$userName = $_SESSION['user_name'];
$userRole = $_SESSION['role'];

// Fetch scheduled appointments (Admins see all; Clients see only their own)
if ($userRole === 'admin') {
    $stmt = $pdo->prepare('SELECT a.*, u.full_name, u.email FROM appointments a JOIN users u ON a.user_id = u.id ORDER BY appointment_date ASC, appointment_time ASC');
    $stmt->execute();
} else {
    $stmt = $pdo->prepare('SELECT * FROM appointments WHERE user_id = ? ORDER BY appointment_date ASC, appointment_time ASC');
    $stmt->execute([$userId]);
}

$appointments = $stmt->fetchAll();

// Mock Unit Performance Metrics
$metrics = [
    'total_appointments' => count($appointments),
    'completion_rate'    => '94%',
    'avg_wait_time'      => '12 mins'
];
?>

<style>
    .dashboard-header { display: flex; justify-content: space-between; align-items: center; background: #343a40; color: white; padding: 15px 20px; border-radius: 6px; margin-bottom: 20px; }
    .dashboard-header h2 { margin: 0 0 5px 0; }
    .dashboard-header p { margin: 0; color: #ccc; }
    .metrics-grid { display: flex; gap: 20px; margin-bottom: 30px; }
    .card { background: white; padding: 20px; border-radius: 8px; flex: 1; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
    .card h3 { margin-top: 0; font-size: 14px; color: #6c757d; text-transform: uppercase; }
    .card .metric { font-size: 28px; font-weight: bold; color: #333; }
    table { width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
    th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #e9ecef; }
    th { background: #e9ecef; }
    .btn-booking { background: #007bff; color: white; padding: 8px 12px; text-decoration: none; border-radius: 4px; font-weight: bold; }
</style>

<div class="dashboard-header">
    <div>
        <h2>Portal Dashboard</h2>
        <p>Welcome, <?= htmlspecialchars($userName) ?> (<?= ucfirst(htmlspecialchars($userRole)) ?>)</p>
    </div>
    <div>
        <a href="booking.php" class="btn-booking">+ New Booking</a>
    </div>
</div>

<!-- Unit Performance Metrics Placeholders -->
<div class="metrics-grid">
    <div class="card">
        <h3>Total Bookings</h3>
        <div class="metric"><?= $metrics['total_appointments'] ?></div>
    </div>
    <div class="card">
        <h3>Unit Completion Rate</h3>
        <div class="metric"><?= $metrics['completion_rate'] ?></div>
    </div>
    <div class="card">
        <h3>Avg Processing Time</h3>
        <div class="metric"><?= $metrics['avg_wait_time'] ?></div>
    </div>
</div>

<!-- Scheduled Appointments Table -->
<h3>Scheduled Appointments</h3>
<?php if (empty($appointments)): ?>
    <p>No appointments scheduled yet.</p>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <?php if ($userRole === 'admin'): ?><th>Client Name</th><?php endif; ?>
                <th>Service Type</th>
                <th>Date</th>
                <th>Time</th>
                <th>Notes</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($appointments as $app): ?>
                <tr>
                    <?php if ($userRole === 'admin'): ?>
                        <td><?= htmlspecialchars($app['full_name']) ?> (<?= htmlspecialchars($app['email']) ?>)</td>
                    <?php endif; ?>
                    <td><?= htmlspecialchars($app['service_type']) ?></td>
                    <td><?= htmlspecialchars($app['appointment_date']) ?></td>
                    <td><?= htmlspecialchars($app['appointment_time']) ?></td>
                    <td><?= htmlspecialchars($app['notes'] ?: 'N/A') ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php
// 4. Load global footer
require_once '../includes/footer.php';
?>