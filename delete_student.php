<?php
session_start();
require 'Config.php';

// Check if user is logged in and is an admin
header('Content-Type: application/json');

if(empty($_SESSION["Admin_ID"])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Check if ID parameter exists
if(!isset($_GET['id']) || empty($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
    exit;
}

$id = (int) $_GET['id'];

try {
    // Disable foreign key checks
    $conn->query("SET FOREIGN_KEY_CHECKS=0");
    
    // Check if user exists
    $check = $conn->query("SELECT User_ID FROM table_user WHERE User_ID = $id");
    if ($check->num_rows == 0) {
        throw new Exception("Student record not found");
    }
    
    // Get all tables in the database
    $tables_result = $conn->query("SHOW TABLES");
    $database_tables = [];
    while ($table = $tables_result->fetch_row()) {
        $database_tables[] = $table[0];
    }
    
    // Known tables that might have User_ID column
    $possible_tables = ['grades', 'attendance', 'user_activities', 'submissions', 'assignments', 'student_logs'];
    
    // Try to delete from tables that actually exist
    foreach ($possible_tables as $table) {
        if (in_array($table, $database_tables)) {
            // Check if this table has User_ID column
            $column_check = $conn->query("SHOW COLUMNS FROM `$table` LIKE 'User_ID'");
            if ($column_check && $column_check->num_rows > 0) {
                // Table exists and has User_ID column
                $conn->query("DELETE FROM `$table` WHERE User_ID = $id");
            }
        }
    }
    
    // Delete the user record
    $result = $conn->query("DELETE FROM table_user WHERE User_ID = $id");
    
    if (!$result) {
        throw new Exception($conn->error);
    }
    
    // Re-enable foreign key checks
    $conn->query("SET FOREIGN_KEY_CHECKS=1");
    
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    // Re-enable foreign key checks
    $conn->query("SET FOREIGN_KEY_CHECKS=1");
    
    // Log the error
    error_log("Error deleting student ID $id: " . $e->getMessage());
    
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>