<?php
/**
 * File: booking.php
 * Path: ProjectFiles/week1/booking.php
 * Module: Vehicle Service Intake Form
 * Author: Ben George
 */

session_start();
require_once dirname(__DIR__) . '/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$error = '';
$edit_mode = false;
$edit_id = null;

// Form field defaults
$service_type       = '';
$appointment_date   = '';
$vehicle_make       = '';
$vehicle_model      = '';
$vehicle_year       = '';
$vehicle_vin        = '';
$mileage            = '';
$contact_phone      = '';
$contact_preference = 'Phone';
$service_summary    = '';
$service_details    = '';

// Pre-fill form if editing an existing appointment
if (isset($_GET['edit_id']) && is_numeric($_GET['edit_id'])) {
    $edit_id = intval($_GET['edit_id']);
    $stmt = $pdo->prepare("SELECT * FROM appointments WHERE id = :id AND user_id = :user_id LIMIT 1");
    $stmt->execute([':id' => $edit_id, ':user_id' => $_SESSION['user_id']]);
    $appt = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($appt) {
        $edit_mode          = true;
        $service_type       = $appt['service_type'] ?? '';
        $appointment_date   = !empty($appt['appointment_date']) ? date('Y-m-d\TH:i', strtotime($appt['appointment_date'])) : '';
        $vehicle_make       = $appt['vehicle_make'] ?? '';
        $vehicle_model      = $appt['vehicle_model'] ?? '';
        $vehicle_year       = $appt['vehicle_year'] ?? '';
        $vehicle_vin        = $appt['vehicle_vin'] ?? '';
        $mileage            = $appt['mileage'] ?? '';
        $contact_phone      = $appt['contact_phone'] ?? '';
        $contact_preference = $appt['contact_preference'] ?? 'Phone';
        $service_summary    = $appt['service_summary'] ?? '';
        $service_details    = $appt['service_details'] ?? '';
    } else {
        $error = "Appointment not found or permission denied.";
    }
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $service_type       = trim($_POST['service_type'] ?? '');
    $appointment_date   = $_POST['appointment_date'] ?? '';
    $vehicle_make       = trim($_POST['vehicle_make'] ?? '');
    $vehicle_model      = trim($_POST['vehicle_model'] ?? '');
    $vehicle_year       = !empty($_POST['vehicle_year']) ? intval($_POST['vehicle_year']) : null;
    $vehicle_vin        = strtoupper(trim($_POST['vehicle_vin'] ?? ''));
    $mileage            = !empty($_POST['mileage']) ? intval($_POST['mileage']) : null;
    $contact_phone      = trim($_POST['contact_phone'] ?? '');
    $contact_preference = $_POST['contact_preference'] ?? 'Phone';
    $service_summary    = trim($_POST['service_summary'] ?? '');
    $service_details    = trim($_POST['service_details'] ?? '');

    if (!empty($service_type) && !empty($appointment_date) && !empty($vehicle_make) && !empty($vehicle_model)) {
        $formatted_date = date('Y-m-d H:i:s', strtotime($appointment_date));

        if (isset($_POST['edit_id']) && !empty($_POST['edit_id'])) {
            $update_id = intval($_POST['edit_id']);
            $sql = "UPDATE appointments SET 
                        service_type = :service, 
                        appointment_date = :app_date,
                        vehicle_make = :make,
                        vehicle_model = :model,
                        vehicle_year = :v_year,
                        vehicle_vin = :vin,
                        mileage = :mileage,
                        contact_phone = :phone,
                        contact_preference = :pref,
                        service_summary = :summary,
                        service_details = :details
                    WHERE id = :id AND user_id = :user_id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':service'  => $service_type,
                ':app_date' => $formatted_date,
                ':make'     => $vehicle_make,
                ':model'    => $vehicle_model,
                ':v_year'   => $vehicle_year,
                ':vin'      => $vehicle_vin,
                ':mileage'  => $mileage,
                ':phone'    => $contact_phone,
                ':pref'     => $contact_preference,
                ':summary'  => $service_summary,
                ':details'  => $service_details,
                ':id'       => $update_id,
                ':user_id'  => $_SESSION['user_id']
            ]);
            $_SESSION['flash_msg'] = "Vehicle service request updated successfully.";
        } else {
            $sql = "INSERT INTO appointments 
                        (user_id, service_type, appointment_date, vehicle_make, vehicle_model, vehicle_year, vehicle_vin, mileage, contact_phone, contact_preference, service_summary, service_details, status) 
                    VALUES 
                        (:user_id, :service, :app_date, :make, :model, :v_year, :vin, :mileage, :phone, :pref, :summary, :details, 'Pending')";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':user_id'  => $_SESSION['user_id'],
                ':service'  => $service_type,
                ':app_date' => $formatted_date,
                ':make'     => $vehicle_make,
                ':model'    => $vehicle_model,
                ':v_year'   => $vehicle_year,
                ':vin'      => $vehicle_vin,
                ':mileage'  => $mileage,
                ':phone'    => $contact_phone,
                ':pref'     => $contact_preference,
                ':summary'  => $service_summary,
                ':details'  => $service_details
            ]);
            $_SESSION['flash_msg'] = "Vehicle service appointment booked successfully.";
        }

        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Please fill in all required fields (Service Type, Date/Time, Vehicle Make & Model).";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $edit_mode ? 'Edit Service Booking' : 'Vehicle Service Intake Form'; ?> - CAMS Portal</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f6f9; display: flex; justify-content: center; align-items: center; min-height: 100vh; padding: 20px 0; margin: 0; }
        .card { background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); width: 100%; max-width: 580px; box-sizing: border-box; }
        h2 { margin-top: 0; margin-bottom: 5px; color: #111; }
        p.subtitle { color: #666; margin-bottom: 20px; font-size: 0.9em; }
        .form-group { margin-bottom: 16px; text-align: left; }
        .form-row { display: flex; gap: 12px; }
        .form-row .form-group { flex: 1; }
        label { display: block; margin-bottom: 6px; font-weight: bold; color: #333; font-size: 0.88rem; }
        label .req { color: #dc3545; }
        input[type="text"], input[type="number"], input[type="tel"], input[type="datetime-local"], select, textarea { width: 100%; padding: 9px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px; font-size: 0.92rem; font-family: inherit; }
        textarea { resize: vertical; min-height: 80px; }
        .btn { width: 100%; padding: 12px; border: none; font-weight: bold; border-radius: 4px; cursor: pointer; display: block; text-align: center; text-decoration: none; box-sizing: border-box; margin-bottom: 10px; }
        .btn-primary { background: #007bff; color: white; }
        .btn-primary:hover { background: #0056b3; }
        .btn-secondary { background: #6c757d; color: white; }
        .alert-danger { background-color: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 15px; font-size: 0.9em; }
    </style>
</head>
<body>

<div class="card">
    <h2><?php echo $edit_mode ? 'Edit Service Request' : 'Vehicle Service Intake Form'; ?></h2>
    <p class="subtitle">Please provide your vehicle details and select the service requested.</p>

    <?php if (!empty($error)): ?>
        <div class="alert-danger"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>

    <form method="POST" action="booking.php">
        <?php if ($edit_mode): ?>
            <input type="hidden" name="edit_id" value="<?php echo htmlspecialchars((string)$edit_id, ENT_QUOTES, 'UTF-8'); ?>">
        <?php endif; ?>

        <!-- Service Category & Schedule -->
        <div class="form-row">
            <div class="form-group">
                <label for="service_type">Service Requested <span class="req">*</span></label>
                <select id="service_type" name="service_type" required>
                    <option value="">-- Select a Service Category --</option>
                    <option value="Oil & Filter Change" <?php echo ($service_type === 'Oil & Filter Change') ? 'selected' : ''; ?>>Oil &amp; Filter Change</option>
                    <option value="Brake Service & Repair" <?php echo ($service_type === 'Brake Service & Repair') ? 'selected' : ''; ?>>Brake Service &amp; Repair</option>
                    <option value="Tire Rotation & Alignment" <?php echo ($service_type === 'Tire Rotation & Alignment') ? 'selected' : ''; ?>>Tire Rotation &amp; Alignment</option>
                    <option value="Engine Diagnostics / Check Engine" <?php echo ($service_type === 'Engine Diagnostics / Check Engine') ? 'selected' : ''; ?>>Engine Diagnostics / Check Engine</option>
                    <option value="Transmission & Drivetrain" <?php echo ($service_type === 'Transmission & Drivetrain') ? 'selected' : ''; ?>>Transmission &amp; Drivetrain</option>
                    <option value="AC & Heating Service" <?php echo ($service_type === 'AC & Heating Service') ? 'selected' : ''; ?>>AC &amp; Heating Service</option>
                    <option value="General Vehicle Inspection" <?php echo ($service_type === 'General Vehicle Inspection') ? 'selected' : ''; ?>>General Vehicle Inspection</option>
                </select>
            </div>
            <div class="form-group">
                <label for="appointment_date">Preferred Date &amp; Time <span class="req">*</span></label>
                <input type="datetime-local" id="appointment_date" name="appointment_date" value="<?php echo htmlspecialchars($appointment_date, ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>
        </div>

        <!-- Vehicle Details -->
        <div class="form-row">
            <div class="form-group">
                <label for="vehicle_year">Year</label>
                <input type="number" id="vehicle_year" name="vehicle_year" placeholder="e.g. 2021" min="1950" max="2027" value="<?php echo htmlspecialchars((string)$vehicle_year, ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div class="form-group">
                <label for="vehicle_make">Make <span class="req">*</span></label>
                <input type="text" id="vehicle_make" name="vehicle_make" placeholder="e.g. Nissan, Toyota" value="<?php echo htmlspecialchars($vehicle_make, ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>
            <div class="form-group">
                <label for="vehicle_model">Model <span class="req">*</span></label>
                <input type="text" id="vehicle_model" name="vehicle_model" placeholder="e.g. Pathfinder, Camry" value="<?php echo htmlspecialchars($vehicle_model, ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="mileage">Current Mileage</label>
                <input type="number" id="mileage" name="mileage" placeholder="e.g. 45000" value="<?php echo htmlspecialchars((string)$mileage, ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div class="form-group">
                <label for="vehicle_vin">VIN (Optional)</label>
                <input type="text" id="vehicle_vin" name="vehicle_vin" placeholder="17-character VIN" maxlength="17" value="<?php echo htmlspecialchars($vehicle_vin, ENT_QUOTES, 'UTF-8'); ?>">
            </div>
        </div>

        <!-- Contact Info -->
        <div class="form-row">
            <div class="form-group">
                <label for="contact_phone">Contact Phone</label>
                <input type="tel" id="contact_phone" name="contact_phone" placeholder="(555) 000-0000" value="<?php echo htmlspecialchars($contact_phone, ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div class="form-group">
                <label for="contact_preference">Preferred Contact Method</label>
                <select id="contact_preference" name="contact_preference">
                    <option value="Phone" <?php echo ($contact_preference === 'Phone') ? 'selected' : ''; ?>>Phone Call</option>
                    <option value="SMS" <?php echo ($contact_preference === 'SMS') ? 'selected' : ''; ?>>SMS Text</option>
                    <option value="Email" <?php echo ($contact_preference === 'Email') ? 'selected' : ''; ?>>Email</option>
                </select>
            </div>
        </div>

        <!-- Intake Summary and Details -->
        <div class="form-group">
            <label for="service_summary">Brief Summary of Request</label>
            <input type="text" id="service_summary" name="service_summary" placeholder="e.g. Squeaking brakes, 30k mile maintenance" value="<?php echo htmlspecialchars($service_summary, ENT_QUOTES, 'UTF-8'); ?>">
        </div>

        <div class="form-group">
            <label for="service_details">Detailed Problem Description / Notes</label>
            <textarea id="service_details" name="service_details" placeholder="Describe symptoms, strange noises, when the issue occurs, or specific requests..."><?php echo htmlspecialchars($service_details, ENT_QUOTES, 'UTF-8'); ?></textarea>
        </div>

        <button type="submit" class="btn btn-primary"><?php echo $edit_mode ? 'Update Booking' : 'Submit Intake & Book'; ?></button>
        <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>

</body>
</html>