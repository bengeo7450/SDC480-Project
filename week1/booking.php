<?php
/**
 * File: booking.php
 * Module: Combined Client Intake & Appointment Booking
 * Author: Ben George
 */

session_start();

// Connect to database looking up one directory
require_once dirname(__DIR__) . '/db.php';

// Protect page: require user to be logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../week1/login.php?error=unauthorized");
    exit();
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. XSS Input Sanitization for free-text fields
    $client_name    = htmlspecialchars(trim($_POST['client_name'] ?? ''), ENT_QUOTES, 'UTF-8');
    $client_phone   = htmlspecialchars(trim($_POST['client_phone'] ?? ''), ENT_QUOTES, 'UTF-8');
    $service_type   = htmlspecialchars(trim($_POST['service_type'] ?? ''), ENT_QUOTES, 'UTF-8');
    $intake_notes   = htmlspecialchars(trim($_POST['intake_notes'] ?? ''), ENT_QUOTES, 'UTF-8');
    $appointment_dt = trim($_POST['appointment_date'] ?? '');
    
    $user_id = $_SESSION['user_id'];

    // Basic Validation
    if (empty($client_name) || empty($service_type) || empty($appointment_dt)) {
        $error = "Please complete all required fields.";
    } else {
        try {
            // 2. PDO Parameterized Query (SQL Injection Prevention)
            $stmt = $pdo->prepare("
                INSERT INTO appointments (user_id, service_type, appointment_date, status, client_phone, intake_notes) 
                VALUES (:user_id, :service_type, :appointment_date, 'Pending', :client_phone, :intake_notes)
            ");
            
            $stmt->execute([
                ':user_id'          => $user_id,
                ':service_type'     => $service_type,
                ':appointment_date' => $appointment_dt,
                ':client_phone'      => $client_phone,
                ':intake_notes'      => $intake_notes
            ]);

            // Redirect back to dashboard upon successful booking
            header("Location: ../week1/dashboard.php?msg=booked");
            exit();

        } catch (PDOException $e) {
            $error = "Database Error: Could not log your booking request.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Schedule Service - CAMS Portal</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f6f9; margin: 20px; }
        .card { max-width: 600px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 15px; }
        label { display: block; font-weight: bold; margin-bottom: 5px; }
        input[type="text"], input[type="datetime-local"], select, textarea { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .btn { padding: 10px 20px; border: none; border-radius: 4px; font-weight: bold; color: white; cursor: pointer; }
        .btn-success { background: #28a745; }
        .btn-secondary { background: #6c757d; text-decoration: none; display: inline-block; }
        .error { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
    </style>
</head>
<body>

    <div class="card">
        <h2>Schedule Appointment & Service Request</h2>
        <p>Please fill out your contact details and preferred appointment slot below.</p>

        <?php if (!empty($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <form action="booking.php" method="POST">
            <div class="form-group">
                <label for="client_name">Full Name *</label>
                <input type="text" id="client_name" name="client_name" value="<?php echo htmlspecialchars($_SESSION['user_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>

            <div class="form-group">
                <label for="client_phone">Phone Number</label>
                <input type="text" id="client_phone" name="client_phone" placeholder="(555) 555-5555">
            </div>

            <div class="form-group">
                <label for="service_type">Service Requested *</label>
                <select id="service_type" name="service_type" required>
                    <option value="">-- Select a Service --</option>
                    <option value="Consultation">General Consultation</option>
                    <option value="Maintenance">Scheduled Maintenance</option>
                    <option value="Inspection">System Inspection</option>
                    <option value="Emergency Repair">Emergency Repair</option>
                </select>
            </div>

            <div class="form-group">
                <label for="appointment_date">Preferred Date & Time *</label>
                <input type="datetime-local" id="appointment_date" name="appointment_date" required>
            </div>

            <div class="form-group">
                <label for="intake_notes">Additional Details / Notes for Admin</label>
                <textarea id="intake_notes" name="intake_notes" rows="4" placeholder="Describe your request or special requirements..."></textarea>
            </div>

            <button type="submit" class="btn btn-success">Submit Request</button>
            <a href="../week1/dashboard.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>

</body>
</html>