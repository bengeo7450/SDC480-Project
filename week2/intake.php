<?php
/**
 * File: intake.php
 * Module: Client Intake Form
 * Author: Ben George
 */

session_start();

// Connect to the database
require_once '../db.php';

$message = '';

// Check if the form was submitted via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Trim whitespace and escape special HTML characters using htmlspecialchars() 
    // to prevent Cross-Site Scripting (XSS) when displaying user input
    $client_name  = htmlspecialchars(trim($_POST['client_name']), ENT_QUOTES, 'UTF-8');
    $client_email = htmlspecialchars(trim($_POST['client_email']), ENT_QUOTES, 'UTF-8');
    $service_note = htmlspecialchars(trim($_POST['service_note']), ENT_QUOTES, 'UTF-8');

    // Use a prepared SQL statement to safely insert the sanitized form data into the database
    $stmt = $pdo->prepare("INSERT INTO client_intake (name, email, notes) VALUES (:name, :email, :notes)");
    
    // Execute the query by binding the form values to the named parameters
    $stmt->execute([':name' => $client_name, ':email' => $client_email, ':notes' => $service_note]);

    // Success message to display on the page after submitting
    $message = "Intake submitted safely for " . $client_name;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CAMS Portal - Client Intake</title>
    
    <style>
        .container { max-width: 500px; margin: 30px auto; padding: 20px; border: 1px solid #ccc; border-radius: 8px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, textarea { width: 100%; padding: 8px; box-sizing: border-box; }
        button { padding: 10px 15px; background-color: #007bff; color: white; border: none; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Client Intake Form</h2>
        
        <!-- Display confirmation message if the form was successfully submitted -->
        <?php if ($message): ?>
            <p style="color: green;"><?php echo $message; ?></p>
        <?php endif; ?>
        
        <!-- Intake form posting back to this same script -->
        <form action="intake.php" method="POST">
            <div class="form-group">
                <label>Full Name:</label>
                <input type="text" name="client_name" required>
            </div>
            <div class="form-group">
                <label>Email Address:</label>
                <input type="email" name="client_email" required>
            </div>
            <div class="form-group">
                <label>Service Request Details:</label>
                <textarea name="service_note" rows="4" required></textarea>
            </div>
            <button type="submit">Submit Intake Request</button>
        </form>
    </div>
</body>
</html>