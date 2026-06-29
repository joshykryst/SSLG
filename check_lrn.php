<?php
require 'Config.php';

if(isset($_POST['lrn'])) {
    $lrn = $_POST['lrn'];
    
   
    $lrn = mysqli_real_escape_string($conn, $lrn);
    
   
    $query = "SELECT LRN FROM table_user WHERE LRN = '$lrn'";
    $result = mysqli_query($conn, $query);
    
    $response = array('exists' => false);
    
    if(mysqli_num_rows($result) > 0) {
        $response['exists'] = true;
    }
    
    
    header('Content-Type: application/json');
    echo json_encode($response);
}
?>