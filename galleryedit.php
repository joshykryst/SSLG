<?php
session_start();
require 'Config.php';

header('Content-Type: application/json');


if (!isset($_SESSION["User_ID"])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$user_id = $_SESSION["User_ID"];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
   
    $imageId = mysqli_real_escape_string($conn, $_POST['id']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    
  
    $check_sql = "SELECT * FROM images WHERE id = '$imageId' AND user_id = '$user_id'";
    $check_result = $conn->query($check_sql);
    
    if ($check_result && $check_result->num_rows > 0) {
        
        $sql = "UPDATE images SET description = '$description' WHERE id = '$imageId'";
        
        if ($conn->query($sql) === TRUE) {
            echo json_encode([
                'success' => true,
                'message' => 'Description updated successfully',
                'description' => $description
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'You can only edit your own images']);
    }
    exit();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
