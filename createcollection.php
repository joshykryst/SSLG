<?php
session_start();
require 'Config.php';


$isAuthorized = false;

if(!empty($_SESSION["Admin_ID"])) {
    $isAuthorized = true;
} elseif(!empty($_SESSION["Teacher_ID"])) {
    $isAuthorized = true;
} elseif(!empty($_SESSION["User_ID"])) {
    $User_ID = $_SESSION["User_ID"];
    $result = mysqli_query($conn, "SELECT * FROM table_user WHERE User_ID = '" . mysqli_real_escape_string($conn, $User_ID) . "'");
    
    if($result && mysqli_num_rows($result) > 0){
        $userData = mysqli_fetch_assoc($result);
        $isAdmin = isset($userData['is_admin']) && $userData['is_admin'] == 1;
        $isAuthorized = $isAdmin;
    }
}

if (!$isAuthorized) {
    header("Location: Admin.php?error=unauthorized");
    exit;
}


if (!file_exists('uploads')) {
    mkdir('uploads', 0777, true);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $collection_name = mysqli_real_escape_string($conn, $_POST['collection_name']);
    $collection_date = mysqli_real_escape_string($conn, $_POST['collection_date']);
    $collection_description = mysqli_real_escape_string($conn, $_POST['collection_description'] ?? '');
    
   
    if (isset($_FILES['collection_cover']) && $_FILES['collection_cover']['error'] == 0) {
        
        $file_name = $_FILES['collection_cover']['name'];
        $file_tmp = $_FILES['collection_cover']['tmp_name'];
        
        
        $new_filename = uniqid() . '_' . $file_name;
        
        
        $upload_dir = "uploads/";
        
        
        if (move_uploaded_file($file_tmp, $upload_dir . $new_filename)) {
            
            $sql = "INSERT INTO collections (name, description, date, cover_image, created_at) 
                   VALUES ('$collection_name', '$collection_description', '$collection_date', '$new_filename', NOW())";
            
            if ($conn->query($sql) === TRUE) {
                header("Location: admingallery.php?success=collection");
                exit;
            } else {
                header("Location: admingallery.php?error=database&msg=" . urlencode($conn->error));
                exit;
            }
        } else {
            header("Location: admingallery.php?error=upload&msg=Failed to move uploaded file");
            exit;
        }
    } else {
        header("Location: admingallery.php?error=file&msg=No file uploaded or file error: " . $_FILES['collection_cover']['error']);
        exit;
    }
}

header("Location: admingallery.php");
exit;
?>