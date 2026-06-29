<?php
session_start();
require 'Config.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

$isLoggedIn = false;
$isAdmin = false;
$userData = null;

if(!empty($_SESSION["User_ID"])){
    $User_ID = $_SESSION["User_ID"];
    $result = mysqli_query($conn, "SELECT * FROM table_user WHERE User_ID = '" . mysqli_real_escape_string($conn, $User_ID) . "'");
    
    if($result && mysqli_num_rows($result) > 0){
        $userData = mysqli_fetch_assoc($result);
        $isLoggedIn = true;
        $isAdmin = isset($userData['is_admin']) && $userData['is_admin'] == 1;
    }
}


$collection_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if(!$collection_id) {
    header("Location: gallery.php");
    exit;
}


$collection = null;
$collection_query = "SELECT * FROM collections WHERE id = " . mysqli_real_escape_string($conn, $collection_id);
$collection_result = $conn->query($collection_query);

if($collection_result && $collection_result->num_rows > 0) {
    $collection = $collection_result->fetch_assoc();
} else {
    header("Location: gallery.php?error=collection_not_found");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($collection['name']); ?> - CMRICTHS</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="styles.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="gallery.css?v=<?php echo time(); ?>">
    
    <style>
        
        .collection-hero {
            background-color: #3498db;
            background-image: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            padding: 60px 20px;
            text-align: center;
            margin-bottom: 30px;
            border-radius: 10px;
            position: relative;
            overflow: hidden;
        }
        
        .collection-hero-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.4);
            z-index: 1;
        }
        
        .collection-hero-content {
            position: relative;
            z-index: 2;
        }
        
        .collection-hero h1 {
            margin-top: 0;
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .collection-hero p {
            font-size: 18px;
            max-width: 800px;
            margin: 10px auto;
            opacity: 0.9;
        }
        
        .collection-meta {
            margin-top: 20px;
            display: flex;
            justify-content: center;
            gap: 20px;
        }
        
        .collection-meta-item {
            display: flex;
            align-items: center;
            font-size: 16px;
        }
        
        .collection-meta-item i {
            margin-right: 6px;
        }
        
        .back-link {
            display: inline-flex;
            align-items: center;
            color: #6c757d;
            text-decoration: none;
            transition: color 0.2s;
            font-weight: 500;
            margin-bottom: 15px;
        }
        
        .back-link i {
            margin-right: 5px;
        }
        
        .back-link:hover {
            color: #343a40;
        }
        
       
        .photo-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
        }
        
        @media (max-width: 1200px) {
            .photo-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }
        
        @media (max-width: 768px) {
            .photo-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 480px) {
            .photo-grid {
                grid-template-columns: 1fr;
            }
        }
        
        .photo-item {
            position: relative;
            overflow: hidden;
            border-radius: 8px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s;
            cursor: pointer;
        }
        
        .photo-item:hover {
            transform: translateY(-5px);
        }
        
        .photo-item img {
            width: 100%;
            height: 250px;
            object-fit: cover;
            display: block;
            transition: transform 0.3s;
        }
        
        .photo-item:hover img {
            transform: scale(1.05);
        }
        
        .photo-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);
            color: white;
            padding: 15px;
            transform: translateY(100%);
            transition: transform 0.3s;
        }
        
        .photo-item:hover .photo-overlay {
            transform: translateY(0);
        }
        
        .photo-title {
            margin: 0;
            font-size: 14px;
            font-weight: 500;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .no-photos {
            grid-column: 1 / -1;
            text-align: center;
            padding: 50px 20px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        
        .no-photos i {
            font-size: 48px;
            color: #dee2e6;
            margin-bottom: 15px;
        }
        
        .no-photos h3 {
            margin: 0 0 10px 0;
            color: #343a40;
        }
        
        .no-photos p {
            color: #6c757d;
            margin: 0;
        }
        
       
        .photo-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.9);
            z-index: 1000;
            overflow-y: auto;
        }
        
        .modal-container {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            min-height: 100%;
            padding: 40px 20px;
        }
        
        .modal-content {
            position: relative;
            max-width: 1000px;
            width: 90%;
            background-color: white;
            border-radius: 10px;
            overflow: hidden;
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
        }
        
        .modal-title {
            margin: 0;
            font-size: 18px;
            font-weight: 500;
        }
        
        .modal-close {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #6c757d;
            transition: color 0.2s;
        }
        
        .modal-close:hover {
            color: #343a40;
        }
        
        .modal-body {
            display: flex;
            flex-direction: column;
        }
        
        .modal-image {
            width: 100%;
            max-height: 70vh;
            object-fit: contain;
            background-color: #000;
        }
        
        .modal-footer {
            padding: 15px 20px;
            background-color: #f8f9fa;
            border-top: 1px solid #dee2e6;
        }
        
        .modal-description {
            margin: 0;
            color: #343a40;
            line-height: 1.5;
        }
        
        .modal-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 100%;
            display: flex;
            justify-content: space-between;
            padding: 0 20px;
            z-index: 1010;
        }
        
        .nav-button {
            background-color: rgba(255, 255, 255, 0.2);
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: white;
            font-size: 18px;
            transition: background-color 0.2s;
        }
        
        .nav-button:hover {
            background-color: rgba(255, 255, 255, 0.4);
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
                    <a href="Gallery.php" class="active">Gallery</a>
                    <a href="#admissions">Admissions</a>
                    <a href="News.php">News & Events</a>
                    <a href="contacts.php">Contact</a>
                </div>
            </div>
            <div class="nav-right">
                <?php if($isLoggedIn): ?>
                    <div class="nav-profile">
                       
                        <div class="profile-trigger" onclick="toggleMenu()">
                            <img src="<?php echo $userData['profile_picture'] ?? 'profile-default.jpg'; ?>" 
                                 alt="Profile" 
                                 class="profile-avatar">
                            <span class="profile-name"><?php echo htmlspecialchars($userData['Username']); ?></span>
                            
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
       
        <a href="gallery.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to Gallery
        </a>
        
        
        <div class="collection-hero">
            <?php if(isset($collection['cover_image']) && !empty($collection['cover_image'])): ?>
            <img src="uploads/<?php echo htmlspecialchars($collection['cover_image']); ?>" 
                 alt="<?php echo htmlspecialchars($collection['name']); ?>" 
                 style="position:absolute; top:0; left:0; width:100%; height:100%; object-fit:cover;">
            <div class="collection-hero-overlay"></div>
            <?php endif; ?>
            
            <div class="collection-hero-content">
                <h1><?php echo htmlspecialchars($collection['name']); ?></h1>
                
                <?php if(isset($collection['description']) && !empty($collection['description'])): ?>
                <p><?php echo htmlspecialchars($collection['description']); ?></p>
                <?php endif; ?>
                
                <div class="collection-meta">
                    <div class="collection-meta-item">
                        <i class="far fa-calendar-alt"></i>
                        <?php 
                            $date = new DateTime($collection['date']);
                            echo $date->format('F j, Y'); 
                        ?>
                    </div>
                    
                    <?php 
                   
                    $count_query = "SELECT COUNT(*) as photo_count FROM collection_images WHERE collection_id = " . mysqli_real_escape_string($conn, $collection_id);
                    $count_result = $conn->query($count_query);
                    $photo_count = 0;
                    
                    if($count_result && $count_result->num_rows > 0) {
                        $count_data = $count_result->fetch_assoc();
                        $photo_count = $count_data['photo_count'];
                    }
                    ?>
                    
                    <div class="collection-meta-item">
                        <i class="fas fa-images"></i>
                        <?php echo $photo_count; ?> Photos
                    </div>
                </div>
            </div>
        </div>
        
    
        <?php if($isAdmin): ?>
        <div style="text-align: center; margin-bottom: 20px;">
            <a href="admingallery.php?collection=<?php echo $collection_id; ?>" class="collection-btn" style="display: inline-block; padding: 10px 15px; background: #3498db; color: white; text-decoration: none; border-radius: 4px; font-weight: 500;">
                <i class="fas fa-cog"></i> Manage Collection
            </a>
        </div>
        <?php endif; ?>
        
      
        <div class="gallery-container">
            <div class="gallery-header">
                <h2>Photos</h2>
                <div class="gallery-filter">
                    <i class="fas fa-images"></i> <?php echo $photo_count; ?> photos in this collection
                </div>
            </div>
            
            <div class="photo-grid">
                <?php
                
                $sql = "SELECT ci.*, i.filename, i.description, i.upload_date 
                        FROM collection_images ci
                        INNER JOIN images i ON ci.image_id = i.id
                        WHERE ci.collection_id = " . mysqli_real_escape_string($conn, $collection_id) . " 
                        ORDER BY ci.sort_order";
                        
                $result = $conn->query($sql);
                
                if ($result && $result->num_rows > 0):
                    $images = array();
                    while ($row = $result->fetch_assoc()): 
                        $images[] = $row;
                        $uploadDate = new DateTime($row['upload_date']);
                        $formattedDate = $uploadDate->format('F j, Y');
                    ?>
                    <div class="photo-item" onclick="openPhotoModal(<?php echo $row['image_id']; ?>)">
                        <img src="uploads/<?php echo htmlspecialchars($row['filename']); ?>" 
                             alt="<?php echo htmlspecialchars($row['description'] ?? 'Collection Photo'); ?>">
                        <div class="photo-overlay">
                            <h3 class="photo-title"><?php echo htmlspecialchars($row['description'] ?? 'Photo'); ?></h3>
                        </div>
                    </div>
                <?php 
                    endwhile;
                else:
                ?>
                    <div class="no-photos">
                        <i class="fas fa-images"></i>
                        <h3>No photos in this collection yet</h3>
                        <p>Photos will be added to this collection soon.</p>
                    </div>
                <?php
                endif;
                ?>
            </div>
        </div>
    </main>

    
    <div id="photoModal" class="photo-modal">
        <div class="modal-container">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title" id="modalTitle">Photo</h3>
                    <button class="modal-close" onclick="closePhotoModal()">&times;</button>
                </div>
                
                <div class="modal-body">
                    <img id="modalImage" src="" alt="Collection Photo" class="modal-image">
                </div>
                
                <div class="modal-footer">
                    <p class="modal-description" id="modalDescription">No description</p>
                </div>
            </div>
            
            <div class="modal-nav">
                <button class="nav-button" onclick="prevPhoto()">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button class="nav-button" onclick="nextPhoto()">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
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
                        <li><span class="icon icon-location"></span>123 School Street, Barangay, City</li>
                        <li><span class="icon icon-phone"></span>(123) 456-7890</li>
                        <li><span class="icon icon-envelope"></span>info@cmricths.edu.ph</li>
                        <li><span class="icon icon-clock"></span>Monday - Friday: 7:00 AM - 5:00 PM</li>
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

        let currentPhotoIndex = 0;
        let collectionPhotos = <?php echo !empty($images) ? json_encode($images) : '[]'; ?>;
        
        
        function openPhotoModal(imageId) {
            
            for(let i = 0; i < collectionPhotos.length; i++) {
                if(collectionPhotos[i].image_id == imageId) {
                    currentPhotoIndex = i;
                    break;
                }
            }
            
            
            showCurrentPhoto();
            
           
            document.getElementById('photoModal').style.display = 'block';
            document.body.style.overflow = 'hidden';
        }
        
        
        function closePhotoModal() {
            document.getElementById('photoModal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }
        
    
        function showCurrentPhoto() {
            if(collectionPhotos.length === 0) return;
            
            const photo = collectionPhotos[currentPhotoIndex];
            const modalImage = document.getElementById('modalImage');
            const modalTitle = document.getElementById('modalTitle');
            const modalDescription = document.getElementById('modalDescription');
            
           
            modalImage.src = 'uploads/' + photo.filename;
            modalImage.alt = photo.description || 'Collection Photo';
            
            
            modalTitle.textContent = photo.description || ('Photo ' + (currentPhotoIndex + 1) + ' of ' + collectionPhotos.length);
            
           
            const uploadDate = new Date(photo.upload_date);
            const formattedDate = uploadDate.toLocaleDateString('en-US', { 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            });
            
            modalDescription.textContent = photo.description ? 
                ('Taken on ' + formattedDate) : 
                ('Uploaded on ' + formattedDate);
        }
        
        
        function prevPhoto() {
            if(collectionPhotos.length === 0) return;
            
            currentPhotoIndex--;
            if(currentPhotoIndex < 0) currentPhotoIndex = collectionPhotos.length - 1;
            showCurrentPhoto();
        }
        
       
        function nextPhoto() {
            if(collectionPhotos.length === 0) return;
            
            currentPhotoIndex++;
            if(currentPhotoIndex >= collectionPhotos.length) currentPhotoIndex = 0;
            showCurrentPhoto();
        }
        
        
        document.addEventListener('keydown', function(e) {
            if(document.getElementById('photoModal').style.display !== 'block') return;
            
            if(e.key === 'ArrowLeft') {
                prevPhoto();
            } else if(e.key === 'ArrowRight') {
                nextPhoto();
            } else if(e.key === 'Escape') {
                closePhotoModal();
            }
        });
        
      
        document.getElementById('photoModal').addEventListener('click', function(e) {
            if(e.target.id === 'photoModal' || e.target.classList.contains('modal-container')) {
                closePhotoModal();
            }
        });
    </script>
</body>
</html>