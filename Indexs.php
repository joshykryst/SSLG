<?php
session_start();
require 'Config.php';


error_log("Session data: " . print_r($_SESSION, true));


$isLoggedIn = false;
$userData = null;

if(!empty($_SESSION["User_ID"])){
    $User_ID = $_SESSION["User_ID"];
    
    $result = mysqli_query($conn, "SELECT * FROM table_user 
        WHERE User_ID = '" . mysqli_real_escape_string($conn, $User_ID) . "'");
    
    if($result && mysqli_num_rows($result) > 0){
        $userData = mysqli_fetch_assoc($result);
        $isLoggedIn = true;
        
        error_log("User data found: " . print_r($userData, true));
    } else {
        error_log("No user found for ID: " . $User_ID);
        
        session_unset();
        session_destroy();
        header("Location: Login.php");
        exit();
    }
}


$latestNews = [];
$check_table = mysqli_query($conn, "SHOW TABLES LIKE 'news_articles'");
if(mysqli_num_rows($check_table) > 0) {
    $query = "SELECT * FROM news_articles ORDER BY publish_date DESC LIMIT 3";
    $result = mysqli_query($conn, $query);
    
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $latestNews[] = $row;
        }
    }
}

echo "<!-- Debug Info:";
echo "Session: "; print_r($_SESSION);
echo "IsLoggedIn: "; var_dump($isLoggedIn);
echo "UserData: "; print_r($userData);
echo "-->";


if($isLoggedIn) {
    error_log("User logged in: " . print_r($userData, true));
} else {
    error_log("No user logged in");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CMRICTHS - Information and Communication Technology High School</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <style>
        
        .announcement-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
            margin-top: 20px;
        }
        
        .announcement-card {
            background-color: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        
        .announcement-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }
        
        .announcement-image {
            height: 180px;
            overflow: hidden;
            position: relative;
        }
        
        .announcement-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .announcement-card:hover .announcement-image img {
            transform: scale(1.05);
        }
        
        .announcement-date {
            position: absolute;
            top: 15px;
            left: 15px;
            background-color: #3498db;
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            z-index: 1;
        }
        
        .announcement-content {
            padding: 20px;
            position: relative;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }
        
        .announcement-category {
            display: inline-block;
            background-color: #f0f0f0;
            padding: 4px 10px;
            border-radius: 15px;
            font-size: 0.75rem;
            color: #555;
            margin-bottom: 10px;
        }
        
        .announcement-content h3 {
            font-size: 1.2rem;
            margin-bottom: 10px;
            color: #333;
            line-height: 1.3;
        }
        
        .announcement-content p {
            color: #555;
            margin-bottom: 15px;
            font-size: 0.9rem;
            line-height: 1.5;
            flex-grow: 1;
        }
        
        .read-more {
            display: inline-block;
            padding: 8px 20px;
            background-color: #3498db;
            color: white;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 500;
            transition: background-color 0.3s ease;
            text-align: center;
            align-self: flex-start;
            margin-top: auto;
        }
        
        .read-more:hover {
            background-color: #2980b9;
        }
        
        .no-news {
            grid-column: span 3;
            text-align: center;
            padding: 30px;
            background-color: #f8f9fa;
            border-radius: 8px;
        }
        
       
        .announcement-date .day {
            font-size: 1.2rem;
            font-weight: 700;
            display: block;
            text-align: center;
        }
        
        .announcement-date .month {
            font-size: 0.8rem;
            display: block;
            text-align: center;
        }
        
        
        .view-all-container {
            text-align: center;
            margin-top: 30px;
        }
        
        .view-all-btn {
            display: inline-block;
            padding: 10px 25px;
            background-color: #3498db;
            color: white;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 500;
            transition: background-color 0.3s ease;
        }
        
        .view-all-btn:hover {
            background-color: #2980b9;
        }
    </style>
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
                    <a href="Indexs.php" class="active">Home</a>
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
                    
                    <a href="grades.php"><span class="icon icon-grades"></span>Grades</a>
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

    
    <main class="main-content">
        <header class="hero">
            <div class="hero-content">
                <h1>Welcome to CMRICTHS</h1>
                <p>Empowering Future Tech Leaders</p>
            </div>
        </header>

        <section class="features">
            <div class="feature-card">
                <i class="icon icon-graduation"></i>
                <h3>Academic Excellence</h3>
                <p>Quality ICT education for future professionals</p>
            </div>
            <div class="feature-card">
                <i class="icon icon-laptop"></i>
                <h3>Modern Facilities</h3>
                <p>State-of-the-art computer laboratories</p>
            </div>
            <div class="feature-card">
                <i class="icon icon-users"></i>
                <h3>Expert Faculty</h3>
                <p>Learn from industry professionals</p>
            </div>
        </section>

        <section class="announcements">
            <h2>Latest Announcements</h2>
            <div class="announcement-grid">
                <?php if(count($latestNews) > 0): ?>
                    <?php foreach($latestNews as $article): ?>
                        <div class="announcement-card">
                            <div class="announcement-image">
                                <img src="<?php echo htmlspecialchars($article['image_url']); ?>" alt="<?php echo htmlspecialchars($article['title']); ?>">
                                <span class="announcement-date"><?php echo date('d M', strtotime($article['publish_date'])); ?></span>
                            </div>
                            <div class="announcement-content">
                                <span class="announcement-category"><?php echo htmlspecialchars(ucfirst($article['category'])); ?></span>
                                <h3><?php echo htmlspecialchars($article['title']); ?></h3>
                                <p>
                                    <?php 
                                        $excerpt = strip_tags($article['content']);
                                        echo htmlspecialchars(substr($excerpt, 0, 100)) . (strlen($excerpt) > 100 ? '...' : '');
                                    ?>
                                </p>
                                <a href="NewsDetail.php?id=<?php echo $article['id']; ?>" class="read-more">Read More</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="announcement-card">
                        <div class="announcement-image" style="background-color: #3498db; display: flex; align-items: center; justify-content: center;">
                            <span class="announcement-date">
                                <span class="day">15</span>
                                <span class="month">MAR</span>
                            </span>
                        </div>
                        <div class="announcement-content">
                            <span class="announcement-category">Enrollment</span>
                            <h3>Enrollment Now Open</h3>
                            <p>School Year 2024-2025 enrollment is now open for new and returning students.</p>
                            <a href="#" class="read-more">Read More</a>
                        </div>
                    </div>
                    <div class="announcement-card">
                        <div class="announcement-image" style="background-color: #2ecc71; display: flex; align-items: center; justify-content: center;">
                            <span class="announcement-date">
                                <span class="day">20</span>
                                <span class="month">MAR</span>
                            </span>
                        </div>
                        <div class="announcement-content">
                            <span class="announcement-category">Competition</span>
                            <h3>ICT Skills Competition</h3>
                            <p>Annual ICT Skills Competition registration starts next week.</p>
                            <a href="#" class="read-more">Read More</a>
                        </div>
                    </div>
                    <div class="announcement-card">
                        <div class="announcement-image" style="background-color: #9b59b6; display: flex; align-items: center; justify-content: center;">
                            <span class="announcement-date">
                                <span class="day">25</span>
                                <span class="month">MAR</span>
                            </span>
                        </div>
                        <div class="announcement-content">
                            <span class="announcement-category">Event</span>
                            <h3>Parents' Orientation</h3>
                            <p>Online orientation for parents of new students.</p>
                            <a href="#" class="read-more">Read More</a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <div class="view-all-container">
                <a href="News.php" class="view-all-btn">View All News & Events</a>
            </div>
        </section>

        <section class="image-showcase">
            <div class="showcase-text">
                <h2>Life at CMRICTHS</h2>
                <p>Experience excellence in ICT education with our state-of-the-art facilities and dynamic learning environment.</p>
            </div>
            <div class="showcase-grid">
                <div class="showcase-item">
                    <img src="comlab.jpg" alt="Computer Laboratory">
                    <div class="showcase-overlay">
                        <h3>Modern Computer Labs</h3>
                    </div>
                </div>
                <div class="showcase-item">
                    <img src="student.jpg" alt="Student Activities">
                    <div class="showcase-overlay">
                        <h3>Student Life</h3>
                    </div>
                    
                </div>
                <div class="showcase-item">
                    <img src="event.jpg" alt="School Events">
                    <div class="showcase-overlay">
                        <h3>School Events</h3>
                    </div>
                </div>
                <div class="showcase-item">
                    <img src="room.jpg" alt="School Facilities">
                    <div class="showcase-overlay">
                        <h3>Facilities</h3>
                    </div>
                </div>
            </div>
        </section>
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
                        <li><a href="Indexs.php">Home</a></li>
                        <li><a href="about.php">About</a></li>
                        <li><a href="gallery.php">Gallery</a></li>
                        <li><a href="news.php">News & Events</a></li>
                        <li><a href="contacts.php">Contacts</a></li>
                    </ul>
                </div>

                <div class="footer-section">
                   
                </div>

                <div class="footer-section">
                    <h4>Contact Information</h4>
                    <ul class="contact-info">
                        <li>
                            <span class="icon icon-location"></span>
                            Dona Aurora, Claro M. Recto, Angeles City, Pampanga, Philippines
                        </li>
                        <li>
                            <span class="icon icon-phone"></span>
                            (045) 887 5502
                        </li>
                        <li>
                            <span class="icon icon-envelope"></span>
                            cmricthsangelescity@yahoo.com
                        </li>
                        <li>
                            <span class="icon icon-clock"></span>
                            Monday - Friday: 6:00 AM - 6:00 PM
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
</body>
</html>
