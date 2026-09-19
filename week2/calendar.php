<?php
/**
 * File: calendar.php
 * Module: Interactive Appointment Calendar View
 * Author: Ben George
 */

session_start();

// Connect to the database
require_once '../db.php';

// Fetch all active appointments (excluding cancelled ones)
// Alias columns to match FullCalendar's required event keys ('id', 'title', 'start')
$stmt = $pdo->prepare("
    SELECT 
        id, 
        service_type AS title, 
        appointment_date AS start, 
        status 
    FROM appointments 
    WHERE status != 'Cancelled'
");
$stmt->execute();

// Store matching appointments as an associative array
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CAMS Portal - Calendar</title>
    
    <!-- Load FullCalendar 6 CSS and JavaScript library from CDN -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
    
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f4f6f9; }
        .nav-container { max-width: 900px; margin: 0 auto 15px auto; text-align: left; }
        .btn-back { display: inline-block; padding: 8px 16px; background-color: #6c757d; color: white; text-decoration: none; border-radius: 4px; font-weight: bold; }
        .btn-back:hover { background-color: #5a6268; }
        #calendar { max-width: 900px; margin: 0 auto; background: #ffffff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
    </style>
</head>
<body>

    <!-- Back to Dashboard Link -->
    <div class="nav-container">
        <a href="../week1/dashboard.php" class="btn-back">&larr; Back to Dashboard</a>
    </div>

    <!-- Target container element where FullCalendar will render -->
    <div id="calendar"></div>

    <script>
        // Wait until the DOM content is fully loaded before initializing FullCalendar
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            
            // Instantiate FullCalendar with configuration options
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth', // Default view set to monthly grid
                headerToolbar: { 
                    left: 'prev,next today', 
                    center: 'title', 
                    right: 'dayGridMonth,timeGridWeek' // View toggle controls
                },
                // Pass structured JavaScript object from PHP
                events: <?php echo json_encode($events, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>
            });
            
            // Draw the calendar on the webpage
            calendar.render();
        });
    </script>
</body>
</html>