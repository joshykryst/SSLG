<?php

ob_start();


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require 'Config.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);


if(!empty($_SESSION["User_ID"])){
    header("Location: Indexs.php");
    exit();
}

if (isset($_POST["submit"])) {
    $Username = $_POST["Username"] ?? '';
    $FirstName = $_POST["FirstName"] ?? '';
    $LastName = $_POST["LastName"] ?? '';
    $Birthday = $_POST["Birthday"] ?? '';
    $Gender = $_POST["Gender"] ?? '';
    $LRN = $_POST["LRN"] ?? '';
    $Email = $_POST["Email"] ?? '';
    $Password = $_POST["Password"] ?? '';
    $ConfirmPassword = $_POST["ConfirmPassword"] ?? '';
    
 
    if (strlen($Username) > 20) {
        echo "<script>alert('Username cannot exceed 20 characters');</script>";
    } 
    else if (strlen($FirstName) > 50) {
        echo "<script>alert('First Name cannot exceed 50 characters');</script>";
    }
    else if (strlen($LastName) > 50) {
        echo "<script>alert('Last Name cannot exceed 50 characters');</script>";
    }
    
    else {
        if ($Gender === 'Others' && !empty($_POST["OtherGender"])) {
            $Gender = htmlspecialchars($_POST["OtherGender"]);
        }

   
        $section = 'A';
        $grade_level = 'Grade 7';
        $school_year = '2024-2025';

  
        $duplicateLRN = mysqli_query($conn, "SELECT * FROM table_user WHERE LRN = '$LRN'");
        $duplicateUsername = mysqli_query($conn, "SELECT * FROM table_user WHERE Username = '$Username'");
        $duplicateEmail = mysqli_query($conn, "SELECT * FROM table_user WHERE Email = '$Email'");

        if (mysqli_num_rows($duplicateLRN) > 0) {
            echo "<script>alert('Registration Failed: This LRN is already registered. Each student can only have one account.');</script>";
        } 
        else if (mysqli_num_rows($duplicateUsername) > 0) {
            echo "<script>alert('Registration Failed: This username is already taken. Please choose a different username.');</script>";
        }
        else if (mysqli_num_rows($duplicateEmail) > 0) {
            echo "<script>alert('Registration Failed: This email address is already registered. Please use a different email.');</script>";
        } 
        else {
            if ($Password == $ConfirmPassword) {
           
                $hashedPassword = password_hash($Password, PASSWORD_BCRYPT);
                
                $stmt = $conn->prepare("INSERT INTO table_user (FirstName, LastName, Username, Email, Password, LRN, section, grade_level, school_year, Birthday, Gender) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("sssssssssss", $FirstName, $LastName, $Username, $Email, $hashedPassword, $LRN, $section, $grade_level, $school_year, $Birthday, $Gender);
            
                if($stmt->execute()) {
                    echo "<script>alert('Registration Successful');</script>";
                    echo "<script>window.location.href = 'Login.php';</script>";
                } else {
                    echo "<script>alert('Registration Failed: " . $stmt->error . "');</script>";
                }
                $stmt->close();
            } else {
                echo "<script>alert('Password Does Not Match');</script>";
            }
        }
    }
}

$checkTable = false;
if ($checkTable) {
    $result = mysqli_query($conn, "DESCRIBE table_user");
    echo "<pre>";
    while ($row = mysqli_fetch_assoc($result)) {
        print_r($row);
    }
    echo "</pre>";
}


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
    <title>Registration - CMRICTHS</title>
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
        padding: 40px 20px; 
        margin-bottom: 100px; 
    }

    .frame {
        background: rgba(255, 255, 255, 0.95);
        padding: 40px;
        border-radius: 20px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        width: 100%;
        max-width: 500px;
        text-align: center;
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.3);
        margin: 0 auto;
    }
    
   
    footer {
        background-color: #1a237e;
        color: white;
        text-align: center;
        padding: 20px 0;
        width: 100%;
        position: relative; 
        margin-top: 50px; 
    }
    
    
    .page-wrapper {
        display: flex;
        flex-direction: column;
        min-height: 100vh;
    }
    
    
    @media (max-height: 900px) {
        .main-content {
            margin-top: 30px;
            margin-bottom: 120px; 
            min-height: auto; 
        }
    }
    

    form {
        width: 100%;
        display: flex;
        flex-direction: column;
        gap: 15px;
        margin-bottom: 20px; 
    }

    h2 {
        font-size: 28px;
        color: #1a237e;
        margin-bottom: 30px;
        text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
    }

    .name-container {
        display: flex;
        gap: 15px;
    }

    input[type="text"], 
    input[type="date"],
    input[type="email"], 
    input[type="password"] {
        width: 100%;
        padding: 15px;
        border: 2px solid #e1e1e1;
        border-radius: 12px;
        font-size: 16px;
        background: rgba(255, 255, 255, 0.9);
        transition: all 0.3s ease;
        box-sizing: border-box;
    }

    input:focus {
        border-color: #1a237e;
        box-shadow: 0 0 0 3px rgba(26, 35, 126, 0.1);
        outline: none;
        transform: translateY(-2px);
    }

    .gender-container {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 15px;
        background: rgba(255, 255, 255, 0.8);
        border-radius: 12px;
        border: 2px solid #e1e1e1;
    }

    .gender-container label {
        color: #1a237e;
        font-size: 16px;
    }

    .gender-container input[type="radio"] {
        accent-color: #1a237e;
        transform: scale(1.2);
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

    .help-text {
        font-size: 0.8rem;
        color: #666;
        text-align: left;
        margin-top: -8px;
        margin-left: 5px;
    }

    .input-container {
        position: relative;
        width: 100%;
        margin-bottom: 15px;
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

    .password-match-indicator {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
    }

    .fa-check-circle {
        color: #28a745;
    }

    .fa-times-circle {
        color: #dc3545;
    }

    #passwordMatchText.match {
        color: #28a745;
    }

    #passwordMatchText.no-match {
        color: #dc3545;
    }

 
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

        .gender-container {
            flex-wrap: wrap;
            justify-content: space-between;
        }
        
        h2 {
            font-size: 24px;
        }
    }
    
 
    footer {
        background-color: #1a237e;
        color: white;
        text-align: center;
        padding: 20px 0;
        width: 100%;
        position: relative; 
        clear: both; 
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
    
    
    html, body {
        height: 100%;
    }
    
    @media (max-height: 800px) {
        .main-content {
            padding-top: 60px;
            padding-bottom: 80px; 
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

        function togglePasswordVisibility() {
            const passwordField = document.getElementById("Password");
            const toggleIcon = document.getElementById("togglePasswordIcon");
            
            if (passwordField.type == "password") {
                passwordField.type = "text";
                toggleIcon.classList.remove("fa-eye");
                toggleIcon.classList.add("fa-eye-slash");
            } else {
                passwordField.type = "password";
                toggleIcon.classList.remove("fa-eye-slash");
                toggleIcon.classList.add("fa-eye");
            }
        }
        
        
        document.addEventListener('DOMContentLoaded', function() {
            const maleRadio = document.getElementById('Male');
            const femaleRadio = document.getElementById('Female');
            const othersRadio = document.getElementById('Others');
            const otherGenderContainer = document.getElementById('otherGenderContainer');
            const otherGenderInput = document.getElementById('OtherGender');
            
            function handleGenderChange() {
                if (othersRadio.checked) {
                    otherGenderContainer.style.display = 'block';
                    otherGenderInput.required = true;
                } else {
                    otherGenderContainer.style.display = 'none';
                    otherGenderInput.required = false;
                }
            }
            
            maleRadio.addEventListener('change', handleGenderChange);
            femaleRadio.addEventListener('change', handleGenderChange);
            othersRadio.addEventListener('change', handleGenderChange);
            
          
            handleGenderChange();
        });
    </script>
    <script type="text/javascript">
    document.addEventListener('DOMContentLoaded', function() {
        // Existing gender code...
        
        // Password matching check
        const passwordField = document.getElementById("Password");
        const confirmField = document.getElementById("ConfirmPassword");
        const matchIndicator = document.getElementById("passwordMatchIndicator");
        const matchText = document.getElementById("passwordMatchText");
        
        function checkPasswordMatch() {
            if (confirmField.value === '') {
                matchIndicator.innerHTML = '';
                matchText.className = '';
                matchText.textContent = "Passwords must match";
                return;
            }
            
            if (passwordField.value === confirmField.value) {
                matchIndicator.innerHTML = '<i class="fas fa-check-circle"></i>';
                matchText.className = 'match';
                matchText.textContent = "Passwords match";
            } else {
                matchIndicator.innerHTML = '<i class="fas fa-times-circle"></i>';
                matchText.className = 'no-match';
                matchText.textContent = "Passwords do not match";
            }
        }
        
        passwordField.addEventListener('keyup', checkPasswordMatch);
        confirmField.addEventListener('keyup', checkPasswordMatch);
    });
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const lrnField = document.getElementById('LRN');
    const lrnHelpText = document.querySelector('#LRN + .help-text');
    let lrnTimeout = null;
    
    lrnField.addEventListener('input', function() {
        // Clear any previous timeout
        clearTimeout(lrnTimeout);
        
        // Make sure the LRN is complete before checking
        if (this.value.length !== 12) {
            lrnHelpText.textContent = "Enter your 12-digit Learner Reference Number";
            lrnHelpText.style.color = "#666";
            return;
        }
        
        // Set a timeout to prevent too many requests
        lrnTimeout = setTimeout(function() {
            // Create AJAX request
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'check_lrn.php', true);
            xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
            
            xhr.onload = function() {
                if (this.status === 200) {
                    const response = JSON.parse(this.responseText);
                    if (response.exists) {
                        lrnHelpText.textContent = "This LRN is already registered";
                        lrnHelpText.style.color = "#dc3545";
                    } else {
                        lrnHelpText.textContent = "LRN is available";
                        lrnHelpText.style.color = "#28a745";
                    }
                }
            }
            
            xhr.send('lrn=' + lrnField.value);
        }, 500);
    });
});
</script>
</head>
<body>
   
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
                    <a href="#facilities">Facilities</a>
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
                    <a href="Login.php"><span class="icon icon-login"></span>Login</a>
                    <a href="Registration.php" class="active"><span class="icon icon-register"></span>Register</a>
                    <a href="#about"><span class="icon icon-info"></span>About Us</a>
                    <a href="#contact"><span class="icon icon-contact"></span>Contact</a>
                </div>
            <?php endif; ?>
        </nav>
    </div>

  
    <main class="main-content">
        <div class="frame">
            <h2>Registration</h2>
            <form action="" method="post" autocomplete="off">
                <label for="Username"></label>
                <input type="text" name="Username" id="Username" placeholder="Username" required maxlength="20">
                <div class="help-text">Maximum 20 characters</div>
                
                <div class="name-container">
                    <input type="text" name="FirstName" id="FirstName" placeholder="First Name" required maxlength="50">
                    <input type="text" name="LastName" id="LastName" placeholder="Last Name" required maxlength="50">
                </div>
                
                <label for="Birthday">Date of Birth:</label>
                <input type="date" name="Birthday" id="Birthday" required min="1900-01-01" max="<?php echo date('Y-m-d'); ?>">
                
                <div class="gender-container">
                    <label>Gender:</label>
                    <input type="radio" name="Gender" value="Male" id="Male" required checked>
                    <label for="Male">Male</label>
                    <input type="radio" name="Gender" value="Female" id="Female" required>
                    <label for="Female">Female</label>
                    <input type="radio" name="Gender" value="Others" id="Others" required>
                    <label for="Others">Others</label>
                </div>
                <div id="otherGenderContainer" style="display: none;">
                    <input type="text" name="OtherGender" id="OtherGender" placeholder="Please specify your gender" maxlength="30">
                </div>

                <label for="LRN"></label>
                <input type="text" name="LRN" id="LRN" placeholder="Learner Reference Number (LRN)" required pattern="\d{12}" maxlength="12">
                <div class="help-text">Enter your 12-digit Learner Reference Number</div>
                
                <label for="Email"></label>
                <input type="email" name="Email" id="Email" placeholder="Email Address" required>
                
                <label for="Password"></label>
                <div class="input-container">
                    <input type="password" name="Password" id="Password" placeholder="Password" required minlength="8" 
                           pattern="^(?=.*[A-Za-z])(?=.*\d).{8,}$">
                    <span class="password-toggle" onclick="togglePasswordVisibility()">
                        <i class="fas fa-eye" id="togglePasswordIcon"></i>
                    </span>
                </div>
                <div class="help-text">At least 8 characters with letters and numbers. Special characters are also allowed.</div>

                <label for="ConfirmPassword"></label>
                <div class="input-container">
                    <input type="password" name="ConfirmPassword" id="ConfirmPassword" placeholder="Confirm Password" required>
                    <span class="password-match-indicator" id="passwordMatchIndicator"></span>
                </div>
                <div class="help-text" id="passwordMatchText">Passwords must match</div>

               
                <button type="submit" name="submit">Enter</button>
            </form>
            <br>
            <a href="login.php">Already have an account? Login</a>
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