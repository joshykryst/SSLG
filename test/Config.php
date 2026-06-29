<?php
// Start session only if it's not already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database Connection
$conn = mysqli_connect("localhost", "root", "", "registerlog");

// Check if connection is successful
if (!$conn) {
    die("❌ Database Connection Failed: " . mysqli_connect_error());
}

// Debugging - Check if session is active
if (!isset($_SESSION["id"])) {
    echo "<script>console.log('⚠ SESSION NOT ACTIVE!');</script>";
} else {
    echo "<script>console.log('✅ SESSION ACTIVE: ID = " . $_SESSION["id"] . "');</script>";
}
?>
