<?php
session_start();
require 'Config.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

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
    <title>My Gallery - CMRICTHS</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <link rel="stylesheet" href="styles.css?v=<?php echo time(); ?>">
   
    <link rel="stylesheet" href="gallery.css?v=<?php echo time(); ?>">
    
    <style>
        
        .sidebar {
            position: fixed;
            top: 70px; 
            right: -300px;
            width: 300px;
            height: calc(100% - 70px); 
            background-color: #fff;
            box-shadow: -5px 0 15px rgba(0,0,0,0.1);
            z-index: 1000;
            transition: right 0.3s ease;
            overflow-y: auto;
        }
        
        .sidebar.active {
            right: 0;
        }
        
        
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 70px;
            left: 0;
            width: 100%;
            height: calc(100% - 70px);
            background-color: rgba(0,0,0,0.2);
            z-index: 999;
        }
        
        .sidebar-overlay.active {
            display: block.
        }
        
        
        .file-input-container {
            margin-bottom: 20px;
        }
        
        .file-input-label {
            display: inline-block;
            cursor: pointer;
            padding: 10px 15px;
            background-color: #3498db;
            color: white;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        
        .file-input-label:hover {
            background-color: #2980b9;
        }
        
        .file-input {
            display: none;
        }
        
        .file-name {
            margin-left: 10px;
            font-size: 14px;
            color: #555;
        }
        
        
        .gallery-container {
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            max-height: none; 
            overflow: visible; 
        }
        
        .gallery-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #e0e0e0;
            padding-bottom: 10px;
        }
        
        .gallery-header h2 {
            margin: 0;
            color: #333;
            font-size: 24px;
        }
        
        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            overflow: visible; 
        }
        
        @media (max-width: 1200px) {
            .gallery-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }
        
        @media (max-width: 992px) {
            .gallery-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 576px) {
            .gallery-grid {
                grid-template-columns: 1fr;
            }
        }
        
        .gallery-item {
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
            height: 100%; 
            display: flex;
            flex-direction: column;
        }
        
        .gallery-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.15);
        }
        
        .gallery-img-container {
            position: relative;
            height: 200px; 
            overflow: hidden;
        }
        
        .gallery-img {
            width: 100%;
            height: 100%;
            object-fit: cover; 
            transition: transform 0.3s;
        }
        
        .gallery-item:hover .gallery-img {
            transform: scale(1.05);
        }
        
        .gallery-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.4);
            opacity: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            transition: opacity 0.3s;
        }
        
        .gallery-item:hover .gallery-overlay {
            opacity: 1;
        }
        
        .gallery-action-btn {
            background: #fff;
            color: #333;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            margin: 0 5px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .gallery-action-btn:hover {
            background: #3498db;
            color: #fff;
        }
        
        .gallery-info {
            padding: 15px;
            flex-grow: 1; 
            display: flex;
            flex-direction: column;
        }
        
        .gallery-description {
            font-size: 14px;
            color: #555;
            margin-bottom: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            flex-grow: 1; 
        }
        
        .gallery-date {
            color: #888;
            font-size: 12px;
            margin-top: 10px;
        }
        
        .no-images {
            text-align: center;
            padding: 40px 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .no-images i {
            font-size: 40px;
            color: #ccc;
            margin-bottom: 10px;
        }
        
        .no-images p {
            color: #888;
            margin: 10px 0;
        }
        
        .upload-section {
            background: #fff;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .upload-section h3 {
            margin-top: 0;
            margin-bottom: 20px;
            color: #333;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 10px;
        }
        
        
        .gallery-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.8);
            z-index: 1050;
            overflow-y: auto; 
            padding: 20px;
        }
        
        .modal-content {
            background-color: #fff;
            margin: 20px auto;
            max-width: 800px;
            border-radius: 8px;
            overflow: hidden;
            position: relative;
            max-height: calc(100vh - 40px);
            display: flex;
            flex-direction: column;
        }
        
        .modal-close {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(255,255,255,0.7);
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 10;
            font-size: 18px;
            transition: all 0.3s;
        }
        
        .modal-close:hover {
            background: rgba(255,255,255,0.9);
            transform: rotate(90deg);
        }
        
        .modal-image-container {
            width: 100%;
            max-height: 60vh;
            overflow: hidden;
        }
        
        .modal-image {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
        
        .modal-details {
            padding: 20px;
            overflow-y: auto; 
        }
        
        .modal-description {
            margin-bottom: 20px;
            font-size: 16px;
            line-height: 1.5;
            color: #333;
        }
        
        .modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
        
        .modal-btn {
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .edit-btn {
            background: #3498db;
            color: #fff;
        }
        
        .delete-btn {
            background: #e74c3c;
            color: #fff;
        }
        
        .cancel-btn {
            background: #95a5a6;
            color: #fff;
        }
        
        .modal-btn:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }
        
        .edit-form {
            display: none;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }
        
        .edit-form textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            resize: vertical;
            min-height: 100px;
            font-family: inherit;
        }
       
        .welcome-block {
            background-color: #3498db;
            color: white;
            padding: 40px 20px;
            text-align: center;
            border-radius: 10px;
            margin-bottom: 30px;
            background-image: linear-gradient(135deg, #3498db, #2980b9);
        }
        
        .welcome-block h1 {
            margin-top: 0;
            font-size: 32px;
            font-weight: 600;
        }
        
        .welcome-block p {
            font-size: 18px;
            max-width: 700px;
            margin: 0 auto;
            opacity: 0.9;
        }
       
        .status-message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        
        .success-message {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .privacy-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(0,0,0,0.7);
            color: white;
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 12px;
            display: flex;
            align-items: center;
        }
        
        .privacy-badge i {
            margin-right: 4px;
        }
        
        .section-description {
            color: #666;
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 25px;
        }
        
        .featured-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .featured-item {
            height: 180px;
            border-radius: 8px;
            overflow: hidden;
            position: relative;
            background: linear-gradient(135deg, #3498db, #2980b9);
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
            color: white;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
        }
        
        .featured-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .featured-item:nth-child(2n) {
            background: linear-gradient(135deg, #9b59b6, #8e44ad);
        }
        
        .featured-item:nth-child(3n) {
            background: linear-gradient(135deg, #2ecc71, #27ae60);
        }
        
        .featured-item:nth-child(4n) {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
        }
        
        .featured-content {
            padding: 20px;
            width: 100%;
        }
        
        .featured-content h3 {
            margin: 0 0 8px 0;
            font-size: 20px;
            font-weight: 600;
        }
        
        .featured-content p {
            margin: 0 0 15px 0;
            font-size: 14px;
            opacity: 0.9;
        }
        
        .featured-count {
            display: inline-block;
            padding: 4px 10px;
            background-color: rgba(255,255,255,0.2);
            border-radius: 20px;
            font-size: 12px;
        }
        
        @media (max-width: 992px) {
            .featured-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 576px) {
            .featured-grid {
                grid-template-columns: 1fr;
            }
        }
        
        .collections-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .collection-item {
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
            cursor: pointer;
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        
        .collection-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .collection-image-container {
            position: relative;
            height: 180px;
            overflow: hidden;
        }
        
        .collection-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s;
        }
        
        .collection-item:hover .collection-image {
            transform: scale(1.05);
        }
        
        .collection-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.3);
            display: flex;
            align-items: flex-end;
            padding: 10px;
        }
        
        .collection-count {
            background: rgba(0,0,0,0.6);
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
        }
        
        .collection-info {
            padding: 15px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }
        
        .collection-info h3 {
            margin: 0 0 8px 0;
            color: #333;
            font-size: 18px;
            font-weight: 600;
        }
        
        .collection-date {
            color: #888;
            font-size: 12px;
            margin: 0;
        }
    
        .add-collection {
            background: rgba(52, 152, 219, 0.1);
            border: 2px dashed #3498db;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .add-collection:hover {
            background: rgba(52, 152, 219, 0.2);
        }
        
        .add-collection-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 20px;
            height: 100%;
        }
        
        .add-icon {
            font-size: 40px;
            color: #3498db;
            margin-bottom: 15px;
        }
        
        .add-collection-content h3 {
            margin: 0 0 8px 0;
            color: #3498db;
        }
        
        .add-collection-content p {
            margin: 0;
            color: #666;
            font-size: 14px;
        }
       
        .collection-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.8);
            z-index: 1050;
            overflow-y: auto;
            padding: 20px;
        }
        
        .collection-modal-content {
            background-color: #fff;
            margin: 20px auto;
            max-width: 500px;
            border-radius: 8px;
            overflow: hidden;
            position: relative;
            padding: 25px;
        }
        
        @media (max-width: 1200px) {
            .collections-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }
        
        @media (max-width: 992px) {
            .collections-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 576px) {
            .collections-grid {
                grid-template-columns: 1fr;
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
                    <a href="Gallery.php" class="active">Gallery</a>
                   
                    <a href="News.php">News & Events</a>
                    <a href="contacts.php">Contact</a>
                </div>
            </div>
            <div class="nav-right">
                <?php if($isLoggedIn): ?>
                    <div class="nav-profile">
                        
                        <div class="profile-trigger" onclick="toggleMenu()">
                            <img src="<?php echo !empty($userData['profile_picture']) ? 'uploads/profile/' . htmlspecialchars($userData['profile_picture']) : 'profile-default.jpg'; ?>" 
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
                    <a href="about.php"><span class="icon icon-info"></span>About Us</a>
                    <a href="contact.php"><span class="icon icon-contact"></span>Contact</a>
                </div>
            <?php endif; ?>
        </nav>
    </div>

   
    <main class="main-content">
        
        <div class="welcome-block">
            <h1>My Private Gallery</h1>
            <p>Upload and manage your personal photos - only you can see your gallery.</p>
        </div>
        
        <?php if(isset($_GET['upload'])): ?>
            <div class="status-message success-message">
                <i class="fas fa-check-circle"></i> Your image was uploaded successfully!
            </div>
        <?php endif; ?>
        
        <?php if(isset($_GET['error'])): ?>
            <div class="status-message error-message">
                <i class="fas fa-exclamation-circle"></i> 
                <?php 
                    switch($_GET['error']) {
                        case 'file':
                            echo "There was a problem with your file. Please try again.";
                            break;
                        case 'database':
                            echo "There was a database error. Please try again.";
                            break;
                        case 'upload':
                            echo "Failed to upload your image. Please try again.";
                            break;
                        default:
                            echo "An error occurred. Please try again.";
                    }
                ?>
            </div>
        <?php endif; ?>
        
        <div class="gallery-container">
            <?php if($isLoggedIn): ?>
                
                <div class="upload-section">
                    <h3><i class="fas fa-cloud-upload-alt"></i> Upload New Image</h3>
                    <form action="galleryprocess.php" method="post" enctype="multipart/form-data" class="upload-form">
                        <div class="file-input-container">
                            <label for="image-upload" class="file-input-label">
                                <i class="fas fa-image"></i> Choose an image
                            </label>
                            <input type="file" name="image" id="image-upload" class="file-input" required accept="image/*">
                            <span class="file-name" id="file-name">No file chosen</span>
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea name="description" id="description" class="description-input" 
                                    placeholder="Add a description for your image..."></textarea>
                        </div>

                        <button type="submit" class="gallery-action-btn">
                            <i class="fas fa-upload"></i> Upload Image
                        </button>
                    </form>
                </div>

                
                <div class="gallery-header">
                    <h2>My Private Gallery</h2>
                    <div class="gallery-filter">
                        <i class="fas fa-lock"></i> Only you can see these images
                    </div>
                </div>
                
                <div class="gallery-grid">
                    <?php
                  
                    $sql = "SELECT * FROM images WHERE user_id = '" . mysqli_real_escape_string($conn, $userData['User_ID']) . "' ORDER BY upload_date DESC";
                    $result = $conn->query($sql);
                    
                    if ($result && $result->num_rows > 0):
                        while ($row = $result->fetch_assoc()): 
                            $uploadDate = new DateTime($row['upload_date']);
                            $formattedDate = $uploadDate->format('F j, Y');
                        ?>
                        <div class="gallery-item">
                            <div class="gallery-img-container">
                                <img src="uploads/<?php echo htmlspecialchars($row['filename']); ?>" 
                                     alt="Gallery Image" 
                                     class="gallery-img">
                                <div class="privacy-badge">
                                    <i class="fas fa-lock"></i> Private
                                </div>
                                <div class="gallery-overlay">
                                    <button class="gallery-action-btn view-btn" 
                                            data-id="<?php echo $row['id']; ?>"
                                            data-image="<?php echo htmlspecialchars($row['filename']); ?>"
                                            data-description="<?php echo htmlspecialchars($row['description'] ?? ''); ?>"
                                            onclick="openImageModal(this)">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                </div>
                            </div>
                            <div class="gallery-info">
                                <p class="gallery-description"><?php echo htmlspecialchars($row['description'] ?? 'No description'); ?></p>
                                <p class="gallery-date"><i class="far fa-calendar-alt"></i> <?php echo $formattedDate; ?></p>
                            </div>
                        </div>
                    <?php 
                        endwhile;
                    else:
                    ?>
                        <div class="no-images" style="grid-column: 1 / -1;">
                            <i class="fas fa-images"></i>
                            <h3>Your gallery is empty</h3>
                            <p>Upload some images to get started with your private collection!</p>
                        </div>
                    <?php
                    endif;
                    ?>
                </div>
            <?php else: ?>
                <div class="no-images">
                    <i class="fas fa-lock"></i>
                    <h3>Private Gallery</h3>
                    <p>Please <a href="Login.php">login</a> to view and manage your personal gallery.</p>
                </div>
            <?php endif; ?>
        </div>

       
        <?php if($isLoggedIn): ?>
<div class="gallery-container">
    <div class="gallery-header">
        <h2>Event Collections</h2>
        <div class="gallery-filter">
            <i class="fas fa-folder"></i> Browse photo collections by event
        </div>
    </div>
    
    <p class="section-description">Explore these collections of photos organized by school events and activities. Click on any collection to view all related images.</p>
    
    <div class="collections-grid">
        <?php
        
        $collections_query = "SELECT c.*, 
                             (SELECT COUNT(*) FROM collection_images WHERE collection_id = c.id) as photo_count 
                             FROM collections c 
                             ORDER BY c.date DESC";
        $collections_result = $conn->query($collections_query);
        
        if ($collections_result && $collections_result->num_rows > 0):
            while ($collection = $collections_result->fetch_assoc()):
              
                $collection_date = new DateTime($collection['date']);
                $formatted_date = $collection_date->format('F j, Y');
                
                
                $cover_image = !empty($collection['cover_image']) ? 
                               "uploads/" . htmlspecialchars($collection['cover_image']) : 
                               "https://via.placeholder.com/400x300/3498db/ffffff?text=" . urlencode($collection['name']);
        ?>
        <div class="collection-item" onclick="location.href='collection.php?id=<?php echo $collection['id']; ?>'">
            <div class="collection-image-container">
                <img src="<?php echo $cover_image; ?>" alt="<?php echo htmlspecialchars($collection['name']); ?>" class="collection-image">
                <div class="collection-overlay">
                    <span class="collection-count">
                        <i class="fas fa-image"></i> <?php echo $collection['photo_count']; ?> photos
                    </span>
                </div>
            </div>
            <div class="collection-info">
                <h3><?php echo htmlspecialchars($collection['name']); ?></h3>
                <p class="collection-date"><?php echo $formatted_date; ?></p>
            </div>
        </div>
        <?php
            endwhile;
        else:
        ?>
        <div class="no-images" style="grid-column: 1 / -1;">
            <i class="fas fa-folder-open"></i>
            <h3>No Collections Available</h3>
            <p>There are currently no event collections to display.</p>
        </div>
        <?php
        endif;
        ?>
    </div>
</div>
<?php endif; ?>
    </main>

   
    <div id="imageModal" class="gallery-modal">
        <div class="modal-content">
            <span class="modal-close" onclick="closeImageModal()"><i class="fas fa-times"></i></span>
            
            <div class="modal-image-container">
                <img id="modalImage" src="" alt="Gallery Image" class="modal-image">
            </div>
            
            <div class="modal-details">
                <p id="modalDescription" class="modal-description"></p>
                
                <div class="modal-actions">
                    <button class="modal-btn edit-btn" onclick="showEditForm()">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                    <button class="modal-btn delete-btn" onclick="confirmDelete()">
                        <i class="fas fa-trash-alt"></i> Delete
                    </button>
                    <button class="modal-btn cancel-btn" onclick="closeImageModal()">
                        <i class="fas fa-times"></i> Close
                    </button>
                </div>
                
                <div id="editForm" class="edit-form">
                    <form onsubmit="updateDescription(event)">
                        <input type="hidden" id="editImageId" value="">
                        <textarea id="editDescriptionText" placeholder="Edit description..."></textarea>
                        <div class="modal-actions">
                            <button type="submit" class="modal-btn edit-btn">
                                <i class="fas fa-save"></i> Save Changes
                            </button>
                            <button type="button" class="modal-btn cancel-btn" onclick="hideEditForm()">
                                <i class="fas fa-times"></i> Cancel
                            </button>
                        </div>
                    </form>
                </div>
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
                        <li><a href="indexs.php">Home</a></li>
                        <li><a href="about.php">About</a></li>
                        <li><a href="gallery.php">Gallery</a></li>
                        <li><a href="news.php">News & Events</a></li>
                        <li><a href="contacts.php">Contacts</a></li>
                    </ul>
                </div>

                

                <div class="footer-section">
                    <h4>Contact Information</h4>
                    <ul class="contact-info">
                        <li><span class="icon icon-location"></span>Dona Aurora St, Claro M. Recto, Angeles City, Pampanga</li>
                        <li><span class="icon icon-phone"></span>(045) 887 5502</li>
                        <li><span class="icon icon-envelope"></span>cmricthsangelescity@yahoo.com</li>
                        <li><span class="icon icon-clock"></span>Monday - Friday: 6:00 AM - 6:00 PM</li>
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

       
        document.addEventListener('DOMContentLoaded', function() {
            const fileInput = document.getElementById('image-upload');
            const fileName = document.getElementById('file-name');
            
            if(fileInput && fileName) {
                fileInput.addEventListener('change', function() {
                    if(this.files.length > 0) {
                        fileName.textContent = this.files[0].name;
                    } else {
                        fileName.textContent = 'No file chosen';
                    }
                });
            }
            
            
            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape') {
                    const sidebar = document.getElementById('sidebar');
                    if (sidebar.classList.contains('active')) {
                        toggleMenu();
                    }
                    
                   
                    const modal = document.getElementById('imageModal');
                    if (modal.style.display === 'flex') {
                        closeImageModal();
                    }
                }
            });
        });
        
       
        function openImageModal(element) {
            const modal = document.getElementById('imageModal');
            const modalImage = document.getElementById('modalImage');
            const modalDesc = document.getElementById('modalDescription');
            const editImageId = document.getElementById('editImageId');
            const editDescText = document.getElementById('editDescriptionText');
            
          
            const imageId = element.getAttribute('data-id');
            const imageSrc = 'uploads/' + element.getAttribute('data-image');
            const description = element.getAttribute('data-description');
            
           
            modalImage.src = imageSrc;
            modalDesc.textContent = description || 'No description provided';
            editImageId.value = imageId;
            editDescText.value = description || '';
            
           
            modal.style.display = 'flex';
            
            
            hideEditForm();
            
            
            document.body.style.overflow = 'hidden';
        }
        
        
        function closeImageModal() {
            const modal = document.getElementById('imageModal');
            modal.style.display = 'none';
            
            
            document.body.style.overflow = 'auto';
        }
        
        
        function showEditForm() {
            const editForm = document.getElementById('editForm');
            editForm.style.display = 'block';
        }
        
        
        function hideEditForm() {
            const editForm = document.getElementById('editForm');
            editForm.style.display = 'none';
        }
        
        
        function updateDescription(event) {
            event.preventDefault();
            
            const imageId = document.getElementById('editImageId').value;
            const description = document.getElementById('editDescriptionText').value;
            
            
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'galleryedit.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function() {
                if (xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            
                            document.getElementById('modalDescription').textContent = description;
                            
                           
                            const items = document.querySelectorAll('.view-btn');
                            items.forEach(item => {
                                if (item.getAttribute('data-id') === imageId) {
                                    item.setAttribute('data-description', description);
                                    const descElement = item.closest('.gallery-item').querySelector('.gallery-description');
                                    if (descElement) {
                                        descElement.textContent = description || 'No description';
                                    }
                                }
                            });
                            
                            
                            hideEditForm();
                            
                           
                            alert('Description updated successfully!');
                        } else {
                            alert(response.message || 'Error updating description');
                        }
                    } catch (e) {
                        alert('Error processing server response');
                    }
                }
            };
            xhr.send('id=' + encodeURIComponent(imageId) + '&description=' + encodeURIComponent(description));
        }
        
       
        function confirmDelete() {
            if (confirm('Are you sure you want to delete this image? This action cannot be undone.')) {
                const imageId = document.getElementById('editImageId').value;
                
               
                const xhr = new XMLHttpRequest();
                xhr.open('POST', 'gallerydelete.php', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        try {
                            const response = JSON.parse(xhr.responseText);
                            if (response.success) {
                               
                                closeImageModal();
                                
                                
                                const items = document.querySelectorAll('.view-btn');
                                items.forEach(item => {
                                    if (item.getAttribute('data-id') === imageId) {
                                        const galleryItem = item.closest('.gallery-item');
                                        if (galleryItem) {
                                            galleryItem.remove();
                                        }
                                    }
                                });
                                
                                
                                const galleryGrid = document.querySelector('.gallery-grid');
                                if (galleryGrid && galleryGrid.children.length === 0) {
                                    galleryGrid.innerHTML = `
                                        <div class="no-images" style="grid-column: 1 / -1;">
                                            <i class="fas fa-images"></i>
                                            <h3>No images yet</h3>
                                            <p>Your private gallery is empty. Upload some images to get started!</p>
                                        </div>
                                    `;
                                }
                            } else {
                                alert(response.message || 'Error deleting image');
                            }
                        } catch (e) {
                            alert('Error processing server response');
                        }
                    }
                };
                xhr.send('id=' + encodeURIComponent(imageId));
            }
        }
    </script>
</body>
</html>