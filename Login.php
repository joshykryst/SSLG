<?php

ob_start();


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require 'Config.php';


error_reporting(E_ALL);
ini_set('display_errors', 1);

// Redirect if already logged in as student
if(!empty($_SESSION["User_ID"])){
    header("Location: Indexs.php");
    exit();
}

// redirect pag naka log ng admin
if(!empty($_SESSION["Admin_ID"])){
    header("Location: admin.php");
    exit();
}

// Handle ng login submissions
if(isset($_POST["submit"])) {
    $usernameemail = $_POST["usernameemail"];
    $password = $_POST["password"];
    
    // Check admin table muna
    $result = mysqli_query($conn, "SELECT * FROM table_admin WHERE (Username = '$usernameemail' OR Email = '$usernameemail')");
    
    if(mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        
        if(password_verify($password, $row["Password"])) {
            $_SESSION["login"] = true;
            $_SESSION["Admin_ID"] = $row["Admin_ID"];
            
            // Store the role and subject in the session
            $_SESSION["role"] = $row["role"];
            $_SESSION["subject"] = $row["subject"];
            
            header("Location: Admin.php");
            exit();
        } else {
            echo "<script> alert('Wrong Password'); </script>";
        }
    } else {
        // Check user table next
        $result = mysqli_query($conn, "SELECT * FROM table_user WHERE (Username = '$usernameemail' OR Email = '$usernameemail')");
        
        if(mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            
            if(password_verify($password, $row["Password"])) {
                $_SESSION["login"] = true;
                $_SESSION["User_ID"] = $row["User_ID"];
                header("Location: indexs.php");
                exit();
            } else {
                echo "<script> alert('Wrong Password'); </script>";
            }
        } else {
            echo "<script> alert('User Not Registered'); </script>";
        }
    }
}

// For navigation setup
$isLoggedIn = false;
$userData = null;

if(!empty($_SESSION["User_ID"])){
    $User_ID = $_SESSION["User_ID"];
    $result = mysqli_query($conn, "SELECT * FROM table_user WHERE User_ID = '" . mysqli_real_escape_string($conn, $User_ID) . "'");
    
    if($result && mysqli_num_rows($result) > 0){
        $userData = mysqli_fetch_assoc($result);
        $isLoggedIn = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - CMRICTHS</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="dashboard.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
    body {
        margin: 0;
        padding: 0;
        font-family: 'Poppins', sans-serif;
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        min-height: 100vh;
        display: flex;
        flex-direction: column;
    }

    .main-content {
        flex: 1;
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 80px 20px 40px;
    }

    .frame {
        background: rgba(255, 255, 255, 0.95);
        padding: 40px;
        border-radius: 20px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        width: 100%;
        max-width: 400px;
        text-align: center;
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.3);
        margin: 0 auto;
    }

    h2 {
        font-size: 28px;
        color: #1a237e;
        margin-bottom: 30px;
        text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
    }

    form {
        width: 100%;
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .input-container {
        position: relative;
        width: 100%;
        margin-bottom: 15px;
    }

    .input-container input {
        width: 100%;
        padding: 15px;
        border: 2px solid #e1e1e1;
        border-radius: 12px;
        font-size: 16px;
        background: rgba(255, 255, 255, 0.9);
        transition: all 0.3s ease;
        box-sizing: border-box;
    }

    .input-container input:focus {
        border-color: #1a237e;
        box-shadow: 0 0 0 3px rgba(26, 35, 126, 0.1);
        outline: none;
        transform: translateY(-2px);
    }

    #PasswordContainer {
        opacity: 0;
        max-height: 0;
        overflow: hidden;
        transform: translateY(-20px);
        transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
    }

    #PasswordContainer.show {
        opacity: 1;
        max-height: 100px;
        transform: translateY(0);
    }

    .password-toggle {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
        color: #777;
        transition: color 0.3s;
    }

    .password-toggle:hover {
        color: #1a237e;
    }

    button {
        width: 100%;
        padding: 16px;
        font-size: 18px;
        font-weight: bold;
        background: linear-gradient(45deg, #1a237e, #3949ab);
        border: none;
        color: white;
        border-radius: 12px;
        cursor: pointer;
        margin-top: 20px;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(26, 35, 126, 0.2);
    }

    button:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 20px rgba(26, 35, 126, 0.3);
        background: linear-gradient(45deg, #3949ab, #1a237e);
    }

    a {
        display: inline-block;
        margin-top: 15px;
        text-decoration: none;
        color: #1a237e;
        font-weight: bold;
        transition: all 0.3s ease;
    }

    a:hover {
        color: #3949ab;
        transform: scale(1.05);
    }

    .error-message {
        color: #dc3545;
        background-color: rgba(220, 53, 69, 0.1);
        padding: 10px;
        border-radius: 8px;
        margin-bottom: 15px;
        font-size: 14px;
        text-align: center;
    }

    /* Footer styles */
    footer {
        background-color: #1a237e;
        color: white;
        text-align: center;
        padding: 20px 0;
        margin-top: auto;
        width: 100%;
    }

    .footer-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
    }

    .footer-content {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: center;
    }

    .footer-logo {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 15px;
    }

    .footer-logo img {
        height: 50px;
    }

    .footer-logo h3 {
        font-size: 1.2rem;
        margin: 0;
    }

    .footer-links {
        display: flex;
        gap: 20px;
    }

    .footer-links a {
        color: white;
        text-decoration: none;
        transition: opacity 0.3s;
        margin: 0;
    }

    .footer-links a:hover {
        opacity: 0.8;
        transform: none;
    }

    .footer-copyright {
        width: 100%;
        margin-top: 15px;
        padding-top: 15px;
        border-top: 1px solid rgba(255,255,255,0.2);
        font-size: 0.9rem;
        opacity: 0.8;
    }

    /* Mobile responsiveness */
    @media (max-width: 576px) {
        .frame {
            padding: 25px;
            width: 100%;
            max-width: none;
            border-radius: 15px;
        }

        .name-container {
            flex-direction: column;
            gap: 15px;
        }

        h2 {
            font-size: 24px;
        }
    }
    </style>
    
    <script type="text/javascript">
        function toggleMenu() {
            const sidebar = document.getElementById('sidebar');
            if (sidebar) {
                sidebar.classList.toggle('active');
            }
        }

        document.addEventListener("DOMContentLoaded", function () {
            const usernameEmailInput = document.getElementById("UsernameEmail");
            const passwordContainer = document.getElementById("PasswordContainer");
            const loginButton = document.querySelector(".login-btn");

            loginButton.addEventListener("click", function (event) {
                if (passwordContainer.classList.contains("show")) {
                    return; 
                }
                event.preventDefault();
                if (usernameEmailInput.value.trim() !== "") {
                    passwordContainer.classList.add("show");
                } else {
                    alert("Please enter your username or email first.");
                }
            });
        });

        function togglePasswordVisibility() {
            const passwordField = document.getElementById("Password");
            const toggleIcon = document.getElementById("togglePasswordIcon");
            
            if (passwordField.type === "password") {
                passwordField.type = "text";
                toggleIcon.classList.remove("fa-eye");
                toggleIcon.classList.add("fa-eye-slash");
            } else {
                passwordField.type = "password";
                toggleIcon.classList.remove("fa-eye-slash");
                toggleIcon.classList.add("fa-eye");
            }
        }
    </script>
</head>
<body>
    <!-- Main Navigation -->
    <nav class="main-nav">
        <div class="nav-container">
            <div class="nav-left">
                <a href="index.php" class="nav-brand">
                    <img src="logo.png" alt="CMRICTHS Logo" class="nav-logo">
                    <div class="brand-text">
                        <h1>CMRICTHS</h1>
                        <p>Information and Communication Technology High School</p>
                    </div>
                </a>
                <div class="nav-links">
                    <a href="Indexs.php">Home</a>
                    <a href="about.php">About</a>
                    <a href="Gallery.php">Gallery</a>
                    <a href="#admissions">Admissions</a>
                    <a href="News.php">News & Events</a>
                    <a href="contacts.php">Contact</a>
                </div>
            </div>
            <div class="nav-right">
                <?php if($isLoggedIn): ?>
                    <div class="nav-profile">
                        <div class="profile-dropdown">
                            <div class="profile-trigger" onclick="toggleMenu()">
                                <img src="<?php echo $userData['profile_picture'] ?? 'profile-default.jpg'; ?>" 
                                     alt="Profile" 
                                     class="profile-avatar">
                                <span class="profile-name"><?php echo htmlspecialchars($userData['Username']); ?></span>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="Login.php" class="portal-btn">
                        <span class="icon icon-login"></span>
                        Student Portal
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <?php if($isLoggedIn): ?>
                <img src="<?php echo $userData['profile_picture'] ?? 'profile-default.jpg'; ?>" alt="User Profile" class="sidebar-logo">
                <h3>Welcome, <?php echo htmlspecialchars($userData['Username']); ?></h3>
                <button class="close-btn" onclick="toggleMenu()">
                    <span class="icon icon-close"></span>
                </button>
            <?php else: ?>
                <img src="profile-default.jpg" alt="Guest Profile" class="sidebar-logo">
                <h3>Welcome, Guest</h3>
                <button class="close-btn" onclick="toggleMenu()">
                    <span class="icon icon-close"></span>
                </button>
            <?php endif; ?>
        </div>
        
        <nav class="sidebar-nav">
            <?php if($isLoggedIn): ?>
                
                <div class="nav-section">
                    <h4>Quick Links</h4>
                    <a href="dashboard.php"><span class="icon icon-dashboard"></span>Dashboard</a>
                    <a href="profile.php"><span class="icon icon-user"></span>Profile</a>
                    <a href="grades.php"><span class="icon icon-grades"></span>Grades</a>
                    <a href="schedule.php"><span class="icon icon-calendar"></span>Schedule</a>
                </div>
                <div class="nav-section">
                    <h4>Communications</h4>
                    <a href="messages.php"><span class="icon icon-messages"></span>Messages</a>
                    <a href="notifications.php"><span class="icon icon-notifications"></span>Notifications</a>
                </div>
                <div class="nav-section">
                    <h4>Settings</h4>
                    <a href="settings.php"><span class="icon icon-settings"></span>Settings</a>
                    <a href="Logout.php" class="logout-btn">
                        <span class="icon icon-logout"></span>Logout
                    </a>
                </div>
            <?php else: ?>
               
                <div class="nav-section">
                    <h4>Menu</h4>
                    <a href="Login.php" class="active"><span class="icon icon-login"></span>Login</a>
                    <a href="Registration.php"><span class="icon icon-register"></span>Register</a>
                    <a href="#about"><span class="icon icon-info"></span>About Us</a>
                    <a href="#contact"><span class="icon icon-contact"></span>Contact</a>
                </div>
            <?php endif; ?>
        </nav>
    </div>


    <main class="main-content">
        <div class="frame">
            <h2>Login</h2>
            <?php if(isset($error)): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>
            <form action="" method="post" autocomplete="off">
                <div class="input-container">
                    <input type="text" name="usernameemail" id="UsernameEmail" placeholder="Email or Username" required>
                </div>
                
                <div id="PasswordContainer" class="input-container">
                    <input type="password" name="password" id="Password" placeholder="Password" required>
                    <span class="password-toggle" onclick="togglePasswordVisibility()">
                        <i class="fas fa-eye" id="togglePasswordIcon"></i>
                    </span>
                </div>
                
                <button type="submit" class="login-btn" name="submit">Enter</button>
            </form>
            <br>
            <a href="Registration.php">Register an account</a>
        </div>
    </main>


    <footer>
        <div class="footer-container">
            <div class="footer-content">
                <div class="footer-logo">
                    <img src="logo.png" alt="CMRICTHS Logo">
                    <h3>CMRICTHS</h3>
                </div>
                <div class="footer-links">
                    <a href="Indexs.php">Home</a>
                    <a href="about.php">About</a>
                    <a href="Gallery.php">Gallery</a>
                    <a href="contacts.php">Contact</a>
                </div>
            </div>
            <div class="footer-copyright">
                &copy; <?php echo date('Y'); ?> CMRICTHS. All rights reserved.
            </div>
        </div>
    </footer>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/js/all.min.js"></script>
</body>
</html>