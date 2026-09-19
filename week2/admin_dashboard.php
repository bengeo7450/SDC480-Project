<?php
/**
 * File: admin_dashboard.php
 * Module: Administrative Portal & Client Intake Inspector
 * Author: Ben George
 */

session_start();

// Connect to database looking up one directory
require_once dirname(__DIR__) . '/db.php';

// Protect page: restricted to admin users only
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: ../week1/login.php?error=unauthorized");
    exit();
}

// Fetch all appointments joined with user details
$stmt = $pdo->prepare("
    SELECT 
        a.id AS appointment_id,
        a.service_type,
        a.appointment_date,
        a.status,
        a.client_phone,
        a.intake_notes,
        u.email AS client_email
    FROM appointments a
    LEFT JOIN users u ON a.user_id = u.id
    ORDER BY a.appointment_date DESC
");
$stmt->execute();
$all_appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - CAMS Portal</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f6f9; margin: 0; padding: 20px; }
        .header { display: flex; justify-content: space-between; align-items: center; background: #343a40; color: #fff; padding: 15px 20px; border-radius: 6px; }
        .nav-links a { color: #fff; text-decoration: none; margin-left: 15px; font-weight: bold; }
        .card { background: #fff; padding: 25px; border-radius: 8px; margin-top: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 12px; border: 1px solid #ddd; text-align: left; }
        th { background-color: #f8f9fa; }
        .clickable-row { cursor: pointer; }
        .clickable-row:hover { background-color: #f1f3f5; }
        .btn { padding: 6px 12px; border: none; border-radius: 4px; font-weight: bold; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn-info { background: #17a2b8; color: white; }
        
        /* Modal Styles */
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); }
        .modal-content { background: #fff; margin: 10% auto; padding: 25px; width: 50%; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.3); position: relative; }
        .close-btn { position: absolute; right: 20px; top: 15px; font-size: 24px; font-weight: bold; cursor: pointer; color: #aaa; }
        .close-btn:hover { color: #000; }
        .detail-group { margin-bottom: 12px; }
        .detail-group label { font-weight: bold; color: #495057; display: block; }
        .notes-box { background: #f8f9fa; padding: 12px; border: 1px solid #e9ecef; border-radius: 4px; max-height: 150px; overflow-y: auto; }
    </style>
</head>
<body>

    <div class="header">
        <h2>Admin Management Portal</h2>
        <div class="nav-links">
            Welcome, <strong>Admin</strong> |
            <a href="calendar.php">View Calendar</a> |
            <a href="../week1/logout.php" style="color: #ffc107;">Logout</a>
        </div>
    </div>

    <div class="card">
        <h3>Master Appointment Schedule</h3>
        <p>Click on any row or the <strong>View Details</strong> button to inspect client intake details.</p>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Client Email</th>
                    <th>Service Requested</th>
                    <th>Date & Time</th>
                    <th>Status</th>
                    <th>Intake Inspection</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($all_appointments)): ?>
                    <?php foreach ($all_appointments as $app): ?>
                    <tr class="clickable-row" onclick="openIntakeModal(<?php echo htmlspecialchars(json_encode($app), ENT_QUOTES, 'UTF-8'); ?>)">
                        <td><?php echo htmlspecialchars((string)$app['appointment_id'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars((string)($app['client_email'] ?? 'Guest / Unlinked'), ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars((string)$app['service_type'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars((string)$app['appointment_date'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><strong><?php echo htmlspecialchars((string)$app['status'], ENT_QUOTES, 'UTF-8'); ?></strong></td>
                        <td>
                            <button type="button" class="btn btn-info" onclick="event.stopPropagation(); openIntakeModal(<?php echo htmlspecialchars(json_encode($app), ENT_QUOTES, 'UTF-8'); ?>)">
                                View Details
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center; color: #6c757d; padding: 20px;">No appointments found in database.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Intake Inspection Modal -->
    <div id="intakeModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeIntakeModal()">&times;</span>
            <h3 style="margin-top: 0;">Client Intake & Service Details</h3>
            <hr><br>
            
            <div class="detail-group">
                <label>Contact Email:</label>
                <span id="modalEmail"></span>
            </div>

            <div class="detail-group">
                <label>Contact Phone:</label>
                <span id="modalPhone"></span>
            </div>

            <div class="detail-group">
                <label>Service Type:</label>
                <span id="modalService"></span>
            </div>

            <div class="detail-group">
                <label>Scheduled Date & Time:</label>
                <span id="modalDate"></span>
            </div>

            <div class="detail-group">
                <label>Intake Notes / Description:</label>
                <div id="modalNotes" class="notes-box"></div>
            </div>
        </div>
    </div>

    <script>
        function openIntakeModal(data) {
            document.getElementById('modalEmail').textContent   = data.client_email || 'Not Provided';
            document.getElementById('modalPhone').textContent   = data.client_phone || 'Not Provided';
            document.getElementById('modalService').textContent = data.service_type || 'N/A';
            document.getElementById('modalDate').textContent    = data.appointment_date || 'N/A';
            document.getElementById('modalNotes').textContent   = data.intake_notes || 'No specific notes recorded for this intake.';
            
            document.getElementById('intakeModal').style.display = 'block';
        }

        function closeIntakeModal() {
            document.getElementById('intakeModal').style.display = 'none';
        }

        window.onclick = function(event) {
            var modal = document.getElementById('intakeModal');
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    </script>

</body>
</html>