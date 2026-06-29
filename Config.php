<?php

if (session_status() === PHP_SESSION_NONE) {

    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
    
   
    session_start();
}


$server = "localhost";
$user = "root";  
$password = "";  
$database = "registerlog";


$conn = new mysqli('localhost', 'root', '', 'registerlog');


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


error_reporting(E_ALL);
ini_set('display_errors', 1);


if (!isset($_SESSION["User_ID"])) {
    error_log('⚠ SESSION NOT ACTIVE!');
} else {
    error_log('✅ SESSION ACTIVE: User_ID = ' . $_SESSION["User_ID"]);
}
?>

