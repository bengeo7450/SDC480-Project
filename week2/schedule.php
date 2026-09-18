<?php
/**
 * File: schedule.php
 * Module: Appointment Booking & Management Engine
 * Author: Ben George
 */

session_start();

// Connect to the database using an absolute path to db.php
require_once dirname(__DIR__) . '/db.php';

// Process incoming POST requests (Form submissions)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Determine which action the user triggered (create, reschedule, or cancel)
    $action = $_POST['action'] ?? '';

    // ----------------------------------------------------
    // 1. CREATE / SCHEDULE APPOINTMENT
    // ----------------------------------------------------
    if ($action === 'create') {
        $service_type     = trim($_POST['service_type'] ?? '');
        $appointment_date = $_POST['appointment_date'] ?? '';
        $user_id          = $_SESSION['user_id'] ?? null;

        // Insert new appointment record into the database with default 'Scheduled' status
        $stmt = $pdo->prepare("INSERT INTO appointments (user_id, service_type, appointment_date, status) VALUES (:user_id, :service, :date, 'Scheduled')");
        $stmt->execute([
            ':user_id' => $user_id,
            ':service' => $service_type,
            ':date'    => $appointment_date
        ]);
    } 
    // ----------------------------------------------------
    // 2. RESCHEDULE APPOINTMENT
    // ----------------------------------------------------
    elseif ($action === 'reschedule') {
        $appointment_id = intval($_POST['appointment_id'] ?? 0);
        $new_date       = $_POST['new_date'] ?? '';

        // Update existing record with the new date and set status to 'Rescheduled'
        if ($appointment_id > 0 && !empty($new_date)) {
            $stmt = $pdo->prepare("UPDATE appointments SET appointment_date = :date, status = 'Rescheduled' WHERE id = :id");
            $stmt->execute([
                ':date' => $new_date, 
                ':id'   => $appointment_id
            ]);
        }

        // Redirect admins back to the admin dashboard with a confirmation query parameter
        if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
            header("Location: admin_dashboard.php?msg=rescheduled");
            exit();
        }
    } 
    // ----------------------------------------------------
    // 3. CANCEL / DELETE APPOINTMENT
    // ----------------------------------------------------
    elseif ($action === 'cancel') {
        $appointment_id = intval($_POST['appointment_id'] ?? 0);

        // Delete the selected appointment from the database table
        if ($appointment_id > 0) {
            $stmt = $pdo->prepare("DELETE FROM appointments WHERE id = :id");
            $stmt->execute([':id' => $appointment_id]);
        }

        // Redirect admins back to the admin dashboard with a confirmation query parameter
        if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
            header("Location: admin_dashboard.php?msg=deleted");
            exit();
        }
    }
    
    // Standard client redirect back to schedule page after handling request
    header("Location: schedule.php?success=1");
    exit();
}
?>