<?php
session_start();
require 'Config.php';


$isLoggedIn = false;
$userData = null;

if(!empty($_SESSION["User_ID"])){
    $User_ID = $_SESSION["User_ID"];
    $result = mysqli_query($conn, "SELECT * FROM table_user 
        WHERE User_ID = '" . mysqli_real_escape_string($conn, $User_ID) . "'");
    
    if($result && mysqli_num_rows($result) > 0){
        $userData = mysqli_fetch_assoc($result);
        $isLoggedIn = true;
    } else {
        session_unset();
        session_destroy();
        header("Location: Login.php");
        exit();
    }
}


if(!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: News.php");
    exit();
}

$article_id = (int)$_GET['id'];


$article = null;
$query = "SELECT * FROM news_articles WHERE id = $article_id";
$result = mysqli_query($conn, $query);

if($result && mysqli_num_rows($result) > 0) {
    $article = mysqli_fetch_assoc($result);
} else {
    header("Location: News.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($article['title']); ?> - CMRICTHS News</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <style>
        .article-hero {
            position: relative;
            height: 400px;
            background-color: #1a2a3a;
            overflow: hidden;
        }
        
        .article-hero img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            opacity: 0.7;
        }
        
        .article-hero-content {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 40px;
            background: linear-gradient(transparent, rgba(0,0,0,0.8));
            color: white;
        }
        
        .article-hero-title {
            font-size: 2.5rem;
            margin-bottom: 10px;
            max-width: 80%;
        }
        
        .article-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        
        .article-meta {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
            font-size: 0.95rem;
            color: #6c757d;
            border-bottom: 1px solid #eee;
            padding-bottom: 20px;
        }
        
        .article-category {
            background-color: #3498db;
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            display: inline-block;
        }
        
        .article-content {
            line-height: 1.8;
            color: #333;
            font-size: 1.1rem;
            white-space: pre-line;
        }
        
        .back-btn {
            display: inline-block;
            margin-top: 40px;
            padding: 10px 20px;
            background-color: #3498db;
            color: white;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 500;
            transition: background-color 0.3s ease;
        }
        
        .back-btn:hover {
            background-color: #2980b9;
        }
        
        @media (max-width: 768px) {
            .article-hero {
                height: 300px;
            }
            
            .article-hero-title {
                font-size: 1.8rem;
            }
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
                    <a href="Indexs.php">Home</a>
                    <a href="about.php">About</a>
                    <a href="Gallery.php">Gallery</a>
                    <a href="#admissions">Admissions</a>
                    <a href="News.php" class="active">News & Events</a>
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
                    <a href="Register.php"><span class="icon icon-register"></span>Register</a>
                    <a href="#about"><span class="icon icon-info"></span>About Us</a>
                    <a href="#contact"><span class="icon icon-contact"></span>Contact</a>
                </div>
            <?php endif; ?>
        </nav>
    </div>


    <main class="main-content">
        <div class="article-hero">
            <img src="<?php echo htmlspecialchars($article['image_url']); ?>" alt="<?php echo htmlspecialchars($article['title']); ?>">
            <div class="article-hero-content">
                <h1 class="article-hero-title"><?php echo htmlspecialchars($article['title']); ?></h1>
            </div>
        </div>
        
        <div class="article-container">
            <div class="article-meta">
                <span class="article-category"><?php echo htmlspecialchars(ucfirst($article['category'])); ?></span>
                <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($article['author']); ?></span>
                <span><i class="fas fa-calendar"></i> <?php echo date('F d, Y', strtotime($article['publish_date'])); ?></span>
            </div>
            
            <div class="article-content">
                <?php echo nl2br(htmlspecialchars($article['content'])); ?>
            </div>
            
            <a href="News.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back to News
            </a>
        </div>
    </main>

 
    <footer class="footer">
        <div class="footer-bottom">
            <div class="container">
                <p>&copy; <?php echo date('Y'); ?> CMRICTHS. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        function toggleMenu() {
            const sidebar = document.getElementById('sidebar');
            const body = document.body;
            
            
            sidebar.classList.toggle('active');
            
            
            if (window.innerWidth < 992) {
                let overlay = document.querySelector('.sidebar-overlay');
                if (!overlay) {
                    overlay = document.createElement('div');
                    overlay.className = 'sidebar-overlay';
                    overlay.addEventListener('click', toggleMenu);
                    document.body.appendChild(overlay);
                }
                
               
                if (sidebar.classList.contains('active')) {
                    overlay.classList.add('active');
                    body.classList.add('sidebar-open');
                } else {
                    overlay.classList.remove('active');
                    body.classList.remove('sidebar-open');
                }
            }
        }
        
      
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