<?php
/**
 * File: search.php
 * Module: Interactive Search & Inline CRUD Operations
 * Author: Ben George
 */

session_start();
require_once dirname(__DIR__) . '/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../week1/login.php");
    exit();
}

$query = trim($_GET['query'] ?? '');
$results = [];
$msg = $_GET['msg'] ?? '';

// Handle Inline Deletion directly from Search Page
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $appt_id = intval($_POST['appointment_id']);
    $stmt_del = $pdo->prepare("DELETE FROM appointments WHERE id = :id AND user_id = :user_id");
    $stmt_del->execute([':id' => $appt_id, ':user_id' => $_SESSION['user_id']]);
    header("Location: search.php?query=" . urlencode($query) . "&msg=deleted");
    exit();
}

// Perform Search with unique named parameters to avoid SQLSTATE[HY093]
if ($query !== '') {
    $searchTerm = "%" . $query . "%";
    $stmt = $pdo->prepare("
        SELECT id, service_type, appointment_date, status 
        FROM appointments 
        WHERE user_id = :user_id 
        AND (service_type LIKE :term1 OR status LIKE :term2)
        ORDER BY appointment_date DESC
    ");
    $stmt->execute([
        ':user_id' => $_SESSION['user_id'],
        ':term1'   => $searchTerm,
        ':term2'   => $searchTerm
    ]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Search Appointments - CAMS Portal</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f6f9; margin: 0; padding: 20px; }
        .card { background: #fff; padding: 25px; border-radius: 8px; max-width: 900px; margin: 0 auto; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .btn { padding: 6px 12px; border: none; border-radius: 4px; font-weight: bold; color: white; text-decoration: none; cursor: pointer; }
        .btn-primary { background: #007bff; }
        .btn-warning { background: #ffc107; color: #000; }
        .btn-danger { background: #dc3545; }
        .btn-secondary { background: #6c757d; }
        .alert { background: #d4edda; color: #155724; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
    </style>
</head>
<body>

<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2>Search Appointments</h2>
        <a href="../week1/dashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
    </div>

    <?php if ($msg === 'deleted'): ?>
        <div class="alert">Appointment deleted successfully from search results.</div>
    <?php endif; ?>

    <form method="GET" action="search.php" style="display: flex; gap: 10px; margin-bottom: 20px;">
        <input type="text" name="query" value="<?php echo htmlspecialchars($query, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Search by service or status..." style="flex-grow: 1; padding: 10px; border: 1px solid #ccc; border-radius: 4px;" required>
        <button type="submit" class="btn btn-primary">Search</button>
    </form>

    <?php if ($query !== ''): ?>
        <h3>Search Results for "<?php echo htmlspecialchars($query, ENT_QUOTES, 'UTF-8'); ?>"</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Service Requested</th>
                    <th>Date & Time</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($results)): ?>
                    <?php foreach ($results as $row): ?>
                    <tr>
                        <td><?php echo htmlspecialchars((string)$row['id'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars((string)$row['service_type'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars((string)$row['appointment_date'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><strong><?php echo htmlspecialchars((string)$row['status'], ENT_QUOTES, 'UTF-8'); ?></strong></td>
                        <td>
                            <!-- Inline Edit Action -->
                            <a href="../week1/booking.php?edit_id=<?php echo htmlspecialchars((string)$row['id'], ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-warning">Edit</a>
                            
                            <!-- Inline Delete Action -->
                            <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this record permanently?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="appointment_id" value="<?php echo htmlspecialchars((string)$row['id'], ENT_QUOTES, 'UTF-8'); ?>">
                                <button type="submit" class="btn btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align: center; color: #6c757d;">No matching appointments found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

</body>
</html>