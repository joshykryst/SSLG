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


if (isset($_POST['selected_images'])) {

    $ids = explode(',', $_POST['selected_images']);
    $image_ids = array_filter(array_map('intval', $ids));
} elseif (isset($_POST['image_ids']) && is_array($_POST['image_ids'])) {

    $image_ids = array_filter(array_map('intval', $_POST['image_ids']));
} else {
    $image_ids = [];
}

if ($collection_id <= 0) {
    header('Location: admingallery.php?error=invalidcollection');
    exit;
}

if (empty($image_ids)) {
    header("Location: admingallery.php?collection=$collection_id&error=noimages");
    exit;
}

try {
    
    $conn->begin_transaction();
    
    
    $order_query = "SELECT COALESCE(MAX(sort_order), 0) as max_order FROM collection_images WHERE collection_id = ?";
    $order_stmt = $conn->prepare($order_query);
    $order_stmt->bind_param('i', $collection_id);
    $order_stmt->execute();
    $order_result = $order_stmt->get_result();
    $order_row = $order_result->fetch_assoc();
    $sort_order = (int)$order_row['max_order'];
    
   
    $insert_sql = "INSERT INTO collection_images (collection_id, image_id, sort_order) VALUES (?, ?, ?)";
    $insert_stmt = $conn->prepare($insert_sql);
    
    $success_count = 0;
    
   
    foreach ($image_ids as $image_id) {
        $sort_order++;
        $insert_stmt->bind_param('iii', $collection_id, $image_id, $sort_order);
        $insert_stmt->execute();
        $success_count++;
    }

    $conn->commit();
    
    header("Location: admingallery.php?collection=$collection_id&success=added&count=$success_count");
    
} catch (Exception $e) {

    $conn->rollback();
    header("Location: admingallery.php?collection=$collection_id&error=database&message=" . urlencode($e->getMessage()));
}
?>