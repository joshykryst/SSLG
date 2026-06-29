<?php
session_start();
require 'Config.php';


if(empty($_SESSION["Admin_ID"])) {
    
    header("Location: Login.php");
    exit();
}


$Admin_ID = $_SESSION["Admin_ID"];
$teacherCheck = $conn->prepare("SELECT * FROM table_admin WHERE Admin_ID = ? AND role = 'teacher'");
$teacherCheck->bind_param("i", $Admin_ID);
$teacherCheck->execute();
$result = $teacherCheck->get_result();


if($result->num_rows > 0) {
    $teacherData = $result->fetch_assoc();
    $isTeacher = true;
    $teacherSubject = $teacherData['subject'];
   
    $adminData = array(
        'Username' => $teacherData['Username'],
        'profile_picture' => $teacherData['profile_picture'] ?? 'admin-default.jpg',
        'FirstName' => $teacherData['FirstName'] ?? '',
        'LastName' => $teacherData['LastName'] ?? ''
    );
} else {
    $isTeacher = false;
    
    
    $adminQuery = $conn->prepare("SELECT * FROM table_admin WHERE Admin_ID = ?");
    $adminQuery->bind_param("i", $Admin_ID);
    $adminQuery->execute();
    $adminResult = $adminQuery->get_result();
    
    if($adminResult->num_rows > 0) {
        $adminData = $adminResult->fetch_assoc();
    } else {
        
        $adminData = array(
            'Username' => 'Admin',
            'profile_picture' => 'admin-default.jpg',
            'FirstName' => 'Admin',
            'LastName' => 'User'
        );
    }
}


$check_table = mysqli_query($conn, "SHOW TABLES LIKE 'news_articles'");

if(mysqli_num_rows($check_table) == 0) {
    
    $create_table_query = "CREATE TABLE news_articles (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        content TEXT NOT NULL,
        image_url VARCHAR(255) NOT NULL,
        category VARCHAR(50) NOT NULL,
        author VARCHAR(100) NOT NULL,
        publish_date DATETIME NULL,
        featured TINYINT(1) DEFAULT 0
    )";

    if(!mysqli_query($conn, $create_table_query)) {
        die("Error creating news_articles table: " . mysqli_error($conn));
    }
} else {
    
    $alter_table_query = "ALTER TABLE news_articles MODIFY publish_date DATETIME NULL";
    mysqli_query($conn, $alter_table_query);
}


$message = null;
$error = null;

if(isset($_POST['submit_news'])){
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $content = mysqli_real_escape_string($conn, $_POST['content']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $author = mysqli_real_escape_string($conn, $_POST['author']);
    $featured = isset($_POST['featured']) ? 1 : 0;
    

    $publish_date_input = mysqli_real_escape_string($conn, $_POST['publish_date']);
    
    
    if (empty($publish_date_input)) {
        
        $publish_date = date('Y-m-d H:i:s');
    } else {
       
        $timestamp = strtotime($publish_date_input);
        if ($timestamp === false) {
            
            $publish_date = date('Y-m-d H:i:s');
        } else {
            
            $publish_date = date('Y-m-d', $timestamp) . ' 12:00:00';
        }
    }

    error_log("Date from form: " . $publish_date_input . ", Formatted for SQL: " . $publish_date);
    

    $image_url = '';
    $upload_success = true;
    
    if(isset($_FILES['image']) && $_FILES['image']['size'] > 0) {
        $target_dir = "news_images/";
        
     
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $file_extension = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
        $new_filename = uniqid() . '.' . $file_extension;
        $target_file = $target_dir . $new_filename;
        
      
        $allowed_types = array('jpg', 'jpeg', 'png', 'gif');
        if(!in_array($file_extension, $allowed_types)) {
            $error = "Only JPG, JPEG, PNG & GIF files are allowed.";
            $upload_success = false;
        }
        
        
        if($_FILES["image"]["size"] > 5000000) {
            $error = "File is too large. Maximum size is 5MB.";
            $upload_success = false;
        }
        
        if($upload_success) {
            if(move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                $image_url = $target_file;
            } else {
                $error = "Error uploading file.";
                $upload_success = false;
            }
        }
    } elseif(isset($_POST['existing_image']) && !empty($_POST['existing_image'])) {
        
        $image_url = $_POST['existing_image'];
    } else {
        $error = "Please select an image.";
        $upload_success = false;
    }
    
    if(!$error && $upload_success) {
        if(isset($_POST['news_id']) && !empty($_POST['news_id'])) {
           
            $news_id = (int)$_POST['news_id'];
            
            
            $before_check = $conn->query("SELECT publish_date FROM news_articles WHERE id = $news_id");
            $before_row = $before_check->fetch_assoc();
            error_log("BEFORE UPDATE: Date in DB was: " . $before_row['publish_date']);
            
            
            $update_query = "UPDATE news_articles SET 
                            title = ?, 
                            content = ?, 
                            image_url = ?, 
                            category = ?, 
                            author = ?,
                            featured = ?,
                            publish_date = ? 
                            WHERE id = ?";
            
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param("sssssisi", $title, $content, $image_url, $category, $author, $featured, $publish_date, $news_id);
            
            if($stmt->execute()) {
                
                $check_date = $conn->query("SELECT publish_date FROM news_articles WHERE id = $news_id");
                $date_row = $check_date->fetch_assoc();
                error_log("AFTER UPDATE: Published date after update: " . $date_row['publish_date'] . " (Expected: $publish_date)");
                
                $message = "Article updated successfully!";
                header("Location: NewsAdmin.php?success=update");
                exit();
            } else {
                $error = "Error updating article: " . $conn->error;
            }
        } else {
           
            $insert_query = "INSERT INTO news_articles (title, content, image_url, category, author, featured, publish_date) 
                           VALUES (?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($insert_query);
            $stmt->bind_param("ssssssi", $title, $content, $image_url, $category, $author, $featured, $publish_date);
            
            if($stmt->execute()) {
                $debug_id = $conn->insert_id;
                $debug_query = "SELECT publish_date FROM news_articles WHERE id = $debug_id";
                $debug_result = $conn->query($debug_query);
                $debug_row = $debug_result->fetch_assoc();
                error_log("DEBUG - Created article ID: $debug_id with date: " . $debug_row['publish_date'] . " (Input was: $publish_date_input)");
                
                $message = "Article published successfully!";
                
                header("Location: NewsAdmin.php?success=create");
                exit();
            } else {
                $error = "Error publishing article: " . $conn->error;
            }
        }
    }
}


if(isset($_GET['delete_news'])){
    $news_id = (int)$_GET['delete_news'];
    

    $image_query = "SELECT image_url FROM news_articles WHERE id = ?";
    $image_stmt = $conn->prepare($image_query);
    $image_stmt->bind_param("i", $news_id);
    $image_stmt->execute();
    $image_result = $image_stmt->get_result();
    
    if($image_row = $image_result->fetch_assoc()) {
        $image_path = $image_row['image_url'];
        if(file_exists($image_path) && strpos($image_path, 'news_images/') === 0) {
            @unlink($image_path);
        }
    }
    
  
    $delete_query = "DELETE FROM news_articles WHERE id = ?";
    $delete_stmt = $conn->prepare($delete_query);
    $delete_stmt->bind_param("i", $news_id);
    
    if($delete_stmt->execute()) {
        header("Location: NewsAdmin.php?success=delete");
        exit();
    } else {
        $error = "Error deleting article.";
    }
}

if(isset($_GET['success'])) {
    if($_GET['success'] === 'create') {
        $message = "Article published successfully!";
    } elseif($_GET['success'] === 'update') {
        $message = "Article updated successfully!";
    } elseif($_GET['success'] === 'delete') {
        $message = "Article deleted successfully!";
    }
}


$edit_article = null;
if(isset($_GET['edit_news'])){
    $news_id = (int)$_GET['edit_news'];
    $edit_query = "SELECT * FROM news_articles WHERE id = ?";
    $edit_stmt = $conn->prepare($edit_query);
    $edit_stmt->bind_param("i", $news_id);
    $edit_stmt->execute();
    $edit_result = $edit_stmt->get_result();
    
    if($edit_result->num_rows > 0) {
        $edit_article = $edit_result->fetch_assoc();
    }
}


$news_query = "SELECT * FROM news_articles ORDER BY publish_date DESC";
$news_result = mysqli_query($conn, $news_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>News Administration - CMRICTHS</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>

    <nav class="main-nav">
        <div class="nav-container">
            <div class="nav-left">
                <a href="Admin.php" class="nav-brand">
                    <img src="logo.png" alt="CMRICTHS Logo" class="nav-logo">
                    <div class="brand-text">
                        <h1>CMRICTHS</h1>
                        <p>Admin Dashboard</p>
                    </div>
                </a>
            </div>
            <div class="nav-right">
              
                <a href="./adminschedules.php" class="admin-nav-btn">
                    <i class="fas fa-calendar-alt"></i> Manage Schedules
                </a>
                <a href="NewsAdmin.php" class="admin-nav-btn active">
                    <i class="fas fa-newspaper"></i> Manage News
                </a>
                <a href="admingallery.php" class="admin-nav-btn">
                    <i class="fas fa-images"></i> Manage Gallery
                </a>
                <a href="admincontacts.php" class="admin-nav-btn">
                    <i class="fas fa-envelope"></i> Contact Feedback
                    <?php
                   
                    $unread_query = "SELECT COUNT(*) as unread FROM contact_feedback WHERE status = 'new'";
                    $unread_result = $conn->query($unread_query);
                    if ($unread_result && $unread_row = $unread_result->fetch_assoc()) {
                        $unread_count = $unread_row['unread'];
                        if ($unread_count > 0) {
                            echo '<span class="notification-badge">' . $unread_count . '</span>';
                        }
                    }
                    ?>
                </a>
                
                <?php if ($isTeacher): ?>
                <span class="teacher-subject-badge">
                    <i class="fas fa-book"></i> <?php echo htmlspecialchars($teacherSubject); ?> Teacher
                </span>
                <?php endif; ?>
                
                <div class="nav-profile">
                    <div class="profile-dropdown">
                        <div class="profile-trigger" onclick="toggleAdminMenu()">
                            <img src="<?php echo $adminData['profile_picture'] ?? 'admin-default.jpg'; ?>" 
                                 alt="Admin" class="profile-avatar">
                            <span class="profile-name"><?php echo htmlspecialchars($adminData['Username'] ?? 'Administrator'); ?></span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="profile-menu" id="adminProfileMenu">
                            <div class="profile-header">
                                <img src="<?php echo $adminData['profile_picture'] ?? 'admin-default.jpg'; ?>" 
                                     alt="Admin" class="profile-picture">
                                <div class="profile-info">
                                    <h4><?php echo htmlspecialchars($adminData['Username'] ?? 'Admin User'); ?></h4>
                                    <p><?php echo $isTeacher ? htmlspecialchars($teacherSubject) . ' Teacher' : 'System Administrator'; ?></p>
                                </div>
                            </div>
                            <div class="profile-links">
                                <a href="admin_settings.php">
                                    <i class="fas fa-cog"></i> Settings
                                </a>
                                <a href="Logout.php" class="logout-link">
                                    <i class="fas fa-sign-out-alt"></i> Logout
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

   
    <main class="main-content">
        <div class="dashboard-container">
            
            <div id="alertContainer">
                <?php if (isset($message)): ?>
                    <div class="alert alert-success">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-error">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success">
                        <?php 
                            if ($_GET['success'] === 'delete') {
                                echo "Article deleted successfully.";
                            } elseif ($_GET['success'] === 'update') {
                                echo "Article updated successfully.";
                            } elseif ($_GET['success'] === 'create') {
                                echo "Article published successfully.";
                            }
                        ?>
                    </div>
                <?php endif; ?>
            </div>

            
            <div class="dashboard-card">
                <div class="card-header">
                    <h2><i class="fas fa-newspaper"></i> News Management</h2>
                    <div class="filter-box">
                        <select id="categoryFilter" class="filter-select-compact" onchange="filterArticles()">
                            <option value="">All Categories</option>
                            <option value="announcements">Announcements</option>
                            <option value="events">Events</option>
                            <option value="academics">Academics</option>
                            <option value="achievements">Achievements</option>
                        </select>
                        <div class="search-box">
                            <input type="text" id="searchInput" placeholder="Search articles...">
                            <i class="fas fa-search"></i>
                        </div>
                    </div>
                </div>

                <div class="admin-tabs">
                    <button class="admin-tab <?= !$edit_article ? 'active' : '' ?>" data-tab="news-list">
                        <i class="fas fa-list"></i> News Articles
                    </button>
                    <button class="admin-tab <?= $edit_article ? 'active' : '' ?>" data-tab="add-news">
                        <i class="fas fa-plus"></i> <?= $edit_article ? 'Edit Article' : 'Add New Article' ?>
                    </button>
                </div>

              
                <div class="tab-content <?= !$edit_article ? 'active' : '' ?>" id="news-list">
                    <div class="table-responsive">
                        <table class="admin-table news-table">
                            <thead>
                                <tr>
                                    <th>Image</th>
                                    <th>Title</th>
                                    <th>Category</th>
                                    <th>Author</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                if($news_result && mysqli_num_rows($news_result) > 0):
                                    while($article = mysqli_fetch_assoc($news_result)):
                                ?>
                                <tr data-category="<?= htmlspecialchars($article['category']); ?>">
                                    <td class="news-image-cell">
                                        <img src="<?= htmlspecialchars($article['image_url']); ?>" alt="<?= htmlspecialchars($article['title']); ?>" class="news-image">
                                    </td>
                                    <td><?= htmlspecialchars($article['title']); ?></td>
                                    <td><span class="category-badge <?= htmlspecialchars($article['category']); ?>"><?= htmlspecialchars($article['category']); ?></span></td>
                                    <td><?= htmlspecialchars($article['author']); ?></td>
                                    <td>
                                        <?php 
                                            if (!empty($article['publish_date'])) {
                                                $timestamp = strtotime($article['publish_date']);
                                                if ($timestamp !== false) {
                                                    echo date('M d, Y', $timestamp);
                                                } else {
                                                    echo 'Invalid date';
                                                }
                                            } else {
                                                echo 'No date';
                                            }
                                        ?>
                                    </td>
                                    <td>
                                        <?php if($article['featured']): ?>
                                            <span class="status-badge featured-badge">Featured</span>
                                        <?php else: ?>
                                            <span class="status-badge standard-badge">Standard</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="action-buttons">
                                        <button class="edit-btn" onclick="window.location.href='NewsAdmin.php?edit_news=<?= $article['id']; ?>'">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <a href="NewsDetail.php?id=<?= $article['id']; ?>" class="view-btn" target="_blank">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                        <button class="delete-btn" onclick="confirmDelete(<?= $article['id']; ?>)">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </td>
                                </tr>
                                <?php 
                                    endwhile;
                                else:
                                ?>
                                <tr>
                                    <td colspan="7" class="empty-table-message">
                                        <div>
                                            <i class="fas fa-newspaper"></i>
                                            <p>No news articles found. Click on "Add New Article" to create your first article.</p>
                                        </div>
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

              
                <div class="tab-content <?= $edit_article ? 'active' : '' ?>" id="add-news">
                    <form class="news-form" method="post" action="NewsAdmin.php" enctype="multipart/form-data">
                        <?php if($edit_article): ?>
                            <input type="hidden" name="news_id" value="<?= $edit_article['id']; ?>">
                            <input type="hidden" name="existing_image" value="<?= $edit_article['image_url']; ?>">
                        <?php endif; ?>
                        
                        <div class="form-group">
                            <label for="title">Article Title</label>
                            <input type="text" class="form-control" id="title" name="title" 
                                  value="<?= $edit_article ? htmlspecialchars($edit_article['title']) : ''; ?>" 
                                  required>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group" style="flex: 1;">
                                <label for="category">Category</label>
                                <select class="form-control" id="category" name="category" required>
                                    <option value="">Select Category</option>
                                    <option value="announcements" <?= ($edit_article && $edit_article['category'] == 'announcements') ? 'selected' : ''; ?>>
                                        Announcements
                                    </option>
                                    <option value="events" <?= ($edit_article && $edit_article['category'] == 'events') ? 'selected' : ''; ?>>
                                        Events
                                    </option>
                                    <option value="academics" <?= ($edit_article && $edit_article['category'] == 'academics') ? 'selected' : ''; ?>>
                                        Academics
                                    </option>
                                    <option value="achievements" <?= ($edit_article && $edit_article['category'] == 'achievements') ? 'selected' : ''; ?>>
                                        Achievements
                                    </option>
                                </select>
                            </div>
                            
                            <div class="form-group" style="flex: 1;">
                                <label for="author">Author</label>
                                <input type="text" class="form-control" id="author" name="author" 
                                      value="<?= $edit_article ? htmlspecialchars($edit_article['author']) : ($adminData['FirstName'] ?? '') . ' ' . ($adminData['LastName'] ?? ''); ?>" 
                                      required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group" style="flex: 1;">
                                <label for="publish_date">Publish Date</label>
                                <?php
                                
                                $currentDate = date('Y-m-d');
                                $publishDate = $currentDate; 

                                if ($edit_article && !empty($edit_article['publish_date'])) {
                                    $dateTimestamp = strtotime($edit_article['publish_date']);
                                    if ($dateTimestamp !== false) {
                                        $publishDate = date('Y-m-d', $dateTimestamp);
                                    }
                                }
                                ?>
                                <input type="date" class="form-control" id="publish_date" name="publish_date" 
                                      value="<?= htmlspecialchars($publishDate); ?>" 
                                      required>
                            </div>
                            
                            <div class="form-group" style="flex: 1;">
                                <label for="featured" class="checkbox-label">
                                    <input type="checkbox" id="featured" name="featured" value="1" 
                                          <?= ($edit_article && $edit_article['featured'] == 1) ? 'checked' : ''; ?>>
                                    <span>Feature this article</span>
                                </label>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="image">Article Image</label>
                            <?php if($edit_article && $edit_article['image_url']): ?>
                                <div class="current-image">
                                    <img src="<?= htmlspecialchars($edit_article['image_url']); ?>" alt="Current Image">
                                    <p>Current image. Upload a new one to replace it.</p>
                                </div>
                            <?php endif; ?>
                            
                            <div class="file-upload-container">
                                <input type="file" class="form-control-file" id="image" name="image" accept="image/*" onchange="updateFileName(this)">
                                <div class="file-upload-button">
                                    <i class="fas fa-upload"></i> Choose Image File
                                </div>
                                <span id="file-name-display" class="file-name-display">No file selected</span>
                                <p class="file-upload-help">Supported formats: JPG, PNG, GIF. Maximum size: 5MB</p>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="content">Article Content</label>
                            <textarea class="form-control" id="content" name="content" rows="15" required><?= $edit_article ? htmlspecialchars($edit_article['content']) : ''; ?></textarea>
                        </div>
                        
                        <div class="form-actions">
                            <?php if($edit_article): ?>
                                <a href="NewsAdmin.php" class="cancel-btn">
                                    Cancel Edit
                                </a>
                            <?php endif; ?>
                            <button type="submit" name="submit_news" class="save-btn">
                                <i class="fas fa-save"></i> <?= $edit_article ? 'Update Article' : 'Publish Article'; ?>
                            </button>
                        </div>
                        
                        
                        <div class="preview-section">
                            <h3><i class="fas fa-eye"></i> Live Preview</h3>
                            <div class="news-preview">
                                <div id="preview-title" class="preview-article-title">
                                    <?= $edit_article ? htmlspecialchars($edit_article['title']) : 'Article Title Preview'; ?>
                                </div>
                                <div class="preview-meta">
                                    <span id="preview-category"><?= $edit_article ? htmlspecialchars($edit_article['category']) : 'Category'; ?></span> • 
                                    <span id="preview-author"><?= $edit_article ? htmlspecialchars($edit_article['author']) : 'Author'; ?></span> • 
                                    <span id="preview-date"><?= $edit_article ? date('M d, Y', strtotime($edit_article['publish_date'])) : date('M d, Y'); ?></span>
                                </div>
                                <div id="preview-content" class="preview-content">
                                    <?= $edit_article ? nl2br(htmlspecialchars($edit_article['content'])) : 'Article content preview will appear here as you type...'; ?>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <script>
      
        const tabs = document.querySelectorAll('.admin-tab');
        const tabContents = document.querySelectorAll('.tab-content');
        
        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                const target = tab.dataset.tab;
                
                tabs.forEach(t => t.classList.remove('active'));
                tabContents.forEach(content => content.classList.remove('active'));
                
                tab.classList.add('active');
                document.getElementById(target).classList.add('active');
            });
        });
        
    
        const titleInput = document.getElementById('title');
        const categorySelect = document.getElementById('category');
        const authorInput = document.getElementById('author');
        const contentInput = document.getElementById('content');
        
        const previewTitle = document.getElementById('preview-title');
        const previewCategory = document.getElementById('preview-category');
        const previewAuthor = document.getElementById('preview-author');
        const previewContent = document.getElementById('preview-content');
        
        if(titleInput && previewTitle) {
            titleInput.addEventListener('input', () => {
                previewTitle.textContent = titleInput.value || 'Article Title Preview';
            });
        }
        
        if(categorySelect && previewCategory) {
            categorySelect.addEventListener('change', () => {
                previewCategory.textContent = categorySelect.options[categorySelect.selectedIndex].text;
            });
        }
        
        if(authorInput && previewAuthor) {
            authorInput.addEventListener('input', () => {
                previewAuthor.textContent = authorInput.value || 'Author';
            });
        }
        
        if(contentInput && previewContent) {
            contentInput.addEventListener('input', () => {
                previewContent.innerHTML = contentInput.value.replace(/\n/g, '<br>') || 'Article content preview will appear here as you type...';
            });
        }
        
        const publishDateInput = document.getElementById('publish_date');
        const previewDate = document.getElementById('preview-date');
        
        if(publishDateInput && previewDate) {
            publishDateInput.addEventListener('change', () => {
                const date = new Date(publishDateInput.value);
                const options = { month: 'short', day: 'numeric', year: 'numeric' };
                previewDate.textContent = date.toLocaleDateString('en-US', options);
            });
        }
        
        
        function confirmDelete(id) {
            if(confirm('Are you sure you want to delete this news article? This cannot be undone.')) {
                window.location.href = 'NewsAdmin.php?delete_news=' + id;
            }
        }
        
       
        function toggleAdminMenu() {
            const profileMenu = document.getElementById('adminProfileMenu');
            profileMenu.classList.toggle('active');
            
            
            document.addEventListener('click', function(event) {
                const isClickInside = event.target.closest('.profile-dropdown');
                if (!isClickInside && profileMenu.classList.contains('active')) {
                    profileMenu.classList.remove('active');
                }
            }, { once: true });
        }
        
        
        function toggleMenu() {
            document.getElementById('sidebar').classList.toggle('active');
        }
        
        
        function filterArticles() {
            const category = document.getElementById('categoryFilter').value;
            const searchText = document.getElementById('searchInput').value.toLowerCase();
            const rows = document.querySelectorAll('.news-table tbody tr');
            
            rows.forEach(row => {
                const rowCategory = row.getAttribute('data-category');
                const title = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
                const author = row.querySelector('td:nth-child(4)').textContent.toLowerCase();
                
                const categoryMatch = !category || rowCategory === category;
                const searchMatch = !searchText || 
                    title.includes(searchText) || 
                    author.includes(searchText);
                
                if (categoryMatch && searchMatch) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }
        
        
        if (document.getElementById('searchInput')) {
            document.getElementById('searchInput').addEventListener('input', filterArticles);
        }
        
        
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                const alerts = document.querySelectorAll('.alert');
                alerts.forEach(alert => {
                    alert.style.opacity = '0';
                    setTimeout(() => {
                        alert.style.display = 'none';
                    }, 500);
                });
            }, 5000);
        });
        
      
        const fileInput = document.getElementById('image');
        const fileNameDisplay = document.getElementById('fileNameDisplay');
        
        if (fileInput && fileNameDisplay) {
            fileInput.addEventListener('change', () => {
                const fileName = fileInput.files[0] ? fileInput.files[0].name : 'No file chosen';
                fileNameDisplay.textContent = fileName;
                fileNameDisplay.classList.toggle('active', !!fileInput.files[0]);
            });
        }

      
        function updateFileName(input) {
            const fileNameDisplay = document.getElementById('file-name-display');
            
            if (input.files && input.files[0]) {
                const fileName = input.files[0].name;
                const fileSize = Math.round(input.files[0].size / 1024); 
                
                fileNameDisplay.innerHTML = `<i class="fas fa-file-image"></i> ${fileName} (${fileSize} KB)`;
                fileNameDisplay.classList.add('active');
                
              
                const reader = new FileReader();
                reader.onload = function(e) {
                    
                    const currentImage = document.querySelector('.current-image img');
                    if (currentImage) {
                        currentImage.src = e.target.result;
                        document.querySelector('.current-image p').textContent = 'New image preview (not saved yet)';
                    } else {
                       
                        const previewContainer = document.createElement('div');
                        previewContainer.className = 'current-image';
                        
                        const previewImage = document.createElement('img');
                        previewImage.src = e.target.result;
                        previewImage.alt = 'Image Preview';
                        
                        const previewText = document.createElement('p');
                        previewText.textContent = 'Image preview (not saved yet)';
                        
                        previewContainer.appendChild(previewImage);
                        previewContainer.appendChild(previewText);
                        
                        const fileUploadContainer = document.querySelector('.file-upload-container');
                        fileUploadContainer.parentNode.insertBefore(previewContainer, fileUploadContainer);
                    }
                }
                reader.readAsDataURL(input.files[0]);
            } else {
                fileNameDisplay.innerHTML = 'No file selected';
                fileNameDisplay.classList.remove('active');
            }
        }
        
      
        document.addEventListener('DOMContentLoaded', function() {
            const fileInput = document.getElementById('image');
            if (fileInput && fileInput.files && fileInput.files[0]) {
                updateFileName(fileInput);
            }
        });
    </script>

    <style>
       
        .news-table .news-image-cell {
            width: 100px;
        }
        
        .news-image {
            width: 80px;
            height: 60px;
            object-fit: cover;
            border-radius: 4px;
        }
        
        .category-badge {
            padding: 4px 8px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            text-transform: capitalize;
        }
        
        .category-badge.announcements {
            background-color: #e3f2fd;
            color: #1565c0;
        }
        
        .category-badge.events {
            background-color: #e8f5e9;
            color: #2e7d32;
        }
        
        .category-badge.academics {
            background-color: #f3e5f5;
            color: #7b1fa2;
        }
        
        .category-badge.achievements {
            background-color: #fff3e0;
            color: #e65100;
        }
        
        .featured-badge {
            background-color: #fff3e0;
            color: #e65100;
            border: 1px solid #ffe0b2;
        }
        
        .standard-badge {
            background-color: #f5f5f5;
            color: #616161;
            border: 1px solid #e0e0e0;
        }
        
        .view-btn {
            background-color: #e8f5e9;
            color: #2e7d32;
            border: none;
            border-radius: 6px;
            font-size: 13px;
            padding: 6px 12px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: all 0.2s;
            font-weight: 500;
            text-decoration: none;
        }
        
        .view-btn:hover {
            background-color: #2e7d32;
            color: white;
        }
        
        .news-form {
            padding: 0;
            box-shadow: none;
            background: transparent;
        }
        
        .checkbox-label {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 28px;
        }
        
        .checkbox-label input {
            width: auto;
        }
        
        .preview-section {
            margin-top: 30px;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }
        
        .preview-section h3 {
            color: #1a237e;
            margin-bottom: 20px;
            font-size: 18px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .news-preview {
            padding: 20px;
            border-radius: 8px;
            background: #f8f9fa;
            border: 1px solid #e9ecef;
        }
        
        .preview-article-title {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 10px;
            color: #333;
        }
        
        .preview-meta {
            color: #666;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .preview-content {
            line-height: 1.6;
            color: #444;
        }
        
        .current-image {
            margin-bottom: 15px;
        }
        
        .current-image img {
            max-width: 200px;
            max-height: 150px;
            border-radius: 4px;
            border: 1px solid #ddd;
        }
        
        .current-image p {
            font-size: 13px;
            color: #666;
            margin-top: 5px;
        }
        
        
        @media (max-width: 992px) {
            .news-image {
                width: 60px;
                height: 45px;
            }
        }
        
        .admin-tab {
            border: none;
            background: none;
            padding: 12px 20px;
            font-size: 15px;
            font-weight: 500;
            color: #555;
            border-bottom: 2px solid transparent;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .admin-tab.active {
            border-bottom: 2px solid #1a237e;
            color: #1a237e;
        }
        
        .admin-tab i {
            margin-right: 8px;
        }
        
        .admin-tabs {
            border-bottom: 1px solid #eee;
            margin-bottom: 20px;
            display: flex;
        }
        
       
        .file-upload-container {
            position: relative;
            margin-bottom: 20px;
        }
        
        .file-upload-container input[type="file"] {
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
            z-index: 2;
        }
        
        .file-upload-button {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background-color: #f0f4ff;
            color: #1a237e;
            padding: 12px 20px;
            border-radius: 6px;
            font-weight: 500;
            border: 1px solid #e0e6ff;
            cursor: pointer;
            transition: all 0.2s ease;
            margin-right: 15px;
        }
        
        .file-upload-button:hover {
            background-color: #e3e9ff;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        
        .file-upload-button i {
            font-size: 18px;
        }
        
        .file-name-display {
            display: inline-flex;
            align-items: center;
            font-size: 14px;
            color: #666;
            margin-left: 10px;
            background: #f5f5f5;
            padding: 6px 12px;
            border-radius: 4px;
            max-width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .file-name-display.active {
            background: #e1f5fe;
            color: #0277bd;
            border: 1px solid #b3e5fc;
        }
        
        .file-name-display i {
            margin-right: 6px;
            color: #0277bd;
        }
        
        .file-upload-help {
            margin-top: 8px;
            font-size: 12px;
            color: #666;
        }
        
        
        
       
        .admin-nav-btn {
            display: inline-flex;
            align-items: center;
            padding: 8px 16px;
            margin-right: 10px;
            border-radius: 4px;
            background-color: #f0f2f5;
            color: #444;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s ease;
            position: relative;
        }
        
        .admin-nav-btn i {
            margin-right: 8px;
            font-size: 16px;
        }
        
        .admin-nav-btn:hover {
            background-color: #e4e6e9;
            color: #1a237e;
        }
        
        .admin-nav-btn.active {
            background-color: #1a237e;
            color: white;
        }
        
        
        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background-color: #f44336;
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            font-weight: bold;
            animation: pulse 1.5s infinite;
        }
        
        @keyframes pulse {
            0% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(244, 67, 54, 0.7);
            }
            
            70% {
                transform: scale(1.1);
                box-shadow: 0 0 0 5px rgba(244, 67, 54, 0);
            }
            
            100% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(244, 67, 54, 0);
            }
        }
        
       
        .teacher-subject-badge {
            display: inline-flex;
            align-items: center;
            padding: 6px 12px;
            margin-right: 15px;
            background-color: #e3f2fd;
            color: #0d47a1;
            border-radius: 4px;
            font-weight: 500;
            font-size: 14px;
        }
        
        .teacher-subject-badge i {
            margin-right: 8px;
        }
    </style>
</body>
</html>