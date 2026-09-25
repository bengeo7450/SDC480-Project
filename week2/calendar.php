<?php
/**
 * File: calendar.php
 * Path: ProjectFiles/week2/calendar.php
 * Module: Monthly Visual Grid Calendar with Real-Time Search Bar
 * Author: Ben George
 */

session_start();
require_once dirname(__DIR__) . '/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../week1/login.php");
    exit();
}

// Navigation for Month/Year
$month = isset($_GET['month']) ? intval($_GET['month']) : intval(date('m'));
$year  = isset($_GET['year']) ? intval($_GET['year']) : intval(date('Y'));

if ($month < 1) { $month = 12; $year--; }
if ($month > 12) { $month = 1; $year++; }

$first_day_timestamp = strtotime("$year-$month-01");
$days_in_month = date('t', $first_day_timestamp);
$day_of_week = date('w', $first_day_timestamp); // 0 (Sun) to 6 (Sat)
$month_name = date('F Y', $first_day_timestamp);

// Prev / Next month targets
$prev_month = $month - 1;
$prev_year = $year;
if ($prev_month < 1) { $prev_month = 12; $prev_year--; }

$next_month = $month + 1;
$next_year = $year;
if ($next_month > 12) { $next_month = 1; $next_year++; }

// Search Filter Handling
$search_query = trim($_GET['search'] ?? '');

$start_date = "$year-" . str_pad((string)$month, 2, '0', STR_PAD_LEFT) . "-01 00:00:00";
$end_date   = "$year-" . str_pad((string)$month, 2, '0', STR_PAD_LEFT) . "-$days_in_month 23:59:59";

// Build SQL Query & Unique Parameters
$params = [
    ':start' => $start_date,
    ':end'   => $end_date
];

if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    $sql = "SELECT id, service_type, appointment_date, status FROM appointments WHERE appointment_date BETWEEN :start AND :end";
    if ($search_query !== '') {
        $sql .= " AND (service_type LIKE :search1 OR status LIKE :search2)";
        $params[':search1'] = '%' . $search_query . '%';
        $params[':search2'] = '%' . $search_query . '%';
    }
} else {
    $sql = "SELECT id, service_type, appointment_date, status FROM appointments WHERE user_id = :user_id AND (appointment_date BETWEEN :start AND :end)";
    $params[':user_id'] = $_SESSION['user_id'];
    if ($search_query !== '') {
        $sql .= " AND (service_type LIKE :search1 OR status LIKE :search2)";
        $params[':search1'] = '%' . $search_query . '%';
        $params[':search2'] = '%' . $search_query . '%';
    }
}

$sql .= " ORDER BY appointment_date ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$raw_appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Map appointments by Day number
$appointments_by_day = [];
foreach ($raw_appointments as $appt) {
    $day_num = intval(date('j', strtotime($appt['appointment_date'])));
    $appointments_by_day[$day_num][] = $appt;
}

$back_url = (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') ? 'admin_dashboard.php' : '../week1/dashboard.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Interactive Calendar - CAMS Portal</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f6f9; margin: 0; padding: 20px; }
        .container { max-width: 1000px; margin: 0 auto; background: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .btn { padding: 6px 12px; border: none; border-radius: 4px; font-weight: bold; color: white; text-decoration: none; cursor: pointer; display: inline-block; }
        .btn-secondary { background: #6c757d; }
        .btn-primary { background: #007bff; }
        .btn-nav { background: #007bff; padding: 5px 10px; text-decoration: none; color: white; border-radius: 4px; font-weight: bold; }
        
        .search-container { background: #e9ecef; padding: 15px; border-radius: 6px; margin-bottom: 20px; }
        
        .calendar-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 5px; background: #ddd; padding: 5px; border-radius: 6px; }
        .day-header { background: #343a40; color: #fff; text-align: center; padding: 10px; font-weight: bold; }
        .day-cell { background: #fff; min-height: 100px; padding: 5px; border-radius: 4px; vertical-align: top; box-sizing: border-box; }
        .day-cell.empty { background: #f8f9fa; }
        .day-number { font-weight: bold; margin-bottom: 5px; color: #495057; font-size: 0.9em; }
        
        .event-badge {
            display: block;
            background-color: #007bff;
            color: #fff;
            padding: 4px 6px;
            margin-bottom: 4px;
            border-radius: 3px;
            font-size: 0.75em;
            text-decoration: none;
            line-height: 1.2;
            word-wrap: break-word;
        }
        .event-badge:hover { background-color: #0056b3; }
        .status-tag { display: block; font-size: 0.85em; opacity: 0.8; }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h2>Interactive Calendar</h2>
        <a href="<?php echo $back_url; ?>" class="btn btn-secondary">← Back to Dashboard</a>
    </div>

    <!-- Calendar Search Bar -->
    <div class="search-container">
        <form method="GET" action="calendar.php" style="display: flex; gap: 10px;">
            <input type="hidden" name="month" value="<?php echo $month; ?>">
            <input type="hidden" name="year" value="<?php echo $year; ?>">
            <input type="text" name="search" value="<?php echo htmlspecialchars($search_query, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Search calendar by service or status..." style="flex-grow: 1; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
            <button type="submit" class="btn btn-primary">Filter Calendar</button>
            <?php if ($search_query !== ''): ?>
                <a href="calendar.php?month=<?php echo $month; ?>&year=<?php echo $year; ?>" class="btn btn-secondary">Clear Filter</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Calendar Month Navigation Controls -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
        <a href="calendar.php?month=<?php echo $prev_month; ?>&year=<?php echo $prev_year; ?><?php echo $search_query !== '' ? '&search=' . urlencode($search_query) : ''; ?>" class="btn-nav">← Prev Month</a>
        <h3 style="margin: 0;"><?php echo $month_name; ?></h3>
        <a href="calendar.php?month=<?php echo $next_month; ?>&year=<?php echo $next_year; ?><?php echo $search_query !== '' ? '&search=' . urlencode($search_query) : ''; ?>" class="btn-nav">Next Month →</a>
    </div>

    <div class="calendar-grid">
        <!-- Day Names -->
        <div class="day-header">Sun</div>
        <div class="day-header">Mon</div>
        <div class="day-header">Tue</div>
        <div class="day-header">Wed</div>
        <div class="day-header">Thu</div>
        <div class="day-header">Fri</div>
        <div class="day-header">Sat</div>

        <!-- Blank cells before start of month -->
        <?php for ($i = 0; $i < $day_of_week; $i++): ?>
            <div class="day-cell empty"></div>
        <?php endfor; ?>

        <!-- Month Days -->
        <?php for ($day = 1; $day <= $days_in_month; $day++): ?>
            <div class="day-cell">
                <div class="day-number"><?php echo $day; ?></div>
                
                <?php if (isset($appointments_by_day[$day])): ?>
                    <?php foreach ($appointments_by_day[$day] as $event): ?>
                        <a href="../week1/booking.php?edit_id=<?php echo htmlspecialchars((string)$event['id'], ENT_QUOTES, 'UTF-8'); ?>" class="event-badge" title="Click to edit">
                            <strong><?php echo date('g:i A', strtotime($event['appointment_date'])); ?></strong><br>
                            <?php echo htmlspecialchars((string)$event['service_type'], ENT_QUOTES, 'UTF-8'); ?>
                            <span class="status-tag">(<?php echo htmlspecialchars((string)$event['status'], ENT_QUOTES, 'UTF-8'); ?>)</span>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        <?php endfor; ?>
    </div>
</div>

</body>
</html>