<?php
session_start();
require 'Config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

if (empty($_SESSION['User_ID'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$User_ID = $_SESSION["User_ID"];


if(isset($_FILES['profile_picture']) && $_POST['action'] == 'update_profile_picture') {
    $response = ['success' => false, 'message' => ''];
    
  
    $uploadDir = 'uploads/profile/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $file = $_FILES['profile_picture'];
    

    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $maxFileSize = 5 * 1024 * 1024; 
    
    if (!in_array($file['type'], $allowedTypes)) {
        $response['message'] = 'Invalid file type. Only JPG, PNG, GIF, and WEBP files are allowed.';
        echo json_encode($response);
        exit;
    }
    
    if ($file['size'] > $maxFileSize) {
        $response['message'] = 'File size too large. Maximum file size is 5MB.';
        echo json_encode($response);
        exit;
    }
    
    
    $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $newFilename = 'profile_' . $User_ID . '_' . time() . '.' . $fileExtension;
    $targetFilePath = $uploadDir . $newFilename;
    
   
    if (move_uploaded_file($file['tmp_name'], $targetFilePath)) {
        
        $stmt = $conn->prepare("UPDATE table_user SET profile_picture = ? WHERE User_ID = ?");
        $stmt->bind_param("si", $newFilename, $User_ID);
        
        if ($stmt->execute()) {
         
            $result = $conn->query("SELECT profile_picture FROM table_user WHERE User_ID = $User_ID");
            $row = $result->fetch_assoc();
            $oldPicture = $row['profile_picture'] ?? '';
            
           
            if (!empty($oldPicture) && $oldPicture != 'profile-default.jpg' && file_exists($uploadDir . $oldPicture)) {
                unlink($uploadDir . $oldPicture);
            }
            
            $response['success'] = true;
            $response['filename'] = $newFilename;
        } else {
            $response['message'] = 'Failed to update database: ' . $conn->error;
        }
    } else {
        $response['message'] = 'Failed to upload file';
    }
    
    echo json_encode($response);
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_FILES['profile_picture'])) {
    // Process form data for normal profile updates
    $grade_level = mysqli_real_escape_string($conn, $_POST['grade_level'] ?? '');
    $section = mysqli_real_escape_string($conn, $_POST['section'] ?? '');
    $school_year = mysqli_real_escape_string($conn, $_POST['school_year'] ?? '');
    
    $sql = "UPDATE table_user SET 
            grade_level = ?, 
            section = ?, 
            school_year = ?
            WHERE User_ID = ?";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $grade_level, $section, $school_year, $User_ID);
    
    if ($stmt->execute()) {
        header("Location: dashboard.php?success=1");
    } else {
        header("Location: dashboard.php?error=1");
    }
    exit();
}


echo json_encode(['success' => false, 'message' => 'Invalid request']);
?>