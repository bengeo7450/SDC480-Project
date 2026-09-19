<?php
/**
 * File: search.php
 * Module: Global Search Query Engine
 * Author: Ben George
 */

session_start();

// Connect to database (looks up one level from week2 to root ProjectFiles)
require_once dirname(__DIR__) . '/db.php';

$search_term = '';
$results = [];

// Process search request when form is submitted via GET or POST
if (isset($_GET['query']) || isset($_POST['query'])) {
    // Sanitize search query input to prevent XSS
    $raw_input = $_GET['query'] ?? $_POST['query'] ?? '';
    $search_term = htmlspecialchars(trim($raw_input), ENT_QUOTES, 'UTF-8');

    if (!empty($search_term)) {
        // Use unique parameters (:term1, :term2) to prevent PDO parameter count mismatches
        $stmt = $pdo->prepare("
            SELECT id, service_type AS title, appointment_date AS detail, status 
            FROM appointments 
            WHERE service_type LIKE :term1 OR status LIKE :term2
            ORDER BY appointment_date DESC
        ");
        
        $term_value = '%' . $search_term . '%';
        $stmt->execute([
            ':term1' => $term_value,
            ':term2' => $term_value
        ]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Search - CAMS Portal</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f9; padding: 30px; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .search-box { display: flex; gap: 10px; margin-bottom: 20px; }
        input[type="text"] { flex: 1; padding: 10px; border: 1px solid #ccc; border-radius: 4px; }
        button { padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .result-item { padding: 12px; border-bottom: 1px solid #eee; }
        .result-item:last-child { border-bottom: none; }
        .badge { background: #e2e3e5; padding: 3px 8px; border-radius: 4px; font-size: 0.85em; }
    </style>
</head>
<body>

<div class="container">
    <h2>Search Portal Records</h2>

    <!-- Search Form -->
    <form action="search.php" method="GET" class="search-box">
        <input type="text" name="query" value="<?php echo htmlspecialchars($search_term); ?>" placeholder="Search by service or status..." required>
        <button type="submit">Search</button>
    </form>

    <!-- Search Results Display -->
    <?php if (!empty($search_term)): ?>
        <h3>Results for "<?php echo htmlspecialchars($search_term); ?>"</h3>
        
        <?php if (count($results) > 0): ?>
            <?php foreach ($results as $row): ?>
                <div class="result-item">
                    <strong><?php echo htmlspecialchars($row['title']); ?></strong><br>
                    <small>Date: <?php echo htmlspecialchars($row['detail']); ?></small> 
                    <span class="badge"><?php echo htmlspecialchars($row['status']); ?></span>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="color: #6c757d;">No records found matching your query.</p>
        <?php endif; ?>
    <?php endif; ?>
</div>

</body>
</html>