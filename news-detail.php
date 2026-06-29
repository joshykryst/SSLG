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
    }
}


$news_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$newsQuery = "SELECT news.*, CONCAT(admin.firstName, ' ', admin.lastName) AS author_name 
             FROM news 
             LEFT JOIN admin ON news.author_id = admin.id 
             WHERE news.id = $news_id";
$newsResult = mysqli_query($conn, $newsQuery);
$newsArticle = $newsResult ? mysqli_fetch_assoc($newsResult) : null;


$announcementsQuery = "SELECT * FROM announcements ORDER BY date DESC LIMIT 5";
$announcementsResult = mysqli_query($conn, $announcementsQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $newsArticle ? htmlspecialchars($newsArticle['title']) : 'News Article'; ?> - CMRICTHS</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="news-styles.css">
    <style>
       
        .news-detail-content {
            padding: 2rem;
            line-height: 1.8;
        }
        
        .news-detail-header {
            margin-bottom: 2rem;
        }
        
        .news-detail-image {
            width: 100%;
            height: auto;
            max-height: 500px;
            object-fit: cover;
            border-radius: 10px;
            margin-bottom: 2rem;
        }
        
        .news-detail-meta {
            display: flex;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
            color: #666;
            font-size: 0.9rem;
        }
        
        .news-detail-meta div {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .news-content {
            margin-bottom: 3rem;
        }
        
        .news-content p {
            margin-bottom: 1.5rem;
        }
        
        .news-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin: 2rem 0;
        }
        
        .news-tag {
            background: #f0f0f0;
            color: #666;
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .news-tag:hover {
            background: var(--primary);
            color: white;
        }
        
        .news-share {
            border-top: 1px solid #eee;
            padding-top: 2rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 3rem;
        }
        
        .share-title {
            font-weight: 500;
            margin-right: 1rem;
        }
        
        .share-links {
            display: flex;
            gap: 0.8rem;
        }
        
        .share-link {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-decoration: none;
            transition: transform 0.3s ease;
        }
        
        .share-link:hover {
            transform: translateY(-3px);
        }
        
        .related-news {
            margin-bottom: 3rem;
        }
        
        .related-news h3 {
            color: var(--primary);
            margin-bottom: 1.5rem;
            padding-bottom: 0.8rem;
            border-bottom: 1px solid #eee;
        }
        
        .related-news-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }
        
        .related-item {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            background: white;
            transition: transform 0.3s ease;
        }
        
        .related-item:hover {
            transform: translateY(-5px);
        }
        
        .related-thumb {
            height: 150px;
            overflow: hidden;
        }
        
        .related-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        
        .related-item:hover .related-thumb img {
            transform: scale(1.05);
        }
        
        .related-content {
            padding: 1rem;
        }
        
        .related-content h4 {
            font-size: 1rem;
            margin-bottom: 0.5rem;
        }
        
        .related-content h4 a {
            color: var(--primary);
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .related-content h4 a:hover {
            color: var(--secondary);
        }
        
        .related-date {
            color: #666;
            font-size: 0.8rem;
        }
        
        @media (max-width: 768px) {
            .news-detail-meta {
                flex-direction: column;
                gap: 0.5rem;
            }
            
            .related-news-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
  
    <nav class="main-nav">
        <div class="nav-container">
            <div class="nav-left">
                <a href="Indexs.php" class="nav-brand">
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
                    <a href="News.php" class="active">News</a>
                    <a href="#admissions">Admissions</a>
                    <a href="contacts.php">Contact</a>
                </div>
            </div>
            <div class="nav-right">
                <?php if($isLoggedIn): ?>
                    <button class="portal-btn logged-in" onclick="toggleMenu()">
                        <span class="icon icon-user"></span>
                        <?php echo htmlspecialchars($userData['Username']); ?>
                    </button>
                <?php else: ?>
                    <a href="Login.php" class="portal-btn">
                        <span class="icon icon-login"></span>
                        Student Portal
                    </a>
                <?php endif; ?>
                <button class="menu-btn" onclick="toggleMenu()">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
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
    
 
    <div class="main-content">
        <?php if($newsArticle): ?>
           
            <section class="news-container">
                <div class="container">
                    <div class="news-content-wrapper">
                       
                        <div class="news-main-content">
                            <div class="news-article">
                                <a href="News.php" class="back-link">&larr; Back to News</a>
                                
                                <div class="article-header">
                                    <span class="news-category"><?php echo htmlspecialchars($newsArticle['category']); ?></span>
                                    <h1><?php echo htmlspecialchars($newsArticle['title']); ?></h1>
                                    <div class="news-meta">
                                        <span class="news-date"><?php echo date("F j, Y", strtotime($newsArticle['publish_date'])); ?></span>
                                        <?php if (!empty($newsArticle['author_name'])): ?>
                                        <span class="news-author">by <?php echo htmlspecialchars($newsArticle['author_name']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <?php if(!empty($newsArticle['image_url'])): ?>
                                <div class="article-image">
                                    <img src="<?php echo htmlspecialchars($newsArticle['image_url']); ?>" alt="<?php echo htmlspecialchars($newsArticle['title']); ?>">
                                </div>
                                <?php endif; ?>
                                
                                <div class="article-content">
                                    <?php echo nl2br(htmlspecialchars($newsArticle['content'])); ?>
                                </div>
                                
                            
                                <div class="social-share">
                                    <h4>Share this article:</h4>
                                    <div class="share-buttons">
                                        <a href="#" class="share-btn facebook">Facebook</a>
                                        <a href="#" class="share-btn twitter">Twitter</a>
                                        <a href="#" class="share-btn email">Email</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                       
                        <aside class="news-sidebar">
                         
                           
                            <div class="sidebar-widget search-widget">
                                <form action="news-search.php" method="GET">
                                    <input type="text" name="q" placeholder="Search news...">
                                    <button type="submit"><span class="icon icon-search">🔍</span></button>
                                </form>
                            </div>
                            
                            
                             
                            <div class="sidebar-widget recent-posts-widget">
                                <h3 class="widget-title">Recent News</h3>
                                <ul>
                                    <?php
                                    $recentQuery = "SELECT * FROM news WHERE id != $news_id ORDER BY publish_date DESC LIMIT 5";
                                    $recentResult = mysqli_query($conn, $recentQuery);
                                    
                                    if ($recentResult && mysqli_num_rows($recentResult) > 0) {
                                        while($recent = mysqli_fetch_assoc($recentResult)) {
                                    ?>
                                    <li>
                                        <div class="post-date"><?php echo date("M j", strtotime($recent['publish_date'])); ?></div>
                                        <h4><a href="news-detail.php?id=<?php echo $recent['id']; ?>"><?php echo htmlspecialchars($recent['title']); ?></a></h4>
                                    </li>
                                    <?php
                                        }
                                    } else {
                                    ?>
                                    <li>
                                        <div class="post-date">Mar 15</div>
                                        <h4><a href="#">No recent articles found</a></h4>
                                    </li>
                                    <?php
                                    }
                                    ?>
                                </ul>
                            </div>
                            
                            
                            <div class="sidebar-widget newsletter-widget">
                                <h3 class="widget-title">Subscribe to Updates</h3>
                                <p>Get the latest news and announcements delivered to your inbox</p>
                                <form action="subscribe.php" method="POST">
                                    <input type="email" name="email" placeholder="Your email address" required>
                                    <button type="submit" class="btn-subscribe">Subscribe</button>
                                </form>
                            </div>
                        </aside>
                    </div>
                </div>
            </section>
        <?php else: ?>
            
            <section class="news-container">
                <div class="container">
                    <div class="not-found">
                        <h1>Article Not Found</h1>
                        <p>The article you're looking for does not exist or has been removed.</p>
                        <a href="News.php" class="back-link">Return to News Page</a>
                    </div>
                </div>
            </section>
        <?php endif; ?>
    </div>
    

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
                    </ul>
                </div>

                <div class="footer-section">
                    <h4>Student Resources</h4>
                    <ul>
                        <li><a href="#portal">Student Portal</a></li>
                        <li><a href="#library">E-Library</a></li>
                        <li><a href="#calendar">Academic Calendar</a></li>
                        <li><a href="#handbook">Student Handbook</a></li>
                    </ul>
                </div>

                <div class="footer-section">
                    <h4>Contact Information</h4>
                    <ul class="contact-info">
                        <li>123 School Street, Barangay, City</li>
                        <li>(123) 456-7890</li>
                        <li>info@cmricths.edu.ph</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="footer-bottom">
            <div class="container">
                <p>&copy; <?php echo date('Y'); ?> CMRICTHS. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="script.js"></script>
    <script src="news-script.js"></script>
</body>
</html>