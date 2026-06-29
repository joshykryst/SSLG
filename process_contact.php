<?php
session_start();
require 'Config.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Add this at the top of contacts.php (after session_start) temporarily for debugging
if (isset($_GET['status'])) {
    echo '<div style="background: #f8d7da; padding: 15px; margin: 10px; border-radius: 4px;">';
    echo 'Parameters received:<br>';
    foreach ($_GET as $key => $value) {
        echo htmlspecialchars("$key: $value") . '<br>';
    }
    echo '</div>';
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $subject = mysqli_real_escape_string($conn, $_POST['subject']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);
    
    // First check if contact_feedback table exists, create if not
    $check_table = mysqli_query($conn, "SHOW TABLES LIKE 'contact_feedback'");
    if(mysqli_num_rows($check_table) == 0) {
        $create_table_query = "CREATE TABLE contact_feedback (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL,
            subject VARCHAR(200) NOT NULL,
            message TEXT NOT NULL,
            status ENUM('new', 'read', 'responded') DEFAULT 'new',
            response TEXT NULL,
            response_date DATETIME NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        
        if(!mysqli_query($conn, $create_table_query)) {
            header("Location: contacts.php?status=error&message=" . urlencode("Database error. Please try again later."));
            exit();
        }
    }
    
    // Now insert the data
    $insert_query = "INSERT INTO contact_feedback (name, email, subject, message) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param("ssss", $name, $email, $subject, $message);
    
    if ($stmt->execute()) {
        // Get the tracking ID (inserted ID)
        $tracking_id = $conn->insert_id;
        
        // Redirect with success message and tracking ID
        header("Location: contacts.php?status=success&message=" . 
            urlencode("Thank you for your message!") . 
            "&tracking=" . $tracking_id . "&email=" . urlencode($email));
        exit();
    } else {
        // Redirect with error message
        header("Location: contacts.php?status=error&message=" . urlencode("Failed to send message. Please try again."));
        exit();
    }
    
    $stmt->close();
} else {
    // If not a POST request, redirect to contact page
    header("Location: contacts.php");
    exit();
}

$conn->close();
exit();
?>