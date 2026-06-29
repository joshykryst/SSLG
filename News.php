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


$check_table = mysqli_query($conn, "SHOW TABLES LIKE 'news_articles'");
if(mysqli_num_rows($check_table) == 0) {
   
    $create_table_query = "CREATE TABLE IF NOT EXISTS news_articles (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        content TEXT NOT NULL,
        image_url VARCHAR(255) NOT NULL,
        category VARCHAR(50) NOT NULL,
        author VARCHAR(100) NOT NULL,
        publish_date DATETIME DEFAULT CURRENT_TIMESTAMP,
        featured BOOLEAN DEFAULT 0
    )";
    
    if(!mysqli_query($conn, $create_table_query)) {
        die("Error creating news_articles table: " . mysqli_error($conn));
    }
}


$news = [];
$query = "SELECT * FROM news_articles ORDER BY publish_date DESC";
$result = mysqli_query($conn, $query);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $news[] = $row;
    }
}


$category_filter = "";
if(isset($_GET['category']) && !empty($_GET['category'])) {
    $category = mysqli_real_escape_string($conn, $_GET['category']);
    $category_filter = "WHERE category = '$category'";
    

    $query = "SELECT * FROM news_articles $category_filter ORDER BY publish_date DESC";
    $result = mysqli_query($conn, $query);
    
    $news = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $news[] = $row;
        }
    }
}


$categories = [];
$cat_query = "SELECT DISTINCT category FROM news_articles";
$cat_result = mysqli_query($conn, $cat_query);
if ($cat_result) {
    while ($cat_row = mysqli_fetch_assoc($cat_result)) {
        $categories[] = $cat_row['category'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>News - CMRICTHS</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        
        .news-hero {
            background-color: #1a2a3a;
            color: white;
            padding: 60px 0;
            text-align: center;
            margin-bottom: 40px;
        }
        
        .news-hero h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        
        .news-hero p {
            font-size: 1.1rem;
            max-width: 800px;
            margin: 0 auto;
            opacity: 0.9;
        }
        
        .news-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .news-filters {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            background-color: #f8f9fa;
            padding: 15px 20px;
            border-radius: 8px;
        }
        
        .filter-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .filter-group label {
            font-weight: 500;
            color: #333;
        }
        
        .filter-group select {
            padding: 8px 15px;
            border-radius: 5px;
            border: 1px solid #ddd;
            background-color: white;
            font-family: inherit;
        }
        
        .news-search {
            position: relative;
            width: 300px;
        }
        
        .news-search input {
            width: 100%;
            padding: 10px 15px;
            border-radius: 5px;
            border: 1px solid #ddd;
            padding-right: 40px;
        }
        
        .news-search button {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
        }
        
        .news-article {
            display: flex;
            margin-bottom: 40px;
            background-color: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .news-article:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }
        
        .article-image {
            flex: 0 0 35%;
            position: relative;
            overflow: hidden;
        }
        
        .article-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .news-article:hover .article-image img {
            transform: scale(1.05);
        }
        
        .article-category {
            position: absolute;
            top: 15px;
            left: 15px;
            background-color: #3498db;
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .article-content {
            flex: 0 0 65%;
            padding: 25px;
            position: relative;
        }
        
        .article-meta {
            display: flex;
            gap: 20px;
            margin-bottom: 15px;
            font-size: 0.9rem;
            color: #6c757d;
        }
        
        .article-meta span {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .article-title {
            font-size: 1.6rem;
            margin-bottom: 10px;
            color: #333;
            line-height: 1.3;
        }
        
        .article-excerpt {
            color: #555;
            margin-bottom: 20px;
            line-height: 1.6;
        }
        
        .read-more-btn {
            display: inline-block;
            padding: 8px 20px;
            background-color: #3498db;
            color: white;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 500;
            transition: background-color 0.3s ease;
        }
        
        .read-more-btn:hover {
            background-color: #2980b9;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 40px;
            gap: 10px;
        }
        
        .pagination a {
            display: inline-block;
            padding: 8px 16px;
            border: 1px solid #ddd;
            border-radius: 5px;
            text-decoration: none;
            color: #333;
            transition: all 0.3s ease;
        }
        
        .pagination a:hover,
        .pagination a.active {
            background-color: #3498db;
            color: white;
            border-color: #3498db;
        }
        
        .no-news {
            text-align: center;
            padding: 40px 20px;
            background-color: #f8f9fa;
            border-radius: 8px;
        }
        
        .no-news h3 {
            margin-bottom: 10px;
            color: #555;
        }
        
        .no-news p {
            color: #777;
        }
        
        @media (max-width: 768px) {
            .news-article {
                flex-direction: column;
            }
            
            .article-image, .article-content {
                flex: 0 0 100%;
            }
            
            .article-image {
                height: 200px;
            }
            
            .news-filters {
                flex-direction: column;
                gap: 15px;
            }
            
            .news-search {
                width: 100%;
            }
        }
        
        .sidebar {
            position: fixed;
            top: 70px; 
            right: -300px;
            width: 280px;
            height: calc(100vh - 70px); 
            background-color: white;
            box-shadow: -5px 0 15px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            transition: right 0.3s ease;
            overflow-y: auto;
        }
        
        .sidebar.active {
            right: 0;
        }
        
        .sidebar-header {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
            border-bottom: 1px solid #eee;
            position: relative;
        }
        
        .sidebar-logo {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 10px;
        }
        
        .close-btn {
            position: absolute;
            top: 15px;
            right: 15px;
            background: none;
            border: none;
            font-size: 20px;
            cursor: pointer;
            color: #777;
        }
        
        .nav-section {
            margin-bottom: 25px;
        }
        
        .nav-section h4 {
            font-size: 14px;
            margin-bottom: 10px;
            color: #777;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .sidebar-nav {
            padding: 20px;
        }
        
        .sidebar-nav a {
            display: flex;
            align-items: center;
            padding: 10px 15px;
            margin-bottom: 5px;
            color: #333;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s;
            font-weight: 500;
        }
        
        .sidebar-nav a:hover, 
        .sidebar-nav a.active {
            background-color: rgba(26, 35, 126, 0.05);
            color: #1a237e;
        }
        
        .sidebar-nav .icon {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        .logout-btn {
            background-color: #f8f9fa;
            margin-top: 10px;
            font-weight: 600;
            color: #dc3545 !important;
        }
        
        .logout-btn:hover {
            background-color: rgba(220, 53, 69, 0.1) !important;
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
                <a href="indexs.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'indexs.php' ? 'active' : ''; ?>">Home</a>
                <a href="about.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'about.php' ? 'active' : ''; ?>">About</a>
                <a href="Gallery.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'Gallery.php' ? 'active' : ''; ?>">Gallery</a>
                <a href="News.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'News.php' ? 'active' : ''; ?>">News & Events</a>
                <a href="contacts.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'contacts.php' ? 'active' : ''; ?>">Contact</a>
            </div>
        </div>
        
        <div class="nav-right">
            <?php if($isLoggedIn): ?>
                <div class="profile-trigger" onclick="toggleMenu()">
                    <img src="<?php echo !empty($userData['profile_picture']) ? 'uploads/profile/' . htmlspecialchars($userData['profile_picture']) : 'profile-default.jpg'; ?>" alt="Profile" class="profile-avatar">
                    <span class="profile-name"><?php echo htmlspecialchars($userData['Username']); ?></span>
                </div>
            <?php else: ?>
                <a href="Login.php" class="portal-btn">
                    <span class="icon icon-login"></span>Student Portal
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
           
            <div class="nav-section">
                <h4>Settings</h4>
                
                <a href="Logout.php" class="logout-btn">
                    <span class="icon icon-logout"></span>Logout
                </a>
            </div>
        <?php else: ?>
            <div class="nav-section">
                <h4>Menu</h4>
                <a href="Indexs.php"><span class="icon icon-home"></span>Home</a>
                <a href="about.php"><span class="icon icon-info"></span>About</a>
                <a href="Gallery.php"><span class="icon icon-gallery"></span>Gallery</a>
                <a href="Login.php"><span class="icon icon-login"></span>Login</a>
                <a href="Register.php"><span class="icon icon-register"></span>Register</a>
            </div>
        <?php endif; ?>
    </nav>
</div>

   
    <main class="main-content">
        <section class="news-hero">
            <h1>School News & Announcements</h1>
            <p>Stay updated with the latest happenings at CMRICTHS</p>
        </section>

        <div class="news-container">
            <div class="news-filters">
                <div class="filter-group">
                    <label for="category-filter">Filter by:</label>
                    <select id="category-filter" onchange="window.location.href=this.value">
                        <option value="News.php">All Categories</option>
                        <?php foreach($categories as $cat): ?>
                            <option value="News.php?category=<?php echo urlencode($cat); ?>" <?php echo (isset($_GET['category']) && $_GET['category'] == $cat) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars(ucfirst($cat)); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <form method="get" action="News.php" class="news-search">
                    <input type="text" name="search" placeholder="Search news..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                    <button type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
            </div>

            <?php if(count($news) > 0): ?>
                <?php foreach($news as $article): ?>
                    <article class="news-article">
                        <div class="article-image">
                            <img src="<?php echo htmlspecialchars($article['image_url']); ?>" alt="<?php echo htmlspecialchars($article['title']); ?>">
                            <div class="article-category">
                                <?php echo htmlspecialchars(ucfirst($article['category'])); ?>
                            </div>
                        </div>
                        <div class="article-content">
                            <div class="article-meta">
                                <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($article['author']); ?></span>
                                <span><i class="fas fa-calendar"></i> <?php echo date('M d, Y', strtotime($article['publish_date'])); ?></span>
                            </div>
                            <h2 class="article-title"><?php echo htmlspecialchars($article['title']); ?></h2>
                            <div class="article-excerpt">
                                <?php 
                                    $excerpt = strip_tags($article['content']);
                                    echo htmlspecialchars(substr($excerpt, 0, 200)) . (strlen($excerpt) > 200 ? '...' : '');
                                ?>
                            </div>
                            <a href="NewsDetail.php?id=<?php echo $article['id']; ?>" class="read-more-btn">Read More</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-news">
                    <h3>No News Articles Found</h3>
                    <p>There are currently no news articles in this category. Please check back later.</p>
                </div>
            <?php endif; ?>

            
        </div>
    </main>


    <footer class="footer">
        <div class="footer-top">
            <div class="container footer-grid">
                <div class="footer-section">
                    <img src="logo.png" alt="CMRICTHS Logo" class="footer-logo">
                    <h3>CMRICTHS</h3>
                    <p>Empowering students through quality ICT education and innovation.</p>
                    <div class="footer-social">
                        <a href="#"><img src="fb.png" alt="Facebook" class="social-icon"></a>
                        <a href="#"><img src="ig.png" alt="Instagram" class="social-icon"></a>
                        <a href="#"><img src="tt.png" alt="TikTok" class="social-icon"></a>
                    </div>
                </div>

                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="indexs.php">Home</a></li>
                        <li><a href="about.php">About</a></li>
                        <li><a href="Gallery.php">Gallery</a></li>
                        <li><a href="News.php">News & Events</a></li>
                        <li><a href="contacts.php">Contacts</a></li>
                    </ul>
                </div>

                <div class="footer-section">
                    <h4>Student Resources</h4>
                    <ul>
                        <li><a href="Login.php">Student Portal</a></li>
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
                        <li>Monday - Friday: 7:00 AM - 5:00 PM</li>
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

    <script>
    function toggleMenu() {
        const sidebar = document.getElementById('sidebar');
        sidebar.classList.toggle('active');
        
 
        if (sidebar.classList.contains('active')) {
            setTimeout(() => {
                document.addEventListener('click', function closeMenu(e) {
                    const menu = document.getElementById('sidebar');
                    const profileTrigger = document.querySelector('.profile-trigger');
                    
                    if (menu && 
                        !menu.contains(e.target) &&
                        (!profileTrigger || !profileTrigger.contains(e.target))) {
                        menu.classList.remove('active');
                        document.removeEventListener('click', closeMenu);
                    }
                });
            }, 100);
        }
    }
</script>
</body>
</html></html>