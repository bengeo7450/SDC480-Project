<?php
/**
 * File: admin_dashboard.php
 * Module: Admin Portal
 * Author: Ben George
 * Description: Admin dashboard for searching, rescheduling, and cancelling appointments.
 */

session_start();

// Connect to the database using path relative to this folder
require_once dirname(__DIR__) . '/db.php';

// Check if the user is logged in and has an admin role
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    // Kick non-admins back to the login page
    header("Location: ../week1/login.php?error=unauthorized");
    exit();
}

// Grab the search term from the URL if the user submitted the search form
$searchTerm = trim($_GET['search'] ?? '');

if (!empty($searchTerm)) {
    // If there is a search term, run a query with SQL wildcards (%)
    // to search client names, service types, dates, or status fields
    $sql = "SELECT 
                a.id AS appointment_id,
                a.user_id,
                u.full_name AS client_name,
                a.service_type,
                a.appointment_date,
                a.status
            FROM appointments a
            LEFT JOIN users u ON a.user_id = u.id
            WHERE u.full_name LIKE :search 
               OR a.service_type LIKE :search 
               OR a.appointment_date LIKE :search
               OR a.status LIKE :search
            ORDER BY a.appointment_date DESC";
    
    $stmt = $pdo->prepare($sql);
    // Wrap search term in % so it matches partial text anywhere in the string
    $stmt->execute([':search' => '%' . $searchTerm . '%']);
} else {
    // If no search was entered, just grab all appointments in order of date
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
}

// Save all query results into an array
$all_appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get the admin's name from session, fallback to 'Admin'
$adminName = $_SESSION['user_name'] ?? $_SESSION['username'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CAMS Portal - Admin Control Center</title>
    
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f6f9; margin: 0; padding: 20px; }
        .header { display: flex; justify-content: space-between; align-items: center; background: #343a40; color: #fff; padding: 15px 20px; border-radius: 6px; }
        .card { background: #fff; padding: 20px; border-radius: 8px; margin-top: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        
        /* Search Box Styles */
        .search-container { background: #e9ecef; padding: 15px; border-radius: 6px; margin-bottom: 20px; }
        .search-instructions { font-size: 0.85em; color: #495057; margin-top: 5px; }
        .search-form { display: flex; gap: 10px; margin-top: 10px; }
        .search-form input[type="text"] { flex-grow: 1; padding: 8px; border: 1px solid #ced4da; border-radius: 4px; }
        .btn-search { background: #007bff; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; font-weight: bold; }
        .btn-reset { background: #6c757d; color: white; text-decoration: none; padding: 8px 16px; border-radius: 4px; font-weight: bold; line-height: 20px; }

        /* Table Styles */
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; vertical-align: middle; }
        th { background-color: #007bff; color: white; }
        .btn-danger { background: #dc3545; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; }
        .btn-warning { background: #ffc107; color: #212529; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-weight: bold; }
        .action-cell { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
        .reschedule-form { display: flex; gap: 5px; align-items: center; }
        .reschedule-form input[type="datetime-local"] { padding: 5px; border: 1px solid #ccc; border-radius: 4px; }
    </style>
</head>
<body>

    <!-- Header with logged-in user name and logout link -->
    <div class="header">
        <h2>CAMS Admin Control Center</h2>
        <div>
            <span>Welcome, <strong><?php echo htmlspecialchars($adminName, ENT_QUOTES, 'UTF-8'); ?></strong> (Admin)</span> |
            <a href="../week1/logout.php" style="color: #ffc107; text-decoration: none;">Logout</a>
        </div>
    </div>

    <div class="card">
        <h3>Master Appointment Management</h3>

        <!-- Search Bar -->
        <div class="search-container">
            <strong>Search Records:</strong>
            <div class="search-instructions">
                Enter a keyword to filter appointments. Search automatically supports partial and wildcard matching across Client Names, Service Types, Dates, and Statuses.
            </div>
            
            <!-- Uses GET request to pass search parameter in the URL back to this page -->
            <form action="admin_dashboard.php" method="GET" class="search-form">
                <input type="text" name="search" placeholder="Search by name, service, date (YYYY-MM-DD), or status..." value="<?php echo htmlspecialchars($searchTerm, ENT_QUOTES, 'UTF-8'); ?>">
                <button type="submit" class="btn-search">Search</button>
                
                <!-- Only show the Clear Search button if a search is currently active -->
                <?php if (!empty($searchTerm)): ?>
                    <a href="admin_dashboard.php" class="btn-reset">Clear Search</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Appointment Data Table -->
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
                <!-- Check if the database query returned any rows -->
                <?php if (!empty($all_appointments)): ?>
                    <?php foreach ($all_appointments as $app): 
                        // Set variables safely using null coalescing operator
                        $appId      = $app['appointment_id'] ?? $app['id'] ?? 'N/A';
                        $clientName = $app['client_name'] ?? $app['full_name'] ?? 'Guest / Unknown';
                        $service    = $app['service_type'] ?? $app['service'] ?? 'General Service';
                        $date       = $app['appointment_date'] ?? 'N/A';
                        $status     = $app['status'] ?? 'Scheduled';
                        
                        // Format date string so datetime-local HTML input can display it properly
                        $formattedDate = ($date !== 'N/A') ? date('Y-m-d\TH:i', strtotime($date)) : '';
                    ?>
                    <tr>
                        <!-- Output sanitized table cells to prevent XSS -->
                        <td><?php echo htmlspecialchars((string)$appId, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars((string)$clientName, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars((string)$service, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars((string)$date, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><strong><?php echo htmlspecialchars((string)$status, ENT_QUOTES, 'UTF-8'); ?></strong></td>
                        
                        <td>
                            <div class="action-cell">
                                <!-- Reschedule Form: Sends new date/time to schedule.php -->
                                <form action="schedule.php" method="POST" class="reschedule-form">
                                    <input type="hidden" name="action" value="reschedule">
                                    <input type="hidden" name="appointment_id" value="<?php echo htmlspecialchars((string)$appId, ENT_QUOTES, 'UTF-8'); ?>">
                                    <input type="datetime-local" name="new_date" value="<?php echo $formattedDate; ?>" required>
                                    <button type="submit" class="btn-warning">Reschedule</button>
                                </form>

                                <!-- Cancel Form: Deletes appointment row via schedule.php -->
                                <form action="schedule.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="action" value="cancel">
                                    <input type="hidden" name="appointment_id" value="<?php echo htmlspecialchars((string)$appId, ENT_QUOTES, 'UTF-8'); ?>">
                                    <button type="submit" class="btn-danger" onclick="return confirm('Are you sure you want to cancel and delete this appointment?');">Cancel</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Message displayed if no database results match search -->
                    <tr>
                        <td colspan="6" style="text-align: center; color: #6c757d;">No matching records found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</body>
</html>