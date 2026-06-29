<?php
require 'Config.php';
if(!empty($_SESSION["id"])){
    header(Location: Indexs.php);
}
if(isset($_POST["submit"])){
    $UsernameEmail = $_POST["UsernameEmail"];
    $Password = $_POST["Password"];
    $result = mysqli_query($conn, "SELECT * FROM table_user WHERE username = '$UsernameEmail' OR email = '$UsernameEmail'");
    $row = mysqli_fetch_assoc($result);
    if (mysqli_num_rows($result) > 0 ){
    if ($Password == $row["Password"]){
    $_SESSION["login"] = true;
    $_SESSION["id"] = $row["id"];
    header("Location: indexs.php");
    }
    else {
        echo 
        "<script> alert('Incorrect Password'); </script>";
    }
    }
    else {
        echo 
        "<script> alert('User Not Registered'); </script>";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f4f4f4;
            font-family: 'Aharoni', sans-serif;
        }

        .navbar {
            width: 100%;
            background: rgb(10, 44, 88);
            padding: 15px 0;
            position: absolute;
            top: 0;
            left: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: 'Aharoni', sans-serif;
            font-weight: bold;
        }

        .logo-container {
            position: relative;
            margin-right: auto;
            padding-left: 14px;
            display: flex;
            align-items: center;
        }

        .logo-container img {
            height: 80px;
            transition: transform 0.3s ease;
        }

        .logo-container:hover img {
            transform: scale(1.1);
        }

        .logo-text {
            position: absolute;
            left: 100px;
            top: 50%;
            transform: translateY(-50%) translateX(-20px);
            color: white;
            font-size: 18px;
            opacity: 0;
            transition: transform 0.8s ease, opacity 0.8s ease;
            white-space: nowrap;
        }

        .logo-container:hover .logo-text {
            opacity: 1;
            transform: translateY(-50%) translateX(10px);
        }

        .logo-text a {
            color: white;
            text-decoration: none;
        }

        .logo-text a:hover {
            text-decoration: none;
        }

        .nav-links {
            display: flex;
            gap: 40px;
            justify-content: center;
            flex-grow: 1;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            font-size: 18px;
            position: relative;
            transition: transform 0.3s ease;
        }

        .nav-links a::after {
            content: "";
            position: absolute;
            left: 0;
            bottom: -3px;
            width: 0;
            height: 2px;
            background: white;
            transition: width 0.3s ease;
        }

        .nav-links a:hover::after {
            width: 100%;
        }

        .nav-links a:hover {
            transform: scale(1.1);
            text-decoration: none;
        }

        .frame {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            width: 400px;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        h2 {
            font-size: 24px;
            font-weight: bold;
        }

        form {
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .input-container {
            position: relative;
            width: 100%;
        }

        .input-container input {
            width: 100%;
            padding: 14px;
            margin: 8px 0;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 16px;
            box-sizing: border-box;
            transition: all 0.4s ease-in-out;
        }

        #PasswordContainer {
            opacity: 0;
            max-height: 0;
            overflow: hidden;
            transform: translateY(-20px);
            transition: opacity 0.6s ease, max-height 0.6s ease, transform 0.6s ease;
        }

        #PasswordContainer.show {
            opacity: 1;
            max-height: 100px;
            transform: translateY(0);
        }

        .login-btn {
            width: 100%;
            padding: 14px;
            font-size: 18px;
            font-weight: bold;
            background: url('icths.png') no-repeat center center/cover;
            border: none;
            color: white;
            border-radius: 8px;
            cursor: pointer;
            margin-top: 15px;
            transition: transform 0.3s ease, background 0.3s ease;
            opacity: 75%;
        }

        .login-btn:hover {
            background: url('icths.png') no-repeat center center/cover;
            opacity: 75%;
            transform: scale(1.05);
        }

        a {
            margin-top: 10px;
            text-decoration: none;
            font-size: 16px;
            color: #007bff;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const usernameEmailInput = document.getElementById("UsernameEmail");
            const passwordContainer = document.getElementById("PasswordContainer");
            const loginButton = document.querySelector(".login-btn");

            loginButton.addEventListener("click", function (event) {
                if (passwordContainer.classList.contains("show")) {
                    return; // Allow form submission
                }
                event.preventDefault();
                if (usernameEmailInput.value.trim() !== "") {
                    passwordContainer.classList.add("show");
                } else {
                    alert("Please enter your username or email first.");
                }
            });
        });
    </script>
</head>
<body>
    <div class="navbar">
        <div class="logo-container">
            <img src="ICT.png" alt="School Logo">
            <span class="logo-text">
                <a href="https://claro-recto-highschool.example">CLARO M. RECTO INFORMATION<br>COMMUNICATION TECHNOLOGY HIGH SCHOOL</a>
            </span>
        </div>
        <div class="nav-links">
            <a href="#">Home</a>
            <a href="#">About</a>
            <a href="#">Gallery</a>
            <a href="#">Contacts</a>
        </div>
    </div>

    <div class="frame">
        <h2>Login</h2>
        <form action="" method="post" autocomplete="off">
            <div class="input-container">
                <input type="text" name="UsernameEmail" id="UsernameEmail" placeholder="Email or Username" required>
            </div>
            
            <div id="PasswordContainer" class="input-container">
                <input type="password" name="Password" id="Password" placeholder="Password" required>
            </div>
            
            <button type="submit" class="login-btn" name="submit">Login</button>
        </form>
        <br>
        <a href="Registration.php">Registration</a>
    </div>
</body>
</html>