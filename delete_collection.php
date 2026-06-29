<?php
session_start();
require 'Config.php';


if(empty($_SESSION["Admin_ID"])) {
    header("Location: Login.php?error=unauthorized");
    exit;
}


if(!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: admingallery.php?error=invalid_request");
    exit;
}

$collection_id = (int)$_GET['id'];

$conn->begin_transaction();

try {

    $collection_query = "SELECT name, cover_image FROM collections WHERE id = ?";
    $stmt = $conn->prepare($collection_query);
    $stmt->bind_param("i", $collection_id);
    $stmt->execute();
    $collection_result = $stmt->get_result();
    
    if($collection_result->num_rows === 0) {
        throw new Exception("Collection not found");
    }
    
    $collection_data = $collection_result->fetch_assoc();
    $collection_name = $collection_data['name'];
    $cover_image = $collection_data['cover_image'];
    
 
    $images_query = "SELECT i.filename 
                     FROM images i 
                     INNER JOIN collection_images ci ON i.id = ci.image_id 
                     WHERE ci.collection_id = ?";
    $stmt = $conn->prepare($images_query);
    $stmt->bind_param("i", $collection_id);
    $stmt->execute();
    $images_result = $stmt->get_result();
    
    $images_to_delete = [];
    while($row = $images_result->fetch_assoc()) {
      
        $images_to_delete[] = $row['filename'];
    }
    
 
    $delete_assoc = "DELETE FROM collection_images WHERE collection_id = ?";
    $stmt = $conn->prepare($delete_assoc);
    $stmt->bind_param("i", $collection_id);
    if(!$stmt->execute()) {
        throw new Exception("Failed to delete collection image associations");
    }
    
   
    $delete_collection = "DELETE FROM collections WHERE id = ?";
    $stmt = $conn->prepare($delete_collection);
    $stmt->bind_param("i", $collection_id);
    if(!$stmt->execute()) {
        throw new Exception("Failed to delete collection");
    }
    
    
    foreach($images_to_delete as $filename) {
     
        $check_usage = "SELECT COUNT(*) AS count FROM collections WHERE cover_image = ? 
                        UNION ALL 
                        SELECT COUNT(*) FROM collection_images ci 
                        JOIN images i ON ci.image_id = i.id 
                        WHERE i.filename = ?";
        $stmt = $conn->prepare($check_usage);
        $stmt->bind_param("ss", $filename, $filename);
        $stmt->execute();
        $usage_result = $stmt->get_result();
        
        $total_usage = 0;
        while($usage = $usage_result->fetch_assoc()) {
            $total_usage += $usage['count'];
        }
        
       
        if($total_usage === 0) {
          
            $file_path = "uploads/" . $filename;
            if(file_exists($file_path)) {
                unlink($file_path);
            }
            
          
            $delete_image = "DELETE FROM images WHERE filename = ?";
            $stmt = $conn->prepare($delete_image);
            $stmt->bind_param("s", $filename);
            $stmt->execute();
        }
    }
    
    
    $conn->commit();
    header("Location: admingallery.php?success=collection_deleted&name=" . urlencode($collection_name));
    exit;
    
} catch (Exception $e) {

    $conn->rollback();
    header("Location: admingallery.php?error=delete_failed&message=" . urlencode($e->getMessage()));
    exit;
}
?>