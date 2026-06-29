<?php
session_start();
require 'Config.php'; 


if(!isset($conn) || $conn->connect_error) {
    die("Database connection failed: " . ($conn->connect_error ?? "Connection variable not set"));
}

error_reporting(E_ALL);
ini_set('display_errors', 1);


$isAuthorized = false;
$userData = null;
$isTeacher = false;
$isAdmin = false;


$teacherSubject = '';

if(!empty($_SESSION["Admin_ID"])) {
    $Admin_ID = $_SESSION["Admin_ID"];
    

    $teacherQuery = "SELECT * FROM table_admin WHERE Admin_ID = ? AND role = 'teacher'";
    $stmt = $conn->prepare($teacherQuery);
    $stmt->bind_param("i", $Admin_ID);
    $stmt->execute();
    $teacherResult = $stmt->get_result();
    
    if($teacherResult && $teacherResult->num_rows > 0) {
        $teacherData = $teacherResult->fetch_assoc();
        $isTeacher = true;
        $isAuthorized = true;
        $teacherSubject = $teacherData['subject']; 
        
      
        $adminData = array(
            'Username' => $teacherData['Username'],
            'profile_picture' => $teacherData['profile_picture'] ?? 'admin-default.jpg'
        );
    } else {
    
        $isTeacher = false;
        $isAuthorized = true;
        $teacherSubject = '';
        
   
        $adminQuery = "SELECT * FROM table_admin WHERE Admin_ID = ?";
        $stmt = $conn->prepare($adminQuery);
        $stmt->bind_param("i", $Admin_ID);
        $stmt->execute();
        $adminResult = $stmt->get_result();
        
        if($adminResult && $adminResult->num_rows > 0) {
            $adminData = $adminResult->fetch_assoc();
           
            if(!isset($adminData['profile_picture']) || empty($adminData['profile_picture'])) {
                $adminData['profile_picture'] = 'admin-default.jpg';
            }
        } else {
          
            $adminData = array(
                'Username' => 'Administrator',
                'profile_picture' => 'admin-default.jpg'
            );
        }
    }
} else {
  
    header("Location: Login.php?error=unauthorized");
    exit;
}


try {
   
    $check_table = $conn->query("SHOW TABLES LIKE 'collections'");
    if ($check_table->num_rows == 0) {

        $sql1 = "CREATE TABLE IF NOT EXISTS collections (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            description TEXT,
            date DATE NOT NULL,
            cover_image VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        if (!$conn->query($sql1)) {
            echo "Error creating collections table: " . $conn->error;
            exit;
        }
        
    
        $sql2 = "CREATE TABLE IF NOT EXISTS collection_images (
            id INT AUTO_INCREMENT PRIMARY KEY,
            collection_id INT NOT NULL,
            image_id INT NOT NULL,
            sort_order INT DEFAULT 0,
            added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (collection_id) REFERENCES collections(id) ON DELETE CASCADE
        )";
        
        if (!$conn->query($sql2)) {
            echo "Error creating collection_images table: " . $conn->error;
            exit;
        }
        
        
        $sql3 = "CREATE TABLE IF NOT EXISTS images (
            id INT AUTO_INCREMENT PRIMARY KEY,
            filename VARCHAR(255) NOT NULL,
            description TEXT,
            upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        
        if (!$conn->query($sql3)) {
            echo "Error creating images table: " . $conn->error;
            exit;
        }
    }
} catch (Exception $e) {
    echo "Error checking database structure: " . $e->getMessage();
    exit;
}


$manage_collection = isset($_GET['collection']) ? intval($_GET['collection']) : 0;
$collection_info = null;

if($manage_collection) {
    $collection_query = "SELECT * FROM collections WHERE id = " . mysqli_real_escape_string($conn, $manage_collection);
    $collection_result = $conn->query($collection_query);
    
    if($collection_result && $collection_result->num_rows > 0) {
        $collection_info = $collection_result->fetch_assoc();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Gallery Management - CMRICTHS</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="styles.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="gallery.css?v=<?php echo time(); ?>">
    
    <style>
       
        :root {
            --primary-color: #3498db;
            --primary-dark: #2980b9;
            --secondary-color: #2ecc71;
            --secondary-dark: #27ae60;
            --danger-color: #e74c3c;
            --danger-dark: #c0392b;
            --light-bg: #f8f9fa;
            --border-color: #e9ecef;
            --text-dark: #343a40;
            --text-muted: #6c757d;
            --shadow-sm: 0 2px 4px rgba(0,0,0,0.05);
            --shadow-md: 0 4px 8px rgba(0,0,0,0.1);
            --shadow-lg: 0 8px 16px rgba(0,0,0,0.1);
            --radius-sm: 4px;
            --radius-md: 8px;
            --radius-lg: 12px;
            --transition: all 0.25s ease;
        }
        
        body {
            background-color: #f5f7fa;
        }
        
        .main-content {
            padding: 30px;
            max-width: 1400px;
            margin: 0 auto;
        }
        
    
        .admin-header {
            background: linear-gradient(135deg, #343a40, #495057);
            color: white;
            padding: 30px 35px; 
            border-radius: var(--radius-md);
            margin-bottom: 35px; 
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: var(--shadow-md);
            position: relative;
            overflow: hidden;
        }
        
        .admin-header::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100"><rect fill="none" width="100" height="100"/><path d="M0 0L100 100M20 0L100 80M0 20L80 100M40 0L100 60M0 40L60 100M60 0L100 40M0 60L40 100M80 0L100 20M0 80L20 100" stroke-width="1" stroke="rgba(255,255,255,0.05)"/></svg>');
            opacity: 0.5;
            z-index: 0;
        }
        
        .admin-header > * {
            position: relative;
            z-index: 1;
        }
        
        .admin-header h1 {
            margin: 0;
            font-size: 32px; 
            font-weight: 700;
            letter-spacing: 0.5px;
        }
        
        .admin-header .back-link {
            color: rgba(255, 255, 255, 0.85);
            text-decoration: none;
            display: flex;
            align-items: center;
            font-size: 14px;
            padding: 8px 12px;
            border-radius: var(--radius-sm);
            background-color: rgba(255, 255, 255, 0.1);
            transition: var(--transition);
            border: 1px solid rgba(255, 255, 255, 0.2);
            margin-left: 10px;
        }
        
        .admin-header .back-link:hover {
            background-color: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }
        
        .admin-header .back-link i {
            margin-right: 8px;
        }
        
        
        .gallery-container {
            background-color: white;
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-sm);
            overflow: hidden;
            margin-bottom: 30px;
            max-width: 1600px; 
            margin-right: auto;
        }
        
        .gallery-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 25px;
            border-bottom: 1px solid var(--border-color);
            background-color: var(--light-bg);
        }
        
        .gallery-header h2 {
            margin: 0;
            font-size: 20px;
            color: var(--text-dark);
            font-weight: 600;
        }
        
        .section-description {
            padding: 15px 25px;
            margin: 0;
            color: var(--text-muted);
            font-size: 15px;
            border-bottom: 1px solid var(--border-color);
        }
        
  
        .collections-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); 
            gap: 30px; 
            padding: 30px;
        }
        
        .collection-item {
            background-color: white;
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-sm);
            overflow: hidden;
            transition: var(--transition);
            height: 100%;
            display: flex;
            flex-direction: column;
            border: 1px solid var(--border-color);
        }
        
        .collection-item:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-md);
        }
        
        .collection-image-container {
            position: relative;
            padding-top: 70%; 
            overflow: hidden;
        }
        
        .collection-image {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: var(--transition);
        }
        
        .collection-item:hover .collection-image {
            transform: scale(1.05);
        }
        
        .collection-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 15px;
            background: linear-gradient(to top, rgba(0,0,0,0.8), rgba(0,0,0,0));
            color: white;
            transition: var(--transition);
        }
        
        .collection-count {
            display: inline-flex;
            align-items: center;
            background-color: rgba(255, 255, 255, 0.2);
            padding: 5px 10px;
            border-radius: 50px;
            font-size: 13px;
            font-weight: 500;
        }
        
        .collection-count i {
            margin-right: 5px;
        }
        
        .collection-info {
            padding: 20px; 
            flex-grow: 1;
        }
        
        .collection-info h3 {
            margin: 0 0 12px 0; 
            font-size: 20px; 
            font-weight: 600;
            color: var(--text-dark);
        }
        
        .collection-date {
            display: flex;
            align-items: center;
            color: var(--text-muted);
            font-size: 14px;
            margin: 0;
        }
        
        .collection-date::before {
            content: '\f073';
            font-family: 'Font Awesome 5 Free';
            margin-right: 8px;
            opacity: 0.6;
        }
        
       
        .collection-actions {
            display: flex;
            justify-content: space-between;
            padding: 15px;
            background-color: #f8f9fa;
            border-top: 1px solid var(--border-color);
            gap: 10px; 
        }
        
        .collection-actions .admin-btn {
            flex: 1;
            padding: 10px;
            font-size: 14px;
            white-space: nowrap;
            text-align: center;
            justify-content: center;
        }
        
        .collection-actions .admin-btn i {
            margin-right: 6px; 
            font-size: 14px; 
        }
        
        .admin-btn {
            padding: 12px 15px;
            border: none;
            border-radius: var(--radius-sm);
            cursor: pointer;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
            font-size: 15px;
        }
        
        .admin-btn i {
            margin-right: 10px; 
            font-size: 18px; 
        }
        
        .edit-btn {
            background-color: var(--primary-color);
            color: white;
        }
        
        .edit-btn:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
        }
        
        .view-btn {
            background-color: var(--secondary-color);
            color: white;
        }
        
        .view-btn:hover {
            background-color: var(--secondary-dark);
            transform: translateY(-2px);
        }
        
        .delete-btn {
            background-color: var(--danger-color);
            color: white;
        }
        
        .delete-btn:hover {
            background-color: var(--danger-dark);
            transform: translateY(-2px);
        }
        
        
        .add-collection {
            background-color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            transition: var (--transition);
            border: 2px dashed var(--border-color);
            cursor: pointer;
        }
        
        .add-collection:hover {
            background-color: var(--light-bg);
            border-color: var(--primary-color);
            transform: translateY(-5px);
        }
        
        .add-collection-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 50px 20px; 
            width: 100%;
        }
        
        .add-icon {
            width: 100px; 
            height: 100px; 
            border-radius: 50%;
            background-color: rgba(52, 152, 219, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 25px; 
            transition: var (--transition);
        }
        
        .add-icon i {
            font-size: 48px;
            color: var(--primary-color);
        }
        
        .add-collection:hover .add-icon {
            transform: scale(1.1);
            background-color: rgba(52, 152, 219, 0.2);
        }
        
        .add-collection h3 {
            margin: 0 0 15px 0;
            color: var(--text-dark);
            font-weight: 600;
            font-size: 22px; 
            transition: var(--transition);
        }
        
        .add-collection p {
            margin: 0;
            color: var(--text-muted);
            font-size: 16px; 
            transition: var(--transition);
        }
        
        .add-collection:hover h3 {
            color: var(--primary-color);
        }
        
      
        .manage-collection-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: linear-gradient(to right, var(--light-bg), white);
            padding: 20px 25px;
            border-radius: var(--radius-md);
            margin-bottom: 20px;
            border-left: 5px solid var(--primary-color);
        }
        
        .manage-collection-title {
            margin: 0;
            font-size: 22px;
            color: var(--text-dark);
            font-weight: 600;
        }
        
        .manage-collection-details {
            display: flex;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
        }
        
        .manage-collection-detail {
            display: flex;
            align-items: center;
            font-size: 15px;
            color: var(--text-muted);
            background-color: white;
            padding: 8px 15px;
            border-radius: 50px;
            box-shadow: var(--shadow-sm);
        }
        
        .manage-collection-detail i {
            margin-right: 8px;
            color: var(--primary-color);
        }
        
        
        .action-bar {
            background-color: white;
            padding: 15px 25px;
            border-radius: var (--radius-md);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border-color);
        }
        
        .action-bar-left, .action-bar-right {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        
        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); 
            gap: 25px; 
            padding: 30px; 
        }
        
        .gallery-item {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            transition: all 0.3s ease;
            border: 1px solid var(--border-color);
            position: relative;
            height: 100%; 
            display: flex;
            flex-direction: column;
        }
        
        .gallery-item:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-md);
        }
        
        .gallery-img-container {
            position: relative;
            padding-top: 66.67%;
            overflow: hidden;
        }
        
        .gallery-img {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: var(--transition);
        }
        
        .gallery-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: var(--transition);
        }
        
        .gallery-item:hover .gallery-overlay {
            opacity: 1;
        }
        
        .gallery-actions {
            display: flex;
            gap: 8px;
        }
        
        .gallery-action-btn {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.9);
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-dark);
            cursor: pointer;
            transition: all 0.2s ease;
            padding: 0; 
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }
        
        .gallery-action-btn i {
            margin: 0; 
            font-size: 14px;
        }
        
        .gallery-action-btn:hover {
            transform: scale(1.1);
            background-color: var(--primary-color);
            color: white;
        }
        
        .gallery-info {
            padding: 12px 15px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        
        .gallery-description {
            margin: 0 0 8px 0;
            font-size: 14px;
            color: var (--text-dark);
            font-weight: 500;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .gallery-date {
            margin: 0;
            font-size: 13px;
            color: var (--text-muted);
            display: flex;
            align-items: center;
        }
        
        .gallery-date i {
            margin-right: 6px;
            opacity: 0.7;
        }
        
        
        .no-images {
            text-align: center;
            padding: 50px 20px;
            background: linear-gradient(to bottom right, var(--light-bg), white);
            border-radius: var(--radius-md);
            margin-bottom: 20px;
        }
        
        .no-images i {
            font-size: 60px;
            color: #dee2e6;
            margin-bottom: 20px;
            opacity: 0.7;
        }
        
        .no-images h3 {
            margin: 0 0 10px 0;
            color: var (--text-dark);
            font-weight: 600;
        }
        
        .no-images p {
            margin: 0;
            color: var (--text-muted);
            font-size: 16px;
        }
        
       
        .collection-modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            overflow-y: auto;
            animation: modalFadeIn 0.3s ease;
        }
        
        @keyframes modalFadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }
        
        .collection-modal-content {
            background-color: white;
            margin: 60px auto;
            max-width: 650px;
            width: 90%;
            border-radius: var(--radius-md);
            position: relative;
            padding: 25px;
            box-shadow: var(--shadow-lg);
            animation: modalSlideIn 0.3s ease;
        }
        
        @keyframes modalSlideIn {
            from {
                transform: translateY(-30px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        
        .collection-modal-content h3 {
            margin-top: 0;
            color: var(--text-dark);
            font-size: 22px;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        
        .modal-close {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 22px;
            cursor: pointer;
            color: var(--text-muted);
            transition: var(--transition);
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background-color: var(--light-bg);
        }
        
        .modal-close:hover {
            background-color: var(--danger-color);
            color: white;
            transform: rotate(90deg);
        }
        
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text-dark);
            font-size: 15px;
        }
        
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-sm);
            font-family: inherit;
            font-size: 15px;
            transition: var(--transition);
            background-color: var(--light-bg);
            color: var (--text-dark);
        }
        
        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--primary-color);
            background-color: white;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
        }
        
        .form-group textarea {
            min-height: 120px;
            resize: vertical;
        }
        
        .file-input-container {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .file-input-label {
            display: inline-flex;
            align-items: center;
            padding: 12px 20px;
            background-color: var(--light-bg);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-sm);
            cursor: pointer;
            color: var(--text-dark);
            font-weight: 500;
            transition: var(--transition);
            max-width: fit-content;
        }
        
        .file-input-label:hover {
            background-color: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }
        
        .file-input-label i {
            margin-right: 10px;
            font-size: 18px;
        }
        
        .file-name {
            font-size: 14px;
            color: var(--text-muted);
            padding: 5px 10px;
            background-color: white;
            border-radius: var (--radius-sm);
            border: 1px solid var(--border-color);
            margin-top: 5px;
        }
        
        input[type="file"] {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            border: 0;
        }
        
        .modal-actions {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            margin-top: 25px;
        }
        
        .modal-btn {
            padding: 12px 20px;
            border: none;
            border-radius: var(--radius-sm);
            cursor: pointer;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
        }
        
        .modal-btn i {
            margin-right: 8px;
        }
        
        .modal-btn.edit-btn {
            background-color: var(--primary-color);
            color: white;
        }
        
        .modal-btn.edit-btn:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
        }
        
        .modal-btn.cancel-btn {
            background-color: #e9ecef;
            color: var(--text-dark);
        }
        
        .modal-btn.cancel-btn:hover {
            background-color: #dee2e6;
            transform: translateY(-2px);
        }
        
      
        .gallery-item.selected {
            box-shadow: 0 0 0 3px var(--primary-color);
            transform: translateY(-5px);
        }
        
        .gallery-item.selected::after {
            content: '\f00c';
            font-family: 'Font Awesome 5 Free';
            font-weight: 900;
            position: absolute;
            top: 10px;
            right: 10px;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background-color: var(--primary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            z-index: 5;
        }
        
        /* Status Messages */
        .status-message {
            padding: 15px 20px;
            border-radius: var (--radius-md);
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            animation: fadeInDown 0.5s ease;
        }
        
        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .status-message i {
            margin-right: 15px;
            font-size: 22px;
        }
        
        .status-success {
            background-color: #d4edda;
            color: #155724;
            border-left: 5px solid #28a745;
        }
        
        .status-error {
            background-color: #f8d7da;
            color: #721c24;
            border-left: 5px solid #dc3545;
        }
        
        
        .filter-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .filter-label {
            font-size: 14px;
            color: var(--text-muted);
            font-weight: 500;
        }
        
        .filter-select {
            padding: 8px 12px;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-sm);
            background-color: white;
            color: var(--text-dark);
            font-size: 14px;
            cursor: pointer;
        }
        
     
        @media (max-width: 992px) {
            .main-content {
                padding: 20px;
            }
            
            .admin-header {
                padding: 20px;
            }
            
            .collection-actions {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 768px) {
            .admin-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .admin-header > div {
                display: flex;
                width: 100%;
                justify-content: space-between;
            }
            
            .manage-collection-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .manage-collection-details {
                width: 100%;
                gap: 10px;
            }
            
            .action-bar {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }
            
            .action-bar-left, 
            .action-bar-right {
                width: 100%;
                justify-content: space-between;
            }
            
            .modal-actions {
                flex-direction: column;
            }
            
            .modal-btn {
                width: 100%;
            }
        }
        
        @media (max-width: 576px) {
            .gallery-grid {
                grid-template-columns: 1fr;
            }
            
            .collections-grid {
                grid-template-columns: 1fr;
            }
            
            .collection-actions {
                flex-direction: column;
                gap: 10px;
            }
            
            .collection-actions .admin-btn {
                width: 100%;
            }
            
          
            .collection-actions div[style="width: 10px;"] {
                display: none;
            }
            
            .gallery-actions {
                gap: 15px; 
            }
            
            .gallery-action-btn {
                width: 42px; 
                height: 42px;
            }
        }
        
      
        .main-nav {
            background-color: #ffffff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .nav-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 20px;
            max-width: 1400px;
            margin: 0 auto;
            height: 70px;
        }
        
        .nav-left {
            display: flex;
            align-items: center;
        }
        
        .nav-brand {
            display: flex;
            align-items: center;
            text-decoration: none;
            color: var(--text-dark);
        }
        
        .nav-logo {
            height: 40px;
            margin-right: 12px;
        }
        
        .brand-text h1 {
            margin: 0;
            font-size: 18px;
            font-weight: 700;
            color: var(--text-dark);
        }
        
        .brand-text p {
            margin: 0;
            font-size: 12px;
            color: var(--text-muted);
        }
        
        .nav-right {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        <a href="Admin.php" class="admin-nav-btn">
    <i class="fas fa-tachometer-alt"></i> Dashboard
</a>
        <a href="adminschedules.php" class="admin-nav-btn">
            <i class="fas fa-calendar-alt"></i> Manage Schedules
        </a>
        <a href="NewsAdmin.php" class="admin-nav-btn">
            <i class="fas fa-newspaper"></i> Manage News
        </a>
        <a href="admingallery.php" class="admin-nav-btn active">
            <i class="fas fa-images"></i> Manage Gallery
        </a>
        <a href="admincontacts.php" class="admin-nav-btn">
            <i class="fas fa-envelope"></i> Contact Feedback
            <?php
            // Get count of unread messages
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
</nav>

<nav class="main-nav">
    <div class="nav-container">
        <div class="nav-left">
            <a href="index.php" class="nav-brand">
                <img src="logo.png" alt="CMRICTHS Logo" class="nav-logo">
                <div class="brand-text">
                    <h1>CMRICTHS</h1>
                    <p>Admin Dashboard</p>
                </div>
            </a>
        </div>
        <div class="nav-right">
            <a href="Admin.php" class="admin-nav-btn">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a href="adminschedules.php" class="admin-nav-btn">
                <i class="fas fa-calendar-alt"></i> Manage Schedules
            </a>
            <a href="NewsAdmin.php" class="admin-nav-btn">
                <i class="fas fa-newspaper"></i> Manage News
            </a>
            <a href="admingallery.php" class="admin-nav-btn active">
                <i class="fas fa-images"></i> Manage Gallery
            </a>
            <a href="admincontacts.php" class="admin-nav-btn">
                <i class="fas fa-envelope"></i> Contact Feedback
                <?php
                // Get count of unread messages
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
                    <div class="profile-trigger" onclick="toggleProfileMenu()">
                        <img src="<?php echo $adminData['profile_picture'] ?? 'admin-default.jpg'; ?>" 
                             alt="Admin" class="profile-avatar">
                        <span class="profile-name"><?php echo htmlspecialchars($adminData['Username'] ?? 'Administrator'); ?></span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="profile-menu" id="profileMenu">
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

<script>
function toggleProfileMenu() {
    const profileMenu = document.getElementById('profileMenu');
    profileMenu.classList.toggle('active');
    
    // Close menu when clicking outside
    document.addEventListener('click', function closeMenu(e) {
        const menu = document.getElementById('profileMenu');
        const trigger = document.querySelector('.profile-trigger');
        
        if (!menu.contains(e.target) && !trigger.contains(e.target)) {
            menu.classList.remove('active');
            document.removeEventListener('click', closeMenu);
        }
    });
}
</script>

<div class="sidebar" id="sidebar">

</div>

    
    <main class="main-content">
        
        <div class="admin-header">
            <h1>Gallery Administration</h1>
    
        .admin-header {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            padding: 25px 30px;
            border-radius: var(--radius-md);
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.2);
            position: relative;
            overflow: hidden;
        }
        
        .admin-header::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100"><rect fill="none" width="100" height="100"/><path d="M0 0L100 100M20 0L100 80M0 20L80 100M40 0L100 60M0 40L60 100M60 0L100 40M0 60L40 100M80 0L100 20M0 80L20 100" stroke-width="1" stroke="rgba(255,255,255,0.1)"/></svg>');
            opacity: 0.5;
            z-index: 0;
        }
        
        .admin-header > * {
            position: relative;
            z-index: 1;
        }
        
        .admin-header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 700;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
        }
        
        .admin-header h1::before {
            content: '\f03e';
            font-family: 'Font Awesome 5 Free';
            font-weight: 900;
            margin-right: 12px;
            font-size: 24px;
            background-color: rgba(255, 255, 255, 0.2);
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .admin-header .back-link {
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            display: flex;
            align-items: center;
            font-size: 14px;
            padding: 10px 15px;
            border-radius: var(--radius-sm);
            background-color: rgba(255, 255, 255, 0.2);
            transition: all 0.2s ease;
            border: 1px solid rgba(255, 255, 255, 0.3);
            font-weight: 500;
        }
        
        .admin-header .back-link:hover {
            background-color: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
        }
        
        .admin-header .back-link i {
            margin-right: 8px;
        }
        
      
        .collection-item {
            transition: all 0.3s ease;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
            border: none;
        }
        
        .collection-item:hover {
            transform: translateY(-8px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }
        
        .collection-info {
            border-top: 1px solid var(--border-color);
        }
        
        .collection-actions {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            padding: 15px;
            background-color: #f8f9fa;
            border-top: 1px solid var(--border-color);
        }
        
        
        .collection-item {
            position: relative;
        }
        
        .collection-item::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            opacity: 0;
            transition: all 0.3s ease;
            border-radius: var(--radius-md) var(--radius-md) 0 0;
        }
        
        .collection-item:hover::after {
            opacity: 1;
        }
        
       
        .add-collection {
            border: 2px dashed var(--border-color);
            background-color: white;
            transition: all 0.3s ease;
        }
        
        .add-collection:hover {
            border-color: var(--primary-color);
            background-color: rgba(52, 152, 219, 0.03);
        }
        
        .add-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, rgba(52, 152, 219, 0.1), rgba(46, 204, 113, 0.1));
            margin-bottom: 20px;
            transition: all 0.3s ease;
            border: 2px dashed rgba(52, 152, 219, 0.3);
        }
        
        .add-icon i {
            font-size: 36px;
            background: linear-gradient(135deg, #3498db, #2ecc71);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .add-collection:hover .add-icon {
            transform: scale(1.1) rotate(5deg);
            border-style: solid;
        }
        
      
        .gallery-action-btn {
            background-color: var(--primary-color);
            color: white;
            padding: 10px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 500;
            font-size: 14px;
            display: flex;
            align-items: center;
            transition: all 0.2s ease;
            box-shadow: 0 3px 6px rgba(52, 152, 219, 0.2);
        }
        
        .gallery-action-btn i {
            margin-right: 8px;
        }
        
        .gallery-action-btn:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 5px 12px rgba(52, 152, 219, 0.3);
        }
        
       
        .collections-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
            padding: 25px;
        }
        
        @media (max-width: 1200px) {
            .menu-toggle {
                display: block;
                margin-left: 20px;
            }
            
            .nav-right {
                position: fixed;
                top: 70px;
                left: -100%;
                width: 250px;
                flex-direction: column;
                background-color: white;
                box-shadow: 5px 0 15px rgba(0,0,0,0.1);
                height: calc(100vh - 70px);
                transition: left 0.3s ease;
                align-items: flex-start;
                padding: 20px;
                z-index: 100;
                gap: 10px;
            }
            
            .nav-right.active {
                left: 0;
            }
            
            .admin-nav-btn {
                width: 100%;
            }
            
            .nav-divider {
                width: 100%;
                height: 1px;
                margin: 10px 0;
            }
        }
        
        @media (max-width: 992px) {
            .collections-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            }
            
            .admin-header {
                padding: 20px;
            }
        }
        
        @media (max-width: 576px) {
            .collection-actions {
                flex-direction: column;
                gap: 10px;
            }
            
            .collection-actions .admin-btn {
                width: 100%;
            }
            
            
            .collection-actions div[style="width: 10px;"] {
                display: none;
            }
        }
        
       
        .nav-profile {
            position: relative;
            margin-left: 15px;
        }

        .profile-dropdown {
            position: relative;
        }

        .profile-trigger {
            display: flex;
            align-items: center;
            cursor: pointer;
            padding: 8px;
            border-radius: 50px;
            transition: all 0.2s ease;
        }

        .profile-trigger:hover {
            background-color: var(--light-bg);
        }

        .profile-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 8px;
            border: 1px solid var(--border-color);
        }

        .profile-name {
            font-size: 14px;
            color: var(--text-dark);
            margin-right: 8px;
            font-weight: 500;
            display: none;
        }

        @media (min-width: 992px) {
            .profile-name {
                display: block;
            }
        }

        .profile-menu {
            position: absolute;
            top: 100%;
            right: 0;
            width: 280px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            padding: 15px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .profile-menu.active {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .profile-header {
            display: flex;
            align-items: center;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--border-color);
        }

        .profile-picture {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 15px;
            border: 1px solid var(--border-color);
        }

        .profile-info h4 {
            margin: 0 0 5px 0;
            font-size: 16px;
            color: var(--text-dark);
        }

        .profile-info p {
            margin: 0;
            color: var(--text-muted);
            font-size: 14px;
        }

        .profile-links {
            margin-top: 15px;
            display: grid;
            gap: 8px;
        }

        .profile-links a {
            padding: 10px 15px;
            display: flex;
            align-items: center;
            text-decoration: none;
            color: var(--text-dark);
            font-size: 14px;
            border-radius: 6px;
            transition: all 0.2s ease;
        }

        .profile-links a:hover {
            background-color: var(--light-bg);
        }

        .profile-links a.logout-link {
            color: var(--danger-color);
        }

        .profile-links a.logout-link:hover {
            background-color: rgba(231, 76, 60, 0.1);
        }

        .profile-links a i {
            margin-right: 12px;
            font-size: 16px;
            width: 16px;
        }

    

 
        .collection-item {
            background-color: white;
            border-radius: var(--radius-md);
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            transition: all 0.3s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
            border: 1px solid var(--border-color);
            position: relative;
        }

        .collection-item:hover {
            transform: translateY(-6px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

       
        .gallery-item {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            transition: all 0.3s ease;
            border: 1px solid var(--border-color);
            position: relative;
        }

        .gallery-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        
        .gallery-img-container {
            position: relative;
            padding-top: 66.67%; 
            overflow: hidden;
            border-bottom: 1px solid var(--border-color);
        }

        .gallery-img {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .gallery-item:hover .gallery-img {
            transform: scale(1.05);
        }

       
        .collection-image-container {
            position: relative;
            padding-top: 65%; 
            overflow: hidden;
            border-bottom: 1px solid var(--border-color);
        }

        .collection-image {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .collection-item:hover .collection-image {
            transform: scale(1.05);
        }

        
        .collection-actions {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            padding: 15px;
            background-color: #f8f9fa;
            border-top: 1px solid var(--border-color);
        }

    
        @media (max-width: 576px) {
            .collection-actions {
                flex-direction: column;
                gap: 10px;
            }
            
            .collection-actions .admin-btn {
                width: 100%;
            }
        }

        
        .gallery-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.6);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .gallery-item:hover .gallery-overlay {
            opacity: 1;
        }

       
        .collection-image-container::after,
        .gallery-img-container::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            box-shadow: inset 0 0 0 1px rgba(255,255,255,0.1);
            pointer-events: none;
        }

       
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            z-index: 1000;
            overflow: auto;
        }

        .modal-content {
            position: relative;
            background-color: white;
            margin: 5vh auto;
            width: 80%;
            max-width: 800px;
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-lg);
            padding: 25px;
            animation: modalFadeIn 0.3s ease;
        }

        @keyframes modalFadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .close-btn {
            position: absolute;
            top: 15px;
            right: 20px;
            font-size: 28px;
            font-weight: 700;
            color: var(--text-muted);
            cursor: pointer;
            transition: var(--transition);
        }

        .close-btn:hover {
            color: var(--danger-color);
        }

        .modal-content h2 {
            margin-top: 0;
            margin-bottom: 20px;
            color: var(--text-dark);
            font-size: 24px;
            font-weight: 600;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--border-color);
        }

       
        .modal-tabs {
            display: flex;
            list-style: none;
            padding: 0;
            margin: 0 0 20px 0;
            border-bottom: 1px solid var(--border-color);
        }

        .tab-item {
            padding: 12px 20px;
            cursor: pointer;
            font-weight: 500;
            color: var(--text-muted);
            position: relative;
            transition: var(--transition);
        }

        .tab-item:hover {
            color: var(--primary-color);
        }

        .tab-item.active {
            color: var(--primary-color);
        }

        .tab-item.active:after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0;
            width: 100%;
            height: 2px;
            background-color: var(--primary-color);
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

      
        .btn {
            padding: 10px 15px;
            border-radius: var(--radius-sm);
            border: none;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
        }

        .primary-btn {
            background-color: var(--primary-color);
            color: white;
        }

        .primary-btn:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
        }

        .cancel-btn {
            background-color: var(--light-bg);
            color: var(--text-dark);
        }

        .cancel-btn:hover {
            background-color: #e9ecef;
        }

       
        .image-preview-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            gap: 10px;
            margin-top: 20px;
        }

        .image-preview {
            border-radius: var(--radius-sm);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            background-color: white;
        }

        .image-preview img {
            width: 100%;
            height: 100px;
            object-fit: cover;
            display: block;
        }
        
        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text-dark);
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid var(--border-color);
        }

        
        .image-preview-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            gap: 10px;
            margin-top: 20px;
        }

        .image-preview {
            border-radius: var(--radius-sm);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            background-color: white;
        }

        .image-preview img {
            width: 100%;
            height: 100px;
            object-fit: cover;
            display: block;
        }

        .file-name {
            font-size: 12px;
            padding: 5px;
            text-align: center;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            background-color: var(--light-bg);
        }

        
        .existing-images {
            max-height: 400px;
            overflow-y: auto;
            margin: 0 -10px;
            padding: 10px;
        }

        .loading-text, .no-images, .error-text {
            text-align: center;
            padding: 40px 0;
            color: var(--text-muted);
        }

        .loading-text i {
            margin-right: 10px;
        }

        .error-text {
            color: var (--danger-color);
        }

        .images-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
            gap: 15px;
        }

        .existing-image {
            position: relative;
            border-radius: var(--radius-sm);
            overflow: hidden;
            border: 2px solid transparent;
            transition: var(--transition);
            cursor: pointer;
        }

        .existing-image.selected {
            border-color: var(--primary-color);
        }

        .existing-image img {
            width: 100%;
            height: 120px;
            object-fit: cover;
            display: block;
        }

        .image-select {
            position: absolute;
            top: 8px;
            right: 8px;
            width: 20px;
            height: 20px;
            z-index: 2;
        }

        .image-caption {
            font-size: 12px;
            padding: 6px;
            text-align: center;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            background-color: white;
        }

        /* Button Styles */
        .btn {
            padding: 10px 15px;
            border-radius: var(--radius-sm);
            border: none;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
        }

        .primary-btn {
            background-color: var(--primary-color);
            color: white;
        }

        .primary-btn:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
        }

        .cancel-btn {
            background-color: var(--light-bg);
            color: var(--text-dark);
        }

        .cancel-btn:hover {
            background-color: #e9ecef;
        }

        
        @media (max-width: 768px) {
            .modal-content {
                width: 90%;
                padding: 20px;
                margin: 10vh auto;
            }
            
            .images-grid {
                grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            }
        }

        @media (max-width: 480px) {
            .form-actions {
                flex-direction: column;
            }
            
            .form-actions .btn {
                width: 100%;
            }
            
            .tab-item {
                padding: 10px 15px;
                font-size: 14px;
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
                        <p>Admin Dashboard</p>
                    </div>
                </a>
            </div>
            <div class="nav-right">
                <a href="Admin.php" class="admin-nav-btn">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>    
                <a href="./adminschedules.php" class="admin-nav-btn">
                    <i class="fas fa-calendar-alt"></i> Manage Schedules
                </a>
                <a href="NewsAdmin.php" class="admin-nav-btn">
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

    <div class="sidebar" id="sidebar">

    </div>

    
    <main class="main-content">
        
        <div class="admin-header">
            <h1>Gallery Administration</h1>
            <div style="display: flex; gap: 15px;"> 
                <a href="Admin.php" class="back-link">
                    <i class="fas fa-tachometer-alt"></i> Admin Dashboard
                </a>
                <a href="gallery.php" class="back-link">
                    <i class="fas fa-arrow-left"></i> View Gallery
                </a>
            </div>
        </div>
        
        <?php if($manage_collection): ?>
       
        <div class="manage-collection-header">
            <h2 class="manage-collection-title">
                Managing: <?php echo htmlspecialchars($collection_info['name']); ?>
            </h2>
            <div class="manage-collection-details">
                <div class="manage-collection-detail">
                    <i class="far fa-calendar-alt"></i>
                    <?php 
                        $date = new DateTime($collection_info['date']);
                        echo $date->format('F j, Y'); 
                    ?>
                </div>
                
                <?php 
                
                $count_query = "SELECT COUNT(*) as photo_count FROM collection_images WHERE collection_id = " . mysqli_real_escape_string($conn, $manage_collection);
                $count_result = $conn->query($count_query);
                $photo_count = 0;
                
                if($count_result && $count_result->num_rows > 0) {
                    $count_data = $count_result->fetch_assoc();
                    $photo_count = $count_data['photo_count'];
                }
                ?>
                
                <div class="manage-collection-detail">
                    <i class="fas fa-images"></i>
                    <?php echo $photo_count; ?> Photos
                </div>
                
                <a href="collection.php?id=<?php echo $manage_collection; ?>" class="view-btn admin-btn">
                    <i class="fas fa-eye"></i> View Collection
                </a>
            </div>
        </div>
        
       
        <div class="action-bar">
            <div class="action-bar-left">
                <button class="admin-btn edit-btn" onclick="showAddImagesModal(<?php echo $manage_collection; ?>)">
                    <i class="fas fa-plus"></i> Add Images
                </button>
                <button class="admin-btn delete-btn" id="removeSelected" style="display: none;">
                    <i class="fas fa-trash"></i> Remove Selected
                </button>
            </div>
            <div class="action-bar-right">
                <div class="filter-group">
                    <span class="filter-label">Sort by:</span>
                    <select class="filter-select" id="sortOrder">
                        <option value="newest">Date Added (Newest)</option>
                        <option value="oldest">Date Added (Oldest)</option>
                        <option value="name">Name</option>
                    </select>
                </div>
            </div>
        </div>
        
       
        <div class="gallery-container">
            <div class="gallery-grid" id="collectionImages">
                <?php
               
                $sql = "SELECT ci.*, i.filename, i.description, i.upload_date 
                        FROM collection_images ci
                        INNER JOIN images i ON ci.image_id = i.id
                        WHERE ci.collection_id = " . mysqli_real_escape_string($conn, $manage_collection) . " 
                        ORDER BY ci.sort_order";
                        
                $result = $conn->query($sql);
                
                if ($result && $result->num_rows > 0):
                    while ($row = $result->fetch_assoc()): 
                        $uploadDate = new DateTime($row['upload_date']);
                        $formattedDate = $uploadDate->format('F j, Y');
                    ?>
                    <div class="gallery-item" data-image-id="<?php echo $row['image_id']; ?>">
                        <div class="gallery-img-container">
                            <img src="uploads/<?php echo htmlspecialchars($row['filename']); ?>" 
                                 alt="<?php echo htmlspecialchars($row['description'] ?? 'Collection Photo'); ?>"
                                 class="gallery-img">
                            <div class="gallery-overlay">
                                <div class="gallery-actions">
                                    <button class="gallery-action-btn" onclick="editImage(<?php echo $row['image_id']; ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="gallery-action-btn" onclick="removeFromCollection(<?php echo $row['image_id']; ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <button class="gallery-action-btn select-image-btn" onclick="toggleImageSelection(this)">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="gallery-info">
                            <p class="gallery-description">
                                <?php echo htmlspecialchars($row['description'] ?? 'No description'); ?>
                            </p>
                            <p class="gallery-date">
                                <i class="far fa-calendar-alt"></i> <?php echo $formattedDate; ?>
                            </p>
                        </div>
                    </div>
                <?php 
                    endwhile;
                else:
                ?>
                    <div class="no-images" style="grid-column: 1 / -1;">
                        <i class="fas fa-images"></i>
                        <h3>No images in this collection</h3>
                        <p>Click "Add Images" to add photos to this collection.</p>
                    </div>
                <?php
                endif;
                ?>
            </div>
        </div>
        
        <?php else: ?>
        
        <div class="gallery-container">
            <div class="gallery-header">
                <h2>Manage Collections</h2>
                <button class="gallery-action-btn" onclick="showNewCollectionModal()">
                    <i class="fas fa-plus"></i> New Collection
                </button>
            </div>
            
            <p class="section-description">Create and manage photo collections for school events and activities.</p>
            
            <div class="collections-grid">
               
                <div class="collection-item add-collection">
                    <div class="add-collection-content" onclick="showNewCollectionModal()">
                        <div class="add-icon">
                            <i class="fas fa-plus-circle"></i>
                        </div>
                        <h3>Create New Collection</h3>
                        <p>Add an event collection for students</p>
                    </div>
                </div>
                
            
                <?php
              
                $sql = "SELECT * FROM collections ORDER BY date DESC";
                $result = $conn->query($sql);
                
                if ($result && $result->num_rows > 0):
                    while ($row = $result->fetch_assoc()):
                        $collectionDate = new DateTime($row['date']);
                        $formattedDate = $collectionDate->format('F j, Y');
                        
                       
                        $photoSql = "SELECT COUNT(*) AS photo_count FROM collection_images WHERE collection_id = " . $row['id'];
                        $photoResult = $conn->query($photoSql);
                        $photoCount = 0;
                        if ($photoResult && $photoResult->num_rows > 0) {
                            $photoData = $photoResult->fetch_assoc();
                            $photoCount = $photoData['photo_count'];
                        }
                ?>
                <div class="collection-item">
                    <div class="collection-image-container">
                        <img src="uploads/<?php echo htmlspecialchars($row['cover_image']); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>" class="collection-image">
                        <div class="collection-overlay">
                            <span class="collection-count">
                                <i class="fas fa-image"></i> <?php echo $photoCount; ?> photos
                            </span>
                        </div>
                    </div>
                    <div class="collection-info">
                        <h3><?php echo htmlspecialchars($row['name']); ?></h3>
                        <p class="collection-date"><?php echo $formattedDate; ?></p>
                    </div>
                    <div class="collection-actions">
                        <a href="admingallery.php?collection=<?php echo $row['id']; ?>" class="admin-btn view-btn">
                            <i class="fas fa-images"></i> Manage
                        </a>
                        <button class="admin-btn edit-btn" onclick="editCollection(<?php echo $row['id']; ?>)">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button class="admin-btn delete-btn" onclick="confirmDeleteCollection(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['name']); ?>')">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </div>
                </div>
                <?php
                    endwhile;
                endif;
                ?>
            </div>
        </div>
        <?php endif; ?>
    </main>

    
    <div id="newCollectionModal" class="collection-modal">
        <div class="collection-modal-content">
            <span class="modal-close" onclick="closeNewCollectionModal()"><i class="fas fa-times"></i></span>
            <h3>Create New Collection</h3>
            <form id="newCollectionForm" action="createcollection.php" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="collection-name">Collection Name</label>
                    <input type="text" id="collection-name" name="collection_name" required 
                           placeholder="Enter collection name (e.g., Graduation Day)">
                </div>
                
                <div class="form-group">
                    <label for="collection-date">Event Date</label>
                    <input type="date" id="collection-date" name="collection_date" required>
                </div>
                
                <div class="form-group">
                    <label for="collection-description">Description</label>
                    <textarea id="collection-description" name="collection_description" 
                           placeholder="Describe this collection of photos..."></textarea>
                </div>
                
                <div class="form-group">
                    <label for="collection-cover">Cover Image</label>
                    <div class="file-input-container">
                        <label for="collection-cover" class="file-input-label">
                            <i class="fas fa-image"></i> Choose Cover Image
                        </label>
                        <input type="file" name="collection_cover" id="collection-cover" accept="image/*" required>
                        <span class="file-name" id="cover-file-name">No file chosen</span>
                    </div>
                </div>
                
                <div class="modal-actions">
                    <button type="submit" class="modal-btn edit-btn">
                        <i class="fas fa-save"></i> Create Collection
                    </button>
                    <button type="button" class="modal-btn cancel-btn" onclick="closeNewCollectionModal()">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    
    <script>
        
        function toggleAdminMenu() {
            document.getElementById('adminProfileMenu').classList.toggle('active');
            
            
            document.addEventListener('click', function closeMenu(e) {
                const menu = document.getElementById('adminProfileMenu');
                const trigger = document.querySelector('.profile-trigger');
                
                if (!menu.contains(e.target) && !trigger.contains(e.target)) {
                    menu.classList.remove('active');
                    document.removeEventListener('click', closeMenu);
                }
            });
        }
    </script>

    
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
   
        function showNewCollectionModal() {
            document.getElementById('newCollectionModal').style.display = 'block';
            document.body.style.overflow = 'hidden';
            
        
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('collection-date').value = today;
        }
        
        function closeNewCollectionModal() {
            document.getElementById('newCollectionModal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }
        
       
        document.addEventListener('DOMContentLoaded', function() {
           
            const coverInput = document.getElementById('collection-cover');
            const coverFileName = document.getElementById('cover-file-name');
            
            if (coverInput && coverFileName) {
                coverInput.addEventListener('change', function() {
                    if (this.files && this.files.length > 0) {
                        coverFileName.textContent = this.files[0].name;
                    } else {
                        coverFileName.textContent = 'No file chosen';
                    }
                });
            }
            
      
            window.addEventListener('click', function(e) {
                const modal = document.getElementById('newCollectionModal');
                if (e.target === modal) {
                    closeNewCollectionModal();
                }
            });
        });
        
        
        function toggleImageSelection(button) {
            const galleryItem = button.closest('.gallery-item');
            galleryItem.classList.toggle('selected');
            
            
            const selectedCount = document.querySelectorAll('.gallery-item.selected').length;
            const removeSelectedBtn = document.getElementById('removeSelected');
            
           
            if (selectedCount > 0) {
                removeSelectedBtn.style.display = 'flex';
                removeSelectedBtn.textContent = `Remove Selected (${selectedCount})`;
            } else {
                removeSelectedBtn.style.display = 'none';
            }
        }
        
       
        function editCollection(collectionId) {
            
            window.location.href = `admingallery.php?collection=${collectionId}&action=edit`;
        }
        
        function confirmDeleteCollection(collectionId, collectionName) {
            if (confirm(`Are you sure you want to delete the collection "${collectionName}"? This cannot be undone.`)) {
                window.location.href = `delete_collection.php?id=${collectionId}`;
            }
        }
        
       
        function removeFromCollection(imageId) {
            const collectionId = <?php echo $manage_collection ?: 0; ?>;
            if (confirm('Are you sure you want to remove this image from the collection?')) {
                
                fetch(`remove_collection_image.php?collection_id=${collectionId}&image_id=${imageId}`, {
                    method: 'POST'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        
                        const imageElement = document.querySelector(`.gallery-item[data-image-id="${imageId}"]`);
                        imageElement.remove();
                        alert('Image removed from collection');
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while removing the image');
                });
            }
        }
        
      
        function toggleMobileMenu() {
            const navMenu = document.getElementById('navMenu');
            navMenu.classList.toggle('active');
            
           
            if(navMenu.classList.contains('active')) {
                document.body.style.overflow = 'hidden';
                
                
                if(!document.querySelector('.nav-overlay')) {
                    const overlay = document.createElement('div');
                    overlay.className = 'nav-overlay';
                    overlay.onclick = toggleMobileMenu;
                    document.body.appendChild(overlay);
                    
                   
                    setTimeout(() => {
                        overlay.style.opacity = '1';
                    }, 10);
                }
            } else {
                document.body.style.overflow = '';
                
               
                const overlay = document.querySelector('.nav-overlay');
                if(overlay) {
                    overlay.style.opacity = '0';
                    setTimeout(() => {
                        overlay.remove();
                    }, 300);
                }
            }
        }
    </script>

    
    <style>
        
        .admin-footer {
            background-color: white;
            border-top: 1px solid var(--border-color);
            padding: 20px 0;
            margin-top: 40px;
        }
        
        .footer-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 30px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .footer-left {
            color: var(--text-muted);
            font-size: 14px;
        }
        
        .footer-right {
            display: flex;
            gap: 20px;
        }
        
        .footer-right a {
            color: var(--text-muted);
            text-decoration: none;
            font-size: 14px;
            transition: color 0.2s ease;
        }
        
        .footer-right a:hover {
            color: var(--primary-color);
        }
        
        @media (max-width: 768px) {
            .footer-container {
                flex-direction: column;
                text-align: center;
            }
        }
    </style>

    <footer class="admin-footer">
        <div class="footer-container">
            <div class="footer-left">
                &copy; <?php echo date('Y'); ?> CMRICTHS Student Portal
            </div>
            <div class="footer-right">
                <a href="privacy.php">Privacy Policy</a>
                <a href="terms.php">Terms of Use</a>
                <a href="contact.php">Contact</a>
            </div>
        </div>
    </footer>

    

    <script>
    
    function showAddImagesModal(collectionId) {
       
        const modalHtml = `
        <div id="addImagesModal" class="modal">
            <div class="modal-content">
                <span class="close-btn" onclick="closeModal('addImagesModal')">&times;</span>
                <h2>Add Images to Collection</h2>
                
                <ul class="modal-tabs">
                    <li class="tab-item active" data-tab="uploadTab">Upload New Images</li>
                    <li class="tab-item" data-tab="existingTab">Select Existing Images</li>
                </ul>
                
                <div id="uploadTab" class="tab-content active">
                    <form id="uploadForm" action="upload_collection_images.php" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="collection_id" value="${collectionId}">
                        
                        <div class="form-group">
                            <label for="images">Select Images:</label>
                            <input type="file" id="images" name="images[]" multiple accept="image/*" required>
                        </div>
                        
                        <div id="imagePreviewContainer" class="image-preview-container"></div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn primary-btn">Upload Images</button>
                            <button type="button" class="btn cancel-btn" onclick="closeModal('addImagesModal')">Cancel</button>
                        </div>
                    </form>
                </div>
                
                <div id="existingTab" class="tab-content">
                    <div id="existingImages" class="existing-images">
                        <p class="loading-text"><i class="fas fa-spinner fa-spin"></i> Loading images...</p>
                    </div>
                    
                    <form id="existingForm" action="add_existing_images.php" method="post">
                        <input type="hidden" name="collection_id" value="${collectionId}">
                        <input type="hidden" name="selected_images" id="selectedImagesInput" value="">
                        
                        <div class="form-actions">
                            <button type="submit" class="btn primary-btn">Add Selected Images</button>
                            <button type="button" class="btn cancel-btn" onclick="closeModal('addImagesModal')">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>`;
        
        
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        
       
        document.getElementById('addImagesModal').style.display = 'block';
        
        
        setupModalTabs();
        setupImagePreview();
        loadExistingImages(collectionId);
        
        
        document.body.style.overflow = 'hidden';
    }

    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.remove();
            document.body.style.overflow = 'auto';
        }
    }

    function setupModalTabs() {
        const tabs = document.querySelectorAll('.tab-item');
        tabs.forEach(tab => {
            tab.addEventListener('click', function() {
                
                document.querySelectorAll('.tab-item').forEach(t => t.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
                
               
                this.classList.add('active');
                
              
                const tabId = this.getAttribute('data-tab');
                document.getElementById(tabId).classList.add('active');
            });
        });
    }

    function setupImagePreview() {
        const input = document.getElementById('images');
        const previewContainer = document.getElementById('imagePreviewContainer');
        
        input.addEventListener('change', function() {
            previewContainer.innerHTML = '';
            
            if (this.files && this.files.length > 0) {
                for (let i = 0; i < this.files.length; i++) {
                    const file = this.files[i];
                    
                 
                    if (!file.type.match('image.*')) {
                        continue;
                    }
                    
                    const reader = new FileReader();
                    
                    reader.onload = function(e) {
                        const previewDiv = document.createElement('div');
                        previewDiv.className = 'image-preview';
                        
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.title = file.name;
                        
                        const fileName = document.createElement('div');
                        fileName.className = 'file-name';
                        fileName.textContent = file.name;
                        
                        previewDiv.appendChild(img);
                        previewDiv.appendChild(fileName);
                        previewContainer.appendChild(previewDiv);
                    };
                    
                    reader.readAsDataURL(file);
                }
            }
        });
    }

    function loadExistingImages(collectionId) {
        const existingImagesDiv = document.getElementById('existingImages');
        
    
        fetch(`get_available_images.php?collection_id=${collectionId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (data.images && data.images.length > 0) {
                        existingImagesDiv.innerHTML = '<div class="images-grid"></div>';
                        const grid = existingImagesDiv.querySelector('.images-grid');
                        
                        data.images.forEach(image => {
                            const imageDiv = document.createElement('div');
                            imageDiv.className = 'existing-image';
                            imageDiv.dataset.id = image.id;
                            
                            const img = document.createElement('img');
                            img.src = `uploads/${image.filename}`;
                            img.alt = image.description || 'Image';
                            
                            const checkbox = document.createElement('input');
                            checkbox.type = 'checkbox';
                            checkbox.className = 'image-select';
                            checkbox.value = image.id;
                            checkbox.name = 'image_ids[]';
                            
                            const caption = document.createElement('div');
                            caption.className = 'image-caption';
                            caption.textContent = image.description || 'No description';
                            
                            imageDiv.appendChild(img);
                            imageDiv.appendChild(checkbox);
                            imageDiv.appendChild(caption);
                            
                            imageDiv.addEventListener('click', function(e) {
                                if (e.target !== checkbox) {
                                    checkbox.checked = !checkbox.checked;
                                }
                                this.classList.toggle('selected', checkbox.checked);
                                updateSelectedImagesInput();
                            });
                            
                            grid.appendChild(imageDiv);
                        });
                    } else {
                        existingImagesDiv.innerHTML = '<p class="no-images">No images available to add</p>';
                    }
                } else {
                    existingImagesDiv.innerHTML = `<p class="error-text">Error: ${data.message || 'Could not load images'}</p>`;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                existingImagesDiv.innerHTML = '<p class="error-text">Failed to load images. Please try again.</p>';
            });
    }

    function updateSelectedImagesInput() {
        const selectedCheckboxes = document.querySelectorAll('.image-select:checked');
        const selectedIds = Array.from(selectedCheckboxes).map(cb => cb.value);
        document.getElementById('selectedImagesInput').value = selectedIds.join(',');
    }

   
    document.addEventListener('DOMContentLoaded', function() {
       
        const addImageButtons = document.querySelectorAll('.add-images-btn');
        
        addImageButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const collectionId = this.dataset.collection;
                showAddImagesModal(collectionId);
            });
        });
    });
    </script>

    <script>

function showAddImagesModal(collectionId) {
    console.log("Opening modal for collection ID:", collectionId);
    

    const modalHtml = `
    <div id="addImagesModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="document.getElementById('addImagesModal').remove(); document.body.style.overflow = 'auto';">&times;</span>
            <h2>Add Images to Collection</h2>
            
            <div id="uploadTab" class="tab-content active">
                <form id="uploadForm" action="upload_collection_images.php" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="collection_id" value="${collectionId}">
                    
                    <div class="form-group">
                        <label for="images">Select Images:</label>
                        <input type="file" id="images" name="images[]" multiple accept="image/*" required>
                    </div>
                    
                    <div id="imagePreviewContainer" class="image-preview-container"></div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn primary-btn">Upload Images</button>
                        <button type="button" class="btn cancel-btn" onclick="document.getElementById('addImagesModal').remove(); document.body.style.overflow = 'auto';">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>`;
    

    document.body.insertAdjacentHTML('beforeend', modalHtml);
    

    document.getElementById('addImagesModal').style.display = 'block';
    
    
    const input = document.getElementById('images');
    const previewContainer = document.getElementById('imagePreviewContainer');
    
    input.addEventListener('change', function() {
        previewContainer.innerHTML = '';
        
        if (this.files && this.files.length > 0) {
            for (let i = 0; i < this.files.length; i++) {
                const file = this.files[i];
                
                
                if (!file.type.match('image.*')) {
                    continue;
                }
                
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    const previewDiv = document.createElement('div');
                    previewDiv.className = 'image-preview';
                    
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.title = file.name;
                    
                    const fileName = document.createElement('div');
                    fileName.className = 'file-name';
                    fileName.textContent = file.name;
                    
                    previewDiv.appendChild(img);
                    previewDiv.appendChild(fileName);
                    previewContainer.appendChild(previewDiv);
                };
                
                reader.readAsDataURL(file);
            }
        }
    });
    
   
    document.body.style.overflow = 'hidden';
}


document.addEventListener('DOMContentLoaded', function() {
    const actionButton = document.querySelector('.action-bar .admin-btn.edit-btn');
    if (actionButton) {
        actionButton.onclick = function() {
            showAddImagesModal(<?php echo $manage_collection; ?>);
        };
    }
});
</script>
</body>
</html>