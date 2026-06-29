<?php
require 'Config.php' ;
if(!empty($_SESSION["id"])){
    $id = $_SESSION["id"];
    $result = mysqli_query($conn, "SELECT * FROM table_user WHERE id = $id");
    $row = mysqli_fetch_assoc($result);
}



?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
        }
        .menu-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            cursor: pointer;
            z-index: 100;
        }
        .menu-btn div {
            width: 30px;
            height: 4px;
            background-color: black;
            margin: 5px;
            transition: 0.3s;
        }
        .sidebar {
            position: fixed;
            top: 0;
            right: -250px;
            width: 250px;
            height: 100%;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            box-shadow: -2px 0 5px rgba(0, 0, 0, 0.2);
            padding: 20px;
            transition: 0.3s;
        }
        .sidebar.active {
            right: 0;
        }
        .sidebar a {
            display: block;
            color: black;
            text-decoration: none;
            padding: 15px 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <div class="menu-btn" onclick="toggleMenu()">
        <div></div>
        <div></div>
        <div></div>
    </div>
    <div class="sidebar" id="sidebar">
        <h2>Menu</h2>
        <a href="#">Home</a>
        <a href="#">Profile</a>
        <a href="#">Settings</a>
        <a href="Logout.php">Logout</a>
    </div>
    <h1>Welcome</h1>
    <script>
        function toggleMenu() {
            document.getElementById("sidebar").classList.toggle("active");
        }
    </script>
</body>
</html>
