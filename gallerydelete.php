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
    

    $check_sql = "SELECT filename FROM images WHERE id = '$imageId' AND user_id = '$user_id'";
    $check_result = $conn->query($check_sql);
    
    if ($check_result && $check_result->num_rows > 0) {
    
        $row = $check_result->fetch_assoc();
        $filename = $row['filename'];
        
       
        $sql = "DELETE FROM images WHERE id = '$imageId'";
        
        if ($conn->query($sql) === TRUE) {
        
            $filepath = "uploads/" . $filename;
            if (file_exists($filepath)) {
                unlink($filepath); 
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Image deleted successfully'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'You can only delete your own images']);
    }
    exit;
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
