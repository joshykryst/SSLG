<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'Config.php';

$isLoggedIn = false;
$userData = null;
$grades = array();
$academicStanding = '';
$summaryData = array();
$scheduleData = array();

if(!empty($_SESSION["User_ID"])){
    $User_ID = $_SESSION["User_ID"];
    
    $result = mysqli_query($conn, "SELECT * FROM table_user WHERE User_ID = '" . mysqli_real_escape_string($conn, $User_ID) . "'");
    
    if($result && mysqli_num_rows($result) > 0){
        $userData = mysqli_fetch_assoc($result);
        $isLoggedIn = true;

        // Add this near the top of your file after getting user data
        if($isLoggedIn) {
            // Debug output (remove in production)
            error_log("Profile picture path: " . ($userData['profile_picture'] ?? 'Not set'));
            
            // Check if the profile picture exists
            if(!empty($userData['profile_picture'])) {
                if(!file_exists($userData['profile_picture'])) {
                    error_log("Profile picture file not found: " . $userData['profile_picture']);
                }
            }
        }

        $quarter = isset($_GET['quarter']) ? (int)$_GET['quarter'] : 1;

        $summaryData = [
            'overall_average' => 0,
            'total_subjects' => 0
        ];

        $summaryQuery = "SELECT 
            COALESCE(ROUND(AVG(grade), 2), 0) as overall_average,
            COUNT(DISTINCT subject_name) as total_subjects
            FROM grades 
            WHERE User_ID = '" . mysqli_real_escape_string($conn, $User_ID) . "'";
        
        $summaryResult = mysqli_query($conn, $summaryQuery);
        if (!$summaryResult) {
            error_log("Database error in Grades.php: " . mysqli_error($conn));
        }

        if ($summaryResult && mysqli_num_rows($summaryResult) > 0) {
            $summaryData = mysqli_fetch_assoc($summaryResult);
        }

        $gradesQuery = "SELECT g.*, 
                        CASE WHEN g.grade >= 75 THEN 'Passed' ELSE 'Failed' END as remarks 
                        FROM grades g
                        WHERE g.User_ID = '" . mysqli_real_escape_string($conn, $User_ID) . "'
                        AND g.quarter = $quarter 
                        ORDER BY g.subject_name";
        
        $gradesResult = mysqli_query($conn, $gradesQuery);
        if ($gradesResult) {
            while ($row = mysqli_fetch_assoc($gradesResult)) {
                $grades[] = $row;
            }
        }

        $average = floatval($summaryData['overall_average']);
        if ($average >= 98) {
            $academicStanding = 'With Highest Honors';
        } elseif ($average >= 95) {
            $academicStanding = 'With High Honors';
        } elseif ($average >= 90) {
            $academicStanding = 'With Honors';
        } else {
            $academicStanding = $average > 0 ? 'Passed' : 'No Grades Yet';
        }

        
        $gradeLevel = $userData['grade_level'] ?? 'Grade 7';
        $section = $userData['section'] ?? 'A';
        
        
        $scheduleQuery = "SELECT * FROM class_schedules 
                         WHERE grade_level = '$gradeLevel' 
                         AND section_name = '$section' 
                         ORDER BY FIELD(day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'), 
                         time_slot";
        $scheduleResult = mysqli_query($conn, $scheduleQuery);
        
        if ($scheduleResult) {
            while ($row = mysqli_fetch_assoc($scheduleResult)) {
                $scheduleData[] = $row;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Grades - CMRICTHS</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="dashboard.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="grades.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        /* Override styles to match schedule.php */
        .profile-avatar {
            border-radius: 0; /* Remove rounded corners on profile image */
            border: none;     /* Remove border */
            box-shadow: none; /* Remove shadow */
            width: 40px;      /* Match size to schedule.php */
            height: 40px;
            object-fit: cover; /* Ensure image covers the area without distortion */
            display: block;    /* Ensure it's treated as a block element */
        }
        
       
        .profile-trigger {
            background-color: transparent;
            padding: 5px;               
            border-radius: 0;             
            transition: none;             
            border: 1px dashed transparent; 
        }
        
        .profile-trigger:hover {
            background-color: transparent; 
            border-color: #ccc;            
        }
        
    
        .sidebar {
            top: 0;
            right: -320px;
            width: 300px;
        }
        
       
        .nav-links a {
            padding: 0.5rem 0.8rem;
        }
        
        
        .nav-right {
            margin-left: auto;
        }
    </style>
</head>
<body>
    
    <nav class="main-nav">
        <div class="nav-container">
            <div class="nav-left">
                <a href="indexs.php" class="nav-brand">
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
                    <a href="News.php">News & Events</a>
                    <a href="contacts.php">Contact</a>
                </div>
            </div>
            <div class="nav-right">
                <?php if($isLoggedIn): ?>
                    <div class="nav-profile">
                        <div class="profile-dropdown">
                            <div class="profile-trigger" onclick="toggleMenu()">
                                <img src="<?php echo !empty($userData['profile_picture']) ? 'uploads/profile/' . htmlspecialchars($userData['profile_picture']) : 'profile-default.jpg'; ?>" 
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
                <img src="<?php echo !empty($userData['profile_picture']) ? 'uploads/profile/' . htmlspecialchars($userData['profile_picture']) : 'profile-default.jpg'; ?>" alt="User Profile" class="sidebar-logo">
                <h3>Welcome, <?php echo htmlspecialchars($userData['Username']); ?></h3>
            <?php else: ?>
                <img src="profile-default.jpg" alt="Guest Profile" class="sidebar-logo">
                <h3>Welcome, Guest</h3>
            <?php endif; ?>
            <button class="close-btn" onclick="toggleMenu()">
                <span class="icon icon-close"></span>
            </button>
        </div>
        
        <nav class="sidebar-nav">
            <?php if($isLoggedIn): ?>
                <div class="nav-section">
                    <h4>Quick Links</h4>
                    <a href="dashboard.php"><span class="icon icon-dashboard"></span>Dashboard</a>
                    
                    <a href="grades.php" class="active"><span class="icon icon-grades"></span>Grades</a>
                    <a href="schedule.php"><span class="icon icon-calendar"></span>Schedule</a>
                </div>
               
                <div class="nav-section">
                    <h4>Settings</h4>
                    
                    <a href="Logout.php" class="logout-btn">
                        <span class="icon icon-logout"></span>Logout
                    </a>
                </div>
            <?php else: ?>
                <div class="nav-section">
                    <h4>Menu</h4>
                    <a href="Login.php"><span class="icon icon-login"></span>Login</a>
                    <a href="Register.php"><span class="icon icon-register"></span>Register</a>
                    <a href="#about"><span class="icon icon-info"></span>About Us</a>
                    <a href="#contact"><span class="icon icon-contact"></span>Contact</a>
                </div>
            <?php endif; ?>
        </nav>
    </div>

    <div class="sidebar-overlay" onclick="toggleMenu()"></div>

    <main class="main-content">
        <?php include 'grades_content.php'; ?>
    </main>

    <footer class="footer">
        <div class="footer-top">
            <div class="container footer-grid">
                <div class="footer-section">
                    <img src="logo.png" alt="CMRICTHS Logo" class="footer-logo">
                    <h3>CMRICTHS</h3>
                    <p>Empowering students through quality ICT education and innovation.</p>
                    <div class="footer-social">
                        <a href="https://www.facebook.com/cmricthsunleashed"><img src="fb.png" alt="Facebook" class="social-icon"></a>
                        <a href="https://www.instagram.com/cmricths.sslg/"><img src="ig.png" alt="Instagram" class="social-icon"></a>
                        <a href="https://www.tiktok.com/@cmricths.sslg"><img src="tt.png" alt="TikTok" class="social-icon"></a>
                        <a href="https://www.facebook.com/CMRICTHSSSLG"><img src="fb.png" alt="Facebook Group" class="social-icon"></a>
                    </div>
                </div>

                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="#about">About Us</a></li>
                        <li><a href="#admissions">Admissions</a></li>
                        <li><a href="#academics">Academic Programs</a></li>
                        <li><a href="#facilities">Facilities</a></li>
                        <li><a href="#news">News & Events</a></li>
                        <li><a href="#careers">Careers</a></li>
                    </ul>
                </div>

                <div class="footer-section">
                    <h4>Student Resources</h4>
                    <ul>
                        <li><a href="#portal">Student Portal</a></li>
                        <li><a href="#library">E-Library</a></li>
                        <li><a href="#calendar">Academic Calendar</a></li>
                        <li><a href="#handbook">Student Handbook</a></li>
                        <li><a href="#downloads">Downloads</a></li>
                    </ul>
                </div>

                <div class="footer-section">
                    <h4>Contact Information</h4>
                    <ul class="contact-info">
                        <li>
                            <span class="icon icon-location"></span>
                            123 School Street, Barangay, City
                        </li>
                        <li>
                            <span class="icon icon-phone"></span>
                            (123) 456-7890
                        </li>
                        <li>
                            <span class="icon icon-envelope"></span>
                            info@cmricths.edu.ph
                        </li>
                        <li>
                            <span class="icon icon-clock"></span>
                            Monday - Friday: 7:00 AM - 5:00 PM
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="footer-bottom">
            <div class="container">
                <p>&copy; <?php echo date('Y'); ?> CMRICTHS. All rights reserved.</p>
                <div class="footer-links">
                    <a href="#privacy">Privacy Policy</a>
                    <a href="#terms">Terms of Use</a>
                    <a href="#sitemap">Sitemap</a>
                </div>
            </div>
        </div>
    </footer>

    <script src="script.js"></script>
    <script src="grades.js"></script>
    <script>
        function toggleMenu() {
            const sidebar = document.getElementById('sidebar');
            const body = document.body;
            
            // Toggle active class on sidebar
            sidebar.classList.toggle('active');
            
            // For mobile devices, handle overlay
            if (window.innerWidth < 992) {
                let overlay = document.querySelector('.sidebar-overlay');
                if (!overlay) {
                    overlay = document.createElement('div');
                    overlay.className = 'sidebar-overlay';
                    overlay.addEventListener('click', toggleMenu);
                    document.body.appendChild(overlay);
                }
                
                // Toggle overlay and body class
                if (sidebar.classList.contains('active')) {
                    overlay.classList.add('active');
                    body.classList.add('sidebar-open');
                } else {
                    overlay.classList.remove('active');
                    body.classList.remove('sidebar-open');
                }
            }
        }
        
        // Close sidebar when Escape key is pressed
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                const sidebar = document.getElementById('sidebar');
                if (sidebar && sidebar.classList.contains('active')) {
                    toggleMenu();
                }
            }
        });
    </script>
</body>
</html>
