<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require 'Config.php';


if(empty($_SESSION["User_ID"])){
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

$User_ID = $_SESSION["User_ID"];


$debug = [];
$debug['user_id'] = $User_ID;


if(isset($_FILES['profile_picture'])){
    $debug['files'] = $_FILES;
    
  
    $uploadDir = 'uploads/profile/';
    if (!file_exists($uploadDir)) {
        $mkdirResult = mkdir($uploadDir, 0777, true);
        $debug['mkdir_result'] = $mkdirResult;
        
        if (!$mkdirResult) {
            $debug['mkdir_error'] = error_get_last();
        }
    }
    
    
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    $maxSize = 5 * 1024 * 1024; 
    
    $file = $_FILES['profile_picture'];
    
    if(!in_array($file['type'], $allowedTypes)){
        echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG, PNG and GIF are allowed.', 'debug' => $debug]);
        exit;
    }
    
    if($file['size'] > $maxSize){
        echo json_encode(['success' => false, 'message' => 'File is too large. Maximum size is 5MB.', 'debug' => $debug]);
        exit;
    }
    
   
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $newFileName = 'profile_' . $User_ID . '_' . time() . '.' . $extension;
    $targetPath = $uploadDir . $newFileName;
    
    $debug['target_path'] = $targetPath;
    
   
    $moveResult = move_uploaded_file($file['tmp_name'], $targetPath);
    $debug['move_result'] = $moveResult;
    
    if($moveResult){
       
        $result = mysqli_query($conn, "SELECT profile_picture FROM table_user WHERE User_ID = $User_ID");
        $row = mysqli_fetch_assoc($result);
        $oldPicture = $row['profile_picture'] ?? '';
        $debug['old_picture'] = $oldPicture;
        
       
        $stmt = $conn->prepare("UPDATE table_user SET profile_picture = ? WHERE User_ID = ?");
        $stmt->bind_param("si", $newFileName, $User_ID);
        
        $executeResult = $stmt->execute();
        $debug['execute_result'] = $executeResult;
        $debug['execute_error'] = $stmt->error;
        
        if($executeResult){
         
            if(!empty($oldPicture) && $oldPicture != 'profile-default.jpg' && file_exists($uploadDir . $oldPicture)){
                unlink($uploadDir . $oldPicture);
            }
            
            echo json_encode(['success' => true, 'message' => 'Profile picture updated successfully', 'file' => $newFileName, 'debug' => $debug]);
        } else {
       
            unlink($targetPath);
            echo json_encode(['success' => false, 'message' => 'Failed to update database: ' . $stmt->error, 'debug' => $debug]);
        }
    } else {
        $moveError = error_get_last();
        $debug['move_error'] = $moveError;
        echo json_encode(['success' => false, 'message' => 'Failed to upload file', 'debug' => $debug]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'No file uploaded']);
}