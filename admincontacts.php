<?php
session_start();
require 'Config.php';

// Near the top of your admincontacts.php file
// Set the default timezone to your local timezone
date_default_timezone_set('Asia/Manila'); // Use your local timezone (Philippines)

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$isTeacher = false;
$teacherSubject = '';
$isAdmin = false;

if(!empty($_SESSION["Admin_ID"])) {
    $Admin_ID = $_SESSION["Admin_ID"];
    
    $adminQuery = "SELECT * FROM table_admin WHERE Admin_ID = ?";
    $stmt = $conn->prepare($adminQuery);
    $stmt->bind_param("i", $Admin_ID);
    $stmt->execute();
    $adminResult = $stmt->get_result();
    
    if($adminResult && $adminResult->num_rows > 0) {
        $adminData = $adminResult->fetch_assoc();
        $isAdmin = true;
        
        if($adminData['role'] == 'teacher') {
            $isTeacher = true;
            $teacherSubject = $adminData['subject']; 
        }
        
        if(!isset($adminData['profile_picture']) || empty($adminData['profile_picture'])) {
            $adminData['profile_picture'] = 'admin-default.jpg';
        }
    } else {
        header("Location: Login.php");
        exit();
    }
} else {
    header("Location: Login.php");
    exit();
}

// Create contact_settings table if it doesn't exist
$check_settings_table = mysqli_query($conn, "SHOW TABLES LIKE 'contact_settings'");
if(mysqli_num_rows($check_settings_table) == 0) {
    $create_settings_table = "CREATE TABLE IF NOT EXISTS contact_settings (
        id INT PRIMARY KEY AUTO_INCREMENT,
        setting_key VARCHAR(50) NOT NULL UNIQUE,
        setting_value TEXT NOT NULL,
        last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    if(!mysqli_query($conn, $create_settings_table)) {
        die("Error creating contact_settings table: " . mysqli_error($conn));
    }
    
    // Insert default values
    $default_settings = [
        ['address', 'Dona Aurora St, Claro M. Recto, Angeles City, Pampanga'],
        ['phone', '(045) 887 5502'],
        ['email', 'cmricthsangelescity@yahoo.com'],
        ['hours', 'Monday - Friday: 6:00 AM - 6:00 PM'],
        ['google_map_embed', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d516.5982697312345!2d120.59260559999996!3d15.1450612!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3396f241c2dfd6db%3A0xc17b92187d52d12c!2sClaro%20M.%20Recto%20Information%20and%20Communication%20Technology%20High%20School!5e0!3m2!1sen!2sph!4v1740848353961!5m2!1sen!2sph']
    ];
    
    foreach ($default_settings as $setting) {
        $key = mysqli_real_escape_string($conn, $setting[0]);
        $value = mysqli_real_escape_string($conn, $setting[1]);
        $insert_query = "INSERT INTO contact_settings (setting_key, setting_value) VALUES ('$key', '$value')";
        mysqli_query($conn, $insert_query);
    }
}

// Function to get contact settings
function getContactSettings($conn) {
    $settings = [];
    $result = mysqli_query($conn, "SELECT * FROM contact_settings");
    if($result) {
        while($row = mysqli_fetch_assoc($result)) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
    }
    return $settings;
}

// Get current settings
$settings = getContactSettings($conn);

// Handle contact settings update
$settings_message = '';
$settings_messageType = '';

if(isset($_POST['update_contacts'])) {
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $hours = mysqli_real_escape_string($conn, $_POST['hours']);
    $google_map_embed = mysqli_real_escape_string($conn, $_POST['google_map_embed']);
    
    // Update each setting
    $queries = [
        "UPDATE contact_settings SET setting_value = '$address' WHERE setting_key = 'address'",
        "UPDATE contact_settings SET setting_value = '$phone' WHERE setting_key = 'phone'",
        "UPDATE contact_settings SET setting_value = '$email' WHERE setting_key = 'email'",
        "UPDATE contact_settings SET setting_value = '$hours' WHERE setting_key = 'hours'",
        "UPDATE contact_settings SET setting_value = '$google_map_embed' WHERE setting_key = 'google_map_embed'"
    ];
    
    $success = true;
    foreach($queries as $query) {
        if(!mysqli_query($conn, $query)) {
            $success = false;
            break;
        }
    }
    
    if($success) {
        $settings_message = "Contact information has been updated successfully!";
        $settings_messageType = "success";
        
        // Refresh settings
        $settings = getContactSettings($conn);
    } else {
        $settings_message = "Error updating contact information: " . mysqli_error($conn);
        $settings_messageType = "error";
    }
}

// Create contact_feedback table if it doesn't exist
$check_table = mysqli_query($conn, "SHOW TABLES LIKE 'contact_feedback'");
if(mysqli_num_rows($check_table) == 0) {
    $create_table_query = "CREATE TABLE IF NOT EXISTS contact_feedback (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL,
        subject VARCHAR(200) NOT NULL,
        message TEXT NOT NULL,
        status ENUM('new', 'read', 'responded') DEFAULT 'new',
        response TEXT,
        response_date DATETIME,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if(!mysqli_query($conn, $create_table_query)) {
        die("Error creating contact_feedback table: " . mysqli_error($conn));
    }
}

// Handle status update
if (isset($_POST['update_status'])) {
    $feedback_id = (int) $_POST['feedback_id'];
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    
    $update_query = "UPDATE contact_feedback SET status = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("si", $status, $feedback_id);
    
    if ($update_stmt->execute()) {
        header("Location: admincontacts.php?success=status_update");
        exit();
    } else {
        header("Location: admincontacts.php?error=status_update");
        exit();
    }
}

// Handle response submission
if (isset($_POST['submit_response'])) {
    $feedback_id = (int) $_POST['feedback_id'];
    $response = mysqli_real_escape_string($conn, $_POST['response']);
    
    // Format current date and time in proper MySQL datetime format
    $current_date = date('Y-m-d H:i:s');
    
    $response_query = "UPDATE contact_feedback SET response = ?, response_date = ?, status = 'responded' WHERE id = ?";
    $response_stmt = $conn->prepare($response_query);
    $response_stmt->bind_param("ssi", $response, $current_date, $feedback_id);
    
    if ($response_stmt->execute()) {
        header("Location: admincontacts.php?success=response_sent");
        exit();
    } else {
        header("Location: admincontacts.php?error=response_failed");
        exit();
    }
}

// Handle feedback deletion
if (isset($_GET['delete'])) {
    $feedback_id = (int) $_GET['delete'];
    
    $delete_query = "DELETE FROM contact_feedback WHERE id = ?";
    $delete_stmt = $conn->prepare($delete_query);
    $delete_stmt->bind_param("i", $feedback_id);
    
    if ($delete_stmt->execute()) {
        header("Location: admincontacts.php?success=feedback_deleted");
        exit();
    } else {
        header("Location: admincontacts.php?error=delete_failed");
        exit();
    }
}

// Get all feedback messages with filter
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$where_clause = "";

switch ($filter) {
    case 'new':
        $where_clause = "WHERE status = 'new'";
        break;
    case 'read':
        $where_clause = "WHERE status = 'read'";
        break;
    case 'responded':
        $where_clause = "WHERE status = 'responded'";
        break;
}

$feedbacks_query = "SELECT * FROM contact_feedback $where_clause ORDER BY created_at DESC";
$feedbacks = $conn->query($feedbacks_query);

// Count messages by status
$counts_query = "SELECT 
                    SUM(CASE WHEN status = 'new' THEN 1 ELSE 0 END) as new_count,
                    SUM(CASE WHEN status = 'read' THEN 1 ELSE 0 END) as read_count,
                    SUM(CASE WHEN status = 'responded' THEN 1 ELSE 0 END) as responded_count,
                    COUNT(*) as total_count
                FROM contact_feedback";
$counts_result = $conn->query($counts_query)->fetch_assoc();

// Determine active tab
$activeTab = isset($_GET['tab']) ? $_GET['tab'] : 'messages';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Management - Admin Dashboard - CMRICTHS</title>
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .dashboard-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: #fff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-card .number {
            font-size: 2.5rem;
            font-weight: 700;
            margin: 10px 0;
            color: #1a2a3a;
        }
        
        .stat-card .label {
            font-size: 0.95rem;
            color: #555;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .stat-card.new-card {
            border-top: 4px solid #ffc107;
        }
        
        .stat-card.read-card {
            border-top: 4px solid #17a2b8;
        }
        
        .stat-card.responded-card {
            border-top: 4px solid #28a745;
        }
        
        .stat-card.total-card {
            border-top: 4px solid #6f42c1;
        }
        
        .feedback-filters {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .filter-tabs {
            display: flex;
            gap: 10px;
        }
        
        .filter-tab {
            padding: 8px 16px;
            border-radius: 20px;
            background-color: #f0f0f0;
            color: #333;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .filter-tab:hover {
            background-color: #e0e0e0;
        }
        
        .filter-tab.active {
            background-color: #3498db;
            color: white;
        }
        
        .feedback-card {
            background: #fff;
            border-radius: 8px;
            margin-bottom: 15px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        
        .feedback-card:hover {
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15);
        }
        
        .feedback-header {
            padding: 15px 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .feedback-sender {
            font-weight: 600;
            font-size: 1.1rem;
        }
        
        .feedback-meta {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .feedback-date {
            color: #777;
            font-size: 0.9rem;
        }
        
        .feedback-status {
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .status-new {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .status-read {
            background-color: #d1ecf1;
            color: #0c5460;
        }
        
        .status-responded {
            background-color: #d4edda;
            color: #155724;
        }
        
        .feedback-body {
            padding: 20px;
        }
        
        .feedback-subject {
            margin-top: 0;
            margin-bottom: 15px;
            color: #333;
        }
        
        .feedback-message {
            margin-bottom: 20px;
            line-height: 1.6;
            color: #444;
        }
        
        .feedback-response {
            background-color: #f8f9fa;
            border-left: 4px solid #28a745;
            padding: 15px;
            margin-top: 20px;
        }
        
        .feedback-response h4 {
            margin-top: 0;
            color: #155724;
        }
        
        .response-form {
            margin-top: 20px;
        }
        
        .response-textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            min-height: 120px;
            margin-bottom: 15px;
            font-family: inherit;
            resize: vertical;
        }
        
        .feedback-actions {
            display: flex;
            justify-content: space-between;
            padding: 15px 20px;
            border-top: 1px solid #eee;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
        }
        
        .status-buttons {
            display: flex;
            gap: 10px;
        }
        
        .no-feedback {
            text-align: center;
            padding: 50px;
            background-color: #f8f9fa;
            border-radius: 8px;
            margin-top: 20px;
        }
        
        .no-feedback i {
            font-size: 3rem;
            color: #adb5bd;
            margin-bottom: 15px;
        }
        
        .no-feedback p {
            color: #6c757d;
            font-size: 1.1rem;
        }

        .profile-footer {
            border-top: 1px solid #eee;
            padding-top: 10px;
            margin-top: 10px;
            text-align: center;
        }

        .logout-button {
            display: block;
            background-color: #dc3545;
            color: white;
            padding: 10px 20px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .logout-button:hover {
            background-color: #c82333;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(220, 53, 69, 0.3);
        }

        .logout-link {
            color: #dc3545 !important;
        }

        .logout-link:hover {
            background-color: #fff1f2;
        }

        .profile-menu {
            min-width: 240px;
            padding-bottom: 10px;
        }

       
        .profile-dropdown {
            position: relative;
            z-index: 1000;
        }

       
        .profile-menu {
            position: absolute;
            right: 0;
            top: 100%;
            background: white;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.15);
            min-width: 240px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(10px);
            transition: all 0.3s ease;
            z-index: 1000;
            padding-bottom: 10px;
            margin-top: 5px;
        }

        .profile-menu.open {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        
        .logout-nav-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            background-color: #dc3545;
            color: white;
            padding: 8px 15px;
            border-radius: 4px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s ease;
            margin-left: 10px;
        }

        .logout-nav-btn:hover {
            background-color: #c82333;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(220, 53, 69, 0.3);
        }

        
        @media (max-width: 992px) {
            .logout-nav-btn {
                padding: 8px 10px;
            }
            
            .logout-nav-btn i {
                margin-right: 0;
            }
            
            .logout-nav-btn span {
                display: none;
            }
        }
        
 
        .tab-navigation {
            display: flex;
            margin-bottom: 20px;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .tab-link {
            padding: 12px 20px;
            font-weight: 500;
            color: #555;
            cursor: pointer;
            position: relative;
            transition: all 0.3s ease;
        }
        
        .tab-link:hover {
            color: #1a2a3a;
            background-color: #f8f9fa;
        }
        
        .tab-link.active {
            color: #1a2a3a;
            font-weight: 600;
        }
        
        .tab-link.active::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0;
            width: 100%;
            height: 3px;
            background-color: #3498db;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        /* Contact Settings Form Styles */
        .settings-section {
            background: white;
            border-radius: 8px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .settings-header {
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .settings-header h3 {
            margin: 0;
            color: #1a2a3a;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
            outline: none;
        }
        
        textarea.form-control {
            min-height: 100px;
            resize: vertical;
        }
        
        .map-preview {
            margin-top: 20px;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 15px;
            background: #f9f9fa;
        }
        
        .map-preview h4 {
            margin-top: 0;
            color: #1a2a3a;
            margin-bottom: 15px;
        }
        
        .map-preview iframe {
            width: 100%;
            height: 300px;
            border: none;
            border-radius: 4px;
        }
        
        .btn-save {
            background: linear-gradient(to right, #3498db, #2980b9);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 4px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-save:hover {
            background: linear-gradient(to right, #2980b9, #2573a7);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(41, 128, 185, 0.3);
        }
        
        .status-message {
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        
        .status-message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .status-message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
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
                    <i class="fas fa-users"></i> Student Management
                </a>
                <a href="./adminschedules.php" class="admin-nav-btn">
                    <i class="fas fa-calendar-alt"></i> Schedules
                </a>
                <a href="NewsAdmin.php" class="admin-nav-btn">
                    <i class="fas fa-newspaper"></i> News
                </a>
                <a href="admingallery.php" class="admin-nav-btn">
                    <i class="fas fa-images"></i> Gallery
                </a>
                <a href="admincontacts.php" class="admin-nav-btn active">
                    <i class="fas fa-envelope"></i> Contact Feedback
                </a>
                <a href="Logout.php" class="logout-nav-btn" onclick="return confirm('Are you sure you want to log out?');">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>

                <div class="nav-profile">
                    <div class="profile-dropdown">
                        <div class="profile-trigger" onclick="toggleAdminMenu()">
                            <img src="<?php echo htmlspecialchars($adminData['profile_picture']); ?>" alt="Admin" class="profile-avatar">
                            <span class="profile-name"><?php echo htmlspecialchars($adminData['Username']); ?></span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="profile-menu" id="adminProfileMenu">
                            <div class="profile-header">
                                <img src="<?php echo htmlspecialchars($adminData['profile_picture']); ?>" alt="Admin" class="profile-picture">
                                <div class="profile-info">
                                    <h4><?php echo htmlspecialchars($adminData['Username']); ?></h4>
                                    <p><?php echo $isTeacher ? htmlspecialchars($teacherSubject) . ' Teacher' : 'System Administrator'; ?></p>
                                </div>
                            </div>
                            <div class="profile-links">
                                <a href="admin_settings.php">
                                    <i class="fas fa-cog"></i> Settings
                                </a>
                                <a href="Logout.php" class="logout-link" onclick="return confirm('Are you sure you want to log out?');">
                                    <i class="fas fa-sign-out-alt"></i> Logout
                                </a>
                            </div>
                            
                            <div class="profile-footer">
                                <a href="Logout.php" class="logout-button" onclick="return confirm('Are you sure you want to log out?');">
                                    <i class="fas fa-power-off"></i> Log Out
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
                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success">
                        <?php 
                            if ($_GET['success'] === 'status_update') {
                                echo "Feedback status updated successfully.";
                            } elseif ($_GET['success'] === 'response_sent') {
                                echo "Response sent successfully.";
                            } elseif ($_GET['success'] === 'feedback_deleted') {
                                echo "Feedback deleted successfully.";
                            } else {
                                echo "Operation completed successfully.";
                            }
                        ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-error">
                        <?php
                        if ($_GET['error'] === 'status_update') {
                            echo "Failed to update feedback status.";
                        } elseif ($_GET['error'] === 'response_failed') {
                            echo "Failed to send response.";
                        } elseif ($_GET['error'] === 'delete_failed') {
                            echo "Failed to delete feedback.";
                        } else {
                            echo "An error occurred. Please try again.";
                        }
                        ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="dashboard-card">
                <div class="card-header">
                    <h2><i class="fas fa-envelope"></i> Contact Management</h2>
                </div>
                
                <!-- Tab Navigation -->
                <div class="tab-navigation">
                    <a href="admincontacts.php?tab=messages" class="tab-link <?php echo $activeTab === 'messages' ? 'active' : ''; ?>">
                        <i class="fas fa-inbox"></i> Contact Messages
                    </a>
                    <a href="admincontacts.php?tab=settings" class="tab-link <?php echo $activeTab === 'settings' ? 'active' : ''; ?>">
                        <i class="fas fa-cog"></i> Contact Information
                    </a>
                </div>
                
                <!-- Messages Tab Content -->
                <div id="messagesTab" class="tab-content <?php echo $activeTab === 'messages' ? 'active' : ''; ?>">
                    <div class="dashboard-stats">
                        <div class="stat-card new-card">
                            <div class="label">New Messages</div>
                            <div class="number"><?php echo $counts_result['new_count'] ?? 0; ?></div>
                            <div class="stat-icon"><i class="fas fa-envelope"></i></div>
                        </div>
                        
                        <div class="stat-card read-card">
                            <div class="label">Read Messages</div>
                            <div class="number"><?php echo $counts_result['read_count'] ?? 0; ?></div>
                            <div class="stat-icon"><i class="fas fa-envelope-open"></i></div>
                        </div>
                        
                        <div class="stat-card responded-card">
                            <div class="label">Responded</div>
                            <div class="number"><?php echo $counts_result['responded_count'] ?? 0; ?></div>
                            <div class="stat-icon"><i class="fas fa-reply"></i></div>
                        </div>
                        
                        <div class="stat-card total-card">
                            <div class="label">Total Feedback</div>
                            <div class="number"><?php echo $counts_result['total_count'] ?? 0; ?></div>
                            <div class="stat-icon"><i class="fas fa-comments"></i></div>
                        </div>
                    </div>
                    
                    <div class="feedback-filters">
                        <div class="filter-tabs">
                            <a href="admincontacts.php?tab=messages" class="filter-tab <?php echo $filter === 'all' ? 'active' : ''; ?>">
                                All Messages
                            </a>
                            <a href="admincontacts.php?tab=messages&filter=new" class="filter-tab <?php echo $filter === 'new' ? 'active' : ''; ?>">
                                New
                            </a>
                            <a href="admincontacts.php?tab=messages&filter=read" class="filter-tab <?php echo $filter === 'read' ? 'active' : ''; ?>">
                                Read
                            </a>
                            <a href="admincontacts.php?tab=messages&filter=responded" class="filter-tab <?php echo $filter === 'responded' ? 'active' : ''; ?>">
                                Responded
                            </a>
                        </div>
                        <div class="search-box">
                            <input type="text" id="searchInput" placeholder="Search feedback...">
                            <i class="fas fa-search"></i>
                        </div>
                    </div>
                    
                    <?php if($feedbacks && $feedbacks->num_rows > 0): ?>
                        <?php while($feedback = $feedbacks->fetch_assoc()): ?>
                            <div class="feedback-card">
                                <div class="feedback-header">
                                    <div class="feedback-sender">
                                        <?php echo htmlspecialchars($feedback['name']); ?> 
                                        <span style="font-size: 0.85rem; color: #666; font-weight: normal;">
                                            (<?php echo htmlspecialchars($feedback['email']); ?>)
                                        </span>
                                    </div>
                                    <div class="feedback-meta">
                                        <span class="feedback-date">
                                            <i class="far fa-clock"></i> 
                                            <?php echo date('M d, Y g:i A', strtotime($feedback['created_at'])); ?>
                                        </span>
                                        <span class="feedback-status status-<?php echo $feedback['status']; ?>">
                                            <?php 
                                            switch($feedback['status']) {
                                                case 'new':
                                                    echo '<i class="fas fa-envelope"></i> New';
                                                    break;
                                                case 'read':
                                                    echo '<i class="fas fa-envelope-open"></i> Read';
                                                    break;
                                                case 'responded':
                                                    echo '<i class="fas fa-reply"></i> Responded';
                                                    break;
                                            }
                                            ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="feedback-body">
                                    <h3 class="feedback-subject"><?php echo htmlspecialchars($feedback['subject']); ?></h3>
                                    <div class="feedback-message">
                                        <?php echo nl2br(htmlspecialchars($feedback['message'])); ?>
                                    </div>
                                    
                                    <?php if($feedback['response']): ?>
                                        <div class="feedback-response">
                                            <h4>Admin Response</h4>
                                            <div class="response-text">
                                                <?php echo nl2br(htmlspecialchars($feedback['response'])); ?>
                                            </div>
                                            <div class="response-date">
                                                Responded on: <?php 
                                                    // Format the date for display
                                                    $response_timestamp = strtotime($feedback['response_date']);
                                                    echo date('F j, Y g:i A', $response_timestamp); 
                                                ?>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <form action="admincontacts.php" method="post" class="response-form">
                                            <input type="hidden" name="feedback_id" value="<?php echo $feedback['id']; ?>">
                                            <textarea name="response" class="response-textarea" placeholder="Type your response here..." required></textarea>
                                            <button type="submit" name="submit_response" class="save-btn">
                                                <i class="fas fa-paper-plane"></i> Send Response
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                                <div class="feedback-actions">
                                    <div class="status-buttons">
                                        <?php if($feedback['status'] !== 'responded'): ?>
                                            <form action="admincontacts.php" method="post">
                                                <input type="hidden" name="feedback_id" value="<?php echo $feedback['id']; ?>">
                                                <?php if($feedback['status'] === 'new'): ?>
                                                    <input type="hidden" name="status" value="read">
                                                    <button type="submit" name="update_status" class="btn btn-info">
                                                        <i class="fas fa-check"></i> Mark as Read
                                                    </button>
                                                <?php elseif($feedback['status'] === 'read'): ?>
                                                    <input type="hidden" name="status" value="new">
                                                    <button type="submit" name="update_status" class="btn btn-secondary">
                                                        <i class="fas fa-undo"></i> Mark as New
                                                    </button>
                                                <?php endif; ?>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                    <div class="action-buttons">
                                        <button type="button" class="delete-btn" onclick="confirmDeleteFeedback(<?php echo $feedback['id']; ?>)">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="no-feedback">
                            <i class="far fa-envelope-open"></i>
                            <p>No feedback messages found.</p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Settings Tab Content -->
                <div id="settingsTab" class="tab-content <?php echo $activeTab === 'settings' ? 'active' : ''; ?>">
                    <?php if(!empty($settings_message)): ?>
                        <div class="status-message <?php echo $settings_messageType; ?>">
                            <?php echo $settings_message; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form action="admincontacts.php?tab=settings" method="POST">
                        <div class="settings-section">
                            <div class="settings-header">
                                <h3><i class="fas fa-info-circle"></i> Contact Details</h3>
                                <small>These details will be displayed on the contact page</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="address">School Address</label>
                                <input type="text" id="address" name="address" class="form-control" 
                                       value="<?php echo htmlspecialchars($settings['address'] ?? ''); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="phone">Phone Number</label>
                                <input type="text" id="phone" name="phone" class="form-control" 
                                       value="<?php echo htmlspecialchars($settings['phone'] ?? ''); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="email">Email Address</label>
                                <input type="email" id="email" name="email" class="form-control" 
                                       value="<?php echo htmlspecialchars($settings['email'] ?? ''); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="hours">Office Hours</label>
                                <input type="text" id="hours" name="hours" class="form-control" 
                                       value="<?php echo htmlspecialchars($settings['hours'] ?? ''); ?>" required>
                            </div>
                        </div>
                        
                        <div class="settings-section">
                            <div class="settings-header">
                                <h3><i class="fas fa-map-marker-alt"></i> Google Maps Settings</h3>
                                <small>Embed code for the location map on the contact page</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="google_map_embed">Google Map Embed URL</label>
                                <textarea id="google_map_embed" name="google_map_embed" class="form-control" required><?php echo htmlspecialchars($settings['google_map_embed'] ?? ''); ?></textarea>
                                <small>Go to Google Maps, search for your location, click "Share", select "Embed a map", copy the iframe src URL and paste it here.</small>
                            </div>
                            
                            <div class="map-preview">
                                <h4>Map Preview</h4>
                                <iframe 
                                    id="map_preview"
                                    src="<?php echo htmlspecialchars($settings['google_map_embed'] ?? ''); ?>" 
                                    allowfullscreen=""
                                    loading="lazy"
                                    referrerpolicy="no-referrer-when-downgrade">
                                </iframe>
                            </div>
                        </div>
                        
                        <button type="submit" name="update_contacts" class="btn-save">
                            <i class="fas fa-save"></i> Save Contact Information
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <script>
        function toggleAdminMenu() {
            const menu = document.getElementById('adminProfileMenu');
            
            const isOpen = menu.classList.contains('open');
            
            if (isOpen) {
                menu.classList.remove('open');
            } else {
                menu.classList.add('open');
            }
            
            if (!isOpen) {
             
                setTimeout(() => {
                    document.addEventListener('click', closeMenuOnClickOutside);
                }, 10);
            } else {
                document.removeEventListener('click', closeMenuOnClickOutside);
            }
            
            event.stopPropagation();
        }

        function closeMenuOnClickOutside(event) {
            const menu = document.getElementById('adminProfileMenu');
            const profileTrigger = document.querySelector('.profile-trigger');
            
            if (!menu.contains(event.target) && !profileTrigger.contains(event.target)) {
                menu.classList.remove('open');
                document.removeEventListener('click', closeMenuOnClickOutside);
            }
        }
        
        function confirmDeleteFeedback(id) {
            if (confirm("Are you sure you want to delete this feedback? This action cannot be undone.")) {
                window.location.href = "admincontacts.php?delete=" + id;
            }
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const feedbackCards = document.querySelectorAll('.feedback-card');
            
            searchInput.addEventListener('keyup', function() {
                const searchTerm = searchInput.value.toLowerCase();
                
                feedbackCards.forEach(card => {
                    const name = card.querySelector('.feedback-sender').textContent.toLowerCase();
                    const email = card.querySelector('.feedback-sender span').textContent.toLowerCase();
                    const subject = card.querySelector('.feedback-subject').textContent.toLowerCase();
                    const message = card.querySelector('.feedback-message').textContent.toLowerCase();
                    
                    if (name.includes(searchTerm) || 
                        email.includes(searchTerm) || 
                        subject.includes(searchTerm) || 
                        message.includes(searchTerm)) {
                        card.style.display = '';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
            
            setTimeout(() => {
                const alerts = document.querySelectorAll('.alert');
                alerts.forEach(alert => {
                    alert.style.opacity = '0';
                    setTimeout(() => {
                        alert.style.display = 'none';
                    }, 500);
                });
            }, 5000);
            
            // If we're on the settings tab
            if (document.getElementById('settingsTab').classList.contains('active')) {
                const mapEmbedInput = document.getElementById('google_map_embed');
                const mapPreview = document.getElementById('map_preview');
                
                if (mapEmbedInput && mapPreview) {
                    mapEmbedInput.addEventListener('input', function() {
                        mapPreview.src = this.value;
                    });
                }
            }
            
            // Handle tab navigation via JavaScript if not using GET parameters
            const tabLinks = document.querySelectorAll('.tab-link');
            const tabContents = document.querySelectorAll('.tab-content');
            
            tabLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    // This is only needed if you're not using GET params for tabs
                    // e.preventDefault();
                    
                    // Remove active class from all tab links
                    tabLinks.forEach(tabLink => {
                        tabLink.classList.remove('active');
                    });
                    
                    // Add active class to clicked tab link
                    this.classList.add('active');
                    
                    // Show corresponding tab content
                    const tabId = this.getAttribute('href').split('=')[1];
                    tabContents.forEach(content => {
                        content.classList.remove('active');
                    });
                    
                    document.getElementById(tabId + 'Tab').classList.add('active');
                });
            });
            
            // Hide alert messages after 5 seconds
            setTimeout(() => {
                const alerts = document.querySelectorAll('.alert, .status-message');
                alerts.forEach(alert => {
                    alert.style.opacity = '0';
                    setTimeout(() => {
                        alert.style.display = 'none';
                    }, 500);
                });
            }, 5000);
        });
    </script>
</body>
</html>