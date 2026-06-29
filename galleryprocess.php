<?php
session_start();
require 'Config.php';

if (!isset($_SESSION["User_ID"])) {
    header("Location: Login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
   
    $user_id = $_SESSION["User_ID"];
    
   
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $description = mysqli_real_escape_string($conn, $_POST['description'] ?? '');
       
        $privacy_status = mysqli_real_escape_string($conn, $_POST['privacy_status'] ?? 'private');
        
        
        $file_name = $_FILES['image']['name'];
        $file_tmp = $_FILES['image']['tmp_name'];
        $file_size = $_FILES['image']['size'];
        
        $new_filename = uniqid() . '_' . $file_name;
        
        
        $upload_dir = "uploads/";
        
        
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        
        if (move_uploaded_file($file_tmp, $upload_dir . $new_filename)) {
           
            $sql = "INSERT INTO images (filename, description, user_id, upload_date, privacy_status) 
                   VALUES ('$new_filename', '$description', $user_id, NOW(), '$privacy_status')";
            
            if ($conn->query($sql) === TRUE) {
                header("Location: gallery.php?upload=success");
                exit();
            } else {
                header("Location: gallery.php?error=database");
                exit();
            }
        } else {
            header("Location: gallery.php?error=upload");
            exit();
        }
    } else {
        header("Location: gallery.php?error=file");
        exit();
    }
}

header("Location: gallery.php");
exit();
?>
