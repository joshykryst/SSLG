<?php
require 'Config.php';
session_start();


if(empty($_SESSION["Admin_ID"])) {
    header('Location: Login.php?error=unauthorized');
    exit;
}


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: admingallery.php?error=invalidmethod');
    exit;
}


$collection_id = isset($_POST['collection_id']) ? intval($_POST['collection_id']) : 0;
if ($collection_id <= 0) {
    header('Location: admingallery.php?error=invalidcollection');
    exit;
}


if (empty($_FILES['images']['name'][0])) {
    header("Location: admingallery.php?collection=$collection_id&error=nofiles");
    exit;
}


$upload_dir = 'uploads/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}


$order_query = "SELECT COALESCE(MAX(sort_order), 0) as max_order FROM collection_images WHERE collection_id = ?";
$order_stmt = $conn->prepare($order_query);
$order_stmt->bind_param('i', $collection_id);
$order_stmt->execute();
$order_result = $order_stmt->get_result();
$order_row = $order_result->fetch_assoc();
$sort_order = (int)$order_row['max_order'];


$conn->begin_transaction();

try {
    $success_count = 0;
    $error_count = 0;
    
 
    foreach ($_FILES['images']['name'] as $key => $name) {
        $file_name = $_FILES['images']['name'][$key];
        $file_tmp = $_FILES['images']['tmp_name'][$key];
        $file_error = $_FILES['images']['error'][$key];
        
        
        if ($file_error !== UPLOAD_ERR_OK) {
            $error_count++;
            continue;
        }
        
       
        $new_file_name = time() . '_' . mt_rand(1000, 9999) . '_' . $file_name;
        $file_path = $upload_dir . $new_file_name;
        
        
        if (move_uploaded_file($file_tmp, $file_path)) {
           
            $insert_image_sql = "INSERT INTO images (filename, description, upload_date) VALUES (?, ?, NOW())";
            $insert_image_stmt = $conn->prepare($insert_image_sql);
            $description = pathinfo($file_name, PATHINFO_FILENAME);
            $insert_image_stmt->bind_param('ss', $new_file_name, $description);
            $insert_image_stmt->execute();
            
           
            $image_id = $conn->insert_id;
            
           
            if ($image_id) {
                $sort_order++;
                $insert_collection_sql = "INSERT INTO collection_images (collection_id, image_id, sort_order) VALUES (?, ?, ?)";
                $insert_collection_stmt = $conn->prepare($insert_collection_sql);
                $insert_collection_stmt->bind_param('iii', $collection_id, $image_id, $sort_order);
                $insert_collection_stmt->execute();
                $success_count++;
            } else {
                $error_count++;
            }
        } else {
            $error_count++;
        }
    }
    
  
    $conn->commit();
    
   
    if ($success_count > 0) {
        header("Location: admingallery.php?collection=$collection_id&success=uploaded&count=$success_count&errors=$error_count");
    } else {
        header("Location: admingallery.php?collection=$collection_id&error=failed");
    }
    
} catch (Exception $e) {
   
    $conn->rollback();
    header("Location: admingallery.php?collection=$collection_id&error=database&message=" . urlencode($e->getMessage()));
}
?>