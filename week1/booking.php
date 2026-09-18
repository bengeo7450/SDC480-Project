<?php
session_start();
require_once '../db.php';

// Auth Guard: User must be logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $service = $_POST['service_type'] ?? '';
    $date    = $_POST['appointment_date'] ?? '';
    $time    = $_POST['appointment_time'] ?? '';
    $notes   = trim($_POST['notes'] ?? '');

    $allowedServices = ['Vehicle Turn-in', 'Parts Pickup', 'ID Cards'];

    if (!in_array($service, $allowedServices) || !$date || !$time) {
        $error = 'Please select a valid service, date, and time.';
    } else {
        $stmt = $pdo->prepare('INSERT INTO appointments (user_id, service_type, appointment_date, appointment_time, notes) VALUES (?, ?, ?, ?, ?)');
        if ($stmt->execute([$_SESSION['user_id'], $service, $date, $time, $notes])) {
            $message = 'Appointment scheduled successfully!';
        } else {
            $error = 'Failed to submit appointment. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Schedule Appointment</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f4f4f9; }
        .container { max-width: 500px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group select, .form-group input, .form-group textarea { width: 100%; padding: 8px; box-sizing: border-box; }
        button { background: #007bff; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; }
        .alert { padding: 10px; margin-bottom: 15px; border-radius: 4px; }
        .danger { background: #f8d7da; color: #721c24; }
        .success { background: #d4edda; color: #155724; }
        .nav { margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="nav"><a href="dashboard.php">&larr; Back to Dashboard</a></div>
        <h2>Schedule an Appointment</h2>
        
        <?php if ($error): ?><div class="alert danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <?php if ($message): ?><div class="alert success"><?= htmlspecialchars($message) ?></div><?php endif; ?>

        <form method="POST" action="booking.php">
            <div class="form-group">
                <label>Select Service</label>
                <select name="service_type" required>
                    <option value="">-- Choose a Service --</option>
                    <option value="Vehicle Turn-in">Vehicle Turn-in</option>
                    <option value="Parts Pickup">Parts Pickup</option>
                    <option value="ID Cards">ID Cards</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Appointment Date</label>
                <input type="date" name="appointment_date" min="<?= date('Y-m-d') ?>" required>
            </div>
            
            <div class="form-group">
                <label>Appointment Time</label>
                <input type="time" name="appointment_time" required>
            </div>
            
            <div class="form-group">
                <label>Additional Notes (Optional)</label>
                <textarea name="notes" rows="3"></textarea>
            </div>
            
            <button type="submit">Confirm Booking</button>
        </form>
    </div>
</body>
</html>